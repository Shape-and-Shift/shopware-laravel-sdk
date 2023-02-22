<?php declare(strict_types=1);

namespace Sas\ShopwareLaravelSdk\Repositories;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Sas\ShopwareLaravelSdk\Models\SwShop;
use Vin\ShopwareSdk\Client\AdminAuthenticator;
use Vin\ShopwareSdk\Client\GrantType\ClientCredentialsGrantType;
use Vin\ShopwareSdk\Data\AccessToken;
use Vin\ShopwareSdk\Data\Context;
use Vin\ShopwareSdk\Data\Webhook\Shop;

class ShopRepository
{
    private array $shops = [];

    private Model $shopModel;

    public function __construct(Model $shopModel)
    {
        $this->shopModel = $shopModel;
    }

    public function updateAccessKeysForShop(string $shopId, string $apiKey, string $secretKey): void
    {
        $shop = $this->getShopById($shopId);

        if (!$shop) {
            throw new Exception('Shop not found');
        }

        $shop->update([
            'api_key' => $apiKey,
            'secret_key' => $secretKey
        ]);
    }

    public function createShop(Shop $shop, array $condition = [], array $update = []): void
    {
        $this->shopModel->updateOrCreate(
            array_merge([
                'shop_id' => $shop->getShopId()
            ], $condition),
            array_merge([
                'app_id' => Uuid::uuid4()->toString(),
                'shop_url' => $shop->getShopUrl(),
                'shop_secret' => $shop->getShopSecret(),
                'access_token' => null,
                'api_key' => null,
                'secret_key' => null
            ], $update)
        );
    }

    public function getShopById(?string $shopId, array $queries = []): ?SwShop
    {
        if (!$shopId) {
            return null;
        }

        if (array_key_exists($shopId, $this->shops)) {
            return $this->shops[$shopId];
        }

        $query = $this->shopModel->where('shop_id', $shopId);
        foreach ($queries as $column => $value) {
            if (empty($value) || empty($column)) {
                continue;
            }

            $query = $query->where($column, $value);
        }

        $shop = $query->first();
        if (!$shop) {
            return null;
        }

        return $this->shops[$shopId] = $shop;
    }

    public function removeShop(string $shopId, array $queries = []): void
    {
        $shop = $this->getShopById($shopId, $queries);

        $shop->delete();

        unset($this->shops[$shopId]);
    }

    public function getSecretByShopId(string $shopId, array $queries = []): string
    {
        $shop = $this->getShopById($shopId, $queries);

        if (!$shop) {
            throw new Exception('Shop not found');
        }

        return $shop->shop_secret;
    }

    public function getShopContext(string $shopId, array $queries = []): Context
    {
        $shop = $this->getShopById($shopId, $queries);
        if (!$shop) {
            throw new Exception('Shop not found');
        }

        $token = $shop->access_token;
        if (!$token instanceof AccessToken || $token->isExpired()) {
            $grantType = new ClientCredentialsGrantType($shop->api_key, $shop->secret_key);
            $authenticator = new AdminAuthenticator($grantType, $shop->shop_url);

            $token = $authenticator->fetchAccessToken();

            $shop->update([
                'access_token' => $token
            ]);
        }

        return new Context($shop->shop_url, $token);
    }
}
