<?php

namespace App\Support;

class MarketingDashboardShell
{
    private const ALL_FRAGMENT_KEYS = [
        'bodyBeforeDashboardMenu', 'bodyAfterDashboardMenu',
        'bodyBeforeMasterPlanMenu', 'bodyAfterMasterPlanMenu',
        'bodyBeforeIdeationMenu', 'bodyAfterIdeationMenu',
        'bodyBeforeDistributionMenu', 'bodyAfterDistributionMenu',
        'bodyBeforeAnalyticsMenu', 'bodyAfterAnalyticsMenu',
        'bodyBeforeCalendarMenu', 'bodyAfterCalendarMenu',
        'bodyBeforeStoryMenu', 'bodyAfterStoryMenu',
        'bodyBeforeAnalisaInsightMenu', 'bodyAfterAnalisaInsightMenu',
        'bodyBeforeMetaStoryMenu', 'bodyAfterMetaStoryMenu',
        'bodyBeforeMetaFeedMenu', 'bodyAfterMetaFeedMenu',
        'bodyBeforeUnboxingMenu', 'bodyAfterUnboxingMenu',
        'bodyBeforeTopContentMenu', 'bodyAfterTopContentMenu',
        'bodyBeforeLowContentMenu', 'bodyAfterLowContentMenu',
        'bodyBeforeOrderOnlineMenu', 'bodyAfterOrderOnlineMenu',
        'bodyBeforeUnitDitanyaMenu', 'bodyAfterUnitDitanyaMenu',
        'bodyBeforeClaimGaransiMenu', 'bodyAfterClaimGaransiMenu',
        'bodyBeforeKeepBarangMenu', 'bodyAfterKeepBarangMenu',
        'bodyBeforeSettingsMenu', 'bodyAfterSettingsMenu',
        'bodyBeforeNamaStockMenu', 'bodyAfterNamaStockMenu',
        'bodyBeforeProfileMenu', 'bodyAfterProfileMenu',
        'bodyBeforeAuthUsersMenu', 'bodyAfterAuthUsersMenu',
        'bodyBeforeActivityLogsMenu', 'bodyAfterActivityLogsMenu',
        'bodyBeforeProgramPromoMenu', 'bodyAfterProgramPromoMenu',
        'bodyBeforeBonusReportMenu', 'bodyAfterBonusReportMenu',
        'bodyBeforeTalentBonusMenu', 'bodyAfterTalentBonusMenu',
        'bodyBeforeEditorPerformanceMenu', 'bodyAfterEditorPerformanceMenu',
        'bodyBeforeHargaKompetitorMenu', 'bodyAfterHargaKompetitorMenu',
        'bodyBeforeLaporanEventMenu', 'bodyAfterLaporanEventMenu',
        'bodyBeforeBudgetingMenu', 'bodyAfterBudgetingMenu',
        'bodyBeforeVueAppSetupStart', 'bodyAfterVueAppSetupStart',
        'bodyBeforeVueAppDateHelpers', 'bodyAfterVueAppDateHelpers',
        'bodyBeforeVueAppBootstrapNavigation', 'bodyAfterVueAppBootstrapNavigation',
        'bodyBeforeAuthSessionCluster', 'bodyAfterAuthSessionCluster',
        'bodyBeforeProtectedUserSettingsCluster', 'bodyAfterProtectedUserSettingsCluster',
        'bodyBeforeProfileUserMutationCluster', 'bodyAfterProfileUserMutationCluster',
        'bodyBeforeRunnerFactoriesCluster', 'bodyAfterRunnerFactoriesCluster',
        'bodyBeforeAnalyticsPdfCluster', 'bodyAfterAnalyticsPdfCluster',
        'bodyBeforeCustomerServicePdfCluster', 'bodyAfterCustomerServicePdfCluster',
        'bodyBeforeSalesAndPromoPdfCluster', 'bodyAfterSalesAndPromoPdfCluster',
        'bodyBeforePrintHelpers', 'bodyAfterPrintHelpers',
        'bodyBeforeAdsLogPdfExport', 'bodyAfterAdsLogPdfExport',
        'bodyBeforePriceComparisonPdfExport', 'bodyAfterPriceComparisonPdfExport',
        'bodyBeforeLpjkDetailPdfExport', 'bodyAfterLpjkDetailPdfExport',
        'bodyBeforeBudgetPdfExport', 'bodyAfterBudgetPdfExport',
        'bodyBeforeVueAppMountEnd', 'bodyAfterVueAppMountEnd',
        'bodyBeforeNotificationErrorUtilityCluster', 'bodyAfterNotificationErrorUtilityCluster',
        'bodyBeforeShellInteractionHelperCluster', 'bodyAfterShellInteractionHelperCluster',
    ];

    public function build(string $backendUrl): array
    {
        $fragments = [
            'htmlAttributes' => ' lang="id"',
            'bodyAttributes' => ' class="text-ppp-text antialiased"',
            'bodyHtml' => '',
            'backendUrl' => rtrim($backendUrl, '/'),
        ];

        foreach (self::ALL_FRAGMENT_KEYS as $key) {
            $fragments[$key] = '';
        }

        return $fragments;
    }
}
