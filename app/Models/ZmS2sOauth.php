<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ZmS2sOauth
 *
 * @property string $id
 * @property string $activation_id
 * @property string $account_id
 * @property mixed|null $refresh_token
 * @property mixed $access_token
 * @property \Illuminate\Support\Carbon $expires_at
 * @property string $token_type
 * @property string $scope
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Activation $activation
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ZmS2sOauth newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ZmS2sOauth newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ZmS2sOauth query()
 * @method static \Illuminate\Database\Eloquent\Builder|ZmS2sOauth whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZmS2sOauth whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZmS2sOauth whereActivationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZmS2sOauth whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZmS2sOauth whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZmS2sOauth whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZmS2sOauth whereRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZmS2sOauth whereScope($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZmS2sOauth whereTokenType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZmS2sOauth whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ZmS2sOauth extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'activation_id',
        'account_id',
        'refresh_token',
        'access_token',
        'expires_at',
        'token_type',
        'scope',
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
     * Get the activation that owns the oauth.
     */
    public function activation(): BelongsTo
    {
        return $this->belongsTo(Activation::class);
    }
}
