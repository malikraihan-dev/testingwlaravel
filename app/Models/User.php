<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'face_descriptor',
        'face_photo_path',
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

    public function ownedDocuments()
    {
        return $this->hasMany(Document::class, 'owner_id');
    }

    public function collaboratingDocuments()
    {
        return $this->belongsToMany(Document::class, 'document_collaborators')->withTimestamps();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasFaceEnrolled(): bool
    {
        return ! empty($this->face_descriptor);
    }

    public function getFacePhotoUrlAttribute(): ?string
    {
        return $this->face_photo_path ? Storage::disk('public')->url($this->face_photo_path) : null;
    }
}
