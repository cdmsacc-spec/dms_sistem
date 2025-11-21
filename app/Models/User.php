<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Kirschbaum\Commentions\Contracts\Commenter;
use Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable implements FilamentUser, HasAvatar, Commenter
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, AuthenticationLoggable, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'auth_token'
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->hasAnyRole(['super_admin', 'admin']),
            'crew'  => $this->hasAnyRole(['staff_crew', 'manager_crew']),
            'document'  => $this->hasAnyRole(['staff_dokumen', 'manager_dokumen', 'operation_dokumen']),
            default => false,
        };
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'email',
                'avatar',
            ])
            ->logOnlyDirty()
            ->useLogName('User')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "User " . $this->name ?? 'unknown' . " {$eventName}");
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if (! $this->avatar) {
            return null; 
        }
        return asset('storage/' . $this->avatar);
    }

    protected static function booted()
    {
        static::saved(function ($user) {
            $original = $user->getOriginal('avatar');
            if ($original && $original !== $user->avatar) {
                $path = preg_replace('#^public/#', '', $original);
                Storage::disk('public')->delete($path);
            }
        });
    }



    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
