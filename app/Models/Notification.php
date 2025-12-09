<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    public $incrementing = false; // karena primary key uuid
    protected $keyType = 'string';

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    protected $fillable = [
        'id',
        'type',
        'notifiable',
        'data',
        'read_at',
    ];
}
