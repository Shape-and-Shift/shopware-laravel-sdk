<?php declare(strict_types=1);

namespace Sas\ShopwareLaravelSdk\Models;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable;

/**
 * @property string $shop_id
 * @property string $shop_url
 * @property string $shop_secret
 * @property string $api_key
 * @property string $secret_key
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

    protected $primaryKey = 'shop_id';

    protected $keyType = 'string';

    protected $guarded = [];

    public function getAccessTokenAttribute($value)
    {
        return $value ? unserialize($value) : null;
    }

    public function setAccessTokenAttribute($value): void
    {
        $this->attributes['access_token'] = $value ? serialize($value) : null;
    }
}
