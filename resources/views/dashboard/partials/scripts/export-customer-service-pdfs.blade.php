const getCustomerServiceExportBridge_ = () => {
    const bridge = window.MarketingDashboardCustomerServiceExports;

    if (!bridge) {
        throw new Error('Customer service export bridge belum dimuat.');
    }

    return bridge;
};

const exportUnitDitanyaToExcel = async () => {
    try {
        const bridge = getCustomerServiceExportBridge_();

        return bridge.exportUnitDitanyaToExcel({
            rows: filteredUnitDitanyaData.value,
            dateRange: unitDitanyaDateRange.value,
            availableFilter: unitDitanyaAvailableFilter.value,
            search: unitDitanyaSearch.value,
            showNotification,
            notifyError,
        });
    } catch (err) {
        notifyError('Gagal export', err, 'Modul export Unit Ditanya belum siap dimuat.');
    }
};

const exportClaimGaransiToExcel = async () => {
    try {
        const bridge = getCustomerServiceExportBridge_();

        return bridge.exportClaimGaransiToExcel({
            rows: filteredClaimGaransiData.value,
            statusFilter: claimGaransiStatusFilter.value,
            garansiFilter: claimGaransiGaransiFilter.value,
            search: claimGaransiSearch.value,
            showNotification,
            notifyError,
        });
    } catch (err) {
        notifyError('Gagal export', err, 'Modul export Claim Garansi belum siap dimuat.');
    }
};

const exportUnitDitanyaToPDF = () => {
    try {
        const bridge = getCustomerServiceExportBridge_();

        return bridge.exportUnitDitanyaToPDF({
            rows: filteredUnitDitanyaData.value,
            dateRange: unitDitanyaDateRange.value,
            availableFilter: unitDitanyaAvailableFilter.value,
            search: unitDitanyaSearch.value,
            formatNumber,
            showNotification,
            notifyError,
            jsonApi,
            getFriendlyErrorMessage,
            resolveAppUrl,
        });
    } catch (err) {
        notifyError('Gagal export', err, 'Modul export Unit Ditanya belum siap dimuat.');
    }
};

const exportClaimGaransiToPDF = () => {
    try {
        const bridge = getCustomerServiceExportBridge_();

        return bridge.exportClaimGaransiToPDF({
            rows: claimGaransiData.value,
            statusFilter: claimGaransiStatusFilter.value,
            lokasiFilter: claimGaransiLokasiFilter.value,
            garansiFilter: claimGaransiGaransiFilter.value,
            formatNumber,
            showNotification,
            notifyError,
            jsonApi,
            getFriendlyErrorMessage,
            resolveAppUrl,
        });
    } catch (err) {
        notifyError('Gagal export', err, 'Modul export Claim Garansi belum siap dimuat.');
    }
};

const exportKeepBarangToExcel = async () => {
    try {
        const bridge = getCustomerServiceExportBridge_();

        return bridge.exportKeepBarangToExcel({
            rows: filteredKeepBarangData.value,
            normalizeTypeHpValue: normalizeKeepBarangTypeHpValue,
            showNotification,
            notifyError,
        });
    } catch (err) {
        notifyError('Gagal export', err, 'Modul export Keep Barang belum siap dimuat.');
    }
};

const exportKeepBarangToPDF = () => {
    try {
        const bridge = getCustomerServiceExportBridge_();

        return bridge.exportKeepBarangToPDF({
            rows: keepBarangData.value,
            normalizeTypeHpValue: normalizeKeepBarangTypeHpValue,
            showNotification,
            notifyError,
            jsonApi,
            getFriendlyErrorMessage,
            resolveAppUrl,
        });
    } catch (err) {
        notifyError('Gagal export', err, 'Modul export Keep Barang belum siap dimuat.');
    }
};
