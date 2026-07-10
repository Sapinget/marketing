export function createLpjkOperations(deps) {
    const {
        computed,
        watch,
        lpjkData,
        lpjkSearch,
        lpjkPage,
        lpjkDetailData,
        lpjkModalType,
        lpjkForm,
        lpjkModalOpen,
        lpjkDetailItem,
        lpjkDetailModalOpen,
        activeLpjkRow,
        ensureRunApi,
        todayStr,
        submitting,
        showNotification,
        handleError,
    } = deps;

    const filteredLpjkData = computed(() => {
        if (!lpjkSearch.value) return lpjkData.value;
        const q = lpjkSearch.value.toLowerCase();
        return lpjkData.value.filter(r => (r.Nama_Event || '').toLowerCase().includes(q));
    });

    const lpjkTotalPages = computed(() => Math.max(1, Math.ceil(filteredLpjkData.value.length / 20)));

    const pagedLpjkData = computed(() => {
        const p = lpjkPage.value;
        return filteredLpjkData.value.slice((p - 1) * 20, p * 20);
    });

    const lpjkDetailGrouped = computed(() => {
        const result = {};
        for (const item of lpjkDetailData.value) {
            const cat = item.Kategori || 'Lainnya';
            if (!result[cat]) result[cat] = [];
            result[cat].push(item);
        }
        return result;
    });

    const lpjkDetailTotal = computed(() => lpjkDetailData.value.reduce((s, i) => s + (Number(i.Total) || 0), 0));

    watch(lpjkSearch, () => { lpjkPage.value = 1; });

    const openLpjkModal = (type = 'create', row = null) => {
        lpjkModalType.value = type;
        lpjkForm.value = row
            ? { ...row }
            : { ID: null, Nama_Event: '', Tanggal: todayStr(), Budget_Rencana: 0, Realisasi_Biaya: 0, Selisih: 0, Status: 'DRAFT', Keterangan: '' };
        lpjkModalOpen.value = true;
    };

    const saveLpjk = () => {
        const form = lpjkForm.value;
        form.Selisih = (form.Budget_Rencana || 0) - (form.Realisasi_Biaya || 0);
        submitting.value = true;
        ensureRunApi()
            .withSuccessHandler(res => {
                submitting.value = false;
                if (!form.ID) {
                    lpjkData.value.unshift({ ...form, ID: (res && res.id) || ('LJ' + Date.now()) });
                } else {
                    const idx = lpjkData.value.findIndex(r => String(r.ID) === String(form.ID));
                    if (idx !== -1) lpjkData.value.splice(idx, 1, { ...form });
                }
                lpjkModalOpen.value = false;
                showNotification('Data event disimpan');
            })
            .withFailureHandler(err => { submitting.value = false; handleError(err); })
            .saveLpjk(form);
    };

    const deleteLpjk = (id) => {
        if (!confirm('Hapus event ini?')) return;
        ensureRunApi()
            .withSuccessHandler(() => {
                lpjkData.value = lpjkData.value.filter(r => String(r.ID) !== String(id));
                showNotification('Event dihapus');
            })
            .withFailureHandler(err => handleError(err))
            .deleteLpjk(id);
    };

    const openLpjkDetail = (row) => {
        activeLpjkRow.value = row;
        lpjkDetailItem.value = { Master_ID: row.ID, Kategori: 'Konsumsi', Nama_Pengeluaran: '', Satuan: 0, Jumlah: 1, Total: 0, Bukti: '' };
        lpjkDetailData.value = [];
        lpjkDetailModalOpen.value = true;
        ensureRunApi()
            .withSuccessHandler(data => { lpjkDetailData.value = Array.isArray(data) ? data : []; })
            .withFailureHandler(() => { })
            .getLpjkDetailData(row.ID);
    };

    const closeLpjkDetail = () => {
        lpjkDetailModalOpen.value = false;
        activeLpjkRow.value = null;
        lpjkDetailData.value = [];
    };

    const saveLpjkDetail = () => {
        const item = lpjkDetailItem.value;
        item.Total = (item.Satuan || 0) * (item.Jumlah || 0);
        submitting.value = true;
        ensureRunApi()
            .withSuccessHandler(res => {
                submitting.value = false;
                const saved = { ...item, ID: (res && res.id) || ('LD' + Date.now()) };
                lpjkDetailData.value.push(saved);
                const newTotal = lpjkDetailData.value.reduce((s, i) => s + (Number(i.Total) || 0), 0);
                const lpjkRow = lpjkData.value.find(r => String(r.ID) === String(item.Master_ID));
                if (lpjkRow) {
                    lpjkRow.Realisasi_Biaya = newTotal;
                    lpjkRow.Selisih = (lpjkRow.Budget_Rencana || 0) - newTotal;
                }
                lpjkDetailItem.value = { Master_ID: item.Master_ID, Kategori: item.Kategori, Nama_Pengeluaran: '', Satuan: 0, Jumlah: 1, Total: 0, Bukti: '' };
                showNotification('Item ditambahkan');
            })
            .withFailureHandler(err => { submitting.value = false; handleError(err); })
            .saveLpjkDetail(item);
    };

    const deleteLpjkDetail = (id) => {
        ensureRunApi()
            .withSuccessHandler(() => {
                lpjkDetailData.value = lpjkDetailData.value.filter(r => String(r.ID) !== String(id));
                const newTotal = lpjkDetailData.value.reduce((s, i) => s + (Number(i.Total) || 0), 0);
                if (activeLpjkRow.value) {
                    const lpjkRow = lpjkData.value.find(r => String(r.ID) === String(activeLpjkRow.value.ID));
                    if (lpjkRow) {
                        lpjkRow.Realisasi_Biaya = newTotal;
                        lpjkRow.Selisih = (lpjkRow.Budget_Rencana || 0) - newTotal;
                    }
                }
                showNotification('Item dihapus');
            })
            .withFailureHandler(err => handleError(err))
            .deleteLpjkDetail(id);
    };

    return {
        filteredLpjkData,
        lpjkTotalPages,
        pagedLpjkData,
        lpjkDetailGrouped,
        lpjkDetailTotal,
        openLpjkModal,
        saveLpjk,
        deleteLpjk,
        openLpjkDetail,
        closeLpjkDetail,
        saveLpjkDetail,
        deleteLpjkDetail,
    };
}
