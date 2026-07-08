// Ads Log PDF
const exportAdsLogToPDF = () => {
    try {
        const bridge = getReportingExportBridge_();

        return bridge.exportAdsLogToPDF({
            rows: filteredAdsData.value,
            formatCurrency,
            showNotification,
            notifyError,
            jsonApi,
            getFriendlyErrorMessage,
            resolveAppUrl,
        });
    } catch (err) {
        notifyError('Gagal export', err, 'Modul export Ads Log belum siap dimuat.');
    }
};
