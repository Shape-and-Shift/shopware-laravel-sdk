<?php declare(strict_types=1);

namespace Sas\ShopwareLaravelSdk\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Sas\ShopwareLaravelSdk\Models\SwShop;
use Sas\ShopwareLaravelSdk\Repositories\ShopRepository;
use Vin\ShopwareSdk\Data\Webhook\Shop;
use Vin\ShopwareSdk\Data\Webhook\ShopRequest;
use Vin\ShopwareSdk\Exception\AuthorizationFailedException;
use Vin\ShopwareSdk\Service\WebhookAuthenticator;

class SwAppMiddleware
{
    public const REQUIRED_KEYS = [
        ShopRequest::SHOP_ID_REQUEST_PARAMETER,
        ShopRequest::SHOP_URL_REQUEST_PARAMETER,
        ShopRequest::SHOPWARE_VERSION_REQUEST_PARAMETER,
        ShopRequest::SHOP_SIGNATURE_REQUEST_PARAMETER,
        ShopRequest::TIME_STAMP_REQUEST_PARAMETER,
    ];

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
     * @param string|null ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $shop = null;

        if ($request->getMethod() === 'POST' && $this->supportsPostRequest($request)) {
            $shop = $this->authenticatePostRequest($request);
        } elseif ($request->getMethod() === 'GET' && $this->supportsGetRequest($request)) {
            $shop = $this->authenticateGetRequest($request);
        } elseif ($request->getMethod() === 'DELETE' && $this->supportsGetRequest($request)) {
            $shop = $this->authenticateDeleteRequest($request);
        }

        // TODO: set custom guard for app
        if ($shop) {
            Auth::setUser($shop);
        }

        return $next($request);
    }

    protected function checkRequiredKeys(array $data): bool
    {
        foreach (self::REQUIRED_KEYS as $key) {
            if (!array_key_exists($key, $data)) {
                return false;
            }
        }

        return true;
    }

    protected function supportsPostRequest(Request $request): bool
    {
        $requestContent = json_decode($request->getContent(), true);

        $hasSource = $requestContent && array_key_exists('source', $requestContent);

        if (!$hasSource) {
            return false;
        }

        return $this->checkRequiredKeys($requestContent['source']);
    }

    protected function supportsGetRequest(Request $request): bool
    {
        return $this->checkRequiredKeys($request->query->all());
    }

    protected function authenticatePostRequest(Request $request): SwShop
    {
        $requestContent = json_decode($request->getContent(), true);
        $sourceRequest = $requestContent['source'];
        $shopId = $sourceRequest[ShopRequest::SHOP_ID_REQUEST_PARAMETER];

        $shop = $this->shopRepository->getShopById($shopId);

        $authenticated = $shop && WebhookAuthenticator::authenticatePostRequest($shop->shop_secret);

        if (!$authenticated) {
            throw new AuthorizationFailedException($request->getMethod() . ' is not supported or the data is invalid');
        }

        return $shop;
    }

    protected function authenticateGetRequest(Request $request): SwShop
    {
        $shopId = $request->query->get(ShopRequest::SHOP_ID_REQUEST_PARAMETER);
        $shop = $this->shopRepository->getShopById($shopId);

        $authenticated = $shop && WebhookAuthenticator::authenticateGetRequest($shop->shop_secret);

        if (!$authenticated) {
            throw new AuthorizationFailedException($request->getMethod() . ' is not supported or the data is invalid');
        }

        return $shop;
    }

    protected function authenticateDeleteRequest(Request $request): SwShop
    {
        $shopId = $request->query->get(ShopRequest::SHOP_ID_REQUEST_PARAMETER);
        $shop = $this->shopRepository->getShopById($shopId);

        $authenticated = $shop && WebhookAuthenticator::authenticateGetRequest($shop->shop_secret);

        if (!$authenticated) {
            throw new AuthorizationFailedException($request->getMethod() . ' is not supported or the data is invalid');
        }

        return $shop;
    }
}
