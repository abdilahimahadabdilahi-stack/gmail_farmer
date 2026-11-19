<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'user_id',
        'proof_url',
        'account_email',
        'account_code',
        'reward_amount',
        'status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return ['reward_amount' => 'decimal:2'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
