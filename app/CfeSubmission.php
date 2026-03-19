<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para Comprobantes Fiscales Electrónicos (CFE)
 * Almacena el historial de envíos a DGI Uruguay
 */
class CfeSubmission extends Model
{
    use SoftDeletes;

    protected $table = 'cfe_submissions';

    protected $fillable = [
        'business_id',
        'location_id',
        'transaction_id',
        'contact_id',
        'user_id',
        'cfe_type',
        'series',
        'number',
        'issue_date',
        'due_date',
        'payment_method',
        'currency',
        'exchange_rate',
        'subtotal',
        'tax_amount',
        'total',
        'items',
        'status',
        'emitter_rut',
        'emitter_name',
        'emitter_address',
        'emitter_city',
        'emitter_department',
        'receiver_doc_type',
        'receiver_document',
        'receiver_name',
        'receiver_address',
        'receiver_city',
        'receiver_department',
        'xml_content',
        'signed_xml',
        'cae',
        'cae_due_date',
        'track_id',
        'dgi_response',
        'submitted_at',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'datetime',
        'due_date' => 'datetime',
        'cae_due_date' => 'date',
        'submitted_at' => 'datetime',
        'items' => 'array',
        'dgi_response' => 'array',
        'exchange_rate' => 'decimal:4',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected $dates = [
        'issue_date',
        'due_date',
        'cae_due_date',
        'submitted_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Estados posibles del CFE
    public const STATUS_PENDING = 'pending';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_ERROR = 'error';

    // Tipos de CFE según DGI
    public const CFE_TYPES = [
        101 => 'e-Ticket',
        102 => 'Nota de Crédito de e-Ticket',
        103 => 'Nota de Débito de e-Ticket',
        111 => 'e-Factura',
        112 => 'Nota de Crédito de e-Factura',
        113 => 'Nota de Débito de e-Factura',
        121 => 'e-Ticket Contingencia',
        131 => 'e-Factura Contingencia',
        141 => 'e-Ticket Venta por Cuenta Ajena',
        151 => 'e-Boleta de Entrada',
        181 => 'e-Remito',
        182 => 'e-Remito de Exportación',
        201 => 'e-Resguardo',
    ];

    // ============ RELACIONES ============

    /**
     * Negocio al que pertenece el CFE
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Sucursal donde se emitió
     */
    public function location()
    {
        return $this->belongsTo(BusinessLocation::class, 'location_id');
    }

    /**
     * Transacción de venta asociada
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Cliente/Receptor del CFE
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Usuario que creó el CFE
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ============ SCOPES ============

    /**
     * CFE pendientes de envío
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * CFE aceptados por DGI
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    /**
     * CFE rechazados
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * CFE con errores
     */
    public function scopeWithErrors($query)
    {
        return $query->whereIn('status', [self::STATUS_REJECTED, self::STATUS_ERROR]);
    }

    /**
     * Filtrar por tipo de CFE
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('cfe_type', $type);
    }

    /**
     * Filtrar por rango de fechas
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('issue_date', [$startDate, $endDate]);
    }

    // ============ ACCESSORS ============

    /**
     * Obtener nombre del tipo de CFE
     */
    public function getCfeTypeNameAttribute()
    {
        return self::CFE_TYPES[$this->cfe_type] ?? 'Desconocido';
    }

    /**
     * Número formateado (Serie-Número)
     */
    public function getFormattedNumberAttribute()
    {
        return $this->series . '-' . str_pad($this->number, 7, '0', STR_PAD_LEFT);
    }

    /**
     * Verificar si está aceptado
     */
    public function getIsAcceptedAttribute()
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    /**
     * Verificar si se puede reenviar
     */
    public function getCanResendAttribute()
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_REJECTED,
            self::STATUS_ERROR,
        ]);
    }

    // ============ MÉTODOS ============

    /**
     * Marcar como enviado
     */
    public function markAsSubmitted()
    {
        $this->status = self::STATUS_SUBMITTED;
        $this->submitted_at = now();
        $this->save();
    }

    /**
     * Marcar como aceptado
     */
    public function markAsAccepted($cae = null, $trackId = null, $response = null, $caeDueDate = null)
    {
        $this->status = self::STATUS_ACCEPTED;
        $this->cae = $cae;
        $this->cae_due_date = $caeDueDate;
        $this->track_id = $trackId;
        $this->dgi_response = $response;
        $this->save();
    }

    /**
     * Marcar como rechazado
     */
    public function markAsRejected($response = null)
    {
        $this->status = self::STATUS_REJECTED;
        $this->dgi_response = $response;
        $this->save();
    }

    /**
     * Marcar con error
     */
    public function markAsError($errorMessage)
    {
        $this->status = self::STATUS_ERROR;
        $this->dgi_response = ['error' => $errorMessage];
        $this->save();
    }

    /**
     * Generar datos para código QR de verificación DGI
     */
    public function generateQRData()
    {
        return implode('|', [
            $this->emitter_rut,
            $this->cfe_type,
            $this->series,
            $this->number,
            number_format($this->total, 2, '.', ''),
            $this->cae ?? '0',
            $this->issue_date->format('Ymd'),
        ]);
    }

    /**
     * Obtener URL de verificación DGI
     */
    public function getVerificationUrl()
    {
        $baseUrl = config('cfe.environment') === 'production'
            ? 'https://www.efactura.dgi.gub.uy/consultaQR/cfe?'
            : 'https://efactura.testing.dgi.gub.uy/consultaQR/cfe?';

        return $baseUrl . base64_encode($this->generateQRData());
    }
}
