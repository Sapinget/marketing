const getReportingExportBridge_ = () => {
    const bridge = window.MarketingDashboardReportingExports;

    if (!bridge) {
        throw new Error('Reporting export bridge belum dimuat.');
    }

    return bridge;
};
