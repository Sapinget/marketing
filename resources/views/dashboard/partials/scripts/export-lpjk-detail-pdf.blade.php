const exportLpjkDetailToPDF = () => {
    try {
        const bridge = getReportingExportBridge_();

        return bridge.exportLpjkDetailToPDF({
            lpjk: activeLpjkRow.value,
            details: lpjkDetailData.value,
            grouped: lpjkDetailGrouped.value,
            formatCurrency,
            showNotification,
            notifyError,
            jsonApi,
            getFriendlyErrorMessage,
            resolveAppUrl,
        });
    } catch (err) {
        notifyError('Gagal export', err, 'Modul export LPJK belum siap dimuat.');
    }
};
