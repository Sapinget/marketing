@verbatim
                const pricelistProducts = ref([]);
                const pricelistProductsLoaded = ref(false);
                const pricelistSyncing = ref(false);
                const pricelistSearch = ref('');
                const pricelistSheetFilter = ref('all');
                const pricelistView = ref(localStorage.getItem('ppp_pricelist_view') || 'card');
                const pricelistPage = ref(1);
                const pricelistPageSize = 20;
                const catalogTemplates = ref([]);
                const catalogTemplatesLoaded = ref(false);
                const defaultCatalogLayoutConfig = () => ({
                    x: 64,
                    y: 255,
                    width: 952,
                    headerHeight: 34,
                    rowHeight: 28,
                    maxItems: 0,
                    borderRadius: 10,
                    headerFontSize: 13,
                    bodyFontSize: 13,
                    priceFontSize: 13,
                    titleFontSize: 34,
                    titleGap: 42,
                    titleX: 540,
                    titleY: 213,
                    titleColor: '#ffffff',
                    headerColor: '#3f3f3f',
                    headerTextColor: '#ffffff',
                    rowOddColor: 'rgba(255,255,255,0.55)',
                    rowEvenColor: 'rgba(255,255,255,0.08)',
                    textColor: '#3f3f3f',
                    normalPriceColor: '#3f3f3f',
                    specialPriceColor: '#3f3f3f',
                    layout_version: 2,
                });
                const catalogTemplateForm = ref({
                    ID: '',
                    name: '',
                    format: 'story',
                    layout_config: defaultCatalogLayoutConfig(),
                    is_active: true,
                });
                const catalogTemplateModalOpen = ref(false);
                const catalogTemplateModalType = ref('create');
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
                const defaultCatalogCardConfig = () => ({
                    imageHeight: 100,
                    modelFontSize: 13,
                    priceFontSize: 13,
                    srpFontSize: 11,
                    rowGap: 4,
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
                                layout_config: template.layout_config?.layout_version === 2
                                    ? { ...defaultCatalogLayoutConfig(), ...template.layout_config }
                                    : defaultCatalogLayoutConfig(),
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
                    fetch(`/api/android-images/${encodeURIComponent(key)}`)
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
                const androidProductImage = (row) => {
                    const brand = String(row.source_sheet || row.brand || '').toUpperCase();
                    const map = androidImageMap.value[brand] || {};
                    if (!Object.keys(map).length) return null;

                    const productKey = androidModelKey(catalogProductType(row));
                    const fullKey = androidModelKey(row.nama_produk);
                    const normalizedProduct = androidNormalizeModelForImage(catalogProductType(row), brand);
                    const normalizedFull = androidNormalizeModelForImage(row.nama_produk, brand);

                    // tier 1: exact key match
                    const exact = map[normalizedProduct] || map[normalizedFull] || map[productKey] || map[fullKey];
                    if (exact?.length) return exact[0];

                    // tier 2: image folder key starts with product name (handles color/variant suffix)
                    const candidates = [normalizedProduct, normalizedFull, productKey, fullKey].filter(Boolean);
                    const mapKeys = Object.keys(map);
                    for (const candidate of candidates) {
                        const match = mapKeys.find((key) => key === candidate || key.startsWith(candidate + ' '));
                        if (match) return map[match][0];
                    }

                    // tier 3: product name starts with image folder key (handles image key being shorter/base model)
                    for (const candidate of candidates) {
                        const match = mapKeys
                            .filter((key) => candidate.startsWith(key + ' ') || candidate === key)
                            .sort((a, b) => b.length - a.length)[0];
                        if (match) return map[match][0];
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
                const catalogRowsForGeneration = computed(() => {
                    const start = Number(catalogUrutStart.value || 0);
                    const end = Number(catalogUrutEnd.value || 0);
                    return catalogDedupeRows(pricelistProducts.value
                        .filter((row) => row.is_active !== false)
                        .filter((row) => catalogSelectedSheet.value === 'all' || row.source_sheet === catalogSelectedSheet.value)
                        .filter((row) => !start || Number(row.urut || 0) >= start)
                        .filter((row) => !end || Number(row.urut || 0) <= end)
                        .sort((a, b) => String(a.source_sheet).localeCompare(String(b.source_sheet)) || Number(a.urut || 0) - Number(b.urut || 0)));
                });
                const catalogPreviewRows = computed(() => {
                    const fallbackRows = [
                        { source_sheet: 'SAMPLE', nama_produk: 'IPHONE 15 PRO', ram: '8GB', storage: '256GB', harga_nasional: 18999000, special_price: 17999000, harga_jual: 17999000 },
                        { source_sheet: 'SAMPLE', nama_produk: 'SAMSUNG S24 ULTRA', ram: '12GB', storage: '512GB', harga_nasional: 21999000, special_price: 20499000, harga_jual: 20499000 },
                        { source_sheet: 'SAMPLE', nama_produk: 'OPPO RENO 12', ram: '12GB', storage: '256GB', harga_nasional: 6999000, special_price: 6499000, harga_jual: 6499000 },
                        { source_sheet: 'SAMPLE', nama_produk: 'VIVO V30', ram: '12GB', storage: '256GB', harga_nasional: 5999000, special_price: 5599000, harga_jual: 5599000 },
                        { source_sheet: 'SAMPLE', nama_produk: 'XIAOMI 14T', ram: '12GB', storage: '512GB', harga_nasional: 7999000, special_price: 7499000, harga_jual: 7499000 },
                    ];
                    return (catalogRowsForGeneration.value.length ? catalogRowsForGeneration.value : fallbackRows).slice(0, 5);
                });

                const catalogPriceKeyForSheet = (sheet = null) => {
                    if (catalogSelectedSheet.value === 'all' && sheet && catalogBrandPriceKeys.value[sheet]) {
                        return catalogBrandPriceKeys.value[sheet];
                    }
                    return catalogPriceKey.value;
                };

                const openCatalogTemplateModal = (mode = 'create', template = null) => {
                    catalogTemplateModalType.value = mode;
                    catalogTemplateForm.value = template ? {
                        ...JSON.parse(JSON.stringify(template)),
                        layout_config: template.layout_config?.layout_version === 2
                            ? { ...defaultCatalogLayoutConfig(), ...template.layout_config }
                            : defaultCatalogLayoutConfig(),
                    } : {
                        ID: '',
                        name: '',
                        format: 'story',
                        layout_config: defaultCatalogLayoutConfig(),
                        is_active: true,
                    };
                    catalogTemplateModalOpen.value = true;
                };

                const saveCatalogTemplate = () => {
                    ensureRunApi()
                        .withSuccessHandler((response) => {
                            const stored = response?.data || response;
                            showNotification('Template katalog tersimpan');
                            catalogTemplateModalOpen.value = false;
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

                const catalogLayoutDragStart = (event, template, dragType = 'table') => {
                    const canvasWidth = Number(template?.canvas_width || (template?.format === 'feed' ? 1080 : 1080));
                    const canvasHeight = Number(template?.canvas_height || (template?.format === 'feed' ? 1350 : 1920));
                    const rect = event.currentTarget.closest('.catalog-layout-drag')?.getBoundingClientRect() || event.currentTarget.getBoundingClientRect();
                    const cfg = template?.layout_config || defaultCatalogLayoutConfig();
                    const width = Number(cfg.width || canvasWidth - 128);

                    catalogLayoutDrag.value = {
                        type: dragType,
                        startX: event.clientX,
                        startY: event.clientY,
                        originX: dragType === 'title' ? Number(cfg.titleX || canvasWidth / 2) : Number(cfg.x || 64),
                        originY: dragType === 'title' ? Number(cfg.titleY || Number(cfg.y || 255) - Number(cfg.titleGap || 42)) : Number(cfg.y || 255),
                        canvasWidth,
                        canvasHeight,
                        tableWidth: width,
                        rectWidth: rect.width,
                        rectHeight: rect.height,
                    };
                    event.preventDefault();
                    event.stopPropagation?.();
                };

                const catalogLayoutDragMove = (event) => {
                    const drag = catalogLayoutDrag.value;
                    if (!drag) return;

                    const scaleX = drag.canvasWidth / drag.rectWidth;
                    const scaleY = drag.canvasHeight / drag.rectHeight;
                    const deltaX = (event.clientX - drag.startX) * scaleX;
                    const deltaY = (event.clientY - drag.startY) * scaleY;

                    const x = Math.round(drag.originX + deltaX);
                    const y = Math.max(0, Math.round(drag.originY + deltaY));

                    if (drag.type === 'title') {
                        const centerX = drag.canvasWidth / 2;
                        catalogTemplateForm.value.layout_config.titleX = Math.abs(x - centerX) <= 12 ? centerX : Math.max(0, x);
                        catalogTemplateForm.value.layout_config.titleY = y;
                        return;
                    }

                    const centerX = (drag.canvasWidth - drag.tableWidth) / 2;
                    catalogTemplateForm.value.layout_config.x = Math.abs(x - centerX) <= 12 ? centerX : Math.max(0, x);
                    catalogTemplateForm.value.layout_config.y = y;
                };

                const catalogLayoutDragEnd = () => {
                    catalogLayoutDrag.value = null;
                };

                const catalogTitlePreviewStyle = (template) => {
                    const cfg = template?.layout_config || defaultCatalogLayoutConfig();
                    const canvasWidth = Number(template?.canvas_width || (template?.format === 'feed' ? 1080 : 1080));
                    const canvasHeight = Number(template?.canvas_height || (template?.format === 'feed' ? 1350 : 1920));
                    const titleX = Number(cfg.titleX || canvasWidth / 2);
                    const titleY = Number(cfg.titleY || Number(cfg.y || 255) - Number(cfg.titleGap || 42));
                    const fontSize = Number(cfg.titleFontSize || 34);

                    return {
                        left: `${(titleX / canvasWidth) * 100}%`,
                        top: `${(titleY / canvasHeight) * 100}%`,
                        transform: 'translate(-50%, -50%)',
                        fontSize: `${Math.max(9, (fontSize / canvasHeight) * 640)}px`,
                        lineHeight: '1',
                        color: cfg.titleColor || '#ffffff',
                    };
                };

                const catalogColorFields = [
                    { key: 'headerColor', label: 'Warna header (latar)' },
                    { key: 'headerTextColor', label: 'Warna teks header' },
                    { key: 'textColor', label: 'Warna teks produk' },
                    { key: 'normalPriceColor', label: 'Warna normal price' },
                    { key: 'specialPriceColor', label: 'Warna special price' },
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

                const catalogPreviewMeasureCtx = document.createElement('canvas').getContext('2d');

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
                    const cfg = cfgOverride || catalogA4Cfg(template, rows.length) || (template.layout_config?.layout_version === 2 ? { ...defaultCatalogLayoutConfig(), ...template.layout_config } : defaultCatalogLayoutConfig());
                    const footerSafeArea = 90;
                    const paintTable = () => {
                        const x = Number(cfg.x || 64);
                        const y = Number(cfg.y || 255);
                        const width = Number(cfg.width || (canvas.width - 128));
                        const headerHeight = Number(cfg.headerHeight || 34);
                        const rowHeight = Number(cfg.rowHeight || 28);
                        const geometry = catalogColumnGeometry(x, width, priceKey);
                        const titleText = String(title || '').trim().toUpperCase();

                        if (titleText) {
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            ctx.font = `800 ${Number(cfg.titleFontSize || 34)}px Inter, Arial, sans-serif`;
                            ctx.fillStyle = cfg.titleColor || '#ffffff';
                            const titleX = Number(cfg.titleX || canvas.width / 2);
                            const titleY = Number(cfg.titleY || y - Number(cfg.titleGap || 42));
                            ctx.fillText(titleText, titleX, titleY, width);
                        }

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
                    const perVariant = rowH + srpFs + 2 + rGap;
                    return imgH + 8 + (mFs + 1) * 2 + 3 + lFs + 5 + variantCount * perVariant;
                };

                const drawPricelistCardPage = (template, groups, title = '', priceKey = 'special_price') => new Promise(async (resolve, reject) => {
                    const imageEls = await Promise.all(groups.map((g) => loadCatalogImage(androidProductImage(g.variants[0]))));
                    const canvas = document.createElement('canvas');
                    const size = catalogCanvasSize(template);
                    canvas.width = size.width;
                    canvas.height = size.height;
                    const ctx = canvas.getContext('2d');
                    const cfg = template.layout_config?.layout_version === 2 ? { ...defaultCatalogLayoutConfig(), ...template.layout_config } : defaultCatalogLayoutConfig();
                    const cardCfg = { ...defaultCatalogCardConfig(), ...catalogCardCfg.value };
                    const paintCards = () => {
                        const x = Number(cfg.x || 64);
                        const y = Number(cfg.y || 255);
                        const width = Number(cfg.width || (canvas.width - 128));
                        const gap = 18;
                        const cols = Number(catalogColumnsPerRow.value || 3);
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
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            ctx.font = `800 ${Number(cfg.titleFontSize || 34)}px Inter, Arial, sans-serif`;
                            ctx.fillStyle = cfg.titleColor || '#ffffff';
                            ctx.fillText(titleText, Number(cfg.titleX || canvas.width / 2), Number(cfg.titleY || y - Number(cfg.titleGap || 42)), width);
                        }

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

                                ctx.font = `700 ${sFs}px Inter, Arial, sans-serif`;
                                const badgeTextW = storage ? ctx.measureText(storage).width : 0;
                                const badgeW = storage ? badgeTextW + badgePadX * 2 : 0;
                                ctx.font = `700 ${Number(cardCfg.priceFontSize || 13)}px Inter, Arial, sans-serif`;
                                const priceW = ctx.measureText(priceText).width;
                                ctx.font = `600 ${srpFs}px Inter, Arial, sans-serif`;
                                const srpW = srpText ? ctx.measureText(srpText).width : 0;
                                const rightW = Math.max(priceW, srpW);
                                const gapW = 6;
                                const combined = badgeW + (badgeW ? gapW : 0) + rightW;
                                const rowInset = Math.max(8, (cardW - combined) / 2);
                                const blockStartX = curX + rowInset;
                                const priceRightX = curX + cardW - rowInset;
                                const rowMidY = innerY + currentRowH / 2;

                                if (storage) {
                                    ctx.font = `700 ${sFs}px Inter, Arial, sans-serif`;
                                    ctx.strokeStyle = 'rgba(255,255,255,0.55)';
                                    ctx.lineWidth = 1;
                                    const br = rowH / 2;
                                    const by = rowMidY - rowH / 2;
                                    ctx.beginPath();
                                    ctx.moveTo(blockStartX + br, by);
                                    ctx.lineTo(blockStartX + badgeW - br, by);
                                    ctx.quadraticCurveTo(blockStartX + badgeW, by, blockStartX + badgeW, by + br);
                                    ctx.lineTo(blockStartX + badgeW, by + rowH - br);
                                    ctx.quadraticCurveTo(blockStartX + badgeW, by + rowH, blockStartX + badgeW - br, by + rowH);
                                    ctx.lineTo(blockStartX + br, by + rowH);
                                    ctx.quadraticCurveTo(blockStartX, by + rowH, blockStartX, by + rowH - br);
                                    ctx.lineTo(blockStartX, by + br);
                                    ctx.quadraticCurveTo(blockStartX, by, blockStartX + br, by);
                                    ctx.closePath();
                                    ctx.stroke();
                                    ctx.fillStyle = cardCfg.badgeTextColor || '#ffffff';
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle';
                                    ctx.fillText(storage, blockStartX + badgeW / 2, rowMidY, badgeW - 4);
                                }

                                if (srpText) {
                                    ctx.font = `600 ${srpFs}px Inter, Arial, sans-serif`;
                                    ctx.fillStyle = cardCfg.srpColor || 'rgba(255,255,255,0.5)';
                                    ctx.textAlign = 'right';
                                    ctx.textBaseline = 'top';
                                    ctx.fillText(srpText, priceRightX, innerY);
                                    ctx.strokeStyle = '#dc2626';
                                    ctx.lineWidth = 1.5;
                                    ctx.beginPath();
                                    ctx.moveTo(priceRightX - srpW, innerY + srpFs * 0.82);
                                    ctx.lineTo(priceRightX, innerY + srpFs * 0.18);
                                    ctx.stroke();
                                }
                                ctx.font = `700 ${Number(cardCfg.priceFontSize || 13)}px Inter, Arial, sans-serif`;
                                ctx.fillStyle = cardCfg.priceColor || '#ffffff';
                                ctx.textAlign = 'right';
                                ctx.textBaseline = srpText ? 'top' : 'middle';
                                ctx.fillText(priceText, priceRightX, srpText ? innerY + srpFs + 2 : rowMidY);

                                innerY += currentRowH;
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

                const catalogA4Pages = async (template, rows) => {
                    const sortedRows = [...rows].sort((a, b) => String(a.source_sheet).localeCompare(String(b.source_sheet)) || Number(a.urut || 0) - Number(b.urut || 0));
                    const cfg = catalogA4Cfg(template, sortedRows.length);
                    const maxItems = Math.max(1, Number(cfg.maxItems || Math.ceil(sortedRows.length / 2)));
                    const pages = [];
                    for (let index = 0; index < sortedRows.length; index += maxItems) {
                        pages.push(await drawCatalogPage(template, sortedRows.slice(index, index + maxItems), index === 0 ? 'KATALOG PRICELIST' : 'KATALOG PRICELIST', catalogPriceKey.value, cfg));
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
                        const cfg = template.layout_config?.layout_version === 2 ? { ...defaultCatalogLayoutConfig(), ...template.layout_config } : defaultCatalogLayoutConfig();
                        const height = Number(template.canvas_height || (template.format === 'feed' ? 1350 : 1920));
                        const y = Number(cfg.y || 255);
                        const rowHeight = Number(cfg.rowHeight || 28);
                        const headerHeight = Number(cfg.headerHeight || 34);
                        const footerSafeArea = 90;
                        const isCardCatalog = catalogOutputMode.value === 'katalog';
                        const cardHeight = Number(catalogCardCfg.value.imageHeight || 100) + 118;
                        const cardGap = 18;
                        const autoMaxItems = isCardCatalog
                            ? Math.max(1, Math.floor((height - y - footerSafeArea + cardGap) / (cardHeight + cardGap)) * Number(catalogColumnsPerRow.value || 3))
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
                                    pages.push(await drawPricelistCardPage(template, chunk, title, catalogPriceKeyForSheet(title)));
                                }
                            } else {
                                for (let index = 0; index < groupRows.length; index += maxItems) {
                                    const chunk = groupRows.slice(index, index + maxItems);
                                    pages.push(await drawCatalogPage(template, chunk, title, catalogPriceKeyForSheet(title)));
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
                        const cfg = template.layout_config?.layout_version === 2
                            ? { ...defaultCatalogLayoutConfig(), ...template.layout_config }
                            : defaultCatalogLayoutConfig();
                        const canvasHeight = Number(template.canvas_height || (template.format === 'feed' ? 1350 : 1920));
                        const y = Number(cfg.y || 255);
                        const rowHeight = Number(cfg.rowHeight || 28);
                        const headerHeight = Number(cfg.headerHeight || 34);
                        const isCardCatalog = catalogOutputMode.value === 'katalog';
                        const cardHeight = Number(catalogCardCfg.value.imageHeight || 100) + 118;
                        const cardGap = 18;
                        const autoMaxItems = isCardCatalog
                            ? Math.max(1, Math.floor((canvasHeight - y - 90 + cardGap) / (cardHeight + cardGap)) * Number(catalogColumnsPerRow.value || 3))
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
                                    dataUrls.push(await drawPricelistCardPage(template, chunk, title, catalogPriceKeyForSheet(title)));
                                }
                            } else {
                                for (let i = 0; i < groupRows.length; i += maxItems) {
                                    const chunk = groupRows.slice(i, i + maxItems);
                                    dataUrls.push(await drawCatalogPage(template, chunk, title, catalogPriceKeyForSheet(title)));
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
