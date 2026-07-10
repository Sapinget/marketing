@verbatim
                const hasBlockingOverlayOpen = computed(() => Boolean(
                    modalOpen.value ||
                    distModalOpen.value ||
                    analyticsModalOpen.value ||
                    storyModalOpen.value ||
                    unboxingModalOpen.value ||
                    orderanOnlineModalOpen.value ||
                    unitDitanyaModalOpen.value ||
                    claimGaransiModalOpen.value ||
                    keepBarangModalOpen.value ||
                    promoModalOpen.value ||
                    sellOutModalOpen.value ||
                    adsModalOpen.value ||
                    hargaKompetitorModalOpen.value ||
                    lpjkModalOpen.value ||
                    lpjkDetailModalOpen.value ||
                    showColabListModal.value ||
                    showNamaStockFormModal.value ||
                    settingsDetailModalOpen.value ||
                    calendarOpen.value ||
                    calendarDayModalOpen.value ||
                    confirmModal.value.open
                ));

                onMounted(async () => {
                    document.addEventListener("click", closeProfileMenu);
                    window.addEventListener("scroll", closeDropdownOnScroll, true);
                    window.addEventListener("resize", handleResize);
                    window.addEventListener("hashchange", handleHashChange);
                    tableSortObserver = new MutationObserver(() => {
                        hydrateSortableTableHeaders();
                    });
                    tableSortObserver.observe(document.getElementById('app'), { childList: true, subtree: true });
                    try {
                        const bootstrap = await new Promise((resolve, reject) => {
                            ensureRunApi().withSuccessHandler(resolve).withFailureHandler(reject).ensureDatabase();
                        });

                        if (bootstrap && bootstrap.user) {
                            currentUser.value = bootstrap.user;
                            localStorage.setItem("ppp_user", JSON.stringify(bootstrap.user));
                        } else if (ensureRunApi().isWebProxy) {
                            localStorage.removeItem("ppp_user");
                            currentUser.value = null;
                        }

                        if (currentUser.value) {
                            // Single RPC: ensureDatabase + getAllData in one server call.
                            // Cache-first: cached data displays instantly, fresh data replaces it.
                            await loadAllData(true);
                        }
                    } catch (error) {
                        handleError(error);
                    } finally {
                        authBootstrapPending.value = false;
                        resumeActiveTabAfterBootstrap();
                        if (window.innerWidth < 768) {
                            isSidebarOpen.value = false;
                        }
                        appLoading.value = false;
                        nextTick(() => hydrateSortableTableHeaders());
                    }
                });

                onBeforeUnmount(() => {
                    document.removeEventListener("click", closeProfileMenu);
                    window.removeEventListener("scroll", closeDropdownOnScroll, true);
                    window.removeEventListener("resize", handleResize);
                    window.removeEventListener("hashchange", handleHashChange);
                    tableSortObserver?.disconnect();
                    setDocumentScrollLock(false);
                });

                // Custom Dropdown & Calendar States
                const searchSelectOpen = ref(null);
                const searchSelectQuery = ref("");
                const formatOptions = computed(() => settings.value.Format_Konten || []);
                const statusOptions = computed(() => settings.value.Status || []);
                const editorOptions = computed(() => settings.value.Editor || []);
                const talentOptions = computed(() => settings.value.Talent || []);
                const platformOptions = computed(() => settings.value.Platforms || []);

                // CS dropdown options - unique from data merged with settings
                const mergeOptionValues = (...values) => [...new Set(values
                    .flat(Infinity)
                    .map(value => String(value || '').trim())
                    .filter(Boolean))]
                    .sort((a, b) => a.localeCompare(b, 'id', { sensitivity: 'base' }));

                const uniqueFrom = (arr, key, defaults = []) => {
                    const vals = [...new Set(arr.map(r => r[key]).filter(Boolean))];
                    const merged = [...new Set([...defaults, ...vals])];
                    return merged;
                };

                const uniqueMultiKeyFrom = (arr, keys = [], defaults = []) => mergeOptionValues(
                    defaults,
                    ...(keys || []).map((key) => (arr || []).map((row) => row?.[key]))
                );

                const orderanEcommerceOptions = computed(() => uniqueFrom(orderanOnlineData.value, 'ECOMMERCE', settings.value.Orderan_Online_Ecommerce || []));
                const orderanPengirimanOptions = computed(() => uniqueFrom(orderanOnlineData.value, 'PENGIRIMAN', settings.value.Orderan_Online_Pengiriman || []));
                const orderanStatusOptions = computed(() => uniqueFrom(orderanOnlineData.value, 'STATUS', settings.value.Orderan_Online_Status || []));
                const unboxingStatusOptions = computed(() => mergeOptionValues(
                    settings.value.Unboxing_Status || [],
                    settings.value.Status_Unboxing || [],
                    settings.value.Status || []
                ));
                const orderanHandleOptions = computed(() => uniqueMultiKeyFrom(
                    orderanOnlineData.value,
                    ['HANDLE'],
                    settings.value.Orderan_Online_Handle || []
                ));

                const filteredUniqueFrom = (arr, key, filters = {}, defaults = []) => {
                    let rows = arr;
                    Object.keys(filters).forEach(f => {
                        if (filters[f]) {
                            const filterVal = String(filters[f]).trim().toUpperCase();
                            rows = rows.filter(r => String(r[f] || '').trim().toUpperCase() === filterVal);
                        }
                    });
                    const vals = [...new Set(rows.map(r => r[key]).filter(Boolean))].sort();
                    return [...new Set([...defaults, ...vals])];
                };

                const getKategoriOptions = () => {
                    const ns = namaStockRows.value;
                    if (ns.length > 0) return filteredUniqueFrom(ns, 'KATEGORI');
                    return filteredUniqueFrom(unitDitanyaData.value, 'KATEGORI');
                };

                const getBrandOptions = (kat) => {
                    const ns = namaStockRows.value;
                    if (ns.length > 0) return filteredUniqueFrom(ns, 'BRAND', { KATEGORI: kat });
                    return filteredUniqueFrom(unitDitanyaData.value, 'BRAND', { KATEGORI: kat });
                };

                const getSeriOptions = (kat, brand) => {
                    const ns = namaStockRows.value;
                    if (ns.length > 0) return filteredUniqueFrom(ns, 'SERI', { KATEGORI: kat, BRAND: brand });
                    return filteredUniqueFrom(unitDitanyaData.value, 'SERI', { KATEGORI: kat, BRAND: brand });
                };

                const normalizeSettingOptions = (items = []) => [...new Set((Array.isArray(items) ? items : [])
                    .map(item => String(item || '').trim().toUpperCase())
                    .filter(Boolean))].sort();

                const resolveNamaStockSettingOptions = (keys = [], fallback = []) => {
                    for (const key of keys) {
                        const options = normalizeSettingOptions(settings.value?.[key] || []);
                        if (options.length > 0) return options;
                    }

                    return normalizeSettingOptions(fallback);
                };

                const unitKategoriOptions = computed(() => getKategoriOptions());
                const unitBrandOptions = computed(() => getBrandOptions(unitDitanyaForm.value['KATEGORI']));
                const nsSeriOptions = computed(() => getSeriOptions(unitDitanyaForm.value['KATEGORI'], unitDitanyaForm.value['BRAND']));
                const namaStockKategoriOptions = computed(() => resolveNamaStockSettingOptions(
                    ['Nama_Stock_Kategori', 'Kategori_Produk', 'Kategori'],
                    filteredUniqueFrom(namaStockRows.value, 'KATEGORI')
                ));
                const namaStockBrandOptions = computed(() => resolveNamaStockSettingOptions(
                    ['Nama_Stock_Brand', 'Brand_Produk', 'Brand'],
                    filteredUniqueFrom(namaStockRows.value, 'BRAND', { KATEGORI: namaStockForm.value.KATEGORI })
                ));
                const namaStockSeriOptions = computed(() => resolveNamaStockSettingOptions(
                    ['Nama_Stock_Seri', 'Seri_Produk', 'Seri'],
                    filteredUniqueFrom(namaStockRows.value, 'SERI', { KATEGORI: namaStockForm.value.KATEGORI, BRAND: namaStockForm.value.BRAND })
                ));

                const unitKondisiOptions = computed(() => resolveNamaStockSettingOptions(
                    ['Unit_Ditanya_Kondisi', 'Kondisi_Produk', 'Kondisi'],
                    filteredUniqueFrom(unitDitanyaData.value, 'KONDISI')
                ));
                const unitAvailableOptions = computed(() => uniqueFrom(unitDitanyaData.value, 'AVAILABLE', []));
                const unitRAMOptions = computed(() => resolveNamaStockSettingOptions(
                    ['Unit_Ditanya_RAM', 'RAM_Produk', 'RAM'],
                    filteredUniqueFrom(unitDitanyaData.value, 'RAM')
                ));
                const unitInternalOptions = computed(() => resolveNamaStockSettingOptions(
                    ['Unit_Ditanya_Internal', 'Internal_Produk', 'Internal'],
                    filteredUniqueFrom(unitDitanyaData.value, 'INTERNAL')
                ));
                const unitSizeOptions = computed(() => resolveNamaStockSettingOptions(
                    ['Unit_Ditanya_Size', 'Size_Produk', 'Size'],
                    filteredUniqueFrom(unitDitanyaData.value, 'SIZE')
                ));
                const sharedUnitTypeOptions = computed(() => {
                    const masterTypeOptions = uniqueMultiKeyFrom(namaStockRows.value, ['SERI']);
                    if (masterTypeOptions.length > 0) return mergeOptionValues(
                    settings.value.Tipe_Produk || [],
                    settings.value.Type_Unit || [],
                    masterTypeOptions
                );
                    return mergeOptionValues(
                    settings.value.Tipe_Produk || [],
                    settings.value.Type_Unit || [],
                    uniqueMultiKeyFrom(orderanOnlineData.value, ['TYPE UNIT', 'TYPE_UNIT']),
                    uniqueMultiKeyFrom(unitDitanyaData.value, ['TIPE', 'TYPE UNIT']),
                    uniqueMultiKeyFrom(claimGaransiData.value, ['TIPE', 'MODEL']),
                    uniqueMultiKeyFrom(keepBarangData.value, ['TYPE_HP'])
                );
                });
                const keepBarangTypeHpOptions = computed(() => {
                    const masterTypeOptions = namaStockRows.value
                        .map(row => buildStockNameLabel(row))
                        .filter(Boolean);
                    if (masterTypeOptions.length > 0) return mergeOptionValues(masterTypeOptions);
                    return mergeOptionValues(
                        uniqueMultiKeyFrom(keepBarangData.value, ['TYPE_HP'])
                    );
                });
                const claimSeriOptions = computed(() => mergeOptionValues(
                    settings.value.Seri_Produk || [],
                    uniqueMultiKeyFrom(claimGaransiData.value, ['SERI']),
                    uniqueMultiKeyFrom(unitDitanyaData.value, ['SERI']),
                    uniqueMultiKeyFrom(namaStockRows.value, ['SERI'])
                ));
                const keepBarangHandleByOptions = computed(() => resolveNamaStockSettingOptions(
                    ['Keep_Barang_Handle_By', 'Handle_By_Keep_Barang', 'Handle_By'],
                    mergeOptionValues(
                        uniqueMultiKeyFrom(keepBarangData.value, ['HANDLE_BY']),
                        uniqueMultiKeyFrom(orderanOnlineData.value, ['HANDLE'])
                    )
                ));
                const keepBarangKasirByOptions = computed(() => uniqueMultiKeyFrom(keepBarangData.value, ['KASIR_BY']));
                const keepBarangTeamGudangOptions = computed(() => uniqueMultiKeyFrom(keepBarangData.value, ['TEAM_GUDANG']));

                watch(() => namaStockForm.value.KATEGORI, () => {
                    if (!namaStockBrandOptions.value.includes(namaStockForm.value.BRAND)) {
                        namaStockForm.value.BRAND = '';
                    }
                });

                const claimLokasiOptions = computed(() => settings.value.Lokasi_Klaim || []);
                const claimStatusOptions = computed(() => uniqueFrom(
                    claimGaransiData.value,
                    'STATUS',
                    settings.value.Claim_Garansi_Status || settings.value.Status_Klaim || []
                ));
                const claimGaransiOptions = computed(() => settings.value.Tipe_Garansi || []);

                const calendarOpen = ref(false);
                const calendarDayModalOpen = ref(false);
                const calendarDayModalDate = ref("");
                const calendarDayModalItems = ref([]);
                const calendarMode = ref("form"); // 'form', 'filter', or 'published'
                const currentPlatForDate = ref("");
                const currentDateView = ref(new Date());
                const hoveredDate = ref("");
                const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                const keepBarangStatusOptions = computed(() => uniqueFrom(
                    keepBarangData.value,
                    'STATUS',
                    settings.value.Keep_Barang_Status || ['PENDING', 'DONE', 'CANCEL']
                ));

                const optionLabel = (options, value, fallback = 'Pilih') => {
                    const list = Array.isArray(options?.value) ? options.value : (Array.isArray(options) ? options : []);
                    const found = list.find((opt) => {
                        const optValue = opt && typeof opt === 'object' ? opt.value : opt;
                        return String(optValue) === String(value);
                    });
                    if (found === undefined) return fallback;
                    return found && typeof found === 'object' ? (found.label ?? found.value ?? fallback) : found;
                };

                const monthOptionLabel = (value, fallback = 'Pilih Bulan') => {
                    const index = Number(value) - 1;
                    return monthNames[index] || fallback;
                };

                const last12Months = computed(() => {
                    const months = [];
                    const now = new Date();
                    for (let i = 0; i < 12; i++) {
                        const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
                        const val = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
                        months.push({
                            value: val,
                            label: `${monthNames[d.getMonth()]} ${d.getFullYear()}`
                        });
                    }
                    return months;
                });

                const availableYears = computed(() => {
                    const y = new Date().getFullYear();
                    return [y + 1, y, y - 1, y - 2];
                });

                const formatMonthLabel = (val) => {
                    if (!val) return "";
                    const [y, m] = val.split("-").map(Number);
                    return `${monthNames[m - 1]} ${y}`;
                };

                const popoverPosition = ref({ top: 0, left: 0, width: 0, maxHeight: 320, placement: 'bottom' });
                const popoverStyle = computed(() => ({
                    position: 'fixed',
                    top: `${popoverPosition.value.top}px`,
                    left: `${popoverPosition.value.left}px`,
                    width: `${popoverPosition.value.width}px`,
                    maxWidth: `calc(100vw - 16px)`,
                    maxHeight: `${popoverPosition.value.maxHeight}px`,
                    transformOrigin: popoverPosition.value.placement === 'top' ? 'bottom center' : 'top center',
                    zIndex: 6000
                }));

                const resetPopoverLayout = (popoverEl) => {
                    if (!popoverEl) return;
                    popoverEl.classList.remove('search-select-popover--flip-up');
                    popoverEl.querySelectorAll('.search-select-popover__search').forEach((el) => el.classList.remove('search-select-popover__search'));
                    popoverEl.querySelectorAll('.search-select-popover__options').forEach((el) => el.classList.remove('search-select-popover__options'));
                };

                const applyPopoverLayout = (popoverEl, placement) => {
                    if (!popoverEl) return;
                    resetPopoverLayout(popoverEl);
                    if (placement !== 'top') return;

                    const searchInput = popoverEl.querySelector('.form-input-popover');
                    if (!searchInput) return;

                    const searchSection = searchInput.closest('div');
                    const optionSection = searchSection?.nextElementSibling;
                    if (!searchSection || !optionSection) return;

                    popoverEl.classList.add('search-select-popover--flip-up');
                    searchSection.classList.add('search-select-popover__search');
                    optionSection.classList.add('search-select-popover__options');
                };

                const positionActivePopover = (triggerEl) => {
                    if (!triggerEl) return;

                    nextTick(() => {
                        const popoverEl = document.querySelector('.search-select-popover');
                        if (!popoverEl) return;

                        resetPopoverLayout(popoverEl);

                        const rect = triggerEl.getBoundingClientRect();
                        const viewportHeight = window.innerHeight;
                        const viewportWidth = window.innerWidth;
                        const gap = 8;
                        const margin = 8;
                        const availableBelow = Math.max(120, viewportHeight - rect.bottom - gap - margin);
                        const availableAbove = Math.max(120, rect.top - gap - margin);
                        const naturalHeight = Math.max(popoverEl.scrollHeight, popoverEl.offsetHeight, 0);
                        const placement = naturalHeight > availableBelow && availableAbove > availableBelow ? 'top' : 'bottom';
                        const maxHeight = Math.max(120, placement === 'top' ? availableAbove : availableBelow);
                        const mobileWidth = viewportWidth < 768
                            ? Math.min(viewportWidth - (margin * 2), Math.max(rect.width, 320))
                            : Math.min(viewportWidth - (margin * 2), Math.max(rect.width, 240));
                        const measuredWidth = Math.max(180, mobileWidth, popoverEl.offsetWidth || 0);
                        const left = Math.min(
                            Math.max(margin, rect.left),
                            Math.max(margin, viewportWidth - measuredWidth - margin)
                        );
                        const top = placement === 'top'
                            ? Math.max(margin, rect.top - Math.min(naturalHeight, maxHeight) - gap)
                            : Math.min(viewportHeight - margin - Math.min(naturalHeight, maxHeight), rect.bottom + gap);

                        popoverPosition.value = {
                            top,
                            left,
                            width: measuredWidth,
                            maxHeight,
                            placement
                        };

                        applyPopoverLayout(popoverEl, placement);
                    });
                };

                const toggleSearchSelect = (event, type) => {
                    if (searchSelectOpen.value === type) {
                        searchSelectOpen.value = null;
                        clearPopoverTriggerState();
                    } else {
                        const rect = event.currentTarget.getBoundingClientRect();
                        popoverPosition.value = {
                            top: rect.bottom + 8,
                            left: rect.left,
                            width: rect.width,
                            maxHeight: Math.max(120, window.innerHeight - rect.bottom - 16),
                            placement: 'bottom'
                        };
                        searchSelectOpen.value = type;
                        searchSelectQuery.value = "";
                        markPopoverTriggerState(event.currentTarget);
                        positionActivePopover(event.currentTarget);
                    }
                };

                const filteredFormatOptions = computed(() => {
                    const q = (searchSelectQuery.value || '').toLowerCase();
                    return (formatOptions.value || []).filter((opt) => String(opt || '').toLowerCase().includes(q));
                });

                const filteredEditorOptions = computed(() => {
                    const q = (searchSelectQuery.value || '').toLowerCase();
                    return (editorOptions.value || []).filter((opt) => String(opt || '').toLowerCase().includes(q));
                });

                const filteredTalentOptions = computed(() => {
                    const q = (searchSelectQuery.value || '').toLowerCase();
                    return (talentOptions.value || []).filter((opt) => String(opt || '').toLowerCase().includes(q));
                });

                const filteredPlatformOptions = computed(() => {
                    const q = (searchSelectQuery.value || '').toLowerCase();
                    return (platformOptions.value || []).filter((opt) => String(opt || '').toLowerCase().includes(q));
                });

                const filteredColabOptions = computed(() => {
                    const opts = settings.value.Colab || [];
                    const q = (searchSelectQuery.value || '').toLowerCase();
                    return opts.filter(opt => String(opt || '').toLowerCase().includes(q));
                });

                const toggleColab = (opt) => {
                    if (!Array.isArray(masterForm.value.Colab)) masterForm.value.Colab = [];
                    const normalized = String(opt || '').trim();
                    if (!normalized) return;
                    const idx = masterForm.value.Colab.indexOf(normalized);
                    if (idx === -1) {
                        masterForm.value.Colab = [...masterForm.value.Colab, normalized];
                    } else {
                        const arr = [...masterForm.value.Colab];
                        arr.splice(idx, 1);
                        masterForm.value.Colab = arr;
                    }
                };

                const selectFormat = (opt) => {
                    masterForm.value.Format_Konten = opt;
                    searchSelectOpen.value = null;
                    clearPopoverTriggerState();
                };

                const selectStatus = (opt) => {
                    masterForm.value.Status = opt;
                    searchSelectOpen.value = null;
                    clearPopoverTriggerState();
                };

                const selectEditor = (opt) => {
                    masterForm.value.Editor = opt;
                    searchSelectOpen.value = null;
                    clearPopoverTriggerState();
                };

                const toggleTalent = (opt) => {
                    if (!Array.isArray(masterForm.value.Talent)) masterForm.value.Talent = [];
                    const normalized = String(opt || '').trim();
                    if (!normalized) return;
                    const idx = masterForm.value.Talent.indexOf(normalized);
                    if (idx === -1) {
                        masterForm.value.Talent = [...masterForm.value.Talent, normalized];
                    } else {
                        const nextTalent = [...masterForm.value.Talent];
                        nextTalent.splice(idx, 1);
                        masterForm.value.Talent = nextTalent;
                    }
                };

                const togglePlatform = (opt) => {
                    if (!Array.isArray(masterForm.value.Platforms)) {
                        masterForm.value.Platforms = [];
                    }
                    const normalizedOpt = String(opt || '').trim();
                    if (!normalizedOpt) return;

                    const idx = masterForm.value.Platforms.indexOf(normalizedOpt);
                    if (idx === -1) {
                        masterForm.value.Platforms = [...masterForm.value.Platforms, normalizedOpt];
                        // Ensure reactivity by replacing the object or ensuring the property exists
                        if (!masterForm.value.Distribution_Meta[normalizedOpt]) {
                            masterForm.value.Distribution_Meta = {
                                ...masterForm.value.Distribution_Meta,
                                [normalizedOpt]: { link: '', type: 'Regular', date: '' }
                            };
                        }
                    } else {
                        const newPlatforms = [...masterForm.value.Platforms];
                        newPlatforms.splice(idx, 1);
                        masterForm.value.Platforms = newPlatforms;
                    }
                };

                // Calendar Logic
                const getFilterRef = (ctx) => {
                    if (ctx === 'orderanOnline' || activeTab.value === 'orderan_online') return orderanOnlineDateRange;
                    if (ctx === 'unitDitanya' || activeTab.value === 'unit_ditanya') return unitDitanyaDateRange;
                    if (ctx === 'bonus') return bonusFilter;
                    if (ctx === 'hargaKompetitor') return hargaKompetitorDateFilter;
                    if (ctx === 'ads_log') return adsDateFilter;
                    if (ctx === 'budgeting') return budgetDateFilter;
                    if (ctx === 'insight' || activeTab.value === 'analisa_insight') return insightDateFilter;
                    if (ctx === 'metaStory' || activeTab.value === 'meta_story') return metaStoryDateFilter;
                    if (ctx === 'metaFeed' || activeTab.value === 'meta_feed') return metaFeedDateFilter;
                    if (activeTab.value === 'master' || activeTab.value === 'ideation') return masterFilterRange;
                    return commonDateFilter;
                };

                const openCalendar = (event, mode, plat = "", formContext = 'master', fieldName = '') => {
                    calendarMode.value = mode;
                    currentPlatForDate.value = plat;
                    calendarFormContext.value = formContext;
                    calendarFieldName.value = fieldName;
                    let initialDate = new Date();

                    if (mode === "form") {
                        if (formContext === 'master') {
                            initialDate = new Date(masterForm.value.Tanggal_Rencana);
                        } else if (formContext === 'story') {
                            initialDate = storyForm.value.Tanggal ? new Date(storyForm.value.Tanggal) : new Date();
                        } else if (formContext === 'orderanOnline1') {
                            initialDate = orderanOnlineForm.value['TANGGAL'] ? new Date(orderanOnlineForm.value['TANGGAL']) : new Date();
                        } else if (formContext === 'unitDitanya1') {
                            initialDate = unitDitanyaForm.value['TANGGAL'] ? new Date(unitDitanyaForm.value['TANGGAL']) : new Date();
                        } else if (formContext === 'claimGaransi1') {
                            initialDate = claimGaransiForm.value['TANGGAL_MASUK'] ? new Date(claimGaransiForm.value['TANGGAL_MASUK']) : new Date();
                        } else if (formContext === 'claimGaransi2') {
                            initialDate = claimGaransiForm.value['TANGGAL_DIAMBIL'] ? new Date(claimGaransiForm.value['TANGGAL_DIAMBIL']) : new Date();
                        } else if (formContext === 'claimGaransi3') {
                            initialDate = claimGaransiForm.value['TANGGAL_ESTIMASI'] ? new Date(claimGaransiForm.value['TANGGAL_ESTIMASI']) : new Date();
                        } else if (formContext === 'distribution') {
                            initialDate = distributionForm.value.Tanggal_Publish ? new Date(distributionForm.value.Tanggal_Publish) : new Date();
                        } else if (formContext === 'promoDate1') {
                            initialDate = promoTempDate.value.start ? new Date(promoTempDate.value.start) : new Date();
                        } else if (formContext === 'promoDate2') {
                            initialDate = promoTempDate.value.end ? new Date(promoTempDate.value.end) : new Date();
                        } else if (formContext === 'sotDate1') {
                            initialDate = sellOutForm.value.Periode_Start ? new Date(sellOutForm.value.Periode_Start) : new Date();
                        } else if (formContext === 'sotDate2') {
                            initialDate = sellOutForm.value.Periode_End ? new Date(sellOutForm.value.Periode_End) : new Date();
                        } else if (formContext === 'hargaKompetitorCek') {
                            initialDate = hargaKompetitorForm.value.Tanggal_Cek ? new Date(hargaKompetitorForm.value.Tanggal_Cek) : new Date();
                        } else if (formContext === 'lpjkTanggal') {
                            initialDate = lpjkForm.value.Tanggal ? new Date(lpjkForm.value.Tanggal) : new Date();
                        } else if (formContext === 'adsTanggal') {
                            initialDate = adsForm.value.Tanggal ? new Date(adsForm.value.Tanggal) : new Date();
                        } else if (formContext === 'keepBarangTanggalKeep') {
                            initialDate = keepBarangForm.value['TANGGAL_KEEP'] ? new Date(keepBarangForm.value['TANGGAL_KEEP']) : new Date();
                        } else if (formContext === 'keepBarangRencanaAmbil') {
                            initialDate = keepBarangForm.value['RENCANA_PENGAMBILAN'] ? new Date(keepBarangForm.value['RENCANA_PENGAMBILAN']) : new Date();
                        } else if (formContext === 'keepBarangDeadlineGudang') {
                            initialDate = keepBarangForm.value['DEADLINE_TEAM_GUDANG'] ? new Date(keepBarangForm.value['DEADLINE_TEAM_GUDANG']) : new Date();
                        } else if (formContext === 'unboxingUploadDate') {
                            initialDate = unboxingForm.value.Upload_Date ? new Date(unboxingForm.value.Upload_Date) : new Date();
                        }
                    } else if (mode === "published" && plat && masterForm.value.Distribution_Meta[plat]) {
                        initialDate = masterForm.value.Distribution_Meta[plat].date ? new Date(masterForm.value.Distribution_Meta[plat].date) : new Date();
                    } else if (mode === 'filter') {
                        const targetFilter = getFilterRef(formContext);
                        initialDate = targetFilter.value.start ? new Date(targetFilter.value.start) : new Date();
                    }

                    currentDateView.value = isNaN(initialDate.getTime()) ? new Date() : initialDate;
                    calendarOpen.value = true;
                };

                const resetCalendar = () => {
                    const ctx = calendarFormContext.value;
                    if (calendarMode.value === 'filter') {
                        getFilterRef(ctx).value = { start: '', end: '' };
                    } else if (calendarMode.value === 'form') {
                        if (ctx === 'master') masterForm.value.Tanggal_Rencana = '';
                        else if (ctx === 'story') storyForm.value.Tanggal = '';
                        else if (ctx === 'orderanOnline1') orderanOnlineForm.value['TANGGAL'] = '';
                        else if (ctx === 'unitDitanya1') unitDitanyaForm.value['TANGGAL'] = '';
                        else if (ctx === 'claimGaransi1') claimGaransiForm.value['TANGGAL_MASUK'] = '';
                        else if (ctx === 'claimGaransi2') claimGaransiForm.value['TANGGAL_DIAMBIL'] = '';
                        else if (ctx === 'claimGaransi3') claimGaransiForm.value['TANGGAL_ESTIMASI'] = '';
                        else if (ctx === 'distribution') distributionForm.value.Tanggal_Publish = '';
                        else if (ctx === 'analytics') analyticsForm.value.Tanggal_Publish = '';
                        else if (ctx === 'promoDate1') { promoTempDate.value.start = ''; syncPromoPerideText(); }
                        else if (ctx === 'promoDate2') { promoTempDate.value.end = ''; syncPromoPerideText(); }
                        else if (ctx === 'sotDate1') sellOutForm.value.Periode_Start = '';
                        else if (ctx === 'sotDate2') sellOutForm.value.Periode_End = '';
                        else if (ctx === 'hargaKompetitorCek') hargaKompetitorForm.value.Tanggal_Cek = '';
                        else if (ctx === 'lpjkTanggal') lpjkForm.value.Tanggal = '';
                        else if (ctx === 'adsTanggal') adsForm.value.Tanggal = '';
                        else if (ctx === 'keepBarangTanggalKeep') keepBarangForm.value['TANGGAL_KEEP'] = '';
                        else if (ctx === 'keepBarangRencanaAmbil') keepBarangForm.value['RENCANA_PENGAMBILAN'] = '';
                        else if (ctx === 'keepBarangDeadlineGudang') keepBarangForm.value['DEADLINE_TEAM_GUDANG'] = '';
                        else if (ctx === 'unboxingUploadDate') unboxingForm.value.Upload_Date = '';
                    } else if (calendarMode.value === 'published') {
                        if (currentPlatForDate.value && masterForm.value.Distribution_Meta[currentPlatForDate.value]) {
                            masterForm.value.Distribution_Meta[currentPlatForDate.value].date = '';
                        }
                    }
                    calendarOpen.value = false;
                };

                const getPlatformIcon = (plat) => {
                    const map = {
                        'Instagram': 'fa-brands fa-instagram',
                        'TikTok': 'fa-brands fa-tiktok',
                        'YouTube': 'fa-brands fa-youtube',
                        'Facebook': 'fa-brands fa-facebook',
                        'X': 'fa-brands fa-x-twitter',
                        'Threads': 'fa-brands fa-threads'
                    };
                    return map[plat] || 'fa-solid fa-link';
                };

                const hasAnyLink = (item) => {
                    if (!item.Distribution_Meta || typeof item.Distribution_Meta !== 'object') return false;
                    return Object.values(item.Distribution_Meta).some(d => d && typeof d === 'object' && d.link && typeof d.link === 'string');
                };
                const hasAnyMasterLink = (item) => hasAnyLink(item) || !!String(item?.Link_Drive || '').trim();

                const formatFullDate = (dateStr) => {
                    if (!dateStr) return "-";
                    const date = new Date(dateStr);
                    if (isNaN(date.getTime())) return "-";
                    return date.toLocaleDateString("id-ID", {
                        day: "numeric",
                        month: "long",
                        year: "numeric"
                    });
                };

                const calendarTargetDate = computed(() => {
                    if (calendarMode.value === "form") return masterForm.value.Tanggal_Rencana;
                    if (calendarMode.value === "published" && currentPlatForDate.value) return masterForm.value.Distribution_Meta[currentPlatForDate.value].date;
                    return getFilterRef(calendarFormContext.value).value.start;
                });

                const calendarDaysInMonth = computed(() => {
                    const year = currentDateView.value.getFullYear();
                    const month = currentDateView.value.getMonth();
                    return new Date(year, month + 1, 0).getDate();
                });

                const calendarEmptyDays = computed(() => {
                    const year = currentDateView.value.getFullYear();
                    const month = currentDateView.value.getMonth();
                    let firstDay = new Date(year, month, 1).getDay();
                    return firstDay === 0 ? 6 : firstDay - 1;
                });

                const selectDate = (day) => {
                    const year = currentDateView.value.getFullYear();
                    const month = currentDateView.value.getMonth();
                    const dateStr = `${year}-${String(month + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;

                    if (calendarMode.value === "form") {
                        if (calendarFormContext.value === 'master') {
                            masterForm.value.Tanggal_Rencana = dateStr;
                        } else if (calendarFormContext.value === 'story') {
                            storyForm.value.Tanggal = dateStr;
                        } else if (calendarFormContext.value === 'orderanOnline1') {
                            orderanOnlineForm.value['TANGGAL'] = dateStr;
                        } else if (calendarFormContext.value === 'unitDitanya1') {
                            unitDitanyaForm.value['TANGGAL'] = dateStr;
                        } else if (calendarFormContext.value === 'claimGaransi1') {
                            claimGaransiForm.value['TANGGAL_MASUK'] = dateStr;
                        } else if (calendarFormContext.value === 'claimGaransi2') {
                            claimGaransiForm.value['TANGGAL_DIAMBIL'] = dateStr;
                        } else if (calendarFormContext.value === 'claimGaransi3') {
                            claimGaransiForm.value['TANGGAL_ESTIMASI'] = dateStr;
                        } else if (calendarFormContext.value === 'distribution') {
                            distributionForm.value.Tanggal_Publish = dateStr;
                        } else if (calendarFormContext.value === 'analytics') {
                            analyticsForm.value.Tanggal_Publish = dateStr;
                        } else if (calendarFormContext.value === 'promoDate1') {
                            promoTempDate.value.start = dateStr;
                            promoPeriodePreset.value = 'custom';
                            syncPromoPerideText();
                        } else if (calendarFormContext.value === 'promoDate2') {
                            promoTempDate.value.end = dateStr;
                            promoPeriodePreset.value = 'custom';
                            syncPromoPerideText();
                        } else if (calendarFormContext.value === 'sotDate1') {
                            sellOutForm.value.Periode_Start = dateStr;
                        } else if (calendarFormContext.value === 'sotDate2') {
                            sellOutForm.value.Periode_End = dateStr;
                        } else if (calendarFormContext.value === 'hargaKompetitorCek') {
                            hargaKompetitorForm.value.Tanggal_Cek = dateStr;
                        } else if (calendarFormContext.value === 'lpjkTanggal') {
                            lpjkForm.value.Tanggal = dateStr;
                        } else if (calendarFormContext.value === 'adsTanggal') {
                            adsForm.value.Tanggal = dateStr;
                        } else if (calendarFormContext.value === 'keepBarangTanggalKeep') {
                            keepBarangForm.value['TANGGAL_KEEP'] = dateStr;
                        } else if (calendarFormContext.value === 'keepBarangRencanaAmbil') {
                            keepBarangForm.value['RENCANA_PENGAMBILAN'] = dateStr;
                        } else if (calendarFormContext.value === 'keepBarangDeadlineGudang') {
                            keepBarangForm.value['DEADLINE_TEAM_GUDANG'] = dateStr;
                        } else if (calendarFormContext.value === 'unboxingUploadDate') {
                            unboxingForm.value.Upload_Date = dateStr;
                        }
                        calendarOpen.value = false;
                    } else if (calendarMode.value === "published") {
                        if (currentPlatForDate.value && masterForm.value.Distribution_Meta[currentPlatForDate.value]) {
                            masterForm.value.Distribution_Meta[currentPlatForDate.value].date = dateStr;
                        }
                    } else {
                        const targetFilter = getFilterRef(calendarFormContext.value);

                        if (!targetFilter.value.start || (targetFilter.value.start && targetFilter.value.end)) {
                            targetFilter.value.start = dateStr;
                            targetFilter.value.end = "";
                        } else {
                            if (dateStr < targetFilter.value.start) {
                                targetFilter.value.end = targetFilter.value.start;
                                targetFilter.value.start = dateStr;
                            } else {
                                targetFilter.value.end = dateStr;
                            }
                            hoveredDate.value = "";
                            calendarOpen.value = false;
                        }
                        if (activeTab.value === 'master' || activeTab.value === 'ideation') saveFilterRange();
                    }
                };

                const isSelectedDate = (day) => {
                    const year = currentDateView.value.getFullYear();
                    const month = currentDateView.value.getMonth();
                    const dateStr = `${year}-${String(month + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;

                    if (calendarMode.value === "form") {
                        if (calendarFormContext.value === 'master') {
                            return masterForm.value.Tanggal_Rencana === dateStr;
                        } else if (calendarFormContext.value === 'story') {
                            return storyForm.value.Tanggal === dateStr;
                        } else if (calendarFormContext.value === 'orderanOnline1') {
                            return orderanOnlineForm.value['TANGGAL'] === dateStr;
                        } else if (calendarFormContext.value === 'unitDitanya1') {
                            return unitDitanyaForm.value['TANGGAL'] === dateStr;
                        } else if (calendarFormContext.value === 'claimGaransi1') {
                            return claimGaransiForm.value['TANGGAL_MASUK'] === dateStr;
                        } else if (calendarFormContext.value === 'claimGaransi2') {
                            return claimGaransiForm.value['TANGGAL_DIAMBIL'] === dateStr;
                        } else if (calendarFormContext.value === 'claimGaransi3') {
                            return claimGaransiForm.value['TANGGAL_ESTIMASI'] === dateStr;
                        } else if (calendarFormContext.value === 'distribution') {
                            return distributionForm.value.Tanggal_Publish === dateStr;
                        } else if (calendarFormContext.value === 'analytics') {
                            return analyticsForm.value.Tanggal_Publish === dateStr;
                        } else if (calendarFormContext.value === 'hargaKompetitorCek') {
                            return hargaKompetitorForm.value.Tanggal_Cek === dateStr;
                        } else if (calendarFormContext.value === 'lpjkTanggal') {
                            return lpjkForm.value.Tanggal === dateStr;
                        } else if (calendarFormContext.value === 'adsTanggal') {
                            return adsForm.value.Tanggal === dateStr;
                        } else if (calendarFormContext.value === 'keepBarangTanggalKeep') {
                            return keepBarangForm.value['TANGGAL_KEEP'] === dateStr;
                        } else if (calendarFormContext.value === 'keepBarangRencanaAmbil') {
                            return keepBarangForm.value['RENCANA_PENGAMBILAN'] === dateStr;
                        } else if (calendarFormContext.value === 'keepBarangDeadlineGudang') {
                            return keepBarangForm.value['DEADLINE_TEAM_GUDANG'] === dateStr;
                        } else if (calendarFormContext.value === 'unboxingUploadDate') {
                            return unboxingForm.value.Upload_Date === dateStr;
                        }
                        return false;
                    } else {
                        const { start, end } = getFilterRef(calendarFormContext.value).value;
                        return start === dateStr || end === dateStr;
                    }
                };

                const isInRange = (day) => {
                    if (calendarMode.value !== 'filter') return false;
                    const { start, end } = getFilterRef(calendarFormContext.value).value;
                    const year = currentDateView.value.getFullYear();
                    const month = currentDateView.value.getMonth();
                    const dateStr = `${year}-${String(month + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;

                    if (start && end) {
                        return dateStr > start && dateStr < end;
                    }
                    if (start && hoveredDate.value) {
                        const s = start < hoveredDate.value ? start : hoveredDate.value;
                        const e = start < hoveredDate.value ? hoveredDate.value : start;
                        return dateStr > s && dateStr < e;
                    }
                    return false;
                };

                const isToday = (day) => {
                    const today = new Date();
                    return today.getDate() === day && today.getMonth() === currentDateView.value.getMonth() && today.getFullYear() === currentDateView.value.getFullYear();
                };

                const isStartDate = (day) => {
                    const year = currentDateView.value.getFullYear();
                    const month = currentDateView.value.getMonth();
                    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                    if (calendarMode.value === 'filter') {
                        return dateStr === getFilterRef(calendarFormContext.value).value.start;
                    }
                    return dateStr === calendarTargetDate.value;
                };

                const isEndDate = (day) => {
                    if (calendarMode.value !== 'filter') return false;
                    const year = currentDateView.value.getFullYear();
                    const month = currentDateView.value.getMonth();
                    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                    return dateStr === getFilterRef(calendarFormContext.value).value.end;
                };

                const changeMonth = (delta) => {
                    const newDate = new Date(currentDateView.value);
                    newDate.setMonth(newDate.getMonth() + delta);
                    currentDateView.value = newDate;
                };



                const changeCalendarMonth = (delta) => {
                    const newDate = new Date(calendarActiveDate.value);
                    newDate.setMonth(newDate.getMonth() + delta);
                    calendarActiveDate.value = newDate;
                };

                const getCalendarDaysInMonth = (date) => {
                    return new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
                };

                const getCalendarEmptyDays = (date) => {
                    return new Date(date.getFullYear(), date.getMonth(), 1).getDay();
                };

                const isTodayCalendar = (day) => {
                    const today = new Date();
                    return today.getDate() === day && today.getMonth() === calendarActiveDate.value.getMonth() && today.getFullYear() === calendarActiveDate.value.getFullYear();
                };

                const getCalendarItems = (day) => {
                    const targetDate = fmtLocalDate(new Date(calendarActiveDate.value.getFullYear(), calendarActiveDate.value.getMonth(), day));

                    const items = masterPlanData.value.filter(item => {
                        const itemDate = item.Tanggal_Rencana ? new Date(item.Tanggal_Rencana).toISOString().split('T')[0] : null;
                        return itemDate === targetDate;
                    }).map(i => ({ ...i, TYPE: 'content' }));

                    const stories = storyData.value.filter(story => {
                        if (!story.Tanggal) return false;
                        const storyDate = new Date(story.Tanggal).toISOString().split('T')[0];
                        return storyDate === targetDate;
                    }).map(s => ({ ...s, TYPE: 'story' }));

                    const events = calendarEventsData.value.filter(ev => {
                        if (!ev.Tanggal) return false;
                        const evDate = new Date(ev.Tanggal).toISOString().split('T')[0];
                        return evDate === targetDate;
                    }).map(e => ({ ...e, TYPE: 'event' }));

                    return [...items, ...stories, ...events];
                };

                // getCalendarDaysInMonth returns a NUMBER (day count), so iterate manually.
                const calendarMonthIsEmpty = computed(() => {
                    const days = getCalendarDaysInMonth(calendarActiveDate.value);
                    for (let d = 1; d <= days; d += 1) {
                        if (getCalendarItems(d).length > 0) return false;
                    }
                    return true;
                });

                // Summary stats (cards + chips) per menu
                const _SPAL = ['bg-blue-50 text-blue-700', 'bg-emerald-50 text-emerald-700', 'bg-amber-50 text-amber-700', 'bg-rose-50 text-rose-700', 'bg-violet-50 text-violet-700', 'bg-slate-100 text-slate-600'];
                const _sCnt = (arr, fn) => { const m = {}; (arr || []).forEach(r => { let k = fn(r); k = (k == null ? '' : String(k)).trim(); if (!k) k = '-'; m[k] = (m[k] || 0) + 1; }); return m; };
                const _sSum = (arr, fn) => (arr || []).reduce((s, r) => s + (Number(fn(r)) || 0), 0);
                const _sChips = (m) => Object.entries(m).sort((a, b) => b[1] - a[1]).slice(0, 6).map(([label, n], i) => ({ label: String(label).toUpperCase(), n, cls: _SPAL[i % _SPAL.length] }));

                const masterSummary = computed(() => {
                    const d = filteredMasterPlanData.value || [];
                    const st = _sCnt(d, r => (r.Status || 'IDE').toUpperCase());
                    const done = (st['DONE'] || 0) + (st['PUBLISHED'] || 0);
                    const prog = (st['EDITING'] || 0) + (st['SHOOTING'] || 0) + (st['PROGRES'] || 0);
                    const now = new Date(); const ym = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
                    const month = d.filter(r => String(r.Tanggal_Rencana || '').startsWith(ym)).length;
                    return { cards: [
                        { label: 'Total Plan', value: formatNumber(d.length), unit: 'Konten', icon: 'fa-clipboard-list', color: 'text-blue-500', unitColor: 'text-blue-400', subColor: 'text-blue-600', sub: 'Semua rencana' },
                        { label: 'Selesai', value: formatNumber(done), icon: 'fa-circle-check', color: 'text-emerald-500', subColor: 'text-emerald-600', sub: 'Published / Done' },
                        { label: 'Dalam Proses', value: formatNumber(prog), icon: 'fa-spinner', color: 'text-amber-500', subColor: 'text-amber-600', sub: 'Shooting / Editing' },
                        { label: 'Bulan Ini', value: formatNumber(month), unit: 'Plan', icon: 'fa-calendar-day', color: 'text-violet-500', unitColor: 'text-violet-400', subColor: 'text-violet-600', sub: 'Periode aktif' },
                    ], chips: _sChips(st) };
                });
                const ideationSummary = computed(() => {
                    const d = filteredMasterPlanData.value || [];
                    const fmt = _sCnt(d, r => r.Format_Konten);
                    const plat = _sCnt(d, r => (r.Platforms || '').split(',')[0]);
                    const ide = d.filter(r => (r.Status || '').toUpperCase() === 'IDE').length;
                    return { cards: [
                        { label: 'Total Ide', value: formatNumber(d.length), unit: 'Konten', icon: 'fa-lightbulb', color: 'text-amber-500', unitColor: 'text-amber-400', subColor: 'text-amber-600', sub: 'Semua ide' },
                        { label: 'Status Ide', value: formatNumber(ide), icon: 'fa-seedling', color: 'text-blue-500', subColor: 'text-blue-600', sub: 'Belum diproduksi' },
                        { label: 'Format', value: formatNumber(Object.keys(fmt).length), unit: 'Jenis', icon: 'fa-shapes', color: 'text-violet-500', unitColor: 'text-violet-400', subColor: 'text-violet-600', sub: 'Variasi format' },
                        { label: 'Platform', value: formatNumber(Object.keys(plat).length), unit: 'Channel', icon: 'fa-share-nodes', color: 'text-emerald-500', unitColor: 'text-emerald-400', subColor: 'text-emerald-600', sub: 'Distribusi' },
                    ], chips: _sChips(fmt) };
                });
                const distributionSummary = computed(() => {
                    const d = filteredDistributionData.value || [];
                    const linked = d.filter(r => String(r.Link || '').trim()).length;
                    const plat = _sCnt(d, r => r.Platform);
                    return { cards: [
                        { label: 'Total Distribusi', value: formatNumber(d.length), unit: 'Konten', icon: 'fa-share-from-square', color: 'text-blue-500', unitColor: 'text-blue-400', subColor: 'text-blue-600', sub: 'Semua distribusi' },
                        { label: 'Ada Link', value: formatNumber(linked), icon: 'fa-link', color: 'text-emerald-500', subColor: 'text-emerald-600', sub: 'Terpublikasi' },
                        { label: 'Belum Link', value: formatNumber(d.length - linked), icon: 'fa-link-slash', color: 'text-rose-500', subColor: 'text-rose-600', sub: 'Belum ada link' },
                        { label: 'Platform', value: formatNumber(Object.keys(plat).length), unit: 'Channel', icon: 'fa-tower-broadcast', color: 'text-violet-500', unitColor: 'text-violet-400', subColor: 'text-violet-600', sub: 'Tersebar' },
                    ], chips: _sChips(plat) };
                });
                const analyticsSummary = computed(() => {
                    const d = filteredAnalyticsData.value || [];
                    const views = _sSum(d, r => r.Views), likes = _sSum(d, r => r.Likes), comments = _sSum(d, r => r.Comments);
                    const platV = {}; d.forEach(r => { const k = (r.Platform || '-'); platV[k] = (platV[k] || 0) + (Number(r.Views) || 0); });
                    const chips = Object.entries(platV).sort((a, b) => b[1] - a[1]).slice(0, 6).map(([label, n], i) => ({ label: String(label).toUpperCase(), n: formatNumber(n), cls: _SPAL[i % _SPAL.length] }));
                    return { cards: [
                        { label: 'Total Views', value: formatNumber(views), icon: 'fa-eye', color: 'text-blue-500', subColor: 'text-blue-600', sub: formatNumber(d.length) + ' konten' },
                        { label: 'Total Likes', value: formatNumber(likes), icon: 'fa-heart', color: 'text-rose-500', subColor: 'text-rose-600', sub: 'Engagement' },
                        { label: 'Total Comments', value: formatNumber(comments), icon: 'fa-comment', color: 'text-emerald-500', subColor: 'text-emerald-600', sub: 'Interaksi' },
                        { label: 'Total Konten', value: formatNumber(d.length), unit: 'Post', icon: 'fa-photo-film', color: 'text-violet-500', unitColor: 'text-violet-400', subColor: 'text-violet-600', sub: 'Dianalisa' },
                    ], chips };
                });
                const unboxingSummary = computed(() => {
                    const d = filteredUnboxingData.value || [];
                    const st = _sCnt(d, r => r.Status);
                    const done = Object.entries(st).filter(([k]) => ['DONE', 'SELESAI', 'PUBLISHED', 'UPLOAD', 'UPLOADED'].includes(k.toUpperCase())).reduce((s, [, n]) => s + n, 0);
                    const editors = _sCnt(d, r => r.Editor);
                    return { cards: [
                        { label: 'Total Unboxing', value: formatNumber(d.length), unit: 'Video', icon: 'fa-box-open', color: 'text-blue-500', unitColor: 'text-blue-400', subColor: 'text-blue-600', sub: 'Semua unboxing' },
                        { label: 'Selesai', value: formatNumber(done), icon: 'fa-circle-check', color: 'text-emerald-500', subColor: 'text-emerald-600', sub: 'Sudah upload' },
                        { label: 'Proses', value: formatNumber(d.length - done), icon: 'fa-spinner', color: 'text-amber-500', subColor: 'text-amber-600', sub: 'Belum selesai' },
                        { label: 'Editor', value: formatNumber(Object.keys(editors).length), unit: 'Orang', icon: 'fa-user-pen', color: 'text-violet-500', unitColor: 'text-violet-400', subColor: 'text-violet-600', sub: 'Terlibat' },
                    ], chips: _sChips(st) };
                });
                const storySummary = computed(() => {
                    const d = storyData.value || [];
                    const genap = d.filter(r => r.is_genap === true || r.is_genap === 1 || String(r.is_genap) === '1').length;
                    const st = _sCnt(d, r => r.Status);
                    return { cards: [
                        { label: 'Total Story', value: formatNumber(d.length), unit: 'Jadwal', icon: 'fa-clapperboard', color: 'text-rose-500', unitColor: 'text-rose-400', subColor: 'text-rose-600', sub: 'Semua story' },
                        { label: 'Ganjil', value: formatNumber(d.length - genap), icon: 'fa-circle-half-stroke', color: 'text-blue-500', subColor: 'text-blue-600', sub: 'Minggu ganjil' },
                        { label: 'Genap', value: formatNumber(genap), icon: 'fa-circle', color: 'text-emerald-500', subColor: 'text-emerald-600', sub: 'Minggu genap' },
                        { label: 'Status', value: formatNumber(Object.keys(st).length), unit: 'Tipe', icon: 'fa-list-check', color: 'text-violet-500', unitColor: 'text-violet-400', subColor: 'text-violet-600', sub: 'Variasi status' },
                    ], chips: _sChips(st) };
                });
                const promoSummary = computed(() => {
                    const d = filteredPromoData.value || [];
                    const kat = _sCnt(d, r => r.Kategori);
                    const totalHarga = _sSum(d, r => r.Harga);
                    return { cards: [
                        { label: 'Total Program', value: formatNumber(d.length), unit: 'Promo', icon: 'fa-tags', color: 'text-blue-500', unitColor: 'text-blue-400', subColor: 'text-blue-600', sub: 'Semua program' },
                        { label: 'Kategori', value: formatNumber(Object.keys(kat).length), unit: 'Jenis', icon: 'fa-layer-group', color: 'text-violet-500', unitColor: 'text-violet-400', subColor: 'text-violet-600', sub: 'Variasi kategori' },
                        { label: 'Total Nilai', value: formatCurrency(totalHarga), icon: 'fa-money-bill-wave', color: 'text-amber-500', subColor: 'text-amber-600', sub: 'Akumulasi harga' },
                        { label: 'Rata Harga', value: formatCurrency(d.length ? totalHarga / d.length : 0), icon: 'fa-calculator', color: 'text-emerald-500', subColor: 'text-emerald-600', sub: 'Per program' },
                    ], chips: _sChips(kat) };
                });
                const orderanSummary = computed(() => {
                    const d = filteredOrderanOnlineData.value || [];
                    const st = _sCnt(d, r => r.STATUS);
                    const cair = _sSum(d, r => (r['NOMINAL CAIR'] != null ? r['NOMINAL CAIR'] : r.HARGA_ONLINE));
                    const done = Object.entries(st).filter(([k]) => ['SELESAI', 'DONE', 'CAIR', 'LUNAS'].includes(k.toUpperCase())).reduce((s, [, n]) => s + n, 0);
                    const kirim = _sCnt(d, r => r.PENGIRIMAN);
                    return { cards: [
                        { label: 'Total Orderan', value: formatNumber(d.length), unit: 'Order', icon: 'fa-cart-shopping', color: 'text-blue-500', unitColor: 'text-blue-400', subColor: 'text-blue-600', sub: 'Semua orderan' },
                        { label: 'Total Cair', value: formatCurrency(cair), icon: 'fa-money-bill-trend-up', color: 'text-emerald-500', subColor: 'text-emerald-600', sub: 'Nominal masuk' },
                        { label: 'Selesai', value: formatNumber(done), icon: 'fa-circle-check', color: 'text-violet-500', subColor: 'text-violet-600', sub: 'Order tuntas' },
                        { label: 'Ekspedisi', value: formatNumber(Object.keys(kirim).length), unit: 'Jasa', icon: 'fa-truck', color: 'text-amber-500', unitColor: 'text-amber-400', subColor: 'text-amber-600', sub: 'Pengiriman' },
                    ], chips: _sChips(st) };
                });
                const unitDitanyaSummary = computed(() => {
                    const d = filteredUnitDitanyaData.value || [];
                    const ditanya = _sSum(d, r => r.DITANYA);
                    const avail = d.filter(r => { const v = String(r.AVAILABLE || '').toUpperCase(); return v === 'YA' || v === 'ADA' || v === 'AVAILABLE' || v === '1' || v === 'TRUE' || Number(r.AVAILABLE) > 0; }).length;
                    const brand = _sCnt(d, r => r.BRAND);
                    return { cards: [
                        { label: 'Total Unit', value: formatNumber(d.length), unit: 'Tipe', icon: 'fa-mobile-screen', color: 'text-blue-500', unitColor: 'text-blue-400', subColor: 'text-blue-600', sub: 'Data unit' },
                        { label: 'Total Ditanya', value: formatNumber(ditanya), icon: 'fa-comments', color: 'text-amber-500', subColor: 'text-amber-600', sub: 'Akumulasi' },
                        { label: 'Available', value: formatNumber(avail), icon: 'fa-circle-check', color: 'text-emerald-500', subColor: 'text-emerald-600', sub: 'Ready stock' },
                        { label: 'Brand', value: formatNumber(Object.keys(brand).length), unit: 'Merek', icon: 'fa-tags', color: 'text-violet-500', unitColor: 'text-violet-400', subColor: 'text-violet-600', sub: 'Variasi brand' },
                    ], chips: _sChips(brand) };
                });
                const claimSummary = computed(() => {
                    const d = filteredClaimGaransiData.value || [];
                    const st = _sCnt(d, r => r.STATUS);
                    const done = Object.entries(st).filter(([k]) => ['SELESAI', 'CLAIM', 'DONE', 'PROCESED', 'PROCESSED'].includes(k.toUpperCase())).reduce((s, [, n]) => s + n, 0);
                    const pending = (st['NOT STARTED'] || 0) + (st['PENDING'] || 0);
                    const gar = _sCnt(d, r => r.GARANSI);
                    return { cards: [
                        { label: 'Total Klaim', value: formatNumber(d.length), unit: 'Unit', icon: 'fa-shield-halved', color: 'text-blue-500', unitColor: 'text-blue-400', subColor: 'text-blue-600', sub: 'Semua klaim' },
                        { label: 'Selesai', value: formatNumber(done), icon: 'fa-circle-check', color: 'text-emerald-500', subColor: 'text-emerald-600', sub: 'Klaim beres' },
                        { label: 'Pending', value: formatNumber(pending), icon: 'fa-hourglass-half', color: 'text-amber-500', subColor: 'text-amber-600', sub: 'Belum diproses' },
                        { label: 'Jenis Garansi', value: formatNumber(Object.keys(gar).length), unit: 'Tipe', icon: 'fa-file-shield', color: 'text-violet-500', unitColor: 'text-violet-400', subColor: 'text-violet-600', sub: 'Variasi garansi' },
                    ], chips: _sChips(st) };
                });
                const hargaSummary = computed(() => {
                    const d = filteredHargaKompetitorData.value || [];
                    const margins = d.map(r => Number(r.Margin_Profit) || 0);
                    const avg = d.length ? margins.reduce((a, b) => a + b, 0) / d.length : 0;
                    const untung = margins.filter(m => m > 0).length;
                    const rugi = margins.filter(m => m <= 0).length;
                    const range = { '>20%': 0, '10-20%': 0, '<10%': 0, 'NEGATIF': 0 };
                    margins.forEach(m => { if (m > 20) range['>20%']++; else if (m >= 10) range['10-20%']++; else if (m >= 0) range['<10%']++; else range['NEGATIF']++; });
                    const chips = Object.entries(range).filter(([, n]) => n > 0).map(([label, n], i) => ({ label, n, cls: _SPAL[i % _SPAL.length] }));
                    return { cards: [
                        { label: 'Total Produk', value: formatNumber(d.length), unit: 'Item', icon: 'fa-box', color: 'text-blue-500', unitColor: 'text-blue-400', subColor: 'text-blue-600', sub: 'Dipantau' },
                        { label: 'Avg Margin', value: (Math.round(avg * 10) / 10) + '%', icon: 'fa-percent', color: 'text-emerald-500', subColor: 'text-emerald-600', sub: 'Rata margin' },
                        { label: 'Untung', value: formatNumber(untung), icon: 'fa-arrow-trend-up', color: 'text-violet-500', subColor: 'text-violet-600', sub: 'Margin positif' },
                        { label: 'Rugi / Tipis', value: formatNumber(rugi), icon: 'fa-arrow-trend-down', color: 'text-rose-500', subColor: 'text-rose-600', sub: 'Margin <= 0' },
                    ], chips };
                });
                const lpjkSummary = computed(() => {
                    const d = lpjkData.value || [];
                    const budget = _sSum(d, r => r.Budget_Rencana);
                    const real = _sSum(d, r => r.Realisasi_Biaya);
                    const st = _sCnt(d, r => r.Status);
                    const sisa = budget - real;
                    return { cards: [
                        { label: 'Total Event', value: formatNumber(d.length), unit: 'Event', icon: 'fa-calendar-check', color: 'text-violet-500', unitColor: 'text-violet-400', subColor: 'text-violet-600', sub: 'Semua event' },
                        { label: 'Total Budget', value: formatCurrency(budget), icon: 'fa-wallet', color: 'text-blue-500', subColor: 'text-blue-600', sub: 'Rencana' },
                        { label: 'Realisasi', value: formatCurrency(real), icon: 'fa-money-check-dollar', color: 'text-amber-500', subColor: 'text-amber-600', sub: 'Terpakai' },
                        { label: 'Sisa', value: formatCurrency(sisa), icon: 'fa-piggy-bank', color: sisa >= 0 ? 'text-emerald-500' : 'text-rose-500', subColor: sisa >= 0 ? 'text-emerald-600' : 'text-rose-600', sub: sisa >= 0 ? 'Hemat' : 'Over budget' },
                    ], chips: _sChips(st) };
                });

                const loadStoryData = () => new Promise(resolve => {
                    ensureRunApi().withSuccessHandler(data => { storyData.value = data || []; resolve(); }).withFailureHandler(() => resolve()).getStorySchedule();
                });

                const loadUnboxingData = () => {
                    return new Promise((resolve) => {
                        ensureRunApi()
                            .withSuccessHandler((data) => { unboxingData.value = Array.isArray(data) ? data : []; resolve(); })
                            .withFailureHandler(() => resolve())
                            .getUnboxingData();
                    });
                };

                const loadOrderanOnlineData = () => new Promise(resolve => {
                    ensureRunApi().withSuccessHandler(d => { orderanOnlineData.value = Array.isArray(d) ? d : []; resolve(); }).withFailureHandler(() => resolve()).getOrderanOnlineData();
                });

                const loadUnitDitanyaData = () => new Promise(resolve => {
                    ensureRunApi().withSuccessHandler(d => { unitDitanyaData.value = Array.isArray(d) ? d : []; resolve(); }).withFailureHandler(() => resolve()).getUnitDitanyaData();
                });

                const openCalendarDayModal = (day) => {
                    const year = calendarActiveDate.value.getFullYear();
                    const month = calendarActiveDate.value.getMonth();
                    calendarDayModalDate.value = `${day} ${monthNames[month]} ${year}`;
                    calendarDayModalItems.value = getCalendarItems(day);
                    calendarDayModalOpen.value = true;
                };

                const loadClaimGaransiData = () => new Promise(resolve => {
                    ensureRunApi().withSuccessHandler(d => { claimGaransiData.value = Array.isArray(d) ? d : []; resolve(); }).withFailureHandler(() => resolve()).getClaimGaransiData();
                });

                const loadKeepBarangData = () => new Promise(resolve => {
                    ensureRunApi().withSuccessHandler(d => { keepBarangData.value = Array.isArray(d) ? d : []; resolve(); }).withFailureHandler(() => resolve()).getKeepBarangData();
                });

                const openCreateStoryModal = () => {
                    storyModalType.value = "create";
                    storyForm.value = {
                        ID: null,
                        is_genap: storyTab.value === 'Genap' ? 1 : 0,
                        Tanggal: "",
                        Jam: "09:00",
                        Story: "",
                        Catatan: "",
                        Link: "",
                        Status: ""
                    };
                    storyModalOpen.value = true;
                };

                const openEditStoryModal = (item) => {
                    storyModalType.value = "edit";
                    storyForm.value = { ...item };
                    storyModalOpen.value = true;
                };

                const saveStory = () => {
                    if (!storyForm.value.Story || !storyForm.value.Jam) {
                        showNotification("Story dan Jam wajib diisi!");
                        return;
                    }
                    submitting.value = true;
                    ensureRunApi().withSuccessHandler(response => {
                        submitting.value = false;
                        if (response.status === "success") {
                            loadStoryData();
                            storyModalOpen.value = false;
                            showNotification(storyModalType.value === "create" ? "Story berhasil ditambahkan" : "Story berhasil diupdate");
                        } else {
                            handleError(new Error(response.message || "Gagal menyimpan story"));
                        }
                    }).withFailureHandler(handleError).saveStory(storyForm.value);
                };

                const deleteStory = (id) => {
                    showConfirm("Hapus Story", "Apakah Anda yakin ingin menghapus jadwal story ini?", () => {
                        ensureRunApi().withSuccessHandler(() => {
                            loadStoryData();
                            showNotification("Story berhasil dihapus");
                        }).deleteStory(id);
                    });
                };

                // Unboxing save/delete
                const saveUnboxing = () => {
                    if (!unboxingForm.value.Nama) { showNotification("Nama wajib diisi"); return; }
                    submitting.value = true;
                    ensureRunApi()
                        .withSuccessHandler((res) => {
                            submitting.value = false;
                            unboxingModalOpen.value = false;
                            loadUnboxingData();
                            showNotification(unboxingModalType.value === 'create' ? 'Unboxing berhasil ditambahkan' : 'Unboxing berhasil diupdate');
                        })
                        .withFailureHandler((err) => { submitting.value = false; handleError(err); })
                        .saveUnboxing(unboxingForm.value);
                };

                const deleteUnboxing = (id) => {
                    showConfirm("Hapus Unboxing?", "Data yang dihapus tidak dapat dikembalikan.", () => {
                        ensureRunApi().withSuccessHandler(() => { loadUnboxingData(); showNotification("Unboxing berhasil dihapus"); }).deleteUnboxing(id);
                    });
                };

                // Orderan Online save/delete
                const saveOrderanOnline = () => {
                    if (submitting.value) return;
                    if (!orderanOnlineForm.value.NAMA || !orderanOnlineForm.value['TYPE UNIT']) { showNotification('Nama customer dan type unit wajib diisi'); return; }
                    submitting.value = true;
                    ensureRunApi()
                        .withSuccessHandler((res) => {
                            submitting.value = false;
                            orderanOnlineModalOpen.value = false;
                            loadOrderanOnlineData();
                            showNotification('Orderan berhasil disimpan');
                        })
                        .withFailureHandler((err) => { submitting.value = false; handleError(err); })
                        .saveOrderanOnline(orderanOnlineForm.value);
                };

                const deleteOrderanOnline = (id) => {
                    showConfirm("Hapus Orderan?", "Data yang dihapus tidak dapat dikembalikan.", () => {
                        ensureRunApi().withSuccessHandler(() => { loadOrderanOnlineData(); showNotification("Orderan berhasil dihapus"); }).deleteOrderanOnline(id);
                    });
                };

                // Unit Ditanya save/delete
                const saveUnitDitanya = () => {
                    if (submitting.value) return;
                    if (!unitDitanyaForm.value.KATEGORI || !unitDitanyaForm.value.BRAND || !unitDitanyaForm.value.SERI) { showNotification('Kategori, brand, dan seri wajib diisi'); return; }
                    submitting.value = true;
                    ensureRunApi()
                        .withSuccessHandler((res) => {
                            submitting.value = false;
                            unitDitanyaModalOpen.value = false;
                            loadUnitDitanyaData();
                            showNotification('Unit berhasil disimpan');
                        })
                        .withFailureHandler((err) => { submitting.value = false; handleError(err); })
                        .saveUnitDitanya(unitDitanyaForm.value);
                };

                const deleteUnitDitanya = (id) => {
                    showConfirm("Hapus Unit?", "Data yang dihapus tidak dapat dikembalikan.", () => {
                        ensureRunApi().withSuccessHandler(() => { loadUnitDitanyaData(); showNotification("Unit berhasil dihapus"); }).deleteUnitDitanya(id);
                    });
                };

                // Claim Garansi save/delete
                const saveClaimGaransi = () => {
                    if (submitting.value) return;
                    if (!claimGaransiForm.value.NAMA_CUSTOMER || !claimGaransiForm.value.TIPE) { showNotification('Nama customer dan tipe wajib diisi'); return; }
                    submitting.value = true;
                    ensureRunApi()
                        .withSuccessHandler((res) => {
                            submitting.value = false;
                            claimGaransiModalOpen.value = false;
                            loadClaimGaransiData();
                            showNotification('Claim berhasil disimpan');
                        })
                        .withFailureHandler((err) => { submitting.value = false; handleError(err); })
                        .saveClaimGaransi(claimGaransiForm.value);
                };

                const deleteClaimGaransi = (id) => {
                    showConfirm("Hapus Claim?", "Data yang dihapus tidak dapat dikembalikan.", () => {
                        ensureRunApi().withSuccessHandler(() => { loadClaimGaransiData(); showNotification("Claim berhasil dihapus"); }).deleteClaimGaransi(id);
                    });
                };

                const saveKeepBarang = () => {
                    if (submitting.value) return;
                    const form = { ...keepBarangForm.value };
                    if (!form.NAMA || !form.NOMOR_HP || !form.TYPE_HP) { showNotification('Nama, nomor HP, dan Type HP wajib diisi'); return; }
                    submitting.value = true;
                    computeKeepBarangDerived(form);
                    ensureRunApi()
                        .withSuccessHandler(() => {
                            submitting.value = false;
                            keepBarangModalOpen.value = false;
                            loadKeepBarangData();
                            showNotification('Data berhasil disimpan');
                        })
                        .withFailureHandler((err) => { submitting.value = false; handleError(err); })
                        .saveKeepBarang(form);
                };

                const deleteKeepBarang = (id) => {
                    showConfirm("Hapus Data?", "Data yang dihapus tidak dapat dikembalikan.", () => {
                        ensureRunApi().withSuccessHandler(() => { loadKeepBarangData(); showNotification("Data berhasil dihapus"); }).deleteKeepBarang(id);
                    });
                };

                // Bonus Report computed & methods

                const formatCurrency = (value) => {
                    const n = Number(value || 0);
                    if (isNaN(n)) return '-';
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n);
                };

                const getBonusTier = (views, isColab) => {
                    const src = isColab ? bonusConfig.value.reelsColab : bonusConfig.value.reelsNonColab;
                    const tiers = Array.isArray(src) ? [...src] : [];
                    tiers.sort((a, b) => b.min - a.min);
                    const tier = tiers.find(t => views >= t.min);
                    return tier ? tier.amount : 0;
                };

                const filteredBonusRows = computed(() => {
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

                    const start = bonusFilter.value.start;
                    const end = bonusFilter.value.end;
                    const cfg = bonusConfig.value;
                    if (!cfg?.engagement) return [];

                    return analytics.map(aRow => {
                        const masterId = String(aRow.Master_ID || '');
                        const mRow = masterById.get(masterId) || {};
                        const distRows = distByMaster.get(masterId) || [];
                        const dRow = distRows.find(r => (r.Platform || '').toLowerCase() === (aRow.Platform || '').toLowerCase()) || distRows[0] || null;
                        const rawDate = dRow?.Tanggal_Publish || aRow.Tanggal_Post || aRow.Tanggal || '';

                        if (start && rawDate && rawDate < start) return null;
                        if (end && rawDate && rawDate > end) return null;

                        const platform = (aRow.Platform || '').toLowerCase();
                        const views = Number(aRow.Views || 0);
                        const likes = Number(aRow.Likes || 0);
                        const comments = Number(aRow.Comments || 0);
                        const shares = Number(aRow.Shares || 0);

                        if ((mRow.Format_Konten || '').toUpperCase() === 'IKLAN') return null;

                        const colab = mRow.Colab || '';
                        const isColab = colab && colab.toLowerCase() !== 'tidak' && colab.trim() !== '';
                        const contentType = isColab ? 'Colab' : (mRow.Format_Konten || 'Regular');

                        const viewBonus = getBonusTier(views, isColab);

                        const safeFloor = (val, unit, bonus) => (unit > 0 && val >= unit ? bonus : 0);
                        let likeBonus = 0;
                        if (platform.includes('instagram')) {
                            likeBonus = safeFloor(likes, cfg.engagement.instagram.likeUnit, cfg.engagement.instagram.likeBonus);
                        } else if (platform.includes('tiktok')) {
                            likeBonus = safeFloor(likes, cfg.engagement.tiktok.likeUnit, cfg.engagement.tiktok.likeBonus);
                        } else if (platform.includes('youtube')) {
                            likeBonus = safeFloor(likes, cfg.engagement.youtube.likeUnit, cfg.engagement.youtube.likeBonus);
                        }

                        const commentBonus = safeFloor(comments, cfg.engagement.general.commentUnit, cfg.engagement.general.commentBonus);
                        const calculatedBonus = viewBonus + likeBonus + commentBonus;

                        return {
                            id: aRow.ID,
                            masterPlan: mRow,
                            Judul: mRow.Judul || aRow.Judul || 'Untitled',
                            Editor: mRow.Editor || aRow.Editor || '-',
                            Talent: mRow.Talent || '',
                            TalentList: Array.isArray(mRow.TalentList) ? mRow.TalentList : [],
                            Platform: aRow.Platform || '-',
                            date: rawDate,
                            Views: views, Likes: likes, Comments: comments, Shares: shares,
                            contentType,
                            viewBonus, likeBonus, commentBonus, calculatedBonus
                        };
                    }).filter(r => r && r.calculatedBonus > 0);
                });

                const bonusTotal = computed(() => {
                    const rows = filteredBonusRows.value;
                    return {
                        count: rows.length,
                        views: rows.reduce((s, r) => s + r.Views, 0),
                        likes: rows.reduce((s, r) => s + r.Likes, 0),
                        comments: rows.reduce((s, r) => s + r.Comments, 0),
                        totalMoney: rows.reduce((s, r) => s + r.calculatedBonus, 0)
                    };
                });

                const bonusTotalPages = computed(() => Math.max(1, Math.ceil(filteredBonusRows.value.length / PAGE_SIZE)));
                const pagedBonusRows = computed(() => filteredBonusRows.value.slice((bonusPage.value - 1) * PAGE_SIZE, bonusPage.value * PAGE_SIZE));

                watch([bonusMonth, bonusYear], () => { bonusPage.value = 1; });

                const TALENT_DAILY_BONUS = 150000;

                const talentBonusRows = computed(() => {
                    const start = bonusFilter.value.start;
                    const end = bonusFilter.value.end;
                    const distByMaster = new Map();

                    distributionData.value.forEach((row) => {
                        const key = String(row.Master_ID || '');
                        if (!key) return;
                        if (!distByMaster.has(key)) distByMaster.set(key, []);
                        distByMaster.get(key).push(row);
                    });

                    return masterPlanData.value
                        .filter((item) => {
                            const talents = Array.isArray(item.TalentList) && item.TalentList.length
                                ? item.TalentList
                                : String(item.Talent || '').split(/[,;]/).map((name) => name.trim()).filter(Boolean);
                            const distRows = distByMaster.get(String(item.ID || '')) || [];
                            const rawDate = item.Tanggal_Rencana || '';
                            if (!talents.length || !rawDate) return false;
                            if (start && rawDate < start) return false;
                            if (end && rawDate > end) return false;
                            return true;
                        })
                        .flatMap((item) => {
                            const talents = Array.isArray(item.TalentList) && item.TalentList.length
                                ? item.TalentList
                                : String(item.Talent || '').split(/[,;]/).map((name) => name.trim()).filter(Boolean);
                            const distRows = distByMaster.get(String(item.ID || '')) || [];
                            const platforms = distRows.length
                                ? [...new Set(distRows.map((row) => String(row.Platform || '').trim()).filter(Boolean))]
                                : String(item.Platforms || '').split(/[;,]/).map((name) => name.trim()).filter(Boolean);
                            const rawDate = item.Tanggal_Rencana || '';
                            return talents.map((talent) => ({
                                id: item.ID,
                                Judul: item.Judul || 'Untitled',
                                Editor: item.Editor || '-',
                                Talent: talent,
                                TalentList: talents,
                                Platform: platforms.join(', ') || '-',
                                date: rawDate,
                                Views: 0,
                                Likes: 0,
                                Comments: 0,
                                Shares: 0,
                                Status: item.Status || '-',
                            }));
                        });
                });

                const buildTalentDailyRows = (rows) => {
                    const groupedByTalent = new Map();

                    rows.forEach((row) => {
                        const talentName = String(row.Talent || '-').trim() || '-';
                        const dateKey = String(row.date || '').slice(0, 10);
                        if (!dateKey) return;
                        if (!groupedByTalent.has(talentName)) groupedByTalent.set(talentName, new Map());
                        const byDate = groupedByTalent.get(talentName);
                        if (!byDate.has(dateKey)) byDate.set(dateKey, []);
                        byDate.get(dateKey).push(row);
                    });

                    const dailyRows = [];

                    [...groupedByTalent.entries()]
                        .sort((a, b) => a[0].localeCompare(b[0], 'id'))
                        .forEach(([talentName, byDate]) => {
                            let carryCount = 0;
                            [...byDate.entries()]
                                .sort((a, b) => a[0].localeCompare(b[0]))
                                .forEach(([dateKey, dayRows]) => {
                                    const effectiveCount = dayRows.length + carryCount;
                                    if (effectiveCount <= 0) return;

                                    const calculatedBonus = effectiveCount <= 2
                                        ? TALENT_DAILY_BONUS
                                        : Math.floor(effectiveCount / 2) * TALENT_DAILY_BONUS;

                                    carryCount = effectiveCount > 2 && effectiveCount % 2 === 1 ? 1 : 0;

                                    const totalViews = dayRows.reduce((sum, row) => sum + Number(row.Views || 0), 0);
                                    const totalLikes = dayRows.reduce((sum, row) => sum + Number(row.Likes || 0), 0);
                                    const totalComments = dayRows.reduce((sum, row) => sum + Number(row.Comments || 0), 0);
                                    const uniquePlatforms = [...new Set(dayRows.map((row) => row.Platform).filter(Boolean))];
                                    const titlePreview = dayRows.map((row) => row.Judul).filter(Boolean);
                                    const detailBits = [
                                        uniquePlatforms.length ? uniquePlatforms.join(', ') : '',
                                        titlePreview.length ? titlePreview.slice(0, 2).join(' | ') : '',
                                        titlePreview.length > 2 ? `+${titlePreview.length - 2} konten` : '',
                                        carryCount ? `carry ${carryCount} video` : ''
                                    ].filter(Boolean);

                                    dailyRows.push({
                                        id: `${talentName}-${dateKey}`,
                                        Talent: talentName,
                                        date: dateKey,
                                        videoCount: dayRows.length,
                                        effectiveCount,
                                        carriedToNextDay: carryCount,
                                        detailLabel: detailBits.join(' | '),
                                        Views: totalViews,
                                        Likes: totalLikes,
                                        Comments: totalComments,
                                        calculatedBonus
                                    });
                                });
                        });

                    return dailyRows.sort((a, b) => {
                        if (a.date !== b.date) return b.date.localeCompare(a.date);
                        return a.Talent.localeCompare(b.Talent, 'id');
                    });
                };

                const talentDashboardData = computed(() => {
                    const rows = buildTalentDailyRows(talentBonusRows.value);
                    const leaderboardMap = new Map();
                    rows.forEach((row) => {
                        const name = row.Talent || '-';
                        if (!leaderboardMap.has(name)) {
                            leaderboardMap.set(name, { name, count: 0, bonus: 0, views: 0, likes: 0, comments: 0 });
                        }
                        const current = leaderboardMap.get(name);
                        current.count++;
                        current.bonus += Number(row.calculatedBonus || 0);
                        current.views += Number(row.Views || 0);
                        current.likes += Number(row.Likes || 0);
                        current.comments += Number(row.Comments || 0);
                    });

                    const leaderboard = [...leaderboardMap.values()].sort((a, b) => b.bonus - a.bonus || b.views - a.views);

                    return {
                        rows,
                        leaderboard,
                        totalEntries: rows.length,
                        totalBonus: rows.reduce((sum, row) => sum + Number(row.calculatedBonus || 0), 0),
                        totalViews: rows.reduce((sum, row) => sum + Number(row.Views || 0), 0),
                        totalLikes: rows.reduce((sum, row) => sum + Number(row.Likes || 0), 0),
                        totalComments: rows.reduce((sum, row) => sum + Number(row.Comments || 0), 0),
                    };
                });

                const talentTotalPages = computed(() => Math.max(1, Math.ceil(talentDashboardData.value.rows.length / PAGE_SIZE)));
                const pagedTalentRows = computed(() => talentDashboardData.value.rows.slice((talentPage.value - 1) * PAGE_SIZE, talentPage.value * PAGE_SIZE));

                watch([bonusMonth, bonusYear], () => { talentPage.value = 1; });

                const CACHE_KEY = 'ppp_allData_v5_clean';

                const applyAllData = (d) => {
                    if (!d) return;
                    const s = d.settings || {};
                    settings.value = ensureSettingDefaults(s);
                    settingsDraft.value = settings.value; // shallow copy is enough; full clone deferred below
                    setTimeout(() => { try { settingsDraft.value = JSON.parse(JSON.stringify(settings.value)); } catch (e) { } }, 0);
                    if (d.masterPlan) masterPlanData.value = normalizeMasterPlanRows(Array.isArray(d.masterPlan) ? d.masterPlan : []);
                    if (d.analytics) analyticsData.value = Array.isArray(d.analytics) ? d.analytics : [];
                    if (d.distribution) distributionData.value = Array.isArray(d.distribution) ? d.distribution : [];
                    if (d.story) storyData.value = Array.isArray(d.story) ? d.story : [];
                    const _tabUpdates = {};
                    if (d.unboxing) { unboxingData.value = Array.isArray(d.unboxing) ? d.unboxing : []; _tabUpdates.unboxing = true; }
                    if (d.orderanOnline) { orderanOnlineData.value = Array.isArray(d.orderanOnline) ? d.orderanOnline : []; _tabUpdates.orderanOnline = true; }
                    if (d.unitDitanya) { unitDitanyaData.value = Array.isArray(d.unitDitanya) ? d.unitDitanya : []; _tabUpdates.unitDitanya = true; }
                    if (d.claimGaransi) { claimGaransiData.value = Array.isArray(d.claimGaransi) ? d.claimGaransi : []; _tabUpdates.claimGaransi = true; }
                    if (d.keepBarang) { keepBarangData.value = Array.isArray(d.keepBarang) ? d.keepBarang : []; keepBarangLoaded.value = true; }
                    if (d.promo) { promoData.value = Array.isArray(d.promo) ? d.promo : []; _tabUpdates.promo = true; }
                    if (d.sellOut) { sellOutData.value = Array.isArray(d.sellOut) ? d.sellOut : []; _tabUpdates.sellOut = true; }
                    if (d.hargaKompetitor) { hargaKompetitorData.value = Array.isArray(d.hargaKompetitor) ? d.hargaKompetitor : []; _tabUpdates.hargaKompetitor = true; }
                    if (d.lpjk) { lpjkData.value = Array.isArray(d.lpjk) ? d.lpjk : []; _tabUpdates.lpjk = true; }
                    if (d.calendarEvents) calendarEventsData.value = Array.isArray(d.calendarEvents) ? d.calendarEvents : [];
                    if (d.ads || d.adsLog || d.adsData) { adsData.value = Array.isArray(d.ads) ? d.ads : Array.isArray(d.adsLog) ? d.adsLog : Array.isArray(d.adsData) ? d.adsData : []; _tabUpdates.ads = true; }
                    if (Object.keys(_tabUpdates).length) tabDataLoaded.value = Object.assign({}, tabDataLoaded.value, _tabUpdates);
                    if (d.bonusConfig && typeof d.bonusConfig === 'object') {
                        bonusConfig.value = _mergeBonusConfig(d.bonusConfig);
                        bonusConfigLoaded.value = true;
                        setTimeout(() => { try { localStorage.setItem('ppp_bonusConfig', JSON.stringify(bonusConfig.value)); } catch (e) { } }, 0);
                    }
                    if (d.budgetingConfig && typeof d.budgetingConfig === 'object') {
                        const _localCfg = (() => { try { const s = localStorage.getItem('ppp_budgetConfig'); return s ? JSON.parse(s) : null; } catch (e) { return null; } })();
                        const _inc = d.budgetingConfig;
                        const bCfg = _mergeBudgetConfig(_inc);
                        if (!(_inc.colabPartners?.length) && _localCfg?.colabPartners?.length) bCfg.colabPartners = _localCfg.colabPartners;
                        if (!(_inc.others?.length) && _localCfg?.others?.length) bCfg.others = _localCfg.others;
                        budgetConfig.value = bCfg;
                        budgetConfigLoaded.value = true;
                        setTimeout(() => { try { localStorage.setItem('ppp_budgetConfig', JSON.stringify(bCfg)); } catch (e) { } }, 0);
                    }
                };

                // Cache-first loader: apply cached data immediately for instant display,
                // then fetch only critical sheets from server for fast startup.
                // Remaining tab data is loaded lazily via loadTabData() on first navigation.
                const loadAllData = (useInitRpc = false) => new Promise((resolve, reject) => {
                    // Read and apply cache synchronously for instant display
                    try {
                        const raw = localStorage.getItem(CACHE_KEY);
                        if (raw) {
                            applyAllData(JSON.parse(raw));
                            appLoading.value = false;
                        }
                    } catch (e) { }
                    const rpcMethod = useInitRpc ? 'initAndGetCriticalData' : 'getCriticalData';
                    ensureRunApi().withSuccessHandler(async d => {
                        if (!d) { resolve(); return; }
                        try {
                            if (!Array.isArray(d.masterPlan)) {
                                if (ensureRunApi().isWebProxy) {
                                    d.masterPlan = await fetchMasterPlansFromDatabase();
                                } else {
                                    d.masterPlan = await new Promise((res, rej) =>
                                        ensureRunApi().withSuccessHandler(res).withFailureHandler(rej).getMasterPlanData()
                                    );
                                }
                            }
                        } catch (error) { }
                        applyAllData(d);
                        resolve();
                        // Defer heavy JSON.stringify + localStorage write to avoid
                        // stacking on top of GAS's own postMessage deserialization cost
                        setTimeout(() => {
                            try { localStorage.setItem(CACHE_KEY, JSON.stringify(d)); } catch (e) { }
                        }, 0);
                    }).withFailureHandler(reject)[rpcMethod]();
                });

                const loadBonusConfig = () => {
                    if (bonusConfigLoaded.value) return Promise.resolve();
                    return new Promise(resolve => {
                        ensureRunApi().withSuccessHandler(cfg => {
                            if (cfg && typeof cfg === 'object') {
                                bonusConfig.value = _mergeBonusConfig(cfg);
                                localStorage.setItem('ppp_bonusConfig', JSON.stringify(bonusConfig.value));
                            }
                            bonusConfigLoaded.value = true;
                            resolve();
                        }).withFailureHandler(() => resolve()).getBonusConfig();
                    });
                };

                const refreshBonusSourceData = () => Promise.allSettled([
                    loadMasterPlanData(),
                    loadDistributionData(),
                    loadAnalyticsData(),
                ]);

                const loadBudgetingConfig = () => {
                    if (budgetConfigLoaded.value) return Promise.resolve();
                    return new Promise(resolve => {
                        ensureRunApi().withSuccessHandler(cfg => {
                            if (cfg && typeof cfg === 'object') {
                                const _localCfg2 = (() => { try { const s = localStorage.getItem('ppp_budgetConfig'); return s ? JSON.parse(s) : null; } catch (e) { return null; } })();
                                const bCfg = _mergeBudgetConfig(cfg);
                                if (!cfg.colabPartners?.length && _localCfg2?.colabPartners?.length) bCfg.colabPartners = _localCfg2.colabPartners;
                                if (!cfg.others?.length && _localCfg2?.others?.length) bCfg.others = _localCfg2.others;
                                budgetConfig.value = bCfg;
                                localStorage.setItem('ppp_budgetConfig', JSON.stringify(bCfg));
                            }
                            budgetConfigLoaded.value = true;
                            resolve();
                        }).withFailureHandler(() => resolve()).getBudgetingConfig();
                    });
                };

                // Lazy tab data loader: fetches sheet data for a specific tab on first navigation.
                // Uses tabDataLoaded to prevent redundant RPC calls on revisit.
                const loadTabData = (tabName) => {
                    if (tabDataLoaded.value[tabName]) return Promise.resolve();
                    return new Promise((resolve) => {
                        ensureRunApi()
                            .withSuccessHandler(d => {
                                if (d) {
                                    if (Array.isArray(d.unboxing)) unboxingData.value = d.unboxing;
                                    if (Array.isArray(d.orderanOnline)) orderanOnlineData.value = d.orderanOnline;
                                    if (Array.isArray(d.unitDitanya)) unitDitanyaData.value = d.unitDitanya;
                                    if (Array.isArray(d.claimGaransi)) claimGaransiData.value = d.claimGaransi;
                                    if (Array.isArray(d.promo)) promoData.value = d.promo;
                                    if (Array.isArray(d.sellOut)) sellOutData.value = d.sellOut;
                                    if (Array.isArray(d.hargaKompetitor)) hargaKompetitorData.value = d.hargaKompetitor;
                                    if (Array.isArray(d.lpjk)) lpjkData.value = d.lpjk;
                                    if (Array.isArray(d.ads)) adsData.value = d.ads;
                                    if (Array.isArray(d.keepBarang)) { keepBarangData.value = d.keepBarang; keepBarangLoaded.value = true; }
                                    if (Array.isArray(d.calendarEvents)) calendarEventsData.value = d.calendarEvents;
                                }
                                tabDataLoaded.value = Object.assign({}, tabDataLoaded.value, { [tabName]: true });
                                resolve();
                            })
                            .withFailureHandler(() => resolve())
                            .getTabData(tabName);
                    });
                };

                const saveBonusConfig = () => {
                    const cfg = JSON.parse(JSON.stringify(bonusConfig.value));
                    ensureRunApi().withSuccessHandler(() => {
                        localStorage.setItem('ppp_bonusConfig', JSON.stringify(cfg));
                        showBonusSettings.value = false;
                        showNotification('Konfigurasi bonus disimpan');
                    }).withFailureHandler(() => {
                        localStorage.setItem('ppp_bonusConfig', JSON.stringify(cfg));
                        showBonusSettings.value = false;
                        showNotification('Disimpan lokal (server tidak tersedia)');
                    }).saveBonusConfig(cfg);
                };

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

                // Laporan Event (LPJK)
                const filteredLpjkData = computed(() => {
                    if (!lpjkSearch.value) return lpjkData.value;
                    const q = lpjkSearch.value.toLowerCase();
                    return lpjkData.value.filter(r => (r.Nama_Event || '').toLowerCase().includes(q));
                });
                const lpjkTotalPages = computed(() => Math.max(1, Math.ceil(filteredLpjkData.value.length / 20)));
                const pagedLpjkData = computed(() => {
                    const p = lpjkPage.value;
                    return filteredLpjkData.value.slice((p - 1) * 20, p * 20);
                });
                const lpjkDetailGrouped = computed(() => {
                    const result = {};
                    for (const item of lpjkDetailData.value) {
                        const cat = item.Kategori || 'Lainnya';
                        if (!result[cat]) result[cat] = [];
                        result[cat].push(item);
                    }
                    return result;
                });
                const lpjkDetailTotal = computed(() => lpjkDetailData.value.reduce((s, i) => s + (Number(i.Total) || 0), 0));
                watch(lpjkSearch, () => { lpjkPage.value = 1; });

                const openLpjkModal = (type = 'create', row = null) => {
                    lpjkModalType.value = type;
                    lpjkForm.value = row ? { ...row } : { ID: null, Nama_Event: '', Tanggal: todayStr(), Budget_Rencana: 0, Realisasi_Biaya: 0, Selisih: 0, Status: 'DRAFT', Keterangan: '' };
                    lpjkModalOpen.value = true;
                };

                const saveLpjk = () => {
                    const form = lpjkForm.value;
                    form.Selisih = (form.Budget_Rencana || 0) - (form.Realisasi_Biaya || 0);
                    submitting.value = true;
                    ensureRunApi()
                        .withSuccessHandler(res => {
                            submitting.value = false;
                            if (!form.ID) {
                                lpjkData.value.unshift({ ...form, ID: (res && res.id) || ('LJ' + Date.now()) });
                            } else {
                                const idx = lpjkData.value.findIndex(r => String(r.ID) === String(form.ID));
                                if (idx !== -1) lpjkData.value.splice(idx, 1, { ...form });
                            }
                            lpjkModalOpen.value = false;
                            showNotification('Data event disimpan');
                        })
                        .withFailureHandler(err => { submitting.value = false; handleError(err); })
                        .saveLpjk(form);
                };

                const deleteLpjk = (id) => {
                    if (!confirm('Hapus event ini?')) return;
                    ensureRunApi()
                        .withSuccessHandler(() => {
                            lpjkData.value = lpjkData.value.filter(r => String(r.ID) !== String(id));
                            showNotification('Event dihapus');
                        })
                        .withFailureHandler(err => handleError(err))
                        .deleteLpjk(id);
                };

                const openLpjkDetail = (row) => {
                    activeLpjkRow.value = row;
                    lpjkDetailItem.value = { Master_ID: row.ID, Kategori: 'Konsumsi', Nama_Pengeluaran: '', Satuan: 0, Jumlah: 1, Total: 0, Bukti: '' };
                    lpjkDetailData.value = [];
                    lpjkDetailModalOpen.value = true;
                    ensureRunApi()
                        .withSuccessHandler(data => { lpjkDetailData.value = Array.isArray(data) ? data : []; })
                        .withFailureHandler(() => { })
                        .getLpjkDetailData(row.ID);
                };

                const closeLpjkDetail = () => {
                    lpjkDetailModalOpen.value = false;
                    activeLpjkRow.value = null;
                    lpjkDetailData.value = [];
                };

                const saveLpjkDetail = () => {
                    const item = lpjkDetailItem.value;
                    item.Total = (item.Satuan || 0) * (item.Jumlah || 0);
                    submitting.value = true;
                    ensureRunApi()
                        .withSuccessHandler(res => {
                            submitting.value = false;
                            const saved = { ...item, ID: (res && res.id) || ('LD' + Date.now()) };
                            lpjkDetailData.value.push(saved);
                            const newTotal = lpjkDetailData.value.reduce((s, i) => s + (Number(i.Total) || 0), 0);
                            const lpjkRow = lpjkData.value.find(r => String(r.ID) === String(item.Master_ID));
                            if (lpjkRow) { lpjkRow.Realisasi_Biaya = newTotal; lpjkRow.Selisih = (lpjkRow.Budget_Rencana || 0) - newTotal; }
                            lpjkDetailItem.value = { Master_ID: item.Master_ID, Kategori: item.Kategori, Nama_Pengeluaran: '', Satuan: 0, Jumlah: 1, Total: 0, Bukti: '' };
                            showNotification('Item ditambahkan');
                        })
                        .withFailureHandler(err => { submitting.value = false; handleError(err); })
                        .saveLpjkDetail(item);
                };

                const deleteLpjkDetail = (id) => {
                    ensureRunApi()
                        .withSuccessHandler(() => {
                            lpjkDetailData.value = lpjkDetailData.value.filter(r => String(r.ID) !== String(id));
                            const newTotal = lpjkDetailData.value.reduce((s, i) => s + (Number(i.Total) || 0), 0);
                            if (activeLpjkRow.value) {
                                const lpjkRow = lpjkData.value.find(r => String(r.ID) === String(activeLpjkRow.value.ID));
                                if (lpjkRow) { lpjkRow.Realisasi_Biaya = newTotal; lpjkRow.Selisih = (lpjkRow.Budget_Rencana || 0) - newTotal; }
                            }
                            showNotification('Item dihapus');
                        })
                        .withFailureHandler(err => handleError(err))
                        .deleteLpjkDetail(id);
                };

                // Budgeting
                const budgetCalculations = computed(() => {
                    const cfg = budgetConfig.value;
                    const safe = (v) => Number(v) || 0;
                    const metaTotal = safe(cfg.meta.costPerAd) * safe(cfg.meta.totalAds) * safe(cfg.meta.days);
                    const metaTopup = Math.max(0, metaTotal - safe(cfg.meta.balance));
                    const googleTotal = safe(cfg.google.costPerAd) * safe(cfg.google.totalAds) * safe(cfg.google.days);
                    const googleTopup = Math.max(0, googleTotal - safe(cfg.google.balance));
                    const mekariVisitorTotal = safe(cfg.mekari.visitor.targetPerDay) * safe(cfg.mekari.visitor.days);
                    const mekariVisitorNeeded = Math.max(0, mekariVisitorTotal - safe(cfg.mekari.visitor.balance));
                    const mekariBroadcastTotal = (safe(cfg.mekari.broadcast.costPerWeek) * safe(cfg.mekari.broadcast.weeks)) + safe(cfg.mekari.broadcast.specialPrice);
                    const mekariBroadcastTopup = Math.max(0, mekariBroadcastTotal - safe(cfg.mekari.broadcast.balance));
                    const mekariTopupTotal = safe(cfg.mekari.visitor.topupCost) + mekariBroadcastTopup;
                    const othersCalculated = (cfg.others || []).map(item => {
                        const total = safe(item.costPerUnit) * safe(item.quantity) * safe(item.duration);
                        const topup = Math.max(0, total - safe(item.balance));
                        return { ...item, total, topup };
                    });
                    const dStart = budgetDateFilter.value.start;
                    const dEnd = budgetDateFilter.value.end;
                    const inRange = (row) => {
                        const d = row.Tanggal_Rencana || '';
                        if (dStart && d < dStart) return false;
                        if (dEnd && d > dEnd) return false;
                        return true;
                    };
                    const colabBreakdown = (cfg.colabPartners || []).map(p => {
                        const used = masterPlanData.value.filter(row => {
                            const names = (row.Colab || '').split(',').map(c => c.trim()).filter(Boolean);
                            return names.includes(p.name) && inRange(row);
                        }).length;
                        const remaining = safe(p.slots) - used;
                        return { name: p.name, packageCost: safe(p.packageCost), slots: safe(p.slots), used, remaining };
                    });
                    const colabList = masterPlanData.value.filter(row => row.Colab && inRange(row)).flatMap(row =>
                        (row.Colab || '').split(',').map(c => c.trim()).filter(Boolean).map(partner => ({
                            colabPartner: partner, Judul: row.Judul, Tanggal_Rencana: row.Tanggal_Rencana
                        }))
                    );
                    const colabTopup = (cfg.colabPartners || []).reduce((s, p) => s + safe(p.packageCost), 0);
                    const othersTopup = othersCalculated.reduce((s, i) => s + i.topup, 0);
                    const totalTopup = metaTopup + googleTopup + mekariTopupTotal + colabTopup + othersTopup;
                    return { metaTotal, metaTopup, googleTotal, googleTopup, mekariVisitorTotal, mekariVisitorNeeded, mekariBroadcastTotal, mekariBroadcastTopup, mekariTopupTotal, othersCalculated, colabBreakdown, colabList, totalTopup };
                });

                const budgetSummary = computed(() => {
                    const c = budgetCalculations.value;
                    const cards = [
                        { label: 'Meta Ads', value: formatCurrency(c.metaTotal), icon: 'fa-meta', color: 'text-blue-500', subColor: 'text-blue-600', sub: 'Anggaran iklan' },
                        { label: 'Google Ads', value: formatCurrency(c.googleTotal), icon: 'fa-google', color: 'text-blue-500', subColor: 'text-blue-600', sub: 'Anggaran iklan' },
                        { label: 'Mekari', value: formatCurrency(c.mekariVisitorTotal + c.mekariBroadcastTotal), icon: 'fa-bullhorn', color: 'text-amber-500', subColor: 'text-amber-600', sub: 'Ecosystem total' },
                        { label: 'Colab', value: formatNumber(c.colabBreakdown.length), unit: 'Partner', icon: 'fa-handshake', color: 'text-violet-500', unitColor: 'text-violet-400', subColor: 'text-violet-600', sub: 'Paid collaboration' },
                    ];
                    c.othersCalculated.forEach(o => {
                        cards.push({ label: o.name || 'Other', value: formatCurrency(o.total), icon: 'fa-layer-group', color: 'text-slate-500', subColor: 'text-slate-600', sub: 'Additional platform' });
                    });
                    return { cards, chips: [] };
                });

                const saveBudgetServer = () => {
                    const cfg = JSON.parse(JSON.stringify(budgetConfig.value));
                    localStorage.setItem('ppp_budgetConfig', JSON.stringify(cfg));
                    ensureRunApi()
                        .withSuccessHandler(() => {
                            showBudgetSettings.value = false;
                            showNotification('Konfigurasi budget disimpan');
                        })
                        .withFailureHandler(() => {
                            showBudgetSettings.value = false;
                            showNotification('Disimpan lokal (server tidak tersedia)');
                        })
                        .saveBudgetingConfig(cfg);
                };

                const exportBudgetToExcel = () => {
                    window.MarketingDashboardReportingExports.exportBudgetToExcel({
                        config: budgetConfig.value,
                        calculations: budgetCalculations.value,
                        showNotification,
                        notifyError,
                    });
                };

                const getStaggerStyle = (index = 0, step = 32, maxDelay = 192) => {
                    const safeIndex = Number.isFinite(index) ? Math.max(0, index) : 0;
                    const safeStep = Number.isFinite(step) ? Math.max(0, step) : 32;
                    const safeMaxDelay = Number.isFinite(maxDelay) ? Math.max(0, maxDelay) : 192;
                    return { '--stagger-delay': `${Math.min(safeIndex * safeStep, safeMaxDelay)}ms` };
                };

                // --- Watchers (Moved to end to ensure all functions/refs are initialized) ---

                // Technician Guard
                watch([currentUser, activeTab], ([user, tab]) => {
                    if (isTeknisi.value && !TEKNISI_TABS.has(tab)) {
                        activeTab.value = 'claim_garansi_asuransi';
                        localStorage.setItem("ppp_active_tab", 'claim_garansi_asuransi');
                    }
                }, { immediate: true });

                watch(hasBlockingOverlayOpen, (locked) => {
                    setDocumentScrollLock(locked);
                }, { immediate: true });

                // Tab Navigation & Data Loading
                watch(() => activeTab.value, (newTab) => {
                    const menuGroup = groupForTab(newTab);
                    if (menuGroup) {
                        openMenuGroup(menuGroup);
                    } else {
                        closeAllMenuGroups();
                    }

                    // Mobile auto-close
                    if (window.innerWidth < 768) isSidebarOpen.value = false;

                    // Scroll top
                    const container = document.querySelector('.overflow-y-auto');
                    if (container) container.scrollTop = 0;

                    if (authBootstrapPending.value) {
                        nextTick(() => hydrateSortableTableHeaders());
                        return;
                    }

                    if (newTab === 'nama_stock' && !namaStockLoaded.value) {
                        loadNamaStockData();
                    }
                    if (newTab === 'meta_story' && !metaStoryLoaded.value) {
                        loadMetaStory();
                    }
                    if (newTab === 'meta_feed' && !metaFeedLoaded.value) {
                        loadMetaFeed();
                    }
                    if (!currentUser.value) {
                        nextTick(() => hydrateSortableTableHeaders());
                        return;
                    }
                    if (newTab === 'budgeting' && !budgetConfigLoaded.value) {
                        loadBudgetingConfig();
                    }
                    if ((newTab === 'bonus_report' || newTab === 'talent_bonus') && !bonusConfigLoaded.value) {
                        loadBonusConfig();
                    }
                    if (['bonus_report', 'talent_bonus', 'editor_performance'].includes(newTab)) {
                        refreshBonusSourceData();
                    }
                    if (newTab === 'keep_barang' && !keepBarangLoaded.value) {
                        keepBarangLoaded.value = true;
                        loadKeepBarangData();
                    }
                    if (currentUser.value && !settingsLoaded.value && newTab !== 'dashboard') {
                        loadSettings();
                    }
                    if (currentUser.value && newTab === 'settings') {
                        loadSettings();
                    }
                    if (currentUser.value && newTab === 'auth_users') {
                        loadAuthUsers();
                    }
                    if (currentUser.value && newTab === 'activity_logs') {
                        loadActivityLogs();
                    }
                    if (newTab === 'distribution') {
                        loadDistributionData();
                    }
                    if (newTab === 'analytics') {
                        loadAnalyticsData();
                    }

                    const TAB_DATA_MAP = {
                        'unboxing': 'unboxing',
                        'orderan_online': 'orderanOnline',
                        'unit_ditanya': 'unitDitanya',
                        'claim_garansi_asuransi': 'claimGaransi',
                        'program_promo': 'promo',
                        'sell_out': 'sellOut',
                        'laporan_event': 'lpjk',
                        'ads_log': 'ads',
                        'harga_kompetitor': 'hargaKompetitor'
                    };
                    const dataKey = TAB_DATA_MAP[newTab];
                    if (currentUser.value && dataKey) loadTabData(dataKey);
                    nextTick(() => hydrateSortableTableHeaders());
                }, { immediate: true });


@endverbatim
