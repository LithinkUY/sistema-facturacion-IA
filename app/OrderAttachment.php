<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderAttachment extends Model
{
    protected $table = 'order_attachments';

    protected $guarded = ['id'];

    public function attachable()
    {
        return $this->morphTo();
    }

    public function uploadedByUser()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}
