const exportBudgetToPDF = () => {
    try {
        const bridge = getReportingExportBridge_();

        return bridge.exportBudgetToPDF({
            config: budgetConfig.value,
            calculations: budgetCalculations.value,
            formatCurrency,
            formatNumber,
            showNotification,
            notifyError,
            jsonApi,
            getFriendlyErrorMessage,
            resolveAppUrl,
        });
    } catch (err) {
        notifyError('Gagal export', err, 'Modul export Budget belum siap dimuat.');
    }
};
