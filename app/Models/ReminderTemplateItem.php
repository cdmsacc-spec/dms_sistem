<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderTemplateItem extends Model
{
    protected $fillable = [
        'id_reminder_template',
        'reminder_hari',
        'reminder_jam',
    ];
 
    public function template(): BelongsTo
    {
        return $this->belongsTo(ReminderTemplate::class, 'id_reminder_template');
    }
}
