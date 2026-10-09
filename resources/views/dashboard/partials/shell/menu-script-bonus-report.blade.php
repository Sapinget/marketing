@verbatim
                const pagedBonusRows = computed(() => filteredBonusRows.value.slice((bonusPage.value - 1) * PAGE_SIZE, bonusPage.value * PAGE_SIZE));

const exportBonusToPDF = () => {
    try {
        const bridge = getSalesExportBridge_();

        return bridge.exportBonusToPDF({
            rows: filteredBonusRows.value,
            monthNames,
            bonusMonth: bonusMonth.value,
            bonusYear: bonusYear.value,
            periodStart: formatShortDate(bonusFilter.value.start),
            periodEnd: formatShortDate(bonusFilter.value.end),
            totals: bonusTotal.value,
            formatCurrency,
            formatNumber,
            formatShortDate,
            showNotification,
            notifyError,
            jsonApi,
            getFriendlyErrorMessage,
            resolveAppUrl,
        });
    } catch (err) {
        notifyError('Gagal export PDF', err, 'Dokumen PDF belum berhasil dibuat.');
    }
};

                Object.assign(menuExports, {
                    pagedBonusRows,
                    exportBonusToPDF,
                });
@endverbatim
