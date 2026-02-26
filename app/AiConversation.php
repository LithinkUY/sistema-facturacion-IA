<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AiConversation extends Model
{
    protected $table = 'ai_conversations';

    protected $guarded = ['id'];

    protected $casts = [
        'context_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    /**
     * Obtener historial de conversación por sesión
     */
    public static function getSessionHistory($sessionId, $limit = 20)
    {
        return self::where('session_id', $sessionId)
            ->whereIn('role', ['user', 'model'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }
}
