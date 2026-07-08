<!doctype html>
<html{!! $htmlAttributes !!}>
    @include('dashboard.partials.shell.head', [
        'backendUrl' => $backendUrl,
    ])
    @include('dashboard.partials.shell.body', [
        'bodyAttributes' => $bodyAttributes,
        'bodyHtml' => $bodyHtml,
        'bodyBeforeAnalyticsPdfCluster' => $bodyBeforeAnalyticsPdfCluster ?? null,
        'bodyAfterAnalyticsPdfCluster' => $bodyAfterAnalyticsPdfCluster ?? null,
        'bodyBeforeCustomerServicePdfCluster' => $bodyBeforeCustomerServicePdfCluster ?? null,
        'bodyAfterCustomerServicePdfCluster' => $bodyAfterCustomerServicePdfCluster ?? null,
        'bodyBeforeSalesAndPromoPdfCluster' => $bodyBeforeSalesAndPromoPdfCluster ?? null,
        'bodyAfterSalesAndPromoPdfCluster' => $bodyAfterSalesAndPromoPdfCluster ?? null,
        'bodyBeforePrintHelpers' => $bodyBeforePrintHelpers ?? null,
        'bodyAfterPrintHelpers' => $bodyAfterPrintHelpers ?? null,
        'bodyBeforeAdsLogPdfExport' => $bodyBeforeAdsLogPdfExport ?? null,
        'bodyAfterAdsLogPdfExport' => $bodyAfterAdsLogPdfExport ?? null,
        'bodyBeforePriceComparisonPdfExport' => $bodyBeforePriceComparisonPdfExport ?? null,
        'bodyAfterPriceComparisonPdfExport' => $bodyAfterPriceComparisonPdfExport ?? null,
        'bodyBeforeLpjkDetailPdfExport' => $bodyBeforeLpjkDetailPdfExport ?? null,
        'bodyAfterLpjkDetailPdfExport' => $bodyAfterLpjkDetailPdfExport ?? null,
        'bodyBeforeBudgetPdfExport' => $bodyBeforeBudgetPdfExport ?? null,
        'bodyAfterBudgetPdfExport' => $bodyAfterBudgetPdfExport ?? null,

    ])
</html>
