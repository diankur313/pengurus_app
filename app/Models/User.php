<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Filament\Models\Contracts\HasName;
use Filament\Models\Contracts\HasAvatar;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements HasName, HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    public function getFilamentName(): string
    {
        return $this->name ?? $this->email;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->photo) {
            // Cek di penyimpanan lokal app2 (Private)
            $localPath = storage_path('app/private/profile_pictures/' . $this->photo);
            if (file_exists($localPath)) {
                return '/profile-picture/' . $this->photo;
            }

            // Fallback ke penyimpanan awalan (eksternal)
            $fallbackPath = '/www/wwwroot/ppab.yiscalazhar.web.id/frontend/storage/app/private/profil_picture/' . $this->photo;
            if (file_exists($fallbackPath)) {
                return '/profile-picture/' . $this->photo;
            }
        }
        return null;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'whatsapp',
        'goldar',
        'photo',
        'gender',
        'angkatan',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
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
