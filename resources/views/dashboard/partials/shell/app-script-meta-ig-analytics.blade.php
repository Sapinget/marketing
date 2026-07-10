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
                const metaStoryDateFilter = ref(getDefaultDateRange());
                const metaFeedDateFilter = ref(getDefaultDateRange());
                const bonusConfigLoaded = ref(false);
                const budgetConfigLoaded = ref(false);
                const tabDataLoaded = ref({});

                // Nama Stock Helpers & Methods
                const generateTempId = () => 'tmp-' + Date.now() + '-' + Math.random().toString(36).slice(2, 7);

                const normalizeNamaStockPayload = (row) => ({
                    ID: String(row?.ID || generateTempId()),
                    KATEGORI: String(row?.KATEGORI || '').trim().toUpperCase(),
                    BRAND: String(row?.BRAND || '').trim().toUpperCase(),
                    SERI: String(row?.SERI || '').trim().toUpperCase(),
                });

                const normalizeNamaStockKeyStrict = (row) => {
                    const clean = v => String(v || '').trim().replace(/\s+/g, ' ').toUpperCase();
                    return `${clean(row?.KATEGORI)}|${clean(row?.BRAND)}|${clean(row?.SERI)}`;
                };

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
                        ID: String(row?.ID || generateTempId()),
                        KATEGORI: String(row?.KATEGORI || row?.Kategori || '').trim().toUpperCase(),
                        BRAND: String(row?.BRAND || row?.Brand || '').trim().toUpperCase(),
                        SERI: String(row?.SERI || row?.Seri || '').trim().toUpperCase(),
                    }));
                    const map = new Map();
                    rows.forEach(r => { const key = normalizeNamaStockKeyStrict(r); if (!map.has(key)) map.set(key, r); });
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
                const _META_KEYMAP = { 'post id': 'post_id', 'account username': 'account', 'account name': 'account_name', 'description': 'description', 'duration (sec)': 'duration', 'publish time': 'publish_time', 'permalink': 'permalink', 'post type': 'post_type', 'views': 'views', 'reach': 'reach', 'likes': 'likes', 'shares': 'shares', 'comments': 'comments', 'saves': 'saves', 'follows': 'follows', 'profile visits': 'profile_visits', 'replies': 'replies', 'navigation': 'navigation', 'link clicks': 'link_clicks', 'sticker taps': 'sticker_taps' };
                const _META_NUM = new Set(['views', 'reach', 'likes', 'shares', 'comments', 'saves', 'follows', 'profile_visits', 'replies', 'navigation', 'link_clicks', 'sticker_taps', 'duration']);
                const _parseMetaDate = (s) => {
                    const m = String(s || '').trim().match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})\s+(\d{1,2}):(\d{2})/);
                    if (!m) return null;
                    const [, mm, dd, yyyy, hh, mi] = m;
                    const pacificUtcMs = Date.UTC(Number(yyyy), Number(mm) - 1, Number(dd), Number(hh) + 7, Number(mi), 0);
                    const gmt8 = new Date(pacificUtcMs + (8 * 60 * 60 * 1000));
                    const y = gmt8.getUTCFullYear();
                    const mo = String(gmt8.getUTCMonth() + 1).padStart(2, '0');
                    const d = String(gmt8.getUTCDate()).padStart(2, '0');
                    const h = String(gmt8.getUTCHours()).padStart(2, '0');
                    const min = String(gmt8.getUTCMinutes()).padStart(2, '0');
                    return `${y}-${mo}-${d} ${h}:${min}:00`;
                };
                const _normalizeMetaRow = (raw) => {
                    const o = { raw_payload: raw };
                    Object.keys(raw).forEach(h => {
                        const ck = _META_KEYMAP[String(h).trim().toLowerCase()];
                        if (!ck) return;
                        const v = raw[h];
                        if (ck === 'publish_time') o[ck] = _parseMetaDate(v);
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
                                const headers = ((res.meta && res.meta.fields) || []).map(h => String(h).trim().toLowerCase());
                                const isStory = headers.includes('navigation') || headers.includes('sticker taps');
                                const isFeed = headers.includes('comments') || headers.includes('saves');
                                if (dataset === 'story' && !isStory) { metaUploading.value = false; showNotification('File ini bukan export Story IG (tidak ada kolom Navigation/Sticker taps).'); return; }
                                if (dataset === 'feed' && !isFeed) { metaUploading.value = false; showNotification('File ini bukan export Feed (tidak ada kolom Comments/Saves).'); return; }
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

                // Meta IG: filter + summary + chart data
                const _metaInRange = (dateStr, f) => { if (!f || !f.start) return true; return isDateInRange(dateStr, f.start, f.end || f.start); };
                const filteredMetaStory = computed(() => {
                    const q = metaStorySearch.value.trim().toLowerCase();
                    return (metaStoryData.value || []).filter(r =>
                        _metaInRange(r.publish_time, metaStoryDateFilter.value) &&
                        (!q || String(r.description || '').toLowerCase().includes(q) || String(r.post_id || '').includes(q)));
                });
                const metaFeedAccounts = computed(() => Array.from(new Set((metaFeedData.value || []).map(r => r.account).filter(Boolean))).sort());
                const filteredMetaFeed = computed(() => {
                    const q = metaFeedSearch.value.trim().toLowerCase();
                    const acc = metaFeedAccount.value;
                    return (metaFeedData.value || []).filter(r =>
                        _metaInRange(r.publish_time, metaFeedDateFilter.value) &&
                        (!acc || r.account === acc) &&
                        (!q || String(r.description || '').toLowerCase().includes(q) || String(r.post_id || '').includes(q)));
                });
                const _metaSum = (d, f) => d.reduce((s, r) => s + (Number(r[f]) || 0), 0);
                const metaStorySummary = computed(() => {
                    const d = filteredMetaStory.value; const sum = f => _metaSum(d, f);
                    return { cards: [
                        { label: 'Total Story', value: formatNumber(d.length), unit: 'Post', icon: 'fa-clapperboard', color: 'text-rose-500', unitColor: 'text-rose-400', subColor: 'text-rose-600', sub: 'Periode aktif' },
                        { label: 'Total Views', value: formatNumber(sum('views')), icon: 'fa-eye', color: 'text-blue-500', subColor: 'text-blue-600', sub: 'Tayangan' },
                        { label: 'Total Reach', value: formatNumber(sum('reach')), icon: 'fa-bullhorn', color: 'text-emerald-500', subColor: 'text-emerald-600', sub: 'Jangkauan' },
                        { label: 'Follows', value: formatNumber(sum('follows')), icon: 'fa-user-plus', color: 'text-violet-500', subColor: 'text-violet-600', sub: 'Dari story' },
                        { label: 'Navigation', value: formatNumber(sum('navigation')), icon: 'fa-arrows-left-right', color: 'text-amber-500', subColor: 'text-amber-600', sub: 'Geser story' },
                        { label: 'Link Clicks', value: formatNumber(sum('link_clicks')), icon: 'fa-link', color: 'text-blue-500', subColor: 'text-blue-600', sub: 'Klik link' },
                        { label: 'Profile Visits', value: formatNumber(sum('profile_visits')), icon: 'fa-user', color: 'text-emerald-500', subColor: 'text-emerald-600', sub: 'Kunjungan profil' },
                        { label: 'Sticker Taps', value: formatNumber(sum('sticker_taps')), icon: 'fa-hand-pointer', color: 'text-rose-500', subColor: 'text-rose-600', sub: 'Tap stiker' },
                    ], chips: _sChips(_sCnt(d, r => r.post_type)) };
                });
                const metaFeedSummary = computed(() => {
                    const d = filteredMetaFeed.value; const sum = f => _metaSum(d, f);
                    const reach = sum('reach'); const eng = sum('likes') + sum('comments') + sum('shares') + sum('saves');
                    return { cards: [
                        { label: 'Total Konten', value: formatNumber(d.length), unit: 'Post', icon: 'fa-photo-film', color: 'text-blue-500', unitColor: 'text-blue-400', subColor: 'text-blue-600', sub: 'Periode aktif' },
                        { label: 'Total Views', value: formatNumber(sum('views')), icon: 'fa-eye', color: 'text-violet-500', subColor: 'text-violet-600', sub: 'Tayangan' },
                        { label: 'Total Reach', value: formatNumber(reach), icon: 'fa-bullhorn', color: 'text-emerald-500', subColor: 'text-emerald-600', sub: 'Jangkauan' },
                        { label: 'Engagement Rate', value: (reach ? (eng / reach * 100).toFixed(1) : '0') + '%', icon: 'fa-fire', color: 'text-rose-500', subColor: 'text-rose-600', sub: '(L+C+S+Sv)/Reach' },
                        { label: 'Likes', value: formatNumber(sum('likes')), icon: 'fa-heart', color: 'text-rose-500', subColor: 'text-rose-600', sub: 'Total suka' },
                        { label: 'Comments', value: formatNumber(sum('comments')), icon: 'fa-comment', color: 'text-blue-500', subColor: 'text-blue-600', sub: 'Total komentar' },
                        { label: 'Shares', value: formatNumber(sum('shares')), icon: 'fa-share', color: 'text-emerald-500', subColor: 'text-emerald-600', sub: 'Total bagikan' },
                        { label: 'Saves', value: formatNumber(sum('saves')), icon: 'fa-bookmark', color: 'text-amber-500', subColor: 'text-amber-600', sub: 'Total simpan' },
                    ], chips: _sChips(_sCnt(d, r => r.post_type)) };
                });
                const metaStoryPage = ref(1);
                const metaStoryTotalPages = computed(() => Math.max(1, Math.ceil(filteredMetaStory.value.length / PAGE_SIZE)));
                const pagedMetaStory = computed(() => filteredMetaStory.value.slice((metaStoryPage.value - 1) * PAGE_SIZE, metaStoryPage.value * PAGE_SIZE));
                watch([() => metaStorySearch.value, () => metaStoryDateFilter.value?.start, () => metaStoryDateFilter.value?.end], () => { metaStoryPage.value = 1; });
                const metaStoryTop = computed(() => [...filteredMetaStory.value].sort((a, b) => (Number(b.views) || 0) - (Number(a.views) || 0)).slice(0, 5));
                const metaFeedTop = computed(() => [...filteredMetaFeed.value].sort((a, b) => (Number(b.views) || 0) - (Number(a.views) || 0)).slice(0, 8));
                const _metaDaily = (rows, mode = 'total') => {
                    const m = {};
                    rows.forEach(r => {
                        const d = String(r.publish_time || '').slice(0, 10);
                        if (!d) return;
                        if (!m[d]) m[d] = { views: 0, reach: 0, count: 0 };
                        m[d].views += Number(r.views) || 0;
                        m[d].reach += Number(r.reach) || 0;
                        m[d].count += 1;
                    });
                    const days = Object.keys(m).sort();
                    return {
                        categories: days,
                        views: days.map(d => mode === 'average' ? Math.round(m[d].views / Math.max(1, m[d].count)) : m[d].views),
                        reach: days.map(d => mode === 'average' ? Math.round(m[d].reach / Math.max(1, m[d].count)) : m[d].reach),
                    };
                };
                const metaStoryDaily = computed(() => _metaDaily(filteredMetaStory.value, 'average'));
                const metaFeedDaily = computed(() => _metaDaily(filteredMetaFeed.value));
                const metaFeedTypeDist = computed(() => { const c = _sCnt(filteredMetaFeed.value, r => r.post_type); return { labels: Object.keys(c), series: Object.values(c) }; });
                const metaStoryActionsDist = computed(() => {
                    const d = filteredMetaStory.value;
                    return { labels: ['Navigation', 'Link Clicks', 'Profile Visits', 'Sticker Taps', 'Replies'], series: ['navigation', 'link_clicks', 'profile_visits', 'sticker_taps', 'replies'].map(f => _metaSum(d, f)) };
                });
                const _metaPct = (num, den) => `${den ? ((num / den) * 100).toFixed(1) : '0.0'}%`;
                const _metaDateObj = (value) => {
                    const dt = value ? new Date(value) : null;
                    return dt && !Number.isNaN(dt.getTime()) ? dt : null;
                };
                const _metaHourLabel = (hour) => hour == null ? '-' : `${String(hour).padStart(2, '0')}:00`;
                const _metaDayLabel = (date) => {
                    const dt = _metaDateObj(date);
                    return dt ? dt.toLocaleDateString('id-ID', { weekday: 'long' }) : '-';
                };
                const _metaBestBucket = (rows, keyBuilder, scoreBuilder) => {
                    const buckets = {};
                    rows.forEach((row) => {
                        const key = keyBuilder(row);
                        if (!key && key !== 0) return;
                        if (!buckets[key]) buckets[key] = { key, score: 0, count: 0 };
                        buckets[key].score += Number(scoreBuilder(row)) || 0;
                        buckets[key].count += 1;
                    });
                    return Object.values(buckets).sort((a, b) => ((b.count ? b.score / b.count : 0) - (a.count ? a.score / a.count : 0)))[0] || null;
                };
                const metaStoryInsights = computed(() => {
                    const rows = filteredMetaStory.value;
                    const views = _metaSum(rows, 'views');
                    const reach = _metaSum(rows, 'reach');
                    const bestHour = _metaBestBucket(rows, row => _metaDateObj(row.publish_time)?.getHours(), row => row.views);
                    const bestDay = _metaBestBucket(rows, row => _metaDateObj(row.publish_time)?.getDay(), row => row.reach);
                    const bestDayRow = bestDay ? rows.find(row => _metaDateObj(row.publish_time)?.getDay() === Number(bestDay.key)) : null;
                    return [
                        { label: 'CTR Link', value: _metaPct(_metaSum(rows, 'link_clicks'), views), detail: `${formatNumber(_metaSum(rows, 'link_clicks'))} klik dari ${formatNumber(views)} views` },
                        { label: 'Follow Rate', value: _metaPct(_metaSum(rows, 'follows'), reach), detail: `${formatNumber(_metaSum(rows, 'follows'))} follow dari ${formatNumber(reach)} reach` },
                        { label: 'Jam Terbaik', value: _metaHourLabel(bestHour ? bestHour.key : null), detail: bestHour ? `Rata-rata ${formatNumber(Math.round(bestHour.score / bestHour.count))} views per story` : 'Belum cukup data' },
                        { label: 'Hari Terkuat', value: bestDayRow ? _metaDayLabel(bestDayRow.publish_time) : '-', detail: bestDay ? `Rata-rata ${formatNumber(Math.round(bestDay.score / bestDay.count))} reach per story` : 'Belum cukup data' },
                        { label: 'Navigation Rate', value: _metaPct(_metaSum(rows, 'navigation'), views), detail: `${formatNumber(_metaSum(rows, 'navigation'))} aksi navigasi` },
                        { label: 'Profile Visit Rate', value: _metaPct(_metaSum(rows, 'profile_visits'), reach), detail: `${formatNumber(_metaSum(rows, 'profile_visits'))} kunjungan profil` },
                    ];
                });
                const metaFeedInsights = computed(() => {
                    const rows = filteredMetaFeed.value;
                    const views = _metaSum(rows, 'views');
                    const reach = _metaSum(rows, 'reach');
                    const bestType = _metaBestBucket(rows, row => String(row.post_type || '').trim().toUpperCase(), row => row.views);
                    const bestHour = _metaBestBucket(rows, row => _metaDateObj(row.publish_time)?.getHours(), row => row.reach);
                    return [
                        { label: 'Save Rate', value: _metaPct(_metaSum(rows, 'saves'), reach), detail: `${formatNumber(_metaSum(rows, 'saves'))} save dari ${formatNumber(reach)} reach` },
                        { label: 'Share Rate', value: _metaPct(_metaSum(rows, 'shares'), reach), detail: `${formatNumber(_metaSum(rows, 'shares'))} share dari ${formatNumber(reach)} reach` },
                        { label: 'Comment Rate', value: _metaPct(_metaSum(rows, 'comments'), reach), detail: `${formatNumber(_metaSum(rows, 'comments'))} komentar dari ${formatNumber(reach)} reach` },
                        { label: 'Best Content Type', value: bestType && bestType.key ? bestType.key : '-', detail: bestType ? `Rata-rata ${formatNumber(Math.round(bestType.score / bestType.count))} views per post` : 'Belum cukup data' },
                        { label: 'Jam Reach Tertinggi', value: _metaHourLabel(bestHour ? bestHour.key : null), detail: bestHour ? `Rata-rata ${formatNumber(Math.round(bestHour.score / bestHour.count))} reach per post` : 'Belum cukup data' },
                        { label: 'Follow Conversion', value: _metaPct(_metaSum(rows, 'follows'), views), detail: `${formatNumber(_metaSum(rows, 'follows'))} follow dari ${formatNumber(views)} views` },
                    ];
                });
                const metaFeedAccountLeaderboard = computed(() => {
                    const buckets = {};
                    filteredMetaFeed.value.forEach((row) => {
                        const account = String(row.account || '').trim();
                        if (!account) return;
                        if (!buckets[account]) buckets[account] = { account, posts: 0, views: 0, reach: 0 };
                        buckets[account].posts += 1;
                        buckets[account].views += Number(row.views) || 0;
                        buckets[account].reach += Number(row.reach) || 0;
                    });
                    return Object.values(buckets).sort((a, b) => b.views - a.views).slice(0, 5);
                });
                const _metaMonthlySummary = (rows, fields) => {
                    const buckets = {};
                    rows.forEach((row) => {
                        const month = String(row.publish_time || '').slice(0, 7);
                        if (!month) return;
                        const account = String(row.account || row.account_name || '-').trim() || '-';
                        const key = `${month}|${account}`;
                        if (!buckets[key]) {
                            buckets[key] = { month, account, posts: 0 };
                            fields.forEach((field) => { buckets[key][field] = 0; });
                        }
                        buckets[key].posts += 1;
                        fields.forEach((field) => {
                            buckets[key][field] += Number(row[field]) || 0;
                        });
                    });
                    return Object.values(buckets).sort((a, b) => `${b.month}|${b.account}`.localeCompare(`${a.month}|${a.account}`));
                };
                const metaStoryMonthlySummary = computed(() => _metaMonthlySummary(filteredMetaStory.value, ['views', 'reach', 'link_clicks', 'follows', 'navigation']).slice(0, 12));
                const metaFeedMonthlySummary = computed(() => _metaMonthlySummary(filteredMetaFeed.value, ['views', 'reach', 'likes', 'comments', 'shares', 'saves']).slice(0, 12));
                const metaShortDesc = (r) => { const s = String(r.description || '').replace(/\s+/g, ' ').trim(); return s ? (s.length > 60 ? s.slice(0, 60) + '...' : s) : (r.post_id || '-'); };

                // Meta IG: ApexCharts
                const _metaCharts = {};
                const _metaChartTokens = {};
                const _destroyChart = (id) => { if (_metaCharts[id]) { try { _metaCharts[id].destroy(); } catch (e) { } delete _metaCharts[id]; } };
                const _metaChartReady = (el) => {
                    if (!el || !el.isConnected) return false;
                    const rect = el.getBoundingClientRect();
                    return Number.isFinite(rect.width) && Number.isFinite(rect.height) && rect.width > 0 && rect.height > 0;
                };
                const _metaSafeSeries = (series = []) => series.map(v => {
                    const n = Number(v);
                    return Number.isFinite(n) ? n : 0;
                });
                const _areaOpts = (cats, viewsS, reachS) => ({
                    chart: { type: 'area', height: 260, parentHeightOffset: 0, toolbar: { show: false }, fontFamily: 'Inter, sans-serif', animations: { enabled: false } },
                    series: [{ name: 'Views', data: _metaSafeSeries(viewsS) }, { name: 'Reach', data: _metaSafeSeries(reachS) }],
                    xaxis: { categories: cats, labels: { rotate: -45, style: { fontSize: '9px' } }, tickAmount: 8 },
                    yaxis: { labels: { formatter: v => formatNumber(Math.round(v)), style: { fontSize: '10px' } } },
                    colors: ['#5066EB', '#10b981'], stroke: { curve: 'smooth', width: 2 }, fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
                    dataLabels: { enabled: false }, legend: { position: 'top', fontSize: '11px' }, grid: { borderColor: '#f1f5f9' },
                    tooltip: { y: { formatter: v => formatNumber(v) } }, noData: { text: 'Belum ada data' },
                });
                const _donutOpts = (labels, series) => ({
                    chart: { type: 'donut', height: 260, parentHeightOffset: 0, fontFamily: 'Inter, sans-serif', animations: { enabled: false } },
                    series: series.length ? _metaSafeSeries(series) : [], labels,
                    colors: ['#5066EB', '#10b981', '#f59e0b', '#f43f5e', '#8b5cf6', '#64748b'],
                    legend: { position: 'bottom', fontSize: '11px' }, dataLabels: { enabled: true, formatter: v => v.toFixed(0) + '%' },
                    plotOptions: { pie: { donut: { size: '62%' } } }, tooltip: { y: { formatter: v => formatNumber(v) } }, noData: { text: 'Belum ada data' },
                });
                const _metaChartOptionsWithDimensions = (el, options) => {
                    const rect = el.getBoundingClientRect();
                    const width = Math.max(320, Math.round(rect.width || el.clientWidth || 0));
                    const height = Math.max(260, Math.round(rect.height || el.clientHeight || 0));
                    return {
                        ...options,
                        chart: {
                            ...(options.chart || {}),
                            width,
                            height,
                        },
                    };
                };
                const _renderMetaChart = (id, el, options, attempt = 0) => {
                    const token = (_metaChartTokens[id] || 0) + 1;
                    _metaChartTokens[id] = token;
                    if (!_metaChartReady(el)) {
                        if (attempt >= 10) return;
                        window.requestAnimationFrame(() => {
                            if (_metaChartTokens[id] !== token) return;
                            _renderMetaChart(id, el, options, attempt + 1);
                        });
                        return;
                    }
                    if (_metaChartTokens[id] !== token) return;
                    _destroyChart(id);
                    _metaCharts[id] = new ApexCharts(el, _metaChartOptionsWithDimensions(el, options));
                    _metaCharts[id].render();
                };
                const renderMetaCharts = (dataset) => {
                    if (typeof ApexCharts === 'undefined') return;
                    nextTick(() => {
                        if (dataset === 'story') {
                            const dy = metaStoryDaily.value, el1 = document.querySelector('#meta-story-trend');
                            if (el1) _renderMetaChart('story-trend', el1, _areaOpts(dy.categories, dy.views, dy.reach));
                            const ac = metaStoryActionsDist.value, el2 = document.querySelector('#meta-story-actions');
                            if (el2) _renderMetaChart('story-actions', el2, _donutOpts(ac.labels, ac.series));
                        } else {
                            const dy = metaFeedDaily.value, el1 = document.querySelector('#meta-feed-trend');
                            if (el1) _renderMetaChart('feed-trend', el1, _areaOpts(dy.categories, dy.views, dy.reach));
                            const td = metaFeedTypeDist.value, el2 = document.querySelector('#meta-feed-type');
                            if (el2) _renderMetaChart('feed-type', el2, _donutOpts(td.labels, td.series));
                        }
                    });
                };
                watch([() => activeTab.value, filteredMetaStory, filteredMetaFeed], () => {
                    if (activeTab.value === 'meta_story') renderMetaCharts('story');
                    else if (activeTab.value === 'meta_feed') renderMetaCharts('feed');
                }, { flush: 'post' });

@endverbatim
