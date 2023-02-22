<?php declare(strict_types=1);

namespace Sas\ShopwareLaravelSdk\Utils;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Vin\ShopwareSdk\Data\Webhook\AppAction\AppAction;
use Vin\ShopwareSdk\Data\Webhook\Shop;
use Vin\ShopwareSdk\Data\Webhook\ShopRequest;
use Symfony\Component\HttpFoundation\Request;

class AppHelper
{
    const PRIVILEGES_REQUEST_PARAMETER = 'privileges';
    const LOCATION_ID_REQUEST_PARAMETER = 'location-id';
    const APP_ID_REQUEST_PARAMETER = 'app-id';

    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function shop_route(string $route, array $params = [], $absolute = true, ?AppAction $action = null): string
    {
        if ($action !== null) {
            $queryString = sprintf(
                'shop-id=%s&shop-url=%s&timestamp=%s&sw-version=%s',
                $action->getSource()->getShopId(),
                $action->getSource()->getShopUrl(),
                $action->getMeta()->getTimestamp(),
                $this->request->headers->get(ShopRequest::SHOPWARE_VERSION_REQUEST_PARAMETER),
            );

            $hmac = \hash_hmac('sha256', htmlspecialchars_decode($queryString), Auth::user()->shop_secret);

            $params = array_merge(array_filter([
                ShopRequest::SHOP_ID_REQUEST_PARAMETER => $action->getSource()->getShopId(),
                ShopRequest::SHOP_URL_REQUEST_PARAMETER => $action->getSource()->getShopUrl(),
                ShopRequest::SHOPWARE_VERSION_REQUEST_PARAMETER => $this->request->headers->get(ShopRequest::SHOPWARE_VERSION_REQUEST_PARAMETER),
                ShopRequest::SHOP_SIGNATURE_REQUEST_PARAMETER => $hmac,
                ShopRequest::TIME_STAMP_REQUEST_PARAMETER => $action->getMeta()->getTimestamp(),
            ]), $params);
        }

        $params = array_merge(array_filter([
            ShopRequest::SHOP_ID_REQUEST_PARAMETER => $this->request->get(ShopRequest::SHOP_ID_REQUEST_PARAMETER),
            ShopRequest::SHOP_URL_REQUEST_PARAMETER => $this->request->get(ShopRequest::SHOP_URL_REQUEST_PARAMETER),
            ShopRequest::SHOPWARE_VERSION_REQUEST_PARAMETER => $this->request->get(ShopRequest::SHOPWARE_VERSION_REQUEST_PARAMETER),
            ShopRequest::SHOP_SIGNATURE_REQUEST_PARAMETER => $this->request->get(ShopRequest::SHOP_SIGNATURE_REQUEST_PARAMETER),
            ShopRequest::TIME_STAMP_REQUEST_PARAMETER => $this->request->get(ShopRequest::TIME_STAMP_REQUEST_PARAMETER),
        ]), $params);

        return route($route, $params, $absolute);
    }
}
