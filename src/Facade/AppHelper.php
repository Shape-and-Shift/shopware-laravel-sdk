<?php declare(strict_types=1);

namespace Sas\ShopwareLaravelSdk\Facade;

use Illuminate\Support\Facades\Facade;
use Vin\ShopwareSdk\Data\Webhook\AppAction\AppAction;

/**
 * @method static string shop_route(string $route, array $params = [], $absolute = true, ?AppAction $action = null)
 */
class AppHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'AppHelper';
    }
}
