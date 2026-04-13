<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToReminderTemplateItem extends Model
{
    protected $fillable = [
        'id_reminder_template',
        'nama',
        'send_to',
        'type',
    ];
 
    public function template(): BelongsTo
    {
        return $this->belongsTo(ReminderTemplate::class, 'id_reminder_template');
    }
}
