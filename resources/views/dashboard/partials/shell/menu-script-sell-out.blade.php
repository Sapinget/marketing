@verbatim
                const sellOutPage = ref(1);

                const sellOutModalType = ref('create');

                const last12Months = computed(() => {
                    const months = [];
                    const now = new Date();
                    for (let i = 0; i < 12; i++) {
                        const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
                        const val = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
                        months.push({
                            value: val,
                            label: `${monthNames[d.getMonth()]} ${d.getFullYear()}`
                        });
                    }
                    return months;
                });

// Sell Out computed & methods

const sellOutVendorOptions = computed(() => {
    const fromData = sellOutData.value.map(r => String(r.Vendor || '').trim()).filter(Boolean);
    const fromSettings = settings.value?.Vendor_SellOut;
    const fromSett = (Array.isArray(fromSettings) && fromSettings.length) ? fromSettings : [];
    return [...new Set([...fromSett, ...fromData])].sort((a, b) => a.localeCompare(b));
});

const sellOutSummary = computed(() => {
    const rows = filteredSellOutData.value;
    let achieved = 0;
    let totalBonus = 0;
    let totalQty = 0;

    rows.forEach(r => {
        const p = getSellOutProgress(r);
        if (p.status === 'TERPAKAI') {
            achieved += 1;
            totalBonus += p.bonusTotal;
            totalQty += Number(r.Realisasi_Unit || 0);
        }
    });

    return { totalTargets: rows.length, achieved, totalBonus, totalQty };
});

const sellOutTotalPages = computed(() => Math.max(1, Math.ceil(filteredSellOutData.value.length / PAGE_SIZE)));

const pagedSellOutData = computed(() => filteredSellOutData.value.slice((sellOutPage.value - 1) * PAGE_SIZE, sellOutPage.value * PAGE_SIZE));

const loadSellOutData = () => new Promise(resolve => {
    ensureRunApi()
        .withSuccessHandler(data => { sellOutData.value = data || []; resolve(); })
        .withFailureHandler(err => { handleError(err); resolve(); })
        .getSellOutTargetData();
});

const getCurrentMonthDateRange = () => {
    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth();
    const start = `${year}-${String(month + 1).padStart(2, '0')}-01`;
    const lastDay = String(new Date(year, month + 1, 0).getDate()).padStart(2, '0');
    return {
        start,
        end: `${year}-${String(month + 1).padStart(2, '0')}-${lastDay}`
    };
};

const buildSellOutProductName = () => {
    const f = sellOutForm.value;
    const parts = [f.Brand, f.Seri, f.RAM, f.Internal, f.Size, f.Kondisi].filter(Boolean);
    f.Nama_Produk = parts.join(' ').trim().toUpperCase();
};

const openSellOutModal = (type, row = null) => {
    sellOutModalType.value = type;
    if (type === 'edit' && row) {
        sellOutForm.value = { ...row, Realisasi_Unit: Number(row.Realisasi_Unit || 0) };
    } else {
        const currentMonthRange = getCurrentMonthDateRange();
        sellOutForm.value = { ID: null, Vendor: '', Kategori: '', Brand: '', Seri: '', RAM: '', Internal: '', Size: '', Kondisi: '', Nama_Produk: '', Target_Unit: 0, Bonus_Nominal: 0, Realisasi_Unit: 0, Periode_Start: currentMonthRange.start, Periode_End: currentMonthRange.end, Catatan: '' };
    }
    sellOutModalOpen.value = true;
};

const saveSellOut = () => {
    if (submitting.value) return;
    if (!sellOutForm.value.Vendor && !sellOutForm.value.Nama_Produk) { showNotification('Vendor atau nama produk wajib diisi'); return; }
    submitting.value = true;
    ensureRunApi()
        .withSuccessHandler(() => {
            submitting.value = false;
            sellOutModalOpen.value = false;
            loadSellOutData();
            showNotification('Target sell out berhasil disimpan');
        })
        .withFailureHandler(err => { submitting.value = false; handleError(err); })
        .saveSellOutTarget(sellOutForm.value);
};

const deleteSellOut = (id) => {
    showConfirm('Hapus Target?', 'Data yang dihapus tidak dapat dikembalikan.', () => {
        ensureRunApi()
            .withSuccessHandler(() => { loadSellOutData(); showNotification('Target berhasil dihapus'); })
            .withFailureHandler(handleError)
            .deleteSellOutTarget(id);
    });
};

const exportSellOutToPDF = () => {
    try {
        const bridge = getSalesExportBridge_();

        return bridge.exportSellOutToPDF({
            rows: filteredSellOutData.value,
            getSellOutProgress,
            formatNumber,
            formatCurrency,
            sellOutMonth: sellOutMonth.value,
            sellOutVendorFilter: sellOutVendorFilter.value,
            showNotification,
            notifyError,
            jsonApi,
            getFriendlyErrorMessage,
            resolveAppUrl,
        });
    } catch (err) {
        notifyError('Gagal export', err, 'File belum berhasil dibuat.');
    }
};

                Object.assign(menuExports, {
                    sellOutPage,
                    sellOutModalType,
                    last12Months,
                    sellOutVendorOptions,
                    sellOutSummary,
                    sellOutTotalPages,
                    pagedSellOutData,
                    loadSellOutData,
                    buildSellOutProductName,
                    openSellOutModal,
                    saveSellOut,
                    deleteSellOut,
                    exportSellOutToPDF,
                });
@endverbatim
