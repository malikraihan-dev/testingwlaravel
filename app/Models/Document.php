<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'owner_id',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function versions()
    {
        return $this->hasMany(DocumentVersion::class)->orderByDesc('version_number');
    }

    public function latestVersion()
    {
        return $this->hasOne(DocumentVersion::class)->orderByDesc('version_number');
    }

    public function collaborators()
    {
        return $this->belongsToMany(User::class, 'document_collaborators')->withTimestamps();
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    public function isAccessibleBy(User $user): bool
    {
        if ($this->isOwnedBy($user)) {
            return true;
        }

        return $this->collaborators()->where('user_id', $user->id)->exists();
    }
}
