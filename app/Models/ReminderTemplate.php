<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReminderTemplate extends Model
{
    protected $fillable = [
        'nama_template',
        'id_author',
    ];
 
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_author');
    }
 
    /** Jadwal (hari & jam) yang tersimpan di template */
    public function reminderItems(): HasMany
    {
        return $this->hasMany(ReminderTemplateItem::class, 'id_reminder_template');
    }
 
    /** Penerima yang tersimpan di template */
    public function toReminderItems(): HasMany
    {
        return $this->hasMany(ToReminderTemplateItem::class, 'id_reminder_template');
    }
}
