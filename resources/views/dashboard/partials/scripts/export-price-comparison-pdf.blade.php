const exportPriceComparisonToPDF = () => {
    try {
        const bridge = getReportingExportBridge_();

        return bridge.exportPriceComparisonToPDF({
            rows: filteredHargaKompetitorData.value,
            formatCurrency,
            showNotification,
            notifyError,
            jsonApi,
            getFriendlyErrorMessage,
            resolveAppUrl,
        });
    } catch (err) {
        notifyError('Gagal export', err, 'Modul export Harga Kompetitor belum siap dimuat.');
    }
};
