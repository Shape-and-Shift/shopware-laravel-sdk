<?php declare(strict_types=1);

namespace Sas\ShopwareLaravelSdk\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Sas\ShopwareLaravelSdk\Repositories\ShopRepository;
use Vin\ShopwareSdk\Data\Response\RegistrationResponse;
use Vin\ShopwareSdk\Data\Webhook\App;
use Vin\ShopwareSdk\Service\WebhookAuthenticator;

class AppRegistrationController extends BaseController
{
    public function register(ShopRepository $shopRepository): RegistrationResponse
    {
        $authenticator = new WebhookAuthenticator();

        $app = new App(config('sas_app.app_name'), config('sas_app.app_secret'));

        $response = $authenticator->register($app);

        $shopRepository->createShop($response->getShop());

        $confirmationUrl = route('sas.app.auth.confirmation');

        return new RegistrationResponse($response, $confirmationUrl);
    }

    public function confirm(Request $request, ShopRepository $shopRepository): Response
    {
        $shopId = $request->request->get('shopId');

        $shopSecret = $shopRepository->getSecretByShopId($shopId);

        if (!WebhookAuthenticator::authenticatePostRequest($shopSecret)) {
            return new Response(null, 401);
        }

        $shopRepository->updateAccessKeysForShop(
            $shopId,
            $request->request->get('apiKey'),
            $request->request->get('secretKey')
        );

        return new Response();
    }
}
