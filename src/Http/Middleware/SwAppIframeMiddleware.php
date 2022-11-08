<?php declare(strict_types=1);

namespace Sas\ShopwareLaravelSdk\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Sas\ShopwareLaravelSdk\Models\SwShop;
use Vin\ShopwareSdk\Data\Webhook\Shop;
use Vin\ShopwareSdk\Data\Webhook\ShopRequest;
use Vin\ShopwareSdk\Exception\AuthorizationFailedException;
use Vin\ShopwareSdk\Service\WebhookAuthenticator;

class SwAppIframeMiddleware extends SwAppMiddleware
{
    protected function authenticatePostRequest(Request $request): SwShop
    {
        $requestContent = json_decode($request->getContent(), true);
        $sourceRequest = $requestContent['source'];
        $shopId = $sourceRequest[ShopRequest::SHOP_ID_REQUEST_PARAMETER];

        $shop = $this->shopRepository->getShopById($shopId);

        $authenticated = $shop && $this->checkPostRequest($sourceRequest, $shop->shop_secret);

        if (!$authenticated) {
            throw new AuthorizationFailedException($request->getMethod() . ' is not supported or the data is invalid');
        }

        return $shop;
    }

    private function checkPostRequest(array $sourceRequests, string $shopSecret): bool
    {
        $shopwareShopSignature = $sourceRequests['shopware-shop-signature'];

        unset($sourceRequests[ShopRequest::SHOP_SIGNATURE_REQUEST_PARAMETER]);

        $results = [];
        foreach ($sourceRequests as $key => $sourceRequest) {
            if (!in_array($key, self::REQUIRED_KEYS)) {
                $sourceRequest = urlencode($sourceRequest);
            }

            $results[$key] = $sourceRequest;
        }

        $queryString = htmlspecialchars_decode(urldecode(http_build_query($results)));
        $hmac = \hash_hmac('sha256', $queryString, $shopSecret);

        return \hash_equals($hmac, $shopwareShopSignature);
    }
}
