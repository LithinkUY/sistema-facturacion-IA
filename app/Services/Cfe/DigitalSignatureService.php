<?php

namespace App\Services\Cfe;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de firma digital para CFEs
 */
class DigitalSignatureService
{
    public function signCFE(string $xml, array $config): array
    {
        try {
            $certificate = $this->loadCertificate($config['certificate'], $config['password']);
            $normalizedXml = $this->normalizeXML($xml);
            $hash = $this->calculateHash($normalizedXml, 'SHA-256');
            $signature = $this->signHash($hash, $certificate['privateKey']);
            $signedXML = $this->embedSignature($normalizedXml, $signature);
            $certificateInfo = $this->extractCertificateInfo($certificate['certificate']);

            Log::info('CFE firmado digitalmente');

            return [
                'originalXML' => $xml,
                'signedXML' => $signedXML,
                'signature' => base64_encode($signature),
                'certificateInfo' => $certificateInfo,
            ];
        } catch (Exception $exception) {
            throw new Exception('Error al firmar CFE: ' . $exception->getMessage());
        }
    }

    private function loadCertificate(string $certificateData, string $password): array
    {
        if (!openssl_pkcs12_read($certificateData, $certs, $password)) {
            throw new Exception('No se pudo leer el certificado PKCS#12');
        }

        $privateKeyPem = $certs['pkey'] ?? null;
        if (!$privateKeyPem) {
            throw new Exception('No se encontró la clave privada en el certificado');
        }

        $privateKeyResource = openssl_get_privatekey($privateKeyPem);
        $publicCert = $certs['cert'] ?? null;

        if (!$privateKeyResource || !$publicCert) {
            throw new Exception('No se pudo cargar la clave privada o cert público');
        }

        return [
            'privateKey' => $privateKeyResource,
            'certificate' => $publicCert,
        ];
    }

    private function normalizeXML(string $xml): string
    {
        $dom = new \DOMDocument();
        @$dom->loadXML($xml);
        $dom->normalizeDocument();

        return $dom->saveXML();
    }

    private function calculateHash(string $xml, string $algorithm): string
    {
        $algo = strtolower(str_replace('-', '', $algorithm));

        return hash($algo, $xml, true);
    }

    private function signHash(string $hash, $privateKey): string
    {
        $signature = '';
        if (!openssl_sign($hash, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new Exception('Error firmando hash');
        }

        return $signature;
    }

    private function embedSignature(string $xml, string $signature): string
    {
        $dom = new \DOMDocument();
        @$dom->loadXML($xml);

        $signatureElement = $dom->createElement('Firma');
        $signatureElement->setAttribute('algoritmo', 'SHA256withRSA');
        $signatureElement->setAttribute('encoding', 'base64');
        $signatureElement->nodeValue = base64_encode($signature);

        $cfElement = $dom->getElementsByTagName('CFE')->item(0);
        if ($cfElement) {
            $cfElement->appendChild($signatureElement);
        }

        return $dom->saveXML();
    }

    private function extractCertificateInfo(string $certificate): array
    {
        $certData = openssl_x509_parse($certificate);

        if (!$certData) {
            return [];
        }

        return [
            'issuer' => $certData['issuer']['CN'] ?? 'Unknown',
            'subject' => $certData['subject']['CN'] ?? 'Unknown',
            'serialNumber' => $certData['serialNumber'] ?? '',
            'validFrom' => date('c', $certData['validFrom_time_t'] ?? time()),
            'validTo' => date('c', $certData['validTo_time_t'] ?? time()),
            'fingerprint' => openssl_x509_fingerprint($certificate, 'sha256'),
        ];
    }

    public function verifyCFESignature(string $signedXml, string $certificate): bool
    {
        $dom = new \DOMDocument();
        @$dom->loadXML($signedXml);
        $signatureElements = $dom->getElementsByTagName('Firma');

        if ($signatureElements->length === 0) {
            throw new Exception('No se encontró firma en el XML');
        }

        $signatureElement = $signatureElements->item(0);
        $signatureValue = base64_decode($signatureElement->nodeValue);
        $signatureElement->parentNode->removeChild($signatureElement);
        $xmlWithoutSignature = $dom->saveXML();
        $hash = $this->calculateHash($xmlWithoutSignature, 'SHA-256');

        $publicKey = openssl_pkey_get_public($certificate);
        $result = openssl_verify($hash, $signatureValue, $publicKey, OPENSSL_ALGO_SHA256);

        return $result === 1;
    }
}
