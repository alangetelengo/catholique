<?php

use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\ApplicationConfigurationController;
use App\Http\Controllers\CaisseController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseReportController;
use App\Http\Controllers\ExpenseTypeController;
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\FinancialStatisticsController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\InventaireMagasinController;
use App\Http\Controllers\InventairePatrimoineController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ParoisseController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PopoteSubventionReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QueteOrdinaireReportController;
use App\Http\Controllers\RevenueCategoryController;
use App\Http\Controllers\RevenueController;
use App\Http\Controllers\RevenueReportController;
use App\Http\Controllers\RevenueTypeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SacramentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

Route::get('/connexion', fn () => redirect()->route('login'))->name('connexion');

Route::middleware('auth')->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('home');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::resource('users', UserController::class)->except(['show']);
    Route::post('members/quick-store', [MemberController::class, 'quickStore'])->name('members.quick-store');
    Route::resource('members', MemberController::class);
    Route::resource('sacraments', SacramentController::class);
    Route::resource('paroisses', ParoisseController::class)
        ->except(['show'])
        ->parameters(['paroisses' => 'paroisse']);
    Route::resource('events', EventController::class);
    Route::resource('groups', GroupController::class)->except(['show']);
    Route::resource('revenues', RevenueController::class)->except(['show']);
    Route::resource('expenses', ExpenseController::class);
    Route::get('caisses', [CaisseController::class, 'index'])->name('caisses.index');
    Route::get('caisses/credit', [CaisseController::class, 'createCredit'])->name('caisses.credit.create');
    Route::post('caisses/credit', [CaisseController::class, 'storeCredit'])->name('caisses.credit.store');
    Route::get('caisses/virement', [CaisseController::class, 'createVirement'])->name('caisses.virement.create');
    Route::post('caisses/virement', [CaisseController::class, 'storeVirement'])->name('caisses.virement.store');
    Route::get('caisses/mouvements/{mouvement}/edit', [CaisseController::class, 'editMouvement'])->name('caisses.mouvements.edit');
    Route::put('caisses/mouvements/{mouvement}', [CaisseController::class, 'updateMouvement'])->name('caisses.mouvements.update');
    Route::delete('caisses/mouvements/{mouvement}', [CaisseController::class, 'destroyMouvement'])->name('caisses.mouvements.destroy');
    Route::get('caisses/{caisse}', [CaisseController::class, 'show'])->name('caisses.show');
    Route::resource('inventories', InventoryController::class)->except(['show']);
    Route::resource('inventaire-magasin', InventaireMagasinController::class)->except(['show']);
    Route::resource('inventaire-patrimoine', InventairePatrimoineController::class)->except(['show']);

    Route::post('api/sync', [SyncController::class, 'store'])->name('api.sync');
    Route::resource('revenue-reports', RevenueReportController::class)->parameters(['revenue-reports' => 'revenueReport']);
    Route::get('revenue-reports/{revenueReport}/print', [RevenueReportController::class, 'print'])->name('revenue-reports.print');
    Route::get('reports/quete-ordinaire', [QueteOrdinaireReportController::class, 'legacyRedirectIndex'])->name('reports.quete.index');
    Route::get('reports/quete-ordinaire/print', [QueteOrdinaireReportController::class, 'legacyRedirectPrint'])->name('reports.quete.print');
    Route::get('reports/quete-ordinaire/pdf', [QueteOrdinaireReportController::class, 'legacyRedirectPdf'])->name('reports.quete.pdf');
    Route::resource('popote-reports', PopoteSubventionReportController::class)->parameters(['popote-reports' => 'popoteReport'])->except(['index']);
    Route::redirect('popote-reports', '/financial-reports/list')->name('popote-reports.index');
    Route::get('popote-reports/{popoteReport}/print', [PopoteSubventionReportController::class, 'print'])->name('popote-reports.print');
    Route::get('popote-reports/{popoteReport}/pdf', [PopoteSubventionReportController::class, 'exportPdf'])->name('popote-reports.pdf');
    Route::resource('revenue-categories', RevenueCategoryController::class)->except(['show']);
    Route::resource('revenue-types', RevenueTypeController::class)->except(['show']);
    Route::resource('expense-types', ExpenseTypeController::class)->except(['show']);

    Route::get('financial-statistics', [FinancialStatisticsController::class, 'index'])->name('financial-statistics.index');
    Route::get('financial-statistics/pdf', [FinancialStatisticsController::class, 'exportPdf'])->name('financial-statistics.pdf');
    Route::get('financial-statistics/excel', [FinancialStatisticsController::class, 'exportExcel'])->name('financial-statistics.excel');

    Route::redirect('financial-reports', '/financial-reports/list')->name('financial-reports.index');
    Route::get('financial-reports/list', [FinancialReportController::class, 'list'])->name('financial-reports.list');
    Route::get('financial-reports/expenses', [ExpenseReportController::class, 'index'])->name('financial-reports.expenses');
    Route::post('financial-reports/expenses/calculate', [ExpenseReportController::class, 'calculate'])->name('financial-reports.expenses.calculate');
    Route::post('financial-reports/expenses/store', [ExpenseReportController::class, 'store'])->name('financial-reports.expenses.store');
    Route::post('financial-reports/expenses/store-popote', [ExpenseReportController::class, 'storePopote'])->name('financial-reports.expenses.store-popote');
    Route::get('financial-reports/expenses/print', [ExpenseReportController::class, 'printPdf'])->name('financial-reports.expenses.print');
    Route::get('financial-reports/expenses/pdf', [ExpenseReportController::class, 'downloadPdf'])->name('financial-reports.expenses.pdf');
    Route::redirect('financial-reports/expenses-by-category', '/financial-reports/expenses')->name('financial-reports.expenses-by-category');
    Route::get('financial-reports/capital-usage', [ExpenseReportController::class, 'capitalUsage'])->name('financial-reports.capital-usage');
    Route::get('financial-reports/capital-usage/print', [ExpenseReportController::class, 'capitalUsagePrint'])->name('financial-reports.capital-usage.print');
    Route::get('financial-reports/statistics', [FinancialReportController::class, 'statistics'])->name('financial-reports.statistics');
    Route::get('financial-reports/revenues-weekly', [FinancialReportController::class, 'revenuesWeekly'])->name('financial-reports.revenues-weekly');
    Route::get('financial-reports/revenues-weekly/print', [FinancialReportController::class, 'revenuesWeeklyPrint'])->name('financial-reports.revenues-weekly-print');
    Route::match(['get', 'post'], 'financial-reports/revenues-weekly/pdf', [FinancialReportController::class, 'downloadRevenuesWeeklyPdf'])->name('financial-reports.revenues-weekly-pdf');
    Route::get('financial-reports/revenues-by-category/revenue-categories', [FinancialReportController::class, 'revenueCategoriesForParoisse'])->name('financial-reports.revenues-by-category.revenue-categories');
    Route::get('financial-reports/revenues-by-category/revenue-types', [FinancialReportController::class, 'revenueTypesForCategory'])->name('financial-reports.revenues-by-category.revenue-types');
    Route::post('financial-reports/revenues-by-category/calculate', [FinancialReportController::class, 'revenuesByCategoryCalculate'])->name('financial-reports.revenues-by-category.calculate');
    Route::get('financial-reports/revenues-by-category', [FinancialReportController::class, 'revenuesByCategory'])->name('financial-reports.revenues-by-category');
    Route::post('financial-reports/revenues-by-category/store', [FinancialReportController::class, 'storeRevenuesByCategory'])->name('financial-reports.revenues-by-category.store');
    Route::get('financial-reports/revenues-by-category/pdf', [FinancialReportController::class, 'downloadRevenuesByCategoryPdf'])->name('financial-reports.revenues-by-category.pdf');
    Route::get('financial-reports/{financialReport}/pdf', [FinancialReportController::class, 'downloadPdf'])->name('financial-reports.download-pdf');
    Route::get('financial-reports/{financialReport}/print', [FinancialReportController::class, 'printViewer'])->name('financial-reports.print');
    Route::delete('financial-reports/{financialReport}', [FinancialReportController::class, 'destroy'])->name('financial-reports.destroy');
    Route::get('financial-reports/{financialReport}', [FinancialReportController::class, 'show'])->name('financial-reports.show');

    Route::get('application-configuration', [ApplicationConfigurationController::class, 'index'])
        ->name('application-configuration.index');

    Route::post('configurations/update-bulk', [ConfigurationController::class, 'updateBulk'])->name('configurations.update-bulk');
    Route::get('configurations/workspace', [ConfigurationController::class, 'workspace'])->name('configurations.workspace');
    Route::resource('configurations', ConfigurationController::class)->except(['show']);

    Route::resource('roles', RoleController::class)->except(['show']);
    Route::resource('permissions', PermissionController::class)->except(['show']);
});
