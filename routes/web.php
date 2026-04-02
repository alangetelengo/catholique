<?php

use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\ChargesFixesReportController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ExpenseController;
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
    Route::resource('members', MemberController::class);
    Route::resource('sacraments', SacramentController::class);
    Route::resource('paroisses', ParoisseController::class)
        ->except(['show'])
        ->parameters(['paroisses' => 'paroisse']);
    Route::resource('events', EventController::class);
    Route::resource('groups', GroupController::class)->except(['show']);
    Route::resource('revenues', RevenueController::class)->except(['show']);
    Route::resource('expenses', ExpenseController::class);
    Route::resource('inventories', InventoryController::class)->except(['show']);
    Route::resource('inventaire-magasin', InventaireMagasinController::class)->except(['show']);
    Route::resource('inventaire-patrimoine', InventairePatrimoineController::class)->except(['show']);

    Route::post('api/sync', [SyncController::class, 'store'])->name('api.sync');
    Route::resource('revenue-reports', RevenueReportController::class)->parameters(['revenue-reports' => 'revenueReport']);
    Route::get('revenue-reports/{revenueReport}/print', [RevenueReportController::class, 'print'])->name('revenue-reports.print');
    Route::get('reports/quete-ordinaire', [QueteOrdinaireReportController::class, 'legacyRedirectIndex'])->name('reports.quete.index');
    Route::get('reports/quete-ordinaire/print', [QueteOrdinaireReportController::class, 'legacyRedirectPrint'])->name('reports.quete.print');
    Route::get('reports/quete-ordinaire/pdf', [QueteOrdinaireReportController::class, 'legacyRedirectPdf'])->name('reports.quete.pdf');
    Route::resource('charges-fixes-reports', ChargesFixesReportController::class)->parameters(['charges-fixes-reports' => 'chargesFixesReport']);
    Route::get('charges-fixes-reports/{chargesFixesReport}/print', [ChargesFixesReportController::class, 'print'])->name('charges-fixes-reports.print');
    Route::get('charges-fixes-reports/{chargesFixesReport}/pdf', [ChargesFixesReportController::class, 'exportPdf'])->name('charges-fixes-reports.pdf');
    Route::resource('popote-reports', PopoteSubventionReportController::class)->parameters(['popote-reports' => 'popoteReport']);
    Route::get('popote-reports/{popoteReport}/print', [PopoteSubventionReportController::class, 'print'])->name('popote-reports.print');
    Route::get('popote-reports/{popoteReport}/pdf', [PopoteSubventionReportController::class, 'exportPdf'])->name('popote-reports.pdf');
    Route::resource('revenue-categories', RevenueCategoryController::class)->except(['show']);
    Route::resource('revenue-types', RevenueTypeController::class)->except(['show']);

    Route::get('financial-statistics', [FinancialStatisticsController::class, 'index'])->name('financial-statistics.index');
    Route::get('financial-statistics/pdf', [FinancialStatisticsController::class, 'exportPdf'])->name('financial-statistics.pdf');
    Route::get('financial-statistics/excel', [FinancialStatisticsController::class, 'exportExcel'])->name('financial-statistics.excel');

    Route::get('financial-reports', [FinancialReportController::class, 'index'])->name('financial-reports.index');
    Route::get('financial-reports/list', [FinancialReportController::class, 'list'])->name('financial-reports.list');
    Route::get('financial-reports/statistics', [FinancialReportController::class, 'statistics'])->name('financial-reports.statistics');
    Route::get('financial-reports/revenues-weekly', [FinancialReportController::class, 'revenuesWeekly'])->name('financial-reports.revenues-weekly');
    Route::get('financial-reports/revenues-weekly/print', [FinancialReportController::class, 'revenuesWeeklyPrint'])->name('financial-reports.revenues-weekly-print');
    Route::match(['get', 'post'], 'financial-reports/revenues-weekly/pdf', [FinancialReportController::class, 'downloadRevenuesWeeklyPdf'])->name('financial-reports.revenues-weekly-pdf');
    Route::get('financial-reports/popote', [FinancialReportController::class, 'popoteReport'])->name('financial-reports.popote');
    Route::get('financial-reports/popote/print', [FinancialReportController::class, 'popotePrint'])->name('financial-reports.popote-print');
    Route::post('financial-reports/popote/pdf', [FinancialReportController::class, 'downloadPopotePdf'])->name('financial-reports.popote-pdf');
    Route::get('financial-reports/charges-fixes', [FinancialReportController::class, 'chargesFixesReport'])->name('financial-reports.charges-fixes');
    Route::get('financial-reports/revenues-by-category', [FinancialReportController::class, 'revenuesByCategory'])->name('financial-reports.revenues-by-category');
    Route::post('financial-reports/revenues-by-category/store', [FinancialReportController::class, 'storeRevenuesByCategory'])->name('financial-reports.revenues-by-category.store');
    Route::get('financial-reports/revenues-by-category/pdf', [FinancialReportController::class, 'downloadRevenuesByCategoryPdf'])->name('financial-reports.revenues-by-category.pdf');
    Route::get('financial-reports/{financialReport}/pdf', [FinancialReportController::class, 'downloadPdf'])->name('financial-reports.download-pdf');
    Route::get('financial-reports/{financialReport}', [FinancialReportController::class, 'show'])->name('financial-reports.show');
    Route::post('financial-reports', [FinancialReportController::class, 'store'])->name('financial-reports.store');

    Route::post('configurations/update-bulk', [ConfigurationController::class, 'updateBulk'])->name('configurations.update-bulk');
    Route::resource('configurations', ConfigurationController::class)->except(['show']);

    Route::resource('roles', RoleController::class)->except(['show']);
    Route::resource('permissions', PermissionController::class)->except(['show']);
});
