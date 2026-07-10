@verbatim
                // Asset Vendor Inventory
                const loadAssetVendorInventory = () => {
                    ensureRunApi().withSuccessHandler(d => { aviData.value = Array.isArray(d) ? d : []; }).withFailureHandler(() => {}).getAssetVendorInventoryData();
                };

                // Asset Vendor Inventory
                const filteredAviData = computed(() => {
                    if (!aviSearch.value) return aviData.value;
                    const q = aviSearch.value.toLowerCase();
                    return aviData.value.filter(r =>
                        (r.vendor || '').toLowerCase().includes(q) ||
                        (r.brand || '').toLowerCase().includes(q) ||
                        (r.seri || '').toLowerCase().includes(q) ||
                        (r.imei || '').toLowerCase().includes(q)
                    );
                });
                const aviTotalPages = computed(() => Math.max(1, Math.ceil(filteredAviData.value.length / 20)));
                const pagedAviData = computed(() => {
                    const p = aviPage.value;
                    return filteredAviData.value.slice((p - 1) * 20, p * 20);
                });
                const aviUniqueVendors = computed(() => {
                    const s = new Set(); aviData.value.forEach(r => { if (r.vendor) s.add(r.vendor); }); return s.size;
                });
                const aviUniqueBrands = computed(() => {
                    const s = new Set(); aviData.value.forEach(r => { if (r.brand) s.add(r.brand); }); return s.size;
                });
                const aviTotalQuantity = computed(() => aviData.value.reduce((s, r) => s + (Number(r.quantity) || 0), 0));
                const aviSummaryChips = computed(() => {
                    const m = {};
                    aviData.value.forEach(r => { const k = (r.vendor || '-').trim(); m[k] = (m[k] || 0) + 1; });
                    return Object.entries(m).sort((a, b) => b[1] - a[1]).slice(0, 6).map(([label, n], i) => {
                        const _SPAL = ['bg-blue-50 text-blue-700', 'bg-emerald-50 text-emerald-700', 'bg-amber-50 text-amber-700', 'bg-rose-50 text-rose-700', 'bg-violet-50 text-violet-700', 'bg-slate-100 text-slate-600'];
                        return { label: String(label).toUpperCase(), n, cls: _SPAL[i % _SPAL.length] };
                    });
                });
                const aviConditionOptions = ['New', 'Used', 'Refurbished', 'Display Unit', 'Broken', 'Others'];
                watch(aviSearch, () => { aviPage.value = 1; });

                const openAviModal = (type = 'create', row = null) => {
                    aviModalType.value = type;
                    aviForm.value = row ? { ...row } : { ID: null, source_id: '', vendor: '', brand: '', seri: '', imei: '', quantity: 1, condition: 'New', purchase_date: todayStr(), notes: '' };
                    aviModalOpen.value = true;
                };

                const saveAvi = () => {
                    const form = aviForm.value;
                    const payload = {
                        source_id: form.source_id || null,
                        vendor: form.vendor,
                        brand: form.brand,
                        seri: form.seri,
                        imei: form.imei,
                        quantity: Number(form.quantity) || 0,
                        condition: form.condition,
                        purchase_date: form.purchase_date,
                        notes: form.notes,
                    };
                    submitting.value = true;
                    ensureRunApi()
                        .withSuccessHandler(res => {
                            submitting.value = false;
                            const saved = { ...payload, ID: res && res.id ? res.id : (form.ID || ('AVI' + Date.now())) };
                            if (!form.ID) {
                                aviData.value.unshift(saved);
                            } else {
                                saved.ID = form.ID;
                                const idx = aviData.value.findIndex(r => String(r.ID) === String(form.ID));
                                if (idx !== -1) aviData.value.splice(idx, 1, saved);
                            }
                            aviModalOpen.value = false;
                            showNotification('Data asset disimpan');
                        })
                        .withFailureHandler(err => { submitting.value = false; handleError(err); })
                        .saveAvi(payload);
                };

                const deleteAvi = (id) => {
                    if (!confirm('Hapus data asset ini?')) return;
                    ensureRunApi()
                        .withSuccessHandler(() => {
                            aviData.value = aviData.value.filter(r => String(r.ID) !== String(id));
                            showNotification('Asset dihapus');
                        })
                        .withFailureHandler(err => handleError(err))
                        .deleteAvi(id);
                };
@endverbatim
