<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'face_descriptor',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'face_descriptor' => 'array',
        ];
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasFaceEnrolled(): bool
    {
        return ! empty($this->face_descriptor);
    }
}
