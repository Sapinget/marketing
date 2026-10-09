@verbatim
                const promoModalType = ref('create');

                const promoSummary = computed(() => {
                    const d = filteredPromoData.value || [];
                    const kat = _sCnt(d, r => r.Kategori);
                    const totalHarga = _sSum(d, r => r.Harga);
                    const topCategory = Object.entries(kat).sort((a, b) => b[1] - a[1])[0] || null;
                    return { cards: limitSummaryCards([
                        { label: 'Total Program', value: formatNumber(d.length), unit: 'Promo', icon: 'fa-tags', color: 'text-amber', unitColor: 'text-amber', subColor: 'text-amber', sub: 'Semua program' },
                        { label: 'Kategori', value: formatNumber(Object.keys(kat).length), unit: 'Jenis', icon: 'fa-layer-group', color: 'text-slate-500', unitColor: 'text-slate-400', subColor: 'text-slate-600', sub: 'Variasi kategori' },
                        { label: 'Total Nilai', value: formatCurrency(totalHarga), icon: 'fa-money-bill-wave', color: 'text-amber', subColor: 'text-amber', sub: 'Akumulasi harga' },
                        { label: 'Rata Harga', value: formatCurrency(d.length ? totalHarga / d.length : 0), icon: 'fa-calculator', color: 'text-success', subColor: 'text-success', sub: 'Per program' },
                        { label: 'Top Kategori', value: topCategory ? String(topCategory[0]).toUpperCase() : '-', icon: 'fa-tag', color: 'text-danger', sub: formatNumber(topCategory ? topCategory[1] : 0) + ' program', subColor: 'text-danger' },
                    ]) };
                });

// Program Promo computed & methods

const kategoriPromoOptions = computed(() => {
    const fromSettings = settings.value?.Kategori_Promo;
    if (fromSettings && Array.isArray(fromSettings) && fromSettings.length) return fromSettings;
    return ['Brand', 'Vendor', 'Internal', 'Platform', 'Bundle', 'Event'];
});

const filteredPromoData = computed(() => {
    const q = promoSearch.value.toLowerCase();
    if (!q) return promoData.value;
    return promoData.value.filter(r =>
        String(r.Kategori || '').toLowerCase().includes(q) ||
        String(r.Program || '').toLowerCase().includes(q) ||
        String(r.Benefit || '').toLowerCase().includes(q) ||
        String(r.Rules || '').toLowerCase().includes(q)
    );
});

const groupedPromoRows = computed(() => {
    const rows = filteredPromoData.value;
    const order = kategoriPromoOptions.value;
    const groups = new Map();
    order.forEach(k => groups.set(k, []));
    rows.forEach(r => {
        const key = r.Kategori || 'Lainnya';
        if (!groups.has(key)) groups.set(key, []);
        groups.get(key).push(r);
    });
    const result = [];
    groups.forEach((items, kat) => {
        if (!items.length) return;
        result.push({ isCategoryHeader: true, name: kat, count: items.length });
        items.forEach((r, i) => result.push({ ...r, isCategoryHeader: false, indexInGroup: i + 1 }));
    });
    return result;
});

const promoTotalPages = computed(() => Math.max(1, Math.ceil(filteredPromoData.value.length / PAGE_SIZE)));

const pagedPromoRows = computed(() => {
    const flat = groupedPromoRows.value.filter(r => !r.isCategoryHeader);
    const pagedFlat = flat.slice((promoPage.value - 1) * PAGE_SIZE, promoPage.value * PAGE_SIZE);
    const pagedIds = new Set(pagedFlat.map(r => r.ID));
    const pagedKats = new Set(pagedFlat.map(r => r.Kategori));
    return groupedPromoRows.value.filter(r =>
        r.isCategoryHeader ? pagedKats.has(r.name) : pagedIds.has(r.ID)
    );
});

const openPromoModal = (type, row = null) => {
    promoModalType.value = type;
    if (type === 'edit' && row) {
        promoForm.value = { ...row };
        promoPeriodePreset.value = '';
        const m = (row.Periode || '').match(/^(\d{4}-\d{2}-\d{2})\s*[--]\s*(\d{4}-\d{2}-\d{2})$/);
        promoTempDate.value = m ? { start: m[1], end: m[2] } : { start: '', end: '' };
    } else {
        promoForm.value = { ID: null, Kategori: '', Program: '', Warna: '', Harga: 0, Periode: 'Selama persediaan masih ada', Rules: '', Benefit: '' };
        promoPeriodePreset.value = 'stock';
        promoTempDate.value = { start: '', end: '' };
    }
    promoModalOpen.value = true;
};

const applyPromoPeriodePreset = () => {
    const p = promoPeriodePreset.value;
    if (p === 'stock') { promoForm.value.Periode = 'Selama persediaan masih ada'; promoTempDate.value = { start: '', end: '' }; }
    else if (p === 'custom') { syncPromoPerideText(); }
    else if (p) { promoForm.value.Periode = p; promoTempDate.value = { start: '', end: '' }; }
};

const exportPromoToPDF = () => {
    try {
        const bridge = getSalesExportBridge_();

        return bridge.exportPromoToPDF({
            rows: filteredPromoData.value,
            kategoriPromoOptions: kategoriPromoOptions.value,
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
                    promoModalType,
                    promoSummary,
                    kategoriPromoOptions,
                    filteredPromoData,
                    groupedPromoRows,
                    promoTotalPages,
                    pagedPromoRows,
                    openPromoModal,
                    applyPromoPeriodePreset,
                    exportPromoToPDF,
                });
@endverbatim
