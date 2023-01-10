<?php declare(strict_types=1);

namespace Sas\ShopwareLaravelSdk\ServiceProvider;

use Vin\ShopwareSdk\Data\Webhook\ShopRequest;
use Sas\ShopwareLaravelSdk\Models\SwShop;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Foundation\Application;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Support\ServiceProvider;
use Sas\ShopwareLaravelSdk\Repositories\ShopRepository;
use Vin\ShopwareSdk\Data\Context;
use Vin\ShopwareSdk\Data\Webhook\AppAction\AppAction;
use Vin\ShopwareSdk\Data\Webhook\Event\Event;
use Vin\ShopwareSdk\Data\Webhook\IFrameRequest\IFrameRequest;

class ContextServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(ShopRepository::class, function (Application $app) {
            return new ShopRepository(new SwShop());
        });

        $this->app->singleton(Context::class, function (Application $app) {
            /** @var ShopRepository $shopRepository */
            $shopRepository = $app->get(ShopRepository::class);

            /** @var Request $request */
            $request = $app->get(Request::class);

            if ($request->headers->has(ShopRequest::SHOP_ID_REQUEST_PARAMETER)) {
                $shopId = $request->headers->get(ShopRequest::SHOP_ID_REQUEST_PARAMETER);
            } else {
                if ($request->getMethod() === 'POST') {
                    $requestContent = \json_decode($request->getContent(), true);
                    $shopId = $requestContent['source']['shopId'];
                } else {
                    $shopId = $request->query->get(ShopRequest::SHOP_ID_REQUEST_PARAMETER);
                }
            }

            if (!$shopId) {
                return null;
            }

            return $shopRepository->getShopContext($shopId);
        });

        $this->app->singleton(AppAction::class, function (Application $app) {
            /** @var Request $request */
            $request = $app->get(Request::class);

            $requestContent = \json_decode($request->getContent(), true);

            return AppAction::createFromPayload($requestContent, $request->headers->all());
        });

        $this->app->singleton(Event::class, function (Application $app) {
            /** @var Request $request */
            $request = $app->get(Request::class);

            $requestContent = \json_decode($request->getContent(), true);

            return Event::createFromPayload($requestContent, $request->headers->all());
        });

        $this->app->singleton(IFrameRequest::class, function (Application $app) {
            /** @var Request $request */
            $request = $app->get(Request::class);

            return new IFrameRequest($request->all());
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Context::class, AppAction::class, ShopRepository::class, Event::class, IFrameRequest::class];
    }
}
