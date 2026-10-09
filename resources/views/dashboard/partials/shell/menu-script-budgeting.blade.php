@verbatim
                const showBudgetSettings = ref(false);

                const budgetSummary = computed(() => {
                    const c = budgetCalculations.value;
                    const cards = [
                        { label: 'Meta Ads', value: formatCurrency(c.metaTotal), icon: 'fa-facebook', iconPrefix: 'fa-brands', color: 'text-amber', subColor: 'text-amber', sub: 'Anggaran iklan' },
                        { label: 'Google Ads', value: formatCurrency(c.googleTotal), icon: 'fa-google', iconPrefix: 'fa-brands', color: 'text-amber', subColor: 'text-amber', sub: 'Anggaran iklan' },
                        { label: 'Mekari', value: formatCurrency(c.mekariVisitorTotal + c.mekariBroadcastTotal), icon: 'fa-bullhorn', color: 'text-amber', subColor: 'text-amber', sub: 'Ecosystem total' },
                        { label: 'Colab', value: formatNumber(c.colabBreakdown.length), unit: 'Partner', icon: 'fa-handshake', color: 'text-slate-500', unitColor: 'text-slate-400', subColor: 'text-slate-600', sub: 'Paid collaboration' },
                    ];
                    c.othersCalculated.forEach(o => {
                        cards.push({ label: o.name || 'Other', value: formatCurrency(o.total), icon: 'fa-layer-group', color: 'text-slate-500', subColor: 'text-slate-600', sub: 'Additional platform' });
                    });
                    return { cards, chips: [] };
                });

                const saveBudgetServer = () => {
                    const cfg = JSON.parse(JSON.stringify(budgetConfig.value));
                    localStorage.setItem('ppp_budgetConfig', JSON.stringify(cfg));
                    ensureRunApi()
                        .withSuccessHandler(() => {
                            showBudgetSettings.value = false;
                            showNotification('Konfigurasi budget disimpan');
                        })
                        .withFailureHandler((error) => {
                            notifyError('Gagal menyimpan konfigurasi budget', error, 'Konfigurasi budget belum tersimpan ke server.');
                        })
                        .saveBudgetingConfig(cfg);
                };

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

                Object.assign(menuExports, {
                    showBudgetSettings,
                    budgetSummary,
                    saveBudgetServer,
                    exportBudgetToPDF,
                });
@endverbatim
