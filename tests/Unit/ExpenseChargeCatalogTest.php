<?php

namespace Tests\Unit;

use App\Support\ExpenseChargeCatalog;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExpenseChargeCatalogTest extends TestCase
{
    #[Test]
    public function categories_hors_popote_exposent_tous_les_types_hors_alimentation(): void
    {
        $this->assertContains('autre', ExpenseChargeCatalog::typesForCategory('charge_fixe'));
        $this->assertContains('electricite', ExpenseChargeCatalog::typesForCategory('charge_fixe'));
        $this->assertContains('electricite', ExpenseChargeCatalog::typesForCategory('charge_variable'));
        $this->assertNotContains('alimentation', ExpenseChargeCatalog::typesForCategory('charge_exceptionnelle'));
    }

    #[Test]
    public function popote_n_autorise_que_alimentation(): void
    {
        $this->assertSame(['alimentation'], ExpenseChargeCatalog::typesForCategory('alimentation_popote'));
    }

    #[Test]
    public function type_allowed_applique_uniquement_la_regle_popote(): void
    {
        $this->assertTrue(ExpenseChargeCatalog::typeAllowedForCategory('charge_fixe', 'eau'));
        $this->assertTrue(ExpenseChargeCatalog::typeAllowedForCategory('charge_variable', 'electricite'));
        $this->assertTrue(ExpenseChargeCatalog::typeAllowedForCategory('alimentation_popote', 'alimentation'));
        $this->assertFalse(ExpenseChargeCatalog::typeAllowedForCategory('charge_variable', 'alimentation'));
        $this->assertFalse(ExpenseChargeCatalog::typeAllowedForCategory('alimentation_popote', 'autre'));
    }

    #[Test]
    public function tous_les_types_globalement_configures_sont_visibles(): void
    {
        $codes = config('expenses.type_charge_codes');
        $this->assertIsArray($codes);

        $this->assertSame($codes, ExpenseChargeCatalog::allTypeCodes());
    }
}
