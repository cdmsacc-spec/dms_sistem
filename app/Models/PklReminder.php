<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PklReminder extends Model
{
    protected $fillable = [
        'reminder_hari',
        'reminder_jam',
    ];
}
