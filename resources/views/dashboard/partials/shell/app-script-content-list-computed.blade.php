@verbatim
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
@endverbatim
