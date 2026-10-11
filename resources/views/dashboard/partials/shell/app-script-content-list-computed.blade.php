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

                const userLookupAliases = {
                    '@ogohogohdenpasar': 'ogoh-ogoh-denpasar',
                    'at-ogohogohdenpasar': 'ogoh-ogoh-denpasar',
                    '@arthadashing_': 'artha',
                    'at-arthadashing': 'artha',
                    '@depraz_': 'depraz',
                    'at-depraz': 'depraz',
                    '@sibliwira': 'wira',
                    'at-sibliwira': 'wira',
                };
                const normalizeUserLookupKey = (value) => {
                    const key = String(value || '').trim().toLowerCase();

                    return userLookupAliases[key] || key;
                };
                const userAvatarFailedKeys = ref(new Set());
                const userAvatarByLookupKey = computed(() => {
                    const lookup = new Map();
                    (Array.isArray(authUsers.value) ? authUsers.value : []).forEach((user) => {
                        const avatarUrl = user?.avatar_url || '';
                        if (!avatarUrl) return;
                        [
                            user?.username,
                            user?.nama,
                            user?.name,
                            user?.email,
                        ].forEach((value) => {
                            const key = normalizeUserLookupKey(value);
                            if (key && !lookup.has(key)) lookup.set(key, avatarUrl);
                        });
                    });

                    return lookup;
                });
                const resolveUserAvatarUrl = (value) => {
                    const key = normalizeUserLookupKey(value);
                    if (!key || userAvatarFailedKeys.value.has(key)) return '';

                    return userAvatarByLookupKey.value.get(key) || '';
                };
                const markMasterPlanEditorAvatarFailed = (value) => {
                    const key = normalizeUserLookupKey(value);
                    if (!key) return;
                    userAvatarFailedKeys.value = new Set([...userAvatarFailedKeys.value, key]);
                };
                const masterPersonInitials = (value) => {
                    const parts = String(value || '')
                        .trim()
                        .split(/\s+/)
                        .filter(Boolean);

                    if (!parts.length) return 'U';
                    if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();

                    return `${parts[0][0] || ''}${parts[parts.length - 1][0] || ''}`.toUpperCase();
                };
                const personDisplayName = (value) => {
                    const trimmed = String(value || '').trim();

                    return trimmed || '-';
                };
                const platformDisplayName = (value) => {
                    const trimmed = String(value || '').trim();

                    return trimmed || '-';
                };

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

                const pagedMasterPlanData = computed(() => filteredMasterPlanData.value.slice((masterPage.value - 1) * PAGE_SIZE, masterPage.value * PAGE_SIZE));

                const boardPages = ref({});

                watch(() => [masterSearch.value, masterFilterRange.value.start, masterFilterRange.value.end], () => { masterPage.value = 1; boardPages.value = createBoardPages(); });
                watch(() => [contentTableSearch.value, commonDateFilter.value.start, commonDateFilter.value.end], () => { distributionPage.value = 1; analyticsPage.value = 1; });

                // Story pagination
                const storyPage = ref(1);
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
                watch(() => [unitDitanyaSearch.value, unitDitanyaDateRange.value.start, unitDitanyaDateRange.value.end, unitDitanyaAvailableFilter.value], () => { unitDitanyaPage.value = 1; });

                // Service filtered + pagination
                const filteredServiceData = computed(() => (serviceData.value || []).filter(r => {
                    const q = (serviceSearch.value || '').toLowerCase();
                    const matchQ = !q || String(r.NAMA_CUSTOMER || r.nama_customer || r.NAMA || r.nama || '').toLowerCase().includes(q) || String(r.NO_SERVICE || r.no_service || '').toLowerCase().includes(q) || String(r.TYPE_UNIT || r.type_unit || r.TIPE || r.tipe || r.MODEL || r.model || '').toLowerCase().includes(q);
                    const rangeEnd = serviceDateRange.value.end || serviceDateRange.value.start;
                    const matchDate = !serviceDateRange.value.start || isDateInRange(r.TANGGAL || r.tanggal || r.TANGGAL_MASUK || r.tanggal_masuk, serviceDateRange.value.start, rangeEnd);
                    return matchQ && matchDate;
                }).sort((a, b) => (b.TANGGAL || b.TANGGAL_MASUK || '').localeCompare(a.TANGGAL || a.TANGGAL_MASUK || '')));
                const servicePage = ref(1);
                const serviceTotalPages = computed(() => Math.max(1, Math.ceil(filteredServiceData.value.length / PAGE_SIZE)));
                const pagedServiceData = computed(() => filteredServiceData.value.slice((servicePage.value - 1) * PAGE_SIZE, servicePage.value * PAGE_SIZE));
                watch(() => [serviceSearch.value, serviceDateRange.value.start, serviceDateRange.value.end], () => { servicePage.value = 1; });

                // Service claim filtered + pagination
                const filteredServiceClaimData = computed(() => (serviceClaimsData.value || []).filter(r => {
                    const q = (serviceClaimSearch.value || '').toLowerCase();
                    return !q || String(r.NAMA_CUSTOMER || '').toLowerCase().includes(q) || String(r.NO_SERVICE || '').toLowerCase().includes(q) || String(r.TIPE || '').toLowerCase().includes(q) || String(r.IMEI_SN || '').toLowerCase().includes(q);
                }).sort((a, b) => (b.TANGGAL_MASUK || '').localeCompare(a.TANGGAL_MASUK || '')));
                const serviceClaimPage = ref(1);
                const serviceClaimTotalPages = computed(() => Math.max(1, Math.ceil(filteredServiceClaimData.value.length / PAGE_SIZE)));
                const pagedServiceClaimData = computed(() => filteredServiceClaimData.value.slice((serviceClaimPage.value - 1) * PAGE_SIZE, serviceClaimPage.value * PAGE_SIZE));
                watch(() => serviceClaimSearch.value, () => { serviceClaimPage.value = 1; });

                // Claim Garansi filtered + pagination
                // Keep Barang
                const keepBarangData = ref([]);
                const keepBarangLoaded = ref(false);
                const keepBarangSearch = ref('');
                const keepBarangStatusFilter = ref('');
                const keepBarangHandleByFilter = ref('');
                const keepBarangModalOpen = ref(false);
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

                const filteredKeepBarangData = computed(() => {
                    const q = (keepBarangSearch.value || '').toLowerCase();
                    return (keepBarangData.value || []).filter(r => {
                        const matchQ = !q || [r.NAMA, r.NOMOR_HP, r.NOMOR_HP_2, r.TYPE_HP, r.IMEI_FULL].some(v => String(v || '').toLowerCase().includes(q));
                        const matchS = !keepBarangStatusFilter.value || r.STATUS === keepBarangStatusFilter.value;
                        const matchH = !keepBarangHandleByFilter.value || r.HANDLE_BY === keepBarangHandleByFilter.value;
                        return matchQ && matchS && matchH;
                    }).sort((a, b) => (b.TANGGAL_KEEP || '').localeCompare(a.TANGGAL_KEEP || ''));
                });

                watch(() => [keepBarangSearch.value, keepBarangStatusFilter.value, keepBarangHandleByFilter.value], () => { keepBarangPage.value = 1; });

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
                watch(() => [claimGaransiSearch.value, claimGaransiStatusFilter.value, claimGaransiGaransiFilter.value], () => { claimPage.value = 1; });
@endverbatim
