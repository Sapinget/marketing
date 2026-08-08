@verbatim
                // Apple Catalog state
                const appleSheets = ['IPHONE', 'IPAD', 'MACBOOK', 'APPLE WATCH', 'AIRPODS', 'APPLE PENCIL'];
                const appleProducts = ref([]);
                const appleProductsLoaded = ref(false);
                const appleSyncing = ref(false);
                const appleSearch = ref('');
                const appleCategory = ref(localStorage.getItem('ppp_apple_category') || 'IPHONE');
                const _restoreAppleSelection = () => {
                    try {
                        const raw = localStorage.getItem('ppp_apple_selection');
                        if (!raw) return { models: [], variants: {} };
                        const parsed = JSON.parse(raw);
                        return {
                            models: Array.isArray(parsed.models) ? parsed.models : [],
                            variants: parsed.variants && typeof parsed.variants === 'object' ? parsed.variants : {},
                        };
                    } catch (e) {
                        return { models: [], variants: {} };
                    }
                };
                const _appleSavedSelection = _restoreAppleSelection();
                const appleSelectedModels = ref(_appleSavedSelection.models);
                const appleSelectedVariants = ref(_appleSavedSelection.variants); // { modelName: [storageKey, ...] }
                const appleSelectedTemplateId = ref('');
                const appleGenerating = ref(false);
                const applePreviewImages = ref([]);
                const applePreviewModalOpen = ref(false);
                const applePreviewModalIndex = ref(0);
                const applePreviewZoom = ref(1);
                const appleColumnsPerRow = ref(4);
                const appleImageMap = ref({});
                const appleColorSlots = ref({}); // category → { modelKey → slotIdx }
                const appleView = ref('card'); // 'card' | 'table'
                const applePriceKey = ref(localStorage.getItem('ppp_apple_price_key') || 'special_price'); // which price to show in catalog

                // Price option configs per sheet — determines what "harga utama" shown in catalog
                const applePriceOptionMap = {
                    'IPHONE':       [
                        { key: 'special_price',   label: 'Brand New Garansi Resmi Indonesia' },
                        { key: 'GARANSI ON',      label: 'Second iBox Garansi On' },
                        { key: 'BEACUKAI',        label: 'Second Terdaftar Beacukai' },
                        { key: 'EXIBOX',          label: 'Second iBox' },
                        { key: 'second_table',    label: 'Second Beacukai + Second iBox' },
                        { key: 'DUAL SIM',        label: 'Second Terdaftar Dual Sim' },
                        { key: 'TIDAK TERDAFTAR', label: 'Second Tidak Terdaftar' },
                    ],
                    'IPAD':         [
                        { key: 'special_price',        label: 'Brand New Garansi Resmi Indonesia WiFi' },
                        { key: 'HARGA TOKO WIFI+CELL', label: 'Brand New Garansi Resmi Indonesia WiFi+Cell' },
                        { key: 'SECOND WIFI',          label: 'Second WiFi' },
                        { key: 'SECOND WIFI+CELL',     label: 'Second WiFi+Cell' },
                        { key: 'SRP WIFI',             label: 'SRP WiFi' },
                    ],
                    'MACBOOK':      [
                        { key: 'special_price', label: 'Brand New Garansi Resmi Indonesia' },
                        { key: 'SECOND',        label: 'Second' },
                        { key: 'SRP NEW',       label: 'SRP New' },
                    ],
                    'APPLE WATCH':  [
                        { key: 'special_price', label: 'Brand New Garansi Resmi Indonesia' },
                        { key: 'SECOND',        label: 'Second' },
                        { key: 'SRP NEW',       label: 'SRP New' },
                    ],
                    'AIRPODS':      [
                        { key: 'special_price', label: 'Brand New Garansi Resmi Indonesia' },
                        { key: 'SECOND',        label: 'Second' },
                        { key: 'SRP NEW',       label: 'SRP New' },
                    ],
                    'APPLE PENCIL': [
                        { key: 'special_price', label: 'Brand New Garansi Resmi Indonesia' },
                        { key: 'SECOND',        label: 'Second' },
                        { key: 'SRP NEW',       label: 'SRP New' },
                    ],
                };

                const applePriceOptions = computed(() => applePriceOptionMap[appleCategory.value] || [{ key: 'special_price', label: 'Harga Toko' }]);

                // Labels of kondisi columns for the active category (used in table header)
                const appleKondisiLabelMap = {
                    'IPHONE':       ['TIDAK TERDAFTAR', 'BEACUKAI', 'EXIBOX', 'DUAL SIM', 'GARANSI ON', 'KREDIT NEW', 'KREDIT GARANSI', 'SRP NEW', 'HARGA TOKO NEW'],
                    'IPAD':         ['SRP WIFI', 'SRP WIFI+CELL', 'HARGA TOKO WIFI', 'HARGA TOKO WIFI+CELL', 'SECOND WIFI', 'SECOND WIFI+CELL', 'KREDIT NEW WIFI', 'KREDIT NEW WIFI+CELL', 'KREDIT SECOND WIFI', 'KREDIT SECOND WIFI+CELL', 'HARGA ONLINE WIFI'],
                    'MACBOOK':      ['SRP NEW', 'CASH TOKO', 'SECOND', 'KREDIT NEW', 'KREDIT SECOND', 'HARGA ONLINE'],
                    'APPLE WATCH':  ['SRP NEW', 'CASH NEW', 'SECOND', 'KREDIT NEW', 'KREDIT SECOND', 'HARGA ONLINE'],
                    'AIRPODS':      ['SRP NEW', 'CASH TOKO', 'SECOND', 'KREDIT NEW', 'KREDIT SECOND', 'HARGA ONLINE'],
                    'APPLE PENCIL': ['SRP NEW', 'CASH TOKO', 'SECOND', 'KREDIT NEW', 'KREDIT SECOND', 'HARGA ONLINE'],
                };
                const appleKondisiLabels = computed(() => appleKondisiLabelMap[appleCategory.value] || []);

                // Compact kondisi to show on card (3 most relevant keys for the sheet)
                const appleKondisiCardKeys = {
                    'IPHONE':       ['GARANSI ON', 'BEACUKAI', 'TIDAK TERDAFTAR'],
                    'IPAD':         ['HARGA TOKO WIFI', 'HARGA TOKO WIFI+CELL', 'SECOND WIFI', 'SECOND WIFI+CELL'],
                    'MACBOOK':      ['SECOND', 'SRP NEW'],
                    'APPLE WATCH':  ['SECOND', 'SRP NEW'],
                    'AIRPODS':      ['SECOND', 'SRP NEW'],
                    'APPLE PENCIL': ['SECOND', 'SRP NEW'],
                };

                const defaultAppleCanvasConfig = () => ({
                    padding: 92,
                    gap: 42,
                    imageHeight: 118,
                    modelFontSize: 14,
                    priceFontSize: 13,
                    topOffset: 150,
                    bottomSafe: 130,
                    titleFontSize: 32,
                    titleY: null,
                    headerHeight: 40,
                    rowHeight: 34,
                    tableHeaderFontSize: 14,
                    tableBodyFontSize: 14,
                    tableSeriWidth: 34,
                    tableStorageWidth: 18,
                    tableBeacukaiWidth: 24,
                    tableHeaderRadius: 12,
                    tableHeaderBg: 'rgba(255,255,255,0.16)',
                    tableRowOddBg: 'rgba(255,255,255,0.08)',
                    tableRowEvenBg: 'rgba(255,255,255,0.16)',
                    tableBorderColor: 'rgba(255,255,255,0.35)',
                    tableGroupLineColor: 'rgba(255,255,255,0.5)',
                    modelColor: '#ffffff',
                    labelColor: 'rgba(255,255,255,0.6)',
                    badgeTextColor: '#ffffff',
                    priceColor: '#ffffff',
                    srpColor: 'rgba(255,255,255,0.5)',
                    srpFontSize: 11,
                    rowGap: 5,
                    rowSep: true,
                    rowsPerPage: 3,
                });
                const appleCfg = ref(defaultAppleCanvasConfig());
                try {
                    const savedAppleCfg = JSON.parse(localStorage.getItem('ppp_apple_canvas_cfg') || '{}');
                    appleCfg.value = { ...defaultAppleCanvasConfig(), ...savedAppleCfg };
                } catch (e) {}

                // helpers

                const formatApplePrice = (val) => {
                    if (!val) return '—';
                    return Number(val).toLocaleString('id-ID');
                };

                const appleModelKey = (modelName) => String(modelName || '').toUpperCase().replace(/[()]/g, '').replace(/\s+/g, ' ').trim();

                // stable card color index: uses folder-insertion slot from image load, fallback to position in key list
                const appleModelColorIdx = (category, modelKey) => {
                    const slots = appleColorSlots.value[category] || {};
                    if (modelKey in slots) return slots[modelKey];
                    // resolve alias/fallback for slot lookup
                    const al = appleModelAliases[modelKey];
                    if (al && al in slots) return slots[al];
                    const fb1 = appleImageFallback(modelKey);
                    if (fb1 && fb1 in slots) return slots[fb1] + 1;
                    return 0;
                };

                // Table view: flat list of all rows for current category + search
                const appleTableRows = computed(() => {
                    const query = appleSearch.value.trim().toLowerCase();
                    return appleProducts.value
                        .filter((r) => r.source_sheet === appleCategory.value)
                        .filter((r) => !query || String(r.model || '').toLowerCase().includes(query) || String(r.storage || '').toLowerCase().includes(query));
                });

                // For card view: show key kondisi prices below each variant
                const appleHasKondisiGaransi = (variant) => {
                    const keys = appleKondisiCardKeys[appleCategory.value] || [];
                    return keys.length > 0 && variant.harga_kondisi && keys.some((k) => variant.harga_kondisi[k] !== undefined);
                };

                const appleKondisiDisplay = (variant) => {
                    const keys = appleKondisiCardKeys[appleCategory.value] || [];
                    const result = {};
                    keys.forEach((k) => {
                        if (variant.harga_kondisi && k in variant.harga_kondisi) {
                            result[k] = variant.harga_kondisi[k];
                        }
                    });
                    return result;
                };

                // Resolve the catalog display price for a variant based on applePriceKey
                const appleResolvePrice = (variant) => {
                    const key = applePriceKey.value;
                    if (key === 'special_price') return variant.special_price;
                    if (key === 'harga_nasional') return variant.harga_nasional;
                    if (key === 'second_table') return variant.harga_kondisi ? (variant.harga_kondisi.BEACUKAI ?? variant.harga_kondisi.EXIBOX ?? null) : null;
                    return variant.harga_kondisi ? (variant.harga_kondisi[key] ?? null) : null;
                };

                // Resolve SRP (strikethrough) price — always harga_nasional (SRP NEW)
                const appleResolveSrp = (variant) => variant.harga_nasional;

                const appleModelGroupName = (category, model) => {
                    const name = String(model || '').trim();
                    const key = appleModelKey(name);
                    if (category === 'IPAD') {
                        if (key.startsWith('IPAD AIR')) return 'IPAD AIR';
                        if (key.startsWith('IPAD PRO')) return 'IPAD PRO';
                        if (key.startsWith('IPAD MINI')) return 'IPAD MINI';
                        return 'IPAD GEN';
                    }
                    if (category === 'MACBOOK') {
                        if (key.startsWith('MACBOOK AIR')) return 'MACBOOK AIR';
                        if (key.startsWith('MACBOOK PRO')) return 'MACBOOK PRO';
                        return 'MACBOOK';
                    }
                    if (category === 'AIRPODS') {
                        if (key.startsWith('AIRPODS PRO')) return 'AIRPODS PRO';
                        if (key.startsWith('AIRPODS MAX')) return 'AIRPODS MAX';
                        return 'AIRPODS';
                    }
                    if (category === 'APPLE WATCH') {
                        if (key.startsWith('APPLE WATCH SE ') || key === 'APPLE WATCH SE') return 'APPLE WATCH SE';
                        if (key.startsWith('APPLE WATCH ULTRA')) return 'APPLE WATCH ULTRA';
                        if (key.startsWith('APPLE WATCH SERIES')) return 'APPLE WATCH SERIES';
                        return 'APPLE WATCH';
                    }
                    if (category === 'APPLE PENCIL') return 'APPLE PENCIL';
                    return name;
                };

                const appleGroupedModels = computed(() => {
                    const query = appleSearch.value.trim().toLowerCase();
                    const rows = appleProducts.value.filter((r) => r.source_sheet === appleCategory.value);
                    const map = {};
                    rows.forEach((row) => {
                        const key = String(row.model || '').trim();
                        if (!key) return;
                        if (!map[key]) map[key] = { model: key, modelKey: appleModelKey(key), variants: [] };
                        map[key].variants.push(row);
                    });
                    let groups = Object.values(map).sort((a, b) => {
                        const aUrut = Math.min(...a.variants.map((v) => v.urut || 9999));
                        const bUrut = Math.min(...b.variants.map((v) => v.urut || 9999));
                        return aUrut - bUrut;
                    });
                    if (query) {
                        groups = groups.filter((g) => g.model.toLowerCase().includes(query));
                    }
                    return groups;
                });

                // ── data loading ─────────────────────────────────────────────────────────────

                const loadAppleProducts = () => {
                    fetch('/api/apple-products')
                        .then((r) => r.json())
                        .then((json) => {
                            appleProducts.value = Array.isArray(json.data) ? json.data : [];
                            appleProductsLoaded.value = true;
                        })
                        .catch((err) => notifyError('', err, 'Gagal memuat produk Apple.'));
                };

                const syncAppleProducts = () => {
                    appleSyncing.value = true;
                    fetch('/api/apple-products/sync', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Content-Type': 'application/json' },
                    })
                        .then((r) => r.json())
                        .then((json) => {
                            const s = json.data || {};
                            showNotification(`Sync selesai: ${s.imported || 0} item masuk`);
                            loadAppleProducts();
                        })
                        .catch((err) => notifyError('', err, 'Gagal sync produk Apple.'))
                        .finally(() => { appleSyncing.value = false; });
                };

                const appleLoadImages = (category) => {
                    if (appleImageMap.value[category]) return;
                    fetch(`/api/apple-images/${encodeURIComponent(category)}`)
                        .then((r) => r.json())
                        .then((json) => {
                            const map = {};
                            const slots = {};
                            let slot = 0;
                            (json.data || []).forEach((item) => {
                                const k = appleModelKey(item.model);
                                map[k] = item.images || [];
                                slots[k] = slot++;
                                // fix folder typos: "PHONE XS MAX" → "IPHONE XS MAX"
                                if (k.startsWith('PHONE ')) {
                                    const alias = 'IPHONE ' + k.slice(6);
                                    map[alias] = map[k];
                                    slots[alias] = slots[k];
                                }
                            });
                            appleImageMap.value   = { ...appleImageMap.value,   [category]: map };
                            appleColorSlots.value = { ...appleColorSlots.value, [category]: slots };
                        })
                        .catch(() => {});
                };

                // static alias: DB model key → image folder/file key
                const appleModelAliases = {
                    // IPAD
                    'IPAD AIR GEN 5':           'IPAD AIR 5',
                    'IPAD AIR M2 11-INCH':      'IPAD AIR 11_ M2',
                    'IPAD AIR M2 13-INCH':      'IPAD AIR 13_ M2',
                    'IPAD AIR M3 11-INCH':      'IPAD AIR 11_ M2',
                    'IPAD AIR M3 13-INCH':      'IPAD AIR 13_ M2',
                    'IPAD AIR M4 11-INCH':      'IPAD AIR 11_ M2',
                    'IPAD AIR M4 13-INCH':      'IPAD AIR 13_ M2',
                    'IPAD GEN 11 A16':          'IPAD GEN 11',
                    'IPAD MINI GEN 5':          'IPAD MINI 5',
                    'IPAD MINI GEN 6':          'IPAD MINI 6',
                    'IPAD MINI GEN 7':          'IPAD MINI 17 PRO',
                    'IPAD PRO GEN 3 11-INCH':   'IPAD PRO 11_ GEN 3',
                    'IPAD PRO GEN 3 12,9-INCH': 'IPAD PRO 12.9_ GEN 3',
                    'IPAD PRO GEN 4 11-INCH':   'IPAD PRO 11_ GEN 4',
                    'IPAD PRO GEN 4 12,9-INCH': 'IPAD PRO 12.9_ GEN 4',
                    'IPAD PRO GEN 5 12,9-INCH': 'IPAD PRO 12.9_ GEN 5',
                    'IPAD PRO GEN 6 12,9-INCH': 'IPAD PRO 12.9_ GEN 6',
                    'IPAD PRO M4 11-INCH':      'IPAD PRO 11_ M4',
                    'IPAD PRO M4 13-INCH':      'IPAD PRO 13_ M4',
                    'IPAD PRO M5 11-INCH':      'IPAD PRO 11_ M4',
                    'IPAD PRO M5 13-INCH':      'IPAD PRO 13_ M4',
                    // MACBOOK
                    'MACBOOK AIR M1':            'MACBOOK AIR 13_ M1',
                    'MACBOOK AIR M2':            'MACBOOK AIR 13_ M2',
                    'MACBOOK AIR M3':            'MACBOOK AIR 13_ M3',
                    'MACBOOK AIR M4 2025':       'MACBOOK AIR 13_ M4',
                    'MACBOOK AIR M5 2026':       'MACBOOK AIR 13_ M4',
                    'MACBOOK NEO':               'MACBOOK AIR 13_ M4',
                    'MACBOOK NEO WITH TOUCHID':  'MACBOOK AIR 13_ M4',
                    'MACBOOK PRO M1 MAX':        'MACBOOK PRO 14_ M1 MAX',
                    'MACBOOK PRO M1 PRO':        'MACBOOK PRO 14_ M1 PRO',
                    'MACBOOK PRO M2':            'MACBOOK PRO 13_ M2',
                    'MACBOOK PRO M2 MAX':        'MACBOOK PRO 14_ M2 MAX',
                    'MACBOOK PRO M2 PRO':        'MACBOOK PRO 14_ M2 PRO',
                    'MACBOOK PRO M3':            'MACBOOK PRO 14_ M3',
                    'MACBOOK PRO M3 MAX':        'MACBOOK PRO 14_ M3 MAX',
                    'MACBOOK PRO M3 PRO':        'MACBOOK PRO 14_ M3 PRO',
                    'MACBOOK PRO M4':            'MACBOOK PRO 14_ M4',
                    'MACBOOK PRO M4 MAX':        'MACBOOK PRO 14_ M4 MAX',
                    'MACBOOK PRO M4 PRO':        'MACBOOK PRO 14_ M4 PRO',
                    'MACBOOK PRO M5':            'MACBOOK PRO 14_ M4',
                    'MACBOOK PRO M5 MAX':        'MACBOOK PRO 14_ M4 MAX',
                    'MACBOOK PRO M5 PRO':        'MACBOOK PRO 14_ M4 PRO',
                    'MACBOOK PRO M5 PRO 15CORE': 'MACBOOK PRO 14_ M4 PRO',
                    // AIRPODS (file-level images inside AIRPODS subfolder)
                    'AIRPODS GEN 2 - ORIGINAL':     'AIRPODS GEN 2',
                    'AIRPODS GEN 4 - ORIGINAL':     'AIRPODS GEN 4',
                    'AIRPODS GEN 4 ANC - ORIGINAL': 'AIRPODS GEN 4 ACTIVE NOISE CANCELLATION',
                    'AIRPODS PRO GEN 3':            'AIRPODS PRO GEN 2',
                    // APPLE PENCIL (parens stripped by appleModelKey → generational name)
                    'APPLE PENCIL GEN 1': 'APPLE PENCIL 1ST GENERATION',
                    'APPLE PENCIL GEN 2': 'APPLE PENCIL 2ND GENERATION',
                };

                // fallback chain: Pro Max → Pro → base; Plus → base; Mini → base
                const appleImageFallback = (key) => {
                    if (key.endsWith(' PRO MAX')) return key.replace(/ PRO MAX$/, ' PRO');
                    if (key.endsWith(' PRO')) return key.replace(/ PRO$/, '');
                    if (key.endsWith(' PLUS')) return key.replace(/ PLUS$/, '');
                    if (key.endsWith(' MINI')) return key.replace(/ MINI$/, '');
                    return null;
                };

                // resolve image URL: exact → alias → fallback chain; variantIdx cycles colors
                const appleGetImage = (category, modelKey, variantIdx = 0) => {
                    const catMap = appleImageMap.value[category] || {};
                    let imgs = catMap[modelKey];
                    let offset = 0;
                    if (!imgs || !imgs.length) {
                        // static alias (exact product, same color slot)
                        const aliased = appleModelAliases[modelKey];
                        imgs = aliased ? catMap[aliased] : null;
                    }
                    if (!imgs || !imgs.length) {
                        // fallback chain (related product, offset color slot)
                        const fb1 = appleImageFallback(modelKey);
                        imgs = fb1 ? catMap[fb1] : null;
                        offset = 1;
                        if (!imgs || !imgs.length) {
                            const fb2 = fb1 ? appleImageFallback(fb1) : null;
                            imgs = fb2 ? catMap[fb2] : null;
                            offset = 2;
                        }
                    }
                    if (!imgs || !imgs.length) return null;
                    return imgs[(variantIdx + offset) % imgs.length];
                };

                const loadAppleData = () => {
                    if (!appleProductsLoaded.value) loadAppleProducts();
                    appleLoadImages(appleCategory.value);
                    if (!catalogTemplatesLoaded.value) {
                        loadCatalogTemplates();
                    } else if (!appleSelectedTemplateId.value && catalogTemplates.value.length) {
                        appleSelectedTemplateId.value = catalogTemplates.value[0].ID;
                    }
                };

                watch([appleSelectedModels, appleSelectedVariants], () => {
                    localStorage.setItem('ppp_apple_selection', JSON.stringify({
                        models: appleSelectedModels.value,
                        variants: appleSelectedVariants.value,
                    }));
                }, { deep: true });
                watch(appleCfg, (cfg) => {
                    localStorage.setItem('ppp_apple_canvas_cfg', JSON.stringify(cfg || {}));
                }, { deep: true });

                const appleCanvasPresetForColumns = (columns) => ({
                    3: { padding: 150, gap: 60, imageHeight: 128, modelFontSize: 15, srpFontSize: 11, rowGap: 5, rowsPerPage: 3 },
                    4: { padding: 92, gap: 42, imageHeight: 118, modelFontSize: 14, srpFontSize: 11, rowGap: 5, rowsPerPage: 3 },
                    5: { padding: 78, gap: 32, imageHeight: 96, modelFontSize: 12, srpFontSize: 10, rowGap: 4, rowsPerPage: 4 },
                    6: { padding: 82, gap: 34, imageHeight: 88, modelFontSize: 12, srpFontSize: 10, rowGap: 4, rowsPerPage: 4 },
                    7: { padding: 72, gap: 26, imageHeight: 78, modelFontSize: 11, srpFontSize: 9, rowGap: 3, rowsPerPage: 5 },
                    8: { padding: 62, gap: 20, imageHeight: 70, modelFontSize: 10, srpFontSize: 9, rowGap: 3, rowsPerPage: 5 },
                }[Number(columns)] || { padding: 92, gap: 42, imageHeight: 118, modelFontSize: 14, srpFontSize: 11, rowGap: 5, rowsPerPage: 3 });

                watch(appleColumnsPerRow, (columns) => {
                    appleCfg.value = {
                        ...appleCfg.value,
                        ...appleCanvasPresetForColumns(columns),
                    };
                });

                watch(appleCategory, (category) => {
                    appleLoadImages(category);
                    localStorage.setItem('ppp_apple_category', category);
                });

                watch(applePriceKey, (key) => {
                    localStorage.setItem('ppp_apple_price_key', key);
                });

                // ── selection ────────────────────────────────────────────────────────────────

                const appleVariantKey = (variant) => variant.storage || String(variant.ID);

                const appleVariantLabel = (group, variant) => variant.storage || '—';

                const appleToggleModel = (model) => {
                    const idx = appleSelectedModels.value.indexOf(model);
                    if (idx >= 0) {
                        appleSelectedModels.value.splice(idx, 1);
                        const sv = { ...appleSelectedVariants.value };
                        delete sv[model];
                        appleSelectedVariants.value = sv;
                    } else {
                        appleSelectedModels.value.push(model);
                        const group = appleGroupedModels.value.find((g) => g.model === model);
                        if (group) {
                            appleSelectedVariants.value = {
                                ...appleSelectedVariants.value,
                                [model]: group.variants.map(appleVariantKey),
                            };
                        }
                    }
                };

                const appleToggleVariant = (model, vKey) => {
                    const current = appleSelectedVariants.value[model] || [];
                    const idx = current.indexOf(vKey);
                    const next = idx >= 0 ? current.filter((k) => k !== vKey) : [...current, vKey];
                    if (next.length === 0) {
                        // deselect model when all variants removed
                        appleSelectedModels.value = appleSelectedModels.value.filter((m) => m !== model);
                        const sv = { ...appleSelectedVariants.value };
                        delete sv[model];
                        appleSelectedVariants.value = sv;
                    } else {
                        appleSelectedVariants.value = { ...appleSelectedVariants.value, [model]: next };
                    }
                };

                const appleSelectAll = () => {
                    appleSelectedModels.value = appleGroupedModels.value.map((g) => g.model);
                    const sv = {};
                    appleGroupedModels.value.forEach((g) => {
                        sv[g.model] = g.variants.map(appleVariantKey);
                    });
                    appleSelectedVariants.value = sv;
                };

                const appleResetSelection = () => {
                    appleSelectedModels.value = [];
                    appleSelectedVariants.value = {};
                    applePreviewImages.value = [];
                };

                // ── canvas generation ────────────────────────────────────────────────────────

                const appleLoadImage = (src) => new Promise((resolve) => {
                    const img = new Image();
                    img.crossOrigin = 'anonymous';
                    img.onload = () => resolve(img);
                    img.onerror = () => resolve(null);
                    img.src = src;
                });

                const appleDrawRoundedRect = (ctx, x, y, w, h, r) => {
                    const cr = Math.min(r, h / 2, w / 2);
                    ctx.beginPath();
                    ctx.moveTo(x + cr, y);
                    ctx.lineTo(x + w - cr, y);
                    ctx.quadraticCurveTo(x + w, y, x + w, y + cr);
                    ctx.lineTo(x + w, y + h - cr);
                    ctx.quadraticCurveTo(x + w, y + h, x + w - cr, y + h);
                    ctx.lineTo(x + cr, y + h);
                    ctx.quadraticCurveTo(x, y + h, x, y + h - cr);
                    ctx.lineTo(x, y + cr);
                    ctx.quadraticCurveTo(x, y, x + cr, y);
                    ctx.closePath();
                };

                const appleDrawCard = (ctx, cfg, group, imgEl, x, y, cardW) => {
                    const imgH       = Number(cfg.imageHeight    || 100);
                    const mFs        = Number(cfg.modelFontSize  || 13);
                    const sFs        = Math.max(9, Math.round(mFs * 0.85));
                    const pFs        = Number(cfg.priceFontSize  || sFs);
                    const rGap       = Number(cfg.rowGap         || 4);
                    const lFs        = Math.max(9, Math.round(mFs * 0.65));
                    const badgePadX  = 6;
                    const badgePadY  = 3;
                    const rowH       = sFs + badgePadY * 2;

                    let curY = y;

                    // product image (centered, aspect-fit inside imgH)
                    if (imgEl) {
                        const aspect = imgEl.naturalWidth / imgEl.naturalHeight;
                        let dw = cardW, dh = dw / aspect;
                        if (dh > imgH) { dh = imgH; dw = dh * aspect; }
                        ctx.drawImage(imgEl, x + (cardW - dw) / 2, curY + (imgH - dh), dw, dh);
                    }
                    curY += imgH + 8;

                    // model name (bold, centered, word-wrap)
                    ctx.font = `700 ${mFs}px Inter, Arial, sans-serif`;
                    ctx.fillStyle   = cfg.modelColor || '#ffffff';
                    ctx.textAlign   = 'center';
                    ctx.textBaseline= 'top';
                    const words = group.model.split(' ');
                    const lines = [];
                    let ln = '';
                    for (const w of words) {
                        const test = ln ? ln + ' ' + w : w;
                        if (ctx.measureText(test).width > cardW) { if (ln) lines.push(ln); ln = w; }
                        else ln = test;
                    }
                    if (ln) lines.push(ln);
                    for (const line of lines) {
                        ctx.fillText(line, x + cardW / 2, curY, cardW);
                        curY += mFs + 1;
                    }
                    curY += 3;

                    // "Harga mulai" label
                    ctx.font        = `400 italic ${lFs}px Inter, Arial, sans-serif`;
                    ctx.fillStyle   = cfg.labelColor || 'rgba(255,255,255,0.6)';
                    ctx.textAlign   = 'center';
                    ctx.textBaseline= 'top';
                    ctx.fillText('Harga mulai', x + cardW / 2, curY, cardW);
                    curY += lFs + 5;

                    // storage + price rows (inline: [BADGE] [PRICE]) — block centered in card
                    group.variants.forEach((variant) => {
                        const storage    = appleVariantLabel(group.model, variant);
                        const price      = appleResolvePrice(variant);
                        if (!price) return;
                        const priceText  = Number(price).toLocaleString('id-ID');
                        const srp        = applePriceKey.value === 'special_price' ? appleResolveSrp(variant) : null;
                        const srpText    = srp && Number(srp) > Number(price) ? Number(srp).toLocaleString('id-ID') : '';
                        const srpFs      = Number(cfg.srpFontSize || 11);
                        const currentRowH = srpText ? rowH + srpFs + 2 : rowH;
                        const rowMidY    = curY + currentRowH / 2;

                        const rowInset = Math.max(12, Math.min(28, cardW * 0.12));
                        const rowLeftX = x + rowInset;
                        const priceRightX = x + cardW - rowInset;
                        ctx.font = `700 ${sFs}px Inter, Arial, sans-serif`;
                        const badgeTextW = storage ? ctx.measureText(storage).width : 0;
                        const badgeW = storage ? Math.min(badgeTextW + badgePadX * 2, Math.max(24, cardW * 0.36)) : 0;

                        if (storage) {
                            ctx.font = `700 ${sFs}px Inter, Arial, sans-serif`;
                            ctx.strokeStyle = 'rgba(255,255,255,0.55)';
                            ctx.lineWidth   = 1;
                            appleDrawRoundedRect(ctx, rowLeftX, rowMidY - rowH / 2, badgeW, rowH, rowH / 2);
                            ctx.stroke();

                            ctx.fillStyle    = cfg.badgeTextColor || '#ffffff';
                            ctx.textAlign    = 'center';
                            ctx.textBaseline = 'middle';
                            ctx.fillText(storage, rowLeftX + badgeW / 2, rowMidY, badgeW - 4);
                        }

                        // price + (optional) SRP strike, right-aligned within row inset
                        if (srpText) {
                            ctx.font         = `600 ${srpFs}px Inter, Arial, sans-serif`;
                            ctx.fillStyle    = cfg.srpColor || 'rgba(255,255,255,0.5)';
                            ctx.textAlign    = 'right';
                            ctx.textBaseline = 'top';
                            ctx.fillText(srpText, priceRightX, curY);
                            const srpW = ctx.measureText(srpText).width;
                            ctx.strokeStyle = '#dc2626';
                            ctx.lineWidth = 1.5;
                            ctx.beginPath();
                            ctx.moveTo(priceRightX - srpW, curY + srpFs * 0.82);
                            ctx.lineTo(priceRightX, curY + srpFs * 0.18);
                            ctx.stroke();
                        }
                        ctx.font         = `700 ${pFs}px Inter, Arial, sans-serif`;
                        ctx.fillStyle    = cfg.priceColor || '#ffffff';
                        ctx.textAlign    = 'right';
                        ctx.textBaseline = srpText ? 'top' : 'middle';
                        ctx.fillText(priceText, priceRightX, srpText ? curY + srpFs + 2 : rowMidY);

                        curY += currentRowH + rGap;
                    });

                    return curY - y;
                };

                const appleCardHeight = (cfg, variantCount) => {
                    const imgH   = Number(cfg.imageHeight     || 100);
                    const mFs    = Number(cfg.modelFontSize   || 13);
                    const sFs    = Math.max(9, Math.round(mFs * 0.85));
                    const rGap   = Number(cfg.rowGap          || 4);
                    const lFs    = Math.max(9, Math.round(mFs * 0.65));
                    const rowH   = sFs + 3 * 2;
                    const srpFs  = Number(cfg.srpFontSize || 11);
                    const perRowH = applePriceKey.value === 'special_price' ? (rowH + srpFs + 2) : rowH;
                    // 2 model lines max estimate + label + variants
                    return imgH + 8 + (mFs + 1) * 2 + 3 + lFs + 5 + variantCount * (perRowH + rGap);
                };

                const appleDrawPage = (template, groups, imageEls, cfg, titleText) => new Promise((resolve, reject) => {
                    const canvas = document.createElement('canvas');
                    canvas.width = Number(template.canvas_width || 1080);
                    canvas.height = Number(template.canvas_height || (template.format === 'feed' ? 1350 : 1920));
                    const ctx = canvas.getContext('2d');

                    const cols = appleColumnsPerRow.value;
                    const padding = Number(cfg.padding || 100);
                    const gap = Number(cfg.gap || 50);
                    const topOffset = Number(cfg.topOffset || 150);
                    const bottomSafe = Number(cfg.bottomSafe || 130);
                    const usableW = canvas.width - padding * 2;
                    const cardW = (usableW - gap * (cols - 1)) / cols;

                    const paint = () => {
                        // draw title (category · kondisi) in topOffset area
                        if (titleText) {
                            const titleFs   = Number(cfg.titleFontSize || 32);
                            const conditionFs = Math.round(titleFs * 0.6);
                            const titleY    = Number(cfg.titleY || topOffset - titleFs - conditionFs - 16);
                            const parts     = titleText.split('\n');

                            ctx.textAlign   = 'left';
                            ctx.textBaseline = 'top';

                            // category line (bold, large)
                            ctx.font      = `700 ${titleFs}px Inter, Arial, sans-serif`;
                            ctx.fillStyle = cfg.modelColor || '#ffffff';
                            ctx.fillText(parts[0] || '', padding, titleY, usableW);

                            // kondisi line (lighter, smaller)
                            if (parts[1]) {
                                ctx.font      = `400 ${conditionFs}px Inter, Arial, sans-serif`;
                                ctx.fillStyle = cfg.labelColor || 'rgba(255,255,255,0.7)';
                                ctx.fillText(parts[1], padding, titleY + titleFs + 4, usableW);
                            }
                        }

                        let curX = padding;
                        let curY = topOffset;
                        let colIdx = 0;
                        let rowMaxH = 0;
                        let activeSection = '';
                        const showRowSep = cfg.rowSep !== false;

                        groups.forEach((group, gi) => {
                            const section = String(group.section || '').trim();
                            if (section && section !== activeSection) {
                                if (colIdx !== 0) {
                                    colIdx = 0;
                                    curX = padding;
                                    curY += rowMaxH + gap;
                                    rowMaxH = 0;
                                }
                                ctx.font = `800 ${Math.max(13, Number(cfg.modelFontSize || 13))}px Inter, Arial, sans-serif`;
                                ctx.fillStyle = cfg.labelColor || 'rgba(255,255,255,0.7)';
                                ctx.textAlign = 'left';
                                ctx.textBaseline = 'top';
                                ctx.fillText(section, padding, curY, usableW);
                                curY += Math.max(22, Number(cfg.modelFontSize || 13) + 9);
                                activeSection = section;
                            }
                            const imgEl = imageEls[gi] || null;
                            const used  = appleDrawCard(ctx, cfg, group, imgEl, curX, curY, cardW);
                            rowMaxH = Math.max(rowMaxH, used);

                            colIdx++;
                            if (colIdx >= cols) {
                                colIdx = 0;
                                curX   = padding;
                                curY  += rowMaxH + gap;
                                rowMaxH = 0;
                                // row separator (skip after last group)
                                if (gi < groups.length - 1 && showRowSep) {
                                    const sepY = curY - gap / 2;
                                    ctx.save();
                                    ctx.strokeStyle = 'rgba(255,255,255,0.18)';
                                    ctx.lineWidth   = 1;
                                    ctx.beginPath();
                                    ctx.moveTo(padding, sepY);
                                    ctx.lineTo(canvas.width - padding, sepY);
                                    ctx.stroke();
                                    ctx.restore();
                                }
                            } else {
                                curX += cardW + gap;
                            }
                        });

                        resolve(canvas.toDataURL('image/png'));
                    };

                    if (template.background_url) {
                        const bg = new Image();
                        bg.onload = () => { ctx.drawImage(bg, 0, 0, canvas.width, canvas.height); paint(); };
                        bg.onerror = reject;
                        bg.src = template.background_url;
                    } else {
                        ctx.fillStyle = '#7b0000';
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                        paint();
                    }
                });

                const appleDrawSecondIphoneTablePage = (template, rows, cfg, titleText) => new Promise((resolve, reject) => {
                    const canvas = document.createElement('canvas');
                    canvas.width = Number(template.canvas_width || 1080);
                    canvas.height = Number(template.canvas_height || (template.format === 'feed' ? 1350 : 1920));
                    const ctx = canvas.getContext('2d');
                    const padding = Number(cfg.padding || 92);
                    const topOffset = Number(cfg.topOffset || 150);
                    const usableW = canvas.width - padding * 2;
                    const rowH = Math.max(20, Number(cfg.rowHeight || 34));
                    const headerH = Math.max(24, Number(cfg.headerHeight || 40));
                    const headerFontSize = Math.max(9, Number(cfg.tableHeaderFontSize || cfg.modelFontSize || 14));
                    const bodyFontSize = Math.max(9, Number(cfg.tableBodyFontSize || cfg.modelFontSize || 14));
                    const seriRatio = Math.max(1, Number(cfg.tableSeriWidth || 34)) / 100;
                    const storageRatio = Math.max(1, Number(cfg.tableStorageWidth || 18)) / 100;
                    const beaRatio = Math.max(1, Number(cfg.tableBeacukaiWidth || 24)) / 100;
                    const seriW = usableW * seriRatio;
                    const storageW = usableW * storageRatio;
                    const beaW = usableW * beaRatio;
                    const iboxW = Math.max(1, usableW - seriW - storageW - beaW);
                    const xSeri = padding;
                    const xStorage = xSeri + seriW;
                    const xBea = xStorage + storageW;
                    const xIbox = xBea + beaW;
                    const paint = () => {
                        const titleFs = Number(cfg.titleFontSize || 32);
                        const conditionFs = Math.round(titleFs * 0.6);
                        const titleY = Number(cfg.titleY || topOffset - titleFs - conditionFs - 16);
                        const parts = titleText.split('\n');
                        ctx.textAlign = 'left';
                        ctx.textBaseline = 'top';
                        ctx.font = `700 ${titleFs}px Inter, Arial, sans-serif`;
                        ctx.fillStyle = cfg.modelColor || '#ffffff';
                        ctx.fillText(parts[0] || '', padding, titleY, usableW);
                        if (parts[1]) {
                            ctx.font = `400 ${conditionFs}px Inter, Arial, sans-serif`;
                            ctx.fillStyle = cfg.labelColor || 'rgba(255,255,255,0.7)';
                            ctx.fillText(parts[1], padding, titleY + titleFs + 4, usableW);
                        }

                        let y = topOffset;
                        ctx.fillStyle = cfg.tableHeaderBg || 'rgba(255,255,255,0.16)';
                        {
                            const r = Number(cfg.tableHeaderRadius ?? 12);
                            const hw = Math.max(0, Math.min(r, usableW / 2, headerH / 2));
                            ctx.beginPath();
                            ctx.moveTo(padding, y + headerH);
                            ctx.lineTo(padding, y + hw);
                            ctx.quadraticCurveTo(padding, y, padding + hw, y);
                            ctx.lineTo(padding + usableW - hw, y);
                            ctx.quadraticCurveTo(padding + usableW, y, padding + usableW, y + hw);
                            ctx.lineTo(padding + usableW, y + headerH);
                            ctx.closePath();
                            ctx.fill();
                        }
                        ctx.font = `800 ${headerFontSize}px Inter, Arial, sans-serif`;
                        ctx.fillStyle = cfg.badgeTextColor || '#ffffff';
                        ctx.textBaseline = 'middle';
                        ctx.textAlign = 'left';
                        ctx.fillText('SERI', xSeri + 14, y + headerH / 2, seriW - 22);
                        ctx.fillText('STORAGE', xStorage + 10, y + headerH / 2, storageW - 18);
                        ctx.textAlign = 'right';
                        ctx.fillText('SECOND BEACUKAI', xBea + beaW - 12, y + headerH / 2, beaW - 18);
                        ctx.fillText('SECOND IBOX', xIbox + iboxW - 12, y + headerH / 2, iboxW - 18);
                        y += headerH;

                        let prevModel = null;
                        let seriIdx = -1;
                        rows.forEach((row, idx) => {
                            const isSameGroup = prevModel === row.model;
                            if (!isSameGroup) seriIdx += 1;
                            ctx.fillStyle = seriIdx % 2 === 0
                                ? (cfg.tableRowOddBg || 'rgba(255,255,255,0.08)')
                                : (cfg.tableRowEvenBg || 'rgba(255,255,255,0.16)');
                            ctx.fillRect(padding, y, usableW, rowH);

                            // vertical border between STORAGE and price columns for readability
                            ctx.strokeStyle = cfg.tableBorderColor || 'rgba(255,255,255,0.35)';
                            ctx.lineWidth = 1;
                            ctx.beginPath();
                            ctx.moveTo(xStorage, y);
                            ctx.lineTo(xStorage, y + rowH);
                            ctx.moveTo(rows[idx + 1]?.model === row.model ? xStorage : padding, y + rowH);
                            ctx.lineTo(canvas.width - padding, y + rowH);
                            ctx.stroke();

                            ctx.font = `700 ${bodyFontSize}px Inter, Arial, sans-serif`;
                            ctx.fillStyle = cfg.modelColor || '#ffffff';
                            ctx.textBaseline = 'middle';
                            if (!isSameGroup) {
                                let groupLen = 1;
                                while (rows[idx + groupLen]?.model === row.model) groupLen += 1;
                                ctx.textAlign = 'left';
                                ctx.fillText(row.model || '—', xSeri + 14, y + (groupLen * rowH) / 2, seriW - 22);
                            }
                            ctx.textAlign = 'left';
                            ctx.fillText(row.storage || '—', xStorage + 10, y + rowH / 2, storageW - 18);
                            ctx.textAlign = 'right';
                            ctx.fillText(formatApplePrice(row.harga_kondisi?.BEACUKAI), xBea + beaW - 12, y + rowH / 2, beaW - 18);
                            ctx.fillText(formatApplePrice(row.harga_kondisi?.EXIBOX), xIbox + iboxW - 12, y + rowH / 2, iboxW - 18);
                            if (!isSameGroup && idx > 0) {
                                ctx.strokeStyle = cfg.tableGroupLineColor || 'rgba(255,255,255,0.5)';
                                ctx.lineWidth = 1.5;
                                ctx.beginPath();
                                ctx.moveTo(padding, y);
                                ctx.lineTo(canvas.width - padding, y);
                                ctx.stroke();
                            }
                            prevModel = row.model;
                            y += rowH;
                        });
                        resolve(canvas.toDataURL('image/png'));
                    };
                    if (template.background_url) {
                        const bg = new Image();
                        bg.onload = () => { ctx.drawImage(bg, 0, 0, canvas.width, canvas.height); paint(); };
                        bg.onerror = reject;
                        bg.src = template.background_url;
                    } else {
                        ctx.fillStyle = '#7b0000';
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                        paint();
                    }
                });

                const openApplePreviewModal = (index) => {
                    applePreviewModalIndex.value = index;
                    applePreviewZoom.value = 1;
                    applePreviewModalOpen.value = true;
                };
                const closeApplePreviewModal = () => {
                    applePreviewModalOpen.value = false;
                };
                const applePreviewNav = (delta) => {
                    if (!applePreviewImages.value.length) return;
                    applePreviewModalIndex.value = (applePreviewModalIndex.value + delta + applePreviewImages.value.length) % applePreviewImages.value.length;
                    applePreviewZoom.value = 1;
                };
                const applePreviewZoomBy = (factor) => {
                    applePreviewZoom.value = Math.max(0.5, Math.min(4, applePreviewZoom.value * factor));
                };
                const downloadApplePreview = (img, idx) => {
                    const a = document.createElement('a');
                    a.href = img;
                    a.download = `katalog-apple-${appleCategory.value.toLowerCase()}-${idx + 1}.png`;
                    a.click();
                };

                const generateAppleCatalog = async () => {
                    const template = catalogTemplates.value.find((t) => t.ID === appleSelectedTemplateId.value);
                    if (!template) { showNotification('Pilih template dulu', 'warning'); return; }
                    if (!appleSelectedModels.value.length) { showNotification('Pilih model dulu', 'warning'); return; }

                    appleGenerating.value = true;
                    try {
                        const cfg = { ...applyCfgDefaults(), ...appleCfg.value };
                        const cols = appleColumnsPerRow.value;
                        const canvasH = Number(template.canvas_height || (template.format === 'feed' ? 1350 : 1920));
                        const topOffset = Number(cfg.topOffset || 150);
                        const bottomSafe = Number(cfg.bottomSafe || 130);
                        const gap = Number(cfg.gap || 50);

                        // build ordered group list from selection, filter variants per group
                        const allGroups = appleGroupedModels.value;
                        const selectedGroups = appleSelectedModels.value
                            .map((m) => allGroups.find((g) => g.model === m))
                            .filter(Boolean)
                            .map((g) => {
                                const sv = appleSelectedVariants.value[g.model];
                                if (!sv || sv.length === 0) return g;
                                return { ...g, variants: g.variants.filter((v) => sv.includes(appleVariantKey(v))) };
                            })
                            .map((g) => ({
                                ...g,
                                variants: applePriceKey.value === 'second_table'
                                    ? g.variants
                                    : g.variants.filter((v) => appleResolvePrice(v)),
                            }))
                            .filter((g) => g.variants.length > 0)
                            .map((g) => ({ ...g, section: appleCategory.value === 'IPHONE' ? '' : appleModelGroupName(appleCategory.value, g.model) }));
                        if (!selectedGroups.length) { showNotification('Tidak ada varian dengan harga untuk dibuat katalog', 'warning'); return; }

                        // Special layout: iPhone "Second Beacukai + Second iBox" combined table
                        if (appleCategory.value === 'IPHONE' && applePriceKey.value === 'second_table') {
                            const rows = selectedGroups
                                .flatMap((g) => g.variants.map((v) => ({ ...v, model: g.model })))
                                .filter((v) => v.harga_kondisi && (v.harga_kondisi.BEACUKAI || v.harga_kondisi.EXIBOX));
                            if (!rows.length) { showNotification('Tidak ada varian dengan harga second', 'warning'); return; }
                            const rowH = Math.max(28, Number(cfg.rowHeight || 34));
                            const headerH = Math.max(34, Number(cfg.headerHeight || 40));
                            const rowsPerPageTable = Math.max(1, Math.floor((canvasH - topOffset - bottomSafe - headerH) / rowH));
                            const priceLabel = 'Second Beacukai + Second iBox';
                            const titleText = `${appleCategory.value}\n${priceLabel}`;
                            // paginate at series boundaries so one seri is never split across pages
                            const pages = [];
                            let start = 0;
                            while (start < rows.length) {
                                let end = Math.min(start + rowsPerPageTable, rows.length);
                                if (end < rows.length && rows[end - 1].model === rows[end].model) {
                                    let boundary = end;
                                    while (boundary > start && rows[boundary - 1].model === rows[end].model) {
                                        boundary -= 1;
                                    }
                                    if (boundary > start) end = boundary;
                                }
                                pages.push(await appleDrawSecondIphoneTablePage(template, rows.slice(start, end), cfg, titleText));
                                start = end;
                            }
                            applePreviewImages.value = pages;
                            showNotification(`${pages.length} halaman katalog Apple dibuat`);
                            return;
                        }

                        // figure out how many cards fit per page (estimate by max variant count)
                        const maxVariants = Math.max(...selectedGroups.map((g) => g.variants.length));
                        const estimatedCardH = appleCardHeight(cfg, maxVariants);
                        const usableH = canvasH - topOffset - bottomSafe;
                        const rowsPerPage = cfg.rowsPerPage
                            ? Math.max(1, Number(cfg.rowsPerPage))
                            : Math.max(1, Math.floor((usableH + gap) / (estimatedCardH + gap)));
                        const cardsPerPage = rowsPerPage * cols;

                        // load images: use global position i as color slot so consecutive models
                        // always get different images even when from different folders
                        const catMap = appleImageMap.value[appleCategory.value] || {};
                        const imageUrls = selectedGroups.map((g, i) => {
                            let imgs = catMap[g.modelKey];
                            if (!imgs || !imgs.length) {
                                const al = appleModelAliases[g.modelKey];
                                if (al && catMap[al]) imgs = catMap[al];
                            }
                            if (!imgs || !imgs.length) {
                                const fb1 = appleImageFallback(g.modelKey);
                                if (fb1 && catMap[fb1]) imgs = catMap[fb1];
                                else if (fb1) {
                                    const fb2 = appleImageFallback(fb1);
                                    if (fb2 && catMap[fb2]) imgs = catMap[fb2];
                                }
                            }
                            if (!imgs || !imgs.length) return null;
                            return imgs[i % imgs.length];
                        });
                        const imageEls = await Promise.all(imageUrls.map((url) => url ? appleLoadImage(url) : Promise.resolve(null)));

                        // build title: "CATEGORY\nKondisi Label"
                        const cat = appleCategory.value;
                        const priceLabel = (applePriceOptionMap[cat] || []).find((o) => o.key === applePriceKey.value)?.label
                            || applePriceKey.value;
                        const titleText = `${cat}\n${priceLabel}`;

                        // paginate
                        const pages = [];
                        for (let i = 0; i < selectedGroups.length; i += cardsPerPage) {
                            const pageGroups = selectedGroups.slice(i, i + cardsPerPage);
                            const pageImages = imageEls.slice(i, i + cardsPerPage);
                            pages.push(await appleDrawPage(template, pageGroups, pageImages, cfg, titleText));
                        }

                        applePreviewImages.value = pages;
                        showNotification(`${pages.length} halaman katalog Apple dibuat`);
                    } catch (err) {
                        notifyError('', err, 'Gagal generate katalog Apple.');
                    } finally {
                        appleGenerating.value = false;
                    }
                };

                // auto-pick first template for Apple when templates finish loading
                watch(catalogTemplatesLoaded, (loaded) => {
                    if (loaded && !appleSelectedTemplateId.value && catalogTemplates.value.length) {
                        appleSelectedTemplateId.value = catalogTemplates.value[0].ID;
                    }
                });

                // ── Shared Color Picker ───────────────────────────────────────
                const colorPickerOpen   = ref(false);
                const colorPickerSetter = ref(null); // fn(colorStr) — set by openColorPicker
                const colorPickerH      = ref(0);
                const colorPickerS      = ref(1);
                const colorPickerV      = ref(1);
                const colorPickerA      = ref(1);
                const colorPickerPos    = ref({ x: 0, y: 0 });
                const colorPickerSaved  = ref(JSON.parse(localStorage.getItem('ppp_saved_colors') || '[]'));

                const _cpHsv2Rgb = (h, s, v) => {
                    const c = v * s, x = c * (1 - Math.abs((h / 60) % 2 - 1)), m = v - c;
                    let r = 0, g = 0, b = 0;
                    if (h < 60)       { r = c; g = x; }
                    else if (h < 120) { r = x; g = c; }
                    else if (h < 180) { g = c; b = x; }
                    else if (h < 240) { g = x; b = c; }
                    else if (h < 300) { r = x; b = c; }
                    else              { r = c; b = x; }
                    return [Math.round((r+m)*255), Math.round((g+m)*255), Math.round((b+m)*255)];
                };
                const _cpRgb2Hsv = (r, g, b) => {
                    r /= 255; g /= 255; b /= 255;
                    const max = Math.max(r,g,b), min = Math.min(r,g,b), d = max - min;
                    let h = 0;
                    const s = max === 0 ? 0 : d / max, v = max;
                    if (d > 0) {
                        if (max === r) h = ((g - b) / d) % 6;
                        else if (max === g) h = (b - r) / d + 2;
                        else h = (r - g) / d + 4;
                        h = Math.round(h * 60); if (h < 0) h += 360;
                    }
                    return [h, s, v];
                };
                const _cpParseColor = (val) => {
                    let r = 255, g = 255, b = 255, a = 1;
                    if (!val) return { r, g, b, a };
                    const hm = val.match(/^#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})/i);
                    const rm = val.match(/rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(?:,\s*([\d.]+))?\s*\)/);
                    if (hm) { r = parseInt(hm[1],16); g = parseInt(hm[2],16); b = parseInt(hm[3],16); }
                    else if (rm) { r = +rm[1]; g = +rm[2]; b = +rm[3]; a = rm[4] !== undefined ? +rm[4] : 1; }
                    return { r, g, b, a };
                };

                const colorPickerRgb      = computed(() => _cpHsv2Rgb(colorPickerH.value, colorPickerS.value, colorPickerV.value));
                const colorPickerHueColor = computed(() => { const [r,g,b] = _cpHsv2Rgb(colorPickerH.value, 1, 1); return `rgb(${r},${g},${b})`; });
                const colorPickerHexStr   = computed(() => colorPickerRgb.value.map(v => v.toString(16).padStart(2,'0')).join('').toUpperCase());
                const colorPickerColorStr = computed(() => {
                    const [r,g,b] = colorPickerRgb.value, a = colorPickerA.value;
                    return a >= 1 ? '#' + colorPickerHexStr.value.toLowerCase() : `rgba(${r},${g},${b},${parseFloat(a.toFixed(2))})`;
                });

                watchEffect(() => {
                    if (colorPickerOpen.value && colorPickerSetter.value) {
                        colorPickerSetter.value(colorPickerColorStr.value);
                    }
                });

                // getter() reads current value; setter(v) writes new value
                const openColorPicker = (getter, setter, event) => {
                    colorPickerSetter.value = setter;
                    const { r, g, b, a } = _cpParseColor(getter() || '#ffffff');
                    [colorPickerH.value, colorPickerS.value, colorPickerV.value] = _cpRgb2Hsv(r, g, b);
                    colorPickerA.value = a;
                    const rect = event.currentTarget.getBoundingClientRect();
                    colorPickerPos.value = {
                        x: Math.max(8, Math.min(window.innerWidth - 232, rect.left)),
                        y: Math.max(8, rect.top - 268),
                    };
                    colorPickerOpen.value = true;
                };
                const colorPickerClose = () => { colorPickerOpen.value = false; };

                const colorPickerGradDrag = (event) => {
                    event.preventDefault();
                    const el = event.currentTarget;
                    const update = (e) => {
                        const rect = el.getBoundingClientRect();
                        colorPickerS.value = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
                        colorPickerV.value = Math.max(0, Math.min(1, 1 - (e.clientY - rect.top) / rect.height));
                    };
                    update(event);
                    const move = (e) => update(e);
                    const up = () => { document.removeEventListener('mousemove', move); document.removeEventListener('mouseup', up); };
                    document.addEventListener('mousemove', move);
                    document.addEventListener('mouseup', up);
                };
                const colorPickerSliderDrag = (event, onUpdate) => {
                    event.preventDefault();
                    const el = event.currentTarget;
                    const update = (e) => {
                        const rect = el.getBoundingClientRect();
                        onUpdate(Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width)));
                    };
                    update(event);
                    const move = (e) => update(e);
                    const up = () => { document.removeEventListener('mousemove', move); document.removeEventListener('mouseup', up); };
                    document.addEventListener('mousemove', move);
                    document.addEventListener('mouseup', up);
                };
                const colorPickerSetHex = (hex) => {
                    if (!/^[0-9a-f]{6}$/i.test(hex)) return;
                    [colorPickerH.value, colorPickerS.value, colorPickerV.value] = _cpRgb2Hsv(parseInt(hex.slice(0,2),16), parseInt(hex.slice(2,4),16), parseInt(hex.slice(4,6),16));
                };
                const colorPickerAddSaved = () => {
                    const c = colorPickerColorStr.value;
                    if (!colorPickerSaved.value.includes(c)) {
                        if (colorPickerSaved.value.length >= 16) colorPickerSaved.value.shift();
                        colorPickerSaved.value.push(c);
                        localStorage.setItem('ppp_saved_colors', JSON.stringify(colorPickerSaved.value));
                    }
                };
                const colorPickerLoadSaved = (color) => {
                    const { r, g, b, a } = _cpParseColor(color);
                    [colorPickerH.value, colorPickerS.value, colorPickerV.value] = _cpRgb2Hsv(r, g, b);
                    colorPickerA.value = a;
                };
                // ─────────────────────────────────────────────────────────────

                // backward-compat aliases (apple-katalog template references)
                const applePickerOpen    = colorPickerOpen;
                const applePickerHueColor = colorPickerHueColor;
                const applePickerHexStr   = colorPickerHexStr;
                const applePickerColorStr = colorPickerColorStr;
                const applePickerPos      = colorPickerPos;
                const applePickerS        = colorPickerS;
                const applePickerV        = colorPickerV;
                const applePickerH        = colorPickerH;
                const applePickerA        = colorPickerA;
                const appleSavedColors    = colorPickerSaved;
                const applePickerClose    = colorPickerClose;
                const applePickerGradDrag = colorPickerGradDrag;
                const applePickerSliderDrag = colorPickerSliderDrag;
                const applePickerSetHex   = colorPickerSetHex;
                const applePickerAddSaved = colorPickerAddSaved;
                const applePickerLoadSaved = colorPickerLoadSaved;
                // ─────────────────────────────────────────────────────────────

                const applyCfgDefaults = () => ({
                    padding: 100,
                    gap: 50,
                    imageHeight: 100,
                    modelFontSize: 13,
                    priceFontSize: 13,
                    topOffset: 150,
                    bottomSafe: 130,
                    titleFontSize: 32,
                    titleY: null, // null = auto: topOffset - titleFs - conditionFs - 16
                    headerHeight: 40,
                    rowHeight: 34,
                    tableHeaderFontSize: 14,
                    tableBodyFontSize: 14,
                    tableSeriWidth: 34,
                    tableStorageWidth: 18,
                    tableBeacukaiWidth: 24,
                    tableHeaderRadius: 12,
                    tableHeaderBg: 'rgba(255,255,255,0.16)',
                    tableRowOddBg: 'rgba(255,255,255,0.08)',
                    tableRowEvenBg: 'rgba(255,255,255,0.16)',
                    tableBorderColor: 'rgba(255,255,255,0.35)',
                    tableGroupLineColor: 'rgba(255,255,255,0.5)',
                    modelColor: '#ffffff',
                    labelColor: 'rgba(255,255,255,0.7)',
                    badgeTextColor: '#ffffff',
                    priceColor: '#ffffff',
                    srpColor: 'rgba(255,255,255,0.5)',
                    srpFontSize: 11,
                    rowGap: 4,
                    rowSep: true,
                    rowsPerPage: 4,
                });
@endverbatim
