@verbatim
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

                const formatMonthLabel = window.MarketingDashboardRuntimeHelpers?.formatMonthLabel || ((m) => {
                    const names = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                    const idx = parseInt(m, 10);
                    return names[idx - 1] || String(m || '');
                });

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
@endverbatim
