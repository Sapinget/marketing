const getAnalyticsExportBridge_ = () => {
    const bridge = window.MarketingDashboardAnalyticsExports;

    if (!bridge) {
        throw new Error('Analytics export bridge belum dimuat.');
    }

    return bridge;
};

const exportAnalyticsToPDF = () => {
    try {
        const bridge = getAnalyticsExportBridge_();

        return bridge.exportAnalyticsToPDF({
            rows: filteredAnalyticsData.value,
            dateFilter: commonDateFilter.value,
            formatShortDate,
            formatNumber,
            calculateScore,
            getVelocity,
            showNotification,
            notifyError,
            jsonApi,
            getFriendlyErrorMessage,
            resolveAppUrl,
        });
    } catch (err) {
        notifyError('Gagal export', err, 'Modul export Analytics belum siap dimuat.');
    }
};

const exportPdf = () => {
    const bridge = getAnalyticsExportBridge_();

    return bridge.exportActiveTabPdf({
        activeTab: activeTab.value,
        formatNumber,
        formatShortDate,
        showNotification,
        notifyError,
        jsonApi,
        getFriendlyErrorMessage,
        resolveAppUrl,
        exportUnitDitanyaToPDF,
        exportClaimGaransiToPDF,
        exportKeepBarangToPDF,
        exportAnalyticsToPDF,
        filteredMasterPlanData: filteredMasterPlanData.value,
        filteredDistributionData: filteredDistributionData.value,
        filteredAnalyticsData: filteredAnalyticsData.value,
        filteredUnboxingData: filteredUnboxingData.value,
        filteredOrderanOnlineData: filteredOrderanOnlineData.value,
        filteredUnitDitanyaData: filteredUnitDitanyaData.value,
        filteredClaimGaransiData: filteredClaimGaransiData.value,
        topContentCombined: topContentCombined.value,
        lowContentCombined: lowContentCombined.value,
        exportGenericTabularPdf: bridge.exportGenericTabularPdf,
    });
};
