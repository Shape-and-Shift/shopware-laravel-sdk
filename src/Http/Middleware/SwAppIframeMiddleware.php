<?php declare(strict_types=1);

namespace Sas\ShopwareLaravelSdk\Http\Middleware;

use Illuminate\Http\Request;
use Sas\ShopwareLaravelSdk\Models\SwShop;
use Symfony\Component\HttpFoundation\InputBag;
use Vin\ShopwareSdk\Data\Webhook\ShopRequest;
use Vin\ShopwareSdk\Exception\AuthorizationFailedException;
use function hash_equals;
use function hash_hmac;

class SwAppIframeMiddleware extends SwAppMiddleware
{
    protected function authenticatePostRequest(Request $request): SwShop
    {
        $requestContent = json_decode((string)$request->getContent(), true);
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
        $hmac = hash_hmac('sha256', $queryString, $shopSecret);

        return hash_equals($hmac, $shopwareShopSignature);
    }

    protected function authenticateGetRequest(Request $request): SwShop
    {
        $queries = $request->query;
        $shopId = (string)$queries->get(ShopRequest::SHOP_ID_REQUEST_PARAMETER);

        $shop = $this->shopRepository->getShopById($shopId);

        $authenticated = $shop && $this->checkGetRequests($queries, $shop);

        if (!$authenticated) {
            throw new AuthorizationFailedException($request->getMethod() . ' is not supported or the data is invalid');
        }

        return $shop;
    }

    protected function checkGetRequests(InputBag $inputBag, SwShop $shop): bool
    {
        $queries = [];

        if ($inputBag->has('location-id')) {
            $queries['location-id'] = $inputBag->get('location-id');
        }

        if ($inputBag->has('privileges')) {
            $queries['privileges'] = urlencode((string)$inputBag->get('privileges'));
        }

        if ($inputBag->has(ShopRequest::SHOP_ID_REQUEST_PARAMETER)) {
            $queries[ShopRequest::SHOP_ID_REQUEST_PARAMETER] = $inputBag->get(ShopRequest::SHOP_ID_REQUEST_PARAMETER);
        }

        if ($inputBag->has(ShopRequest::SHOP_URL_REQUEST_PARAMETER)) {
            $queries[ShopRequest::SHOP_URL_REQUEST_PARAMETER] = $inputBag->get(ShopRequest::SHOP_URL_REQUEST_PARAMETER);
        }

        if ($inputBag->has(ShopRequest::TIME_STAMP_REQUEST_PARAMETER)) {
            $queries[ShopRequest::TIME_STAMP_REQUEST_PARAMETER] = $inputBag->get(ShopRequest::TIME_STAMP_REQUEST_PARAMETER);
        }

        if ($inputBag->has(ShopRequest::SHOPWARE_VERSION_REQUEST_PARAMETER)) {
            $queries[ShopRequest::SHOPWARE_VERSION_REQUEST_PARAMETER] = $inputBag->get(ShopRequest::SHOPWARE_VERSION_REQUEST_PARAMETER);
        }

        if ($inputBag->has(ShopRequest::SHOP_CONTEXT_LANGUAGE)) {
            $queries[ShopRequest::SHOP_CONTEXT_LANGUAGE] = $inputBag->get(ShopRequest::SHOP_CONTEXT_LANGUAGE);
        }

        if ($inputBag->has(ShopRequest::SHOP_USER_LANGUAGE)) {
            $queries[ShopRequest::SHOP_USER_LANGUAGE] = $inputBag->get(ShopRequest::SHOP_USER_LANGUAGE);
        }

        $queryString = htmlspecialchars_decode(urldecode(http_build_query($queries)));

        $hmac = hash_hmac('sha256', htmlspecialchars_decode($queryString), $shop->shop_secret);

        return hash_equals($hmac, (string)$inputBag->get(ShopRequest::SHOP_SIGNATURE_REQUEST_PARAMETER));
    }
}
