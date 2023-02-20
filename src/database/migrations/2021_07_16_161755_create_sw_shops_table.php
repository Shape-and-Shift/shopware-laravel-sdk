<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class CreateSwShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('sw_shops', function (Blueprint $table): void {
            $table->string('app_id')->primary();
            $table->string('shop_id');
            $table->string('shop_url', 255);
            $table->string('shop_secret', 255);
            $table->string('app_name', 255)->nullable();
            $table->string('api_key', 255)->nullable();
            $table->string('secret_key', 255)->nullable();
            $table->longText('access_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('sw_shops');
    }
}
