<?php declare(strict_types=1);

namespace Sas\ShopwareLaravelSdk\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Vin\ShopwareSdk\Data\Webhook\ShopRequest;
use Sas\ShopwareLaravelSdk\Repositories\ShopRepository;
use Vin\ShopwareSdk\Exception\AuthorizationFailedException;
use Vin\ShopwareSdk\Service\WebhookAuthenticator;

class SwAppMiddleware
{
    private ShopRepository $shopRepository;

    public function __construct(ShopRepository $shopRepository)
    {
        $this->shopRepository = $shopRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $authenticated = false;
        $shop = null;

        if ($request->getMethod() === 'POST' && $this->supportsPostRequest($request)) {
            $requestContent = json_decode($request->getContent(), true);
            $shopId = $requestContent['source']['shopId'];

            $shop = $this->shopRepository->getShopById($shopId);

            $authenticated = $shop && WebhookAuthenticator::authenticatePostRequest($shop->shop_secret);
        } elseif ($request->getMethod() === 'GET' && $this->supportsGetRequest($request)) {
            $shopId = $request->query->get(ShopRequest::SHOP_ID_REQUEST_PARAMETER);
            $shop = $this->shopRepository->getShopById($shopId);

            $authenticated = $shop && WebhookAuthenticator::authenticateGetRequest($shop->shop_secret);
        }elseif ($request->getMethod() === 'DELETE' && $this->supportsGetRequest($request)) {
            $shopId = $request->query->get(ShopRequest::SHOP_ID_REQUEST_PARAMETER);
            $shop = $this->shopRepository->getShopById($shopId);

            $authenticated = $shop && WebhookAuthenticator::authenticateGetRequest($shop->shop_secret);
        }

        if (!$authenticated) {
            throw new AuthorizationFailedException($request->getMethod() . ' is not supported or the data is invalid');
        }

        // TODO: set custom guard for app
        if ($shop) {
            Auth::setUser($shop);
        }

        return $next($request);
    }

    private function supportsPostRequest(Request $request): bool
    {
        $requestContent = json_decode($request->getContent(), true);

        $hasSource = $requestContent && array_key_exists('source', $requestContent);

        if (!$hasSource) {
            return false;
        }

        return $this->checkRequiredKeys($requestContent['source']);
    }

    private function supportsGetRequest(Request $request): bool
    {
        return $this->checkRequiredKeys($request->query->all());
    }

    private function checkRequiredKeys(array $data): bool {
        $requiredKeys = [
            ShopRequest::SHOP_ID_REQUEST_PARAMETER,
            ShopRequest::SHOP_URL_REQUEST_PARAMETER,
            ShopRequest::SHOPWARE_VERSION_REQUEST_PARAMETER,
            ShopRequest::SHOP_SIGNATURE_REQUEST_PARAMETER,
            ShopRequest::TIME_STAMP_REQUEST_PARAMETER,
        ];

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $data)) {
                return false;
            }
        }

        return true;
    }
}
