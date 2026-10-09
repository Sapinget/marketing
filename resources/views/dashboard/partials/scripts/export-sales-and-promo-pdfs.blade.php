const getSalesExportBridge_ = () => {
    const bridge = window.MarketingDashboardSalesExports;

    if (!bridge) {
        throw new Error('Sales export bridge belum dimuat.');
    }

    return bridge;
};

const exportBonusToExcel = async () => {
    try {
        const bridge = getSalesExportBridge_();

        return bridge.exportBonusToExcel({
            rows: filteredBonusRows.value,
            formatNumber,
            formatCurrency,
            formatShortDate,
            showNotification,
            notifyError,
        });
    } catch (err) {
        notifyError('Gagal export Excel', err, 'File Excel belum berhasil dibuat.');
    }
};

// Editor Performance computed

const _editorDefault = (() => { const d = getDefaultDateRange(); const [y, m] = d.end.split('-').map(Number); return { month: m, year: y }; })();
const editorMonth = ref(_editorDefault.month);
const editorYear = ref(_editorDefault.year);

watch([editorMonth, editorYear], () => { editorPage.value = 1; });

const loadPromoData = () => new Promise(resolve => {
    ensureRunApi()
        .withSuccessHandler(data => { promoData.value = data || []; resolve(); })
        .withFailureHandler(err => { handleError(err); resolve(); })
        .getPromoData();
});

const syncPromoPerideText = () => {
    const { start, end } = promoTempDate.value;
    if (start && end) promoForm.value.Periode = `${start} - ${end}`;
    else if (start) promoForm.value.Periode = `Mulai ${start}`;
};

const savePromo = () => {
    if (!promoForm.value.Program) { showNotification('Nama program wajib diisi'); return; }
    submitting.value = true;
    ensureRunApi()
        .withSuccessHandler(() => {
            submitting.value = false;
            promoModalOpen.value = false;
            loadPromoData();
            showNotification('Program promo berhasil disimpan');
        })
        .withFailureHandler(err => { submitting.value = false; handleError(err); })
        .savePromo(promoForm.value);
};

const deletePromo = (id) => {
    showConfirm('Hapus Program Promo?', 'Data yang dihapus tidak dapat dikembalikan.', () => {
        ensureRunApi()
            .withSuccessHandler(() => { loadPromoData(); showNotification('Program promo berhasil dihapus'); })
            .withFailureHandler(handleError)
            .deletePromo(id);
    });
};

watch(promoSearch, () => { promoPage.value = 1; });
watch(() => promoTempDate.value.start + promoTempDate.value.end, () => {
    if (promoPeriodePreset.value === 'custom') syncPromoPerideText();
});

const filteredSellOutData = computed(() => {
    let rows = JSON.parse(JSON.stringify(sellOutData.value));

    if (sellOutVendorFilter.value) {
        rows = rows.filter(r => String(r.Vendor || '').toLowerCase() === sellOutVendorFilter.value.toLowerCase());
    }
    if (sellOutMonth.value) {
        const [y, m] = sellOutMonth.value.split('-').map(Number);
        const mStart = `${y}-${String(m).padStart(2, '0')}-01`;
        const mEnd = `${y}-${String(m).padStart(2, '0')}-${String(new Date(y, m, 0).getDate()).padStart(2, '0')}`;
        rows = rows.filter(r => {
            const s = r.Periode_Start || '';
            const e = r.Periode_End || '';
            if (!s && !e) return true;
            const rs = s || e; const re = e || s;
            return rs <= mEnd && re >= mStart;
        });
    }
    if (sellOutSearch.value) {
        const q = sellOutSearch.value.toLowerCase();
        rows = rows.filter(r =>
            String(r.Vendor || '').toLowerCase().includes(q) ||
            String(r.Nama_Produk || '').toLowerCase().includes(q) ||
            String(r.Brand || '').toLowerCase().includes(q) ||
            String(r.Seri || '').toLowerCase().includes(q)
        );
    }
    return rows;
});

const getSellOutTargetGroupKey = (target) => {
    const norm = (v) => String(v || '').trim().toUpperCase();
    return [
        norm(target.Vendor),
        norm(target.Kategori),
        norm(target.Brand),
        norm(target.Seri),
        norm(target.RAM),
        norm(target.Internal),
        norm(target.Size),
        norm(target.Kondisi),
        norm(target.Nama_Produk),
        norm(target.Periode_Start),
        norm(target.Periode_End)
    ].join('|');
};

const getSellOutProgress = (row) => {
    const target = Number(row.Target_Unit || 0);
    const real = Number(row.Realisasi_Unit || 0);
    const bonus = Number(row.Bonus_Nominal || 0);

    // Check for tiers in the same group
    const groupKey = getSellOutTargetGroupKey(row);
    const groupTargets = filteredSellOutData.value.filter(t => getSellOutTargetGroupKey(t) === groupKey);

    // Find the best achieved tier
    let bestTier = null;
    groupTargets.forEach(t => {
        const tUnit = Number(t.Target_Unit || 0);
        if (real >= tUnit) {
            if (!bestTier || tUnit > Number(bestTier.Target_Unit || 0)) {
                bestTier = t;
            }
        }
    });

    const isSelected = bestTier ? String(bestTier.ID) === String(row.ID) : false;
    const eligible = real >= target;
    const pct = target > 0 ? Math.min(100, Math.round(real / target * 100)) : (real > 0 ? 100 : 0);

    let status = 'BELUM';
    if (isSelected) status = 'TERPAKAI';
    else if (eligible) status = 'TIDAK DIPAKAI';
    else if (real > 0) status = 'PROGRESS';

    const bonusTotal = isSelected ? real * bonus : 0;

    return { pct, achieved: isSelected, eligible, bonusTotal, status };
};

const exportSellOutToExcel = async () => {
    try {
        const bridge = getSalesExportBridge_();

        return bridge.exportSellOutToExcel({
            rows: filteredSellOutData.value,
            getSellOutProgress,
            formatNumber,
            formatCurrency,
            sellOutMonth: sellOutMonth.value,
            sellOutVendorFilter: sellOutVendorFilter.value,
            showNotification,
            notifyError,
        });
    } catch (err) {
        notifyError('Gagal export', err, 'File belum berhasil dibuat.');
    }
};

