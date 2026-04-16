<?php

namespace Tests\Unit;

use App\Support\ExpenseChargeCatalog;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExpenseChargeCatalogTest extends TestCase
{
    #[Test]
    public function charge_fixe_n_inclut_pas_autre(): void
    {
        $this->assertNotContains('autre', ExpenseChargeCatalog::typesForCategory('charge_fixe'));
        $this->assertContains('electricite', ExpenseChargeCatalog::typesForCategory('charge_fixe'));
    }

    #[Test]
    public function charge_exceptionnelle_n_autorise_que_autre(): void
    {
        $this->assertSame(['autre'], ExpenseChargeCatalog::typesForCategory('charge_exceptionnelle'));
    }

    #[Test]
    public function type_allowed_rejette_les_paires_invalides(): void
    {
        $this->assertTrue(ExpenseChargeCatalog::typeAllowedForCategory('charge_fixe', 'eau'));
        $this->assertFalse(ExpenseChargeCatalog::typeAllowedForCategory('charge_fixe', 'autre'));
        $this->assertFalse(ExpenseChargeCatalog::typeAllowedForCategory('charge_variable', 'electricite'));
        $this->assertTrue(ExpenseChargeCatalog::typeAllowedForCategory('alimentation_popote', 'alimentation'));
    }

    #[Test]
    public function chaque_type_enum_est_assigne_exactement_une_fois(): void
    {
        $codes = config('expenses.type_charge_codes');
        $this->assertIsArray($codes);
        $map = config('expenses.types_by_categorie');
        $this->assertIsArray($map);
        $seen = [];
        foreach ($map as $cat => $list) {
            $this->assertIsString($cat);
            $this->assertIsArray($list);
            foreach ($list as $code) {
                $this->assertIsString($code);
                $this->assertArrayNotHasKey($code, $seen, 'Type dupliqué entre catégories : '.$code);
                $seen[$code] = $cat;
            }
        }
        foreach ($codes as $code) {
            $this->assertArrayHasKey($code, $seen, 'Type non assigné à une catégorie : '.$code);
        }
    }
}
