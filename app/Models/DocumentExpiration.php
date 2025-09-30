<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DocumentExpiration extends Model
{
    protected $fillable = [
        'document_id',
        'tanggal_terbit',
        'tanggal_expired',
        'file_path',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    protected static function booted()
    {
        static::created(function ($exp) {
            activity()
                ->performedOn($exp->document)
                ->event('created')
                ->causedBy(auth()->user() ?? null)
                ->withProperties([
                    'relation' => 'DocumentExpiration',
                    'action'   => 'created',
                    'attributes' => $exp->getAttributes(),
                ])
                ->log('Related model created');
        });

        static::updated(function ($exp) {
            activity()
                ->performedOn($exp->document)
                ->event('updated')
                ->causedBy(auth()->user() ?? null)
                ->withProperties([
                    'relation' => 'DocumentExpiration',
                    'action'   => 'updated',
                    'changes'  => $exp->getChanges(),
                ])
                ->log('Related model updated');
        });

        static::deleted(function ($exp) {
            if ($exp->file_path && \Storage::disk('public')->exists($exp->file_path)) {
                \Storage::disk('public')->delete($exp->file_path);
            }
            activity()
                ->performedOn($exp->document)
                ->event('deleted')
                ->causedBy(auth()->user() ?? null)
                ->withProperties([
                    'relation' => 'DocumentExpiration',
                    'action'   => 'deleted',
                ])
                ->log('Related model deleted');
        });
    }
}
