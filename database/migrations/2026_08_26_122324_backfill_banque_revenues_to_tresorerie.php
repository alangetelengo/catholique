<?php

use App\Services\CaisseService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (! class_exists(CaisseService::class)) {
            return;
        }

        /** @var CaisseService $service */
        $service = app(CaisseService::class);
        $service->backfillBanqueCredits();
    }

    public function down(): void
    {
        // Les mouvements générés restent utiles ; pas de rollback destructif.
    }
};
