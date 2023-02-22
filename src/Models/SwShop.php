<?php declare(strict_types=1);

namespace Sas\ShopwareLaravelSdk\Models;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Vin\ShopwareSdk\Data\AccessToken;

/**
 * @property string $app_id
 * @property string $shop_id
 * @property string $shop_url
 * @property string $shop_name
 * @property string $shop_secret
 * @property string $api_key
 * @property string $secret_key
 * @property ?AccessToken $access_token
 */
class SwShop extends Model implements AuthenticatableContract
{
    use Authenticatable;

    public $incrementing = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sw_shops';

    protected $primaryKey = 'app_id';

    protected $keyType = 'string';

    protected $guarded = [];

    public function getAccessTokenAttribute(?string $value): ?AccessToken
    {
        return $value ? unserialize($value) : null;
    }

    public function setAccessTokenAttribute(?AccessToken $value): void
    {
        $this->attributes['access_token'] = $value ? serialize($value) : null;
    }

    public function getAuthIdentifierName(): string
    {
        return 'secret_key';
    }

    public function getAuthIdentifier(): string
    {
        return $this->secret_key;
    }

    public function getAuthPassword(): string
    {
        return '';
    }

    public function getRememberToken(): string
    {
        return '';
    }

    public function setRememberToken($value)
    {
        // TODO: Implement setRememberToken() method.
    }

    public function getRememberTokenName(): string
    {
        return '';
    }
}
