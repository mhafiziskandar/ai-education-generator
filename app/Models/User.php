<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'grade_level',
        'student_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function generatedContents(): HasMany
    {
        return $this->hasMany(GeneratedContent::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->hasRole('admin'),
            'educator' => $this->hasRole('educator'),
            'student' => $this->hasRole('student'),
            default => false,
        };
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEducator(): bool
    {
        return $this->role === 'educator';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }
}