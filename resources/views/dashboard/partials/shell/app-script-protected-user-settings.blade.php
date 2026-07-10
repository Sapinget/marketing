@verbatim
                // Profile Form
                const profileForm = ref({
                    namaLengkap: "",
                    oldPin: "",
                    newPin: "",
                    confirmPin: ""
                });
                const authUsers = ref([]);
                const authUsersLoaded = ref(false);
                const activityLogs = ref([]);
                const activityLogsLoaded = ref(false);
                const activityLogFilters = ref({
                    table_name: "",
                    action: "",
                    record_key: ""
                });
                const submittingAuthUser = ref(false);
                const authUserForm = ref({
                    username: "",
                    nama: "",
                    email: "",
                    pin: "",
                    confirmPin: ""
                });

                // Nama Stock State
                const namaStockRows = ref([]);
                const namaStockSearchQuery = ref('');
                const namaStockKategoriFilter = ref('');
                const namaStockBrandFilter = ref('');
                const namaStockSaving = ref(false);
                const showNamaStockFormModal = ref(false);
                const namaStockFormMode = ref('create');
                const namaStockForm = ref({ ID: '', KATEGORI: '', BRAND: '', SERI: '' });
                const namaStockLoaded = ref(false);
                const filteredStories = computed(() => {
                    const wantGenap = storyTab.value === 'Genap';
                    return storyData.value.filter(s => {
                        const genap = s.is_genap === 1 || s.is_genap === true || s.is_genap === "1" || s.is_genap === "TRUE" || s.is_genap === "true" || s.is_genap === "Genap";
                        return wantGenap ? genap : !genap;
                    }).sort((a, b) => (a.Jam || "").localeCompare(b.Jam || ""));
                });

                const switchTab = (tab) => {
                    if (isTeknisi.value && !TEKNISI_TABS.has(tab)) {
                        showNotification("Akses dibatasi untuk role Teknisi", "warning");
                        return;
                    }
                    activeTab.value = tab;
                    localStorage.setItem("ppp_active_tab", tab);
                    history.replaceState(null, '', '#' + tab);
                    if (window.innerWidth < 768) {
                        isSidebarOpen.value = false;
                    }
                    // Calendar butuh story data (master plan & events sudah dimuat saat init).
                    if (tab === 'calendar' && storyData.value.length === 0) {
                        loadStoryData();
                    }
                };

                // Modal & Form States
                const modalOpen = ref(false);
                const modalType = ref("create");
                const settings = ref({});
                const settingsLoaded = ref(false);
                const settingsDraft = ref({});
                const settingsDirty = ref(false);
                const activeSettingTab = ref(null);
                const savingSettings = ref(false);
                const settingsSearchQuery = ref('');
                const settingsFilterMode = ref('all');
                const activeSettingValueSearch = ref('');
                const settingsDetailModalOpen = ref(false);
                const showSettingsBulkAdd = ref(false);
                const settingsBulkAddText = ref('');
                let settingsLoadPromise = null;
                const ideationDraftLabel = computed(() => (settings.value.Status || [])[0] || 'Draft');
                const ideationProgressLabel = 'In Progress';
                const ideationDoneLabel = 'Done';
                const createBoardPages = () => ({
                    [ideationDraftLabel.value]: 1,
                    [ideationProgressLabel]: 1,
                    [ideationDoneLabel]: 1
                });
                const masterForm = ref({
                    ID: null,
                    Judul: "",
                    Format_Konten: "",
                    Platforms: [],
                    Colab: [],
                    Editor: "",
                    Talent: [],
                    Status: "",
                    Tanggal_Rencana: todayStr(),
                    Skrip: "Tidak",
                    Caption: "Tidak",
                    Distribution_Meta: {},
                    Link_Drive: ""
                });

                // Search & Filters
                const masterSearch = ref("");
                const getInitialMasterFilter = () => {
                    const saved = localStorage.getItem("ppp_master_filter_range");
                    if (saved) {
                        try {
                            const parsed = JSON.parse(saved);
                            if (parsed && parsed.start) return parsed;
                        } catch (e) { }
                    }
                    return getDefaultDateRange();
                };
                const masterFilterRange = ref(getInitialMasterFilter());

                const saveFilterRange = () => {
                    localStorage.setItem("ppp_master_filter_range", JSON.stringify(masterFilterRange.value));
                };

                const filteredMasterPlanData = computed(() => {
                    return masterPlanData.value.filter((item) => {
                        let matchSearch = true;
                        if (masterSearch.value) {
                            const q = masterSearch.value.toLowerCase();
                            matchSearch = (item.Judul || "").toLowerCase().includes(q) ||
                                (item.Colab || "").toLowerCase().includes(q) ||
                                (item.Talent || "").toLowerCase().includes(q) ||
                                (item.Editor || "").toLowerCase().includes(q);
                        }

                        let matchDate = true;
                        if (activeTab.value !== 'ideation' && (masterFilterRange.value.start || masterFilterRange.value.end)) {
                            matchDate = isDateInRange(
                                item.Tanggal_Rencana,
                                masterFilterRange.value.start,
                                masterFilterRange.value.end || masterFilterRange.value.start
                            );
                        }

                        return matchSearch && matchDate;
                    }).sort((a, b) => (b.Tanggal_Rencana || "").localeCompare(a.Tanggal_Rencana || ""));
                });

                const kanbanBuckets = computed(() => {
                    const list = filteredMasterPlanData.value || [];
                    const ide = [];
                    const inProgress = [];
                    const done = [];
                    const draftStatus = ideationDraftLabel.value.toLowerCase();

                    list.forEach(item => {
                        const s = (item.Status || "").toLowerCase();
                        if (s === "" || s === draftStatus || s === "ide" || s === "scripting" || s === "not started") {
                            ide.push(item);
                        } else if (s === "shooting" || s === "editing" || s === "progres") {
                            inProgress.push(item);
                        } else if (s === "done" || s === "published") {
                            done.push(item);
                        }
                    });

                    return {
                        [ideationDraftLabel.value]: ide,
                        [ideationProgressLabel]: inProgress,
                        [ideationDoneLabel]: done
                    };
                });

                const filteredAnalyticsData = computed(() => {
                    const distMap = new Map();
                    const masterMap = new Map();
                    masterPlanData.value.forEach(m => {
                        const key = String(m.ID || '');
                        if (key && !masterMap.has(key)) masterMap.set(key, m);
                    });
                    distributionData.value.forEach(d => {
                        const key = String(d.Master_ID || '') + '|' + String(d.Platform || '').toLowerCase();
                        if (!distMap.has(key)) distMap.set(key, pickDateKey(d.Tanggal_Publish, d.Tanggal_Post, d.Tanggal, d.DisplayDate));
                    });
                    return analyticsData.value.map(item => {
                        const key = String(item.Master_ID || '') + '|' + String(item.Platform || '').toLowerCase();
                        const masterRow = masterMap.get(String(item.Master_ID || '')) || {};
                        const analyticsDate = pickDateKey(
                            distMap.get(key),
                            item.Tanggal_Publish,
                            item.Tanggal_Post,
                            item.Tanggal,
                            item.DisplayDate,
                            masterRow.Tanggal_Rencana
                        );
                        return { ...item, Tanggal_Publish: analyticsDate };
                    }).filter(item => {
                        const q = String(contentTableSearch.value || '').toLowerCase();
                        const matchSearch = String(item.Judul || '').toLowerCase().includes(q) || String(item.Platform || '').toLowerCase().includes(q);

                        const d = normalizeDateKey(item.Tanggal_Publish);
                        let matchDate = true;
                        if (commonDateFilter.value.start || commonDateFilter.value.end) {
                            matchDate = isDateInRange(
                                d,
                                commonDateFilter.value.start,
                                commonDateFilter.value.end || commonDateFilter.value.start
                            );
                        }
                        return matchSearch && matchDate;
                    }).sort((a, b) => (b.Tanggal_Publish || '').localeCompare(a.Tanggal_Publish || ''));
                });

                const filteredDistributionData = computed(() => {
                    return distributionData.value.filter(item => {
                        const q = contentTableSearch.value.toLowerCase();
                        const matchSearch = (item.Judul || "").toLowerCase().includes(q) || (item.Platform || "").toLowerCase().includes(q);

                        let matchDate = true;
                        if (commonDateFilter.value.start || commonDateFilter.value.end) {
                            matchDate = isDateInRange(
                                pickDateKey(item.Tanggal_Publish, item.Tanggal_Post, item.Tanggal, item.DisplayDate),
                                commonDateFilter.value.start,
                                commonDateFilter.value.end || commonDateFilter.value.start
                            );
                        }
                        return matchSearch && matchDate;
                    }).map(item => ({
                        ...item,
                        Tanggal_Publish: pickDateKey(item.Tanggal_Publish, item.Tanggal_Post, item.Tanggal, item.DisplayDate)
                    })).sort((a, b) => (b.Tanggal_Publish || "").localeCompare(a.Tanggal_Publish || ""));
                });

                const PAGE_SIZE = 15;
                const masterPage = ref(1);
                const distributionPage = ref(1);
                const analyticsPage = ref(1);

                const masterTotalPages = computed(() => Math.max(1, Math.ceil(filteredMasterPlanData.value.length / PAGE_SIZE)));
                const distributionTotalPages = computed(() => Math.max(1, Math.ceil(filteredDistributionData.value.length / PAGE_SIZE)));
                const analyticsTotalPages = computed(() => Math.max(1, Math.ceil(filteredAnalyticsData.value.length / PAGE_SIZE)));

                const pagedMasterPlanData = computed(() => filteredMasterPlanData.value.slice((masterPage.value - 1) * PAGE_SIZE, masterPage.value * PAGE_SIZE));
                const pagedDistributionData = computed(() => filteredDistributionData.value.slice((distributionPage.value - 1) * PAGE_SIZE, distributionPage.value * PAGE_SIZE));
                const pagedAnalyticsData = computed(() => filteredAnalyticsData.value.slice((analyticsPage.value - 1) * PAGE_SIZE, analyticsPage.value * PAGE_SIZE));

                const BOARD_COL_SIZE = 10;
                const boardPages = ref({});
                const pagedKanbanBuckets = computed(() => {
                    const b = kanbanBuckets.value;
                    const result = {};
                    for (const status of [ideationDraftLabel.value, ideationProgressLabel, ideationDoneLabel]) {
                        const items = b[status] || [];
                        const p = boardPages.value[status] || 1;
                        result[status] = items.slice((p - 1) * BOARD_COL_SIZE, p * BOARD_COL_SIZE);
                    }
                    return result;
                });

                watch(() => [masterSearch.value, masterFilterRange.value.start, masterFilterRange.value.end], () => { masterPage.value = 1; boardPages.value = createBoardPages(); });
                watch(() => [contentTableSearch.value, commonDateFilter.value.start, commonDateFilter.value.end], () => { distributionPage.value = 1; analyticsPage.value = 1; });
                watch(() => ideationDraftLabel.value, (label) => {
                    if (!ideationBoardMobileTab.value || !(label in kanbanBuckets.value)) {
                        ideationBoardMobileTab.value = label;
                    }
                    boardPages.value = createBoardPages();
                }, { immediate: true });
                watch(() => ideationViewMode.value, (mode) => {
                    if (mode === 'board') ideationBoardMobileTab.value = ideationDraftLabel.value;
                });
                watch(() => activeTab.value, (tab) => {
                    if (tab === 'ideation') ideationBoardMobileTab.value = ideationDraftLabel.value;
                });

                // Story pagination
                const storyPage = ref(1);
                const storyTotalPages = computed(() => Math.max(1, Math.ceil(filteredStories.value.length / PAGE_SIZE)));
                const pagedStories = computed(() => filteredStories.value.slice((storyPage.value - 1) * PAGE_SIZE, storyPage.value * PAGE_SIZE));
                watch(() => storyTab.value, () => { storyPage.value = 1; });

                // Unboxing filtered + pagination
                const filteredUnboxingData = computed(() => (unboxingData.value || []).filter(r => {
                    const q = (unboxingSearch.value || '').toLowerCase();
                    const matchSearch = !q || String(r.Nama || '').toLowerCase().includes(q);
                    const rangeEnd = commonDateFilter.value.end || commonDateFilter.value.start;
                    const matchDate = !commonDateFilter.value.start || isDateInRange(r.Upload_Date, commonDateFilter.value.start, rangeEnd);
                    return matchSearch && matchDate;
                }).sort((a, b) => (b.Upload_Date || '').localeCompare(a.Upload_Date || '')));
                const unboxingPage = ref(1);
                const unboxingTotalPages = computed(() => Math.max(1, Math.ceil(filteredUnboxingData.value.length / PAGE_SIZE)));
                const pagedUnboxingData = computed(() => filteredUnboxingData.value.slice((unboxingPage.value - 1) * PAGE_SIZE, unboxingPage.value * PAGE_SIZE));
                watch(() => [unboxingSearch.value, commonDateFilter.value.start, commonDateFilter.value.end], () => { unboxingPage.value = 1; });

                // Orderan Online filtered + pagination
                const filteredOrderanOnlineData = computed(() => (orderanOnlineData.value || []).filter(r => {
                    const q = (orderanOnlineSearch.value || '').toLowerCase();
                    const matchQ = !q || String(r.NAMA || '').toLowerCase().includes(q) || String(r.ECOMMERCE || '').toLowerCase().includes(q) || String(r['NO PESANAN'] || r.NO_PESANAN || '').toLowerCase().includes(q);
                    const rangeEnd = orderanOnlineDateRange.value.end || orderanOnlineDateRange.value.start;
                    const matchDate = !orderanOnlineDateRange.value.start || isDateInRange(r.TANGGAL, orderanOnlineDateRange.value.start, rangeEnd);
                    return matchQ && matchDate;
                }).sort((a, b) => (b.TANGGAL || '').localeCompare(a.TANGGAL || '')));
                const orderanPage = ref(1);
                const orderanTotalPages = computed(() => Math.max(1, Math.ceil(filteredOrderanOnlineData.value.length / PAGE_SIZE)));
                const pagedOrderanOnlineData = computed(() => filteredOrderanOnlineData.value.slice((orderanPage.value - 1) * PAGE_SIZE, orderanPage.value * PAGE_SIZE));
                watch(() => [orderanOnlineSearch.value, orderanOnlineDateRange.value.start, orderanOnlineDateRange.value.end], () => { orderanPage.value = 1; });

                watch(() => [orderanOnlineForm.value['HARGA ONLINE'], orderanOnlineForm.value['NOMINAL CAIR']], ([h, c]) => {
                    if (!orderanOnlineForm.value['ADMIN %'] && Number(h) > 0 && Number(c) >= 0) {
                        orderanOnlineForm.value['ADMIN %'] = ((Number(h) - Number(c)) / Number(h) * 100).toFixed(1) + '%';
                    }
                });

                // Unit Ditanya filtered + pagination
                const filteredUnitDitanyaData = computed(() => (unitDitanyaData.value || []).filter(r => {
                    const q = (unitDitanyaSearch.value || '').toLowerCase();
                    const matchQ = !q || String(r.BRAND || '').toLowerCase().includes(q) || String(r.SERI || '').toLowerCase().includes(q) || String(r.TIPE || r['TYPE UNIT'] || '').toLowerCase().includes(q);
                    const rangeEnd = unitDitanyaDateRange.value.end || unitDitanyaDateRange.value.start;
                    const matchDate = !unitDitanyaDateRange.value.start || isDateInRange(r.TANGGAL, unitDitanyaDateRange.value.start, rangeEnd);
                    const matchA = !unitDitanyaAvailableFilter.value || (r.AVAILABLE || '').toUpperCase() === unitDitanyaAvailableFilter.value;
                    return matchQ && matchDate && matchA;
                }).sort((a, b) => (b.TANGGAL || '').localeCompare(a.TANGGAL || '')));
                const unitDitanyaPage = ref(1);
                const unitDitanyaTotalPages = computed(() => Math.max(1, Math.ceil(filteredUnitDitanyaData.value.length / PAGE_SIZE)));
                const pagedUnitDitanyaData = computed(() => filteredUnitDitanyaData.value.slice((unitDitanyaPage.value - 1) * PAGE_SIZE, unitDitanyaPage.value * PAGE_SIZE));
                watch(() => [unitDitanyaSearch.value, unitDitanyaDateRange.value.start, unitDitanyaDateRange.value.end, unitDitanyaAvailableFilter.value], () => { unitDitanyaPage.value = 1; });

                // Claim Garansi filtered + pagination
                // Keep Barang
                const keepBarangData = ref([]);
                const keepBarangLoaded = ref(false);
                const keepBarangSearch = ref('');
                const keepBarangStatusFilter = ref('');
                const keepBarangHandleByFilter = ref('');
                const keepBarangModalOpen = ref(false);
                const keepBarangModalType = ref('create');
                const keepBarangForm = ref({});
                const keepBarangPage = ref(1);
                const buildStockNameLabel = (row = {}) => [row.KATEGORI, row.BRAND, row.SERI]
                    .map(part => String(part || '').trim())
                    .filter(Boolean)
                    .join(' ');
                const normalizeKeepBarangTypeHpValue = (value) => {
                    const raw = String(value || '').trim().toUpperCase();
                    if (!raw) return '';

                    const exactMasterMatch = namaStockRows.value.find(row => buildStockNameLabel(row).toUpperCase() === raw);
                    if (exactMasterMatch) return buildStockNameLabel(exactMasterMatch);

                    const seriMatches = namaStockRows.value.filter(row => String(row.SERI || '').trim().toUpperCase() === raw);
                    if (seriMatches.length === 1) return buildStockNameLabel(seriMatches[0]);

                    return String(value || '').trim();
                };

                const openKeepBarangModal = (type, row = null) => {
                    keepBarangModalType.value = type;
                    keepBarangForm.value = row ? { ...row, TYPE_HP: normalizeKeepBarangTypeHpValue(row.TYPE_HP) } : { TANGGAL_KEEP: todayStr(), RENCANA_PENGAMBILAN: '', NAMA: '', NOMOR_HP: '', NOMOR_HP_2: '', TYPE_HP: '', IMEI_FULL: '', DP_UANG_MUKA: 0, HARGA_JUAL: 0, HANDLE_BY: '', KASIR_BY: '', TEAM_GUDANG: '', DEADLINE_TEAM_GUDANG: '', STATUS: keepBarangStatusOptions.value[0] || '' };
                    keepBarangModalOpen.value = true;
                    if (!namaStockLoaded.value) loadNamaStockData();
                };

                const filteredKeepBarangData = computed(() => {
                    const q = (keepBarangSearch.value || '').toLowerCase();
                    return (keepBarangData.value || []).filter(r => {
                        const matchQ = !q || [r.NAMA, r.NOMOR_HP, r.NOMOR_HP_2, r.TYPE_HP, r.IMEI_FULL].some(v => String(v || '').toLowerCase().includes(q));
                        const matchS = !keepBarangStatusFilter.value || r.STATUS === keepBarangStatusFilter.value;
                        const matchH = !keepBarangHandleByFilter.value || r.HANDLE_BY === keepBarangHandleByFilter.value;
                        return matchQ && matchS && matchH;
                    }).sort((a, b) => (b.TANGGAL_KEEP || '').localeCompare(a.TANGGAL_KEEP || ''));
                });

                const keepBarangUniqueStatus = computed(() => [...new Set((keepBarangData.value || []).map(r => r.STATUS).filter(Boolean))].sort());
                const keepBarangUniqueHandleBy = computed(() => [...new Set((keepBarangData.value || []).map(r => r.HANDLE_BY).filter(Boolean))].sort());
                const keepBarangSummary = computed(() => {
                    const rows = keepBarangData.value || [];
                    return { total: rows.length, pending: rows.filter(r => r.STATUS === 'PENDING').length, done: rows.filter(r => r.STATUS === 'DONE').length, cancel: rows.filter(r => r.STATUS === 'CANCEL').length };
                });
                const keepBarangTotalPages = computed(() => Math.max(1, Math.ceil(filteredKeepBarangData.value.length / PAGE_SIZE)));
                const pagedKeepBarangData = computed(() => filteredKeepBarangData.value.slice((keepBarangPage.value - 1) * PAGE_SIZE, keepBarangPage.value * PAGE_SIZE));

                watch(() => [keepBarangSearch.value, keepBarangStatusFilter.value, keepBarangHandleByFilter.value], () => { keepBarangPage.value = 1; });

                const keepBarangStatusClass = (status) => {
                    if (status === 'PENDING') return 'bg-amber-100 text-amber-700';
                    if (status === 'DONE') return 'bg-emerald-100 text-emerald-700';
                    if (status === 'CANCEL') return 'bg-red-100 text-red-700';
                    return 'bg-slate-100 text-slate-600';
                };

                const keepBarangSisaHariClass = (val) => {
                    if (!val || val === '-') return 'text-slate-400';
                    const n = parseInt(val);
                    if (isNaN(n)) return 'text-slate-500';
                    if (n <= 0) return 'text-red-600 font-bold';
                    if (n <= 3) return 'text-amber-600 font-bold';
                    return 'text-slate-600';
                };

                const computeKeepBarangDerived = (form) => {
                    form['TYPE_HP'] = normalizeKeepBarangTypeHpValue(form['TYPE_HP']);
                    const tgl = form['TANGGAL_KEEP'];
                    if (tgl) {
                        try {
                            const d = new Date(tgl);
                            d.setDate(d.getDate() + 7);
                            form['TANGGAL_EXPIRED'] = d.toISOString().split('T')[0];
                            const batas = Math.ceil((d - new Date()) / 86400000);
                            form['BATAS_HARI_PENGAMBILAN'] = String(batas);
                        } catch (e) { }
                    }
                    const ambil = form['RENCANA_PENGAMBILAN'];
                    if (ambil && form['STATUS'] !== 'DONE' && form['STATUS'] !== 'CANCEL') {
                        try {
                            const sisa = Math.ceil((new Date(ambil) - new Date()) / 86400000);
                            form['SISA_HARI_PENGAMBILAN'] = String(sisa);
                        } catch (e) { }
                    } else if (form['STATUS'] === 'DONE') {
                        form['SISA_HARI_PENGAMBILAN'] = 'SUDAH DIAMBIL';
                    } else if (form['STATUS'] === 'CANCEL') {
                        form['SISA_HARI_PENGAMBILAN'] = 'BATAL';
                    }
                };

                const filteredClaimGaransiData = computed(() => {
                    const statusOrder = { "NOT STARTED": 1, "PENDING": 2, "PROCESED": 3, "CLAIM": 4 };
                    return (claimGaransiData.value || []).filter(r => {
                        const q = (claimGaransiSearch.value || '').toLowerCase();
                        const matchQ = !q || String(r.NAMA_CUSTOMER || '').toLowerCase().includes(q) || String(r.TIPE || '').toLowerCase().includes(q) || String(r.IMEI || '').toLowerCase().includes(q);
                        const matchS = !claimGaransiStatusFilter.value || r.STATUS === claimGaransiStatusFilter.value;
                        const matchG = !claimGaransiGaransiFilter.value || r.GARANSI === claimGaransiGaransiFilter.value;

                        // Custom logic: Hide if STATUS is CLAIM or REJECT and TANGGAL_DIAMBIL is filled, but only if no search/filter is active
                        const isFinishedAndPickedUp = (r.STATUS === 'CLAIM' || r.STATUS === 'REJECT') && r.TANGGAL_DIAMBIL;
                        const isFiltering = q || claimGaransiStatusFilter.value || claimGaransiGaransiFilter.value;
                        if (!isFiltering && isFinishedAndPickedUp) return false;

                        return matchQ && matchS && matchG;
                    }).sort((a, b) => {
                        const sA = statusOrder[a.STATUS] || 99;
                        const sB = statusOrder[b.STATUS] || 99;
                        if (sA !== sB) return sA - sB;
                        return (b.TANGGAL_MASUK || '').localeCompare(a.TANGGAL_MASUK || '');
                    });
                });
                const claimPage = ref(1);
                const claimTotalPages = computed(() => Math.max(1, Math.ceil(filteredClaimGaransiData.value.length / PAGE_SIZE)));
                const pagedClaimGaransiData = computed(() => filteredClaimGaransiData.value.slice((claimPage.value - 1) * PAGE_SIZE, claimPage.value * PAGE_SIZE));
                watch(() => [claimGaransiSearch.value, claimGaransiStatusFilter.value, claimGaransiGaransiFilter.value], () => { claimPage.value = 1; });

                const buildContentRanking = (mode) => {
                    const analytics = analyticsData.value;
                    const master = masterPlanData.value;
                    const dist = distributionData.value;
                    if (!analytics.length) return [];

                    const masterById = new Map(master.map(m => [String(m.ID || ''), m]));
                    const distByMaster = new Map();
                    dist.forEach(d => {
                        const key = String(d.Master_ID || '');
                        if (!key) return;
                        if (!distByMaster.has(key)) distByMaster.set(key, []);
                        distByMaster.get(key).push(d);
                    });

                    const start = commonDateFilter.value.start;
                    const end = commonDateFilter.value.end;

                    const grouped = {};
                    analytics.forEach(aRow => {
                        const masterId = String(aRow.Master_ID || '');
                        const mRow = masterById.get(masterId);
                        const distRows = distByMaster.get(masterId) || [];
                        const dRow = distRows.find(r => (r.Platform || '').toLowerCase() === (aRow.Platform || '').toLowerCase()) || distRows[0] || null;
                        const rawDate = (dRow?.Tanggal_Publish) || (aRow.Tanggal_Post) || (aRow.Tanggal) || '';
                        if (start && rawDate && rawDate < start) return;
                        if (end && rawDate && rawDate > end) return;
                        const platform = (aRow.Platform || 'Lainnya').trim();
                        if (!grouped[platform]) grouped[platform] = [];
                        const views = Number(aRow.Views || 0);
                        const likes = Number(aRow.Likes || 0);
                        const comments = Number(aRow.Comments || 0);
                        const shares = Number(aRow.Shares || 0);
                        const score = likes * 1 + comments * 3 + shares * 5;
                        grouped[platform].push({
                            title: mRow?.Judul || aRow.Judul || 'Untitled',
                            editor: mRow?.Editor || aRow.Editor || '-',
                            platform,
                            date: rawDate,
                            views, likes, comments, shares, score,
                            driveLink: mRow?.Link_Drive || ''
                        });
                    });

                    return Object.keys(grouped).sort().map(platform => {
                        const rows = grouped[platform].sort((a, b) =>
                            mode === 'top'
                                ? (b.views - a.views || b.score - a.score)
                                : (a.views - b.views || a.score - b.score)
                        ).filter(r => mode === 'low' ? (r.views > 0 || r.score > 0) : true).slice(0, 5);
                        return { platform, rows };
                    }).filter(g => g.rows.length > 0);
                };

                const topContentByPlatform = computed(() => buildContentRanking('top'));
                const topContentCombined = computed(() =>
                    topContentByPlatform.value.flatMap(g => g.rows)
                        .sort((a, b) => b.views - a.views || b.score - a.score).slice(0, 5)
                );
                const lowContentByPlatform = computed(() => buildContentRanking('low'));
                const lowContentCombined = computed(() =>
                    lowContentByPlatform.value.flatMap(g => g.rows)
                        .sort((a, b) => a.views - b.views || a.score - b.score).slice(0, 5)
                );

                // Insight and Tren
                const analisaInsightTab = ref('konten');

                const colabVsNonColabStats = computed(() => {
                    const analytics = analyticsData.value;
                    const master = masterPlanData.value;
                    if (!analytics.length) return null;
                    const masterById = new Map(master.map(m => [String(m.ID || ''), m]));
                    const start = commonDateFilter.value.start;
                    const end = commonDateFilter.value.end;
                    const groups = { colab: [], nonColab: [] };
                    analytics.forEach(aRow => {
                        const rawDate = aRow.Tanggal_Publish || aRow.Tanggal_Post || aRow.Tanggal || '';
                        if (start && rawDate && rawDate < start) return;
                        if (end && rawDate && rawDate > end) return;
                        const mRow = masterById.get(String(aRow.Master_ID || '')) || {};
                        const colab = (mRow.Colab || '').trim();
                        const isColab = colab && colab.toLowerCase() !== 'tidak';
                        const views = Number(aRow.Views || 0);
                        const likes = Number(aRow.Likes || 0);
                        const comments = Number(aRow.Comments || 0);
                        const shares = Number(aRow.Shares || 0);
                        const score = likes + comments * 3 + shares * 5;
                        (isColab ? groups.colab : groups.nonColab).push({ views, likes, comments, shares, score });
                    });
                    const summarize = (arr) => {
                        if (!arr.length) return { count: 0, avgViews: 0, avgLikes: 0, avgComments: 0, avgScore: 0 };
                        const n = arr.length;
                        return {
                            count: n,
                            avgViews: Math.round(arr.reduce((s, r) => s + r.views, 0) / n),
                            avgLikes: Math.round(arr.reduce((s, r) => s + r.likes, 0) / n),
                            avgComments: Math.round(arr.reduce((s, r) => s + r.comments, 0) / n),
                            avgScore: Math.round(arr.reduce((s, r) => s + r.score, 0) / n),
                        };
                    };
                    return { colab: summarize(groups.colab), nonColab: summarize(groups.nonColab) };
                });

                // Insight & Tren memakai date filter sendiri (default 26-25).
                const _insightInRange = (dateStr) => {
                    const f = insightDateFilter.value;
                    if (!f || !f.start) return true;
                    return isDateInRange(dateStr, f.start, f.end || f.start);
                };

                const contentFunnelStats = computed(() => {
                    const stageOrder = ['IDE', 'SHOOTING', 'EDITING', 'PROGRES', 'SCHEDULE', 'PUBLISHED', 'DONE'];
                    const counts = {};
                    stageOrder.forEach(s => { counts[s] = 0; });
                    masterPlanData.value.filter(_x => _insightInRange(_x.Tanggal_Rencana)).forEach(r => {
                        const s = (r.Status || 'IDE').toUpperCase();
                        if (counts[s] !== undefined) counts[s]++;
                        else counts['IDE']++;
                    });
                    const total = masterPlanData.value.length;
                    return stageOrder.map(stage => ({ stage, count: counts[stage], pct: total ? Math.round(counts[stage] / total * 100) : 0 }));
                });

                const monthlyTrendData = computed(() => {
                    const map = {};
                    analyticsData.value.filter(_x => _insightInRange(_x.Tanggal_Publish || _x.Tanggal_Post || _x.Tanggal)).forEach(aRow => {
                        const d = aRow.Tanggal_Publish || aRow.Tanggal_Post || aRow.Tanggal || '';
                        if (!d || d.length < 7) return;
                        const ym = d.substring(0, 7);
                        if (!map[ym]) map[ym] = { ym, views: 0, likes: 0, comments: 0, count: 0 };
                        map[ym].views += Number(aRow.Views || 0);
                        map[ym].likes += Number(aRow.Likes || 0);
                        map[ym].comments += Number(aRow.Comments || 0);
                        map[ym].count++;
                    });
                    const sorted = Object.values(map).sort((a, b) => a.ym.localeCompare(b.ym));
                    return sorted.slice(-12);
                });

                const productDemandStats = computed(() => {
                    const map = {};
                    unitDitanyaData.value.filter(_x => _insightInRange(_x.TANGGAL)).forEach(r => {
                        const key = `${r.BRAND || ''}||${r.SERI || ''}`;
                        if (!map[key]) map[key] = { brand: r.BRAND || '-', seri: r.SERI || '-', total: 0, available: 0, notAvailable: 0 };
                        const qty = Number(r.DITANYA || 1);
                        map[key].total += qty;
                        if ((r.AVAILABLE || '').toUpperCase().includes('TERSEDIA') && !(r.AVAILABLE || '').toUpperCase().includes('TIDAK')) map[key].available += qty;
                        else map[key].notAvailable += qty;
                    });
                    return Object.values(map).sort((a, b) => b.total - a.total).slice(0, 20);
                });

                const orderOnlineSummary = computed(() => {
                    const map = {};
                    const start = commonDateFilter.value.start;
                    const end = commonDateFilter.value.end;
                    orderanOnlineData.value.filter(_x => _insightInRange(_x.TANGGAL)).forEach(r => {
                        const d = r.TANGGAL || '';
                        if (start && d && d < start) return;
                        if (end && d && d > end) return;
                        const ec = r.ECOMMERCE || 'Lainnya';
                        if (!map[ec]) map[ec] = { platform: ec, total: 0, nominalCair: 0, hargaOnline: 0, adminPctSum: 0, adminPctCount: 0 };
                        map[ec].total++;
                        map[ec].nominalCair += Number(r['NOMINAL CAIR'] || r.NOMINAL_CAIR || 0);
                        map[ec].hargaOnline += Number(r['HARGA ONLINE'] || r.HARGA_ONLINE || 0);
                        const ho = Number(r['HARGA ONLINE'] || r.HARGA_ONLINE || 0);
                        const nc = Number(r['NOMINAL CAIR'] || r.NOMINAL_CAIR || 0);
                        if (ho > 0) { map[ec].adminPctSum += (ho - nc) / ho * 100; map[ec].adminPctCount++; }
                    });
                    return Object.values(map).sort((a, b) => b.total - a.total).map(e => ({
                        ...e, avgAdminPct: e.adminPctCount ? (e.adminPctSum / e.adminPctCount).toFixed(1) : '0.0'
                    }));
                });

                const claimGaransiStats = computed(() => {
                    const statusOrder = ['NOT STARTED', 'PENDING', 'PROCESED', 'CLAIM', 'DONE'];
                    const statusCount = {};
                    statusOrder.forEach(s => { statusCount[s] = 0; });
                    const produkMap = {};
                    const lokasiMap = {};
                    let totalDays = 0; let doneCount = 0;
                    claimGaransiData.value.filter(_x => _insightInRange(_x.TANGGAL_MASUK)).forEach(r => {
                        const s = (r.STATUS || 'PENDING').toUpperCase();
                        if (statusCount[s] !== undefined) statusCount[s]++;
                        const key = `${r.TIPE || ''}||${r.SERI || r.MODEL || ''}`;
                        if (!produkMap[key]) produkMap[key] = { label: `${r.TIPE || '-'} ${r.SERI || r.MODEL || ''}`.trim(), count: 0 };
                        produkMap[key].count++;
                        const lok = r.LOKASI_KLAIM || 'Tidak Diketahui';
                        if (!lokasiMap[lok]) lokasiMap[lok] = 0;
                        lokasiMap[lok]++;
                        if (s === 'DONE' && r.TANGGAL_MASUK && r.TANGGAL_DIAMBIL) {
                            const diff = (new Date(r.TANGGAL_DIAMBIL) - new Date(r.TANGGAL_MASUK)) / 86400000;
                            if (diff >= 0) { totalDays += diff; doneCount++; }
                        }
                    });
                    return {
                        statusCount,
                        topProduk: Object.values(produkMap).sort((a, b) => b.count - a.count).slice(0, 10),
                        topLokasi: Object.entries(lokasiMap).sort((a, b) => b[1] - a[1]).map(([l, c]) => ({ lokasi: l, count: c })),
                        avgDays: doneCount ? (totalDays / doneCount).toFixed(1) : null,
                        total: claimGaransiData.value.length,
                    };
                });
                // End Insight and Tren

                const openCreateModal = () => {
                    modalType.value = "create";
                    masterForm.value = {
                        ID: null,
                        Judul: "",
                        Format_Konten: "",
                        Colab: [],
                        Editor: "",
                        Talent: [],
                        Tanggal_Rencana: todayStr(),
                        Status: statusOptions.value[0] || '',
                        Platforms: [],
                        Skrip: "Tidak",
                        Caption: "Tidak",
                        Distribution_Meta: {},
                        Link_Drive: ""
                    };
                    modalOpen.value = true;
                };

                const openEditModal = (item) => {
                    modalType.value = "edit";

                    // Backward compat: Colab fallback from Collab or PIC - parse to array
                    const colabRaw = item.Colab || item.Collab || item.PIC || "";
                    const colab = Array.isArray(colabRaw)
                        ? colabRaw
                        : String(colabRaw).split(/[,;]/).map(s => s.trim()).filter(Boolean);
                    const talentRaw = item.Talent || "";
                    const talent = Array.isArray(talentRaw)
                        ? talentRaw.map(s => String(s).trim()).filter(Boolean)
                        : String(talentRaw).split(/[,;]/).map(s => s.trim()).filter(Boolean);

                    // Ensure Platforms is an array (split by comma or semicolon)
                    let platforms = [];
                    if (Array.isArray(item.Platforms)) {
                        platforms = item.Platforms.map(s => String(s).trim()).filter(Boolean);
                    } else if (item.Platforms && typeof item.Platforms === 'string') {
                        platforms = item.Platforms.split(/[;,]/).map(s => s.trim()).filter(Boolean);
                    }

                    // Resolve Distribution_Meta - fallback to Distribution_Details_JSON
                    let distMetaRaw = item.Distribution_Meta || item.Distribution_Details_JSON || {};
                    let distMeta = {};
                    try {
                        const parsed = typeof distMetaRaw === 'string' ? JSON.parse(distMetaRaw) : distMetaRaw;
                        distMeta = parsed && typeof parsed === 'object' ? JSON.parse(JSON.stringify(parsed)) : {};
                    } catch (e) {
                        distMeta = {};
                    }

                    // Ensure every platform has an entry in distMeta
                    platforms.forEach(p => {
                        if (!distMeta[p]) {
                            distMeta[p] = { link: '', type: 'Regular', date: '' };
                        }
                    });

                    masterForm.value = {
                        ID: item.ID,
                        Judul: item.Judul || "",
                        Format_Konten: item.Format_Konten || "",
                        Colab: colab,
                        Editor: item.Editor || "",
                        Talent: talent,
                        Tanggal_Rencana: item.Tanggal_Rencana || "",
                        Status: item.Status || statusOptions.value[0] || '',
                        Platforms: platforms,
                        Skrip: item.Skrip || "Tidak",
                        Caption: item.Caption || "Tidak",
                        Distribution_Meta: distMeta,
                        Link_Drive: item.Link_Drive || ""
                    };
                    modalOpen.value = true;
                };

                const normalizeMasterPlanRow = (item = {}) => {
                    const splitMultiValue = (value) => Array.isArray(value)
                        ? value.map(s => String(s).trim()).filter(Boolean)
                        : String(value || '').split(/[,;]/).map(s => s.trim()).filter(Boolean);
                    const colabRaw = item.Colab || item.Collab || item.PIC || "";
                    const colab = Array.isArray(colabRaw)
                        ? colabRaw
                        : String(colabRaw || '').split(/[,;]/).map(s => s.trim()).filter(Boolean).join(', ');
                    const talentList = splitMultiValue(item.Talent || '');

                    const platformList = Array.isArray(item.Platforms)
                        ? item.Platforms.map(s => String(s).trim()).filter(Boolean)
                        : String(item.Platforms || '').split(/[;,]/).map(s => s.trim()).filter(Boolean);

                    let distMeta = {};
                    try {
                        const raw = item.Distribution_Meta || item.Distribution_Details_JSON || {};
                        const parsed = typeof raw === 'string' ? JSON.parse(raw) : raw;
                        if (parsed && typeof parsed === 'object') {
                            Object.entries(parsed).forEach(([key, value]) => {
                                const normalizedKey = String(key || '').trim();
                                if (!normalizedKey || normalizedKey === 'contentType') return;
                                if (value && typeof value === 'object') {
                                    distMeta[normalizedKey] = {
                                        link: String(value.link || '').trim(),
                                        type: String(value.type || '').trim(),
                                        date: String(value.date || '').trim(),
                                    };
                                }
                            });
                        }
                    } catch (e) {
                        distMeta = {};
                    }

                    platformList.forEach((plat) => {
                        if (!distMeta[plat]) distMeta[plat] = { link: '', type: 'Regular', date: '' };
                    });

                    return {
                        ...item,
                        Colab: colab,
                        Talent: talentList.join(', '),
                        TalentList: talentList,
                        Platforms: platformList.join(', '),
                        Distribution_Meta: distMeta,
                        Link_Drive: String(item.Link_Drive || '').trim(),
                    };
                };

                const normalizeMasterPlanRows = (rows) => Array.isArray(rows) ? rows.map(normalizeMasterPlanRow) : [];

                const fetchMasterPlansFromDatabase = async () => {
                    if (!ensureRunApi().isWebProxy) {
                        return new Promise((resolve, reject) => {
                            ensureRunApi()
                                .withSuccessHandler((data) => resolve(normalizeMasterPlanRows(data)))
                                .withFailureHandler(reject)
                                .getMasterPlanData();
                        });
                    }

                    const response = await fetch(resolveAppUrl('/api/master-plans'), {
                        headers: { 'Accept': 'application/json' },
                    });
                    if (!response.ok) {
                        throw new Error(`Gagal memuat Master Konten (${response.status})`);
                    }
                    const payload = await response.json();
                    return normalizeMasterPlanRows(payload.data);
                };

                const loadMasterPlanData = async () => {
                    try {
                        masterPlanData.value = await fetchMasterPlansFromDatabase();
                        return;
                    } catch (error) {
                        return new Promise((resolve, reject) => {
                            ensureRunApi().withSuccessHandler((data) => {
                                masterPlanData.value = normalizeMasterPlanRows(data);
                                resolve();
                            }).withFailureHandler(reject).getMasterPlanData();
                        });
                    }
                };

                const saveMasterPlan = async () => {
                    if (submitting.value) return;
                    if (!masterForm.value.Judul || !masterForm.value.Editor) {
                        showNotification("Judul dan Editor wajib diisi");
                        return;
                    }

                    const isEdit = !!masterForm.value.ID;
                    submitting.value = true;

                    const formData = {
                        ID: masterForm.value.ID || null,
                        Judul: masterForm.value.Judul,
                        Format_Konten: masterForm.value.Format_Konten,
                        Platforms: Array.isArray(masterForm.value.Platforms) ? masterForm.value.Platforms.join(', ') : (masterForm.value.Platforms || ''),
                        Colab: Array.isArray(masterForm.value.Colab) ? masterForm.value.Colab.join(', ') : (masterForm.value.Colab || ''),
                        Editor: masterForm.value.Editor,
                        Talent: Array.isArray(masterForm.value.Talent) ? masterForm.value.Talent.join(', ') : (masterForm.value.Talent || ''),
                        Skrip: masterForm.value.Skrip || 'Tidak',
                        Caption: masterForm.value.Caption || 'Tidak',
                        Status: masterForm.value.Status || statusOptions.value[0] || '',
                        Tanggal_Rencana: masterForm.value.Tanggal_Rencana || '',
                        Distribution_Meta: JSON.stringify(masterForm.value.Distribution_Meta || {}),
                        Link_Drive: masterForm.value.Link_Drive || '',
                        _actor: currentUser.value?.username || '',
                    };

                    ensureRunApi()
                        .withSuccessHandler((response) => {
                            submitting.value = false;
                            const saved = response?.data || response;
                            if (saved && saved.ID) {
                                const normalizedSaved = normalizeMasterPlanRow(saved);
                                if (isEdit) {
                                    const idx = masterPlanData.value.findIndex(r => r.ID === saved.ID);
                                    if (idx !== -1) masterPlanData.value[idx] = { ...masterPlanData.value[idx], ...normalizedSaved };
                                } else {
                                    masterPlanData.value = [normalizedSaved, ...masterPlanData.value];
                                }
                            } else {
                                loadMasterPlanData();
                            }
                            modalOpen.value = false;
                            showNotification(isEdit ? "Berhasil memperbarui rencana" : "Berhasil menambah rencana");
                        })
                        .withFailureHandler(err => { submitting.value = false; handleError(err); })
                        .saveMasterPlan(formData);
                };


                const exportExcel = () => {
                    if (activeTab.value === 'unit_ditanya') { exportUnitDitanyaToExcel(); return; }
                    if (activeTab.value === 'claim_garansi_asuransi') { exportClaimGaransiToExcel(); return; }
                    if (activeTab.value === 'keep_barang') { exportKeepBarangToExcel(); return; }
                    if (activeTab.value === 'bonus_report') { exportBonusToExcel(); return; }
                    if (activeTab.value === 'sell_out') { exportSellOutToExcel(); return; }
                    if (activeTab.value === 'budgeting') { exportBudgetToExcel(); return; }
                    showNotification("Menyiapkan file Excel...");
                    const dataMap = {
                        'master': masterPlanData.value,
                        'ideation': masterPlanData.value,
                        'distribution': distributionData.value,
                        'analytics': analyticsData.value,
                        'unboxing': unboxingData.value,
                        'orderan_online': orderanOnlineData.value,
                        'top_content_platform': topContentCombined.value.map(r => ({ Platform: r.platform, Judul: r.title, Editor: r.editor, Views: r.views, Tanggal: formatShortDate(r.date) })),
                        'low_content_platform': lowContentCombined.value.map(r => ({ Platform: r.platform, Judul: r.title, Editor: r.editor, Views: r.views, Tanggal: formatShortDate(r.date) }))
                    };
                    const currentData = dataMap[activeTab.value] || [];
                    if (currentData.length === 0) {
                        showNotification("Tidak ada data untuk diekspor");
                        return;
                    }

                    ensureRunApi().withSuccessHandler((url) => {
                        if (url) {
                            const win = window.open(url, '_blank');
                            if (!win || win.closed) {
                                showNotification("Izinkan popup untuk mengunduh file Excel", 'warning');
                            } else {
                                showNotification("Ekspor berhasil dimulai");
                            }
                        }
                    }).exportToExcel(activeTab.value, currentData);
                };

                const formatShortDate = (dateStr) => {
                    if (!dateStr) return "-";
                    const date = new Date(dateStr);
                    return date.toLocaleDateString("id-ID", {
                        day: "2-digit",
                        month: "short",
                    });
                };

                const getStatusColor = (status) => {
                    const s = status?.toUpperCase();

                    // Claim Garansi Statuses
                    if (s === "NOT STARTED") return "bg-slate-100 text-slate-600 border border-slate-200";
                    if (s === "PENDING") return "bg-amber-100 text-amber-600 border border-amber-200";
                    if (s === "PROCESED" || s === "PROCESSED") return "bg-indigo-100 text-indigo-600 border border-indigo-200";
                    if (s === "CLAIM") return "bg-emerald-100 text-emerald-600 border border-emerald-200";

                    // LPJK Statuses
                    if (s === "DRAFT") return "bg-slate-100 text-slate-500";
                    if (s === "ONGOING") return "bg-amber-100 text-amber-700";
                    if (s === "SELESAI") return "bg-emerald-100 text-emerald-700";
                    if (s === "CANCEL") return "bg-rose-100 text-rose-500";

                    // Other module statuses
                    const sl = s?.toLowerCase();
                    if (sl === "editing" || sl === "progres") return "bg-blue-100 text-blue-600";
                    if (sl === "shooting") return "bg-amber-100 text-amber-600";
                    if (sl === "ide") return "bg-slate-100 text-slate-600";
                    if (sl === "done" || sl === "published") return "bg-emerald-100 text-emerald-600";

                    return "bg-slate-100 text-slate-600";
                };

                const loadAnalyticsData = async () => {
                    if (ensureRunApi().isWebProxy) {
                        try {
                            const response = await fetch(resolveAppUrl('/api/analytics'), {
                                headers: { 'Accept': 'application/json' },
                            });
                            if (!response.ok) throw new Error(`HTTP ${response.status}`);
                            const payload = await response.json();
                            analyticsData.value = Array.isArray(payload.data) ? payload.data : [];
                            return;
                        } catch (error) { }
                    }
                    return new Promise((resolve, reject) => {
                        ensureRunApi().withSuccessHandler((data) => {
                            analyticsData.value = Array.isArray(data) ? data : [];
                            resolve();
                        }).withFailureHandler(reject).getAnalyticsData();
                    });
                };

                const loadDistributionData = async () => {
                    if (ensureRunApi().isWebProxy) {
                        try {
                            const response = await fetch(resolveAppUrl('/api/distributions'), {
                                headers: { 'Accept': 'application/json' },
                            });
                            if (!response.ok) throw new Error(`HTTP ${response.status}`);
                            const payload = await response.json();
                            distributionData.value = Array.isArray(payload.data) ? payload.data : [];
                            return;
                        } catch (error) { }
                    }
                    return new Promise((resolve, reject) => {
                        ensureRunApi().withSuccessHandler((data) => {
                            distributionData.value = Array.isArray(data) ? data : [];
                            resolve();
                        }).withFailureHandler(reject).getDistributionData();
                    });
                };

                const openDistModal = (item = null) => {
                    if (item) {
                        distributionForm.value = { ...item };
                        modalType.value = "edit";
                    } else {
                        distributionForm.value = {
                            ID: null,
                            Master_ID: "",
                            Judul: "",
                            Platform: "Instagram",
                            Tanggal_Publish: todayStr(),
                            Link: ""
                        };
                        modalType.value = "create";
                    }
                    distModalOpen.value = true;
                };

                const saveDistribution = () => {
                    if (submitting.value) return;
                    if (!distributionForm.value.Judul) {
                        showNotification("Judul wajib diisi");
                        return;
                    }
                    const isEdit = !!distributionForm.value.ID;
                    submitting.value = true;
                    ensureRunApi()
                        .withSuccessHandler((response) => {
                            submitting.value = false;
                            distModalOpen.value = false;
                            const saved = response?.data || response;
                            if (saved && saved.ID) {
                                if (isEdit) {
                                    const idx = distributionData.value.findIndex(r => r.ID === saved.ID);
                                    if (idx !== -1) distributionData.value[idx] = { ...distributionData.value[idx], ...saved };
                                } else {
                                    distributionData.value = [saved, ...distributionData.value];
                                }
                            } else {
                                loadDistributionData();
                            }
                            showNotification("Berhasil menyimpan data distribusi");
                        })
                        .withFailureHandler(err => { submitting.value = false; handleError(err); })
                        .saveDistribution(distributionForm.value);
                };

                const deleteDistribution = (id) => {
                    showConfirm("Hapus Data?", "Yakin ingin menghapus data distribusi ini?", () => {
                        ensureRunApi()
                            .withSuccessHandler(() => {
                                distributionData.value = distributionData.value.filter(r => r.ID !== id);
                                showNotification("Data berhasil dihapus");
                            })
                            .withFailureHandler(handleError)
                            .deleteDistribution(id);
                    });
                };

                const openAnalyticsModal = (item = null) => {
                    if (item) {
                        analyticsForm.value = { ...item };
                        modalType.value = "edit";
                    } else {
                        analyticsForm.value = {
                            ID: null,
                            Master_ID: "",
                            Judul: "",
                            Platform: "Instagram",
                            Views: 0,
                            Likes: 0,
                            Comments: 0,
                            Shares: 0
                        };
                        modalType.value = "create";
                    }
                    analyticsModalOpen.value = true;
                };

                const saveAnalytics = () => {
                    if (submitting.value) return;
                    if (!analyticsForm.value.Judul) {
                        showNotification("Judul wajib diisi");
                        return;
                    }
                    const isEdit = !!analyticsForm.value.ID;
                    submitting.value = true;
                    ensureRunApi()
                        .withSuccessHandler((response) => {
                            submitting.value = false;
                            analyticsModalOpen.value = false;
                            const saved = response?.data || response;
                            if (saved && saved.ID) {
                                if (isEdit) {
                                    const idx = analyticsData.value.findIndex(r => r.ID === saved.ID);
                                    if (idx !== -1) analyticsData.value[idx] = { ...analyticsData.value[idx], ...saved };
                                } else {
                                    analyticsData.value = [saved, ...analyticsData.value];
                                }
                            } else {
                                loadAnalyticsData();
                            }
                            showNotification("Berhasil menyimpan data analitik");
                        })
                        .withFailureHandler(err => { submitting.value = false; handleError(err); })
                        .saveAnalytics(analyticsForm.value);
                };

                const deleteAnalytics = (id) => {
                    showConfirm("Hapus Data?", "Yakin ingin menghapus data analitik ini?", () => {
                        ensureRunApi()
                            .withSuccessHandler(() => {
                                analyticsData.value = analyticsData.value.filter(r => r.ID !== id);
                                showNotification("Data berhasil dihapus");
                            })
                            .withFailureHandler(handleError)
                            .deleteAnalytics(id);
                    });
                };

                const getKanbanItems = (statusGroup) => {
                    return kanbanBuckets.value[statusGroup] || [];
                };

                const getIdeaAgeLabel = (item) => {
                    const rawDate = String(item?.Tanggal_Rencana || '').trim();
                    if (!rawDate) return 'Umur -';
                    const source = new Date(rawDate);
                    if (Number.isNaN(source.getTime())) return 'Umur -';
                    const today = new Date();
                    source.setHours(0, 0, 0, 0);
                    today.setHours(0, 0, 0, 0);
                    const diffDays = Math.max(0, Math.floor((today.getTime() - source.getTime()) / 86400000));
                    return `Umur ${diffDays}h`;
                };

                const getIdeationTypeTone = (item) => {
                    const raw = String(item?.Format_Konten || item?.ContentType || item?.Status || "general").trim().toUpperCase();
                    const palettes = [
                        { chip: "bg-blue-500 text-white", card: "border-blue-500" },
                        { chip: "bg-emerald-500 text-white", card: "border-emerald-500" },
                        { chip: "bg-amber-500 text-white", card: "border-amber-500" },
                        { chip: "bg-violet-500 text-white", card: "border-violet-500" },
                        { chip: "bg-rose-500 text-white", card: "border-rose-500" }
                    ];
                    const fixedMap = { "AD": 0, "COLAB": 1, "PROMO": 2, "EDUKASI": 3, "STORY": 4 };
                    let idx = fixedMap[raw];
                    if (idx === undefined) idx = raw.length % palettes.length;
                    return palettes[idx];
                };

                const calculateScore = (row) => {
                    if (!row) return 0;
                    const likes = Number(row.Likes || 0);
                    const comments = Number(row.Comments || 0);
                    const shares = Number(row.Shares || 0);
                    const rawScore = likes * 1 + comments * 3 + shares * 5;
                    const finalScore = rawScore / 5000 * 100; // Simplified KPI target
                    return parseFloat(Math.min(finalScore, 100).toFixed(1));
                };

                const getVelocity = (row) => {
                    const score = calculateScore(row);
                    if (!row.Views || row.Views == 0) return { label: "New", class: "bg-slate-500", icon: "fas fa-clock" };
                    if (score >= 80) return { label: "Viral", class: "bg-red-500", icon: "fas fa-fire" };
                    if (score >= 50) return { label: "High", class: "bg-emerald-500", icon: "fas fa-arrow-up" };
                    if (score >= 20) return { label: "Avg", class: "bg-amber-500", icon: "fas fa-minus" };
                    return { label: "Low", class: "bg-violet-500", icon: "fas fa-arrow-down" };
                };



                const formatNumber = (value) => new Intl.NumberFormat("id-ID").format(Number(value || 0));

                const formatWaNumber = (phone) => { let s = String(phone || ''); if (s.startsWith('0')) s = s.slice(1); return s.replace(/[^0-9]/g, ''); };

                const calcAdminPct = (row) => {
                    if (row['ADMIN %'] || row.ADMIN_PCT) return row['ADMIN %'] || row.ADMIN_PCT;
                    const h = Number(row['HARGA ONLINE'] || row.HARGA_ONLINE || 0);
                    const c = Number(row['NOMINAL CAIR'] || row.NOMINAL_CAIR || 0);
                    if (h > 0 && c >= 0) return ((h - c) / h * 100).toFixed(1) + '%';
                    return '-';
                };
@endverbatim
@include('dashboard.partials.shell.app-script-notification-error-utils')
@verbatim
@endverbatim
@include('dashboard.partials.shell.app-script-shell-interaction-helpers')
@verbatim
                const loadAuthUsers = () => {
                    const runner = ensureRunApi();
                    if (runner.isWebProxy && !currentUser.value) {
                        authUsers.value = [];
                        authUsersLoaded.value = false;
                        return;
                    }

                    runner
                        .withSuccessHandler((rows) => {
                            authUsers.value = Array.isArray(rows) ? rows : [];
                            authUsersLoaded.value = true;
                        })
                        .withFailureHandler((err) => {
                            authUsers.value = [];
                            authUsersLoaded.value = false;
                            notifyError('Gagal memuat user', err, 'Daftar user belum berhasil dimuat.');
                        })
                        .getAuthUsers();
                };

                const loadActivityLogs = () => {
                    const runner = ensureRunApi();
                    if (runner.isWebProxy && !currentUser.value) {
                        activityLogs.value = [];
                        activityLogsLoaded.value = false;
                        return;
                    }

                    runner
                        .withSuccessHandler((rows) => {
                            activityLogs.value = Array.isArray(rows) ? rows : [];
                            activityLogsLoaded.value = true;
                        })
                        .withFailureHandler((err) => {
                            activityLogs.value = [];
                            activityLogsLoaded.value = false;
                            notifyError('Gagal memuat activity logs', err, 'Riwayat aktivitas belum berhasil dimuat.');
                        })
                        .getActivityLogs({
                            table_name: activityLogFilters.value.table_name || '',
                            action: activityLogFilters.value.action || '',
                            record_key: activityLogFilters.value.record_key || '',
                        });
                };

@endverbatim
