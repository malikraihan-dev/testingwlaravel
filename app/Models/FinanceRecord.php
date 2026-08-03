<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'category',
        'amount',
        'description',
        'date',
        'attachment_path',
        'attachment_original_name',
        'status',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function canBeEditedBy(User $user): bool
    {
        return $this->user_id === $user->id && in_array($this->status, ['draft', 'rejected']);
    }

    public function canBeFinalizedBy(User $user): bool
    {
        return $this->user_id === $user->id && in_array($this->status, ['draft', 'rejected']) && $this->attachment_path;
    }
}
