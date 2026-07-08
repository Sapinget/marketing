<?php

namespace Tests\Feature;

use App\Support\MarketingDashboardShell;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MarketingDashboardShellTest extends TestCase
{
    use RefreshDatabase;

    protected function assertHtmlContains(string $needle, string $haystack, string $message = ''): void
    {
        $this->assertNotFalse(
            strpos($haystack, $needle),
            $message !== '' ? $message : "Failed asserting that the dashboard shell contains [{$needle}]."
        );
    }

    protected function assertHtmlNotContains(string $needle, string $haystack, string $message = ''): void
    {
        $this->assertFalse(
            str_contains($haystack, $needle),
            $message !== '' ? $message : "Failed asserting that the dashboard shell does not contain [{$needle}]."
        );
    }

    protected function assertHtmlMatches(string $pattern, string $haystack, string $message = ''): void
    {
        $this->assertSame(
            1,
            preg_match($pattern, $haystack),
            $message !== '' ? $message : "Failed asserting that the dashboard shell matches [{$pattern}]."
        );
    }

    protected function assertFragmentHasKey(string $needle, array $fragments, string $message = ''): void
    {
        $this->assertTrue(
            array_key_exists($needle, $fragments),
            $message !== '' ? $message : "Failed asserting that the dashboard shell fragments contain [{$needle}]."
        );
    }

    protected function assertFragmentNotHasKey(string $needle, array $fragments, string $message = ''): void
    {
        $this->assertFalse(
            array_key_exists($needle, $fragments),
            $message !== '' ? $message : "Failed asserting that the dashboard shell fragments do not contain [{$needle}]."
        );
    }

    public function test_root_serves_marketing_dashboard_shell(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $response->assertSee('Marketing Dashboard', false);
        $response->assertSee('Selamat Datang', false);
        $response->assertDontSee('https://cdn.tailwindcss.com', false);
        $response->assertDontSee('https://cdn.jsdelivr.net', false);
        $response->assertDontSee('https://fonts.googleapis.com', false);
        $response->assertSee('/api/master-plans', false);
        $response->assertSee('/api/distributions', false);
        $response->assertSee('/api/analytics', false);
        $response->assertSee('Dashboard marketing lengkap berjalan di Laravel', false);
        $this->assertHtmlMatches('/<link[^>]+href="[^"]*\/asset\/images\/favicon\.ico"/', $html);
        $this->assertHtmlMatches('/<link[^>]+href="[^"]*\/build\/assets\/app-[^"]+\.css"/', $html);
        $this->assertHtmlMatches('/<script[^>]+src="[^"]*\/vendor\/dashboard\/vue\/vue\.global\.prod\.js"/', $html);
        $this->assertHtmlMatches('/<script[^>]+src="[^"]*\/vendor\/dashboard\/papaparse\/papaparse\.min\.js"/', $html);
        $this->assertHtmlMatches('/<script[^>]+src="[^"]*\/vendor\/dashboard\/apexcharts\/apexcharts\.min\.js"/', $html);
        $this->assertHtmlMatches('/<link[^>]+href="[^"]*\/vendor\/dashboard\/fontawesome\/css\/all\.min\.css"/', $html);
    }

    public function test_root_injects_backend_url_and_no_store_meta_into_shell_response(): void
    {
        $response = $this->get('/');
        $cacheControl = (string) $response->headers->get('Cache-Control');

        $response->assertOk();
        $response->assertHeader('Pragma', 'no-cache');
        $this->assertHtmlContains('no-store', $cacheControl);
        $this->assertHtmlContains('no-cache', $cacheControl);
        $this->assertHtmlContains('must-revalidate', $cacheControl);
        $this->assertHtmlContains('max-age=0', $cacheControl);
        $response->assertSee('<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">', false);
        $response->assertSee('<meta http-equiv="Pragma" content="no-cache">', false);
        $response->assertSee('window.MARKETING_BACKEND_URL=', false);
    }

    public function test_root_applies_security_headers_and_accessible_viewport(): void
    {
        $response = $this->get('/');
        $contentSecurityPolicy = (string) $response->headers->get('Content-Security-Policy');
        $permissionsPolicy = (string) $response->headers->get('Permissions-Policy');

        $response->assertOk();
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertHtmlContains("frame-ancestors 'none'", $contentSecurityPolicy);
        $this->assertHtmlContains("object-src 'none'", $contentSecurityPolicy);
        $this->assertHtmlContains("'unsafe-eval'", $contentSecurityPolicy);
        $this->assertHtmlNotContains('cdn.jsdelivr.net', $contentSecurityPolicy);
        $this->assertHtmlNotContains('fonts.googleapis.com', $contentSecurityPolicy);
        $this->assertHtmlNotContains('fonts.gstatic.com', $contentSecurityPolicy);
        $this->assertHtmlContains('camera=()', $permissionsPolicy);
        $this->assertHtmlContains('microphone=()', $permissionsPolicy);
        $response->assertSee('<meta name="viewport" content="width=device-width, initial-scale=1.0" />', false);
        $response->assertDontSee('user-scalable=0', false);
        $response->assertDontSee('maximum-scale=1', false);
    }

    public function test_root_renders_explicit_blade_shell_wrappers_around_legacy_dashboard_markup(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('<!doctype html>', false);
        $response->assertSee('<html lang="id">', false);
        $response->assertSee('<head>', false);
        $response->assertSee('</head>', false);
        $response->assertSee('<body class="text-ppp-text antialiased">', false);
        $response->assertSee('</body>', false);
        $response->assertSee('</html>', false);
    }

    public function test_dashboard_shell_moves_large_inline_head_styles_into_vite_css(): void
    {
        $head = file_get_contents(resource_path('views/dashboard/partials/shell/head.blade.php'));
        $appCss = file_get_contents(resource_path('css/app.css'));
        $dashboardShellCss = file_get_contents(resource_path('css/dashboard-shell.css'));

        $this->assertIsString($head);
        $this->assertIsString($appCss);
        $this->assertIsString($dashboardShellCss);
        $this->assertHtmlNotContains('<style>', $head);
        $this->assertHtmlContains("@import './dashboard-shell.css';", $appCss);
        $this->assertHtmlContains('[v-cloak] {', $dashboardShellCss);
        $this->assertHtmlContains('@media print {', $dashboardShellCss);
    }

    public function test_root_uses_html_and_body_shell_attributes_from_legacy_dashboard_file(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('<html lang="id">', false);
        $response->assertSee('<body class="text-ppp-text antialiased">', false);
    }

    public function test_shell_builder_returns_vue_app_body_without_head_fragments(): void
    {
        $shell = app(MarketingDashboardShell::class);

        $fragments = $shell->build('https://backend.example.test');

        $this->assertSame(' lang="id"', $fragments['htmlAttributes']);
        $this->assertSame(' class="text-ppp-text antialiased"', $fragments['bodyAttributes']);
        $this->assertHtmlContains('<div id="app"', $fragments['bodyHtml']);
        $this->assertHtmlContains('v-cloak', $fragments['bodyHtml']);
        $this->assertHtmlContains('const { createApp, ref, computed, watch, onMounted, onBeforeUnmount, nextTick } = Vue;', $fragments['bodyHtml']);
        $this->assertFragmentHasKey('backendUrl', $fragments);
        $this->assertSame('https://backend.example.test', $fragments['backendUrl']);
        $this->assertFragmentNotHasKey('headHtml', $fragments);
        $this->assertFragmentNotHasKey('doctype', $fragments);
        $this->assertHtmlNotContains('window.MARKETING_BACKEND_URL=', $fragments['bodyHtml']);
        $this->assertHtmlNotContains('http-equiv="Cache-Control"', $fragments['bodyHtml']);
        $this->assertHtmlNotContains('http-equiv="Pragma"', $fragments['bodyHtml']);
    }

    public function test_shell_builder_splits_calendar_menu_markup_from_legacy_body(): void
    {
        $shell = app(MarketingDashboardShell::class);

        $fragments = $shell->build('https://backend.example.test');

        $this->assertFragmentHasKey('bodyBeforeCalendarMenu', $fragments);
        $this->assertFragmentHasKey('bodyAfterCalendarMenu', $fragments);
        $this->assertHtmlContains('<!-- Calendar View -->', $fragments['bodyBeforeCalendarMenu']);
        $this->assertHtmlContains('<!-- Story Schedule View -->', $fragments['bodyAfterCalendarMenu']);
        $this->assertHtmlNotContains('<div v-if="activeTab === \'calendar\'" class="space-y-6 animate-fadeIn pb-10">', $fragments['bodyBeforeCalendarMenu']);
        $this->assertHtmlNotContains('<div v-if="activeTab === \'calendar\'" class="space-y-6 animate-fadeIn pb-10">', $fragments['bodyAfterCalendarMenu']);
        $this->assertHtmlNotContains('Jadwal publikasi konten bulanan', $fragments['bodyBeforeCalendarMenu']);
        $this->assertHtmlNotContains('Jadwal publikasi konten bulanan', $fragments['bodyAfterCalendarMenu']);
    }

    public function test_shell_builder_splits_story_menu_markup_from_legacy_body(): void
    {
        $shell = app(MarketingDashboardShell::class);

        $fragments = $shell->build('https://backend.example.test');

        $this->assertFragmentHasKey('bodyBeforeStoryMenu', $fragments);
        $this->assertFragmentHasKey('bodyAfterStoryMenu', $fragments);
        $this->assertHtmlContains('<!-- Story Schedule View -->', $fragments['bodyBeforeStoryMenu']);
        $this->assertHtmlContains('<!-- Analisa Insight & Tren View -->', $fragments['bodyAfterStoryMenu']);
        $this->assertHtmlNotContains('<div v-if="activeTab === \'story\'" class="space-y-6 animate-fadeIn pb-10">', $fragments['bodyBeforeStoryMenu']);
        $this->assertHtmlNotContains('<div v-if="activeTab === \'story\'" class="space-y-6 animate-fadeIn pb-10">', $fragments['bodyAfterStoryMenu']);
        $this->assertHtmlNotContains('Pengaturan jadwal story harian Instagram &', $fragments['bodyBeforeStoryMenu']);
        $this->assertHtmlNotContains('Pengaturan jadwal story harian Instagram &', $fragments['bodyAfterStoryMenu']);
    }

    public function test_shell_builder_splits_ideation_menu_markup_from_legacy_body(): void
    {
        $shell = app(MarketingDashboardShell::class);

        $fragments = $shell->build('https://backend.example.test');

        $this->assertFragmentHasKey('bodyBeforeIdeationMenu', $fragments);
        $this->assertFragmentHasKey('bodyAfterIdeationMenu', $fragments);
        $this->assertFragmentHasKey('bodyBeforeMasterPlanMenu', $fragments);
        $this->assertFragmentHasKey('bodyAfterMasterPlanMenu', $fragments);
        $this->assertFragmentHasKey('bodyBeforeDashboardMenu', $fragments);
        $this->assertFragmentHasKey('bodyAfterDashboardMenu', $fragments);
        $this->assertEmpty($fragments['bodyBeforeMasterPlanMenu']);
        $this->assertStringStartsWith('<!-- Ideation View -->', ltrim($fragments['bodyAfterMasterPlanMenu']));
        $this->assertHtmlContains('<!-- Distribution View -->', $fragments['bodyAfterIdeationMenu']);
        $this->assertHtmlNotContains('<div v-if="activeTab === \'master\'" class="space-y-6 animate-fadeIn pb-10">', $fragments['bodyBeforeIdeationMenu']);
        $this->assertHtmlNotContains('<div v-if="activeTab === \'ideation\'" class="space-y-6 animate-fadeIn pb-10">', $fragments['bodyBeforeIdeationMenu']);
        $this->assertHtmlNotContains('<div v-if="activeTab === \'ideation\'" class="space-y-6 animate-fadeIn pb-10">', $fragments['bodyAfterIdeationMenu']);
        $this->assertHtmlNotContains('Brainstorming ide konten dan tracking', $fragments['bodyBeforeIdeationMenu']);
        $this->assertHtmlNotContains('Brainstorming ide konten dan tracking', $fragments['bodyAfterIdeationMenu']);
    }

    public function test_shell_builder_splits_distribution_menu_markup_from_legacy_body(): void
    {
        $shell = app(MarketingDashboardShell::class);

        $fragments = $shell->build('https://backend.example.test');

        $this->assertFragmentHasKey('bodyBeforeDistributionMenu', $fragments);
        $this->assertFragmentHasKey('bodyAfterDistributionMenu', $fragments);
        $this->assertHtmlContains('<!-- Distribution View -->', $fragments['bodyBeforeDistributionMenu']);
        $this->assertHtmlContains('<!-- Analytics View -->', $fragments['bodyAfterDistributionMenu']);
        $this->assertHtmlNotContains('<div v-if="activeTab === \'distribution\'" class="space-y-6 animate-fadeIn pb-10">', $fragments['bodyBeforeDistributionMenu']);
        $this->assertHtmlNotContains('<div v-if="activeTab === \'distribution\'" class="space-y-6 animate-fadeIn pb-10">', $fragments['bodyAfterDistributionMenu']);
        $this->assertHtmlNotContains('Monitoring penyebaran konten di berbagai', $fragments['bodyBeforeDistributionMenu']);
        $this->assertHtmlNotContains('Monitoring penyebaran konten di berbagai', $fragments['bodyAfterDistributionMenu']);
    }

    public function test_shell_builder_splits_analytics_menu_markup_from_legacy_body(): void
    {
        $shell = app(MarketingDashboardShell::class);

        $fragments = $shell->build('https://backend.example.test');

        $this->assertFragmentHasKey('bodyBeforeAnalyticsMenu', $fragments);
        $this->assertFragmentHasKey('bodyAfterAnalyticsMenu', $fragments);
        $this->assertHtmlContains('<!-- Analytics View -->', $fragments['bodyBeforeAnalyticsMenu']);
        $this->assertHtmlContains('<!-- Calendar View -->', $fragments['bodyAfterAnalyticsMenu']);
        $this->assertHtmlNotContains('<div v-if="activeTab === \'analytics\'" class="space-y-6 animate-fadeIn pb-10">', $fragments['bodyBeforeAnalyticsMenu']);
        $this->assertHtmlNotContains('<div v-if="activeTab === \'analytics\'" class="space-y-6 animate-fadeIn pb-10">', $fragments['bodyAfterAnalyticsMenu']);
        $this->assertHtmlNotContains('Analisis performa konten berdasarkan', $fragments['bodyBeforeAnalyticsMenu']);
        $this->assertHtmlNotContains('Analisis performa konten berdasarkan', $fragments['bodyAfterAnalyticsMenu']);
    }

    public function test_shell_builder_splits_analytics_pdf_cluster_from_legacy_body_script(): void
    {
        $shell = app(MarketingDashboardShell::class);

        $fragments = $shell->build('https://backend.example.test');

        $this->assertFragmentHasKey('bodyBeforeAnalyticsPdfCluster', $fragments);
        $this->assertFragmentHasKey('bodyAfterAnalyticsPdfCluster', $fragments);
        $this->assertHtmlContains('}).exportToExcel(activeTab.value, currentData);', $fragments['bodyBeforeAnalyticsPdfCluster']);
        $this->assertHtmlContains('const formatShortDate = (dateStr) => {', $fragments['bodyAfterAnalyticsPdfCluster']);
        $this->assertHtmlNotContains('const exportAnalyticsToPDF = () => {', $fragments['bodyBeforeAnalyticsPdfCluster']);
        $this->assertHtmlNotContains('const exportAnalyticsToPDF = () => {', $fragments['bodyAfterAnalyticsPdfCluster']);
        $this->assertHtmlNotContains('const exportPdf = () => {', $fragments['bodyBeforeAnalyticsPdfCluster']);
        $this->assertHtmlNotContains('const exportPdf = () => {', $fragments['bodyAfterAnalyticsPdfCluster']);
    }

    public function test_shell_builder_splits_customer_service_pdf_cluster_from_legacy_body_script(): void
    {
        $shell = app(MarketingDashboardShell::class);

        $fragments = $shell->build('https://backend.example.test');

        $this->assertFragmentHasKey('bodyBeforeCustomerServicePdfCluster', $fragments);
        $this->assertFragmentHasKey('bodyAfterCustomerServicePdfCluster', $fragments);
        $this->assertHtmlContains('const deleteKeepBarang = (id) => {', $fragments['bodyBeforeCustomerServicePdfCluster']);
        $this->assertHtmlContains('const formatCurrency = (value) => {', $fragments['bodyAfterCustomerServicePdfCluster']);
        $this->assertHtmlNotContains('const buildUnitDitanyaGrouped_ = (rows) => {', $fragments['bodyBeforeCustomerServicePdfCluster']);
        $this->assertHtmlNotContains('const buildUnitDitanyaGrouped_ = (rows) => {', $fragments['bodyAfterCustomerServicePdfCluster']);
        $this->assertHtmlNotContains('const exportUnitDitanyaToExcel = async () => {', $fragments['bodyBeforeCustomerServicePdfCluster']);
        $this->assertHtmlNotContains('const exportUnitDitanyaToExcel = async () => {', $fragments['bodyAfterCustomerServicePdfCluster']);
        $this->assertHtmlNotContains('const exportUnitDitanyaToPDF = () => {', $fragments['bodyBeforeCustomerServicePdfCluster']);
        $this->assertHtmlNotContains('const exportClaimGaransiToPDF = () => {', $fragments['bodyAfterCustomerServicePdfCluster']);
        $this->assertHtmlNotContains('const exportKeepBarangToExcel = async () => {', $fragments['bodyBeforeCustomerServicePdfCluster']);
        $this->assertHtmlNotContains('const exportKeepBarangToExcel = async () => {', $fragments['bodyAfterCustomerServicePdfCluster']);
        $this->assertHtmlNotContains('const exportKeepBarangToPDF = () => {', $fragments['bodyBeforeCustomerServicePdfCluster']);
        $this->assertHtmlNotContains('const exportKeepBarangToPDF = () => {', $fragments['bodyAfterCustomerServicePdfCluster']);
    }

    public function test_root_uses_customer_service_export_bridge_for_pilot_menu_exports(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('window.MarketingDashboardCustomerServiceExports', $html);
        $this->assertHtmlContains('const getCustomerServiceExportBridge_ = () => {', $html);
        $this->assertHtmlContains('return bridge.exportUnitDitanyaToExcel({', $html);
        $this->assertHtmlContains('return bridge.exportUnitDitanyaToPDF({', $html);
        $this->assertHtmlContains('return bridge.exportClaimGaransiToExcel({', $html);
        $this->assertHtmlContains('return bridge.exportClaimGaransiToPDF({', $html);
        $this->assertHtmlContains('return bridge.exportKeepBarangToExcel({', $html);
        $this->assertHtmlContains('return bridge.exportKeepBarangToPDF({', $html);
        $this->assertHtmlNotContains('const buildUnitDitanyaGrouped_ = (rows) => {', $html);
        $this->assertHtmlNotContains("const exportData = grouped.map((g, idx) => ({\n                            'No': idx + 1,", $html);
        $this->assertHtmlNotContains("title: 'CLAIM GARANSI & ASURANSI',", $html);
        $this->assertHtmlNotContains("title: 'Keep Barang',", $html);
        $this->assertHtmlNotContains("'Type HP': normalizeKeepBarangTypeHpValue(r.TYPE_HP) || '',", $html);
    }

    public function test_root_uses_reporting_export_bridge_for_ads_log_pdf(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('window.MarketingDashboardReportingExports', $html);
        $this->assertHtmlContains('const getReportingExportBridge_ = () => {', $html);
        $this->assertHtmlContains('return bridge.exportAdsLogToPDF({', $html);
        $this->assertHtmlNotContains("title: 'ADS PERFORMANCE REPORT',", $html);
        $this->assertHtmlNotContains("openPrintWindow_(html, 'Ads Report');", $html);
    }

    public function test_root_uses_reporting_export_bridge_for_price_comparison_pdf(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('window.MarketingDashboardReportingExports', $html);
        $this->assertHtmlContains('return bridge.exportPriceComparisonToPDF({', $html);
        $this->assertHtmlNotContains("title: 'ANALISIS HARGA & KOMPETITOR',", $html);
        $this->assertHtmlNotContains("openPrintWindow_(html, 'Harga Kompetitor');", $html);
    }

    public function test_root_uses_reporting_export_bridge_for_lpjk_detail_pdf(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('window.MarketingDashboardReportingExports', $html);
        $this->assertHtmlContains('return bridge.exportLpjkDetailToPDF({', $html);
        $this->assertHtmlNotContains("title: 'LAPORAN PERTANGGUNGJAWABAN KEUANGAN',", $html);
        $this->assertHtmlNotContains('openPrintWindow_(html, `LPJK - ${lpjk.Nama_Event}`);', $html);
    }

    public function test_root_uses_reporting_export_bridge_for_budget_pdf(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('window.MarketingDashboardReportingExports', $html);
        $this->assertHtmlContains('return bridge.exportBudgetToPDF({', $html);
        $this->assertHtmlNotContains('<title>Budget Plan</title>', $html);
        $this->assertHtmlNotContains("openPrintWindow_(html, 'Budget Plan');", $html);
    }

    public function test_root_uses_shared_reporting_export_bridge_helper(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('const getReportingExportBridge_ = () => {', $html);
        $this->assertGreaterThanOrEqual(1, substr_count($html, 'window.MarketingDashboardReportingExports'));
        $this->assertGreaterThanOrEqual(1, substr_count($html, 'const bridge = getReportingExportBridge_();'));
    }

    public function test_root_uses_analytics_export_bridge_for_analytics_pdf(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('window.MarketingDashboardAnalyticsExports', $html);
        $this->assertHtmlContains('const getAnalyticsExportBridge_ = () => {', $html);
        $this->assertHtmlContains('return bridge.exportAnalyticsToPDF({', $html);
        $this->assertHtmlNotContains("title: 'ANALYTICS PERFORMANCE REPORT',", $html);
        $this->assertHtmlNotContains("openPrintWindow_(html, 'Analytics Performance');", $html);
    }

    public function test_root_uses_analytics_export_bridge_for_generic_tabular_pdf(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('window.MarketingDashboardAnalyticsExports', $html);
        $this->assertHtmlNotContains('title: title.toUpperCase(),', $html);
        $this->assertHtmlNotContains('openPrintWindow_(html, title);', $html);
    }

    public function test_root_uses_analytics_export_bridge_for_active_tab_pdf_dispatch(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('return bridge.exportActiveTabPdf({', $html);
        $this->assertHtmlNotContains("if (activeTab.value === 'unit_ditanya') { exportUnitDitanyaToPDF(); return; }", $html);
        $this->assertHtmlNotContains('const tabTitles = {', $html);
    }

    public function test_root_hardens_external_links_and_print_popup_url(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('target="_blank" rel="noopener noreferrer"', $html);
        $this->assertHtmlContains('pw.location.href = `${backendUrl}/print-job/${encodeURIComponent(token)}`;', $html);
        $this->assertHtmlNotContains('ngrok-skip-browser-warning=1', $html);
    }

    public function test_root_uses_sales_export_bridge_for_bonus_pdf(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('window.MarketingDashboardSalesExports', $html);
        $this->assertHtmlContains('const getSalesExportBridge_ = () => {', $html);
        $this->assertHtmlContains('return bridge.exportBonusToPDF({', $html);
        $this->assertHtmlNotContains("title: 'BONUS & PERFORMANCE PAYOUT REPORT',", $html);
        $this->assertHtmlNotContains("openPrintWindow_(html, 'Bonus Report');", $html);
    }

    public function test_root_uses_sales_export_bridge_for_promo_pdf(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('window.MarketingDashboardSalesExports', $html);
        $this->assertHtmlContains('return bridge.exportPromoToPDF({', $html);
        $this->assertHtmlNotContains("title: 'PROGRAM PROMO',", $html);
        $this->assertHtmlNotContains("openPrintWindow_(html, 'Program Promo');", $html);
    }

    public function test_root_uses_sales_export_bridge_for_sell_out_pdf(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('window.MarketingDashboardSalesExports', $html);
        $this->assertHtmlContains('return bridge.exportSellOutToPDF({', $html);
        $this->assertHtmlNotContains("title: 'TARGET VENDOR (SELL OUT)',", $html);
        $this->assertHtmlNotContains("openPrintWindow_(html, 'Target Vendor Sell Out');", $html);
    }

    public function test_shell_builder_splits_sales_and_promo_pdf_cluster_from_legacy_body_script(): void
    {
        $shell = app(MarketingDashboardShell::class);

        $fragments = $shell->build('https://backend.example.test');

        $this->assertFragmentHasKey('bodyBeforeSalesAndPromoPdfCluster', $fragments);
        $this->assertFragmentHasKey('bodyAfterSalesAndPromoPdfCluster', $fragments);
        $this->assertHtmlContains('const saveBonusConfig = () => {', $fragments['bodyBeforeSalesAndPromoPdfCluster']);
        $this->assertHtmlContains('watch([sellOutSearch, sellOutVendorFilter, sellOutMonth], () => { sellOutPage.value = 1; });', $fragments['bodyAfterSalesAndPromoPdfCluster']);
        $this->assertHtmlNotContains('const exportBonusToPDF = () => {', $fragments['bodyBeforeSalesAndPromoPdfCluster']);
        $this->assertHtmlNotContains('const exportPromoToPDF = () => {', $fragments['bodyAfterSalesAndPromoPdfCluster']);
        $this->assertHtmlNotContains('const exportSellOutToPDF = () => {', $fragments['bodyBeforeSalesAndPromoPdfCluster']);
    }

    public function test_shell_builder_splits_print_helpers_from_legacy_body_script(): void
    {
        $shell = app(MarketingDashboardShell::class);

        $fragments = $shell->build('https://backend.example.test');

        $this->assertFragmentHasKey('bodyBeforePrintHelpers', $fragments);
        $this->assertFragmentHasKey('bodyAfterPrintHelpers', $fragments);
        $this->assertHtmlContains('const adsSaldoPlatform = computed(() => {', $fragments['bodyBeforePrintHelpers']);
        $this->assertHtmlContains('// Ads Log PDF', $fragments['bodyAfterPrintHelpers']);
        $this->assertHtmlContains('const exportAdsLogToPDF = () => {', $fragments['bodyAfterPrintHelpers']);
        $this->assertHtmlNotContains('// PDF print helpers', $fragments['bodyBeforePrintHelpers']);
        $this->assertHtmlNotContains('const getPrintBaseStyles_ = () => {', $fragments['bodyBeforePrintHelpers']);
        $this->assertHtmlNotContains('const openPrintWindow_ = (html, reportName) => {', $fragments['bodyAfterPrintHelpers']);
    }

    public function test_shell_builder_splits_ads_log_pdf_export_from_remaining_legacy_script(): void
    {
        $shell = app(MarketingDashboardShell::class);

        $fragments = $shell->build('https://backend.example.test');

        $this->assertFragmentHasKey('bodyBeforeAdsLogPdfExport', $fragments);
        $this->assertFragmentHasKey('bodyAfterAdsLogPdfExport', $fragments);
        $this->assertSame('', trim($fragments['bodyBeforeAdsLogPdfExport']));
        $this->assertHtmlContains('// Harga & Kompetitor', $fragments['bodyAfterAdsLogPdfExport']);
        $this->assertHtmlContains('const filteredHargaKompetitorData = computed(() => {', $fragments['bodyAfterAdsLogPdfExport']);
        $this->assertHtmlNotContains('const exportAdsLogToPDF = () => {', $fragments['bodyBeforeAdsLogPdfExport']);
        $this->assertHtmlNotContains('const exportAdsLogToPDF = () => {', $fragments['bodyAfterAdsLogPdfExport']);
    }

    public function test_shell_builder_splits_price_comparison_pdf_export_from_remaining_legacy_script(): void
    {
        $shell = app(MarketingDashboardShell::class);

        $fragments = $shell->build('https://backend.example.test');

        $this->assertFragmentHasKey('bodyBeforePriceComparisonPdfExport', $fragments);
        $this->assertFragmentHasKey('bodyAfterPriceComparisonPdfExport', $fragments);
        $this->assertHtmlContains('const deleteHargaKompetitor = (id) => {', $fragments['bodyBeforePriceComparisonPdfExport']);
        $this->assertHtmlContains('// Laporan Event (LPJK)', $fragments['bodyAfterPriceComparisonPdfExport']);
        $this->assertHtmlContains('const filteredLpjkData = computed(() => {', $fragments['bodyAfterPriceComparisonPdfExport']);
        $this->assertHtmlNotContains('const exportPriceComparisonToPDF = () => {', $fragments['bodyBeforePriceComparisonPdfExport']);
        $this->assertHtmlNotContains('const exportPriceComparisonToPDF = () => {', $fragments['bodyAfterPriceComparisonPdfExport']);
    }

    public function test_shell_builder_splits_lpjk_detail_pdf_export_from_remaining_legacy_script(): void
    {
        $shell = app(MarketingDashboardShell::class);

        $fragments = $shell->build('https://backend.example.test');

        $this->assertFragmentHasKey('bodyBeforeLpjkDetailPdfExport', $fragments);
        $this->assertFragmentHasKey('bodyAfterLpjkDetailPdfExport', $fragments);
        $this->assertHtmlContains('const deleteLpjkDetail = (id) => {', $fragments['bodyBeforeLpjkDetailPdfExport']);
        $this->assertHtmlContains('// Budgeting', $fragments['bodyAfterLpjkDetailPdfExport']);
        $this->assertHtmlContains('const budgetCalculations = computed(() => {', $fragments['bodyAfterLpjkDetailPdfExport']);
        $this->assertHtmlNotContains('const exportLpjkDetailToPDF = () => {', $fragments['bodyBeforeLpjkDetailPdfExport']);
        $this->assertHtmlNotContains('const exportLpjkDetailToPDF = () => {', $fragments['bodyAfterLpjkDetailPdfExport']);
    }

    public function test_shell_builder_splits_budget_pdf_export_from_remaining_legacy_script(): void
    {
        $shell = app(MarketingDashboardShell::class);

        $fragments = $shell->build('https://backend.example.test');

        $this->assertFragmentHasKey('bodyBeforeBudgetPdfExport', $fragments);
        $this->assertFragmentHasKey('bodyAfterBudgetPdfExport', $fragments);
        $this->assertHtmlContains('const saveBudgetServer = () => {', $fragments['bodyBeforeBudgetPdfExport']);
        $this->assertHtmlContains('const exportBudgetToExcel = () => {', $fragments['bodyAfterBudgetPdfExport']);
        $this->assertHtmlNotContains('const exportBudgetToPDF = () => {', $fragments['bodyBeforeBudgetPdfExport']);
        $this->assertHtmlNotContains('const exportBudgetToPDF = () => {', $fragments['bodyAfterBudgetPdfExport']);
    }

    public function test_dashboard_shell_does_not_embed_mockup_business_data(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        foreach ([
            'Carousel Tips Beli HP Bekas',
            'Promo iPhone',
            'Unboxing Samsung A Series',
            'Komang',
            'Samsung A55',
            'Roadshow Kampus',
            'Ads Promo iPhone',
            'Vendor A',
            'Administrator',
        ] as $mockValue) {
            $response->assertDontSee($mockValue, false);
        }
    }

    public function test_dashboard_contains_legacy_navigation_labels(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        foreach ([
            'Dashboard',
            'Master Plan',
            'Unboxing',
            'Distribution',
            'Analytics',
            'Program Promo',
            'Sell Out Target',
            'Ads Log',
            'Budgeting',
            'Order Online',
            'Unit Ditanya',
            'Claim Garansi',
            'Harga Kompetitor',
            'Laporan Event',
            'Pengaturan',
            'Nama Stock',
            'Bonus Report',
            'Talent Bonus',
            'Editor Performance',
            'Keep Barang',
        ] as $label) {
            $response->assertSee($label, false);
        }
    }

    public function test_master_plans_api_returns_database_rows_for_legacy_frontend(): void
    {
        DB::table('master_plans')->insert([
            'source_id' => 'Konten-API-001',
            'title' => 'Konten Dari Database',
            'format_konten' => 'REELS',
            'platforms' => 'Instagram',
            'colab' => null,
            'editor' => 'Agus',
            'talent' => 'Talent Konten, Talent Pendamping',
            'script' => 'Script database',
            'caption' => 'Caption database',
            'status' => 'PUBLISHED',
            'tanggal_rencana' => '2026-06-25',
            'distribution_meta' => '{"Instagram":{"link":"https://example.test","date":"2026-06-25","type":"Regular"}}',
            'link_drive' => 'https://drive.google.com/database-row',
            'raw_payload' => json_encode(['ID' => 'Konten-API-001']),
            'imported_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/master-plans');

        $response->assertOk()
            ->assertJsonPath('data.0.ID', 'Konten-API-001')
            ->assertJsonPath('data.0.Judul', 'Konten Dari Database')
            ->assertJsonPath('data.0.Format_Konten', 'REELS')
            ->assertJsonPath('data.0.Tanggal_Rencana', '2026-06-25')
            ->assertJsonPath('data.0.Talent', 'Talent Konten, Talent Pendamping');
    }

    public function test_settings_api_returns_database_setting_groups(): void
    {
        DB::table('marketing_settings')->insert([
            'key' => 'Format_Konten',
            'values' => json_encode(['REELS', 'STORY'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'imported_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/settings');

        $response->assertOk()
            ->assertJsonPath('data.Format_Konten.0', 'REELS')
            ->assertJsonPath('data.Format_Konten.1', 'STORY');
    }

    public function test_settings_api_can_return_talent_options(): void
    {
        DB::table('marketing_settings')->insert([
            'key' => 'Talent',
            'values' => json_encode(['Talent A', 'Talent B'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'imported_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/settings');

        $response->assertOk()
            ->assertJsonPath('data.Talent.0', 'Talent A')
            ->assertJsonPath('data.Talent.1', 'Talent B');
    }

    public function test_settings_tab_loads_database_settings_when_opened(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains("newTab === 'settings'", $html);
        $this->assertHtmlContains('loadSettings();', $html);
    }

    public function test_dropdown_settings_are_bootstrapped_before_settings_tab_is_opened(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('const settingsLoaded = ref(false);', $html);
        $this->assertHtmlContains('if (currentUser.value && !settingsLoaded.value && newTab !== \'dashboard\') {', $html);
        $this->assertHtmlNotContains('await loadSettings();'."\n".'                        const bootstrap = await new Promise', $html);
    }

    public function test_master_plan_and_settings_shell_include_talent_controls(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains("'Talent'", $html);
        $this->assertHtmlContains('masterForm.Talent', $html);
        $this->assertHtmlContains('filteredTalentOptions', $html);
    }

    public function test_bonus_shell_includes_talent_bonus_tab_and_computed_data(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains("switchTab('talent_bonus')", $html);
        $this->assertHtmlContains("activeTab === 'talent_bonus'", $html);
        $this->assertHtmlContains("talent_bonus: { label: 'Talent Bonus', category: 'Performa & Bonus' }", $html);
        $this->assertHtmlContains('const talentBonusRows = computed(() => {', $html);
        $this->assertHtmlContains('const talentDashboardData = computed(() => {', $html);
    }

    public function test_talent_bonus_uses_master_plan_as_source_of_truth(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('return masterPlanData.value', $html);
        $this->assertHtmlContains('const distByMaster = new Map();', $html);
        $this->assertHtmlContains("const rawDate = item.Tanggal_Rencana || '';", $html);
        $this->assertHtmlNotContains('return filteredBonusRows.value.flatMap((row) => {', $html);
    }

    public function test_talent_bonus_shell_includes_daily_carry_over_rules(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('const TALENT_DAILY_BONUS = 150000;', $html);
        $this->assertHtmlContains('const buildTalentDailyRows = (rows) => {', $html);
        $this->assertHtmlContains('const effectiveCount = dayRows.length + carryCount;', $html);
        $this->assertHtmlContains('carryCount = effectiveCount > 2 && effectiveCount % 2 === 1 ? 1 : 0;', $html);
    }

    public function test_bonus_tabs_refresh_master_plan_and_related_sources(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('const refreshBonusSourceData = () => Promise.allSettled([', $html);
        $this->assertHtmlContains("if (['bonus_report', 'talent_bonus', 'editor_performance'].includes(newTab)) {", $html);
        $this->assertHtmlContains('refreshBonusSourceData();', $html);
    }

    public function test_web_bootstrap_checks_dashboard_session_before_loading_protected_settings(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlNotContains('await loadSettings();'."\n".'                        const bootstrap = await new Promise', $html);
        $this->assertHtmlContains('const bootstrap = await new Promise((resolve, reject) => {', $html);
    }

    public function test_tab_data_loader_requires_authenticated_user_before_fetching_protected_tab_payloads(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('if (currentUser.value && dataKey) loadTabData(dataKey);', $html);
    }

    public function test_distribution_and_analytics_tabs_load_database_rows_when_opened(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains("newTab === 'distribution'", $html);
        $this->assertHtmlContains('loadDistributionData();', $html);
        $this->assertHtmlContains("newTab === 'analytics'", $html);
        $this->assertHtmlContains('loadAnalyticsData();', $html);
    }

    public function test_meta_analytics_tabs_include_actionable_insight_sections(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('Insight Story yang Bisa Dipakai', $html);
        $this->assertHtmlContains('Insight Feed yang Bisa Dipakai', $html);
        $this->assertHtmlContains('Peringkat Akun Feed', $html);
        $this->assertHtmlContains('const metaStoryInsights = computed(() => {', $html);
        $this->assertHtmlContains('const metaFeedInsights = computed(() => {', $html);
        $this->assertHtmlContains('const metaFeedAccountLeaderboard = computed(() => {', $html);
    }

    public function test_meta_story_trend_uses_average_per_content(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('Average Views & Reach per Konten', $html);
        $this->assertHtmlContains("const metaStoryDaily = computed(() => _metaDaily(filteredMetaStory.value, 'average'));", $html);
        $this->assertHtmlContains('const _metaChartReady = (el) => {', $html);
        $this->assertHtmlContains('const _renderMetaChart = (id, el, options, attempt = 0) => {', $html);
        $this->assertHtmlContains('const _metaChartOptionsWithDimensions = (el, options) => {', $html);
        $this->assertHtmlContains('const _metaChartTokens = {};', $html);
    }

    public function test_meta_story_top_list_and_table_use_updated_limits_and_pagination(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains("const metaStoryTop = computed(() => [...filteredMetaStory.value].sort((a, b) => (Number(b.views) || 0) - (Number(a.views) || 0)).slice(0, 5));", $html);
        $this->assertHtmlContains('const metaStoryTotalPages = computed(() => Math.max(1, Math.ceil(filteredMetaStory.value.length / PAGE_SIZE)));', $html);
        $this->assertHtmlContains('const pagedMetaStory = computed(() => filteredMetaStory.value.slice((metaStoryPage.value - 1) * PAGE_SIZE, metaStoryPage.value * PAGE_SIZE));', $html);
        $this->assertHtmlContains('@click="metaStoryPage++" :disabled="metaStoryPage >= metaStoryTotalPages" aria-label="Halaman berikutnya"', $html);
    }

    public function test_meta_analytics_tabs_include_folder_import_and_monthly_summary_sections(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('Import Folder', $html);
        $this->assertHtmlContains('Ringkasan Bulanan per Akun', $html);
        $this->assertHtmlContains('const metaStoryMonthlySummary = computed(() => _metaMonthlySummary', $html);
        $this->assertHtmlContains('const metaFeedMonthlySummary = computed(() => _metaMonthlySummary', $html);
        $this->assertHtmlContains("const importMetaFolder = (dataset) => {", $html);
    }

    public function test_meta_import_flow_requests_confirmation_before_overwrite(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains("importMetaStory(rows, options = {})", $html);
        $this->assertHtmlContains("importMetaFeed(rows, options = {})", $html);
        $this->assertHtmlContains("importMetaStoryFolder(options = {})", $html);
        $this->assertHtmlContains("importMetaFeedFolder(options = {})", $html);
        $this->assertHtmlContains("overwrite: !!options.overwrite", $html);
        $this->assertHtmlContains('const handleMetaImportResult = (dataset, result, retryImport, successMessage) => {', $html);
        $this->assertHtmlContains('if (r.requires_confirmation) {', $html);
        $this->assertHtmlContains("showConfirm(", $html);
        $this->assertHtmlContains('Data Meta Sudah Ada', $html);
    }

    public function test_meta_feed_toolbar_uses_consistent_custom_select_shell(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains("toggleSearchSelect(\$event, 'meta_feed_account')", $html);
        $this->assertHtmlContains('.select-trigger-button-form:hover,', $html);
        $this->assertHtmlContains('.select-trigger-button-form-tight:focus-visible,', $html);
        $this->assertHtmlContains('.select-trigger-button-compact:focus-visible {', $html);
        $this->assertHtmlContains('class="relative search-select-container sm:w-44"', $html);
        $this->assertHtmlContains("searchSelectOpen === 'meta_feed_account'", $html);
        $this->assertHtmlContains("metaFeedAccount || 'Semua Akun'", $html);
    }

    public function test_meta_story_sidebar_menu_uses_visible_solid_icon(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('fa-solid fa-clapperboard text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110', $html);
        $this->assertHtmlNotContains('fa-brands fa-instagram text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110', $html);
    }

    public function test_meta_story_and_feed_place_summary_cards_above_header_card(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $storySummaryPos = strpos($html, "v-for=\"c in metaStorySummary.cards\"");
        $storyHeaderPos = strpos($html, 'Story IG Analytics');
        $feedSummaryPos = strpos($html, "v-for=\"c in metaFeedSummary.cards\"");
        $feedHeaderPos = strpos($html, 'Feed Konten Analytics');

        $this->assertNotFalse($storySummaryPos);
        $this->assertNotFalse($storyHeaderPos);
        $this->assertNotFalse($feedSummaryPos);
        $this->assertNotFalse($feedHeaderPos);
        $this->assertTrue($storySummaryPos < $storyHeaderPos);
        $this->assertTrue($feedSummaryPos < $feedHeaderPos);
    }

    public function test_sell_out_searchable_dropdowns_use_select_trigger_pattern(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains("toggleSearchSelect(\$event, 'sellOutVendor')", $html);
        $this->assertHtmlContains("toggleSearchSelect(\$event, 'sellOutMonth')", $html);
        $this->assertHtmlContains('class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form"', $html);
        $this->assertHtmlContains("fa-solid fa-chevron-down ml-auto text-[9px] text-slate-400", $html);
    }

    public function test_unit_and_claim_searchable_dropdowns_use_select_trigger_pattern(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains("toggleSearchSelect(\$event, 'filter_available')", $html);
        $this->assertHtmlContains("toggleSearchSelect(\$event, 'filter_claim_status')", $html);
        $this->assertHtmlContains("toggleSearchSelect(\$event, 'filter_claim_garansi')", $html);
        $this->assertHtmlContains("unitDitanyaAvailableFilter = ''", $html);
        $this->assertHtmlContains("claimGaransiStatusFilter = ''", $html);
        $this->assertHtmlContains("claimGaransiGaransiFilter = ''", $html);
    }

    public function test_distribution_and_analytics_start_without_date_filter(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains("const commonDateFilter = ref({ start: '', end: '' });", $html);
    }

    public function test_all_dashboard_tables_use_10px_headers_and_9px_body(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('#app table thead,', $html);
        $this->assertHtmlContains('#app table thead * {', $html);
        $this->assertHtmlContains('font-size: 10px !important;', $html);
        $this->assertHtmlContains('#app table thead tr {', $html);
        $this->assertHtmlContains('background: rgb(248 250 252 / 0.72) !important;', $html);
        $this->assertHtmlContains('#app table th {', $html);
        $this->assertHtmlContains('padding: 8px 12px !important;', $html);
        $this->assertHtmlContains('#app table tbody,', $html);
        $this->assertHtmlContains('#app table tbody *,', $html);
        $this->assertHtmlContains('#app table tfoot,', $html);
        $this->assertHtmlContains('font-size: 9px !important;', $html);
        $this->assertHtmlContains('#app table tbody tr:nth-child(even) {', $html);
        $this->assertHtmlContains('#app table td {', $html);
        $this->assertHtmlContains('padding: 6px 12px !important;', $html);
    }

    public function test_dashboard_tables_use_shared_sortable_headers(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('.table-sortable {', $html);
        $this->assertHtmlContains('.table-sortable.table-sort-asc::after {', $html);
        $this->assertHtmlContains('.table-sortable.table-sort-desc::after {', $html);
        $this->assertHtmlContains('const hydrateSortableTableHeaders = () => {', $html);
        $this->assertHtmlContains('const sortTableDomRows = (headerCell) => {', $html);
        $this->assertHtmlContains("nextTick(() => hydrateSortableTableHeaders());", $html);
    }

    public function test_ideation_kanban_caps_font_size_at_10px(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('.ideation-kanban-board,', $html);
        $this->assertHtmlContains('.ideation-kanban-board * {', $html);
        $this->assertHtmlContains('class="ideation-kanban-board grid grid-cols-1 md:grid-cols-3 gap-6 items-start"', $html);
    }

    public function test_dashboard_write_actions_use_laravel_crud_api(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        foreach ([
            'jsonApi',
            '/api/master-plans',
            '/api/distributions',
            '/api/analytics',
            '/api/settings',
            "'POST'",
            "'PUT'",
            "method: 'DELETE'",
            'X-XSRF-TOKEN',
        ] as $needle) {
            $this->assertHtmlContains($needle, $html);
        }
    }

    public function test_nama_stock_uses_laravel_raw_sheet_api(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('/api/raw-sheets/Nama_Stock', $html);
        $this->assertHtmlContains('getNamaStockData()', $html);
        $this->assertHtmlContains('saveNamaStockRows(rows)', $html);
    }

    public function test_nama_stock_form_uses_dropdowns_for_kategori_brand_and_manual_input_for_seri(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        foreach ([
            'namaStockKategoriOptions',
            'namaStockBrandOptions',
            "toggleSearchSelect(\$event, 'nama_stock_kategori')",
            "toggleSearchSelect(\$event, 'nama_stock_brand')",
            'v-model="searchSelectQuery"',
            'Pilih Kategori',
            'Pilih Brand',
            'v-model.trim="namaStockForm.SERI"',
            'placeholder="Ketik seri"',
        ] as $needle) {
            $this->assertHtmlContains($needle, $html);
        }

        $this->assertHtmlNotContains("toggleSearchSelect(\$event, 'nama_stock_seri')", $html);
    }

    public function test_other_seri_selectors_still_source_options_from_nama_stock_master(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        foreach ([
            "const getSeriOptions = (kat, brand) => {",
            "const nsSeriOptions = computed(() => getSeriOptions(unitDitanyaForm.value['KATEGORI'], unitDitanyaForm.value['BRAND']));",
            "getSeriOptions(sellOutForm.Kategori, sellOutForm.Brand)",
            "searchSelectOpen === 'unit_seri'",
            "searchSelectOpen === 'sotSeri'",
        ] as $needle) {
            $this->assertHtmlContains($needle, $html);
        }
    }

    public function test_nama_stock_view_uses_kategori_and_brand_filter_dropdowns(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        foreach ([
            "const namaStockKategoriFilter = ref('');",
            "const namaStockBrandFilter = ref('');",
            "const namaStockFilterKategoriOptions = computed(() => filteredUniqueFrom(namaStockRows.value, 'KATEGORI'));",
            "const namaStockFilterBrandOptions = computed(() => filteredUniqueFrom(namaStockRows.value, 'BRAND', { KATEGORI: namaStockKategoriFilter.value }));",
            "searchSelectOpen === 'nama_stock_filter_kategori'",
            "searchSelectOpen === 'nama_stock_filter_brand'",
            "namaStockKategoriFilter ? 'text-slate-800 font-medium' : 'text-slate-400'",
            "namaStockBrandFilter ? 'text-slate-800 font-medium' : 'text-slate-400'",
            "return namaStockRows.value.filter(row => {",
            "const matchKategori = !namaStockKategoriFilter.value || String(row.KATEGORI || '').trim().toUpperCase() === String(namaStockKategoriFilter.value || '').trim().toUpperCase();",
            "const matchBrand = !namaStockBrandFilter.value || String(row.BRAND || '').trim().toUpperCase() === String(namaStockBrandFilter.value || '').trim().toUpperCase();",
        ] as $needle) {
            $this->assertHtmlContains($needle, $html);
        }
    }

    public function test_keep_barang_type_hp_selector_sources_options_from_nama_stock_master(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains("searchSelectOpen === 'keep_type_hp'", $html);
        $this->assertHtmlContains(<<<'HTML'
const keepBarangTypeHpOptions = computed(() => {
                    const masterTypeOptions = namaStockRows.value
                        .map(row => buildStockNameLabel(row))
                        .filter(Boolean);
                    if (masterTypeOptions.length > 0) return mergeOptionValues(masterTypeOptions);
                    return mergeOptionValues(
                        uniqueMultiKeyFrom(keepBarangData.value, ['TYPE_HP'])
                    );
                });
HTML, $html);
        $this->assertHtmlContains("const normalizeKeepBarangTypeHpValue = (value) => {", $html);
        $this->assertHtmlContains("const exactMasterMatch = namaStockRows.value.find(row => buildStockNameLabel(row).toUpperCase() === raw);", $html);
        $this->assertHtmlContains("const seriMatches = namaStockRows.value.filter(row => String(row.SERI || '').trim().toUpperCase() === raw);", $html);
    }

    public function test_keep_barang_type_hp_selector_uses_accessible_button_and_empty_state(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('<button type="button" @click="toggleSearchSelect($event, \'keep_type_hp\')"', $html);
        $this->assertHtmlContains("v-if=\"keepBarangTypeHpOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0\"", $html);
        $this->assertHtmlContains('Belum ada opsi Type HP', $html);
    }

    public function test_open_keep_barang_modal_loads_nama_stock_master_when_needed(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains("if (!namaStockLoaded.value) loadNamaStockData();", $html);
        $this->assertHtmlContains("TYPE_HP: normalizeKeepBarangTypeHpValue(row.TYPE_HP)", $html);
    }

    public function test_customer_service_save_handlers_prevent_double_submit_and_validate_required_fields(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        foreach ([
            "const saveOrderanOnline = () => {",
            "const saveUnitDitanya = () => {",
            "const saveClaimGaransi = () => {",
            "const saveKeepBarang = () => {",
            "const saveSellOut = () => {",
        ] as $needle) {
            $this->assertHtmlContains($needle, $html);
        }

        $this->assertGreaterThanOrEqual(5, substr_count($html, 'if (submitting.value) return;'));
        $this->assertHtmlContains("if (!orderanOnlineForm.value.NAMA || !orderanOnlineForm.value['TYPE UNIT']) { showNotification('Nama customer dan type unit wajib diisi'); return; }", $html);
        $this->assertHtmlContains("if (!unitDitanyaForm.value.KATEGORI || !unitDitanyaForm.value.BRAND || !unitDitanyaForm.value.SERI) { showNotification('Kategori, brand, dan seri wajib diisi'); return; }", $html);
        $this->assertHtmlContains("if (!claimGaransiForm.value.NAMA_CUSTOMER || !claimGaransiForm.value.TIPE) { showNotification('Nama customer dan tipe wajib diisi'); return; }", $html);
        $this->assertHtmlContains("if (!form.NAMA || !form.NOMOR_HP || !form.TYPE_HP) { showNotification('Nama, nomor HP, dan Type HP wajib diisi'); return; }", $html);
    }

    public function test_sell_out_default_month_range_uses_valid_end_date_format(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains("const lastDay = String(new Date(year, month + 1, 0).getDate()).padStart(2, '0');", $html);
        $this->assertHtmlContains(<<<'HTML'
end: `${year}-${String(month + 1).padStart(2, '0')}-${lastDay}`
HTML, $html);
        $this->assertHtmlNotContains(<<<'HTML'
const end = `${year}-${String(new Date(year, month + 1, 0).getDate()).padStart(2, '0')}`;
HTML, $html);
    }

    public function test_customer_service_search_selectors_use_accessible_buttons(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        foreach ([
            '<button type="button" @click="toggleSearchSelect($event, \'ecommerce\')"',
            '<button type="button" @click="toggleSearchSelect($event, \'orderan_type_unit\')"',
            '<button type="button" @click="toggleSearchSelect($event, \'unit_kategori\')"',
            '<button type="button" @click="toggleSearchSelect($event, \'unit_tipe\')"',
            '<button type="button" @click="toggleSearchSelect($event, \'claim_tipe\')"',
            '<button type="button" @click="toggleSearchSelect($event, \'claim_seri\')"',
            '<button type="button" @click="toggleSearchSelect($event, \'keep_handle_by\')"',
            '<button type="button" @click="toggleSearchSelect($event, \'keep_team_gudang\')"',
            ":aria-expanded=\"searchSelectOpen === 'ecommerce' ? 'true' : 'false'\"",
            ":aria-expanded=\"searchSelectOpen === 'unit_kategori' ? 'true' : 'false'\"",
            ":aria-expanded=\"searchSelectOpen === 'claim_tipe' ? 'true' : 'false'\"",
            ":aria-expanded=\"searchSelectOpen === 'keep_handle_by' ? 'true' : 'false'\"",
        ] as $needle) {
            $this->assertHtmlContains($needle, $html);
        }
    }

    public function test_customer_service_modals_preload_nama_stock_master_when_needed(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('const openOrderanOnlineModal = (type = \'create\', row = null) => {', $html);
        $this->assertHtmlContains('const openClaimGaransiModal = (type = \'create\', row = null) => {', $html);
        $this->assertSame(4, substr_count($html, "if (!namaStockLoaded.value) loadNamaStockData();"));
    }

    public function test_master_plan_links_render_drive_and_normalized_distribution_meta(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('const normalizeMasterPlanRow = (item = {}) => {', $html);
        $this->assertHtmlContains("normalizedKey === 'contentType'", $html);
        $this->assertHtmlContains('return normalizeMasterPlanRows(payload.data);', $html);
        $this->assertHtmlContains('v-if="item.Link_Drive"', $html);
        $this->assertHtmlContains('hasAnyMasterLink(item)', $html);
    }

    public function test_master_plan_search_input_is_bound_to_returned_state(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('const masterSearch = ref("");', $html);
        $this->assertHtmlContains('<input v-model="masterSearch" type="text" placeholder="Cari judul atau colab..."', $html);
        $this->assertHtmlContains('masterSearch,', $html);
    }

    public function test_settings_and_master_plan_use_runner_via_is_web_proxy(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('if (!ensureRunApi().isWebProxy) {', $html);
        $this->assertHtmlContains('.getSettings();', $html);
        $this->assertHtmlContains('.getMasterPlanData();', $html);
    }

    public function test_ideation_board_card_has_delete_button(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('@click.stop="deleteMasterPlan(item.ID)"', $html);
        $this->assertHtmlContains("class=\"w-6 h-6 rounded-full bg-rose-50 border border-rose-100 text-rose-400", $html);
    }

    public function test_ideation_board_card_shows_idea_age(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('const getIdeaAgeLabel = (item) => {', $html);
        $this->assertHtmlContains("return `Umur \${diffDays}h`;", $html);
        $this->assertHtmlContains('{{ getIdeaAgeLabel(item) }}', $html);
    }

    public function test_content_calendar_renders_content_story_and_event_lists_without_content_slice(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains("getCalendarItems(day).filter(i => i.TYPE === 'content')", $html);
        $this->assertHtmlContains("getCalendarItems(day).filter(i => i.TYPE === 'story')", $html);
        $this->assertHtmlContains("getCalendarItems(day).filter(i => i.TYPE === 'event')", $html);
        $this->assertHtmlContains("}).map(s => ({ ...s, TYPE: 'story' }));", $html);
        $this->assertHtmlNotContains(".filter(i => i.TYPE === 'content').slice(0, 3)", $html);
    }

    public function test_content_calendar_uses_distinct_colors_for_content_story_and_hari_raya(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('bg-blue-50 border border-blue-100', $html);
        $this->assertHtmlContains('bg-rose-50 border border-rose-100', $html);
        $this->assertHtmlContains('bg-amber-50', $html);
        $this->assertHtmlContains('>Konten</span>', $html);
        $this->assertHtmlContains('>Story</span>', $html);
        $this->assertHtmlContains('>Hari Raya</span>', $html);
    }

    public function test_content_calendar_date_cells_grow_with_item_count_without_inner_scroll(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('class="min-h-[140px] bg-slate-50/50 rounded-2xl border border-dashed border-slate-100"', $html);
        $this->assertHtmlContains(":class=\"['min-h-[140px] rounded-2xl border p-3 transition-all group relative cursor-pointer'", $html);
        $this->assertHtmlContains('<div class="space-y-1.5">', $html);
        $this->assertHtmlNotContains('overflow-y-auto pr-0.5 custom-scrollbar max-h-[calc(100%-24px)]', $html);
        $this->assertHtmlNotContains('aspect-square md:aspect-video rounded-2xl border p-2 md:p-3', $html);
    }

    public function test_calendar_day_modal_cards_use_compact_spacing(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains("['p-3 rounded-xl border transition-all'", $html);
        $this->assertHtmlContains('class="flex items-start justify-between gap-2 mb-1.5"', $html);
        $this->assertHtmlContains('class="text-[12px] font-bold text-slate-900 leading-[1.2] mb-1 uppercase"', $html);
        $this->assertHtmlContains('class="mt-2.5 pt-2 border-t border-slate-50 flex items-center justify-end"', $html);
        $this->assertHtmlNotContains("['p-4 rounded-2xl border transition-all'", $html);
    }

    public function test_desktop_form_modals_are_horizontally_centered(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('class="fixed inset-0 z-[2500] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet"', $html);
        $this->assertHtmlContains('class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet"', $html);
        $this->assertHtmlNotContains('class="fixed inset-0 z-[2500] flex items-end md:items-center md:p-4"', $html);
    }

    public function test_notifications_are_type_aware_with_icon_and_tone(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('const notification = ref({ open: false, message: \'\', type: \'success\', icon: \'fa-circle-check\' });', $html);
        $this->assertHtmlContains("notification.type === 'error'", $html);
        $this->assertHtmlContains("notification.type === 'warning'", $html);
        $this->assertHtmlContains('<i :class="[\'fa-solid text-[12px]\', notification.icon]"></i>', $html);
    }

    public function test_long_form_modals_use_sticky_mobile_safe_footers(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('.modal-footer-bar {', $html);
        $this->assertHtmlContains('.radius-sheet-bottom {', $html);
        $this->assertHtmlContains('class="modal-footer-bar modal-footer-actions"', $html);
        $this->assertHtmlContains('.modal-footer-actions {', $html);
    }

    public function test_blocking_overlays_lock_document_scroll(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('html.modal-scroll-lock,', $html);
        $this->assertHtmlContains("document.documentElement.classList.toggle('modal-scroll-lock', locked);", $html);
        $this->assertHtmlContains("document.body.classList.toggle('modal-scroll-lock', locked);", $html);
        $this->assertHtmlContains('const hasBlockingOverlayOpen = computed(() => Boolean(', $html);
        $this->assertHtmlContains('watch(hasBlockingOverlayOpen, (locked) => {', $html);
        $this->assertHtmlContains('setDocumentScrollLock(false);', $html);
    }

    public function test_table_row_actions_use_labeled_tap_safe_buttons(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('.table-action-button {', $html);
        $this->assertHtmlContains('white-space: nowrap;', $html);
        $this->assertHtmlContains('.table-action-button.table-action-compact {', $html);
        $this->assertHtmlContains('.table-action-button.table-action-link:hover {', $html);
        $this->assertHtmlContains('.table-action-button.table-action-view:hover {', $html);
        $this->assertHtmlNotContains('button:not(.table-action-button):has(> .fa-pen)', $html);
        $this->assertHtmlContains('.reset-filter-button {', $html);
        $this->assertHtmlContains('aria-label="Edit"', $html);
        $this->assertHtmlContains('aria-label="Hapus"', $html);
        $this->assertHtmlContains('aria-label="Link Drive"', $html);
        $this->assertHtmlContains('aria-label="Detail Pengeluaran"', $html);
        $this->assertHtmlNotContains('>Hapus</span>', $html);
        $this->assertHtmlContains('>Reset</span>', $html);
    }

    public function test_icon_utility_buttons_use_shared_classes(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('.icon-utility-button {', $html);
        $this->assertHtmlContains('.icon-utility-button.icon-utility-bordered {', $html);
        $this->assertHtmlContains('.icon-utility-button.icon-utility-round {', $html);
        $this->assertHtmlContains('.icon-utility-button.icon-utility-danger:hover {', $html);
        $this->assertHtmlContains('class="icon-utility-button icon-utility-bordered"', $html);
        $this->assertHtmlContains('class="icon-utility-button icon-utility-bordered !w-10 !h-10 text-slate-600"', $html);
        $this->assertHtmlContains('class="icon-utility-button icon-utility-bordered !w-10 !h-10 text-slate-600"', $html);
        $this->assertHtmlContains('class="icon-utility-button icon-utility-round"', $html);
        $this->assertHtmlContains('class="icon-utility-button icon-utility-danger"', $html);
        $this->assertHtmlNotContains('class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-white transition-all disabled:opacity-30"', $html);
        $this->assertHtmlNotContains('class="w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-400 transition-all"', $html);
        $this->assertHtmlNotContains('class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-slate-100 transition-all"', $html);
        $this->assertHtmlNotContains('class="w-10 h-10 rounded-xl bg-slate-50 text-slate-600 flex items-center justify-center"', $html);
        $this->assertHtmlNotContains('class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center"', $html);
    }

    public function test_secondary_cta_buttons_use_shared_classes(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('.secondary-cta-button {', $html);
        $this->assertHtmlContains('class="secondary-cta-button secondary-cta-link">', $html);
        $this->assertHtmlContains('>WA 1</a>', $html);
        $this->assertHtmlContains('>WA 2</a>', $html);
        $this->assertHtmlContains('.secondary-cta-button.secondary-cta-success {', $html);
        $this->assertHtmlContains('.secondary-cta-button.secondary-cta-danger {', $html);
        $this->assertHtmlContains('.secondary-cta-button.secondary-cta-neutral {', $html);
        $this->assertHtmlContains('.secondary-cta-button.secondary-cta-link {', $html);
        $this->assertHtmlContains('background: rgb(248 250 252);', $html);
        $this->assertHtmlContains('color: rgb(71 85 105);', $html);
        $this->assertHtmlContains('class="secondary-cta-button secondary-cta-success active:scale-95"', $html);
        $this->assertHtmlContains('class="secondary-cta-button secondary-cta-danger active:scale-95"', $html);
        $this->assertHtmlContains('class="secondary-cta-button secondary-cta-link"', $html);
        $this->assertHtmlNotContains('class="h-9 px-2.5 sm:px-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 text-[10px] font-bold uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all active:scale-95 flex items-center gap-1.5"', $html);
        $this->assertHtmlNotContains('class="h-9 px-2.5 sm:px-4 rounded-xl bg-slate-800 text-white text-[10px] font-bold uppercase tracking-widest hover:bg-slate-900 transition-all active:scale-95 flex items-center gap-1.5"', $html);
        $this->assertHtmlNotContains('class="h-9 px-3 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 text-[10px] font-bold hover:bg-emerald-600 hover:text-white transition-all active:scale-95 flex items-center gap-1.5"', $html);
        $this->assertHtmlNotContains('class="h-9 px-3 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 text-[10px] font-bold hover:bg-rose-600 hover:text-white transition-all active:scale-95 flex items-center gap-1.5"', $html);
        $this->assertHtmlNotContains('class="h-9 px-3 rounded-xl bg-slate-50 border border-slate-100 text-slate-500 text-[10px] font-bold hover:bg-slate-100 transition-all active:scale-95 flex items-center gap-1.5"', $html);
    }

    public function test_forms_and_dialogs_use_shared_ui_primitives(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('.form-input {', $html);
        $this->assertHtmlContains('.form-input-auth {', $html);
        $this->assertHtmlContains('.form-input-compact {', $html);
        $this->assertHtmlContains('.form-input-compact-white {', $html);
        $this->assertHtmlContains('.form-input-search {', $html);
        $this->assertHtmlContains('.form-input-popover {', $html);
        $this->assertHtmlContains('.date-trigger-button {', $html);
        $this->assertHtmlContains('.date-trigger-button-compact {', $html);
        $this->assertHtmlContains('.multi-select-chip {', $html);
        $this->assertHtmlContains('.popover-option-check {', $html);
        $this->assertHtmlContains('.calendar-day-button {', $html);
        $this->assertHtmlContains('.popover-option {', $html);
        $this->assertHtmlContains('.popover-option.popover-option-active {', $html);
        $this->assertHtmlContains('.surface-panel-soft {', $html);
        $this->assertHtmlContains('popover-option-active', $html);
        $this->assertHtmlContains('class="modal-secondary-button flex-1"', $html);
        $this->assertHtmlContains('class="modal-primary-button flex-1"', $html);
        $this->assertHtmlContains('class="secondary-cta-button secondary-cta-danger w-full"', $html);
        $this->assertHtmlContains('.secondary-cta-button.secondary-cta-neutral {', $html);
        $this->assertHtmlContains('class="form-input-auth"', $html);
        $this->assertHtmlContains('class="form-input-compact-white"', $html);
        $this->assertHtmlContains('class="form-input-search"', $html);
        $this->assertHtmlContains('class="form-input-popover"', $html);
        $this->assertHtmlContains('class="date-trigger-button toolbar-trigger-field"', $html);
        $this->assertHtmlContains('class="date-trigger-button date-trigger-button-compact"', $html);
        $this->assertHtmlContains('class="multi-select-chip"', $html);
        $this->assertHtmlContains('class="popover-option"', $html);
        $this->assertHtmlContains("['popover-option-check',", $html);
        $this->assertHtmlContains("['calendar-day-button',", $html);
        $this->assertHtmlContains('class="surface-panel-soft"', $html);
        $this->assertHtmlContains('class="select-trigger-button select-trigger-button-compact"', $html);
        $this->assertHtmlContains('class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form"', $html);
        $this->assertHtmlNotContains('class="w-full py-3 bg-white text-red-500 text-[10px] font-medium rounded-xl border border-red-100 hover:bg-red-50 transition-all uppercase tracking-widest"', $html);
        $this->assertHtmlNotContains('class="flex-1 py-3 rounded-2xl border border-slate-200 text-slate-600 text-[12px] font-bold hover:bg-slate-50 transition-all"', $html);
        $this->assertHtmlNotContains('class="w-full bg-slate-50 border border-slate-100 rounded-xl pl-9 pr-4 py-2.5 text-[11px] focus:border-ppp-accent outline-none transition-all"', $html);
        $this->assertHtmlNotContains('class="w-full bg-slate-50 rounded-2xl pl-10 pr-4 py-4 text-[12px] outline-none border border-slate-100 focus:border-ppp-accent"', $html);
        $this->assertHtmlNotContains('class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3 text-[12px] outline-none hover:border-ppp-accent transition-all flex items-center gap-2 text-left"', $html);
        $this->assertHtmlNotContains('class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-[11px] outline-none hover:border-ppp-accent transition-all flex items-center gap-2 text-left"', $html);
        $this->assertHtmlNotContains('class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-[11px] outline-none focus:border-ppp-accent transition-all"', $html);
        $this->assertHtmlNotContains('class="w-full bg-white border border-slate-100 rounded-xl px-3 py-2 text-[11px] font-bold focus:outline-none focus:border-ppp-accent focus:ring-2 focus:ring-ppp-accent/10 text-slate-700 transition-all shadow-sm uppercase"', $html);
        $this->assertHtmlNotContains("['px-3 py-2 text-[11px] rounded-xl cursor-pointer transition-all',", $html);
        $this->assertHtmlNotContains("['px-4 py-2.5 text-[11px] rounded-xl cursor-pointer transition-all flex items-center justify-between group',", $html);
        $this->assertHtmlNotContains("['py-2 text-[11px] rounded-xl cursor-pointer transition-all font-medium relative z-10 flex flex-col items-center justify-center min-h-[36px]',", $html);
    }

    public function test_export_buttons_only_use_pdf_or_excel_labels(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('class="fa-solid fa-file-pdf"></i> PDF', $html);
        $this->assertHtmlContains('class="fa-solid fa-file-excel text-[9px]"></i> Excel', $html);
        $this->assertHtmlContains('<i class="fa-solid fa-file-excel"></i> Excel', $html);
        $this->assertHtmlContains('class="fa-solid fa-file-pdf text-[9px]"', $html);
        $this->assertHtmlContains('class="fa-solid fa-file-excel"></i><span', $html);
        $this->assertHtmlContains('class="secondary-cta-button secondary-cta-danger active:scale-95"', $html);
        $this->assertHtmlContains('class="secondary-cta-button secondary-cta-success active:scale-95"', $html);
        $this->assertHtmlContains('const exportPromoToPDF = () => {', $html);
        $this->assertHtmlContains('const exportAdsLogToPDF = () => {', $html);
        $this->assertHtmlContains('const exportPriceComparisonToPDF = () => {', $html);
        $this->assertHtmlContains('const exportLpjkDetailToPDF = () => {', $html);
        $this->assertHtmlContains('const exportBudgetToPDF = () => {', $html);
        $this->assertHtmlNotContains('<i class="fa-solid fa-print"></i> Print', $html);
        $this->assertHtmlNotContains('>Print</span>', $html);
        $this->assertHtmlNotContains('>Cetak LPJK</button>', $html);
        $this->assertHtmlNotContains('const printPromoReport = () => {', $html);
        $this->assertHtmlNotContains('const printAdsLog = () => {', $html);
        $this->assertHtmlNotContains('const printPriceComparisonReport = () => {', $html);
        $this->assertHtmlNotContains('const printLpjkDetail = () => {', $html);
        $this->assertHtmlNotContains('const printBudgetReport = () => {', $html);
        $this->assertHtmlNotContains('class="secondary-cta-button secondary-cta-neutral active:scale-95"><i class="fa-solid fa-file-pdf', $html);
        $this->assertHtmlNotContains('>CSV</button>', $html);
        $this->assertHtmlNotContains('fa-file-csv', $html);
    }

    public function test_primary_cta_filter_trigger_and_modal_footer_buttons_use_shared_classes(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('.primary-cta-button {', $html);
        $this->assertHtmlContains('.filter-trigger-button,', $html);
        $this->assertHtmlContains('.select-trigger-button {', $html);
        $this->assertHtmlContains('.search-select-popover {', $html);
        $this->assertHtmlContains('.search-select-popover--compact {', $html);
        $this->assertHtmlContains('.dashboard-summary-card {', $html);
        $this->assertHtmlContains('.segmented-control {', $html);
        $this->assertHtmlContains('.segmented-control__item {', $html);
        $this->assertHtmlContains('.modal-secondary-button {', $html);
        $this->assertHtmlContains('.modal-primary-button {', $html);
        $this->assertHtmlContains('class="primary-cta-button primary-cta-button--accent active:scale-95"', $html);
        $this->assertHtmlContains(":class=\"['secondary-cta-button active:scale-95', showBonusSettings ? 'bg-slate-900 text-white border-slate-900 hover:bg-black' : 'secondary-cta-neutral']\"", $html);
        $this->assertHtmlContains('class="filter-trigger-button toolbar-trigger-field"', $html);
        $this->assertHtmlContains('class="select-trigger-button toolbar-trigger-field"', $html);
        $this->assertHtmlContains('class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form"', $html);
        $this->assertHtmlContains('class="select-trigger-button select-trigger-button-compact"', $html);
        $this->assertHtmlContains('class="search-select-popover"', $html);
        $this->assertHtmlContains('class="search-select-popover search-select-popover--compact max-h-60 overflow-y-auto"', $html);
        $this->assertHtmlContains('class="segmented-control segmented-control--ios segmented-control--equal w-full justify-center"', $html);
        $this->assertHtmlContains("@click=\"analisaInsightTab = 'konten'\"", $html);
        $this->assertHtmlContains("@click=\"analisaInsightTab = 'sales'\"", $html);
        $this->assertHtmlContains("analisaInsightTab === 'konten' ? 'segmented-control__item--active' : ''", $html);
        $this->assertHtmlContains("analisaInsightTab === 'sales' ? 'segmented-control__item--active' : ''", $html);
        $this->assertHtmlContains('class="modal-primary-button"', $html);
        $this->assertHtmlContains('class="modal-secondary-button"', $html);
        $this->assertHtmlNotContains('class="flex-1 sm:flex-none px-4 py-2.5 bg-ppp-accent text-white rounded-xl text-[11px] font-medium hover:bg-ppp-accent-dark transition-all active:scale-95"', $html);
        $this->assertHtmlNotContains('class="w-full sm:w-auto bg-slate-50 border border-slate-100 rounded-xl px-4 py-2.5 text-[11px] text-left text-slate-600 flex items-center gap-2 hover:bg-slate-100 transition-all"', $html);
        $this->assertHtmlNotContains("['px-6 py-2 rounded-xl text-[11px] font-bold transition-all', storyTab === 'Ganjil' ? 'bg-white text-rose-500 shadow-sm' : 'text-slate-400 hover:text-slate-600']", $html);
        $this->assertHtmlNotContains('class="h-9 px-4 rounded-xl bg-ppp-accent text-white text-[10px] font-bold uppercase tracking-widest hover:bg-ppp-accent-dark transition-all active:scale-95 flex items-center gap-1.5"', $html);
        $this->assertHtmlNotContains('class="h-9 px-4 rounded-xl bg-blue-500 text-white text-[10px] font-bold uppercase tracking-widest hover:bg-blue-600 transition-all active:scale-95 flex items-center gap-1.5"', $html);
        $this->assertHtmlNotContains('class="h-9 px-4 rounded-xl bg-violet-500 text-white text-[10px] font-bold uppercase tracking-widest hover:bg-violet-600 transition-all active:scale-95 flex items-center gap-1.5"', $html);
        $this->assertHtmlNotContains(":class=\"['h-9 px-4 rounded-xl text-[10px] font-bold uppercase tracking-widest transition-all flex items-center gap-1.5 border', showBonusSettings ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-500 border-slate-200 hover:bg-slate-100']\"", $html);
        $this->assertHtmlNotContains('class="h-9 px-4 rounded-xl bg-slate-100 text-slate-600 text-[10px] font-bold uppercase tracking-widest hover:bg-slate-200 transition-all active:scale-95 flex items-center gap-1.5 border border-slate-200"', $html);
        $this->assertHtmlNotContains('class="flex bg-slate-100 rounded-2xl p-1 gap-1"', $html);
        $this->assertHtmlNotContains("['px-4 py-2 rounded-xl text-[11px] font-bold transition-all', analisaInsightTab === 'konten' ? 'bg-white text-ppp-accent shadow-sm' : 'text-slate-500 hover:text-slate-700']", $html);
        $this->assertHtmlNotContains('class="w-full sm:w-40 bg-slate-50 border border-slate-100 rounded-xl px-3 py-2.5 text-[11px] text-slate-600 outline-none hover:border-ppp-accent transition-all flex items-center justify-between gap-2"', $html);
        $this->assertHtmlNotContains('class="w-full sm:w-44 bg-slate-50 border border-slate-100 rounded-xl px-3 py-2.5 text-[11px] text-slate-600 outline-none hover:border-ppp-accent transition-all flex items-center justify-between gap-2"', $html);
        $this->assertHtmlNotContains('class="bg-white border border-slate-100 rounded-2xl overflow-hidden p-2 animate-fadeIn shadow-2xl"', $html);
        $this->assertHtmlNotContains('class="bg-white border border-slate-100 rounded-2xl shadow-2xl p-1.5 max-h-60 overflow-y-auto"', $html);
        $this->assertHtmlNotContains('class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3 text-[12px] cursor-pointer flex items-center justify-between hover:bg-slate-100 transition-all"', $html);
        $this->assertHtmlNotContains('class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3 text-[12px] cursor-pointer flex items-center justify-between hover:bg-slate-100 transition-all min-h-[48px]"', $html);
        $this->assertHtmlNotContains('class="w-full bg-white border border-slate-100 rounded-2xl px-4 py-3 text-[12px] cursor-pointer flex items-center justify-between hover:bg-slate-100 transition-all"', $html);
        $this->assertHtmlNotContains('class="flex-1 bg-white border border-slate-100 rounded-2xl px-3 py-3 text-[11px] cursor-pointer flex items-center gap-1.5 hover:bg-slate-100 transition-all"', $html);
    }

    public function test_summary_cards_use_compact_shared_layout_tokens(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('.dashboard-summary-card-compact {', $html);
        $this->assertHtmlContains('.dashboard-summary-grid-compact {', $html);
        $this->assertHtmlContains('.dashboard-summary-value {', $html);
        $this->assertHtmlContains('.dashboard-summary-unit {', $html);
        $this->assertHtmlContains('.dashboard-summary-card-compact>.absolute {', $html);
        $this->assertHtmlContains('padding-right: 4.25rem;', $html);
        $this->assertHtmlContains('opacity: 0.06;', $html);
        $this->assertHtmlContains('transform: scale(0.62);', $html);
        $this->assertHtmlContains('grid-template-columns: repeat(4, minmax(0, 1fr)) !important;', $html);
        $this->assertHtmlContains('@media (max-width: 767px) {', $html);
        $this->assertHtmlContains('min-height: 4.65rem;', $html);
        $this->assertHtmlContains('border-radius: 0 !important;', $html);
        $this->assertHtmlContains('font-size: 15px;', $html);
        $this->assertHtmlContains('class="dashboard-summary-grid-compact grid grid-cols-3 md:grid-cols-4 gap-3 md:gap-4"', $html);
        $this->assertHtmlContains('class="dashboard-summary-card-compact bg-white radius-panel border border-slate-100"', $html);
        $this->assertHtmlContains('class="dashboard-summary-value"', $html);
        $this->assertHtmlContains('dashboard-summary-unit', $html);
        $this->assertHtmlContains('.dashboard-summary-card .icon-utility-button {', $html);
        $this->assertHtmlNotContains('class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4"', $html);
        $this->assertHtmlNotContains('class="bg-white radius-panel border border-slate-100 p-6 relative overflow-hidden group"', $html);
        $this->assertHtmlNotContains('class="text-[22px] font-bold text-slate-900 leading-none tracking-tight"', $html);
    }

    public function test_link_ctas_are_consistent_between_mobile_and_desktop_cards(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('class="secondary-cta-button secondary-cta-link">', $html);
        $this->assertHtmlContains('<i class="fa-solid fa-link"></i> Buka Link', $html);
        $this->assertHtmlContains('<i class="fa-solid fa-external-link-alt text-[9px]"></i> Buka Link', $html);
        $this->assertHtmlContains('<i class="fa-solid fa-external-link-alt text-[10px]"></i> Buka Link', $html);
        $this->assertHtmlNotContains('class="inline-flex items-center gap-1 text-blue-500 text-[10px] font-bold mt-1 hover:underline"', $html);
        $this->assertHtmlNotContains('class="inline-flex items-center gap-1 text-blue-500 hover:text-blue-600 text-[11px] font-bold"', $html);
    }

    public function test_shell_typography_uses_shared_type_tiers_for_navigation_and_section_headers(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('.type-micro {', $html);
        $this->assertHtmlContains('.type-meta {', $html);
        $this->assertHtmlContains('.type-body {', $html);
        $this->assertHtmlContains('.type-title {', $html);
        $this->assertHtmlContains('class="type-body font-medium tracking-wide">Dashboard</span>', $html);
        $this->assertHtmlContains('class="type-micro text-slate-400 uppercase tracking-widest">{{ currentUser.role }}', $html);
        $this->assertHtmlContains('class="type-meta uppercase tracking-[0.2em] text-slate-400 mb-2">Ringkasan', $html);
        $this->assertHtmlContains('class="type-title font-semibold text-slate-800 truncate">{{ item.Judul', $html);
        $this->assertHtmlNotContains('class="text-[11px] font-medium tracking-wide">Dashboard</span>', $html);
        $this->assertHtmlNotContains('class="text-[10px] uppercase tracking-[0.2em] text-slate-400 mb-2">Ringkasan', $html);
    }

    public function test_table_and_modal_typography_continue_migrating_to_shared_type_tiers(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('class="type-title font-semibold text-slate-900">Gabungan Semua Platform</h3>', $html);
        $this->assertHtmlContains('<h3 class="type-title font-bold text-slate-800 mb-4"><i', $html);
        $this->assertHtmlContains('class="fa-solid fa-handshake text-ppp-accent mr-2"></i>Colab vs Non-Colab</h3>', $html);
        $this->assertHtmlContains('<h3 class="type-title font-bold text-slate-800 mb-4"><i', $html);
        $this->assertHtmlContains('class="fa-solid fa-chart-line text-ppp-accent mr-2"></i>Tren Bulanan (Views)', $html);
        $this->assertHtmlContains('class="fa-solid fa-bag-shopping text-ppp-accent mr-2"></i>Ringkasan Order Online', $html);
        $this->assertHtmlContains('class="px-4 py-3 type-body text-slate-500">{{ (orderanPage - 1) * 15 +', $html);
        $this->assertHtmlContains('class="type-title text-slate-900">{{ namaStockFormMode === \'create\' ? \'Tambah Nama Stock\' : \'Edit Nama Stock\' }}</div>', $html);
        $this->assertHtmlContains('class="type-meta font-semibold text-slate-500 uppercase tracking-wide">Kategori</label>', $html);
        $this->assertHtmlContains('class="type-title font-bold text-slate-900">Nama Stock</h2>', $html);
        $this->assertHtmlContains('class="type-title font-bold text-slate-900 mb-6 flex items-center gap-2">', $html);
        $this->assertHtmlContains('class="type-meta font-bold text-slate-400 uppercase mb-1.5">Nama', $html);
        $this->assertHtmlContains('class="type-meta font-bold text-slate-400 uppercase mb-1.5">Tanggal', $html);
        $this->assertHtmlContains('class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Vendor', $html);
        $this->assertHtmlNotContains('class="text-[12px] font-semibold text-slate-900">Gabungan Semua Platform</h3>', $html);
        $this->assertHtmlNotContains('class="text-[13px] font-bold text-slate-800 mb-4"><i class="fa-solid fa-handshake text-ppp-accent mr-2"></i>Colab vs Non-Colab</h3>', $html);
        $this->assertHtmlNotContains('class="text-[13px] font-bold text-slate-800 mb-4"><i class="fa-solid fa-chart-line text-ppp-accent mr-2"></i>Tren Bulanan (Views)</h3>', $html);
        $this->assertHtmlNotContains('class="text-[13px] font-bold text-slate-800 mb-4"><i class="fa-solid fa-bag-shopping text-ppp-accent mr-2"></i>Ringkasan Order Online</h3>', $html);
        $this->assertHtmlNotContains('class="text-[13px] font-bold text-slate-900">{{ namaStockFormMode === \'create\' ? \'Tambah Nama Stock\' : \'Edit Nama Stock\' }}</h3>', $html);
        $this->assertHtmlNotContains('class="text-[13px] font-bold text-slate-900">Nama Stock</h2>', $html);
        $this->assertHtmlNotContains('class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5">Nama', $html);
    }

    public function test_radius_tokens_are_used_for_panels_cards_and_dialog_shells(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('.radius-card {', $html);
        $this->assertHtmlContains('.radius-panel {', $html);
        $this->assertHtmlContains('.radius-dialog {', $html);
        $this->assertHtmlContains('.radius-sheet {', $html);
        $this->assertHtmlContains('.radius-sheet-top {', $html);
        $this->assertHtmlContains('.radius-sheet-bottom {', $html);
        $this->assertHtmlContains('.radius-panel {', $html);
        $this->assertHtmlContains('class="dashboard-summary-card stat-card"', $html);
        $this->assertHtmlContains('class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface"', $html);
        $this->assertHtmlContains('class="modal-footer-bar modal-footer-actions"', $html);
        $this->assertHtmlNotContains('class="bg-white rounded-[28px] border border-slate-100 p-5 md:p-6"', $html);
        $this->assertHtmlNotContains('class="mobile-sheet relative w-full md:max-w-xl bg-white rounded-t-[24px] md:rounded-[32px] shadow-2xl flex flex-col max-h-[90dvh] animate-fadeIn"', $html);
        $this->assertHtmlNotContains('class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm"', $html);
    }

    public function test_small_primary_actions_use_shared_modal_button_variants(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('.modal-primary-button.modal-primary-button--success {', $html);
        $this->assertHtmlContains('.modal-primary-button.modal-primary-button--danger {', $html);
        $this->assertHtmlContains('.modal-primary-button.modal-primary-button--info {', $html);
        $this->assertHtmlContains('class="modal-primary-button w-full modal-primary-button--info shadow-lg shadow-blue-100 active:scale-95 disabled:opacity-50"', $html);
        $this->assertHtmlContains('class="modal-primary-button w-full modal-primary-button--danger shadow-lg shadow-rose-100 active:scale-95 disabled:opacity-50"', $html);
        $this->assertHtmlContains('class="modal-primary-button"', $html);
        $this->assertHtmlContains('class="modal-primary-button modal-primary-button--success"', $html);
        $this->assertHtmlNotContains('class="w-full px-6 py-3.5 bg-blue-500 text-white rounded-2xl text-[12px] font-bold hover:bg-blue-600 transition-all shadow-lg shadow-blue-100 active:scale-95 flex items-center justify-center gap-2 disabled:opacity-50"', $html);
        $this->assertHtmlNotContains('class="w-full px-6 py-3.5 bg-rose-500 text-white rounded-2xl text-[12px] font-bold hover:bg-rose-600 transition-all shadow-lg shadow-rose-100 active:scale-95 flex items-center justify-center gap-2 disabled:opacity-50"', $html);
        $this->assertHtmlNotContains('class="px-6 py-2.5 rounded-xl bg-ppp-accent text-white text-[11px] font-bold hover:bg-ppp-accent-dark transition-all disabled:opacity-50 flex items-center gap-2"', $html);
        $this->assertHtmlNotContains('class="px-6 py-2.5 bg-emerald-500 text-white rounded-xl text-[11px] font-bold uppercase tracking-widest hover:bg-emerald-600 transition-all disabled:opacity-50"', $html);
    }

    public function test_existing_mobile_table_cards_share_compact_helpers_and_patterns(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('.mobile-data-card {', $html);
        $this->assertHtmlContains('.mobile-data-card__header {', $html);
        $this->assertHtmlContains('.mobile-data-card__title {', $html);
        $this->assertHtmlContains('.mobile-data-card__meta {', $html);
        $this->assertHtmlContains('.mobile-data-card__summary {', $html);
        $this->assertHtmlContains('.mobile-data-card__actions {', $html);
        $this->assertHtmlContains('class="md:hidden space-y-3"', $html);
        $this->assertHtmlContains('class="stat-card mobile-record-card mobile-data-card animate-fadeIn"', $html);
        $this->assertHtmlContains('class="mobile-data-card__actions"', $html);
        $this->assertHtmlContains('class="mobile-data-card__summary"', $html);
        $this->assertHtmlNotContains('class="md:hidden divide-y divide-slate-50"', $html);
        $this->assertHtmlNotContains('class="w-full flex items-center justify-center gap-1.5 py-2 rounded-xl bg-ppp-accent/10 text-ppp-accent hover:bg-ppp-accent hover:text-white transition-all text-[10px] font-semibold"', $html);
    }

    public function test_mobile_operational_tabs_use_compact_card_layouts_instead_of_mobile_tables(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        foreach ([
            "activeTab === 'unboxing'",
            "activeTab === 'top_content_platform'",
            "activeTab === 'low_content_platform'",
            "activeTab === 'orderan_online'",
            "activeTab === 'unit_ditanya'",
            "activeTab === 'claim_garansi_asuransi'",
            "activeTab === 'keep_barang'",
            "activeTab === 'nama_stock'",
            "activeTab === 'harga_kompetitor'",
            "activeTab === 'laporan_event'",
            "activeTab === 'ads_log'",
        ] as $needle) {
            $this->assertHtmlContains($needle, $html);
        }

        foreach ([
            "class=\"md:hidden space-y-3\"",
            "class=\"stat-card mobile-record-card mobile-data-card animate-fadeIn\"",
            "class=\"mobile-data-card__summary\"",
            "class=\"mobile-data-card__actions\"",
            "line-clamp-2",
            "line-clamp-1",
        ] as $needle) {
            $this->assertHtmlContains($needle, $html);
        }

        $this->assertHtmlContains("{{ row.NAMA || row['TYPE UNIT'] || '-' }}", $html);
        $this->assertHtmlContains("{{ row.BRAND ? row.BRAND + ' | ' + row.SERI : row.SERI || '-' }}", $html);
        $this->assertHtmlContains("{{ row.Nama_Produk || '-' }}", $html);
        $this->assertHtmlContains("{{ row.Nama_Event || '-' }}", $html);
        $this->assertHtmlContains("{{ row.Nama || '-' }}", $html);
        $this->assertHtmlContains("{{ row.title }}", $html);
        $this->assertHtmlContains("{{ row.Program }}", $html);

        $this->assertHtmlNotContains('class="bg-white radius-panel border border-slate-100 overflow-hidden">
                            <div class="md:hidden space-y-3', $html);
        $this->assertHtmlNotContains('class="bg-white radius-panel border border-slate-100 overflow-hidden">
                            <div class="md:hidden space-y-3 p-3', $html);
    }

    public function test_master_plan_and_claim_forms_are_grouped_with_section_headers(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('Ringkasan Konten', $html);
        $this->assertHtmlContains('Distribusi & Asset', $html);
        $this->assertHtmlContains('Info Customer', $html);
        $this->assertHtmlContains('Info Unit & Service', $html);
    }

    public function test_error_messages_use_friendly_formatter_and_avoid_generic_required_copy(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('const getFriendlyErrorMessage = (error, fallback = \'Terjadi kendala saat memproses permintaan.\') => {', $html);
        $this->assertHtmlContains('const notifyError = (prefix, error, fallback) => {', $html);
        $this->assertHtmlContains('Data Nama Stock belum lengkap. Isi kategori, brand, dan seri terlebih dulu.', $html);
        $this->assertHtmlNotContains('Semua kolom wajib diisi.', $html);
    }

    public function test_dashboard_uses_no_native_select_dropdowns(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertSame(2, substr_count($html, '<select'));
        $this->assertSame(2, substr_count($html, '</select>'));
        $this->assertHtmlContains('<select v-model="activityLogFilters.table_name" class="form-input min-w-[170px]">', $html);
        $this->assertHtmlContains('<select v-model="activityLogFilters.action" class="form-input min-w-[150px]">', $html);
    }

    public function test_dashboard_uses_only_custom_date_pickers_with_shared_calendar_contexts(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlNotContains('type="date"', $html);
        $this->assertHtmlContains("openCalendar(\$event, 'filter', '', 'budgeting')", $html);
        $this->assertHtmlContains("openCalendar(\$event, 'published', plat)", $html);
        $this->assertHtmlContains("openCalendar(\$event, 'form', '', 'keepBarangTanggalKeep')", $html);
        $this->assertHtmlContains("openCalendar(\$event, 'form', '', 'keepBarangRencanaAmbil')", $html);
        $this->assertHtmlContains("openCalendar(\$event, 'form', '', 'keepBarangDeadlineGudang')", $html);
        $this->assertHtmlContains("openCalendar(\$event, 'form', '', 'unboxingUploadDate')", $html);
        $this->assertHtmlContains("openCalendar(\$event, 'form', '', 'distribution')", $html);
        $this->assertHtmlContains("else if (formContext === 'keepBarangTanggalKeep')", $html);
        $this->assertHtmlContains("else if (formContext === 'keepBarangRencanaAmbil')", $html);
        $this->assertHtmlContains("else if (formContext === 'keepBarangDeadlineGudang')", $html);
        $this->assertHtmlContains("else if (formContext === 'unboxingUploadDate')", $html);
        $this->assertHtmlContains("return budgetDateFilter;", $html);
        $this->assertHtmlContains("formatShortDate(adsDateFilter.start)", $html);
    }

    public function test_keep_barang_default_view_does_not_hide_non_pending_rows(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('const filteredKeepBarangData = computed(() => {', $html);
        $this->assertHtmlNotContains("if (!isFiltering && r.STATUS !== 'PENDING') return false;", $html);
    }

    public function test_keep_barang_summary_counts_use_full_dataset_not_filtered_rows(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('const keepBarangSummary = computed(() => {', $html);
        $this->assertHtmlContains('const rows = keepBarangData.value || [];', $html);
        $this->assertHtmlContains("return { total: rows.length, pending: rows.filter(r => r.STATUS === 'PENDING').length, done: rows.filter(r => r.STATUS === 'DONE').length, cancel: rows.filter(r => r.STATUS === 'CANCEL').length };", $html);
        $this->assertHtmlContains('CANCEL:', $html);
    }

    public function test_mobile_date_and_select_triggers_share_full_width_and_trailing_chevron_rules(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('.select-trigger-button-compact {', $html);
        $this->assertHtmlContains('width: 100%;', $html);
        $this->assertHtmlContains('.date-trigger-button-compact {', $html);
        $this->assertHtmlContains('@media (min-width: 640px) {', $html);
        $this->assertHtmlContains('.search-select-container {', $html);
        $this->assertTrue(
            str_contains($html, '.select-trigger-button > .fa-chevron-down,') || str_contains($html, '.select-trigger-button>.fa-chevron-down,')
        );
        $this->assertTrue(
            str_contains($html, '.filter-trigger-button > .fa-chevron-down,') || str_contains($html, '.filter-trigger-button>.fa-chevron-down,')
        );
        $this->assertHtmlContains('margin-left: auto;', $html);
    }

    public function test_period_toolbar_groups_use_shared_mobile_stack_layout(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('.compact-period-toolbar {', $html);
        $this->assertHtmlContains('.compact-period-toolbar__controls {', $html);
        $this->assertHtmlContains('class="compact-period-toolbar"', $html);
        $this->assertHtmlContains('class="compact-period-toolbar__controls"', $html);
    }

    public function test_shared_action_filter_toolbar_stacks_consistently_on_mobile(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('.mobile-toolbar-stack {', $html);
        $this->assertTrue(
            str_contains($html, '.mobile-toolbar-stack > * {') || str_contains($html, '.mobile-toolbar-stack>* {')
        );
        $this->assertHtmlContains('class="mobile-toolbar-stack"', $html);
    }

    public function test_ideation_board_uses_mobile_lane_switch_to_avoid_three_long_stacks(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains("const ideationBoardMobileTab = ref('');", $html);
        $this->assertHtmlContains('@click="ideationBoardMobileTab = ideationDraftLabel"', $html);
        $this->assertHtmlContains("@click=\"ideationBoardMobileTab = 'In Progress'\"", $html);
        $this->assertHtmlContains("@click=\"ideationBoardMobileTab = 'Done'\"", $html);
        $this->assertHtmlContains("v-show=\"status === ideationBoardMobileTab || !isMobileViewport\"", $html);
        $this->assertHtmlContains("watch(() => ideationDraftLabel.value, (label) => {", $html);
        $this->assertHtmlContains("if (mode === 'board') ideationBoardMobileTab.value = ideationDraftLabel.value;", $html);
    }

    public function test_story_modal_and_content_modals_share_dashboard_controls_consistently(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlNotContains('type="radio" v-model="storyForm.is_genap"', $html);
        $this->assertHtmlContains("@click=\"storyForm.is_genap = 'Ganjil'\"", $html);
        $this->assertHtmlContains("@click=\"storyForm.is_genap = 'Genap'\"", $html);
        $this->assertHtmlContains("storyForm.is_genap === 'Ganjil' ? 'segmented-control__item--active' : ''", $html);
        $this->assertHtmlContains("storyForm.is_genap === 'Genap' ? 'segmented-control__item--active' : ''", $html);
        $this->assertHtmlContains('<div class="modal-footer-bar modal-footer-actions">', $html);
        $this->assertHtmlContains('@click="unboxingModalOpen = false" aria-label="Tutup modal"', $html);
        $this->assertHtmlContains('@click="distModalOpen = false" aria-label="Tutup modal"', $html);
        $this->assertHtmlNotContains("console.log('openUnboxingModal called', type);", $html);
        $this->assertHtmlNotContains("console.log('unboxingModalOpen:', unboxingModalOpen.value);", $html);
    }

    public function test_ideation_lane_shell_and_remaining_icon_buttons_follow_shared_dashboard_tokens(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlNotContains('bg-white/60 backdrop-blur-sm radius-dialog p-5 border border-slate-100/50', $html);
        $this->assertHtmlContains('class="bg-white radius-panel border border-slate-100 p-5 flex flex-col min-h-[400px]"', $html);
        $this->assertHtmlContains('@click="sellOutModalOpen = false" aria-label="Tutup modal"', $html);
        $this->assertHtmlContains('@click="calendarDayModalOpen = false" aria-label="Tutup modal"', $html);
        $this->assertHtmlContains('@click="changeMonth(-1)" aria-label="Bulan sebelumnya"', $html);
        $this->assertHtmlContains('@click="changeMonth(1)" aria-label="Bulan berikutnya"', $html);
    }

    public function test_crud_modals_use_shared_sticky_header_shell(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlNotContains('class="p-6 border-b border-slate-100 flex items-center justify-between radius-sheet-top bg-white"', $html);
        $this->assertHtmlContains('.modal-header-bar-sticky {', $html);
        $this->assertHtmlContains('class="modal-header-bar modal-header-bar-sticky radius-sheet-top z-[2010]"', $html);
    }

    public function test_primary_pagination_icon_buttons_have_accessible_labels(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('@click="masterPage--" :disabled="masterPage <= 1"', $html);
        $this->assertHtmlContains('@click="masterPage++" :disabled="masterPage >= masterTotalPages"', $html);
        $this->assertHtmlContains('@click="distributionPage--" :disabled="distributionPage <= 1"', $html);
        $this->assertHtmlContains('@click="analyticsPage++" :disabled="analyticsPage >= analyticsTotalPages"', $html);
        $this->assertHtmlContains('aria-label="Halaman sebelumnya"', $html);
        $this->assertHtmlContains('aria-label="Halaman berikutnya"', $html);
        $this->assertHtmlContains('aria-label="Kolom sebelumnya"', $html);
        $this->assertHtmlContains('aria-label="Kolom berikutnya"', $html);
        $this->assertHtmlContains('@click="storyPage--" :disabled="storyPage <= 1"', $html);
        $this->assertHtmlContains('@click="unboxingPage--" :disabled="unboxingPage <= 1"', $html);
        $this->assertHtmlContains('@click="unboxingPage++" :disabled="unboxingPage >= unboxingTotalPages"', $html);
        $this->assertHtmlContains('@click="orderanPage++" :disabled="orderanPage >= orderanTotalPages"', $html);
        $this->assertHtmlContains('@click="unitDitanyaPage--" :disabled="unitDitanyaPage <= 1"', $html);
        $this->assertHtmlContains('@click="claimPage++" :disabled="claimPage >= claimTotalPages"', $html);
        $this->assertHtmlContains('@click="keepBarangPage--" :disabled="keepBarangPage <= 1"', $html);
        $this->assertHtmlContains('@click="namaStockPage++" :disabled="namaStockPage >= namaStockTotalPages"', $html);
        $this->assertHtmlContains('@click="promoPage--" :disabled="promoPage <= 1"', $html);
        $this->assertHtmlContains('@click="bonusPage++" :disabled="bonusPage >= bonusTotalPages"', $html);
        $this->assertHtmlContains('@click="editorPage--" :disabled="editorPage <= 1"', $html);
        $this->assertHtmlContains('@click="sellOutPage++" :disabled="sellOutPage >= sellOutTotalPages"', $html);
        $this->assertHtmlContains('@click="hargaKompetitorPage = 1" :disabled="hargaKompetitorPage <= 1"', $html);
        $this->assertHtmlContains('@click="hargaKompetitorPage = hargaKompetitorTotalPages"', $html);
        $this->assertHtmlContains('aria-label="Halaman pertama"', $html);
        $this->assertHtmlContains('aria-label="Halaman terakhir"', $html);
        $this->assertHtmlContains('@click="lpjkPage--" :disabled="lpjkPage <= 1"', $html);
        $this->assertHtmlContains('@click="adsPage++" :disabled="adsPage >= adsTotalPages"', $html);
    }

    public function test_ads_log_uses_dedicated_ads_performance_crud_instead_of_master_plan_bridge(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains("saveAds(data)", $html);
        $this->assertHtmlContains("deleteAds(id)", $html);
        $this->assertHtmlContains('/api/ads-performance', $html);
    }

    public function test_ads_log_kategori_column_is_wide_and_never_wraps(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('<th class="px-4 py-3 text-center w-36 whitespace-nowrap">Kategori</th>', $html);
        $this->assertHtmlContains('<td class="px-4 py-3 text-center whitespace-nowrap">', $html);
        $this->assertHtmlContains('class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 text-[9px] font-bold uppercase whitespace-nowrap"', $html);
    }

    public function test_table_status_badges_never_wrap(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold whitespace-nowrap"', $html);
        $this->assertHtmlContains('class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap"', $html);
        $this->assertHtmlContains('class="inline-flex items-center px-2.5 py-1 rounded-lg text-[9px] font-bold uppercase whitespace-nowrap"', $html);
    }

    public function test_print_headers_render_logo_on_left_of_company_text(): void
    {
        $printCore = file_get_contents(resource_path('js/dashboard/export/print-core.js'));
        $printHelpers = file_get_contents(resource_path('views/dashboard/partials/scripts/print-helpers.blade.php'));

        $this->assertHtmlContains("const logoUrl = getDashboardAssetUrl('/asset/images/logo.png');", $printCore);
        $this->assertHtmlContains('`src="${logoUrl}"`', $printCore);
        $this->assertHtmlContains('export function getPrintOrgHeaderHTML()', $printCore);
        $this->assertHtmlContains('.print-org-header {', $printCore);
        $this->assertHtmlContains('.print-org-brand {', $printCore);
        $this->assertHtmlContains('.print-org-copy {', $printCore);
        $this->assertHtmlContains('width="62"', $printCore);
        $this->assertHtmlContains('height="62"', $printCore);
        $this->assertHtmlContains('style="flex:0 0 auto;width:62px;height:62px;object-fit:contain;margin:0;"', $printCore);
        $this->assertHtmlContains('style="display:flex;align-items:center;justify-content:center;gap:18px;width:100%;margin:0 0 6px;"', $printCore);
        $this->assertHtmlContains('const getPrintOrgHeaderHTML_ = window.getPrintOrgHeaderHTML;', $printHelpers);
    }

    public function test_print_templates_force_exact_color_output(): void
    {
        $html = file_get_contents(resource_path('js/dashboard/export/print-core.js'));

        $this->assertHtmlContains('-webkit-print-color-adjust: exact !important;', $html);
        $this->assertHtmlContains('print-color-adjust: exact !important;', $html);
        $this->assertHtmlContains('color-adjust: exact !important;', $html);
    }

    public function test_dashboard_source_files_avoid_decorative_unicode_and_external_logo_assets(): void
    {
        $files = [
            resource_path('js/dashboard/export/print-core.js'),
            resource_path('js/dashboard/export/print-browser.js'),
            resource_path('js/dashboard/export/unit-ditanya.js'),
            resource_path('js/dashboard/export/claim-garansi.js'),
            resource_path('views/dashboard/partials/menus/analisa-insight.blade.php'),
            resource_path('views/dashboard/partials/menus/meta-story.blade.php'),
            resource_path('views/dashboard/partials/shell/head.blade.php'),
            resource_path('views/welcome.blade.php'),
            base_path('routes/web.php'),
            public_path('marketing-dashboard.html'),
            public_path('design-system.html'),
        ];

        foreach ($files as $file) {
            $contents = file_get_contents($file);

            $this->assertIsString($contents);
            $this->assertSame(
                0,
                preg_match('/[─═■]/u', $contents),
                "Failed asserting that [{$file}] does not contain decorative Unicode characters."
            );
            $this->assertStringNotContainsString('https://dashboard.purapuraponsel.com/asset/images/logo.png', $contents);
            $this->assertStringNotContainsString('https://dashboard.purapuraponsel.com/asset/images/favicon.ico', $contents);
            $this->assertStringNotContainsString('https://purapuraponsel.com/asset/images/logo.png', $contents);
        }
    }

    public function test_custom_pdf_exports_reuse_shared_print_template(): void
    {
        $printCore = file_get_contents(resource_path('js/dashboard/export/print-core.js'));
        $promoExport = file_get_contents(resource_path('js/dashboard/export/promo.js'));
        $adsLogExport = file_get_contents(resource_path('js/dashboard/export/ads-log.js'));

        $this->assertHtmlContains("export function getPrintHTML({", $printCore);
        $this->assertHtmlContains('getPrintBaseStyles(),', $printCore);
        $this->assertHtmlContains('getPrintOrgHeaderHTML(),', $printCore);
        $this->assertHtmlContains("/vendor/dashboard/fontawesome/css/all.min.css", $printCore);
        $this->assertHtmlNotContains('https://cdn.jsdelivr.net', $printCore);
        $this->assertHtmlContains("const html = getPrintHTML({", $promoExport);
        $this->assertHtmlContains("title: 'PROGRAM PROMO'", $promoExport);
        $this->assertHtmlContains("openPrintWindow(html, 'Program Promo', {", $promoExport);
        $this->assertHtmlContains("const html = getPrintHTML({", $adsLogExport);
        $this->assertHtmlContains("title: 'ADS PERFORMANCE REPORT'", $adsLogExport);
        $this->assertHtmlContains("openPrintWindow(html, 'Ads Report', {", $adsLogExport);
    }

    public function test_print_templates_use_readable_typography_for_exports(): void
    {
        $printCore = file_get_contents(resource_path('js/dashboard/export/print-core.js'));
        $bonusExport = file_get_contents(resource_path('js/dashboard/export/bonus.js'));

        $this->assertHtmlContains("body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 10pt; line-height: 1.45;", $printCore);
        $this->assertHtmlContains("h1 { font-family: 'Times New Roman', Times, serif; font-size: 18pt;", $printCore);
        $this->assertHtmlContains("table { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 9pt;", $printCore);
        $this->assertHtmlContains("td { border-bottom: 1px solid #e2e8f0; border-left: none; border-right: none; border-top: none; padding: 6px; font-size: 9pt;", $printCore);
        $this->assertHtmlContains(".report-meta { margin: -6px 0 12px; text-align: center; color: var(--color-stone-500, #78716c); font-size: 8pt; }", $printCore);
        $this->assertHtmlContains(".signature-section { margin-top: 28px; page-break-inside: avoid; display: flex !important;", $printCore);
        $this->assertHtmlContains("headers: [", $bonusExport);
        $this->assertHtmlContains("'Konten & Platform',", $bonusExport);
        $this->assertHtmlContains("'Total Bonus',", $bonusExport);
    }

    public function test_print_window_waits_for_document_assets_before_printing(): void
    {
        $printCore = file_get_contents(resource_path('js/dashboard/export/print-core.js'));
        $printHelpers = file_get_contents(resource_path('views/dashboard/partials/scripts/print-helpers.blade.php'));
        $printSources = $printCore . "\n" . $printHelpers;

        $this->assertHtmlContains('export function waitForPrintAssets(printWindow) {', $printCore);
        $this->assertHtmlContains('export function buildStandalonePrintHtml(html, { autoPrint = false } = {}) {', $printCore);
        $this->assertHtmlContains('const waitForPrintAssets_ = window.waitForPrintAssets;', $printHelpers);
        $this->assertHtmlContains("const printDocumentHtml = buildStandalonePrintHtml_(html);", $printHelpers);
        $this->assertHtmlContains('const submitBrowserPrintJob_ = (printHtml) => {', $printHelpers);
        $this->assertHtmlContains("return jsonApi('/print-job', {", $printHelpers);
        $this->assertHtmlContains("body: JSON.stringify({ html: printHtml }),", $printHelpers);
        $this->assertHtmlContains("const autoPrintHtml = buildStandalonePrintHtml_(html, { autoPrint: true });", $printHelpers);
        $this->assertHtmlContains("pw.document.write('<!DOCTYPE html><html><head><meta charset=\"UTF-8\"></head><body style=\"font-family:Arial,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0\"><p style=\"color:#64748b;font-size:14px\">[1/2] Menyiapkan dokumen print...</p></body></html>');", $printHelpers);
        $this->assertHtmlContains("const backendUrl = new URL(resolveAppUrl('/print-job'), window.location.origin).origin.replace(/\\/+$/, '');", $printHelpers);
        $this->assertHtmlContains("const token = String((result && result.token) || '').trim();", $printHelpers);
        $this->assertHtmlContains('pw.location.href = `${backendUrl}/print-job/${encodeURIComponent(token)}`;', $printHelpers);
        $this->assertHtmlContains("_pwStatus('[2/2] Dokumen siap - membuka print...');", $printHelpers);
        $this->assertHtmlContains("notifyError('Print gagal', err, 'Dokumen print harus dibuka dari backend print-job.');", $printHelpers);
        $this->assertHtmlNotContains('fallbackWrite', $printSources);
        $this->assertHtmlNotContains('createObjectURL(blob)', $printSources);
        $this->assertHtmlNotContains('new Blob([printDocumentHtml]', $printSources);
        $this->assertHtmlNotContains("pw.document.write(autoPrintHtml);", $printSources);
        $this->assertHtmlNotContains("setTimeout(() => { try { pw.print(); } catch (e) { } }, 500);", $printSources);
        $this->assertHtmlNotContains('pw.location.href = `${execBaseUrl}/print?printJob=${encodeURIComponent(printJobKey)}`;', $printSources);
    }

    public function test_print_job_route_keeps_html_available_during_cache_ttl(): void
    {
        cache()->put('ppp_print_job_deadbeef1234', '<!DOCTYPE html><html><body>Demo Print</body></html>', now()->addMinutes(5));

        $firstResponse = $this->get('/print-job/deadbeef1234');
        $firstResponse->assertOk();
        $firstResponse->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $firstResponse->assertSee('Demo Print', false);

        $secondResponse = $this->get('/print-job/deadbeef1234');
        $secondResponse->assertOk();
        $secondResponse->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $secondResponse->assertSee('Demo Print', false);
    }

    public function test_profile_tab_includes_dashboard_user_management_panel(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('Manajemen User', $html);
        $this->assertHtmlContains("switchTab('auth_users')", $html);
        $this->assertHtmlContains("activeTab === 'auth_users'", $html);
        $this->assertHtmlContains('/api/auth/users', $html);
        $this->assertHtmlContains('authUserForm.username', $html);
        $this->assertHtmlContains('loadAuthUsers()', $html);
    }

    public function test_settings_sidebar_and_shell_include_activity_logs_panel(): void
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        $this->assertHtmlContains('Activity Logs', $html);
        $this->assertHtmlContains("switchTab('activity_logs')", $html);
        $this->assertHtmlContains("activeTab === 'activity_logs'", $html);
        $this->assertHtmlContains("activity_logs: { label: 'Activity Logs', category: 'System Settings' }", $html);
        $this->assertHtmlContains("newTab === 'activity_logs'", $html);
        $this->assertHtmlContains('loadActivityLogs();', $html);
        $this->assertHtmlContains('getActivityLogs(filters = {})', $html);
    }
}
