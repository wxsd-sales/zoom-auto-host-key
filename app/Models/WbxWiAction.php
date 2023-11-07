<?php

namespace App\Models;

use App\Traits\JsonSerialize;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\WbxWiAction
 *
 * @property string $id
 * @property string $activation_id
 * @property string $sub
 * @property \Illuminate\Support\Carbon $iat
 * @property string $jti
 * @property string $action
 * @property string $jwt
 * @property array $jwt_payload
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Activation $activation
 *
 * @method static \Illuminate\Database\Eloquent\Builder|WbxWiAction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WbxWiAction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WbxWiAction onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|WbxWiAction query()
 * @method static \Illuminate\Database\Eloquent\Builder|WbxWiAction whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WbxWiAction whereActivationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WbxWiAction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WbxWiAction whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WbxWiAction whereIat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WbxWiAction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WbxWiAction whereJti($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WbxWiAction whereJwt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WbxWiAction whereJwtPayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WbxWiAction whereSub($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WbxWiAction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WbxWiAction withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|WbxWiAction withoutTrashed()
 *
 * @mixin \Eloquent
 */
class WbxWiAction extends Model
{
    use HasFactory;
    use HasUuids;
    use JsonSerialize;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'activation_id',
        'sub',
        'iat',
        'jti',
        'action',
        'jwt',
        'jwt_payload',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'iat' => 'datetime',
        'jwt_payload' => 'array',
    ];

    /**
     * Get the activation that owns the action.
     */
    public function activation(): BelongsTo
    {
        return $this->belongsTo(Activation::class);
    }
}
