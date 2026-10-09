@verbatim
                // Meta IG Analytics
                const metaStoryData = ref([]);
                const metaFeedData = ref([]);
                const metaStoryLoaded = ref(false);
                const metaFeedLoaded = ref(false);
                const metaUploading = ref(false);
                const metaStorySearch = ref('');
                const metaFeedSearch = ref('');
                const metaFeedAccount = ref('');
                const metaFeedAccountSearch = ref('');
                const metaStoryDateFilter = ref({ start: '', end: '' });
                const metaFeedDateFilter = ref({ start: '', end: '' });
                const metaFeedManualModalOpen = ref(false);
                const createMetaFeedManualForm = () => ({
                    post_id: '',
                    account: '',
                    publish_time: '',
                    post_type: 'IG reel',
                    description: '',
                    views: 0,
                    reach: 0,
                    likes: 0,
                    comments: 0,
                    shares: 0,
                    saves: 0,
                });
                const metaFeedManualForm = ref(createMetaFeedManualForm());
                const metaFeedManualMetricFields = [
                    { key: 'views', label: 'Views' },
                    { key: 'reach', label: 'Reach' },
                    { key: 'likes', label: 'Likes' },
                    { key: 'comments', label: 'Komen' },
                    { key: 'shares', label: 'Share' },
                    { key: 'saves', label: 'Save' },
                ];
                const bonusConfigLoaded = ref(false);
                const budgetConfigLoaded = ref(false);
                const tabDataLoaded = ref({});

                // Nama Stock Helpers & Methods (already destructured in nama-stock-actions, reuse here)
                const namaStockFilterKategoriOptions = computed(() => filteredUniqueFrom(namaStockRows.value, 'KATEGORI'));
                const namaStockFilterBrandOptions = computed(() => filteredUniqueFrom(namaStockRows.value, 'BRAND', { KATEGORI: namaStockKategoriFilter.value }));

                const namaStockFilteredRows = computed(() => {
                    const q = (namaStockSearchQuery.value || '').toLowerCase().trim();
                    return namaStockRows.value.filter(row => {
                        const matchKategori = !namaStockKategoriFilter.value || String(row.KATEGORI || '').trim().toUpperCase() === String(namaStockKategoriFilter.value || '').trim().toUpperCase();
                        const matchBrand = !namaStockBrandFilter.value || String(row.BRAND || '').trim().toUpperCase() === String(namaStockBrandFilter.value || '').trim().toUpperCase();
                        const hay = `${row.KATEGORI || ''} ${row.BRAND || ''} ${row.SERI || ''}`.toLowerCase();
                        const matchQuery = !q || hay.includes(q);
                        return matchKategori && matchBrand && matchQuery;
                    });
                });

                const namaStockPage = ref(1);
                const namaStockTotalPages = computed(() => Math.max(1, Math.ceil(namaStockFilteredRows.value.length / PAGE_SIZE)));
                const pagedNamaStockRows = computed(() => namaStockFilteredRows.value.slice((namaStockPage.value - 1) * PAGE_SIZE, namaStockPage.value * PAGE_SIZE));
                watch(() => [namaStockSearchQuery.value, namaStockKategoriFilter.value, namaStockBrandFilter.value], () => { namaStockPage.value = 1; });
                watch(() => namaStockKategoriFilter.value, () => {
                    if (!namaStockFilterBrandOptions.value.includes(namaStockBrandFilter.value)) {
                        namaStockBrandFilter.value = '';
                    }
                });

                const initNamaStockRows = (src) => {
                    const rows = (Array.isArray(src) ? src : []).map(row => ({
                        ID: String(row?.ID || namaStockGenerateTempId()),
                        KATEGORI: String(row?.KATEGORI || row?.Kategori || '').trim().toUpperCase(),
                        BRAND: String(row?.BRAND || row?.Brand || '').trim().toUpperCase(),
                        SERI: String(row?.SERI || row?.Seri || '').trim().toUpperCase(),
                    }));
                    const map = new Map();
                    rows.forEach(r => { const key = namaStockNormalizeKeyStrict(r); if (!map.has(key)) map.set(key, r); });
                    namaStockRows.value = Array.from(map.values());
                };

                const loadNamaStockData = () => {
                    return new Promise(resolve => {
                        ensureRunApi()
                            .withSuccessHandler(data => { initNamaStockRows(Array.isArray(data) ? data : []); namaStockLoaded.value = true; resolve(); })
                            .withFailureHandler(() => { namaStockLoaded.value = true; resolve(); })
                            .getNamaStockData();
                    });
                };

                // Meta IG Analytics: load + CSV upload/parse
                const loadMetaStory = () => new Promise(resolve => {
                    ensureRunApi()
                        .withSuccessHandler(data => { metaStoryData.value = Array.isArray(data) ? data : []; metaStoryLoaded.value = true; resolve(); })
                        .withFailureHandler(() => { metaStoryLoaded.value = true; resolve(); })
                        .getMetaStoryData();
                });
                const loadMetaFeed = () => new Promise(resolve => {
                    ensureRunApi()
                        .withSuccessHandler(data => { metaFeedData.value = Array.isArray(data) ? data : []; metaFeedLoaded.value = true; resolve(); })
                        .withFailureHandler(() => { metaFeedLoaded.value = true; resolve(); })
                        .getMetaFeedData();
                });

                // CSV header (urutan bebas) -> key kanonik
                const _META_KEYMAP = {
                    'post id': 'post_id',
                    'account username': 'account',
                    'account name': 'account_name',
                    'description': 'description',
                    'duration (sec)': 'duration',
                    'publish time': 'publish_time',
                    'permalink': 'permalink',
                    'post type': 'post_type',
                    'views': 'views',
                    'reach': 'reach',
                    'likes': 'likes',
                    'shares': 'shares',
                    'comments': 'comments',
                    'saves': 'saves',
                    'follows': 'follows',
                    'profile visits': 'profile_visits',
                    'replies': 'replies',
                    'navigation': 'navigation',
                    'link clicks': 'link_clicks',
                    'sticker taps': 'sticker_taps'
                };
                const _META_NUM = new Set(['views', 'reach', 'likes', 'shares', 'comments', 'saves', 'follows', 'profile_visits', 'replies', 'navigation', 'link_clicks', 'sticker_taps', 'duration']);
                const _META_REQUIRED = new Set(['post id', 'account username', 'publish time']);
                const _normalizeMetaHeaders = (headers) => headers.map(h => String(h || '').replace(/^﻿/, '').trim().toLowerCase()).filter(Boolean);
                const _hasMetaRequiredHeaders = (headers) => Array.from(_META_REQUIRED).every(key => headers.includes(key));
                const _inferMetaDataset = (headers) => {
                    if (headers.includes('navigation') || headers.includes('sticker taps') || headers.includes('replies') || headers.includes('link clicks') || headers.includes('profile visits')) {
                        return 'story';
                    }
                    if (headers.includes('comments') || headers.includes('saves') || headers.includes('shares') || headers.includes('reach') || headers.includes('views')) {
                        return 'feed';
                    }
                    return null;
                };
                const _normalizeMetaRow = (raw) => {
                    const o = { raw_payload: raw };
                    Object.keys(raw).forEach(h => {
                        const ck = _META_KEYMAP[String(h).trim().toLowerCase()];
                        if (!ck) return;
                        const v = raw[h];
                        if (ck === 'publish_time') o[ck] = (v == null ? '' : String(v).trim());
                        else if (_META_NUM.has(ck)) { const n = parseInt(String(v).replace(/[^0-9-]/g, ''), 10); o[ck] = Number.isFinite(n) ? n : (String(v).trim() === '' ? 0 : null); }
                        else o[ck] = (v == null ? '' : String(v));
                    });
                    return o;
                };
                const refreshMetaDataset = (dataset) => {
                    if (dataset === 'story') {
                        metaStoryLoaded.value = false;
                        loadMetaStory();
                    } else {
                        metaFeedLoaded.value = false;
                        loadMetaFeed();
                    }
                };
                const handleMetaImportResult = (dataset, result, retryImport, successMessage) => {
                    const r = result || {};
                    if (r.requires_confirmation) {
                        const duplicateCount = Number(r.duplicates) || 0;
                        const sampleIds = Array.isArray(r.sample_post_ids) && r.sample_post_ids.length ? ` Contoh Post ID: ${r.sample_post_ids.join(', ')}.` : '';
                        showConfirm(
                            'Data Meta Sudah Ada',
                            `${duplicateCount} data terdeteksi ganda dan akan ditimpa jika dilanjutkan.${sampleIds}`,
                            retryImport,
                            'info'
                        );
                        return;
                    }
                    showNotification(successMessage(r));
                    refreshMetaDataset(dataset);
                };
                const uploadMetaCsv = (file, dataset) => {
                    if (!file) return;
                    if (typeof Papa === 'undefined') { showNotification('Library CSV belum termuat, refresh halaman.'); return; }
                    metaUploading.value = true;
                    Papa.parse(file, {
                        header: true, skipEmptyLines: true,
                        complete: (res) => {
                            try {
                                const raw = res.data || [];
                                const headers = _normalizeMetaHeaders((res.meta && res.meta.fields) || []);
                                if (!_hasMetaRequiredHeaders(headers)) {
                                    metaUploading.value = false;
                                    showNotification('Header export Meta tidak cocok. Wajib ada: Post ID, Account username, Publish time.');
                                    return;
                                }
                                const detectedDataset = _inferMetaDataset(headers);
                                if (dataset === 'story' && detectedDataset !== 'story') {
                                    metaUploading.value = false;
                                    showNotification('File ini bukan export Story IG. Upload file Story yang punya metrik khas story seperti Navigation, Replies, Link Clicks, Profile Visits, atau Sticker Taps.');
                                    return;
                                }
                                if (dataset === 'feed' && detectedDataset !== 'feed') {
                                    metaUploading.value = false;
                                    showNotification('File ini bukan export Feed IG. Upload file Feed yang punya metrik seperti Views, Reach, Likes, Comments, Shares, atau Saves.');
                                    return;
                                }
                                const rows = raw.map(_normalizeMetaRow).filter(r => String(r.post_id || '').trim() !== '');
                                if (!rows.length) { metaUploading.value = false; showNotification('Tidak ada baris valid (Post ID kosong).'); return; }
                                const runner = ensureRunApi();
                                const fn = dataset === 'story' ? 'importMetaStory' : 'importMetaFeed';
                                const executeImport = (overwrite = false) => runner.withSuccessHandler(result => {
                                    metaUploading.value = false;
                                    handleMetaImportResult(
                                        dataset,
                                        result,
                                        () => { metaUploading.value = true; executeImport(true); },
                                        (r) => `Import sukses: ${r.inserted || 0} baru, ${r.updated || 0} update`
                                    );
                                }).withFailureHandler(err => { metaUploading.value = false; notifyError('Import gagal', err, 'CSV gagal diimport.'); })[fn](rows, { overwrite });
                                executeImport(false);
                            } catch (e) { metaUploading.value = false; notifyError('Parse gagal', e, 'CSV gagal diparse.'); }
                        },
                        error: (err) => { metaUploading.value = false; notifyError('Parse gagal', err, 'CSV gagal dibaca.'); }
                    });
                };
                const handleMetaFileInput = (event, dataset) => {
                    const file = event.target.files && event.target.files[0];
                    uploadMetaCsv(file, dataset);
                    event.target.value = '';
                };
                const importMetaFolder = (dataset) => {
                    metaUploading.value = true;
                    const runner = ensureRunApi();
                    const fn = dataset === 'story' ? 'importMetaStoryFolder' : 'importMetaFeedFolder';
                    const executeImport = (overwrite = false) => runner.withSuccessHandler(result => {
                        metaUploading.value = false;
                        handleMetaImportResult(
                            dataset,
                            result,
                            () => { metaUploading.value = true; executeImport(true); },
                            (r) => `Import folder selesai: ${r.inserted || 0} baru, ${r.updated || 0} update, ${r.files_matched || 0}/${r.files_scanned || 0} file cocok`
                        );
                    }).withFailureHandler(err => {
                        metaUploading.value = false;
                        notifyError('Import folder gagal', err, 'Folder export-meta belum berhasil diproses.');
                    })[fn]({ overwrite });
                    executeImport(false);
                };
                const openMetaFeedManualModal = () => {
                    metaFeedManualForm.value = createMetaFeedManualForm();
                    metaFeedManualModalOpen.value = true;
                };
                const closeMetaFeedManualModal = () => {
                    if (submitting.value) return;
                    metaFeedManualModalOpen.value = false;
                };
                const toMetaManualNumber = (value) => {
                    const parsed = parseInt(String(value ?? '').replace(/[^0-9-]/g, ''), 10);
                    return Number.isFinite(parsed) && parsed > 0 ? parsed : 0;
                };
                const toMetaManualDateTime = (value) => {
                    const raw = String(value || '').trim();
                    if (!raw) return '';
                    return raw.length === 16 ? `${raw.replace('T', ' ')}:00` : raw.replace('T', ' ');
                };
                const buildMetaFeedManualRow = () => {
                    const form = metaFeedManualForm.value || {};
                    return {
                        post_id: String(form.post_id || '').trim(),
                        account: String(form.account || '').trim(),
                        publish_time: toMetaManualDateTime(form.publish_time),
                        post_type: String(form.post_type || '').trim() || 'IG reel',
                        description: String(form.description || '').trim(),
                        views: toMetaManualNumber(form.views),
                        reach: toMetaManualNumber(form.reach),
                        likes: toMetaManualNumber(form.likes),
                        comments: toMetaManualNumber(form.comments),
                        shares: toMetaManualNumber(form.shares),
                        saves: toMetaManualNumber(form.saves),
                    };
                };
                const saveMetaFeedManual = () => {
                    if (submitting.value) return;
                    const row = buildMetaFeedManualRow();
                    if (!row.post_id) { showNotification('Post ID wajib diisi.'); return; }
                    if (!row.account) { showNotification('Akun wajib diisi.'); return; }
                    if (!row.publish_time) { showNotification('Tanggal publish wajib diisi.'); return; }

                    const runner = ensureRunApi();
                    const executeSave = (overwrite = false) => {
                        submitting.value = true;
                        runner.withSuccessHandler(result => {
                            submitting.value = false;
                            if (result?.requires_confirmation) {
                                showConfirm(
                                    'Data Feed Sudah Ada',
                                    'Post ID ini sudah ada. Lanjutkan untuk menimpa data lama.',
                                    () => executeSave(true),
                                    'info'
                                );
                                return;
                            }
                            metaFeedManualModalOpen.value = false;
                            showNotification('Data Feed berhasil ditambahkan.');
                            refreshMetaDataset('feed');
                        }).withFailureHandler(err => {
                            submitting.value = false;
                            notifyError('Simpan Feed gagal', err, 'Data Feed manual belum berhasil disimpan.');
                        }).importMetaFeed([row], { overwrite });
                    };
                    executeSave(false);
                };

@endverbatim
