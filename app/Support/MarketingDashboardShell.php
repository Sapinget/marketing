<?php

namespace App\Support;

use RuntimeException;

class MarketingDashboardShell
{
    private const CALENDAR_MENU_START_MARKER = '<div v-if="activeTab === \'calendar\'" class="space-y-6 animate-fadeIn pb-10">';
    private const CALENDAR_MENU_END_MARKER = '<!-- Story Schedule View -->';
    private const STORY_MENU_START_MARKER = '<div v-if="activeTab === \'story\'" class="space-y-6 animate-fadeIn pb-10">';
    private const STORY_MENU_END_MARKER = '<!-- Analisa Insight & Tren View -->';
    private const IDEATION_MENU_START_MARKER = '<div v-if="activeTab === \'ideation\'" class="space-y-6 animate-fadeIn pb-10">';
    private const IDEATION_MENU_END_MARKER = '<!-- Distribution View -->';
    private const DISTRIBUTION_MENU_START_MARKER = '<div v-if="activeTab === \'distribution\'" class="space-y-6 animate-fadeIn pb-10">';
    private const DISTRIBUTION_MENU_END_MARKER = '<!-- Analytics View -->';
    private const ANALYTICS_MENU_START_MARKER = '<div v-if="activeTab === \'analytics\'" class="space-y-6 animate-fadeIn pb-10">';
    private const ANALYTICS_MENU_END_MARKER = '<!-- Calendar View -->';
    private const ANALYTICS_PDF_CLUSTER_START_MARKER = 'const exportAnalyticsToPDF = () => {';
    private const ANALYTICS_PDF_CLUSTER_END_MARKER = 'const formatShortDate = (dateStr) => {';
    private const CUSTOMER_SERVICE_PDF_CLUSTER_START_MARKER = 'const exportUnitDitanyaToExcel = () => {';
    private const CUSTOMER_SERVICE_PDF_CLUSTER_END_MARKER = 'const formatCurrency = (value) => {';
    private const SALES_AND_PROMO_PDF_CLUSTER_START_MARKER = 'const exportBonusToPDF = () => {';
    private const SALES_AND_PROMO_PDF_CLUSTER_END_MARKER = 'watch([sellOutSearch, sellOutVendorFilter, sellOutMonth], () => { sellOutPage.value = 1; });';
    private const PRINT_HELPERS_START_MARKER = '// PDF print helpers - delegates to IIFE bundle (dashboard-exports.js)';
    private const PRINT_HELPERS_END_MARKER = '// Ads Log PDF';
    private const ADS_LOG_PDF_EXPORT_START_MARKER = '// Ads Log PDF';
    private const ADS_LOG_PDF_EXPORT_END_MARKER = '// Harga & Kompetitor';
    private const PRICE_COMPARISON_PDF_EXPORT_START_MARKER = 'const exportPriceComparisonToPDF = () => {';
    private const PRICE_COMPARISON_PDF_EXPORT_END_MARKER = '// Laporan Event (LPJK)';
    private const LPJK_DETAIL_PDF_EXPORT_START_MARKER = 'const exportLpjkDetailToPDF = () => {';
    private const LPJK_DETAIL_PDF_EXPORT_END_MARKER = '// Budgeting';
    private const BUDGET_PDF_EXPORT_START_MARKER = 'const exportBudgetToPDF = () => {';
    private const BUDGET_PDF_EXPORT_END_MARKER = 'const exportBudgetToExcel = () => {';

    private const MENU_DASHBOARD_START_MARKER = '<div v-if="activeTab === \'dashboard\'" class="space-y-4">';
    private const MENU_DASHBOARD_END_MARKER = '<!-- Master Plan View -->';
    private const MENU_MASTERPLAN_START_MARKER = '<!-- Master Plan View -->';
    private const MENU_MASTERPLAN_END_MARKER = '<!-- Ideation View -->';
    private const MENU_ANALISAINSIGHT_START_MARKER = '<!-- Analisa Insight & Tren View -->';
    private const MENU_ANALISAINSIGHT_END_MARKER = '<div v-if="activeTab === \'meta_story\'"';
    private const MENU_METASTORY_START_MARKER = '<div v-if="activeTab === \'meta_story\'"';
    private const MENU_METASTORY_END_MARKER = '<div v-if="activeTab === \'meta_feed\'"';
    private const MENU_METAFEED_START_MARKER = '<div v-if="activeTab === \'meta_feed\'"';
    private const MENU_METAFEED_END_MARKER = '<!-- Unboxing View -->';
    private const MENU_UNBOXING_START_MARKER = '<!-- Unboxing View -->';
    private const MENU_UNBOXING_END_MARKER = '<!-- Top Konten View -->';
    private const MENU_TOPCONTENT_START_MARKER = '<!-- Top Konten View -->';
    private const MENU_TOPCONTENT_END_MARKER = '<!-- Low Konten View -->';
    private const MENU_LOWCONTENT_START_MARKER = '<!-- Low Konten View -->';
    private const MENU_LOWCONTENT_END_MARKER = '<!-- Order Online View -->';
    private const MENU_ORDERONLINE_START_MARKER = '<!-- Order Online View -->';
    private const MENU_ORDERONLINE_END_MARKER = '<!-- Unit Ditanya View -->';
    private const MENU_UNITDITANYA_START_MARKER = '<!-- Unit Ditanya View -->';
    private const MENU_UNITDITANYA_END_MARKER = '<!-- Claim Garansi View -->';
    private const MENU_CLAIMGARANSI_START_MARKER = '<!-- Claim Garansi View -->';
    private const MENU_CLAIMGARANSI_END_MARKER = '<!-- Keep Barang / Retur View -->';
    private const MENU_KEEPBARANG_START_MARKER = '<!-- Keep Barang / Retur View -->';
    private const MENU_KEEPBARANG_END_MARKER = '<!-- Settings View -->';
    private const MENU_SETTINGS_START_MARKER = '<!-- Settings View -->';
    private const MENU_SETTINGS_END_MARKER = '<!-- Nama Stock View -->';
    private const MENU_NAMASTOCK_START_MARKER = '<!-- Nama Stock View -->';
    private const MENU_NAMASTOCK_END_MARKER = '<!-- Profile Setting View -->';
    private const MENU_PROFILE_START_MARKER = '<!-- Profile Setting View -->';
    private const MENU_PROFILE_END_MARKER = '<div v-if="activeTab === \'auth_users\'" class="space-y-6 animate-fadeIn pb-10"';
    private const MENU_AUTHUSERS_START_MARKER = '<div v-if="activeTab === \'auth_users\'" class="space-y-6 animate-fadeIn pb-10"';
    private const MENU_AUTHUSERS_END_MARKER = '<div v-if="activeTab === \'activity_logs\'" class="space-y-6 animate-fadeIn pb-10"';
    private const MENU_ACTIVITYLOGS_START_MARKER = '<div v-if="activeTab === \'activity_logs\'" class="space-y-6 animate-fadeIn pb-10"';
    private const MENU_ACTIVITYLOGS_END_MARKER = '<!-- Program Promo tab -->';
    private const MENU_PROGRAMPROMO_START_MARKER = '<!-- Program Promo tab -->';
    private const MENU_PROGRAMPROMO_END_MARKER = '<!-- Bonus Report tab -->';
    private const MENU_BONUSREPORT_START_MARKER = '<!-- Bonus Report tab -->';
    private const MENU_BONUSREPORT_END_MARKER = '<!-- Talent Bonus tab -->';
    private const MENU_TALENTBONUS_START_MARKER = '<!-- Talent Bonus tab -->';
    private const MENU_TALENTBONUS_END_MARKER = '<!-- Editor Performance tab -->';
    private const MENU_EDITORPERFORMANCE_START_MARKER = '<!-- Editor Performance tab -->';
    private const MENU_EDITORPERFORMANCE_END_MARKER = '<!-- Harga & Kompetitor tab -->';
    private const MENU_HARGAKOMPETITOR_START_MARKER = '<!-- Harga & Kompetitor tab -->';
    private const MENU_HARGAKOMPETITOR_END_MARKER = '<!-- Laporan Event tab -->';
    private const MENU_LAPORANEVENT_START_MARKER = '<!-- Laporan Event tab -->';
    private const MENU_LAPORANEVENT_END_MARKER = '<!-- Budgeting tab -->';
    private const MENU_BUDGETING_START_MARKER = '<!-- Budgeting tab -->';
    private const MENU_BUDGETING_END_MARKER = '<script>';
    public function build(string $backendUrl): array
    {
        $html = file_get_contents(public_path('marketing-dashboard.html'));

        if ($html === false) {
            throw new RuntimeException('Gagal membaca marketing-dashboard.html.');
        }

        // Extract html tag attributes (e.g. lang="id")
        preg_match('/<html\b([^>]*)>/i', $html, $htmlMatch);
        $htmlAttributes = $htmlMatch[1] ?? '';

        // Extract body tag attributes (e.g. class="...")
        $bodyOpenPos = stripos($html, '<body');
        $bodyClosePos = strripos($html, '</body>');

        if ($bodyOpenPos === false || $bodyClosePos === false) {
            throw new RuntimeException('Struktur marketing-dashboard.html tidak valid.');
        }

        $bodyTagEndPos = strpos($html, '>', $bodyOpenPos);

        if ($bodyTagEndPos === false) {
            throw new RuntimeException('Struktur marketing-dashboard.html tidak valid.');
        }

        $bodyAttributes = substr(
            $html,
            $bodyOpenPos + strlen('<body'),
            $bodyTagEndPos - ($bodyOpenPos + strlen('<body'))
        );

        // Extract body content: everything between <body> and </body>
        // This is the Vue app markup + scripts (templates, createApp, print helpers)
        $bodyHtml = substr($html, $bodyTagEndPos + 1, $bodyClosePos - ($bodyTagEndPos + 1));

        $fragments = [
            'htmlAttributes' => $htmlAttributes,
            'bodyAttributes' => $bodyAttributes,
            'bodyHtml' => trim($bodyHtml),
            'backendUrl' => rtrim($backendUrl, '/'),
        ];

        $fragments = array_merge($fragments, $this->extractDashboardFragments($fragments['bodyHtml']));

        if (isset($fragments['bodyAfterDashboardMenu'])) {
            $fragments = array_merge($fragments, $this->extractMasterPlanFragments($fragments['bodyAfterDashboardMenu']));
        }

        $ideationSource = $fragments['bodyAfterMasterPlanMenu'] ?? $fragments['bodyAfterDashboardMenu'] ?? $fragments['bodyHtml'];
        $fragments = array_merge($fragments, $this->extractIdeationMenuFragments($ideationSource));

        if (isset($fragments['bodyAfterIdeationMenu'])) {
            $fragments = array_merge($fragments, $this->extractDistributionMenuFragments($fragments['bodyAfterIdeationMenu']));
        }

        if (isset($fragments['bodyAfterDistributionMenu'])) {
            $fragments = array_merge($fragments, $this->extractAnalyticsMenuFragments($fragments['bodyAfterDistributionMenu']));
        }

        if (isset($fragments['bodyAfterAnalyticsMenu'])) {
            $fragments = array_merge($fragments, $this->extractCalendarMenuFragments($fragments['bodyAfterAnalyticsMenu']));
        }

        if (isset($fragments['bodyAfterCalendarMenu'])) {
            $fragments = array_merge($fragments, $this->extractStoryMenuFragments($fragments['bodyAfterCalendarMenu']));
        }

        // PR 4: batch 2 - analisa_insight through low_content
        if (isset($fragments['bodyAfterStoryMenu'])) {
            $fragments = array_merge($fragments, $this->extractAnalisaInsightFragments($fragments['bodyAfterStoryMenu']));
        }
        if (isset($fragments['bodyAfterAnalisaInsightMenu'])) {
            $fragments = array_merge($fragments, $this->extractMetaStoryFragments($fragments['bodyAfterAnalisaInsightMenu']));
        }
        if (isset($fragments['bodyAfterMetaStoryMenu'])) {
            $fragments = array_merge($fragments, $this->extractMetaFeedFragments($fragments['bodyAfterMetaStoryMenu']));
        }
        if (isset($fragments['bodyAfterMetaFeedMenu'])) {
            $fragments = array_merge($fragments, $this->extractUnboxingFragments($fragments['bodyAfterMetaFeedMenu']));
        }
        if (isset($fragments['bodyAfterUnboxingMenu'])) {
            $fragments = array_merge($fragments, $this->extractTopContentFragments($fragments['bodyAfterUnboxingMenu']));
        }
        if (isset($fragments['bodyAfterTopContentMenu'])) {
            $fragments = array_merge($fragments, $this->extractLowContentFragments($fragments['bodyAfterTopContentMenu']));
        }
        if (isset($fragments['bodyAfterLowContentMenu'])) {
            $fragments = array_merge($fragments, $this->extractOrderOnlineFragments($fragments['bodyAfterLowContentMenu']));
        }
        if (isset($fragments['bodyAfterOrderOnlineMenu'])) {
            $fragments = array_merge($fragments, $this->extractUnitDitanyaFragments($fragments['bodyAfterOrderOnlineMenu']));
        }
        if (isset($fragments['bodyAfterUnitDitanyaMenu'])) {
            $fragments = array_merge($fragments, $this->extractClaimGaransiFragments($fragments['bodyAfterUnitDitanyaMenu']));
        }
        if (isset($fragments['bodyAfterClaimGaransiMenu'])) {
            $fragments = array_merge($fragments, $this->extractKeepBarangFragments($fragments['bodyAfterClaimGaransiMenu']));
        }
        if (isset($fragments['bodyAfterKeepBarangMenu'])) {
            $fragments = array_merge($fragments, $this->extractSettingsFragments($fragments['bodyAfterKeepBarangMenu']));
        }
        if (isset($fragments['bodyAfterSettingsMenu'])) {
            $fragments = array_merge($fragments, $this->extractNamaStockFragments($fragments['bodyAfterSettingsMenu']));
        }
        if (isset($fragments['bodyAfterNamaStockMenu'])) {
            $fragments = array_merge($fragments, $this->extractProfileFragments($fragments['bodyAfterNamaStockMenu']));
        }
        if (isset($fragments['bodyAfterProfileMenu'])) {
            $fragments = array_merge($fragments, $this->extractAuthUsersFragments($fragments['bodyAfterProfileMenu']));
        }
        if (isset($fragments['bodyAfterAuthUsersMenu'])) {
            $fragments = array_merge($fragments, $this->extractActivityLogsFragments($fragments['bodyAfterAuthUsersMenu']));
        }
        if (isset($fragments['bodyAfterActivityLogsMenu'])) {
            $fragments = array_merge($fragments, $this->extractProgramPromoFragments($fragments['bodyAfterActivityLogsMenu']));
        }

        if (isset($fragments['bodyAfterProgramPromoMenu'])) {
            $fragments = array_merge($fragments, $this->extractBonusReportFragments($fragments['bodyAfterProgramPromoMenu']));
        }

        if (isset($fragments['bodyAfterBonusReportMenu'])) {
            $fragments = array_merge($fragments, $this->extractTalentBonusFragments($fragments['bodyAfterBonusReportMenu']));
        }

        if (isset($fragments['bodyAfterTalentBonusMenu'])) {
            $fragments = array_merge($fragments, $this->extractEditorPerformanceFragments($fragments['bodyAfterTalentBonusMenu']));
        }

        if (isset($fragments['bodyAfterEditorPerformanceMenu'])) {
            $fragments = array_merge($fragments, $this->extractHargaKompetitorFragments($fragments['bodyAfterEditorPerformanceMenu']));
        }

        if (isset($fragments['bodyAfterHargaKompetitorMenu'])) {
            $fragments = array_merge($fragments, $this->extractLaporanEventFragments($fragments['bodyAfterHargaKompetitorMenu']));
        }

        if (isset($fragments['bodyAfterLaporanEventMenu'])) {
            $fragments = array_merge($fragments, $this->extractBudgetingFragments($fragments['bodyAfterLaporanEventMenu']));
        }

        $analyticsSource = $fragments['bodyAfterBudgetingMenu'] ?? $fragments['bodyAfterLaporanEventMenu'] ?? $fragments['bodyAfterHargaKompetitorMenu'] ?? $fragments['bodyAfterEditorPerformanceMenu'] ?? $fragments['bodyAfterProgramPromoMenu'] ?? $fragments['bodyAfterStoryMenu'] ?? $fragments['bodyAfterCalendarMenu'] ?? $fragments['bodyHtml'];
        $fragments = array_merge($fragments, $this->extractAnalyticsPdfClusterFragments($analyticsSource));

        if (isset($fragments['bodyAfterAnalyticsPdfCluster'])) {
            $fragments = array_merge($fragments, $this->extractCustomerServicePdfClusterFragments($fragments['bodyAfterAnalyticsPdfCluster']));
        }

        if (isset($fragments['bodyAfterCustomerServicePdfCluster'])) {
            $fragments = array_merge($fragments, $this->extractSalesAndPromoPdfClusterFragments($fragments['bodyAfterCustomerServicePdfCluster']));
        }

        $printHelpersSource = $fragments['bodyAfterSalesAndPromoPdfCluster'] ?? $fragments['bodyHtml'];
        $fragments = array_merge($fragments, $this->extractPrintHelperFragments($printHelpersSource));

        if (isset($fragments['bodyAfterPrintHelpers'])) {
            $fragments = array_merge($fragments, $this->extractAdsLogPdfExportFragments($fragments['bodyAfterPrintHelpers']));
        }

        if (isset($fragments['bodyAfterAdsLogPdfExport'])) {
            $fragments = array_merge($fragments, $this->extractPriceComparisonPdfExportFragments($fragments['bodyAfterAdsLogPdfExport']));
        }

        if (isset($fragments['bodyAfterPriceComparisonPdfExport'])) {
            $fragments = array_merge($fragments, $this->extractLpjkDetailPdfExportFragments($fragments['bodyAfterPriceComparisonPdfExport']));
        }

        if (isset($fragments['bodyAfterLpjkDetailPdfExport'])) {
            $fragments = array_merge($fragments, $this->extractBudgetPdfExportFragments($fragments['bodyAfterLpjkDetailPdfExport']));
        }

        return $fragments;
    }

    /**
     * @return array<string, string>
     */
    private function extractCalendarMenuFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::CALENDAR_MENU_START_MARKER);
        $endPos = strpos($bodyHtml, self::CALENDAR_MENU_END_MARKER);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeCalendarMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterCalendarMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function extractStoryMenuFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::STORY_MENU_START_MARKER);
        $endPos = strpos($bodyHtml, self::STORY_MENU_END_MARKER);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeStoryMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterStoryMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function extractIdeationMenuFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::IDEATION_MENU_START_MARKER);
        $endPos = strpos($bodyHtml, self::IDEATION_MENU_END_MARKER);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeIdeationMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterIdeationMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function extractDistributionMenuFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::DISTRIBUTION_MENU_START_MARKER);
        $endPos = strpos($bodyHtml, self::DISTRIBUTION_MENU_END_MARKER);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeDistributionMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterDistributionMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function extractAnalyticsMenuFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::ANALYTICS_MENU_START_MARKER);
        $endPos = strpos($bodyHtml, self::ANALYTICS_MENU_END_MARKER);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeAnalyticsMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterAnalyticsMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function extractAnalyticsPdfClusterFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::ANALYTICS_PDF_CLUSTER_START_MARKER);
        $endPos = strpos($bodyHtml, self::ANALYTICS_PDF_CLUSTER_END_MARKER);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeAnalyticsPdfCluster' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterAnalyticsPdfCluster' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function extractCustomerServicePdfClusterFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::CUSTOMER_SERVICE_PDF_CLUSTER_START_MARKER);
        $endPos = strpos($bodyHtml, self::CUSTOMER_SERVICE_PDF_CLUSTER_END_MARKER);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeCustomerServicePdfCluster' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterCustomerServicePdfCluster' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function extractSalesAndPromoPdfClusterFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::SALES_AND_PROMO_PDF_CLUSTER_START_MARKER);
        $endPos = strpos($bodyHtml, self::SALES_AND_PROMO_PDF_CLUSTER_END_MARKER);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeSalesAndPromoPdfCluster' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterSalesAndPromoPdfCluster' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    /**
     * Split the legacy Vue body script around the print helper block so Blade can
     * own that subsystem independently without changing its execution order.
     *
     * @return array<string, string>
     */
    private function extractPrintHelperFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::PRINT_HELPERS_START_MARKER);
        $endPos = strpos($bodyHtml, self::PRINT_HELPERS_END_MARKER);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforePrintHelpers' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterPrintHelpers' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function extractAdsLogPdfExportFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::ADS_LOG_PDF_EXPORT_START_MARKER);
        $endPos = strpos($bodyHtml, self::ADS_LOG_PDF_EXPORT_END_MARKER);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeAdsLogPdfExport' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterAdsLogPdfExport' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function extractPriceComparisonPdfExportFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::PRICE_COMPARISON_PDF_EXPORT_START_MARKER);
        $endPos = strpos($bodyHtml, self::PRICE_COMPARISON_PDF_EXPORT_END_MARKER);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforePriceComparisonPdfExport' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterPriceComparisonPdfExport' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function extractLpjkDetailPdfExportFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::LPJK_DETAIL_PDF_EXPORT_START_MARKER);
        $endPos = strpos($bodyHtml, self::LPJK_DETAIL_PDF_EXPORT_END_MARKER);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeLpjkDetailPdfExport' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterLpjkDetailPdfExport' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function extractBudgetPdfExportFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::BUDGET_PDF_EXPORT_START_MARKER);
        $endPos = strpos($bodyHtml, self::BUDGET_PDF_EXPORT_END_MARKER);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeBudgetPdfExport' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterBudgetPdfExport' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }
    private function extractDashboardFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::MENU_DASHBOARD_START_MARKER);
        $endPos = strpos($bodyHtml, self::MENU_DASHBOARD_END_MARKER, $startPos + 1);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeDashboardMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterDashboardMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    private function extractMasterPlanFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::MENU_MASTERPLAN_START_MARKER);
        $endPos = strpos($bodyHtml, self::MENU_MASTERPLAN_END_MARKER, $startPos + 1);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeMasterPlanMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterMasterPlanMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    private function extractAnalisaInsightFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::MENU_ANALISAINSIGHT_START_MARKER);
        $endPos = strpos($bodyHtml, self::MENU_ANALISAINSIGHT_END_MARKER, $startPos + 1);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeAnalisaInsightMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterAnalisaInsightMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    private function extractMetaStoryFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::MENU_METASTORY_START_MARKER);
        $endPos = strpos($bodyHtml, self::MENU_METASTORY_END_MARKER, $startPos + 1);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeMetaStoryMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterMetaStoryMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    private function extractMetaFeedFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::MENU_METAFEED_START_MARKER);
        $endPos = strpos($bodyHtml, self::MENU_METAFEED_END_MARKER, $startPos + 1);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeMetaFeedMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterMetaFeedMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    private function extractUnboxingFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::MENU_UNBOXING_START_MARKER);
        $endPos = strpos($bodyHtml, self::MENU_UNBOXING_END_MARKER, $startPos + 1);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeUnboxingMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterUnboxingMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    private function extractTopContentFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::MENU_TOPCONTENT_START_MARKER);
        $endPos = strpos($bodyHtml, self::MENU_TOPCONTENT_END_MARKER, $startPos + 1);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeTopContentMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterTopContentMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    private function extractLowContentFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::MENU_LOWCONTENT_START_MARKER);
        $endPos = strpos($bodyHtml, self::MENU_LOWCONTENT_END_MARKER, $startPos + 1);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeLowContentMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterLowContentMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    private function extractOrderOnlineFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::MENU_ORDERONLINE_START_MARKER);
        $endPos = strpos($bodyHtml, self::MENU_ORDERONLINE_END_MARKER, $startPos + 1);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeOrderOnlineMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterOrderOnlineMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    private function extractUnitDitanyaFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::MENU_UNITDITANYA_START_MARKER);
        $endPos = strpos($bodyHtml, self::MENU_UNITDITANYA_END_MARKER, $startPos + 1);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeUnitDitanyaMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterUnitDitanyaMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    private function extractClaimGaransiFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::MENU_CLAIMGARANSI_START_MARKER);
        $endPos = strpos($bodyHtml, self::MENU_CLAIMGARANSI_END_MARKER, $startPos + 1);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeClaimGaransiMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterClaimGaransiMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    private function extractKeepBarangFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::MENU_KEEPBARANG_START_MARKER);
        $endPos = strpos($bodyHtml, self::MENU_KEEPBARANG_END_MARKER, $startPos + 1);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeKeepBarangMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterKeepBarangMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    private function extractSettingsFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::MENU_SETTINGS_START_MARKER);
        $endPos = strpos($bodyHtml, self::MENU_SETTINGS_END_MARKER, $startPos + 1);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeSettingsMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterSettingsMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    private function extractNamaStockFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::MENU_NAMASTOCK_START_MARKER);
        $endPos = strpos($bodyHtml, self::MENU_NAMASTOCK_END_MARKER, $startPos + 1);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeNamaStockMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterNamaStockMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    private function extractProfileFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::MENU_PROFILE_START_MARKER);
        $endPos = strpos($bodyHtml, self::MENU_PROFILE_END_MARKER, $startPos + 1);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeProfileMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterProfileMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    private function extractAuthUsersFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::MENU_AUTHUSERS_START_MARKER);
        $endPos = strpos($bodyHtml, self::MENU_AUTHUSERS_END_MARKER, $startPos + 1);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeAuthUsersMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterAuthUsersMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    private function extractActivityLogsFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::MENU_ACTIVITYLOGS_START_MARKER);
        $endPos = strpos($bodyHtml, self::MENU_ACTIVITYLOGS_END_MARKER, $startPos + 1);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeActivityLogsMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterActivityLogsMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    private function extractProgramPromoFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::MENU_PROGRAMPROMO_START_MARKER);
        $endPos = strpos($bodyHtml, self::MENU_PROGRAMPROMO_END_MARKER, $startPos + 1);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeProgramPromoMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterProgramPromoMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    private function extractBonusReportFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::MENU_BONUSREPORT_START_MARKER);
        $endPos = strpos($bodyHtml, self::MENU_BONUSREPORT_END_MARKER, $startPos + 1);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeBonusReportMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterBonusReportMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    private function extractTalentBonusFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::MENU_TALENTBONUS_START_MARKER);
        $endPos = strpos($bodyHtml, self::MENU_TALENTBONUS_END_MARKER, $startPos + 1);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeTalentBonusMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterTalentBonusMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    private function extractEditorPerformanceFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::MENU_EDITORPERFORMANCE_START_MARKER);
        $endPos = strpos($bodyHtml, self::MENU_EDITORPERFORMANCE_END_MARKER, $startPos + 1);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeEditorPerformanceMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterEditorPerformanceMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    private function extractHargaKompetitorFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::MENU_HARGAKOMPETITOR_START_MARKER);
        $endPos = strpos($bodyHtml, self::MENU_HARGAKOMPETITOR_END_MARKER, $startPos + 1);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeHargaKompetitorMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterHargaKompetitorMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    private function extractLaporanEventFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::MENU_LAPORANEVENT_START_MARKER);
        $endPos = strpos($bodyHtml, self::MENU_LAPORANEVENT_END_MARKER, $startPos + 1);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeLaporanEventMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterLaporanEventMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

    private function extractBudgetingFragments(string $bodyHtml): array
    {
        $startPos = strpos($bodyHtml, self::MENU_BUDGETING_START_MARKER);
        $endPos = strpos($bodyHtml, self::MENU_BUDGETING_END_MARKER, $startPos + 1);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            return [];
        }

        return [
            'bodyBeforeBudgetingMenu' => rtrim(substr($bodyHtml, 0, $startPos)),
            'bodyAfterBudgetingMenu' => ltrim(substr($bodyHtml, $endPos)),
        ];
    }

}
