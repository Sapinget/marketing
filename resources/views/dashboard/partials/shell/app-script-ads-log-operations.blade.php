@verbatim
                // Ads Log
                const adsComputedScore = computed(() => {
                    const suka = Number(adsForm.value.Suka) || 0;
                    const komentar = Number(adsForm.value.Komentar) || 0;
                    const share = Number(adsForm.value.Share) || 0;
                    const raw = suka * 1 + komentar * 3 + share * 5;
                    return parseFloat(Math.min(100, raw / 5000 * 100).toFixed(1));
                });

                const adsSaldoPlatform = computed(() => {
                    const bc = budgetConfig.value;
                    const p = (adsForm.value.Platform || '').toLowerCase();
                    if (p === 'meta') return bc.meta?.balance || 0;
                    if (p === 'google') return bc.google?.balance || 0;
                    if (p === 'mekari') return (bc.mekari?.visitor?.balance || 0) + (bc.mekari?.broadcast?.balance || 0);
                    const other = (bc.others || []).find(o => (o.name || '').toLowerCase() === p);
                    return other?.balance || 0;
                });

                const adsPlatformColor = (platform) => {
                    const p = (platform || '').toLowerCase();
                    if (p === 'meta') return 'bg-blue-100 text-blue-700';
                    if (p === 'google') return 'bg-red-100 text-red-700';
                    if (p === 'mekari') return 'bg-teal-100 text-teal-700';
                    return 'bg-slate-100 text-slate-600';
                };

                const filteredAdsData = computed(() => {
                    let data = adsData.value;
                    const q = adsSearch.value.trim().toLowerCase();
                    if (q) data = data.filter(r => (r.Nama || '').toLowerCase().includes(q) || (r.Platform || '').toLowerCase().includes(q) || (r.Kategori || '').toLowerCase().includes(q));
                    if (adsDateFilter.value.start) data = data.filter(r => isDateInRange(r.Tanggal, adsDateFilter.value.start, adsDateFilter.value.end || adsDateFilter.value.start));
                    return data;
                });

                const adsTotalPages = computed(() => Math.max(1, Math.ceil(filteredAdsData.value.length / 20)));
                const pagedAdsData = computed(() => {
                    const start = (adsPage.value - 1) * 20;
                    return filteredAdsData.value.slice(start, start + 20);
                });

                const openAdsModal = (type = 'create', row = null) => {
                    adsModalType.value = type;
                    adsForm.value = row ? { ...row } : { ID: null, Tanggal: todayStr(), Nama: '', ID_Ads: '', Platform: 'Meta', Kategori: '', Jangkauan: 0, Suka: 0, Komentar: 0, Share: 0, Rata_Komentar: 0, Biaya: 0 };
                    adsModalOpen.value = true;
                };

                const saveAdsRow = () => {
                    const form = { ...adsForm.value };
                    if (!form.Tanggal || !form.Nama) { showNotification('Tanggal dan Nama Iklan wajib diisi', 'error'); return; }
                    form.Rata_Komentar = adsComputedScore.value;
                    submitting.value = true;
                    ensureRunApi().withSuccessHandler(result => {
                        submitting.value = false;
                        const saved = (result && result.data) ? result.data : { ...form, ID: form.ID || ('AD' + Date.now()) };
                        const idx = adsData.value.findIndex(r => String(r.ID) === String(saved.ID));
                        if (idx >= 0) adsData.value[idx] = saved;
                        else adsData.value.unshift(saved);
                        adsModalOpen.value = false;
                        showNotification('Ads berhasil disimpan');
                    }).withFailureHandler(err => {
                        submitting.value = false;
                        notifyError('Gagal menyimpan', err, 'Data iklan belum berhasil disimpan.');
                    }).saveAds(form);
                };

                const deleteAdsRow = (id) => {
                    if (!confirm('Hapus data iklan ini?')) return;
                    ensureRunApi().withSuccessHandler(() => {
                        adsData.value = adsData.value.filter(r => String(r.ID) !== String(id));
                        showNotification('Ads dihapus');
                    }).withFailureHandler(err => notifyError('Gagal menghapus', err, 'Data iklan belum berhasil dihapus.'))
                        .deleteAds(id);
                };
@endverbatim
