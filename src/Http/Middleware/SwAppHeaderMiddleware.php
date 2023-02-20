<?php declare(strict_types=1);

namespace Sas\ShopwareLaravelSdk\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Sas\ShopwareLaravelSdk\Models\SwShop;
use Sas\ShopwareLaravelSdk\Repositories\ShopRepository;
use Sas\ShopwareLaravelSdk\Utils\AppHelper;
use Symfony\Component\HttpFoundation\HeaderBag;
use Vin\ShopwareSdk\Data\Webhook\ShopRequest;
use Vin\ShopwareSdk\Exception\AuthorizationFailedException;
use function hash_equals;
use function hash_hmac;

class SwAppHeaderMiddleware
{
    protected ShopRepository $shopRepository;

    public function __construct(ShopRepository $shopRepository)
    {
        $this->shopRepository = $shopRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws AuthorizationFailedException
     */
    public function handle(Request $request, Closure $next)
    {
        $shop = $this->authenticateHeaderRequest($request);

        Auth::setUser($shop);

        return $next($request);
    }

    protected function authenticateHeaderRequest(Request $request): SwShop
    {
        $headers = $request->headers;
        $shopId = $headers->get(ShopRequest::SHOP_ID_REQUEST_PARAMETER);
        $appId = $headers->get(AppHelper::APP_ID_REQUEST_PARAMETER);
        if ($headers->has(AppHelper::APP_ID_REQUEST_PARAMETER) && empty($appId)) {
            throw new AuthorizationFailedException(sprintf('%s in headers is invalid', AppHelper::APP_ID_REQUEST_PARAMETER));
        }

        $shop = $this->shopRepository->getShopById($shopId, ['app_id' => $appId]);

        $authenticated = $shop && $this->checkHeaderRequests($headers, $shop);

        if (!$authenticated) {
            throw new AuthorizationFailedException($request->getMethod() . ' is not supported or the data is invalid');
        }

        return $shop;
    }

    protected function checkHeaderRequests(HeaderBag $headers, SwShop $shop): bool
    {
        $queries = [];

        if ($headers->has(AppHelper::LOCATION_ID_REQUEST_PARAMETER)) {
            $queries[AppHelper::LOCATION_ID_REQUEST_PARAMETER] = $headers->get(AppHelper::LOCATION_ID_REQUEST_PARAMETER);
        }

        if ($headers->has(AppHelper::PRIVILEGES_REQUEST_PARAMETER)) {
            $queries[AppHelper::PRIVILEGES_REQUEST_PARAMETER] = urlencode((string)$headers->get(AppHelper::PRIVILEGES_REQUEST_PARAMETER));
        }

        if ($headers->has(ShopRequest::SHOP_ID_REQUEST_PARAMETER)) {
            $queries[ShopRequest::SHOP_ID_REQUEST_PARAMETER] = $headers->get(ShopRequest::SHOP_ID_REQUEST_PARAMETER);
        }

        if ($headers->has(ShopRequest::SHOP_URL_REQUEST_PARAMETER)) {
            $queries[ShopRequest::SHOP_URL_REQUEST_PARAMETER] = $headers->get(ShopRequest::SHOP_URL_REQUEST_PARAMETER);
        }

        if ($headers->has(ShopRequest::TIME_STAMP_REQUEST_PARAMETER)) {
            $queries[ShopRequest::TIME_STAMP_REQUEST_PARAMETER] = $headers->get(ShopRequest::TIME_STAMP_REQUEST_PARAMETER);
        }

        if ($headers->has(ShopRequest::SHOPWARE_VERSION_REQUEST_PARAMETER)) {
            $queries[ShopRequest::SHOPWARE_VERSION_REQUEST_PARAMETER] = $headers->get(ShopRequest::SHOPWARE_VERSION_REQUEST_PARAMETER);
        }

        if ($headers->has(ShopRequest::SHOP_CONTEXT_LANGUAGE)) {
            $queries[ShopRequest::SHOP_CONTEXT_LANGUAGE] = $headers->get(ShopRequest::SHOP_CONTEXT_LANGUAGE);
        }

        if ($headers->has(ShopRequest::SHOP_USER_LANGUAGE)) {
            $queries[ShopRequest::SHOP_USER_LANGUAGE] = $headers->get(ShopRequest::SHOP_USER_LANGUAGE);
        }

        $queryString = htmlspecialchars_decode(urldecode(http_build_query($queries)));

        $hmac = hash_hmac('sha256', htmlspecialchars_decode($queryString), $shop->shop_secret);

        return hash_equals($hmac, (string)$headers->get(ShopRequest::SHOP_SIGNATURE_REQUEST_PARAMETER));
    }
}
