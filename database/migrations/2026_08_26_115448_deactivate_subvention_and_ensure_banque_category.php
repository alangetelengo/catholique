<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('revenue_categories')
            ->where('code', 'subvention')
            ->update(['actif' => false, 'updated_at' => $now]);

        DB::table('revenue_types')
            ->where('code', 'like', 'subvention_%')
            ->update(['actif' => false, 'updated_at' => $now]);

        $paroisseIds = DB::table('paroisses')->pluck('id');

        foreach ($paroisseIds as $paroisseId) {
            // Ancien code prod « capita » / « capital » → banque
            DB::table('revenue_categories')
                ->where('paroisse_id', $paroisseId)
                ->whereIn('code', ['capita', 'capital'])
                ->update([
                    'code' => 'banque',
                    'nom' => 'BANQUE (économat diocésain)',
                    'description' => 'Versements de la hiérarchie / économat diocésain — trésorerie générale uniquement',
                    'actif' => true,
                    'ordre' => 0,
                    'updated_at' => $now,
                ]);

            DB::table('revenue_types')
                ->where('paroisse_id', $paroisseId)
                ->whereIn('code', ['rev-principal', 'rev_principal'])
                ->update([
                    'code' => 'revenu_principal',
                    'nom' => 'Revenu principal',
                    'actif' => true,
                    'updated_at' => $now,
                ]);

            $categoryId = DB::table('revenue_categories')->where([
                'paroisse_id' => $paroisseId,
                'code' => 'banque',
            ])->value('id');

            if (! $categoryId) {
                $categoryId = DB::table('revenue_categories')->insertGetId([
                    'paroisse_id' => $paroisseId,
                    'code' => 'banque',
                    'nom' => 'BANQUE (économat diocésain)',
                    'description' => 'Versements de la hiérarchie / économat diocésain — trésorerie générale uniquement',
                    'actif' => true,
                    'ordre' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('revenue_categories')->where('id', $categoryId)->update([
                    'nom' => 'BANQUE (économat diocésain)',
                    'description' => 'Versements de la hiérarchie / économat diocésain — trésorerie générale uniquement',
                    'actif' => true,
                    'ordre' => 0,
                    'updated_at' => $now,
                ]);
            }

            $typeExists = DB::table('revenue_types')->where([
                'paroisse_id' => $paroisseId,
                'code' => 'revenu_principal',
            ])->exists();

            if (! $typeExists) {
                DB::table('revenue_types')->insert([
                    'paroisse_id' => $paroisseId,
                    'revenue_category_id' => $categoryId,
                    'code' => 'revenu_principal',
                    'nom' => 'Revenu principal',
                    'description' => 'Revenu principal reçu de la hiérarchie',
                    'actif' => true,
                    'ordre' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('revenue_types')->where([
                    'paroisse_id' => $paroisseId,
                    'code' => 'revenu_principal',
                ])->update([
                    'revenue_category_id' => $categoryId,
                    'nom' => 'Revenu principal',
                    'actif' => true,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('revenue_categories')
            ->where('code', 'subvention')
            ->update(['actif' => true, 'updated_at' => now()]);

        DB::table('revenue_types')
            ->where('code', 'like', 'subvention_%')
            ->update(['actif' => true, 'updated_at' => now()]);
    }
};
