<?php

use App\Models\CaisseMouvement;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('caisse_mouvements') || ! Schema::hasTable('revenues')) {
            return;
        }

        $hasLegacyCredits = CaisseMouvement::query()
            ->where('type', CaisseMouvement::TYPE_CREDIT_DIRECT)
            ->where('libelle', 'like', 'Crédit legacy#%')
            ->exists();

        if (! $hasLegacyCredits) {
            return;
        }

        Artisan::call('paroisse:align-legacy-finances-to-caisses', [
            '--force' => true,
        ]);
    }

    public function down(): void
    {
        // Irreversible data repair.
    }
};
