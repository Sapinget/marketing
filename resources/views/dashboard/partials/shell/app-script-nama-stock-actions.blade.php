@verbatim
                const openNamaStockFormModal = (mode = 'create', row = null) => {
                    namaStockFormMode.value = mode === 'edit' ? 'edit' : 'create';
                    if (mode === 'edit' && row) {
                        namaStockForm.value = { ...normalizeNamaStockPayload(row) };
                    } else {
                        namaStockForm.value = { ID: generateTempId(), KATEGORI: '', BRAND: '', SERI: '' };
                    }
                    showNamaStockFormModal.value = true;
                };

                const closeNamaStockFormModal = () => { showNamaStockFormModal.value = false; };

                const submitNamaStockForm = () => {
                    const payload = normalizeNamaStockPayload(namaStockForm.value || {});
                    if (!payload.KATEGORI || !payload.BRAND || !payload.SERI) {
                        showNotification('Kategori, Brand, dan Seri wajib diisi.');
                        return;
                    }
                    let current = [...namaStockRows.value];
                    const payloadKey = normalizeNamaStockKeyStrict(payload);
                    const dupIdx = current.findIndex(r => String(r.ID) !== String(payload.ID) && normalizeNamaStockKeyStrict(r) === payloadKey);
                    if (dupIdx > -1) {
                        showNotification('Kombinasi Kategori, Brand, dan Seri sudah ada.');
                        return;
                    }
                    const idx = current.findIndex(r => String(r.ID) === String(payload.ID));
                    if (idx > -1) current[idx] = payload;
                    else current.unshift(payload);
                    namaStockRows.value = current;
                    showNamaStockFormModal.value = false;
                    saveNamaStockSettings();
                };

                const removeNamaStockRow = (id) => {
                    showConfirm('Hapus Nama Stock?', 'Data yang dihapus tidak bisa dikembalikan.', () => {
                        namaStockRows.value = namaStockRows.value.filter(r => String(r.ID) !== String(id));
                        saveNamaStockSettings();
                    });
                };

                const saveNamaStockSettings = async () => {
                    if (namaStockSaving.value) return;
                    const rows = namaStockRows.value;
                    for (const r of rows) {
                        if (!r.KATEGORI || !r.BRAND || !r.SERI) {
                            showNotification('Data Nama Stock belum lengkap. Isi kategori, brand, dan seri terlebih dulu.', 'error');
                            return;
                        }
                    }
                    const seen = new Map();
                    rows.forEach(r => { const k = normalizeNamaStockKeyStrict(r); if (!seen.has(k)) seen.set(k, r); });
                    const normalized = Array.from(seen.values());
                    namaStockRows.value = normalized;
                    namaStockSaving.value = true;
                    ensureRunApi()
                        .withSuccessHandler(() => {
                            namaStockSaving.value = false;
                            namaStockLoaded.value = true;
                            showNotification(`Nama Stock tersimpan (${normalized.length} baris).`);
                        })
                        .withFailureHandler(err => {
                            namaStockSaving.value = false;
                            notifyError('Gagal menyimpan', err, 'Nama Stock belum berhasil disimpan.');
                        })
                        .saveNamaStockRows(normalized);
                };
@endverbatim
