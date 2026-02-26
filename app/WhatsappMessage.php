<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WhatsappMessage extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_ai_response' => 'boolean',
    ];

    // ===== Relaciones =====

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    // ===== Scopes =====

    public function scopeIncoming($query)
    {
        return $query->where('direction', 'incoming');
    }

    public function scopeOutgoing($query)
    {
        return $query->where('direction', 'outgoing');
    }

    public function scopeForPhone($query, $phone)
    {
        return $query->where('phone_number', $phone);
    }

    // ===== Helpers =====

    /**
     * Obtener conversaciones agrupadas por número de teléfono
     */
    public static function getConversations($businessId, $limit = 30)
    {
        return self::where('business_id', $businessId)
            ->select('phone_number', 'contact_name')
            ->selectRaw('MAX(created_at) as last_message_at')
            ->selectRaw('COUNT(*) as total_messages')
            ->selectRaw("SUM(CASE WHEN direction = 'incoming' AND status = 'received' THEN 1 ELSE 0 END) as unread_count")
            ->selectRaw('SUBSTRING_INDEX(GROUP_CONCAT(message ORDER BY created_at DESC SEPARATOR "|||"), "|||", 1) as last_message')
            ->groupBy('phone_number', 'contact_name')
            ->orderByDesc('last_message_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtener historial de chat con un número
     */
    public static function getChatHistory($businessId, $phone, $limit = 50)
    {
        return self::where('business_id', $businessId)
            ->where('phone_number', $phone)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtener historial reciente para contexto IA
     */
    public static function getRecentContext($businessId, $phone, $limit = 10)
    {
        return self::where('business_id', $businessId)
            ->where('phone_number', $phone)
            ->where('message_type', 'text')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    /**
     * Formato para mostrar el número
     */
    public function getFormattedPhoneAttribute()
    {
        $phone = $this->phone_number;
        if (str_starts_with($phone, '598')) {
            return '+' . substr($phone, 0, 3) . ' ' . substr($phone, 3, 2) . ' ' . substr($phone, 5, 3) . ' ' . substr($phone, 8);
        }
        return '+' . $phone;
    }

    /**
     * Vincular con contacto del sistema
     */
    public function linkToContact()
    {
        if ($this->contact_id) return;

        $phone = $this->phone_number;
        // Buscar en contactos por mobile o alternate_number
        $contact = Contact::where('business_id', $this->business_id)
            ->where(function ($q) use ($phone) {
                $q->where('mobile', 'LIKE', '%' . substr($phone, -8) . '%')
                  ->orWhere('alternate_number', 'LIKE', '%' . substr($phone, -8) . '%');
            })
            ->first();

        if ($contact) {
            $this->update(['contact_id' => $contact->id]);
        }
    }
}
