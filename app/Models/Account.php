<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Account
 *
 * @property string $id
 * @property string $user_id
 * @property string $type
 * @property string $provider
 * @property string $provider_account_id
 * @property mixed|null $refresh_token
 * @property mixed|null $access_token
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property string|null $token_type
 * @property string|null $scope
 * @property string|null $id_token
 * @property string|null $session_state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Account newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Account newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Account query()
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereIdToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereProviderAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereScope($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereSessionState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereTokenType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Account extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'provider',
        'provider_account_id',
        'refresh_token',
        'access_token',
        'expires_at',
        'token_type',
        'scope',
        'id_token',
        'session_state',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'refresh_token' => 'encrypted',
        'access_token' => 'encrypted',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user that owns the account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
