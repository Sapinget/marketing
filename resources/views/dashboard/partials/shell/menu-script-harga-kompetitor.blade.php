@verbatim
                const hargaKompetitorModalType = ref('create');

                // Harga & Kompetitor
                const filteredHargaKompetitorData = computed(() => {
                    let data = hargaKompetitorData.value;
                    if (hargaKompetitorSearch.value) {
                        const q = hargaKompetitorSearch.value.toLowerCase();
                        data = data.filter(r => [
                            r.Nama_Produk,
                            r.KATEGORI,
                            r.BRAND,
                            r.SERI,
                            r.RAM,
                            r.INTERNAL,
                            r.SIZE,
                            r.WARNA,
                        ].some(value => String(value || '').toLowerCase().includes(q)));
                    }
                    if (hargaKompetitorDateFilter.value.start) {
                        data = data.filter(r => isDateInRange(r.Tanggal_Cek, hargaKompetitorDateFilter.value.start, hargaKompetitorDateFilter.value.end || hargaKompetitorDateFilter.value.start));
                    }
                    return data;
                });

                const hargaKompetitorTotalPages = computed(() => Math.max(1, Math.ceil(filteredHargaKompetitorData.value.length / PAGE_SIZE)));

                const pagedHargaKompetitorData = computed(() => {
                    const p = hargaKompetitorPage.value;
                    return filteredHargaKompetitorData.value.slice((p - 1) * PAGE_SIZE, p * PAGE_SIZE);
                });

                const applyHargaKompetitorSuggestion = () => {
                    const suggestion = hargaKompetitorSuggestion.value;
                    if (!suggestion.canSuggest) return;
                    hargaKompetitorForm.value.Harga_Rencana_Jual = suggestion.suggestedPrice;
                    hargaKompetitorLastAutoSuggestion.value = suggestion.suggestedPrice;
                };

                const openHargaKompetitorModal = (type = 'create', row = null) => {
                    hargaKompetitorModalType.value = type;
                    hargaKompetitorForm.value = row ? { KATEGORI: '', BRAND: '', SERI: '', RAM: '', INTERNAL: '', SIZE: '', WARNA: '', ...row } : { ID: null, Nama_Produk: '', KATEGORI: '', BRAND: '', SERI: '', RAM: '', INTERNAL: '', SIZE: '', WARNA: '', Tanggal_Cek: todayStr(), Harga_Distributor_1: 0, Harga_Distributor_2: 0, Harga_Kompetitor: 0, Harga_Rencana_Jual: 0, Margin_Profit: 0, Selisih: 0 };
                    hargaKompetitorLastAutoSuggestion.value = 0;
                    hargaKompetitorModalOpen.value = true;
                    ensureNamaStockLoaded();
                };

                const hargaKategoriOptions = computed(() => getKategoriOptions());

                const hargaBrandOptions = computed(() => getBrandOptions(hargaKompetitorForm.value.KATEGORI));

                const hargaSeriOptions = computed(() => getSeriOptions(hargaKompetitorForm.value.KATEGORI, hargaKompetitorForm.value.BRAND));

                const hargaRAMOptions = computed(() => unitRAMOptions.value);

                const hargaInternalOptions = computed(() => unitInternalOptions.value);

                const hargaSizeOptions = computed(() => unitSizeOptions.value);

                const hargaSummary = computed(() => {
                    const d = filteredHargaKompetitorData.value || [];
                    const margins = d.map(r => Number(r.Margin_Profit) || 0);
                    const avg = d.length ? margins.reduce((a, b) => a + b, 0) / d.length : 0;
                    let pct20plus = 0, pct10to20 = 0, pctUnder10 = 0, pctNeg = 0;
                    margins.forEach(m => { if (m > 20) pct20plus++; else if (m >= 10) pct10to20++; else if (m >= 0) pctUnder10++; else pctNeg++; });
                    return { cards: limitSummaryCards([
                        { label: 'Total Produk', value: formatNumber(d.length), unit: 'Item', icon: 'fa-box', color: 'text-amber', unitColor: 'text-amber', subColor: 'text-amber', sub: 'Dipantau' },
                        { label: 'Avg Margin', value: (Math.round(avg * 10) / 10) + '%', icon: 'fa-percent', color: 'text-success', subColor: 'text-success', sub: 'Rata margin' },
                        { label: '>20% Margin', value: formatNumber(pct20plus), icon: 'fa-circle-check', color: 'text-success', sub: 'Margin sehat', subColor: 'text-success' },
                        { label: '10-20%', value: formatNumber(pct10to20), icon: 'fa-circle-half-stroke', color: 'text-amber', sub: 'Margin layak', subColor: 'text-amber' },
                        { label: '<10%', value: formatNumber(pctUnder10), icon: 'fa-triangle-exclamation', color: 'text-amber', sub: 'Margin tipis', subColor: 'text-amber' },
                        { label: 'Negatif', value: formatNumber(pctNeg), icon: 'fa-circle-xmark', color: 'text-danger', sub: 'Margin rugi', subColor: 'text-danger' },
                    ]) };
                });

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

                Object.assign(menuExports, {
                    hargaKompetitorModalType,
                    filteredHargaKompetitorData,
                    hargaKompetitorTotalPages,
                    pagedHargaKompetitorData,
                    applyHargaKompetitorSuggestion,
                    openHargaKompetitorModal,
                    hargaKategoriOptions,
                    hargaBrandOptions,
                    hargaSeriOptions,
                    hargaRAMOptions,
                    hargaInternalOptions,
                    hargaSizeOptions,
                    hargaSummary,
                    exportPriceComparisonToPDF,
                });
@endverbatim
