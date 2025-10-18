<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable implements FilamentUser, HasAvatar
{

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->hasAnyRole(['super_admin', 'admin']),
            'crew'  => $this->hasAnyRole(['staff_crew', 'manager_crew']),
            'document'  => $this->hasAnyRole(['staff_document', 'manager_document', 'operation']),
            default => false,
        };
        //  $user = Auth::user();
        //  $roles = $user->getRoleNames();
        //  if ($panel->getId() == 'admin' && $roles->contains('super_admin')) {
        //      return true;
        //  } else if ($panel->getId() == 'document' && $roles->contains('staff_document')) {
        //      return true;
        //  } else if ($panel->getId() == 'crew' && $roles->contains('staff_crew')) {
        //      return true;
        //  } else {
        //      return false;
        //  }
    }
    use HasFactory, Notifiable, LogsActivity, HasRoles;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'email',
                'avatar_url',
            ])
            ->logOnlyDirty()
            ->useLogName('User')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "User " . $this->name ?? 'unknown' . " {$eventName}");
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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

    public function getFilamentAvatarUrl(): ?string
    {
        $avatarColumn = config('filament-edit-profile.avatar_column', 'avatar_url');
        return $this->$avatarColumn ? Storage::url($this->$avatarColumn) : null;
    }

    public function document()
    {
        return $this->hasMany(Document::class, 'created_by');
    }

    protected static function booted()
    {
        static::saved(function ($user) {
            $original = $user->getOriginal('avatar_url');
            if ($original && $original !== $user->avatar_url) {
                $path = preg_replace('#^public/#', '', $original);
                Log::info('Deleting old avatar: ' . $path);
                Storage::disk('public')->delete($path);
            }
        });
    }
}
