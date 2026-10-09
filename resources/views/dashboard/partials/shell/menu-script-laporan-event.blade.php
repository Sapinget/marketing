@verbatim
                const lpjkExpenseCategories = ['Konsumsi', 'Dekorasi', 'Dokumentasi', 'Transportasi', 'Venue', 'Promosi', 'Perlengkapan', 'Lainnya'];

                const lpjkStatusOptions = computed(() => settings.value.Status || []);

                const lpjkSummary = computed(() => {
                    const d = lpjkData.value || [];
                    const budget = _sSum(d, r => r.Budget_Rencana);
                    const real = _sSum(d, r => r.Realisasi_Biaya);
                    const st = _sCnt(d, r => r.Status);
                    const sisa = budget - real;
                    const topStatus = Object.entries(st).sort((a, b) => b[1] - a[1])[0] || null;
                    return { cards: limitSummaryCards([
                        { label: 'Total Event', value: formatNumber(d.length), unit: 'Event', icon: 'fa-calendar-check', color: 'text-slate-500', unitColor: 'text-slate-400', subColor: 'text-slate-600', sub: 'Semua event' },
                        { label: 'Total Budget', value: formatCurrency(budget), icon: 'fa-wallet', color: 'text-amber', subColor: 'text-amber', sub: 'Rencana' },
                        { label: 'Realisasi', value: formatCurrency(real), icon: 'fa-money-check-dollar', color: 'text-amber', subColor: 'text-amber', sub: 'Terpakai' },
                        { label: 'Sisa', value: formatCurrency(sisa), icon: 'fa-piggy-bank', color: sisa >= 0 ? 'text-success' : 'text-danger', subColor: sisa >= 0 ? 'text-success' : 'text-danger', sub: sisa >= 0 ? 'Hemat' : 'Over budget' },
                        { label: 'Top Status', value: topStatus ? String(topStatus[0]).toUpperCase() : '-', icon: 'fa-circle-dot', color: 'text-slate-500', sub: formatNumber(topStatus ? topStatus[1] : 0) + ' event', subColor: 'text-slate-600' },
                    ]) };
                });

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

                Object.assign(menuExports, {
                    lpjkExpenseCategories,
                    lpjkStatusOptions,
                    lpjkSummary,
                    exportLpjkDetailToPDF,
                });
@endverbatim
