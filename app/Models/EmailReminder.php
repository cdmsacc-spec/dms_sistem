<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailReminder extends Model
{
    protected $fillable = [
        'document_id',
        'nama',
        'email',
    ];

    public function dokument()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    protected static function booted()
    {
        static::creating(function ($reminder) {
            \Log::info('Creating Reminder:', $reminder->toArray());
        });

        static::saved(function ($reminder) {
            $document = $reminder->dokument;
            if ($document) {
                activity()
                    ->performedOn($document)
                    ->causedBy(auth()->user())
                    ->event('created')
                    ->withProperties([
                        'attributes' => $reminder->getAttributes(),
                    ])
                    ->log("Reminder ID {$reminder->id} for Document '{$document->kapal->nama_kapal}' has been created");
            }
        });

        static::deleted(function ($reminder) {
            $document = $reminder->dokument;
            if ($document) {
                activity()
                    ->performedOn($document)
                    ->event('deleted')
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'attributes' => $reminder->getAttributes(),
                    ])
                    ->log("Reminder ID {$reminder->id} for Document '{$document->kapal->nama_kapal}' has been deleted");
            }
        });
    }
}
