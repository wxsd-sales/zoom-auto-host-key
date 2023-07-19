<?php

namespace App\Models;

use App\Traits\CamelCaseJsonSerialize;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Activation
 *
 * @property string $id
 * @property string $wbx_wi_org_id
 * @property string $wbx_wi_manifest_id
 * @property int $wbx_wi_manifest_version
 * @property string $wbx_wi_manifest_url
 * @property string $wbx_wi_app_url
 * @property string $wbx_wi_display_name
 * @property string $wbx_wi_client_id
 * @property mixed $wbx_wi_client_secret
 * @property string $zm_s2s_account_id
 * @property string $zm_s2s_client_id
 * @property mixed $zm_s2s_client_secret
 * @property array|null $zm_host_accounts
 * @property string $hmac_secret
 * @property string|null $wbx_wi_oauth_id
 * @property string|null $zm_s2s_oauth_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WbxWiAction> $wbxWiActions
 * @property-read int|null $wbx_wi_actions_count
 * @property-read \App\Models\WbxWiOauth|null $wbxWiOauth
 * @property-read \App\Models\ZmS2sOauth|null $zmS2sOauth
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Activation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Activation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Activation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Activation query()
 * @method static \Illuminate\Database\Eloquent\Builder|Activation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activation whereHmacSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activation whereWbxWiAppUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activation whereWbxWiClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activation whereWbxWiClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activation whereWbxWiDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activation whereWbxWiManifestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activation whereWbxWiManifestUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activation whereWbxWiManifestVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activation whereWbxWiOauthId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activation whereWbxWiOrgId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activation whereZmHostAccounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activation whereZmS2sAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activation whereZmS2sClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activation whereZmS2sClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activation whereZmS2sOauthId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activation withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Activation withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Activation extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;
    use CamelCaseJsonSerialize;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'wbx_wi_org_id',
        'wbx_wi_manifest_id',
        'wbx_wi_manifest_version',
        'wbx_wi_manifest_url',
        'wbx_wi_app_url',
        'wbx_wi_display_name',
        'wbx_wi_client_id',
        'wbx_wi_client_secret',
        'zm_s2s_account_id',
        'zm_s2s_client_id',
        'zm_s2s_client_secret',
        'zm_host_accounts',
        'wbx_wi_oauth_id',
        'zm_s2s_oauth_id',
        'hmac_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'wbx_wi_client_secret' => 'encrypted',
        'zm_s2s_client_secret' => 'encrypted',
        'zm_host_accounts' => 'array',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [
        'wbx_wi_client_secret',
        'zm_s2s_client_secret',
        'hmac_secret',
    ];

    /**
     * Get the Webex WI actions for the activation.
     */
    public function wbxWiActions(): HasMany
    {
        return $this->hasMany(WbxWiAction::class);
    }

    /**
     * Get the Webex WI Oauth associated with the activation.
     */
    public function wbxWiOauth(): HasOne
    {
        return $this->hasOne(WbxWiOauth::class);
    }

    /**
     * Get the Zoom S2S Oauth associated with the activation.
     */
    public function zmS2sOauth(): HasOne
    {
        return $this->hasOne(ZmS2sOauth::class);
    }
}
