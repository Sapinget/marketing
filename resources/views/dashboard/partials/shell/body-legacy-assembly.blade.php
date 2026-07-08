@if (isset($bodyBeforePrintHelpers, $bodyAfterPrintHelpers))
@if (isset($bodyBeforeAnalyticsPdfCluster, $bodyAfterAnalyticsPdfCluster))
{!! $bodyBeforeAnalyticsPdfCluster !!}
@include('dashboard.partials.scripts.export-analytics-cluster')
@if (isset($bodyBeforeCustomerServicePdfCluster, $bodyAfterCustomerServicePdfCluster))
{!! $bodyBeforeCustomerServicePdfCluster !!}
@include('dashboard.partials.scripts.export-customer-service-pdfs')
@if (isset($bodyBeforeSalesAndPromoPdfCluster, $bodyAfterSalesAndPromoPdfCluster))
{!! $bodyBeforeSalesAndPromoPdfCluster !!}
@include('dashboard.partials.scripts.export-sales-and-promo-pdfs')
{!! $bodyBeforePrintHelpers !!}
@else
{!! $bodyAfterCustomerServicePdfCluster !!}
@endif
@else
{!! $bodyAfterAnalyticsPdfCluster !!}
@endif
@else
{!! $bodyBeforePrintHelpers !!}
@endif
@include('dashboard.partials.scripts.print-helpers')
@include('dashboard.partials.scripts.export-reporting-bridge')
@if (isset($bodyBeforeAdsLogPdfExport, $bodyAfterAdsLogPdfExport))
{!! $bodyBeforeAdsLogPdfExport !!}
@include('dashboard.partials.scripts.export-ads-log-pdf')
@if (isset($bodyBeforePriceComparisonPdfExport, $bodyAfterPriceComparisonPdfExport))
{!! $bodyBeforePriceComparisonPdfExport !!}
@include('dashboard.partials.scripts.export-price-comparison-pdf')
@if (isset($bodyBeforeLpjkDetailPdfExport, $bodyAfterLpjkDetailPdfExport))
{!! $bodyBeforeLpjkDetailPdfExport !!}
@include('dashboard.partials.scripts.export-lpjk-detail-pdf')
@if (isset($bodyBeforeBudgetPdfExport, $bodyAfterBudgetPdfExport))
{!! $bodyBeforeBudgetPdfExport !!}
@include('dashboard.partials.scripts.export-budget-pdf')
{!! $bodyAfterBudgetPdfExport !!}
@else
{!! $bodyAfterLpjkDetailPdfExport !!}
@endif
@else
{!! $bodyAfterPriceComparisonPdfExport !!}
@endif
@else
{!! $bodyAfterAdsLogPdfExport !!}
@endif
@else
{!! $bodyAfterPrintHelpers !!}
@endif
@else
{!! $bodyAfterBudgetingMenu ?? $bodyAfterLaporanEventMenu ?? $bodyAfterHargaKompetitorMenu ?? $bodyAfterEditorPerformanceMenu ?? $bodyAfterTalentBonusMenu ?? $bodyAfterBonusReportMenu ?? $bodyAfterProgramPromoMenu ?? $bodyAfterActivityLogsMenu ?? $bodyAfterAuthUsersMenu ?? $bodyAfterProfileMenu ?? $bodyAfterNamaStockMenu ?? $bodyAfterSettingsMenu ?? $bodyAfterKeepBarangMenu ?? $bodyAfterClaimGaransiMenu ?? $bodyAfterUnitDitanyaMenu ?? $bodyAfterOrderOnlineMenu ?? $bodyAfterLowContentMenu ?? $bodyAfterTopContentMenu ?? $bodyAfterUnboxingMenu ?? $bodyAfterMetaFeedMenu ?? $bodyAfterMetaStoryMenu ?? $bodyAfterAnalisaInsightMenu ?? $bodyAfterStoryMenu ?? $bodyAfterCalendarMenu ?? $bodyAfterAnalyticsMenu ?? $bodyAfterDistributionMenu ?? $bodyAfterIdeationMenu ?? $bodyAfterMasterPlanMenu ?? $bodyAfterDashboardMenu ?? $bodyHtml !!}
@endif
