<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class DocumentReminder extends Model
{
    protected $fillable = [
        'document_id',
        'reminder_hari',
        'reminder_jam',
    ];
    protected static function booted()
    {
 static::creating(function ($reminder) {
        \Log::info('Creating Reminder:', $reminder->toArray());
    });
    
        static::saved(function ($reminder) {
            $document = $reminder->document;
            Log::info($document);
            if ($document) {
                activity()
                    ->performedOn($document)
                    ->causedBy(auth()->user())
                    ->event('created')
                    ->withProperties([
                        'attributes' => $reminder->getAttributes(),
                    ])
                    ->log("Reminder ID {$reminder->id} for Document '{$document->title}' has been created");
            }
        });

        static::deleted(function ($reminder) {
            $document = $reminder->document;
            if ($document) {
                activity()
                    ->performedOn($document)
                    ->event('deleted')
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'attributes' => $reminder->getAttributes(),
                    ])
                    ->log("Reminder ID {$reminder->id} for Document '{$document->title}' has been deleted");
            }
        });
    }

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }
}
