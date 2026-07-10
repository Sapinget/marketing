@verbatim
                // Harga & Kompetitor
                const filteredHargaKompetitorData = computed(() => {
                    let data = hargaKompetitorData.value;
                    if (hargaKompetitorSearch.value) {
                        const q = hargaKompetitorSearch.value.toLowerCase();
                        data = data.filter(r => (r.Nama_Produk || '').toLowerCase().includes(q));
                    }
                    if (hargaKompetitorDateFilter.value.start) {
                        data = data.filter(r => isDateInRange(r.Tanggal_Cek, hargaKompetitorDateFilter.value.start, hargaKompetitorDateFilter.value.end || hargaKompetitorDateFilter.value.start));
                    }
                    return data;
                });
                const hargaKompetitorTotalPages = computed(() => Math.max(1, Math.ceil(filteredHargaKompetitorData.value.length / 20)));
                const pagedHargaKompetitorData = computed(() => {
                    const p = hargaKompetitorPage.value;
                    return filteredHargaKompetitorData.value.slice((p - 1) * 20, p * 20);
                });
                watch([hargaKompetitorSearch, hargaKompetitorDateFilter], () => { hargaKompetitorPage.value = 1; }, { deep: true });

                const openHargaKompetitorModal = (type = 'create', row = null) => {
                    hargaKompetitorModalType.value = type;
                    hargaKompetitorForm.value = row ? { ...row } : { ID: null, Nama_Produk: '', Tanggal_Cek: todayStr(), Harga_Distributor_1: 0, Harga_Distributor_2: 0, Harga_Kompetitor: 0, Harga_Rencana_Jual: 0, Margin_Profit: 0, Selisih: 0 };
                    hargaKompetitorModalOpen.value = true;
                };

                const saveHargaKompetitor = () => {
                    const form = hargaKompetitorForm.value;
                    form.Selisih = (form.Harga_Rencana_Jual || 0) - (form.Harga_Kompetitor || 0);
                    submitting.value = true;
                    ensureRunApi()
                        .withSuccessHandler(res => {
                            submitting.value = false;
                            if (!form.ID) {
                                hargaKompetitorData.value.unshift({ ...form, ID: (res && res.id) || ('HK' + Date.now()) });
                            } else {
                                const idx = hargaKompetitorData.value.findIndex(r => String(r.ID) === String(form.ID));
                                if (idx !== -1) hargaKompetitorData.value.splice(idx, 1, { ...form });
                            }
                            hargaKompetitorModalOpen.value = false;
                            showNotification('Data harga disimpan');
                        })
                        .withFailureHandler(err => { submitting.value = false; handleError(err); })
                        .saveHargaKompetitor(form);
                };

                const deleteHargaKompetitor = (id) => {
                    if (!confirm('Hapus data ini?')) return;
                    ensureRunApi()
                        .withSuccessHandler(() => {
                            hargaKompetitorData.value = hargaKompetitorData.value.filter(r => String(r.ID) !== String(id));
                            showNotification('Data dihapus');
                        })
                        .withFailureHandler(err => handleError(err))
                        .deleteHargaKompetitor(id);
                };
@endverbatim
