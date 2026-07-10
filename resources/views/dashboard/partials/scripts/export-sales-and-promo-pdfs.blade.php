const getSalesExportBridge_ = () => {
    const bridge = window.MarketingDashboardSalesExports;

    if (!bridge) {
        throw new Error('Sales export bridge belum dimuat.');
    }

    return bridge;
};

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
const editorDateRange = computed(() => {
    const pm = editorMonth.value === 1 ? 12 : editorMonth.value - 1;
    const py = editorMonth.value === 1 ? editorYear.value - 1 : editorYear.value;
    return {
        start: `${py}-${String(pm).padStart(2, '0')}-26`,
        end: `${editorYear.value}-${String(editorMonth.value).padStart(2, '0')}-25`
    };
});

const editorDashboardData = computed(() => {
    const master = masterPlanData.value;
    const analytics = analyticsData.value;
    const dist = distributionData.value;

    const start = editorDateRange.value.start;
    const end = editorDateRange.value.end;

    const distByMaster = new Map();
    dist.forEach(d => {
        const key = String(d.Master_ID || '');
        if (!key) return;
        if (!distByMaster.has(key)) distByMaster.set(key, []);
        distByMaster.get(key).push(d);
    });

    const analyticsGrouped = new Map();
    analytics.forEach(a => {
        const key = String(a.Master_ID || '');
        if (!key) return;
        if (!analyticsGrouped.has(key)) analyticsGrouped.set(key, { views: 0, likes: 0, comments: 0, shares: 0 });
        const g = analyticsGrouped.get(key);
        g.views += Number(a.Views || 0);
        g.likes += Number(a.Likes || 0);
        g.comments += Number(a.Comments || 0);
        g.shares += Number(a.Shares || 0);
    });

    const filtered = master.filter(m => {
        const status = (m.Status || '').toUpperCase();
        if (!['PUBLISHED', 'SCHEDULE', 'DONE'].includes(status)) return false;
        const distRows = distByMaster.get(String(m.ID || '')) || [];
        const rawDate = distRows[0]?.Tanggal_Publish || m.Tanggal_Rencana || '';
        if (start && rawDate && rawDate < start) return false;
        if (end && rawDate && rawDate > end) return false;
        return true;
    });

    const videoList = filtered.map(m => {
        const distRows = distByMaster.get(String(m.ID || '')) || [];
        const ag = analyticsGrouped.get(String(m.ID || '')) || { views: 0, likes: 0, comments: 0, shares: 0 };
        return {
            ID: m.ID,
            Judul: m.Judul,
            Editor: m.Editor || '-',
            Platforms: m.Platforms || '',
            DisplayDate: distRows[0]?.Tanggal_Publish || m.Tanggal_Rencana || '',
            Tanggal_Rencana: m.Tanggal_Rencana || '',
            totalViews: ag.views,
            Likes: ag.likes,
            Comments: ag.comments,
            Shares: ag.shares,
            Views: ag.views
        };
    }).sort((a, b) => (b.DisplayDate || '').localeCompare(a.DisplayDate || ''));

    const editorMap = new Map();
    videoList.forEach(v => {
        const name = v.Editor || '-';
        if (!editorMap.has(name)) editorMap.set(name, { name, count: 0, views: 0, scores: [] });
        const e = editorMap.get(name);
        e.count++;
        e.views += v.totalViews;
        e.scores.push(calculateScore(v));
    });

    const leaderboard = [...editorMap.values()]
        .map(e => ({ ...e, avgScore: e.scores.length ? parseFloat((e.scores.reduce((s, x) => s + x, 0) / e.scores.length).toFixed(1)) : 0 }))
        .sort((a, b) => b.views - a.views);

    return {
        videoList,
        leaderboard,
        totalVideos: videoList.length,
        totalViews: videoList.reduce((s, v) => s + v.totalViews, 0)
    };
});

const editorTotalPages = computed(() => Math.max(1, Math.ceil(editorDashboardData.value.videoList.length / PAGE_SIZE)));
const pagedEditorRows = computed(() => editorDashboardData.value.videoList.slice((editorPage.value - 1) * PAGE_SIZE, editorPage.value * PAGE_SIZE));

watch([editorMonth, editorYear], () => { editorPage.value = 1; });

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

const loadPromoData = () => new Promise(resolve => {
    ensureRunApi()
        .withSuccessHandler(data => { promoData.value = data || []; resolve(); })
        .withFailureHandler(err => { handleError(err); resolve(); })
        .getPromoData();
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

const syncPromoPerideText = () => {
    const { start, end } = promoTempDate.value;
    if (start && end) promoForm.value.Periode = `${start} - ${end}`;
    else if (start) promoForm.value.Periode = `Mulai ${start}`;
};

const applyPromoPeriodePreset = () => {
    const p = promoPeriodePreset.value;
    if (p === 'stock') { promoForm.value.Periode = 'Selama persediaan masih ada'; promoTempDate.value = { start: '', end: '' }; }
    else if (p === 'custom') { syncPromoPerideText(); }
    else if (p) { promoForm.value.Periode = p; promoTempDate.value = { start: '', end: '' }; }
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

watch(promoSearch, () => { promoPage.value = 1; });
watch(() => promoTempDate.value.start + promoTempDate.value.end, () => {
    if (promoPeriodePreset.value === 'custom') syncPromoPerideText();
});

// Sell Out computed & methods

const sellOutVendorOptions = computed(() => {
    const fromData = sellOutData.value.map(r => String(r.Vendor || '').trim()).filter(Boolean);
    const fromSettings = settings.value?.Vendor_SellOut;
    const fromSett = (Array.isArray(fromSettings) && fromSettings.length) ? fromSettings : [];
    return [...new Set([...fromSett, ...fromData])].sort((a, b) => a.localeCompare(b));
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

const sellOutTotalPages = computed(() => Math.max(1, Math.ceil(filteredSellOutData.value.length / 20)));
const pagedSellOutData = computed(() => filteredSellOutData.value.slice((sellOutPage.value - 1) * 20, sellOutPage.value * 20));

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
