@verbatim
                const pricelistProducts = ref([]);
                const pricelistProductsLoaded = ref(false);
                const pricelistSyncing = ref(false);
                const pricelistSearch = ref('');
                const pricelistSheetFilter = ref('all');
                const pricelistView = ref(localStorage.getItem('ppp_pricelist_view') || 'card');
                const pricelistPage = ref(1);
                const pricelistSelectedIds = ref([]);
                const pricelistShowSelectedOnly = ref(false);
                try { pricelistSelectedIds.value = JSON.parse(localStorage.getItem('ppp_pricelist_selected_ids') || '[]'); } catch (e) {}
                const pricelistPageSize = 20;
                const catalogTemplates = ref([]);
                const catalogTemplatesLoaded = ref(false);
                const defaultCatalogLayoutConfig = () => ({
                    x: 92,
                    y: 150,
                    width: 896,
                    gap: 42,
                    headerHeight: 40,
                    rowHeight: 34,
                    maxItems: 0,
                    borderRadius: 10,
                    fontFamily: 'Inter, sans-serif',
                    headerFontSize: 13,
                    bodyFontSize: 13,
                    priceFontSize: 13,
                    titleFontSize: 32,
                    titleGap: 16,
                    titleX: 92,
                    titleY: null,
                    bottomSafe: 130,
                    titleColor: '#ffffff',
                    titleStrokeColor: '#000000',
                    titleStrokeWidth: 0,
                    headerColor: '#3f3f3f',
                    headerTextColor: '#ffffff',
                    rowOddColor: 'rgba(255,255,255,0.55)',
                    rowEvenColor: 'rgba(255,255,255,0.08)',
                    textColor: '#3f3f3f',
                    normalPriceColor: '#3f3f3f',
                    specialPriceColor: '#3f3f3f',
                    strikeColor: '#dc2626',
                    priceShape: 'plain',
                    priceBadgeBg: 'rgba(255,255,255,0.18)',
                    showHematBadge: true,
                    cardColumns: 4,
                    card_config: {
                        imageHeight: 118,
                        modelFontSize: 14,
                        priceFontSize: 13,
                        srpFontSize: 11,
                        rowGap: 5,
                        modelColor: '#ffffff',
                        labelColor: 'rgba(255,255,255,0.6)',
                        badgeTextColor: '#ffffff',
                        priceColor: '#ffffff',
                        srpColor: 'rgba(255,255,255,0.5)',
                        priceShape: 'plain',
                        priceBadgeBg: 'rgba(255,255,255,0.18)',
                        showHematBadge: true,
                        hematBadgeBg: '#16a34a',
                        hematBadgeColor: '#ffffff',
                    },
                    assets: [],
                    textBoxes: [],
                    creditMode: 'text',
                    creditAssetId: '',
                    layout_version: 5,
                });
                let initialModalOpen = false;
                let initialModalType = 'create';
                let initialForm = null;
                try {
                    if (localStorage.getItem('ppp_catalog_template_modal_open') === 'true') {
                        initialModalOpen = true;
                        initialModalType = localStorage.getItem('ppp_catalog_template_modal_type') || 'create';
                        const savedForm = localStorage.getItem('ppp_catalog_template_form');
                        if (savedForm) {
                            const parsedForm = JSON.parse(savedForm);
                            if (Number(parsedForm?.layout_config?.layout_version || 0) >= 5) {
                                initialForm = parsedForm;
                            } else {
                                localStorage.removeItem('ppp_catalog_template_modal_open');
                                localStorage.removeItem('ppp_catalog_template_modal_type');
                                localStorage.removeItem('ppp_catalog_template_form');
                            }
                        }
                    }
                } catch (e) {}

                const catalogTemplateForm = ref(initialForm || {
                    ID: '',
                    name: '',
                    format: 'story',
                    output_mode: 'list',
                    layout_config: defaultCatalogLayoutConfig(),
                    is_active: true,
                });
                const catalogTemplateModalOpen = ref(initialModalOpen);
                const catalogTemplateModalType = ref(initialModalType);

                let catalogPersistTimer = null;
                const schedulePersistCatalogTemplate = () => {
                    if (catalogPersistTimer) clearTimeout(catalogPersistTimer);
                    catalogPersistTimer = setTimeout(() => {
                        try {
                            if (catalogTemplateModalOpen.value) {
                                localStorage.setItem('ppp_catalog_template_modal_open', 'true');
                                localStorage.setItem('ppp_catalog_template_modal_type', catalogTemplateModalType.value);
                                localStorage.setItem('ppp_catalog_template_form', JSON.stringify(catalogTemplateForm.value));
                            } else {
                                localStorage.removeItem('ppp_catalog_template_modal_open');
                                localStorage.removeItem('ppp_catalog_template_modal_type');
                                localStorage.removeItem('ppp_catalog_template_form');
                            }
                        } catch (e) {}
                    }, 400);
                };

                Vue.watch([catalogTemplateModalOpen, catalogTemplateModalType, catalogTemplateForm], () => {
                    if (catalogLayoutDrag.value) return;
                    schedulePersistCatalogTemplate();
                }, { deep: true });
                const catalogSelectedTemplateId = ref(localStorage.getItem('ppp_pricelist_template_id') || '');
                const catalogSelectedSheet = ref(localStorage.getItem('ppp_pricelist_selected_sheet') || 'all');
                const catalogPriceKey = ref(localStorage.getItem('ppp_pricelist_price_key') || 'special_price'); // 'special_price' | 'harga_jual'
                const catalogBrandPriceKeys = ref({});
                try { catalogBrandPriceKeys.value = JSON.parse(localStorage.getItem('ppp_pricelist_brand_price_keys') || '{}'); } catch (e) {}
                const catalogOutputMode = ref(localStorage.getItem('ppp_pricelist_output_mode') || 'list'); // 'list' | 'katalog'
                const catalogColumnsPerRow = ref(Number(localStorage.getItem('ppp_pricelist_columns_per_row') || 3));
                const catalogTemplateFormatOptions = [
                    { key: 'story', label: 'Story 1080x1920' },
                    { key: 'feed', label: 'Feed 1080x1350' },
                    { key: 'a4', label: 'A4 1240x1754' },
                ];
                const catalogColumnOptions = [2, 3, 4, 5];
                const catalogTemplateFormatLabel = computed(() => catalogTemplateFormatOptions.find((opt) => opt.key === catalogTemplateForm.value.format)?.label || 'Pilih format');
                const catalogColumnsPerRowLabel = computed(() => `${catalogColumnsPerRow.value || 3} kolom`);
                const catalogConditionLabel = (priceKey = 'special_price') => ({
                    special_price: 'SPECIAL PRICE',
                    harga_jual: 'HARGA JUAL',
                    harga_nasional: 'HARGA NASIONAL',
                }[priceKey] || String(priceKey || 'HARGA').replaceAll('_', ' ').toUpperCase());
                const catalogEditorPreviewTitle = computed(() => ({
                    category: String(catalogPreviewRows.value?.[0]?.source_sheet || 'KATEGORI').trim().toUpperCase(),
                    condition: catalogConditionLabel(catalogPriceKey.value),
                }));
                const defaultCatalogCardConfig = () => ({
                    imageHeight: 118,
                    modelFontSize: 14,
                    priceFontSize: 13,
                    srpFontSize: 11,
                    rowGap: 5,
                    modelColor: '#ffffff',
                    labelColor: 'rgba(255,255,255,0.6)',
                    badgeTextColor: '#ffffff',
                    priceColor: '#ffffff',
                    srpColor: 'rgba(255,255,255,0.5)',
                });
                const catalogCardCfg = ref({ ...defaultCatalogCardConfig() });
                try {
                    const saved = JSON.parse(localStorage.getItem('ppp_pricelist_card_cfg') || '{}');
                    catalogCardCfg.value = { ...defaultCatalogCardConfig(), ...saved };
                } catch (e) {}
                const catalogUrutStart = ref(localStorage.getItem('ppp_pricelist_urut_start') || '');
                const catalogUrutEnd = ref(localStorage.getItem('ppp_pricelist_urut_end') || '');
                const catalogGenerating = ref(false);
                const catalogPreviewImages = ref([]);
                const catalogTemplateEditorPreviewImage = ref('');
                const catalogPreviewModalOpen = ref(false);
                const catalogPreviewModalIndex = ref(0);
                const catalogPreviewZoom = ref(1);
                const catalogLayoutDrag = ref(null);
                const androidImageMap = ref({});

                const pricelistBrandSheets = ['SAMSUNG', 'XIAOMI', 'OPPO', 'VIVO', 'HUAWEI', 'TECNO', 'INFINIX', 'NUBIA', 'REALME', 'ITEL', 'HONOR'];
                const filteredPricelistBrandSheetOptions = computed(() => {
                    const query = String(searchSelectQuery.value || '').toLowerCase();
                    return pricelistBrandSheets.filter((sheet) => sheet.toLowerCase().includes(query));
                });
                const filteredCatalogBrandSheetOptions = computed(() => filteredPricelistBrandSheetOptions.value);
                const currentPricelistSheetFilterLabel = computed(() => pricelistSheetFilter.value === 'all' ? 'Semua Brand' : pricelistSheetFilter.value);
                const currentCatalogSheetLabel = computed(() => catalogSelectedSheet.value === 'all' ? 'Semua Brand' : catalogSelectedSheet.value);

                watch(catalogCardCfg, (cfg) => {
                    localStorage.setItem('ppp_pricelist_card_cfg', JSON.stringify(cfg));
                }, { deep: true });

                watch(catalogSelectedTemplateId, (v) => localStorage.setItem('ppp_pricelist_template_id', v || ''));
                watch(catalogSelectedSheet, (v) => localStorage.setItem('ppp_pricelist_selected_sheet', v || ''));
                 watch(pricelistView, (v) => localStorage.setItem('ppp_pricelist_view', v || 'table'));
                 watch(pricelistSelectedIds, (v) => localStorage.setItem('ppp_pricelist_selected_ids', JSON.stringify(v || [])), { deep: true });
                 const pricelistRowKey = (row) => String(row.ID || catalogDedupeKeyForRow(row));
                 const pricelistIsSelected = (row) => pricelistSelectedIds.value.includes(pricelistRowKey(row));
                 const pricelistToggleSelected = (row) => {
                     const key = pricelistRowKey(row);
                     pricelistSelectedIds.value = pricelistIsSelected(row) ? pricelistSelectedIds.value.filter((id) => id !== key) : [...pricelistSelectedIds.value, key];
                 };
                 const pricelistSelectAll = () => { pricelistSelectedIds.value = filteredPricelistProducts.value.map(pricelistRowKey); };
                 const pricelistDeselectAll = () => { pricelistSelectedIds.value = []; };
                watch(catalogPriceKey, (v) => localStorage.setItem('ppp_pricelist_price_key', v || ''));
                watch(catalogBrandPriceKeys, (v) => localStorage.setItem('ppp_pricelist_brand_price_keys', JSON.stringify(v || {})), { deep: true });
                watch(catalogOutputMode, (v) => localStorage.setItem('ppp_pricelist_output_mode', v || 'list'));
                watch(catalogColumnsPerRow, (v) => localStorage.setItem('ppp_pricelist_columns_per_row', String(v)));
                watch(catalogUrutStart, (v) => localStorage.setItem('ppp_pricelist_urut_start', v || ''));
                watch(catalogUrutEnd, (v) => localStorage.setItem('ppp_pricelist_urut_end', v || ''));

                const catalogDedupeKeyForRow = (row) => [
                    row.source_sheet,
                    row.nama_produk,
                    row.storage,
                    row.ram,
                    row.warna,
                    row.harga_nasional,
                    row.special_price,
                    row.harga_jual,
                ].map((value) => String(value || '').toUpperCase().replace(/\s+/g, ' ').trim()).join('|');
                const catalogDedupeRows = (rows) => {
                    const seen = new Map();
                    const result = [];
                    (Array.isArray(rows) ? rows : []).forEach((row) => {
                        const key = catalogDedupeKeyForRow(row);
                        const existing = seen.get(key);
                        if (!existing) {
                            seen.set(key, row);
                            result.push(row);
                            return;
                        }
                        if (Number(row.source_row ?? 0) < Number(existing.source_row ?? 0)) {
                            seen.set(key, row);
                            result[result.indexOf(existing)] = row;
                        }
                    });
                    return result;
                };

                const loadPricelistProducts = () => {
                    ensureRunApi()
                        .withSuccessHandler((rows) => {
                            pricelistProducts.value = catalogDedupeRows(rows);
                            pricelistProductsLoaded.value = true;
                            pricelistPage.value = 1;
                        })
                        .withFailureHandler((error) => notifyError('', error, 'Gagal memuat pricelist.'))
                        .getPricelistProducts();
                };

                const loadCatalogTemplates = () => {
                    ensureRunApi()
                        .withSuccessHandler((rows) => {
                            catalogTemplates.value = (Array.isArray(rows) ? rows : []).map((template) => ({
                                ...template,
                                layout_config: normalizeCatalogTemplateLayout(template),
                            }));
                            catalogTemplatesLoaded.value = true;
                            if (!catalogSelectedTemplateId.value && catalogTemplates.value.length) {
                                catalogSelectedTemplateId.value = catalogTemplates.value[0].ID;
                            }
                        })
                        .withFailureHandler((error) => notifyError('', error, 'Gagal memuat template katalog.'))
                        .getCatalogTemplates();
                };

                const androidModelKey = (name) => String(name || '')
                    .toUpperCase()
                    .replace(/[()]/g, '')
                    .replace(/[._-]+/g, ' ')
                    .replace(/\s+/g, ' ')
                    .trim();
                const androidNormalizeModelForImage = (name, brand = '') => {
                    let text = androidModelKey(name)
                        .replace(/PROMAX/g, 'PRO MAX')
                        .replace(/\b(\d+GB|\d+TB)\s*\/\s*(\d+GB|\d+TB)\b/g, ' ')
                        .replace(/\b\d+GB\b/g, ' ')
                        .replace(/\b\d+TB\b/g, ' ')
                        .replace(/\bRAM\b/g, ' ')
                        .replace(/\s+/g, ' ')
                        .trim();
                    const brandText = androidModelKey(brand);
                    if (brandText && text.startsWith(brandText + ' ')) {
                        text = text.slice(brandText.length).trim();
                    }
                    return text;
                };
                const loadAndroidImages = (brand) => {
                    const key = String(brand || '').toUpperCase();
                    if (!key || androidImageMap.value[key]) return;
                    fetch(window.MarketingDashboardRuntimeHelpers.resolveAppUrl(`/api/android-images/${encodeURIComponent(key)}`))
                        .then((r) => r.json())
                        .then((json) => {
                            const map = {};
                            (json.data || []).forEach((item) => {
                                const modelKey = androidModelKey(item.model);
                                const normalizedKey = androidNormalizeModelForImage(item.model, key);
                                map[modelKey] = [...(map[modelKey] || []), ...(item.images || [])];
                                map[normalizedKey] = [...(map[normalizedKey] || []), ...(item.images || [])];
                            });
                            androidImageMap.value = { ...androidImageMap.value, [key]: map };
                        })
                        .catch(() => {});
                };
                const catalogPreviewImageBrand = (row) => {
                    const name = String(row?.nama_produk || row?.model || '').toUpperCase();
                    if (name.includes('IPHONE')) return 'IPHONE';
                    if (name.includes('SAMSUNG')) return 'SAMSUNG';
                    if (name.includes('OPPO')) return 'OPPO';
                    if (name.includes('VIVO')) return 'VIVO';
                    if (name.includes('XIAOMI')) return 'XIAOMI';
                    return String(row?.source_sheet || row?.brand || 'SAMSUNG').toUpperCase();
                };

                const catalogImageIndexForRow = (row, imageCount) => {
                    if (!imageCount || imageCount < 2) return 0;
                    const identity = [row?.ID, row?.source_sheet, row?.nama_produk, row?.ram, row?.storage, row?.warna]
                        .map((value) => String(value || ''))
                        .join('|');
                    let hash = 0;
                    for (let index = 0; index < identity.length; index += 1) {
                        hash = ((hash << 5) - hash) + identity.charCodeAt(index);
                        hash |= 0;
                    }
                    return Math.abs(hash) % imageCount;
                };
                const catalogImageForRow = (row, images) => {
                    if (!Array.isArray(images) || !images.length) return null;
                    return images[catalogImageIndexForRow(row, images.length)] || null;
                };
                const androidProductImage = (row) => {
                    const explicitBrand = String(row.source_sheet || row.brand || '').toUpperCase();
                    const inferredBrand = catalogPreviewImageBrand(row);
                    const brand = (explicitBrand && explicitBrand !== 'SAMPLE' && explicitBrand !== 'ALL') ? explicitBrand : inferredBrand;
                    const map = androidImageMap.value[brand] || {};
                    const productKey = androidModelKey(catalogProductType(row));
                    const fullKey = androidModelKey(row.nama_produk);
                    const normalizedProduct = androidNormalizeModelForImage(catalogProductType(row), brand);
                    const normalizedFull = androidNormalizeModelForImage(row.nama_produk, brand);
                    const candidates = [normalizedProduct, normalizedFull, productKey, fullKey].filter(Boolean);

                    if (!Object.keys(map).length && brand !== 'IPHONE') {
                        loadAndroidImages(brand);
                        return null;
                    }

                    // tier 1: exact key match
                    const exact = map[normalizedProduct] || map[normalizedFull] || map[productKey] || map[fullKey];
                    if (exact?.length) return catalogImageForRow(row, exact);

                    // tier 2: image folder key starts with product name (handles color/variant suffix)
                    const mapKeys = Object.keys(map);
                    for (const candidate of candidates) {
                        const match = mapKeys.find((key) => key === candidate || key.startsWith(candidate + ' '));
                        if (match) return catalogImageForRow(row, map[match]);
                    }

                    // tier 3: product name starts with image folder key (handles image key being shorter/base model)
                    for (const candidate of candidates) {
                        const match = mapKeys
                            .filter((key) => candidate.startsWith(key + ' ') || candidate === key)
                            .sort((a, b) => b.length - a.length)[0];
                        if (match) return catalogImageForRow(row, map[match]);
                    }

                    if (brand === 'IPHONE') {
                        if (typeof appleLoadImages === 'function' && (!appleImageMap.value || !appleImageMap.value.IPHONE)) {
                            appleLoadImages('IPHONE');
                        }
                        const appleMap = (typeof appleImageMap !== 'undefined' && appleImageMap.value?.IPHONE) ? appleImageMap.value.IPHONE : {};
                        const appleKeys = Object.keys(appleMap);
                        for (const candidate of candidates) {
                            if (appleMap[candidate]?.length) return catalogImageForRow(row, appleMap[candidate]);
                            const match = appleKeys.find((key) => key === candidate || key.startsWith(candidate + ' ') || candidate.startsWith(key + ' '));
                            if (match && appleMap[match]?.length) return catalogImageForRow(row, appleMap[match]);
                        }
                    }

                    return null;
                };

                const loadPricelistCatalogData = () => {
                    if (!pricelistProductsLoaded.value) loadPricelistProducts();
                    if (!catalogTemplatesLoaded.value) loadCatalogTemplates();
                    pricelistBrandSheets.forEach(loadAndroidImages);
                };

                const syncPricelistProducts = () => {
                    pricelistSyncing.value = true;
                    ensureRunApi()
                        .withSuccessHandler((summary) => {
                            showNotification(`Sync selesai: ${summary?.imported || 0} item masuk`);
                            loadPricelistProducts();
                        })
                        .withFailureHandler((error) => notifyError('', error, 'Gagal sync pricelist dari spreadsheet.'))
                        .syncPricelistProducts();
                    window.setTimeout(() => { pricelistSyncing.value = false; }, 1200);
                };

                const filteredPricelistProducts = computed(() => {
                    const query = pricelistSearch.value.trim().toLowerCase();
                    const sheet = pricelistSheetFilter.value;
                    return pricelistProducts.value.filter((row) => {
                        if (pricelistShowSelectedOnly.value && !pricelistIsSelected(row)) return false;
                        if (sheet !== 'all' && row.source_sheet !== sheet) return false;
                        if (!query) return true;
                        return [row.source_sheet, row.brand, row.kategori, row.nama_produk, row.ram, row.storage, row.warna]
                            .some((value) => String(value || '').toLowerCase().includes(query));
                    });
                });

                const pricelistTotalPages = computed(() => Math.max(1, Math.ceil(filteredPricelistProducts.value.length / pricelistPageSize)));
                const pagedPricelistProducts = computed(() => filteredPricelistProducts.value.slice((pricelistPage.value - 1) * pricelistPageSize, pricelistPage.value * pricelistPageSize));
                const pricelistCardGroups = computed(() => catalogAndroidCardGroups(filteredPricelistProducts.value));
                const pricelistCardVariantLabel = (row) => [row.ram, row.storage, row.warna].filter(Boolean).join(' · ') || row.kategori || row.source_sheet || 'Varian';

                const catalogA4AutoTemplate = () => ({
                    ID: 'a4_auto',
                    name: 'A4 Auto',
                    format: 'a4',
                    canvas_width: 1240,
                    canvas_height: 1754,
                    layout_config: defaultCatalogLayoutConfig(),
                    is_active: true,
                });
                const catalogTemplateOptions = computed(() => [catalogA4AutoTemplate(), ...catalogTemplates.value]);
                const filteredCatalogTemplateOptions = computed(() => {
                    const query = String(searchSelectQuery.value || '').toLowerCase();
                    return catalogTemplateOptions.value.filter((template) => String(template.name || '').toLowerCase().includes(query));
                });
                const catalogSelectedTemplate = computed(() => catalogTemplateOptions.value.find((template) => template.ID === catalogSelectedTemplateId.value) || null);
                const currentCatalogTemplateLabel = computed(() => catalogSelectedTemplate.value?.name || 'A4 Auto');
                watch(catalogSelectedTemplate, (template) => {
                    if (template?.output_mode && template.ID !== 'a4_auto') catalogOutputMode.value = template.output_mode;
                }, { immediate: true });
                const catalogRowsForGeneration = computed(() => {
                    const start = Number(catalogUrutStart.value || 0);
                    const end = Number(catalogUrutEnd.value || 0);
                    return catalogDedupeRows(pricelistProducts.value
                        .filter((row) => row.is_active !== false)
                        .filter((row) => catalogSelectedSheet.value === 'all' || row.source_sheet === catalogSelectedSheet.value)
                        .filter((row) => !start || Number(row.urut || 0) >= start)
                        .filter((row) => !end || Number(row.urut || 0) <= end)
                        .filter((row) => !pricelistSelectedIds.value.length || pricelistIsSelected(row))
                        .sort((a, b) => String(a.source_sheet).localeCompare(String(b.source_sheet)) || Number(a.urut || 0) - Number(b.urut || 0)));
                });
                const catalogPreviewRows = computed(() => {
                    const fallbackRows = [
                        { source_sheet: 'IPHONE', nama_produk: 'IPHONE 15 PRO', ram: '8GB', storage: '256GB', harga_nasional: 18999000, special_price: 17999000, harga_jual: 17999000 },
                        { source_sheet: 'IPHONE', nama_produk: 'IPHONE 15 PRO MAX', ram: '8GB', storage: '256GB', harga_nasional: 21999000, special_price: 20499000, harga_jual: 20499000 },
                        { source_sheet: 'IPHONE', nama_produk: 'IPHONE 16', ram: '8GB', storage: '128GB', harga_nasional: 14999000, special_price: 13999000, harga_jual: 13999000 },
                        { source_sheet: 'IPHONE', nama_produk: 'IPHONE 16 PRO', ram: '8GB', storage: '128GB', harga_nasional: 17999000, special_price: 16999000, harga_jual: 16999000 },
                        { source_sheet: 'IPHONE', nama_produk: 'IPHONE 16 PRO MAX', ram: '8GB', storage: '256GB', harga_nasional: 21999000, special_price: 20999000, harga_jual: 20999000 },
                    ];
                    return (catalogRowsForGeneration.value.length ? catalogRowsForGeneration.value : fallbackRows).slice(0, 5);
                });
                const catalogCardPreviewRows = computed(() => [
                    { source_sheet: 'IPHONE', nama_produk: 'IPHONE 15 PRO', ram: '8GB', storage: '256GB', harga_nasional: 18999000, special_price: 17999000, harga_jual: 17999000 },
                    { source_sheet: 'IPHONE', nama_produk: 'IPHONE 15 PRO MAX', ram: '8GB', storage: '256GB', harga_nasional: 21999000, special_price: 20499000, harga_jual: 20499000 },
                    { source_sheet: 'IPHONE', nama_produk: 'IPHONE 16', ram: '8GB', storage: '128GB', harga_nasional: 14999000, special_price: 13999000, harga_jual: 13999000 },
                    { source_sheet: 'IPHONE', nama_produk: 'IPHONE 16 PRO', ram: '8GB', storage: '128GB', harga_nasional: 17999000, special_price: 16999000, harga_jual: 16999000 },
                    { source_sheet: 'IPHONE', nama_produk: 'IPHONE 16 PRO MAX', ram: '8GB', storage: '256GB', harga_nasional: 21999000, special_price: 20999000, harga_jual: 20999000 },
                ]);

                const catalogPriceKeyForSheet = (sheet = null) => {
                    if (catalogSelectedSheet.value === 'all' && sheet && catalogBrandPriceKeys.value[sheet]) {
                        return catalogBrandPriceKeys.value[sheet];
                    }
                    return catalogPriceKey.value;
                };

                const normalizeCatalogTemplateLayout = (template) => {
                    const current = template?.layout_config || {};
                    const legacyLayout = Number(current.layout_version || 0) < 5;
                    const defaults = defaultCatalogLayoutConfig();
                    const layout = { ...defaults, ...current };

                    if (legacyLayout) {
                        layout.x = defaults.x;
                        layout.y = defaults.y;
                        layout.width = defaults.width;
                        layout.gap = defaults.gap;
                        layout.headerHeight = defaults.headerHeight;
                        layout.rowHeight = defaults.rowHeight;
                        layout.titleFontSize = defaults.titleFontSize;
                        layout.titleX = defaults.titleX;
                        layout.titleY = defaults.titleY;
                        layout.bottomSafe = defaults.bottomSafe;
                        layout.card_config = {
                            ...defaultCatalogCardConfig(),
                            ...(current.card_config || {}),
                            imageHeight: defaults.card_config.imageHeight,
                            modelFontSize: defaults.card_config.modelFontSize,
                            priceFontSize: defaults.card_config.priceFontSize,
                            srpFontSize: defaults.card_config.srpFontSize,
                            rowGap: defaults.card_config.rowGap,
                        };
                        layout.layout_version = defaults.layout_version;
                    }

                    return layout;
                };
                const openCatalogTemplateModal = (mode = 'create', template = null) => {
                    loadAndroidImages('IPHONE');
                    if (typeof appleLoadImages === 'function') appleLoadImages('IPHONE');
                    catalogTemplateModalType.value = mode;
                    catalogTemplateForm.value = template ? {
                        output_mode: 'list',
                        ...JSON.parse(JSON.stringify(template)),
                        layout_config: normalizeCatalogTemplateLayout(template),
                    } : {
                        ID: '',
                        name: '',
                        format: 'story',
                        layout_config: defaultCatalogLayoutConfig(),
                        is_active: true,
                    };
                    catalogTemplateModalOpen.value = true;
                };

                const catalogAssetPreviewStyle = (asset, template, assetIndex = null) => {
                    const size = catalogCanvasSize(template);
                    const rot = Number(asset.rotation || 0);
                    const sx = asset.flipX ? -1 : 1;
                    const sy = asset.flipY ? -1 : 1;
                    const assets = template?.layout_config?.assets || [];
                    const idx = assetIndex !== null ? Number(assetIndex) : assets.findIndex((a) => a.id === asset.id);
                    const total = assets.length || 1;
                    const z = 1 + Math.max(0, total - (idx >= 0 ? idx : 0));
                    return {
                        left: `${(Number(asset.x || 0) / size.width) * 100}%`,
                        top: `${(Number(asset.y || 0) / size.height) * 100}%`,
                        width: `${(Number(asset.width || 120) / size.width) * 100}%`,
                        height: `${(Number(asset.height || 120) / size.height) * 100}%`,
                        transform: `rotate(${rot}deg) scale(${sx}, ${sy})`,
                        transformOrigin: 'center center',
                        zIndex: z,
                        willChange: 'left, top, transform',
                        userSelect: 'none',
                        pointerEvents: 'auto',
                    };
                };
                const catalogAssetStyle = catalogAssetPreviewStyle;
                const catalogToggleFlip = (asset, axis) => {
                    if (axis === 'x') asset.flipX = !asset.flipX;
                    if (axis === 'y') asset.flipY = !asset.flipY;
                    schedulePersistCatalogTemplate();
                };
                const catalogOnAssetImageLoad = (event, asset) => {
                    const nw = event.target?.naturalWidth;
                    const nh = event.target?.naturalHeight;
                    if (!nw || !nh) return;
                    asset.naturalWidth = nw;
                    asset.naturalHeight = nh;
                    const currentRatio = Number(asset.width || 1) / Number(asset.height || 1);
                    const realRatio = nw / nh;
                    if (Math.abs(currentRatio - realRatio) > 0.05) {
                        const w = Number(asset.width || 160);
                        asset.height = Math.round(w / realRatio);
                        schedulePersistCatalogTemplate();
                    }
                };
                const catalogAddAsset = (event) => {
                    const files = Array.from(event?.target?.files || []);
                    if (!files.length) return;
                    files.forEach((file) => {
                        if (!file.type.startsWith('image/')) return;
                        const reader = new FileReader();
                        reader.onload = () => {
                            const img = new Image();
                            img.onload = () => {
                                const nw = img.naturalWidth || 160;
                                const nh = img.naturalHeight || 160;
                                const maxDim = 180;
                                let initW = nw;
                                let initH = nh;
                                if (initW > maxDim || initH > maxDim) {
                                    if (initW >= initH) {
                                        initH = Math.round((nh * maxDim) / nw);
                                        initW = maxDim;
                                    } else {
                                        initW = Math.round((nw * maxDim) / nh);
                                        initH = maxDim;
                                    }
                                }
                                const assets = [...(catalogTemplateForm.value.layout_config.assets || [])];
                                const offset = (assets.length * 30) % 240;
                                const newAsset = {
                                    id: `asset-${Date.now()}-${Math.random().toString(36).slice(2, 6)}`,
                                    name: file.name,
                                    src: reader.result,
                                    naturalWidth: nw,
                                    naturalHeight: nh,
                                    x: 64 + offset,
                                    y: 64 + offset,
                                    width: Math.max(24, initW),
                                    height: Math.max(24, initH),
                                    rotation: 0,
                                    flipX: false,
                                    flipY: false,
                                    zIndex: 30,
                                };
                                const updated = [newAsset, ...assets];
                                catalogTemplateForm.value.layout_config.assets = updated;
                                schedulePersistCatalogTemplate();
                            };
                            img.src = reader.result;
                        };
                        reader.readAsDataURL(file);
                    });
                    event.target.value = '';
                };
                const catalogRemoveAsset = (id) => {
                    const assets = (catalogTemplateForm.value.layout_config.assets || []).filter((asset) => asset.id !== id);
                    catalogTemplateForm.value.layout_config.assets = assets;
                };
                const catalogCloneAsset = (asset) => {
                    const assets = [...(catalogTemplateForm.value.layout_config.assets || [])];
                    const sourceIndex = assets.findIndex((item) => item.id === asset.id);
                    if (sourceIndex === -1) return;

                    const clone = {
                        ...asset,
                        id: `asset-${Date.now()}-${Math.random().toString(36).slice(2, 6)}`,
                        name: `${asset.name || 'Asset'} (copy)`,
                        x: Number(asset.x || 0),
                        y: Number(asset.y || 0),
                    };
                    assets.splice(sourceIndex + 1, 0, clone);
                    const total = assets.length;
                    assets.forEach((item, index) => {
                        item.zIndex = (total - index) * 10 + 20;
                    });
                    catalogTemplateForm.value.layout_config.assets = assets;
                    schedulePersistCatalogTemplate();
                };
                const catalogMoveAsset = (asset, delta) => {
                    const assets = [...(catalogTemplateForm.value.layout_config.assets || [])];
                    const index = assets.findIndex((a) => a.id === asset.id);
                    if (index === -1) return;

                    const targetIndex = index - delta;
                    if (targetIndex < 0 || targetIndex >= assets.length) return;

                    const [moved] = assets.splice(index, 1);
                    assets.splice(targetIndex, 0, moved);

                    const total = assets.length;
                    assets.forEach((item, idx) => {
                        item.zIndex = (total - idx) * 10 + 20;
                    });

                    catalogTemplateForm.value.layout_config.assets = assets;
                };

                const assetDragSourceIndex = ref(null);
                const assetDragOverIndex = ref(null);

                const catalogAssetDragStart = (index, event) => {
                    assetDragSourceIndex.value = index;
                    if (event?.dataTransfer) {
                        event.dataTransfer.effectAllowed = 'move';
                        event.dataTransfer.setData('text/plain', String(index));
                    }
                };

                const catalogAssetDragOver = (index, event) => {
                    event.preventDefault();
                    if (event.dataTransfer) event.dataTransfer.dropEffect = 'move';
                    assetDragOverIndex.value = index;
                };

                const catalogAssetDrop = (targetIndex, event) => {
                    event.preventDefault();
                    const sourceIndex = assetDragSourceIndex.value;
                    if (sourceIndex === null || sourceIndex === targetIndex) {
                        assetDragSourceIndex.value = null;
                        assetDragOverIndex.value = null;
                        return;
                    }

                    const assets = [...(catalogTemplateForm.value.layout_config.assets || [])];
                    const [moved] = assets.splice(sourceIndex, 1);
                    assets.splice(targetIndex, 0, moved);
                    const total = assets.length;
                    assets.forEach((item, index) => {
                        item.zIndex = (total - index) * 10 + 20;
                    });
                    catalogTemplateForm.value.layout_config.assets = assets;
                    schedulePersistCatalogTemplate();
                    assetDragSourceIndex.value = null;
                    assetDragOverIndex.value = null;
                };

                const catalogAssetDragEnd = () => {
                    assetDragSourceIndex.value = null;
                    assetDragOverIndex.value = null;
                };

                const drawCatalogAssets = async (ctx, cfg) => {
                    const assets = [...(cfg.assets || [])].sort((a, b) => Number(a.zIndex || 0) - Number(b.zIndex || 0));
                    for (const asset of assets) {
                        const image = await loadCatalogImage(asset.src);
                        if (!image) continue;
                        const x = Number(asset.x || 0);
                        const y = Number(asset.y || 0);
                        const width = Number(asset.width || image.naturalWidth);
                        const height = Number(asset.height || image.naturalHeight);
                        const rotation = Number(asset.rotation || 0) * Math.PI / 180;
                        const scaleX = asset.flipX ? -1 : 1;
                        const scaleY = asset.flipY ? -1 : 1;
                        ctx.save();
                        ctx.translate(x + width / 2, y + height / 2);
                        ctx.rotate(rotation);
                        ctx.scale(scaleX, scaleY);
                        ctx.drawImage(image, -width / 2, -height / 2, width, height);
                        ctx.restore();
                    }
                };

                const drawCatalogTextBoxes = (ctx, cfg, canvasWidth, canvasHeight) => {
                    const boxes = cfg.textBoxes || [];
                    if (!boxes.length) return;
                    boxes.forEach((box) => {
                        if (!box.text) return;
                        const fs = Number(box.fontSize || 16);
                        const font = box.fontFamily || cfg.fontFamily || 'Inter, Arial, sans-serif';
                        ctx.font = `700 ${fs}px ${font}`;
                        const padX = Number(box.paddingX || 12);
                        const padY = Number(box.paddingY || 6);
                        const metrics = ctx.measureText(box.text);
                        const textW = metrics.width;
                        const boxW = textW + padX * 2;
                        const boxH = fs + padY * 2;
                        const x = Number(box.x || canvasWidth / 2);
                        const y = Number(box.y || 100);
                        const rectX = x - boxW / 2;
                        const rectY = y - boxH / 2;

                        if (box.shape && box.shape !== 'none' && box.bgColor) {
                            ctx.save();
                            ctx.fillStyle = box.bgColor || 'rgba(0,0,0,0.6)';
                            const radius = box.shape === 'pill' ? boxH / 2 : (box.shape === 'rounded' ? 8 : (box.shape === 'tag' ? 4 : 0));
                            ctx.beginPath();
                            if (typeof ctx.roundRect === 'function') {
                                ctx.roundRect(rectX, rectY, boxW, boxH, radius);
                            } else {
                                ctx.rect(rectX, rectY, boxW, boxH);
                            }
                            ctx.fill();
                            if (box.shape === 'tag') {
                                ctx.strokeStyle = box.color || '#ffffff';
                                ctx.lineWidth = 1;
                                ctx.stroke();
                            }
                            ctx.restore();
                        }

                        ctx.save();
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillStyle = box.color || '#ffffff';
                        ctx.font = `700 ${fs}px ${font}`;
                        ctx.fillText(box.text, x, y);
                        ctx.restore();
                    });
                };

                const catalogTextBoxPreviewStyle = (box, template) => {
                    const canvasWidth = Number(template?.canvas_width || 1080);
                    const canvasHeight = Number(template?.canvas_height || (template?.format === 'feed' ? 1350 : 1920));
                    const x = Number(box.x || canvasWidth / 2);
                    const y = Number(box.y || 100);
                    const fs = Number(box.fontSize || 16);
                    const scaledFs = Math.max(9, (fs / canvasHeight) * 640);
                    const shape = box.shape || 'none';
                    return {
                        left: `${(x / canvasWidth) * 100}%`,
                        top: `${(y / canvasHeight) * 100}%`,
                        transform: 'translate(-50%, -50%)',
                        fontSize: `${scaledFs}px`,
                        color: box.color || '#ffffff',
                        backgroundColor: shape !== 'none' ? (box.bgColor || 'rgba(0,0,0,0.6)') : 'transparent',
                        borderRadius: shape === 'pill' ? '9999px' : (shape === 'rounded' ? '6px' : (shape === 'tag' ? '4px' : '0px')),
                        border: shape === 'tag' ? '1px solid currentColor' : 'none',
                        padding: shape !== 'none' ? `${Math.max(2, (Number(box.paddingY || 6) / canvasHeight) * 640)}px ${Math.max(4, (Number(box.paddingX || 12) / canvasWidth) * 400)}px` : '0px',
                        zIndex: 25,
                        fontFamily: box.fontFamily || template?.layout_config?.fontFamily || 'Inter, sans-serif',
                    };
                };

                const addCatalogTextBox = () => {
                    if (!catalogTemplateForm.value.layout_config.textBoxes) {
                        catalogTemplateForm.value.layout_config.textBoxes = [];
                    }
                    catalogTemplateForm.value.layout_config.textBoxes.push({
                        id: 'tb_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4),
                        text: 'PROMO TERBATAS',
                        x: Math.round(Number(catalogTemplateForm.value.canvas_width || 1080) / 2),
                        y: 120,
                        fontSize: 20,
                        color: '#ffffff',
                        bgColor: 'rgba(15, 23, 42, 0.85)',
                        shape: 'pill',
                        paddingX: 16,
                        paddingY: 8,
                    });
                };

                const removeCatalogTextBox = (index) => {
                    if (!catalogTemplateForm.value.layout_config.textBoxes) return;
                    catalogTemplateForm.value.layout_config.textBoxes.splice(index, 1);
                };
                const catalogCloseConfirmOpen = ref(false);
                const closeCatalogTemplateModal = () => {
                    if (catalogPersistTimer) clearTimeout(catalogPersistTimer);
                    catalogPersistTimer = null;
                    localStorage.removeItem('ppp_catalog_template_modal_open');
                    localStorage.removeItem('ppp_catalog_template_modal_type');
                    localStorage.removeItem('ppp_catalog_template_form');
                    catalogTemplateModalOpen.value = false;
                };

                const saveAndCloseCatalogTemplate = () => {
                    catalogCloseConfirmOpen.value = false;
                    saveCatalogTemplate();
                };

                const discardAndCloseCatalogTemplate = () => {
                    catalogCloseConfirmOpen.value = false;
                    closeCatalogTemplateModal();
                };

                const generateTemplateThumbnailDataUrl = async (templateForm) => {
                    try {
                        const rows = catalogPreviewRows.value;
                        const tempTemplate = {
                            ID: templateForm.ID || 'temp',
                            name: templateForm.name || 'KATALOG',
                            format: templateForm.format || 'story',
                            output_mode: templateForm.output_mode || 'list',
                            canvas_width: templateForm.canvas_width || (templateForm.format === 'a4' ? 1240 : 1080),
                            canvas_height: templateForm.canvas_height || (templateForm.format === 'a4' ? 1754 : (templateForm.format === 'feed' ? 1350 : 1920)),
                            layout_config: templateForm.layout_config,
                            background_url: templateForm.background_url || null,
                        };
                        const isCard = tempTemplate.output_mode === 'katalog';
                        if (isCard) {
                            const groups = catalogAndroidCardGroups(rows).slice(0, Number(templateForm.layout_config?.cardColumns || 3));
                            return await drawPricelistCardPage(tempTemplate, groups, tempTemplate.name, 'special_price');
                        }
                        return await drawCatalogPage(tempTemplate, rows.slice(0, 5), tempTemplate.name, 'special_price');
                    } catch (e) {
                        return null;
                    }
                };

                const saveCatalogTemplate = async () => {
                    let thumbnailDataUrl = null;
                    try {
                        thumbnailDataUrl = await generateTemplateThumbnailDataUrl(catalogTemplateForm.value);
                    } catch (e) {}

                    ensureRunApi()
                        .withSuccessHandler(async (response) => {
                            const stored = response?.data || response;
                            showNotification('Template katalog tersimpan');
                            if (stored?.ID && thumbnailDataUrl) {
                                try {
                                    await ensureRunApi().uploadCatalogThumbnail(thumbnailDataUrl, stored.ID);
                                } catch (e) {}
                            }
                            closeCatalogTemplateModal();
                            loadCatalogTemplates();
                            if (stored?.ID) catalogSelectedTemplateId.value = stored.ID;
                        })
                        .withFailureHandler((error) => notifyError('', error, 'Gagal menyimpan template katalog.'))
                        .saveCatalogTemplate(catalogTemplateForm.value);
                };

                const deleteCatalogTemplate = (template) => {
                    if (!template?.ID) return;
                    showConfirm('Hapus Template?', 'Template katalog akan dihapus permanen.', () => {
                        ensureRunApi()
                            .withSuccessHandler(() => {
                                showNotification('Template katalog dihapus');
                                loadCatalogTemplates();
                            })
                            .withFailureHandler((error) => notifyError('', error, 'Gagal menghapus template katalog.'))
                            .deleteCatalogTemplate(template.ID);
                    }, 'danger');
                };

                const openCatalogPreviewModal = (index) => {
                    catalogPreviewModalIndex.value = index;
                    catalogPreviewZoom.value = 1;
                    catalogPreviewModalOpen.value = true;
                };
                const closeCatalogPreviewModal = () => {
                    catalogPreviewModalOpen.value = false;
                };
                const catalogPreviewNav = (delta) => {
                    if (!catalogPreviewImages.value.length) return;
                    catalogPreviewModalIndex.value = (catalogPreviewModalIndex.value + delta + catalogPreviewImages.value.length) % catalogPreviewImages.value.length;
                    catalogPreviewZoom.value = 1;
                };
                const catalogPreviewZoomBy = (factor) => {
                    catalogPreviewZoom.value = Math.max(0.5, Math.min(4, catalogPreviewZoom.value * factor));
                };
                const downloadCatalogPreview = (img, idx) => {
                    const a = document.createElement('a');
                    a.href = img;
                    a.download = `katalog-pricelist-${idx + 1}.png`;
                    a.click();
                };

                const uploadCatalogTemplateBackground = (event, template) => {
                    const file = event?.target?.files?.[0];
                    if (!file || !template?.ID) return;
                    ensureRunApi()
                        .withSuccessHandler(() => {
                            showNotification('Background template berhasil diupload');
                            loadCatalogTemplates();
                        })
                        .withFailureHandler((error) => notifyError('', error, 'Gagal upload background template.'))
                        .uploadCatalogBackground(file, template.ID);
                    event.target.value = '';
                };

                const uploadNewCatalogTemplateBackground = (event) => {
                    const file = event?.target?.files?.[0];
                    if (!file) return;
                    const baseName = (file.name || 'Template Baru').replace(/\.[^.]+$/, '').slice(0, 120) || 'Template Baru';
                    ensureRunApi()
                        .withSuccessHandler((response) => {
                            const stored = response?.data || response;
                            const id = stored?.ID;
                            if (!id) { loadCatalogTemplates(); event.target.value = ''; return; }
                            ensureRunApi()
                                .withSuccessHandler(() => {
                                    showNotification('Template baru berhasil dibuat');
                                    loadCatalogTemplates();
                                    catalogSelectedTemplateId.value = id;
                                })
                                .withFailureHandler((error) => notifyError('', error, 'Template dibuat tapi gagal upload background.'))
                                .uploadCatalogBackground(file, id);
                        })
                        .withFailureHandler((error) => notifyError('', error, 'Gagal membuat template baru.'))
                        .saveCatalogTemplate({
                            name: baseName,
                            format: 'story',
                            layout_config: defaultCatalogLayoutConfig(),
                            is_active: true,
                        });
                    event.target.value = '';
                };

                let catalogDragMoved = false;
                const catalogSelectedAssetIds = ref([]);
                let dragRafId = null;
                let assetResizeRafId = null;

                const catalogToggleAssetSelection = (asset, event = null) => {
                    const id = asset?.id;
                    if (!id) return;
                    const selected = catalogSelectedAssetIds.value;
                    if (event?.shiftKey || event?.metaKey || event?.ctrlKey) {
                        catalogSelectedAssetIds.value = selected.includes(id)
                            ? selected.filter((selectedId) => selectedId !== id)
                            : [...selected, id];
                        return;
                    }
                    catalogSelectedAssetIds.value = selected.includes(id) && selected.length === 1 ? [] : [id];
                };

                const catalogSelectedAssetsBounds = (template) => {
                    const selected = new Set(catalogSelectedAssetIds.value);
                    const assets = template?.layout_config?.assets || [];
                    const picked = assets.filter((asset) => selected.has(asset.id));
                    if (picked.length < 2) return null;
                    const minX = Math.min(...picked.map((asset) => Number(asset.x || 0)));
                    const minY = Math.min(...picked.map((asset) => Number(asset.y || 0)));
                    const maxX = Math.max(...picked.map((asset) => Number(asset.x || 0) + Number(asset.width || 120)));
                    const maxY = Math.max(...picked.map((asset) => Number(asset.y || 0) + Number(asset.height || 120)));
                    const size = catalogCanvasSize(template);
                    return {
                        left: `${(minX / size.width) * 100}%`,
                        top: `${(minY / size.height) * 100}%`,
                        width: `${((maxX - minX) / size.width) * 100}%`,
                        height: `${((maxY - minY) / size.height) * 100}%`,
                    };
                };

                const catalogCenterSelectedAssets = () => {
                    const selected = new Set(catalogSelectedAssetIds.value);
                    const assets = catalogTemplateForm.value.layout_config.assets || [];
                    const picked = assets.filter((asset) => selected.has(asset.id));
                    if (picked.length < 2) return;
                    const minX = Math.min(...picked.map((asset) => Number(asset.x || 0)));
                    const maxX = Math.max(...picked.map((asset) => Number(asset.x || 0) + Number(asset.width || 120)));
                    const minY = Math.min(...picked.map((asset) => Number(asset.y || 0)));
                    const maxY = Math.max(...picked.map((asset) => Number(asset.y || 0) + Number(asset.height || 120)));
                    const canvas = catalogCanvasSize(catalogTemplateForm.value);
                    const deltaX = Math.round(canvas.width / 2 - (minX + maxX) / 2);
                    const deltaY = Math.round(canvas.height / 2 - (minY + maxY) / 2);
                    picked.forEach((asset) => {
                        asset.x = Number(asset.x || 0) + deltaX;
                        asset.y = Number(asset.y || 0) + deltaY;
                    });
                    schedulePersistCatalogTemplate();
                };

                const catalogAlignSelectedAssets = (type = 'centerX') => {
                    const selected = new Set(catalogSelectedAssetIds.value);
                    const assets = catalogTemplateForm.value.layout_config.assets || [];
                    const picked = assets.filter((asset) => selected.has(asset.id));
                    if (picked.length < 2) return;

                    if (type === 'centerX') {
                        const avgCenterX = picked.reduce((sum, a) => sum + (Number(a.x || 0) + Number(a.width || 120) / 2), 0) / picked.length;
                        picked.forEach((a) => {
                            a.x = Math.round(avgCenterX - Number(a.width || 120) / 2);
                        });
                    } else if (type === 'centerY') {
                        const avgCenterY = picked.reduce((sum, a) => sum + (Number(a.y || 0) + Number(a.height || 120) / 2), 0) / picked.length;
                        picked.forEach((a) => {
                            a.y = Math.round(avgCenterY - Number(a.height || 120) / 2);
                        });
                    } else if (type === 'canvasCenter') {
                        catalogCenterSelectedAssets();
                        return;
                    }
                    schedulePersistCatalogTemplate();
                };

                const catalogSelectedAssetsDragStart = (event, template) => {
                    const selected = new Set(catalogSelectedAssetIds.value);
                    const assets = template?.layout_config?.assets || [];
                    const moving = assets
                        .filter((asset) => selected.has(asset.id))
                        .map((asset) => ({
                            id: asset.id,
                            x: Number(asset.x || 0),
                            y: Number(asset.y || 0),
                            width: Number(asset.width || 120),
                            height: Number(asset.height || 120),
                        }));
                    if (moving.length < 2) return;
                    if (event.target && event.target.setPointerCapture && event.pointerId !== undefined) {
                        try { event.target.setPointerCapture(event.pointerId); } catch (e) {}
                    }
                    const rect = event.currentTarget.closest('.catalog-layout-drag')?.getBoundingClientRect() || event.currentTarget.getBoundingClientRect();
                    const size = catalogCanvasSize(template);
                    const minX = Math.min(...moving.map((m) => m.x));
                    const maxX = Math.max(...moving.map((m) => m.x + m.width));
                    const minY = Math.min(...moving.map((m) => m.y));
                    const maxY = Math.max(...moving.map((m) => m.y + m.height));

                    catalogDragMoved = false;
                    catalogLayoutDrag.value = {
                        type: 'asset-group',
                        startX: event.clientX,
                        startY: event.clientY,
                        canvasWidth: size.width,
                        canvasHeight: size.height,
                        rectWidth: rect.width,
                        rectHeight: rect.height,
                        moving,
                        groupX: minX,
                        groupY: minY,
                        groupW: maxX - minX,
                        groupH: maxY - minY,
                    };
                };

                const catalogLayoutDragStart = (event, template, dragType = 'table', assetIndex = null) => {
                    if (dragType === 'asset' && assetIndex !== null) {
                        const asset = template?.layout_config?.assets?.[assetIndex];
                        if (asset) catalogToggleAssetSelection(asset, event);
                    }
                    if (event.target && event.target.setPointerCapture && event.pointerId !== undefined) {
                        try { event.target.setPointerCapture(event.pointerId); } catch (e) {}
                    }
                    const canvasWidth = Number(template?.canvas_width || (template?.format === 'feed' ? 1080 : 1080));
                    const canvasHeight = Number(template?.canvas_height || (template?.format === 'feed' ? 1350 : 1920));
                    const rect = event.currentTarget.closest('.catalog-layout-drag')?.getBoundingClientRect() || event.currentTarget.getBoundingClientRect();
                    const cfg = template?.layout_config || defaultCatalogLayoutConfig();
                    const width = Number(cfg.width || canvasWidth - 128);

                    let originX = Number(cfg.x || 64);
                    let originY = Number(cfg.y || 255);
                    let titleWidth = 0;
                    let titleHeight = 0;
                    if (dragType === 'title') {
                        originX = Number(cfg.titleX || canvasWidth / 2);
                        originY = Number(cfg.titleY || Number(cfg.y || 255) - Number(cfg.titleGap || 42));
                        const titleRect = event.currentTarget.getBoundingClientRect();
                        if (titleRect.width) {
                            titleWidth = (titleRect.width * canvasWidth) / (rect.width || 1);
                            titleHeight = (titleRect.height * canvasHeight) / (rect.height || 1);
                        }
                    } else if (dragType === 'asset' && assetIndex !== null && cfg.assets && cfg.assets[assetIndex]) {
                        originX = Number(cfg.assets[assetIndex].x || 0);
                        originY = Number(cfg.assets[assetIndex].y || 0);
                    } else if (dragType === 'textbox' && assetIndex !== null && cfg.textBoxes && cfg.textBoxes[assetIndex]) {
                        originX = Number(cfg.textBoxes[assetIndex].x || canvasWidth / 2);
                        originY = Number(cfg.textBoxes[assetIndex].y || 100);
                    }

                    catalogDragMoved = false;
                    catalogLayoutDrag.value = {
                        type: dragType,
                        assetIndex,
                        startX: event.clientX,
                        startY: event.clientY,
                        originX,
                        originY,
                        canvasWidth,
                        canvasHeight,
                        tableWidth: width,
                        titleWidth,
                        titleHeight,
                        rectWidth: rect.width,
                        rectHeight: rect.height,
                    };
                };

                const catalogSnapAssetPosition = (assetIndex, x, y, width, height, canvasWidth, canvasHeight) => {
                    const assets = catalogTemplateForm.value.layout_config.assets || [];
                    const threshold = 14;
                    let snappedX = x;
                    let snappedY = y;
                    const source = {
                        left: x,
                        right: x + width,
                        center: x + width / 2,
                        top: y,
                        bottom: y + height,
                        middle: y + height / 2,
                    };

                    let minDiffX = threshold + 1;
                    let minDiffY = threshold + 1;

                    // Snap ke batas kanvas (Kiri, Tengah, Kanan)
                    if (canvasWidth) {
                        const diffLeft = Math.abs(source.left);
                        if (diffLeft <= threshold && diffLeft < minDiffX) {
                            minDiffX = diffLeft;
                            snappedX = 0;
                        }
                        const diffCenterX = Math.abs(source.center - canvasWidth / 2);
                        if (diffCenterX <= threshold && diffCenterX < minDiffX) {
                            minDiffX = diffCenterX;
                            snappedX = canvasWidth / 2 - width / 2;
                        }
                        const diffRight = Math.abs(source.right - canvasWidth);
                        if (diffRight <= threshold && diffRight < minDiffX) {
                            minDiffX = diffRight;
                            snappedX = canvasWidth - width;
                        }
                    }

                    // Snap ke batas kanvas (Atas, Tengah, Bawah)
                    if (canvasHeight) {
                        const diffTop = Math.abs(source.top);
                        if (diffTop <= threshold && diffTop < minDiffY) {
                            minDiffY = diffTop;
                            snappedY = 0;
                        }
                        const diffMiddleY = Math.abs(source.middle - canvasHeight / 2);
                        if (diffMiddleY <= threshold && diffMiddleY < minDiffY) {
                            minDiffY = diffMiddleY;
                            snappedY = canvasHeight / 2 - height / 2;
                        }
                        const diffBottom = Math.abs(source.bottom - canvasHeight);
                        if (diffBottom <= threshold && diffBottom < minDiffY) {
                            minDiffY = diffBottom;
                            snappedY = canvasHeight - height;
                        }
                    }

                    // Snap ke aset-aset lain
                    assets.forEach((target, index) => {
                        if (index === assetIndex) return;
                        const tx = Number(target.x || 0);
                        const ty = Number(target.y || 0);
                        const tw = Number(target.width || 120);
                        const th = Number(target.height || 120);
                        const targetX = [tx, tx + tw / 2, tx + tw];
                        const targetY = [ty, ty + th / 2, ty + th];
                        const sourceX = [source.left, source.center, source.right];
                        const sourceY = [source.top, source.middle, source.bottom];

                        sourceX.forEach((sVal, sIdx) => {
                            targetX.forEach((tVal) => {
                                const diff = Math.abs(sVal - tVal);
                                if (diff <= threshold && diff < minDiffX) {
                                    minDiffX = diff;
                                    const offset = [0, width / 2, width][sIdx];
                                    snappedX = tVal - offset;
                                }
                            });
                        });

                        sourceY.forEach((sVal, sIdx) => {
                            targetY.forEach((tVal) => {
                                const diff = Math.abs(sVal - tVal);
                                if (diff <= threshold && diff < minDiffY) {
                                    minDiffY = diff;
                                    const offset = [0, height / 2, height][sIdx];
                                    snappedY = tVal - offset;
                                }
                            });
                        });
                    });

                    return { x: Math.round(snappedX), y: Math.round(snappedY) };
                };

                const catalogLayoutDragMove = (event) => {
                    const drag = catalogLayoutDrag.value;
                    if (!drag) return;

                    const clientX = event.clientX;
                    const clientY = event.clientY;

                    const deltaDist = Math.hypot(clientX - drag.startX, clientY - drag.startY);
                    if (deltaDist < 3 && !catalogDragMoved) return;
                    catalogDragMoved = true;

                    const scaleX = drag.canvasWidth / drag.rectWidth;
                    const scaleY = drag.canvasHeight / drag.rectHeight;
                    const deltaX = (clientX - drag.startX) * scaleX;
                    const deltaY = (clientY - drag.startY) * scaleY;

                    if (drag.type === 'asset-group') {
                        const assets = catalogTemplateForm.value.layout_config.assets || [];
                        const assetsMap = new Map(assets.map((asset) => [asset.id, asset]));
                        const snapped = catalogSnapAssetPosition(
                            -1,
                            drag.groupX + deltaX,
                            drag.groupY + deltaY,
                            drag.groupW,
                            drag.groupH,
                            drag.canvasWidth,
                            drag.canvasHeight,
                        );
                        const moveX = snapped.x - drag.groupX;
                        const moveY = snapped.y - drag.groupY;
                        drag.moving.forEach((orig) => {
                            const asset = assetsMap.get(orig.id);
                            if (asset) {
                                asset.x = Math.round(orig.x + moveX);
                                asset.y = Math.round(orig.y + moveY);
                            }
                        });
                        return;
                    }

                    if (drag.type === 'asset') {
                        const assets = catalogTemplateForm.value.layout_config.assets;
                        if (assets && assets[drag.assetIndex]) {
                            const asset = assets[drag.assetIndex];
                            const snapped = catalogSnapAssetPosition(
                                drag.assetIndex,
                                drag.originX + deltaX,
                                drag.originY + deltaY,
                                Number(asset.width || 120),
                                Number(asset.height || 120),
                                drag.canvasWidth,
                                drag.canvasHeight,
                            );
                            asset.x = snapped.x;
                            asset.y = snapped.y;
                        }
                        return;
                    }

                    if (drag.type === 'textbox') {
                        const boxes = catalogTemplateForm.value.layout_config.textBoxes;
                        if (boxes && boxes[drag.assetIndex]) {
                            boxes[drag.assetIndex].x = Math.round(drag.originX + deltaX);
                            boxes[drag.assetIndex].y = Math.round(drag.originY + deltaY);
                        }
                        return;
                    }

                    if (dragRafId) cancelAnimationFrame(dragRafId);
                    dragRafId = requestAnimationFrame(() => {
                        if (!catalogLayoutDrag.value) return;
                        const x = Math.round(drag.originX + deltaX);
                        const y = Math.round(drag.originY + deltaY);

                        if (drag.type === 'title') {
                            const snapped = catalogSnapAssetPosition(
                                -1,
                                x,
                                y,
                                drag.titleWidth,
                                drag.titleHeight,
                                drag.canvasWidth,
                                drag.canvasHeight,
                            );
                            catalogTemplateForm.value.layout_config.titleX = snapped.x;
                            catalogTemplateForm.value.layout_config.titleY = snapped.y;
                            return;
                        }

                        const centerX = (drag.canvasWidth - drag.tableWidth) / 2;
                        catalogTemplateForm.value.layout_config.x = Math.abs(x - centerX) <= 12 ? centerX : Math.max(0, x);
                        catalogTemplateForm.value.layout_config.y = y;
                    });
                };

                const catalogCardImageResizeStart = (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    const rect = event.currentTarget.closest('.catalog-layout-drag')?.getBoundingClientRect();
                    if (!rect) return;
                    if (!catalogTemplateForm.value.layout_config.card_config) {
                        catalogTemplateForm.value.layout_config.card_config = defaultCatalogCardConfig();
                    }
                    const cardCfg = catalogTemplateForm.value.layout_config.card_config;
                    const startH = Number(cardCfg.imageHeight || 100);
                    const startY = event.clientY;
                    const scaleY = catalogCanvasSize(catalogTemplateForm.value).height / rect.height;
                    const move = (moveEvent) => {
                        if (assetResizeRafId) cancelAnimationFrame(assetResizeRafId);
                        assetResizeRafId = requestAnimationFrame(() => {
                            cardCfg.imageHeight = Math.round(Math.max(40, Math.min(360, startH + (moveEvent.clientY - startY) * scaleY)));
                        });
                    };
                    const stop = () => {
                        document.removeEventListener('pointermove', move, true);
                        document.removeEventListener('pointerup', stop, true);
                        if (assetResizeRafId) cancelAnimationFrame(assetResizeRafId);
                        assetResizeRafId = null;
                        schedulePersistCatalogTemplate();
                    };
                    document.addEventListener('pointermove', move, true);
                    document.addEventListener('pointerup', stop, true);
                };

                const catalogCenterCanvasElement = (type) => {
                    const cfg = catalogTemplateForm.value.layout_config;
                    const size = catalogCanvasSize(catalogTemplateForm.value);
                    if (type === 'table') {
                        cfg.x = Math.round((size.width - Number(cfg.width || size.width - 128)) / 2);
                    }
                    if (type === 'title') {
                        cfg.titleX = Math.round(size.width / 2);
                    }
                    schedulePersistCatalogTemplate();
                };

                const catalogTitleResizeStart = (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    const rect = event.currentTarget.closest('.catalog-layout-drag')?.getBoundingClientRect();
                    if (!rect) return;
                    const cfg = catalogTemplateForm.value.layout_config;
                    const startFontSize = Number(cfg.titleFontSize || 34);
                    const startY = event.clientY;
                    const scaleY = catalogCanvasSize(catalogTemplateForm.value).height / rect.height;
                    const move = (moveEvent) => {
                        if (assetResizeRafId) cancelAnimationFrame(assetResizeRafId);
                        assetResizeRafId = requestAnimationFrame(() => {
                            cfg.titleFontSize = Math.round(Math.max(12, Math.min(180, startFontSize + (moveEvent.clientY - startY) * scaleY)));
                        });
                    };
                    const stop = () => {
                        document.removeEventListener('pointermove', move, true);
                        document.removeEventListener('pointerup', stop, true);
                        if (assetResizeRafId) cancelAnimationFrame(assetResizeRafId);
                        assetResizeRafId = null;
                        schedulePersistCatalogTemplate();
                    };
                    document.addEventListener('pointermove', move, true);
                    document.addEventListener('pointerup', stop, true);
                };

                const catalogTableResizeStart = (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    const rect = event.currentTarget.closest('.catalog-layout-drag')?.getBoundingClientRect();
                    if (!rect) return;
                    const cfg = catalogTemplateForm.value.layout_config;
                    const startWidth = Number(cfg.width || catalogCanvasSize(catalogTemplateForm.value).width - 128);
                    const startX = event.clientX;
                    const scaleX = catalogCanvasSize(catalogTemplateForm.value).width / rect.width;
                    const move = (moveEvent) => {
                        if (assetResizeRafId) cancelAnimationFrame(assetResizeRafId);
                        assetResizeRafId = requestAnimationFrame(() => {
                            cfg.width = Math.round(Math.max(160, startWidth + (moveEvent.clientX - startX) * scaleX));
                        });
                    };
                    const stop = () => {
                        document.removeEventListener('pointermove', move, true);
                        document.removeEventListener('pointerup', stop, true);
                        if (assetResizeRafId) cancelAnimationFrame(assetResizeRafId);
                        assetResizeRafId = null;
                        schedulePersistCatalogTemplate();
                    };
                    document.addEventListener('pointermove', move, true);
                    document.addEventListener('pointerup', stop, true);
                };

                const catalogAssetResizeStart = (event, assetIndex) => {
                    event.preventDefault();
                    event.stopPropagation();
                    const rect = event.currentTarget.closest('.catalog-layout-drag')?.getBoundingClientRect();
                    const asset = catalogTemplateForm.value.layout_config.assets?.[assetIndex];
                    if (!rect || !asset) return;
                    const startWidth = Number(asset.width || 160);
                    const startHeight = Number(asset.height || 160);
                    const aspectRatio = Number(asset.naturalWidth || 0) / Number(asset.naturalHeight || 0) || startWidth / startHeight || 1;
                    const startX = event.clientX;
                    const startY = event.clientY;
                    const scaleX = catalogCanvasSize(catalogTemplateForm.value).width / rect.width;
                    const scaleY = catalogCanvasSize(catalogTemplateForm.value).height / rect.height;
                    const move = (moveEvent) => {
                        if (assetResizeRafId) cancelAnimationFrame(assetResizeRafId);
                        assetResizeRafId = requestAnimationFrame(() => {
                            const deltaWidth = (moveEvent.clientX - startX) * scaleX;
                            const deltaHeight = (moveEvent.clientY - startY) * scaleY;
                            const nextWidth = Math.max(24, startWidth + deltaWidth);
                            const nextHeight = Math.max(24, startHeight + deltaHeight);
                            const widthDriven = Math.abs(deltaWidth) >= Math.abs(deltaHeight * aspectRatio);
                            asset.width = Math.round(widthDriven ? nextWidth : Math.max(24, nextHeight * aspectRatio));
                            asset.height = Math.round(asset.width / aspectRatio);
                        });
                    };
                    const stop = () => {
                        document.removeEventListener('pointermove', move, true);
                        document.removeEventListener('pointerup', stop, true);
                        if (assetResizeRafId) cancelAnimationFrame(assetResizeRafId);
                        assetResizeRafId = null;
                        schedulePersistCatalogTemplate();
                    };
                    document.addEventListener('pointermove', move, true);
                    document.addEventListener('pointerup', stop, true);
                };

                let assetRotateRafId = null;
                const catalogAssetRotateStart = (event, assetIndex) => {
                    event.preventDefault();
                    event.stopPropagation();
                    const rect = event.currentTarget.closest('.catalog-layout-drag')?.getBoundingClientRect();
                    const asset = catalogTemplateForm.value.layout_config.assets?.[assetIndex];
                    if (!rect || !asset) return;
                    const size = catalogCanvasSize(catalogTemplateForm.value);
                    const scaleX = rect.width / size.width;
                    const scaleY = rect.height / size.height;
                    const assetCenterX = rect.left + (Number(asset.x || 0) + Number(asset.width || 160) / 2) * scaleX;
                    const assetCenterY = rect.top + (Number(asset.y || 0) + Number(asset.height || 160) / 2) * scaleY;
                    const move = (moveEvent) => {
                        if (assetRotateRafId) cancelAnimationFrame(assetRotateRafId);
                        assetRotateRafId = requestAnimationFrame(() => {
                            const dx = moveEvent.clientX - assetCenterX;
                            const dy = moveEvent.clientY - assetCenterY;
                            let deg = Math.round((Math.atan2(dy, dx) * 180 / Math.PI) + 90);
                            deg = (deg % 360 + 360) % 360;
                            const snapAngles = [0, 45, 90, 135, 180, 225, 270, 315, 360];
                            for (const snap of snapAngles) {
                                if (Math.abs(deg - snap) <= 4) {
                                    deg = snap % 360;
                                    break;
                                }
                            }
                            asset.rotation = deg;
                        });
                    };
                    const stop = () => {
                        document.removeEventListener('pointermove', move, true);
                        document.removeEventListener('pointerup', stop, true);
                        if (assetRotateRafId) cancelAnimationFrame(assetRotateRafId);
                        assetRotateRafId = null;
                        schedulePersistCatalogTemplate();
                    };
                    document.addEventListener('pointermove', move, true);
                    document.addEventListener('pointerup', stop, true);
                };

                const catalogLayoutDragEnd = () => {
                    if (dragRafId) { cancelAnimationFrame(dragRafId); dragRafId = null; }
                    catalogLayoutDrag.value = null;
                    setTimeout(() => {
                        catalogDragMoved = false;
                    }, 80);
                    schedulePersistCatalogTemplate();
                };

                const catalogPickColorSafe = (getter, setter, event) => {
                    if (catalogDragMoved) return;
                    openColorPicker(getter, setter, event);
                };

                const catalogTitlePreviewStyle = (template) => {
                    const cfg = template?.layout_config || defaultCatalogLayoutConfig();
                    const canvasWidth = Number(template?.canvas_width || (template?.format === 'feed' ? 1080 : 1080));
                    const canvasHeight = Number(template?.canvas_height || (template?.format === 'feed' ? 1350 : 1920));
                    const titleX = Number(cfg.titleX ?? Number(cfg.x || 64));
                    const titleY = Number(cfg.titleY ?? Math.max(0, Number(cfg.y || 255) - Number(cfg.titleFontSize || 34) - Math.round(Number(cfg.titleFontSize || 34) * 0.6) - 16));
                    const fontSize = Number(cfg.titleFontSize || 34);

                    return {
                        left: `${(titleX / canvasWidth) * 100}%`,
                        top: `${(titleY / canvasHeight) * 100}%`,
                        transform: 'translate(0, 0)',
                        fontSize: `${Math.max(9, (fontSize / canvasHeight) * 640)}px`,
                        fontFamily: cfg.fontFamily || 'Inter, sans-serif',
                        lineHeight: '1',
                        color: cfg.titleColor || '#ffffff',
                        WebkitTextStroke: `${Number(cfg.titleStrokeWidth || 0)}px ${cfg.titleStrokeColor || '#000000'}`,
                        paintOrder: 'stroke fill',
                        zIndex: 30,
                    };
                };

                const catalogColorFields = [
                    { key: 'headerColor', label: 'Warna header (latar)' },
                    { key: 'headerTextColor', label: 'Warna teks header' },
                    { key: 'textColor', label: 'Warna teks produk' },
                    { key: 'normalPriceColor', label: 'Warna normal price' },
                    { key: 'specialPriceColor', label: 'Warna special price' },
                    { key: 'strikeColor', label: 'Warna coret harga normal' },
                    { key: 'rowOddColor', label: 'Warna row ganjil' },
                    { key: 'rowEvenColor', label: 'Warna row genap' },
                ];

                const catalogColorHex = (value) => {
                    const text = String(value || '').trim();
                    if (/^#([0-9a-f]{6})$/i.test(text)) return text;
                    const match = text.match(/rgba?\(\s*([\d.]+)\s*,\s*([\d.]+)\s*,\s*([\d.]+)/);
                    if (match) {
                        return '#' + match.slice(1, 4).map((part) => Math.max(0, Math.min(255, Math.round(Number(part)))).toString(16).padStart(2, '0')).join('');
                    }
                    return '#000000';
                };

                const catalogApplyColor = (key, event) => {
                    const hex = String(event.target.value || '#000000');
                    const current = String(catalogTemplateForm.value.layout_config[key] || '');
                    const alphaMatch = current.match(/rgba\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+\s*,\s*([\d.]+)/);
                    if (alphaMatch) {
                        const r = parseInt(hex.slice(1, 3), 16);
                        const g = parseInt(hex.slice(3, 5), 16);
                        const b = parseInt(hex.slice(5, 7), 16);
                        catalogTemplateForm.value.layout_config[key] = `rgba(${r}, ${g}, ${b}, ${alphaMatch[1]})`;
                        return;
                    }
                    catalogTemplateForm.value.layout_config[key] = hex;
                };

                const catalogTemplateThumbnailScale = (template) => {
                    const canvasHeight = Number(template?.canvas_height || (template?.format === 'feed' ? 1350 : 1920));
                    return 190 / canvasHeight;
                };

                const catalogTemplateThumbnailTitleStyle = (template) => {
                    const cfg = template?.layout_config || defaultCatalogLayoutConfig();
                    const canvasWidth = Number(template?.canvas_width || 1080);
                    const canvasHeight = Number(template?.canvas_height || (template?.format === 'feed' ? 1350 : 1920));
                    const scale = catalogTemplateThumbnailScale(template);
                    return {
                        left: `${(Number(cfg.titleX ?? canvasWidth / 2) / canvasWidth) * 100}%`,
                        top: `${(Number(cfg.titleY ?? Number(cfg.y || 255) - Number(cfg.titleGap || 42)) / canvasHeight) * 100}%`,
                        transform: 'translate(0, 0)',
                        fontSize: `${Math.max(2, Number(cfg.titleFontSize || 34) * scale)}px`,
                        fontFamily: cfg.fontFamily || 'Inter, sans-serif',
                        lineHeight: '1',
                        color: cfg.titleColor || '#ffffff',
                        WebkitTextStroke: `${Math.max(0, Number(cfg.titleStrokeWidth || 0) * scale)}px ${cfg.titleStrokeColor || '#000000'}`,
                        paintOrder: 'stroke fill',
                        zIndex: 30,
                    };
                };

                const catalogTemplateThumbnailTextBoxStyle = (box, template) => {
                    const canvasWidth = Number(template?.canvas_width || 1080);
                    const canvasHeight = Number(template?.canvas_height || (template?.format === 'feed' ? 1350 : 1920));
                    const scale = catalogTemplateThumbnailScale(template);
                    const shape = box.shape || 'none';
                    return {
                        left: `${(Number(box.x || canvasWidth / 2) / canvasWidth) * 100}%`,
                        top: `${(Number(box.y || 100) / canvasHeight) * 100}%`,
                        transform: 'translate(-50%, -50%)',
                        fontSize: `${Math.max(1.5, Number(box.fontSize || 16) * scale)}px`,
                        fontFamily: box.fontFamily || template?.layout_config?.fontFamily || 'Inter, sans-serif',
                        color: box.color || '#ffffff',
                        backgroundColor: shape !== 'none' ? (box.bgColor || 'rgba(0,0,0,0.6)') : 'transparent',
                        borderRadius: shape === 'pill' ? '9999px' : (shape === 'rounded' ? '3px' : (shape === 'tag' ? '2px' : '0px')),
                        padding: shape !== 'none' ? `${Math.max(1, Number(box.paddingY || 6) * scale)}px ${Math.max(1, Number(box.paddingX || 12) * scale)}px` : '0',
                        zIndex: 25,
                    };
                };

                const catalogTemplateThumbnailTableHeaderStyle = (template) => {
                    const style = catalogTableHeaderPreviewStyle(template);
                    const cfg = template?.layout_config || defaultCatalogLayoutConfig();
                    return { ...style, fontSize: `${Math.max(1.5, Number(cfg.headerFontSize || 13) * catalogTemplateThumbnailScale(template))}px` };
                };

                const catalogTemplateThumbnailTableRowStyle = (template, index = 0) => {
                    const style = catalogTableRowPreviewStyle(template, index);
                    const cfg = template?.layout_config || defaultCatalogLayoutConfig();
                    return { ...style, fontSize: `${Math.max(1.5, Number(cfg.bodyFontSize || 13) * catalogTemplateThumbnailScale(template))}px` };
                };

                const catalogTemplateThumbnailCardTextStyle = (template, type) => {
                    const cfg = template?.layout_config || defaultCatalogLayoutConfig();
                    const cardCfg = cfg.card_config || {};
                    const fontSize = type === 'model' ? Number(cardCfg.modelFontSize || 13) : Number(cardCfg.priceFontSize || 13);
                    return {
                        color: type === 'model' ? (cardCfg.modelColor || '#ffffff') : (cardCfg.priceColor || '#ffffff'),
                        fontSize: `${Math.max(1.5, fontSize * catalogTemplateThumbnailScale(template))}px`,
                        lineHeight: '1.15',
                    };
                };

                const catalogLayoutPreviewStyle = (template) => {
                    const cfg = template?.layout_config || defaultCatalogLayoutConfig();
                    const canvasWidth = Number(template?.canvas_width || (template?.format === 'feed' ? 1080 : 1080));
                    const canvasHeight = Number(template?.canvas_height || (template?.format === 'feed' ? 1350 : 1920));
                    const width = Number(cfg.width || canvasWidth - 128);
                    const height = Number(cfg.headerHeight || 34) + (Number(cfg.maxItems || 10) * Number(cfg.rowHeight || 28));

                    return {
                        left: `${((Number(cfg.x || 64)) / canvasWidth) * 100}%`,
                        top: `${((Number(cfg.y || 255)) / canvasHeight) * 100}%`,
                        width: `${(width / canvasWidth) * 100}%`,
                        height: `${(height / canvasHeight) * 100}%`,
                        borderRadius: `${(Number(cfg.borderRadius || 10) / canvasHeight) * 100}%`,
                        zIndex: 20,
                    };
                };

                const catalogTableHeaderPreviewStyle = (template) => {
                    const cfg = template?.layout_config || defaultCatalogLayoutConfig();
                    const height = Number(cfg.headerHeight || 34) + (Number(cfg.maxItems || 10) * Number(cfg.rowHeight || 28));
                    const canvasHeight = Number(template?.canvas_height || (template?.format === 'feed' ? 1350 : 1920));

                    return {
                        height: `${(Number(cfg.headerHeight || 34) / height) * 100}%`,
                        backgroundColor: cfg.headerColor || '#3f3f3f',
                        color: cfg.headerTextColor || '#ffffff',
                        fontSize: `${Math.max(7, (Number(cfg.headerFontSize || 13) / canvasHeight) * 640)}px`,
                        fontWeight: 700,
                        textTransform: 'uppercase',
                    };
                };

                const catalogTableRowsPreviewStyle = (template) => {
                    const cfg = template?.layout_config || defaultCatalogLayoutConfig();
                    const height = Number(cfg.headerHeight || 34) + (Number(cfg.maxItems || 10) * Number(cfg.rowHeight || 28));

                    return {
                        height: `${((Number(cfg.maxItems || 10) * Number(cfg.rowHeight || 28)) / height) * 100}%`,
                    };
                };

                const catalogTableRowPreviewStyle = (template, index = 0) => {
                    const cfg = template?.layout_config || defaultCatalogLayoutConfig();
                    const canvasHeight = Number(template?.canvas_height || (template?.format === 'feed' ? 1350 : 1920));

                    const maxItems = Math.max(1, Number(cfg.maxItems || 10));

                    return {
                        height: `${100 / maxItems}%`,
                        backgroundColor: index % 2 === 0 ? (cfg.rowEvenColor || 'rgba(255,255,255,0.08)') : (cfg.rowOddColor || 'rgba(255,255,255,0.55)'),
                        color: cfg.textColor || '#3f3f3f',
                        fontSize: `${Math.max(7, (Number(cfg.bodyFontSize || 13) / canvasHeight) * 640)}px`,
                        fontWeight: 600,
                    };
                };

                const catalogCardPreviewStyle = (template) => {
                    const cfg = template?.layout_config || defaultCatalogLayoutConfig();
                    const canvasWidth = Number(template?.canvas_width || 1080);
                    const canvasHeight = Number(template?.canvas_height || 1920);
                    const columns = Math.max(1, Number(cfg.cardColumns || 3));

                    return {
                        left: `${(Number(cfg.x || 64) / canvasWidth) * 100}%`,
                        top: `${(Number(cfg.y || 255) / canvasHeight) * 100}%`,
                        width: `${(Number(cfg.width || canvasWidth - 128) / canvasWidth) * 100}%`,
                        gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))`,
                    };
                };

                const catalogCardPreviewTextStyle = (template, type) => {
                    const cfg = template?.layout_config || defaultCatalogLayoutConfig();
                    const cardCfg = cfg.card_config || {};
                    const canvasHeight = Number(template?.canvas_height || 1920);
                    const fontSize = type === 'model' ? Number(cardCfg.modelFontSize || 13) : Number(cardCfg.priceFontSize || 13);

                    return {
                        color: type === 'model' ? (cardCfg.modelColor || '#ffffff') : (cardCfg.priceColor || '#ffffff'),
                        fontSize: `${Math.max(7, (fontSize / canvasHeight) * 640)}px`,
                    };
                };

                const catalogPreviewMeasureCtx = document.createElement('canvas').getContext('2d');

                const catalogPreviewPriceStyle = (template, type) => {
                    const cfg = template?.layout_config || defaultCatalogLayoutConfig();
                    const canvasHeight = Number(template?.canvas_height || (template?.format === 'feed' ? 1350 : 1920));

                    return {
                        color: type === 'normal' ? (cfg.normalPriceColor || '#3f3f3f') : (cfg.specialPriceColor || '#3f3f3f'),
                        fontSize: `${Math.max(7, (Number(cfg.priceFontSize || 13) / canvasHeight) * 640)}px`,
                    };
                };

                const catalogPreviewStrikeStyle = (template, value) => {
                    const cfg = template?.layout_config || defaultCatalogLayoutConfig();
                    const canvasHeight = Number(template?.canvas_height || (template?.format === 'feed' ? 1350 : 1920));
                    const scale = 640 / canvasHeight;
                    const fontSize = Math.max(7, Number(cfg.priceFontSize || 13) * scale);
                    const rise = 10 * scale;

                    catalogPreviewMeasureCtx.font = `600 ${fontSize}px Inter, Arial, sans-serif`;
                    const text = formatCatalogPrice(value);
                    const width = Math.max(1, catalogPreviewMeasureCtx.measureText(text).width);
                    const angleDeg = -Math.atan2(rise, width) * (180 / Math.PI);

                    return {
                        borderTopWidth: `${Math.max(0.5, 2 * scale)}px`,
                        borderTopColor: cfg.strikeColor || '#dc2626',
                        transform: `rotate(${angleDeg}deg)`,
                    };
                };

                const formatPricelistPrice = (value) => {
                    const number = Number(value || 0);
                    return number ? `Rp ${number.toLocaleString('id-ID')}` : '—';
                };

                const formatCatalogPrice = (value) => {
                    const number = Number(value || 0);
                    return number ? number.toLocaleString('id-ID') : '—';
                };

                const catalogProductType = (row) => {
                    const seri = String(row.seri || row.nama_produk || '').trim();
                    let name = String(row.nama_produk || row.seri || '').trim();
                    if (!name) return seri || '-';

                    let label = name;
                    const brand = String(row.brand || '').trim().toUpperCase();
                    if (brand && label.toUpperCase().startsWith(brand)) {
                        label = label.slice(brand.length).trim();
                    }
                    const storage = String(row.storage || '').trim().toUpperCase();
                    const ram = String(row.ram || '').trim().toUpperCase();
                    const suffix = [];
                    if (ram) suffix.push(ram.replace(/\s+/g, ''));
                    if (storage) suffix.push(storage.replace(/\s+/g, ''));
                    if (suffix.length) {
                        const tail = suffix.join('/');
                        if (label.toUpperCase().endsWith(tail)) {
                            label = label.slice(0, label.length - tail.length).trim();
                        }
                    }

                    return label || seri || '-';
                };

                const catalogCanvasSize = (template) => ({
                    width: Number(template?.canvas_width || (template?.format === 'a4' ? 1240 : 1080)),
                    height: Number(template?.canvas_height || (template?.format === 'a4' ? 1754 : (template?.format === 'feed' ? 1350 : 1920))),
                });

                const catalogA4Cfg = (template, rowCount = 0) => {
                    if (template?.format !== 'a4') return null;
                    const size = catalogCanvasSize(template);
                    const pageTarget = catalogSelectedSheet.value === 'all' ? 2 : 1;
                    const rowsPerPage = Math.max(1, Math.ceil(rowCount / pageTarget));
                    const y = 118;
                    const bottom = 64;
                    const headerHeight = 24;
                    const rowHeight = Math.max(12, Math.floor((size.height - y - bottom - headerHeight) / rowsPerPage));
                    return {
                        ...defaultCatalogLayoutConfig(),
                        ...template.layout_config,
                        x: 40,
                        y,
                        width: size.width - 80,
                        maxItems: rowsPerPage,
                        headerHeight,
                        rowHeight,
                        borderRadius: 8,
                        headerFontSize: 10,
                        bodyFontSize: Math.max(8, Math.min(10, rowHeight - 3)),
                        priceFontSize: Math.max(8, Math.min(10, rowHeight - 3)),
                        titleFontSize: 24,
                        titleX: size.width / 2,
                        titleY: 74,
                        showBrandInline: true,
                    };
                };

                const fillRoundedRect = (ctx, x, y, width, height, radius) => {
                    const r = Math.min(radius, height / 2, width / 2);
                    ctx.beginPath();
                    ctx.moveTo(x + r, y);
                    ctx.lineTo(x + width - r, y);
                    ctx.quadraticCurveTo(x + width, y, x + width, y + r);
                    ctx.lineTo(x + width, y + height - r);
                    ctx.quadraticCurveTo(x + width, y + height, x + width - r, y + height);
                    ctx.lineTo(x + r, y + height);
                    ctx.quadraticCurveTo(x, y + height, x, y + height - r);
                    ctx.lineTo(x, y + r);
                    ctx.quadraticCurveTo(x, y, x + r, y);
                    ctx.closePath();
                    ctx.fill();
                };

                const catalogColumnGeometry = (x, width, priceKey = 'special_price') => {
                    const includeNormalPrice = priceKey !== 'harga_jual';
                    const typeWidth = width * (includeNormalPrice ? 0.40 : 0.48);
                    const ramWidth = width * 0.12;
                    const storageWidth = width * 0.15;
                    const normalWidth = includeNormalPrice ? width * 0.165 : 0;
                    const specialWidth = width - typeWidth - ramWidth - storageWidth - normalWidth;

                    return {
                        includeNormalPrice,
                        typeX: x,
                        ramX: x + typeWidth,
                        storageX: x + typeWidth + ramWidth,
                        normalX: x + typeWidth + ramWidth + storageWidth,
                        specialX: x + typeWidth + ramWidth + storageWidth + normalWidth,
                        rightX: x + width,
                    };
                };

                const drawCatalogTableHeader = (ctx, cfg, geometry, top, priceKey = 'special_price') => {
                    const priceLabel = priceKey === 'harga_jual' ? 'HARGA JUAL' : 'SPECIAL PRICE';
                    ctx.font = `700 ${Number(cfg.headerFontSize || 13)}px Inter, Arial, sans-serif`;
                    ctx.fillStyle = cfg.headerColor || '#3f3f3f';
                    fillRoundedRect(ctx, geometry.typeX, top, geometry.rightX - geometry.typeX, Number(cfg.headerHeight || 34), Number(cfg.borderRadius || 12));
                    ctx.fillStyle = cfg.headerTextColor || '#ffffff';
                    ctx.textBaseline = 'middle';
                    ctx.textAlign = 'left';
                    ctx.fillText('TYPE', geometry.typeX + 16, top + (Number(cfg.headerHeight || 34) / 2), geometry.ramX - geometry.typeX - 32);
                    ctx.textAlign = 'center';
                    ctx.fillText('RAM', (geometry.ramX + geometry.storageX) / 2, top + (Number(cfg.headerHeight || 34) / 2), geometry.storageX - geometry.ramX);
                    ctx.fillText('STORAGE', (geometry.storageX + geometry.normalX) / 2, top + (Number(cfg.headerHeight || 34) / 2), geometry.normalX - geometry.storageX);
                    ctx.textAlign = 'right';
                    if (geometry.includeNormalPrice) {
                        ctx.fillText('NORMAL PRICE', geometry.specialX - 12, top + (Number(cfg.headerHeight || 34) / 2), geometry.specialX - geometry.normalX - 24);
                    }
                    ctx.fillText(priceLabel, geometry.rightX - 12, top + (Number(cfg.headerHeight || 34) / 2), geometry.rightX - geometry.specialX - 24);
                };

                const drawCatalogTableRow = (ctx, cfg, geometry, row, top, index = 0, priceKey = 'special_price') => {
                    const rowHeight = Number(cfg.rowHeight || 28);
                    const fontSize = Number(cfg.bodyFontSize || 13);
                    const yCenter = top + (rowHeight / 2);

                    ctx.textBaseline = 'middle';

                    ctx.fillStyle = index % 2 === 0 ? (cfg.rowEvenColor || 'rgba(255,255,255,0.08)') : (cfg.rowOddColor || 'rgba(255,255,255,0.55)');
                    ctx.fillRect(geometry.typeX, top, geometry.rightX - geometry.typeX, rowHeight);
                    ctx.strokeStyle = cfg.rowEvenColor || 'rgba(255,255,255,0.08)';
                    ctx.lineWidth = 1;
                    ctx.beginPath();
                    ctx.moveTo(geometry.typeX, top + rowHeight - 0.5);
                    ctx.lineTo(geometry.rightX, top + rowHeight - 0.5);
                    ctx.stroke();

                    ctx.font = `600 ${fontSize}px Inter, Arial, sans-serif`;
                    ctx.fillStyle = cfg.textColor || '#3f3f3f';
                    ctx.textAlign = 'left';
                    const typeLabel = cfg.showBrandInline ? `${row.source_sheet || row.brand || ''} ${catalogProductType(row)}`.trim() : catalogProductType(row);
                    ctx.fillText(typeLabel, geometry.typeX + 16, yCenter, geometry.ramX - geometry.typeX - 32);

                    ctx.textAlign = 'center';
                    ctx.fillText(String(row.ram || '—'), (geometry.ramX + geometry.storageX) / 2, yCenter, geometry.storageX - geometry.ramX);
                    ctx.fillText(String(row.storage || '—'), (geometry.storageX + geometry.normalX) / 2, yCenter, geometry.normalX - geometry.storageX);

                    ctx.font = `600 ${Number(cfg.priceFontSize || 13)}px Inter, Arial, sans-serif`;
                    ctx.textAlign = 'right';
                    if (geometry.includeNormalPrice) {
                        ctx.fillStyle = cfg.normalPriceColor || '#3f3f3f';
                        const normalPriceText = formatCatalogPrice(row.harga_nasional);
                        const normalPriceX = geometry.specialX - 12;
                        const normalPriceMaxWidth = geometry.specialX - geometry.normalX - 24;
                        ctx.fillText(normalPriceText, normalPriceX, yCenter, normalPriceMaxWidth);
                        const normalPriceWidth = Math.min(ctx.measureText(normalPriceText).width, normalPriceMaxWidth);
                        ctx.strokeStyle = '#dc2626';
                        ctx.lineWidth = 2;
                        ctx.beginPath();
                        ctx.moveTo(normalPriceX - normalPriceWidth, yCenter + 5);
                        ctx.lineTo(normalPriceX, yCenter - 5);
                        ctx.stroke();
                    }

                    ctx.fillStyle = cfg.specialPriceColor || '#3f3f3f';
                    ctx.fillText(formatCatalogPrice(row[priceKey]), geometry.rightX - 12, yCenter, geometry.rightX - geometry.specialX - 24);
                };

                const drawCatalogPage = (template, rows, title = '', priceKey = 'special_price', cfgOverride = null) => new Promise((resolve, reject) => {
                    const canvas = document.createElement('canvas');
                    const size = catalogCanvasSize(template);
                    canvas.width = size.width;
                    canvas.height = size.height;
                    const ctx = canvas.getContext('2d');
                    const cfg = cfgOverride || catalogA4Cfg(template, rows.length) || (Number(template.layout_config?.layout_version || 0) >= 2 ? { ...defaultCatalogLayoutConfig(), ...template.layout_config } : defaultCatalogLayoutConfig());
                    const footerSafeArea = 90;
                    const paintTable = async () => {
                        await drawCatalogAssets(ctx, cfg);
                        const x = Number(cfg.x || 64);
                        const y = Number(cfg.y || 255);
                        const width = Number(cfg.width || (canvas.width - 128));
                        const headerHeight = Number(cfg.headerHeight || 34);
                        const rowHeight = Number(cfg.rowHeight || 28);
                        const geometry = catalogColumnGeometry(x, width, priceKey);
                        const titleText = String(title || '').trim().toUpperCase();

                        if (titleText) {
                            const [category, condition] = titleText.split('\n');
                            const titleFontSize = Number(cfg.titleFontSize || 34);
                            const conditionFontSize = Math.round(titleFontSize * 0.6);
                            const titleX = Number(cfg.titleX ?? x);
                            const titleY = Number(cfg.titleY ?? Math.max(0, y - titleFontSize - conditionFontSize - 16));
                            ctx.textAlign = 'left';
                            ctx.textBaseline = 'top';
                            ctx.font = `700 ${titleFontSize}px ${cfg.fontFamily || 'Inter, Arial, sans-serif'}`;
                            ctx.fillStyle = cfg.titleColor || '#ffffff';
                            if (Number(cfg.titleStrokeWidth || 0) > 0) {
                                ctx.strokeStyle = cfg.titleStrokeColor || '#000000';
                                ctx.lineWidth = Number(cfg.titleStrokeWidth);
                                ctx.strokeText(category, titleX, titleY, width);
                            }
                            ctx.fillText(category, titleX, titleY, width);
                            if (condition) {
                                ctx.font = `400 ${conditionFontSize}px ${cfg.fontFamily || 'Inter, Arial, sans-serif'}`;
                                ctx.fillStyle = cfg.labelColor || 'rgba(255,255,255,0.7)';
                                ctx.fillText(condition, titleX, titleY + titleFontSize + 4, width);
                            }
                        }
                        drawCatalogTextBoxes(ctx, cfg, canvas.width, canvas.height);

                        drawCatalogTableHeader(ctx, cfg, geometry, y, priceKey);

                        rows.forEach((row, index) => {
                            drawCatalogTableRow(ctx, cfg, geometry, row, y + headerHeight + (index * rowHeight), index, priceKey);
                        });

                        resolve(canvas.toDataURL('image/png'));
                    };
                    if (template.background_url) {
                        const image = new Image();
                        image.onload = () => {
                            ctx.drawImage(image, 0, 0, canvas.width, canvas.height);
                            paintTable();
                        };
                        image.onerror = reject;
                        image.src = template.background_url;
                    } else {
                        ctx.fillStyle = '#f8fafc';
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                        ctx.fillStyle = '#0f172a';
                        ctx.font = '800 54px Inter, Arial, sans-serif';
                        ctx.fillText('KATALOG PRICELIST', 64, 160);
                        paintTable();
                    }
                });

                const catalogAndroidCardGroups = (rows) => Object.values(rows.reduce((groups, row) => {
                    const key = catalogProductType(row);
                    groups[key] = groups[key] || { model: key, modelKey: androidModelKey(key), variants: [] };
                    groups[key].variants.push(row);
                    return groups;
                }, {}));

                const loadCatalogImage = (src) => new Promise((resolve) => {
                    if (!src) { resolve(null); return; }
                    const img = new Image();
                    img.onload = () => resolve(img);
                    img.onerror = () => resolve(null);
                    img.src = src;
                });

                const catalogAndroidCardHeight = (cardCfg, variantCount) => {
                    const imgH = Math.max(76, Number(cardCfg.imageHeight || 100));
                    const mFs = Number(cardCfg.modelFontSize || 13);
                    const sFs = Math.max(9, Math.round(mFs * 0.85));
                    const lFs = Math.max(9, Math.round(mFs * 0.65));
                    const srpFs = Number(cardCfg.srpFontSize || 11);
                    const rowH = sFs + 6;
                    const rGap = Number(cardCfg.rowGap || 4);
                    const savingsHeight = cardCfg.showHematBadge !== false ? Number(cardCfg.priceFontSize || 13) + 8 : 0;
                    const perVariant = rowH + srpFs + 2 + rGap + savingsHeight;
                    return imgH + 8 + (mFs + 1) * 2 + 3 + lFs + 5 + variantCount * perVariant;
                };

                const drawPricelistCardPage = (template, groups, title = '', priceKey = 'special_price') => new Promise(async (resolve, reject) => {
                    const imageEls = await Promise.all(groups.map((g) => loadCatalogImage(androidProductImage(g.variants[0]))));
                    const canvas = document.createElement('canvas');
                    const size = catalogCanvasSize(template);
                    canvas.width = size.width;
                    canvas.height = size.height;
                    const ctx = canvas.getContext('2d');
                    const cfg = Number(template.layout_config?.layout_version || 0) >= 2 ? { ...defaultCatalogLayoutConfig(), ...template.layout_config } : defaultCatalogLayoutConfig();
                    const cardCfg = { ...defaultCatalogCardConfig(), ...(cfg.card_config || catalogCardCfg.value) };
                    const paintCards = async () => {
                        await drawCatalogAssets(ctx, cfg);
                        const x = Number(cfg.x || 64);
                        const y = Number(cfg.y || 255);
                        const width = Number(cfg.width || (canvas.width - 128));
                        const gap = Number(cfg.gap || 42);
                        const cols = Number(cfg.cardColumns || catalogColumnsPerRow.value || 3);
                        const cardW = (width - (gap * (cols - 1))) / cols;
                        const imgH = Math.max(76, Number(cardCfg.imageHeight || 100));
                        const mFs = Number(cardCfg.modelFontSize || 13);
                        const sFs = Math.max(9, Math.round(mFs * 0.85));
                        const lFs = Math.max(9, Math.round(mFs * 0.65));
                        const srpFs = Number(cardCfg.srpFontSize || 11);
                        const badgePadX = 6;
                        const badgePadY = 3;
                        const rowH = sFs + badgePadY * 2;
                        const titleText = String(title || '').trim().toUpperCase();
                        if (titleText) {
                            const [category, condition] = titleText.split('\n');
                            const titleFontSize = Number(cfg.titleFontSize || 34);
                            const conditionFontSize = Math.round(titleFontSize * 0.6);
                            const cardTitleX = Number(cfg.titleX ?? x);
                            const cardTitleY = Number(cfg.titleY ?? Math.max(0, y - titleFontSize - conditionFontSize - 16));
                            ctx.textAlign = 'left';
                            ctx.textBaseline = 'top';
                            ctx.font = `700 ${titleFontSize}px ${cfg.fontFamily || 'Inter, Arial, sans-serif'}`;
                            ctx.fillStyle = cfg.titleColor || '#ffffff';
                            if (Number(cfg.titleStrokeWidth || 0) > 0) {
                                ctx.strokeStyle = cfg.titleStrokeColor || '#000000';
                                ctx.lineWidth = Number(cfg.titleStrokeWidth);
                                ctx.strokeText(category, cardTitleX, cardTitleY, width);
                            }
                            ctx.fillText(category, cardTitleX, cardTitleY, width);
                            if (condition) {
                                ctx.font = `400 ${conditionFontSize}px ${cfg.fontFamily || 'Inter, Arial, sans-serif'}`;
                                ctx.fillStyle = cfg.labelColor || 'rgba(255,255,255,0.7)';
                                ctx.fillText(condition, cardTitleX, cardTitleY + titleFontSize + 4, width);
                            }
                        }
                        drawCatalogTextBoxes(ctx, cfg, canvas.width, canvas.height);

                        let curX = x;
                        let curY = y;
                        let colIdx = 0;
                        let rowMaxH = 0;
                        groups.forEach((group, gi) => {
                            const imgEl = imageEls[gi] || null;
                            const variantCount = group.variants.length;
                            const used = catalogAndroidCardHeight(cardCfg, variantCount);
                            rowMaxH = Math.max(rowMaxH, used);

                            // card: image, name, "Harga mulai", variant rows
                            let innerY = curY;
                            if (imgEl) {
                                const aspect = imgEl.naturalWidth / imgEl.naturalHeight;
                                let dw = cardW, dh = dw / aspect;
                                if (dh > imgH) { dh = imgH; dw = dh * aspect; }
                                ctx.drawImage(imgEl, curX + (cardW - dw) / 2, innerY + (imgH - dh), dw, dh);
                            }
                            innerY += imgH + 8;

                            ctx.font = `700 ${mFs}px Inter, Arial, sans-serif`;
                            ctx.fillStyle = cardCfg.modelColor || '#ffffff';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'top';
                            const words = group.model.split(' ');
                            const lines = [];
                            let ln = '';
                            for (const w of words) {
                                const test = ln ? ln + ' ' + w : w;
                                if (ctx.measureText(test).width > cardW - 8) { if (ln) lines.push(ln); ln = w; }
                                else ln = test;
                            }
                            if (ln) lines.push(ln);
                            for (const l of lines) {
                                ctx.fillText(l, curX + cardW / 2, innerY, cardW);
                                innerY += mFs + 1;
                            }
                            innerY += 3;

                            ctx.font = `400 italic ${lFs}px Inter, Arial, sans-serif`;
                            ctx.fillStyle = cardCfg.labelColor || 'rgba(255,255,255,0.6)';
                            ctx.textAlign = 'center';
                            ctx.fillText('Harga mulai', curX + cardW / 2, innerY, cardW);
                            innerY += lFs + 5;

                            group.variants.forEach((row) => {
                                const storage = [row.ram, row.storage].filter(Boolean).join('/');
                                const price = row[priceKey];
                                if (!price) return;
                                const normal = row.harga_nasional;
                                const showSrp = priceKey !== 'harga_jual' && normal && Number(normal) > Number(price || 0);
                                const priceText = formatCatalogPrice(price);
                                const srpText = showSrp ? formatCatalogPrice(normal) : '';
                                const currentRowH = srpText ? rowH + srpFs + 2 : rowH;

                                const rowInset = Math.max(12, Math.min(28, cardW * 0.12));
                                const rowLeftX = curX + rowInset;
                                const priceRightX = curX + cardW - rowInset;
                                const rowMidY = innerY + currentRowH / 2;
                                ctx.font = `700 ${sFs}px Inter, Arial, sans-serif`;
                                const badgeTextW = storage ? ctx.measureText(storage).width : 0;
                                const badgeW = storage ? Math.min(badgeTextW + badgePadX * 2, Math.max(24, cardW * 0.36)) : 0;

                                if (storage) {
                                    ctx.font = `700 ${sFs}px Inter, Arial, sans-serif`;
                                    ctx.strokeStyle = 'rgba(255,255,255,0.55)';
                                    ctx.lineWidth = 1;
                                    const br = rowH / 2;
                                    const by = rowMidY - rowH / 2;
                                    ctx.beginPath();
                                    ctx.moveTo(rowLeftX + br, by);
                                    ctx.lineTo(rowLeftX + badgeW - br, by);
                                    ctx.quadraticCurveTo(rowLeftX + badgeW, by, rowLeftX + badgeW, by + br);
                                    ctx.lineTo(rowLeftX + badgeW, by + rowH - br);
                                    ctx.quadraticCurveTo(rowLeftX + badgeW, by + rowH, rowLeftX + badgeW - br, by + rowH);
                                    ctx.lineTo(rowLeftX + br, by + rowH);
                                    ctx.quadraticCurveTo(rowLeftX, by + rowH, rowLeftX, by + rowH - br);
                                    ctx.lineTo(rowLeftX, by + br);
                                    ctx.quadraticCurveTo(rowLeftX, by, rowLeftX + br, by);
                                    ctx.closePath();
                                    ctx.stroke();
                                    ctx.fillStyle = cardCfg.badgeTextColor || '#ffffff';
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle';
                                    ctx.fillText(storage, rowLeftX + badgeW / 2, rowMidY, badgeW - 4);
                                }

                                if (srpText) {
                                    ctx.font = `600 ${srpFs}px Inter, Arial, sans-serif`;
                                    ctx.fillStyle = cardCfg.srpColor || 'rgba(255,255,255,0.5)';
                                    ctx.textAlign = 'right';
                                    ctx.textBaseline = 'top';
                                    ctx.fillText(srpText, priceRightX, innerY);
                                    const srpW = ctx.measureText(srpText).width;
                                    ctx.strokeStyle = '#dc2626';
                                    ctx.lineWidth = 1.5;
                                    ctx.beginPath();
                                    ctx.moveTo(priceRightX - srpW, innerY + srpFs * 0.82);
                                    ctx.lineTo(priceRightX, innerY + srpFs * 0.18);
                                    ctx.stroke();
                                }
                                const priceFontSize = Number(cardCfg.priceFontSize || 13);
                                const priceY = srpText ? innerY + srpFs + 2 : rowMidY;
                                ctx.font = `700 ${priceFontSize}px ${cfg.fontFamily || 'Inter, Arial, sans-serif'}`;
                                ctx.fillStyle = cardCfg.priceColor || '#ffffff';
                                ctx.textAlign = 'right';
                                ctx.textBaseline = srpText ? 'top' : 'middle';
                                if (cardCfg.priceShape && cardCfg.priceShape !== 'plain') {
                                    const priceBackgroundWidth = ctx.measureText(priceText).width + 12;
                                    const priceBackgroundHeight = priceFontSize + 8;
                                    ctx.fillStyle = cardCfg.priceBadgeBg || 'rgba(255,255,255,0.18)';
                                    const priceBackgroundX = priceRightX - priceBackgroundWidth;
                                    const priceBackgroundY = priceY - priceBackgroundHeight / 2;
                                    const radius = cardCfg.priceShape === 'pill' ? priceBackgroundHeight / 2 : 6;
                                    ctx.beginPath();
                                    ctx.roundRect(priceBackgroundX, priceBackgroundY, priceBackgroundWidth, priceBackgroundHeight, radius);
                                    ctx.fill();
                                    ctx.fillStyle = cardCfg.priceColor || '#ffffff';
                                }
                                ctx.fillText(priceText, priceRightX - (cardCfg.priceShape && cardCfg.priceShape !== 'plain' ? 6 : 0), priceY);

                                const savings = Number(normal || 0) - Number(price || 0);
                                const showSavings = cardCfg.showHematBadge !== false && savings > 0;
                                if (showSavings) {
                                    const savingsText = `HEMAT ${formatCatalogPrice(savings)}`;
                                    const savingsFontSize = Math.max(8, priceFontSize - 3);
                                    ctx.font = `700 ${savingsFontSize}px ${cfg.fontFamily || 'Inter, Arial, sans-serif'}`;
                                    const savingsWidth = Math.min(cardW * 0.9, ctx.measureText(savingsText).width + 12);
                                    const savingsHeight = Math.max(16, priceFontSize + 4);
                                    const savingsX = curX + (cardW - savingsWidth) / 2;
                                    const savingsY = priceY + priceFontSize + 4;
                                    ctx.fillStyle = cardCfg.hematBadgeBg || '#16a34a';
                                    ctx.beginPath();
                                    ctx.roundRect(savingsX, savingsY, savingsWidth, savingsHeight, 8);
                                    ctx.fill();
                                    ctx.fillStyle = cardCfg.hematBadgeColor || '#ffffff';
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle';
                                    ctx.fillText(savingsText, curX + cardW / 2, savingsY + savingsHeight / 2);
                                }

                                innerY += currentRowH + rGap + (showSavings ? priceFontSize + 8 : 0);
                            });

                            colIdx++;
                            if (colIdx >= cols) {
                                colIdx = 0;
                                curX = x;
                                curY += rowMaxH + gap;
                                rowMaxH = 0;
                             } else {
                                 curX += cardW + gap;
                             }
                         });
                         resolve(canvas.toDataURL('image/png'));
                    };
                    if (template.background_url) {
                        const image = new Image();
                        image.onload = () => { ctx.drawImage(image, 0, 0, canvas.width, canvas.height); paintCards(); };
                        image.onerror = reject;
                        image.src = template.background_url;
                    } else {
                        ctx.fillStyle = '#f8fafc';
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                        paintCards();
                    }
                });

                let catalogTemplateEditorPreviewTimer = null;
                const renderCatalogTemplateEditorPreview = async () => {
                    if (!catalogTemplateModalOpen.value || catalogTemplateForm.value.output_mode !== 'katalog') {
                        catalogTemplateEditorPreviewImage.value = '';
                        return;
                    }

                    const template = {
                        ...catalogTemplateForm.value,
                        canvas_width: Number(catalogTemplateForm.value.canvas_width || 1080),
                        canvas_height: Number(catalogTemplateForm.value.canvas_height || (catalogTemplateForm.value.format === 'feed' ? 1350 : 1920)),
                    };
                    const rows = catalogCardPreviewRows.value;
                    const groups = catalogAndroidCardGroups(rows);
                    const title = catalogTitleForOutput(template, rows[0]?.source_sheet || 'KATEGORI', 'katalog', catalogPriceKey.value);

                    try {
                        catalogTemplateEditorPreviewImage.value = await drawPricelistCardPage(template, groups, title, catalogPriceKey.value);
                    } catch (error) {
                        catalogTemplateEditorPreviewImage.value = '';
                    }
                };
                const scheduleCatalogTemplateEditorPreview = () => {
                    if (catalogTemplateEditorPreviewTimer) window.clearTimeout(catalogTemplateEditorPreviewTimer);
                    catalogTemplateEditorPreviewTimer = window.setTimeout(renderCatalogTemplateEditorPreview, 120);
                };
                watch([catalogTemplateModalOpen, catalogTemplateForm, catalogPriceKey], scheduleCatalogTemplateEditorPreview, { deep: true });

                const catalogTitleForOutput = (template, title, outputMode = 'list', priceKey = 'special_price') => {
                    const category = String(title || 'KATEGORI').trim().toUpperCase();

                    return `${category}\n${catalogConditionLabel(priceKey)}`;
                };

                const catalogA4Pages = async (template, rows) => {
                    const sortedRows = [...rows].sort((a, b) => String(a.source_sheet).localeCompare(String(b.source_sheet)) || Number(a.urut || 0) - Number(b.urut || 0));
                    const cfg = catalogA4Cfg(template, sortedRows.length);
                    const maxItems = Math.max(1, Number(cfg.maxItems || Math.ceil(sortedRows.length / 2)));
                    const pages = [];
                    for (let index = 0; index < sortedRows.length; index += maxItems) {
                        pages.push(await drawCatalogPage(template, sortedRows.slice(index, index + maxItems), catalogTitleForOutput(template, 'PRICELIST', 'list', catalogPriceKey.value), catalogPriceKey.value, cfg));
                    }
                    return pages.slice(0, 2);
                };

                const generateCatalogPreview = async () => {
                    const template = catalogSelectedTemplate.value;
                    if (!template) {
                        showNotification('Pilih template dulu', 'warning');
                        return;
                    }
                    const rows = catalogRowsForGeneration.value;
                    if (!rows.length) {
                        showNotification('Tidak ada produk untuk dibuat katalog', 'warning');
                        return;
                    }
                    catalogGenerating.value = true;
                    try {
                        if (template.format === 'a4' && catalogOutputMode.value === 'list' && catalogSelectedSheet.value === 'all') {
                            const pages = await catalogA4Pages(template, rows);
                            catalogPreviewImages.value = pages;
                            showNotification(`${pages.length} halaman A4 katalog dibuat`);
                            return;
                        }
                        const cfg = Number(template.layout_config?.layout_version || 0) >= 2 ? { ...defaultCatalogLayoutConfig(), ...template.layout_config } : defaultCatalogLayoutConfig();
                        const height = Number(template.canvas_height || (template.format === 'feed' ? 1350 : 1920));
                        const y = Number(cfg.y || 255);
                        const rowHeight = Number(cfg.rowHeight || 28);
                        const headerHeight = Number(cfg.headerHeight || 34);
                        const footerSafeArea = 90;
                        const isCardCatalog = catalogOutputMode.value === 'katalog';
                        const cardConfig = { ...defaultCatalogCardConfig(), ...(cfg.card_config || catalogCardCfg.value) };
                        const cardHeight = Number(cardConfig.imageHeight || 100) + 118;
                        const cardGap = 18;
                        const autoMaxItems = isCardCatalog
                            ? Math.max(1, Math.floor((height - y - footerSafeArea + cardGap) / (cardHeight + cardGap)) * Number(cfg.cardColumns || catalogColumnsPerRow.value || 3))
                            : Math.max(1, Math.floor((height - y - headerHeight - footerSafeArea) / rowHeight));
                        const maxItems = Math.max(1, Math.min(Number(cfg.maxItems || autoMaxItems), autoMaxItems));
                        const pages = [];
                        const rowGroups = catalogSelectedSheet.value === 'all'
                            ? rows.reduce((groups, row) => {
                                const sheet = row.source_sheet || 'SPECIAL PRICE';
                                groups[sheet] = groups[sheet] || [];
                                groups[sheet].push(row);
                                return groups;
                            }, {})
                            : { [catalogSelectedSheet.value]: rows };
                        for (const [title, groupRows] of Object.entries(rowGroups)) {
                            if (isCardCatalog) {
                                const cardGroups = catalogAndroidCardGroups(groupRows);
                                for (let index = 0; index < cardGroups.length; index += maxItems) {
                                    const chunk = cardGroups.slice(index, index + maxItems);
                                     pages.push(await drawPricelistCardPage(template, chunk, catalogTitleForOutput(template, title, 'katalog', catalogPriceKeyForSheet(title)), catalogPriceKeyForSheet(title)));
                                }
                            } else {
                                for (let index = 0; index < groupRows.length; index += maxItems) {
                                    const chunk = groupRows.slice(index, index + maxItems);
                                     pages.push(await drawCatalogPage(template, chunk, catalogTitleForOutput(template, title, 'list', catalogPriceKeyForSheet(title)), catalogPriceKeyForSheet(title)));
                                }
                            }
                        }
                        catalogPreviewImages.value = pages;
                        showNotification(`${pages.length} halaman katalog dibuat`);
                    } catch (error) {
                        notifyError('', error, 'Gagal generate katalog.');
                    } finally {
                        catalogGenerating.value = false;
                    }
                };

                // Convert any dataURL to JPEG Uint8Array (strips alpha, safe for PDF DCTDecode)
                const _toJpegData = (dataUrl) => new Promise((resolve) => {
                    const img = new Image();
                    img.onload = () => {
                        const c = document.createElement('canvas');
                        c.width = img.width; c.height = img.height;
                        const ctx = c.getContext('2d');
                        ctx.fillStyle = '#000';
                        ctx.fillRect(0, 0, c.width, c.height);
                        ctx.drawImage(img, 0, 0);
                        const j = c.toDataURL('image/jpeg', 0.92);
                        const raw = atob(j.split(',')[1]);
                        const b = new Uint8Array(raw.length);
                        for (let k = 0; k < raw.length; k++) b[k] = raw.charCodeAt(k);
                        resolve({ bytes: b, width: c.width, height: c.height });
                    };
                    img.src = dataUrl;
                });

                // Build a valid PDF binary from JPEG page data — no external library
                const _buildImagePdf = (pages) => {
                    const mmPt = 72 / 25.4;
                    const enc = new TextEncoder();
                    const chunks = [];
                    let pos = 0;
                    const t = (s) => { const b = enc.encode(s); chunks.push(b); pos += b.length; };
                    const bi = (arr) => { chunks.push(arr); pos += arr.length; };

                    t('%PDF-1.4\n');
                    const n = pages.length;
                    const objOff = {};

                    objOff[1] = pos;
                    t(`1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n`);

                    const kids = pages.map((_, i) => `${3 + i * 3} 0 R`).join(' ');
                    objOff[2] = pos;
                    t(`2 0 obj\n<< /Type /Pages /Kids [${kids}] /Count ${n} >>\nendobj\n`);

                    for (let i = 0; i < n; i++) {
                        const { bytes, width, height } = pages[i];
                        const pId = 3 + i * 3, iId = 4 + i * 3, cId = 5 + i * 3;
                        const ptW = Math.round(210 * mmPt);
                        const ptH = Math.round(ptW * height / width);
                        const cs = enc.encode(`q ${ptW} 0 0 ${ptH} 0 0 cm /Im${i} Do Q\n`);

                        objOff[pId] = pos;
                        t(`${pId} 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ${ptW} ${ptH}]\n/Resources << /XObject << /Im${i} ${iId} 0 R >> >>\n/Contents ${cId} 0 R >>\nendobj\n`);

                        objOff[iId] = pos;
                        t(`${iId} 0 obj\n<< /Type /XObject /Subtype /Image /Width ${width} /Height ${height}\n/ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ${bytes.length} >>\nstream\n`);
                        bi(bytes);
                        t(`\nendstream\nendobj\n`);

                        objOff[cId] = pos;
                        t(`${cId} 0 obj\n<< /Length ${cs.length} >>\nstream\n`);
                        bi(cs);
                        t(`\nendstream\nendobj\n`);
                    }

                    const xrefPos = pos;
                    const ids = [1, 2, ...pages.flatMap((_, i) => [3 + i * 3, 4 + i * 3, 5 + i * 3])];
                    t(`xref\n0 ${ids.length + 1}\n0000000000 65535 f \n`);
                    for (const id of ids) t(`${String(objOff[id]).padStart(10, '0')} 00000 n \n`);
                    t(`trailer\n<< /Size ${ids.length + 1} /Root 1 0 R >>\nstartxref\n${xrefPos}\n%%EOF\n`);

                    const total = chunks.reduce((s, c) => s + c.length, 0);
                    const out = new Uint8Array(total);
                    let off = 0;
                    for (const c of chunks) { out.set(c, off); off += c.length; }
                    return out;
                };

                const exportCatalogToPdf = async () => {
                    const template = catalogSelectedTemplate.value;
                    if (!template) { showNotification('Pilih template dulu', 'warning'); return; }
                    const rows = catalogRowsForGeneration.value;
                    if (!rows.length) { showNotification('Tidak ada produk untuk diekspor', 'warning'); return; }

                    catalogGenerating.value = true;
                    try {
                        if (template.format === 'a4' && catalogOutputMode.value === 'list' && catalogSelectedSheet.value === 'all') {
                            const dataUrls = await catalogA4Pages(template, rows);
                            if (!dataUrls.length) { showNotification('Tidak ada halaman yang dihasilkan', 'warning'); return; }
                            const jpegPages = await Promise.all(dataUrls.map(_toJpegData));
                            const pdfBytes = _buildImagePdf(jpegPages);
                            const blob = new Blob([pdfBytes], { type: 'application/pdf' });
                            const url = URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = `katalog-android-a4-${new Date().toISOString().slice(0, 10)}.pdf`;
                            a.click();
                            URL.revokeObjectURL(url);
                            showNotification(`PDF A4 ${jpegPages.length} halaman berhasil didownload`);
                            return;
                        }
                        const cfg = Number(template.layout_config?.layout_version || 0) >= 2
                            ? { ...defaultCatalogLayoutConfig(), ...template.layout_config }
                            : defaultCatalogLayoutConfig();
                        const canvasHeight = Number(template.canvas_height || (template.format === 'feed' ? 1350 : 1920));
                        const y = Number(cfg.y || 255);
                        const rowHeight = Number(cfg.rowHeight || 28);
                        const headerHeight = Number(cfg.headerHeight || 34);
                        const isCardCatalog = catalogOutputMode.value === 'katalog';
                        const cardConfig = { ...defaultCatalogCardConfig(), ...(cfg.card_config || catalogCardCfg.value) };
                        const cardHeight = Number(cardConfig.imageHeight || 100) + 118;
                        const cardGap = 18;
                        const autoMaxItems = isCardCatalog
                            ? Math.max(1, Math.floor((canvasHeight - y - 90 + cardGap) / (cardHeight + cardGap)) * Number(cfg.cardColumns || catalogColumnsPerRow.value || 3))
                            : Math.max(1, Math.floor((canvasHeight - y - headerHeight - 90) / rowHeight));
                        const maxItems = Math.max(1, Math.min(Number(cfg.maxItems || autoMaxItems), autoMaxItems));

                        // Group by brand/sheet, sort by count DESC
                        const rawGroups = catalogSelectedSheet.value === 'all'
                            ? rows.reduce((acc, row) => {
                                const key = row.source_sheet || 'SPECIAL PRICE';
                                (acc[key] = acc[key] || []).push(row);
                                return acc;
                            }, {})
                            : { [catalogSelectedSheet.value]: rows };
                        const sortedGroups = Object.entries(rawGroups).sort((a, b) => b[1].length - a[1].length);

                        // Generate canvas pages
                        const dataUrls = [];
                        for (const [title, groupRows] of sortedGroups) {
                            if (isCardCatalog) {
                                const cardGroups = catalogAndroidCardGroups(groupRows);
                                for (let i = 0; i < cardGroups.length; i += maxItems) {
                                    const chunk = cardGroups.slice(i, i + maxItems);
                                    dataUrls.push(await drawPricelistCardPage(template, chunk, catalogTitleForOutput(template, title, 'katalog', catalogPriceKeyForSheet(title)), catalogPriceKeyForSheet(title)));
                                }
                            } else {
                                for (let i = 0; i < groupRows.length; i += maxItems) {
                                    const chunk = groupRows.slice(i, i + maxItems);
                                    dataUrls.push(await drawCatalogPage(template, chunk, catalogTitleForOutput(template, title, 'list', catalogPriceKeyForSheet(title)), catalogPriceKeyForSheet(title)));
                                }
                            }
                        }
                        if (!dataUrls.length) { showNotification('Tidak ada halaman yang dihasilkan', 'warning'); return; }

                        // Convert to JPEG + build PDF binary (no external library)
                        const jpegPages = await Promise.all(dataUrls.map(_toJpegData));
                        const pdfBytes = _buildImagePdf(jpegPages);

                        const blob = new Blob([pdfBytes], { type: 'application/pdf' });
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `katalog-android-${new Date().toISOString().slice(0, 10)}.pdf`;
                        a.click();
                        URL.revokeObjectURL(url);
                        showNotification(`PDF ${jpegPages.length} halaman berhasil didownload`);
                    } catch (error) {
                        notifyError('', error, 'Gagal export PDF katalog.');
                    } finally {
                        catalogGenerating.value = false;
                    }
                };
@endverbatim
