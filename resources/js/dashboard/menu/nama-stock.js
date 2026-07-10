const generateTempId = () => `tmp-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;

const normalizeNamaStockPayload = (row) => ({
    ID: String(row?.ID || generateTempId()),
    KATEGORI: String(row?.KATEGORI || '').trim().toUpperCase(),
    BRAND: String(row?.BRAND || '').trim().toUpperCase(),
    SERI: String(row?.SERI || '').trim().toUpperCase(),
});

const normalizeNamaStockKeyStrict = (row) => {
    const clean = (value) => String(value || '').trim().replace(/\s+/g, ' ').toUpperCase();
    return `${clean(row?.KATEGORI)}|${clean(row?.BRAND)}|${clean(row?.SERI)}`;
};

function createNamaStockState(ref) {
    return {
        namaStockRows: ref([]),
        namaStockSearchQuery: ref(''),
        namaStockKategoriFilter: ref(''),
        namaStockBrandFilter: ref(''),
        namaStockSaving: ref(false),
        showNamaStockFormModal: ref(false),
        namaStockFormMode: ref('create'),
        namaStockForm: ref({ ID: '', KATEGORI: '', BRAND: '', SERI: '' }),
        namaStockLoaded: ref(false),
    };
}

function createNamaStockActions(deps) {
    const saveNamaStockSettings = async () => {
        if (deps.namaStockSaving.value) return;
        const rows = deps.namaStockRows.value;
        for (const r of rows) {
            if (!r.KATEGORI || !r.BRAND || !r.SERI) {
                deps.showNotification('Data Nama Stock belum lengkap. Isi kategori, brand, dan seri terlebih dulu.', 'error');
                return;
            }
        }

        const seen = new Map();
        rows.forEach((r) => {
            const k = deps.normalizeNamaStockKeyStrict(r);
            if (!seen.has(k)) seen.set(k, r);
        });
        const normalized = Array.from(seen.values());
        deps.namaStockRows.value = normalized;
        deps.namaStockSaving.value = true;
        deps.ensureRunApi()
            .withSuccessHandler(() => {
                deps.namaStockSaving.value = false;
                deps.namaStockLoaded.value = true;
                deps.showNotification(`Nama Stock tersimpan (${normalized.length} baris).`);
            })
            .withFailureHandler((err) => {
                deps.namaStockSaving.value = false;
                deps.notifyError('Gagal menyimpan', err, 'Nama Stock belum berhasil disimpan.');
            })
            .saveNamaStockRows(normalized);
    };

    const openNamaStockFormModal = (mode = 'create', row = null) => {
        deps.namaStockFormMode.value = mode === 'edit' ? 'edit' : 'create';
        if (mode === 'edit' && row) {
            deps.namaStockForm.value = { ...deps.normalizeNamaStockPayload(row) };
        } else {
            deps.namaStockForm.value = { ID: deps.generateTempId(), KATEGORI: '', BRAND: '', SERI: '' };
        }
        deps.showNamaStockFormModal.value = true;
    };

    const closeNamaStockFormModal = () => {
        deps.showNamaStockFormModal.value = false;
    };

    const submitNamaStockForm = () => {
        const payload = deps.normalizeNamaStockPayload(deps.namaStockForm.value || {});
        if (!payload.KATEGORI || !payload.BRAND || !payload.SERI) {
            deps.showNotification('Kategori, Brand, dan Seri wajib diisi.');
            return;
        }
        const current = [...deps.namaStockRows.value];
        const payloadKey = deps.normalizeNamaStockKeyStrict(payload);
        const dupIdx = current.findIndex((r) => String(r.ID) !== String(payload.ID) && deps.normalizeNamaStockKeyStrict(r) === payloadKey);
        if (dupIdx > -1) {
            deps.showNotification('Kombinasi Kategori, Brand, dan Seri sudah ada.');
            return;
        }
        const idx = current.findIndex((r) => String(r.ID) === String(payload.ID));
        if (idx > -1) current[idx] = payload;
        else current.unshift(payload);
        deps.namaStockRows.value = current;
        deps.showNamaStockFormModal.value = false;
        saveNamaStockSettings();
    };

    const removeNamaStockRow = (id) => {
        deps.showConfirm('Hapus Nama Stock?', 'Data yang dihapus tidak bisa dikembalikan.', () => {
            deps.namaStockRows.value = deps.namaStockRows.value.filter((r) => String(r.ID) !== String(id));
            saveNamaStockSettings();
        });
    };

    return {
        openNamaStockFormModal,
        closeNamaStockFormModal,
        submitNamaStockForm,
        removeNamaStockRow,
        saveNamaStockSettings,
    };
}

export {
    generateTempId,
    normalizeNamaStockPayload,
    normalizeNamaStockKeyStrict,
    createNamaStockState,
    createNamaStockActions,
};
