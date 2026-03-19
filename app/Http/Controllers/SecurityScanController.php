<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SecurityScanController extends Controller
{
    /**
     * Patrones de malware/código sospechoso conocidos
     */
    private $malwarePatterns = [
        // Ejecución dinámica de código
        'eval\s*\(\s*base64_decode' => 'Eval + Base64 decode (ofuscación de malware)',
        'eval\s*\(\s*gzinflate' => 'Eval + gzinflate (código comprimido malicioso)',
        'eval\s*\(\s*str_rot13' => 'Eval + str_rot13 (ofuscación)',
        'eval\s*\(\s*\$_(?:GET|POST|REQUEST|COOKIE)' => 'Eval con input del usuario (backdoor)',
        'assert\s*\(\s*\$_(?:GET|POST|REQUEST)' => 'Assert con input del usuario (backdoor)',
        'preg_replace\s*\(.*/e' => 'preg_replace con /e (ejecución de código)',
        
        // Shells y backdoors
        'c99shell|r57shell|wso\s*shell|b374k' => 'Web shell conocida',
        'FilesMan|WSO\s' => 'File manager malicioso',
        '\$_(?:GET|POST|REQUEST|COOKIE)\s*\[\s*[\'"][a-z0-9]{1,3}[\'"]\s*\]' => 'Variable sospechosa de backdoor corta',
        'passthru\s*\(\s*\$_' => 'Ejecución de comandos con input (backdoor)',
        'shell_exec\s*\(\s*\$_' => 'Shell exec con input del usuario',
        'system\s*\(\s*\$_' => 'System con input del usuario',
        'exec\s*\(\s*\$_(?:GET|POST|REQUEST)' => 'Exec con input del usuario',
        'popen\s*\(\s*\$_' => 'Popen con input del usuario',
        'proc_open\s*\(\s*\$_' => 'Proc_open con input del usuario',
        
        // Inyección de código remoto
        'file_get_contents\s*\(\s*[\'"]https?://' => 'Carga de archivo remoto (posible inyección)',
        'include\s*\(\s*\$_(?:GET|POST|REQUEST)' => 'Include dinámico con input (LFI/RFI)',
        'require\s*\(\s*\$_(?:GET|POST|REQUEST)' => 'Require dinámico con input (LFI/RFI)',
        
        // Funciones peligrosas sin contexto válido
        'base64_decode\s*\(\s*[\'"][A-Za-z0-9+/=]{100,}[\'"]\s*\)' => 'Cadena base64 larga (posible payload oculto)',
        'chr\s*\(\s*\d+\s*\)\s*\.\s*chr\s*\(\s*\d+\s*\)\s*\.\s*chr' => 'Concatenación de chr() (ofuscación)',
        '\\\\x[0-9a-fA-F]{2}\\\\x[0-9a-fA-F]{2}\\\\x[0-9a-fA-F]{2}' => 'Cadena hexadecimal (ofuscación)',
        
        // Manipulación de archivos sospechosa
        'file_put_contents\s*\(.*\$_(?:GET|POST|REQUEST)' => 'Escritura de archivo con input (uploader)',
        'move_uploaded_file.*\.(php|phtml|php5|pht)' => 'Upload de archivo PHP',
        'fwrite\s*\(.*\$_(?:GET|POST|REQUEST)' => 'fwrite con input del usuario',
        
        // Spam / SEO hack
        'viagra|cialis|pharm|casino\s*online' => 'Contenido spam inyectado (SEO hack)',
        '<iframe\s+src\s*=\s*[\'"]https?://(?!youtube|vimeo|google)' => 'Iframe externo sospechoso',
        
        // Cryptominers
        'coinhive|cryptonight|monero.*miner|stratum\+tcp' => 'Cryptominer detectado',
    ];

    /**
     * Extensiones de archivo a escanear
     */
    private $scanExtensions = ['php', 'phtml', 'php5', 'pht', 'phps', 'inc', 'html', 'htm', 'js', 'htaccess'];

    /**
     * Carpetas a excluir del escaneo
     */
    private $excludeDirs = ['vendor', 'node_modules', '.git', 'storage/framework/cache'];

    /**
     * Página principal del scanner
     */
    public function index()
    {
        if (!Auth::user()->can('superadmin') && !$this->isAdmin()) {
            abort(403);
        }
        return view('security.index');
    }

    /**
     * Ejecutar escaneo completo
     */
    public function scan(Request $request)
    {
        if (!Auth::user()->can('superadmin') && !$this->isAdmin()) {
            abort(403);
        }

        set_time_limit(300); // 5 minutos máximo

        $basePath = base_path();
        $results = [
            'malware' => [],
            'suspicious_files' => [],
            'permission_issues' => [],
            'recently_modified' => [],
            'unknown_php_files' => [],
            'summary' => [],
        ];

        // 1. Escaneo de malware en archivos
        $results['malware'] = $this->scanForMalware($basePath);

        // 2. Archivos con permisos peligrosos
        $results['permission_issues'] = $this->checkPermissions($basePath);

        // 3. Archivos PHP modificados recientemente (últimos 7 días)
        $results['recently_modified'] = $this->getRecentlyModified($basePath, 7);

        // 4. Archivos PHP fuera de lugar (en public/, uploads/, storage/)
        $results['suspicious_files'] = $this->findSuspiciousFiles($basePath);

        // 5. Verificar integridad de archivos críticos
        $results['integrity'] = $this->checkCriticalFiles($basePath);

        // 6. Verificar configuración de seguridad
        $results['config_issues'] = $this->checkSecurityConfig();

        // Resumen
        $totalIssues = count($results['malware']) +
            count($results['suspicious_files']) +
            count($results['permission_issues']) +
            count($results['integrity']);

        $results['summary'] = [
            'total_issues' => $totalIssues,
            'malware_found' => count($results['malware']),
            'suspicious_files' => count($results['suspicious_files']),
            'permission_issues' => count($results['permission_issues']),
            'recently_modified' => count($results['recently_modified']),
            'integrity_issues' => count($results['integrity']),
            'config_issues' => count($results['config_issues']),
            'scan_date' => now()->format('Y-m-d H:i:s'),
            'status' => $totalIssues === 0 ? 'clean' : 'issues_found',
        ];

        // Guardar log del escaneo
        Log::channel('security')->info('Security scan completed', $results['summary']);

        if ($request->ajax()) {
            return response()->json($results);
        }

        return view('security.results', compact('results'));
    }

    /**
     * Escanear archivos en busca de patrones de malware
     */
    private function scanForMalware($basePath)
    {
        $findings = [];
        $files = $this->getFilesToScan($basePath);

        foreach ($files as $file) {
            try {
                $content = file_get_contents($file);
                if ($content === false) continue;

                foreach ($this->malwarePatterns as $pattern => $description) {
                    if (preg_match('/' . $pattern . '/i', $content, $matches)) {
                        $lineNumber = $this->getLineNumber($content, $matches[0]);
                        $findings[] = [
                            'file' => str_replace($basePath . DIRECTORY_SEPARATOR, '', $file),
                            'threat' => $description,
                            'pattern' => $pattern,
                            'line' => $lineNumber,
                            'snippet' => $this->getSnippet($content, $matches[0]),
                            'severity' => $this->getSeverity($pattern),
                        ];
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $findings;
    }

    /**
     * Verificar permisos peligrosos
     */
    private function checkPermissions($basePath)
    {
        $issues = [];

        // Solo en Linux
        if (PHP_OS_FAMILY === 'Windows') {
            return $issues;
        }

        $criticalDirs = [
            'storage' => '775',
            'bootstrap/cache' => '775',
            'public' => '755',
            '.env' => '640',
        ];

        foreach ($criticalDirs as $path => $expectedPerms) {
            $fullPath = $basePath . '/' . $path;
            if (file_exists($fullPath)) {
                $perms = substr(sprintf('%o', fileperms($fullPath)), -3);
                if ($perms === '777') {
                    $issues[] = [
                        'file' => $path,
                        'current_perms' => $perms,
                        'recommended_perms' => $expectedPerms,
                        'severity' => 'high',
                        'message' => "Permisos 777 son peligrosos. Cambiar a $expectedPerms",
                    ];
                }
            }
        }

        // Verificar que .env no sea accesible públicamente
        $envPath = $basePath . '/.env';
        if (file_exists($envPath) && is_readable($basePath . '/public/.env')) {
            $issues[] = [
                'file' => '.env',
                'severity' => 'critical',
                'message' => 'El archivo .env es accesible desde la web. ¡URGENTE!',
            ];
        }

        return $issues;
    }

    /**
     * Archivos PHP modificados recientemente
     */
    private function getRecentlyModified($basePath, $days = 7)
    {
        $modified = [];
        $since = now()->subDays($days)->timestamp;
        $files = $this->getFilesToScan($basePath);

        foreach ($files as $file) {
            if (filemtime($file) > $since) {
                $relativePath = str_replace($basePath . DIRECTORY_SEPARATOR, '', $file);
                // Excluir archivos en storage/framework (vistas compiladas)
                if (strpos($relativePath, 'storage' . DIRECTORY_SEPARATOR . 'framework') === 0) continue;
                
                $modified[] = [
                    'file' => $relativePath,
                    'modified' => date('Y-m-d H:i:s', filemtime($file)),
                    'size' => $this->formatBytes(filesize($file)),
                ];
            }
        }

        // Ordenar por fecha de modificación (más reciente primero)
        usort($modified, function ($a, $b) {
            return strtotime($b['modified']) - strtotime($a['modified']);
        });

        return array_slice($modified, 0, 100); // Máximo 100
    }

    /**
     * Buscar archivos PHP sospechosos en carpetas donde no deberían estar
     */
    private function findSuspiciousFiles($basePath)
    {
        $suspicious = [];
        $dangerousDirs = [
            'public/uploads',
            'storage/app/public',
            'public/img',
        ];

        foreach ($dangerousDirs as $dir) {
            $fullDir = $basePath . '/' . $dir;
            if (!is_dir($fullDir)) continue;

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($fullDir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, ['php', 'phtml', 'php5', 'pht', 'phps'])) {
                    $suspicious[] = [
                        'file' => str_replace($basePath . DIRECTORY_SEPARATOR, '', $file->getPathname()),
                        'severity' => 'high',
                        'message' => "Archivo PHP encontrado en carpeta de uploads/media - posible web shell",
                        'size' => $this->formatBytes($file->getSize()),
                        'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                    ];
                }
            }
        }

        return $suspicious;
    }

    /**
     * Verificar integridad de archivos críticos
     */
    private function checkCriticalFiles($basePath)
    {
        $issues = [];

        // Verificar que index.php no fue modificado
        $indexContent = file_get_contents($basePath . '/public/index.php');
        if ($indexContent && preg_match('/eval|base64_decode|gzinflate/', $indexContent)) {
            $issues[] = [
                'file' => 'public/index.php',
                'severity' => 'critical',
                'message' => 'public/index.php contiene código sospechoso. Puede estar infectado.',
            ];
        }

        // Verificar .htaccess
        $htaccess = $basePath . '/public/.htaccess';
        if (file_exists($htaccess)) {
            $htContent = file_get_contents($htaccess);
            if (preg_match('/RewriteRule.*https?:\/\/(?!%{HTTP_HOST})/', $htContent)) {
                $issues[] = [
                    'file' => 'public/.htaccess',
                    'severity' => 'high',
                    'message' => '.htaccess contiene redirecciones a sitios externos.',
                ];
            }
        }

        // Verificar web.php y api.php no tienen rutas inyectadas
        $routeFiles = ['routes/web.php', 'routes/api.php'];
        foreach ($routeFiles as $routeFile) {
            $path = $basePath . '/' . $routeFile;
            if (file_exists($path)) {
                $content = file_get_contents($path);
                if (preg_match('/eval\s*\(|base64_decode|shell_exec|passthru/', $content)) {
                    $issues[] = [
                        'file' => $routeFile,
                        'severity' => 'critical',
                        'message' => "Código peligroso detectado en archivo de rutas.",
                    ];
                }
            }
        }

        return $issues;
    }

    /**
     * Verificar configuración de seguridad
     */
    private function checkSecurityConfig()
    {
        $issues = [];

        // APP_DEBUG en producción
        if (config('app.env') === 'production' && config('app.debug')) {
            $issues[] = [
                'type' => 'config',
                'severity' => 'high',
                'message' => 'APP_DEBUG está activado en producción. Desactívalo en .env',
            ];
        }

        // APP_KEY vacía
        if (empty(config('app.key'))) {
            $issues[] = [
                'type' => 'config',
                'severity' => 'critical',
                'message' => 'APP_KEY no está configurada. Ejecutar: php artisan key:generate',
            ];
        }

        // Session driver inseguro
        if (config('session.driver') === 'array') {
            $issues[] = [
                'type' => 'config',
                'severity' => 'medium',
                'message' => 'Session driver es "array" (no persiste). Usar "file" o "database".',
            ];
        }

        // Verificar HTTPS
        if (config('app.env') === 'production' && !request()->secure()) {
            $issues[] = [
                'type' => 'config',
                'severity' => 'high',
                'message' => 'El sitio no está usando HTTPS en producción.',
            ];
        }

        return $issues;
    }

    /**
     * Cuarentena: mover archivo sospechoso a carpeta segura
     */
    public function quarantine(Request $request)
    {
        if (!Auth::user()->can('superadmin') && !$this->isAdmin()) {
            abort(403);
        }

        $filePath = $request->input('file');
        $fullPath = base_path($filePath);

        if (!file_exists($fullPath)) {
            return response()->json(['success' => false, 'message' => 'Archivo no encontrado']);
        }

        // Crear carpeta de cuarentena
        $quarantineDir = storage_path('security/quarantine/' . date('Y-m-d'));
        if (!is_dir($quarantineDir)) {
            mkdir($quarantineDir, 0755, true);
        }

        $newName = basename($filePath) . '.quarantined.' . time();
        $quarantinePath = $quarantineDir . '/' . $newName;

        // Mover y registrar
        if (rename($fullPath, $quarantinePath)) {
            Log::channel('security')->warning('File quarantined', [
                'original' => $filePath,
                'quarantine' => $quarantinePath,
                'user' => Auth::user()->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Archivo movido a cuarentena: $newName",
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No se pudo mover el archivo']);
    }

    /**
     * Restaurar archivo de cuarentena
     */
    public function restore(Request $request)
    {
        if (!Auth::user()->can('superadmin') && !$this->isAdmin()) {
            abort(403);
        }

        $quarantineFile = $request->input('quarantine_file');
        $originalPath = $request->input('original_path');

        $quarantinePath = storage_path('security/quarantine/' . $quarantineFile);

        if (!file_exists($quarantinePath)) {
            return response()->json(['success' => false, 'message' => 'Archivo de cuarentena no encontrado']);
        }

        $restorePath = base_path($originalPath);

        if (rename($quarantinePath, $restorePath)) {
            Log::channel('security')->info('File restored from quarantine', [
                'file' => $originalPath,
                'user' => Auth::user()->id,
            ]);

            return response()->json(['success' => true, 'message' => 'Archivo restaurado']);
        }

        return response()->json(['success' => false, 'message' => 'No se pudo restaurar']);
    }

    // ==================== HELPERS ====================

    private function getFilesToScan($basePath)
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($basePath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            // Excluir directorios
            $relativePath = str_replace($basePath . DIRECTORY_SEPARATOR, '', $file->getPathname());
            foreach ($this->excludeDirs as $excludeDir) {
                if (strpos($relativePath, $excludeDir) === 0) continue 2;
            }

            if ($file->isFile()) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, $this->scanExtensions)) {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }

    private function getLineNumber($content, $match)
    {
        $pos = strpos($content, $match);
        if ($pos === false) return 0;
        return substr_count(substr($content, 0, $pos), "\n") + 1;
    }

    private function getSnippet($content, $match)
    {
        $pos = strpos($content, $match);
        $start = max(0, $pos - 30);
        $length = min(strlen($content) - $start, 100);
        return '...' . htmlspecialchars(substr($content, $start, $length)) . '...';
    }

    private function getSeverity($pattern)
    {
        $criticalPatterns = ['eval.*base64', 'c99shell', 'r57shell', 'backdoor', 'shell_exec.*\$_', 'system.*\$_'];
        foreach ($criticalPatterns as $cp) {
            if (strpos($pattern, $cp) !== false) return 'critical';
        }
        return 'high';
    }

    private function formatBytes($bytes)
    {
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' bytes';
    }

    private function isAdmin()
    {
        return auth()->user()->id === 1 || auth()->user()->user_type === 'admin';
    }
}
