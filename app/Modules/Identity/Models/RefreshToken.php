<?php

namespace App\Modules\Identity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefreshToken extends Model
{
    use HasUuids;

    protected $table = 'identity_refresh_tokens';
    protected $primaryKey = 'token_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'token_id',
        'user_id',
        'token_hash',
        'expires_at',
        'revoked',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
            'revoked' => 'boolean',
        ];
    }

    public function uniqueIds(): array
    {
        return ['token_id'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
