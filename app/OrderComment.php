<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderComment extends Model
{
    protected $table = 'order_comments';

    protected $guarded = ['id'];

    public function commentable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getIsSystemAttribute()
    {
        return $this->type === 'system';
    }
}
