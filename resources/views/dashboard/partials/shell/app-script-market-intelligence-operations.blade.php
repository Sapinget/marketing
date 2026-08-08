@verbatim
                // ── Market Intelligence Operations ────────────────────────────────────────────

                const filteredIntelijenHarga = computed(() => {
                    const rows = intelijenHargaData.value.rows || [];
                    if (!ihFilter.value) return rows;
                    if (ihFilter.value === 'mahal') return rows.filter(r => r.gap_pct !== null && r.gap_pct > 5);
                    if (ihFilter.value === 'murah') return rows.filter(r => r.gap_pct !== null && r.gap_pct < -5);
                    if (ihFilter.value === 'ok')    return rows.filter(r => r.gap_pct !== null && Math.abs(r.gap_pct) <= 5);
                    return rows;
                });

                const filteredPuraPriceChanges = computed(() => {
                    const rows = puraPriceChangesData.value.rows || [];
                    const dir = puraPriceChangesDirection.value;
                    if (dir === 'naik')  return rows.filter(r => (r.selisih || 0) > 0);
                    if (dir === 'turun') return rows.filter(r => (r.selisih || 0) < 0);
                    return rows;
                });

                const filteredAuditHarga = computed(() => auditHargaData.value.rows || []);

                const filteredEksternalChanges = computed(() => {
                    const rows = eksternalChangesData.value.rows || [];
                    const dir = eksternalChangesDirection.value;
                    if (dir === 'naik')  return rows.filter(r => (r.selisih || 0) > 0);
                    if (dir === 'turun') return rows.filter(r => (r.selisih || 0) < 0);
                    return rows;
                });

                const marketPageSize = PAGE_SIZE || 15;
                const marketSlicePage = (rows, page) => {
                    const safeRows = Array.isArray(rows) ? rows : [];
                    const safePage = Math.max(1, Number(page || 1));
                    return safeRows.slice((safePage - 1) * marketPageSize, safePage * marketPageSize);
                };
                const marketTotalPages = (rows) => Math.max(1, Math.ceil((Array.isArray(rows) ? rows.length : 0) / marketPageSize));

                const marketIntelijenHargaTotalPages = computed(() => marketTotalPages(filteredIntelijenHarga.value));
                const pagedIntelijenHarga = computed(() => marketSlicePage(filteredIntelijenHarga.value, marketIntelijenHargaPage.value));
                const marketIntelijenHargaAuditTotalPages = computed(() => marketTotalPages(filteredPuraPriceChanges.value));
                const pagedIntelijenHargaAudit = computed(() => marketSlicePage(filteredPuraPriceChanges.value, marketIntelijenHargaAuditPage.value));
                const marketAuditHargaTotalPages = computed(() => marketTotalPages(filteredAuditHarga.value));
                const pagedMarketAuditHarga = computed(() => marketSlicePage(filteredAuditHarga.value, marketAuditHargaPage.value));
                const marketEksternalRows = computed(() => Array.isArray(eksternalData.value.rows) ? eksternalData.value.rows : []);
                const marketEksternalRowsTotalPages = computed(() => marketTotalPages(marketEksternalRows.value));
                const pagedMarketEksternalRows = computed(() => marketSlicePage(marketEksternalRows.value, marketEksternalRowsPage.value));
                const marketEksternalChangesTotalPages = computed(() => marketTotalPages(filteredEksternalChanges.value));
                const pagedMarketEksternalChanges = computed(() => marketSlicePage(filteredEksternalChanges.value, marketEksternalChangesPage.value));
                const marketProductLabel = (row) => {
                    const product = String(row?.produk || row?.display_name || '').trim();
                    if (product) return product;
                    const normalizePart = (value) => String(value || '')
                        .replace(/[_|]/g, ' ')
                        .replace(/\s*[\[(]\s*([^\])]+)\s*[\])]/g, (_, tag) => {
                            const upperTag = String(tag || '').trim().toUpperCase();
                            const brandTags = new Set(['APPLE', 'IPHONE', 'IPAD', 'MACBOOK', 'AW', 'AIRPODS', 'SAMSUNG', 'GALAXY', 'XIAOMI', 'REDMI', 'POCO', 'OPPO', 'VIVO', 'REALME', 'INFINIX', 'TECNO', 'ITEL', 'NUBIA', 'HONOR', 'HUAWEI', 'NOKIA', 'GOOGLE', 'PIXEL', 'MOTOROLA']);
                            if (brandTags.has(upperTag)) return ' ';
                            if (/\b(IPAD\s*&\s*TAB|TABLET|SMARTPHONE)\b/.test(upperTag)) return ' ';
                            const cleanedTag = upperTag
                                .replace(/\b(BRAND\s+NEW|NEW|BARU|REGULER)\b/g, '')
                                .replace(/\s+/g, ' ')
                                .trim();
                            return cleanedTag ? ` ${cleanedTag} ` : ' ';
                        })
                        .replace(/[^\p{L}\p{N}\s/,+\-.&]/gu, ' ')
                        .replace(/[\[\]()]/g, ' ')
                        .replace(/\s+/g, ' ')
                        .trim()
                        .toUpperCase()
                        .replace(/\bGIFT\s+BOX\b/g, 'GIFTBOX')
                        .replace(/\b(\d+)\s*\/\s*(\d+)\s*(GB|TB|MB)\b/g, '$1$3/$2$3')
                        .replace(/\b(\d+)\s*\/\s*(\d+)(GB|TB|MB)\b/g, '$1$3/$2$3')
                        .replace(/\b(\d+)(GB|TB|MB)\s+(\d+)(GB|TB|MB)\b/g, '$1$2/$3$4')
                        .replace(/\b(\d{1,2})(PROMAX|PRO|PLUS|MAX|MINI)\b/g, (_, number, suffix) => `${number} ${suffix === 'PROMAX' ? 'PRO MAX' : suffix}`)
                        .replace(/\b(S\d{2})(ULTRA|PLUS|FE)\b/g, '$1 $2');
                    const normalizeCondition = (condition, detail) => normalizePart(`${condition || ''} ${detail || ''}`)
                        .replace(/\bNEW\b/g, 'BARU')
                        .replace(/\bREGULER\b/g, '')
                        .replace(/-/g, ' ')
                        .replace(/\s+/g, ' ')
                        .trim()
                        .split(' ')
                        .filter((token, index, tokens) => token && tokens.indexOf(token) === index)
                        .join(' ');
                    const inferCondition = (name) => {
                        const upperName = String(name || '').toUpperCase();
                        if (/\b(BRAND\s+NEW|NEW|BARU)\b/.test(upperName)) return 'BARU';
                        if (/\b(SECOND|BEKAS|SEKEN|EX)\b/.test(upperName)) return 'SECOND';
                        return '';
                    };
                    const segments = [
                        normalizePart(row?.nama),
                        normalizePart(row?.varian),
                        normalizeCondition(`${inferCondition(row?.nama)} ${row?.kondisi || ''}`, row?.kondisi_detail),
                    ].filter(Boolean).reduce((items, part) => {
                        if (!items.some(item => item === part || item.includes(part) || part.includes(item))) {
                            items.push(part);
                        }
                        return items;
                    }, []);
                    return segments.length ? segments.join(' · ') : '—';
                };

                watch([ihFilter, () => (intelijenHargaData.value.rows || []).length], () => {
                    marketIntelijenHargaPage.value = 1;
                });
                watch([puraPriceChangesDirection, () => (puraPriceChangesData.value.rows || []).length], () => {
                    marketIntelijenHargaAuditPage.value = 1;
                });
                watch([() => (auditHargaData.value.rows || []).length], () => {
                    marketAuditHargaPage.value = 1;
                });
                watch([eksternalSourceFilter, eksternalBrandFilter, () => marketEksternalRows.value.length], () => {
                    marketEksternalRowsPage.value = 1;
                });
                watch([eksternalChangesDirection, eksternalSourceFilter, () => filteredEksternalChanges.value.length], () => {
                    marketEksternalChangesPage.value = 1;
                });
                watch([marketIntelijenHargaPage, marketIntelijenHargaTotalPages], ([page, totalPages]) => {
                    if (page > totalPages) marketIntelijenHargaPage.value = totalPages;
                    if (page < 1) marketIntelijenHargaPage.value = 1;
                });
                watch([marketIntelijenHargaAuditPage, marketIntelijenHargaAuditTotalPages], ([page, totalPages]) => {
                    if (page > totalPages) marketIntelijenHargaAuditPage.value = totalPages;
                    if (page < 1) marketIntelijenHargaAuditPage.value = 1;
                });
                watch([marketAuditHargaPage, marketAuditHargaTotalPages], ([page, totalPages]) => {
                    if (page > totalPages) marketAuditHargaPage.value = totalPages;
                    if (page < 1) marketAuditHargaPage.value = 1;
                });
                watch([marketEksternalRowsPage, marketEksternalRowsTotalPages], ([page, totalPages]) => {
                    if (page > totalPages) marketEksternalRowsPage.value = totalPages;
                    if (page < 1) marketEksternalRowsPage.value = 1;
                });
                watch([marketEksternalChangesPage, marketEksternalChangesTotalPages], ([page, totalPages]) => {
                    if (page > totalPages) marketEksternalChangesPage.value = totalPages;
                    if (page < 1) marketEksternalChangesPage.value = 1;
                });

                const pasarInsight = computed(() => {
                    const indonesia = pasarData.value.market_indonesia || [];
                    const kita = pasarData.value.kita_mix || [];
                    const competitor = pasarData.value.bali_competitor || [];
                    const topKita = kita[0];
                    const topIndonesia = indonesia[0];
                    if (topKita && topIndonesia && String(topKita.brand || '').toLowerCase() !== String(topIndonesia.brand || '').toLowerCase()) {
                        return {
                            title: 'Brand terlaris toko berbeda dari market Indonesia.',
                            action: `Mix toko dipimpin ${topKita.brand}, sedangkan market Indonesia dipimpin ${topIndonesia.brand}. Cek stok dan promo agar mengikuti peluang market lokal.`,
                        };
                    }
                    if (competitor.length >= 10) {
                        return {
                            title: `${competitor.length.toLocaleString('id-ID')} brand kompetitor Bali terpantau.`,
                            action: 'Gunakan daftar kompetitor untuk prioritas cek harga SKU yang juga dijual toko.',
                        };
                    }
                    return {
                        title: kita.length ? `${kita.length.toLocaleString('id-ID')} brand sudah menghasilkan transaksi periode ini.` : 'Data market belum tersedia.',
                        action: kita.length ? 'Bandingkan brand bergerak dengan market share Indonesia sebelum menambah stok besar.' : 'Jalankan sinkronisasi data BOT agar Intelijen Pasar terisi.',
                    };
                });

                const pasarCharts = computed(() => {
                    const makeRows = (rows, labelKey, valueKey, limit = 10) => (rows || []).slice(0, limit).map(row => ({
                        label: row[labelKey] || '-',
                        value: Number(row[valueKey] || 0),
                    }));
                    const formatDecimal = (value) => Number(value || 0).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    });
                    const charts = [
                        {
                            title: 'Market Share Indonesia',
                            description: 'Top brand berdasarkan data market',
                            rows: makeRows(pasarData.value.market_indonesia, 'brand', 'pct'),
                            displayValue: (value) => formatDecimal(value),
                        },
                        {
                            title: 'Market Share Global',
                            description: 'Top brand berdasarkan data market',
                            rows: makeRows(pasarData.value.market_global, 'brand', 'pct'),
                            displayValue: (value) => formatDecimal(value),
                        },
                        {
                            title: 'Mix Penjualan Pura Pura (Brand)',
                            description: 'Kontribusi brand dari transaksi toko',
                            rows: makeRows(pasarData.value.kita_mix, 'brand', 'qty'),
                            displayValue: (value) => Number(value || 0).toLocaleString('id-ID'),
                        },
                    ];
                    return charts.map(chart => {
                        const total = chart.rows.reduce((sum, row) => sum + Number(row.value || 0), 0);
                        const rows = chart.rows.map(row => ({
                            ...row,
                            share: total ? Number(row.value || 0) / total * 100 : 0,
                        }));
                        return {
                            ...chart,
                            rows,
                            total,
                            max: Math.max(...rows.map(row => row.value), 0),
                        };
                    });
                });
                const eksternalSourceKeyFromName = (name) => {
                    const map = { 'Good Ponsel': 'goodponsel', 'Devstore': 'devstore', 'Rumah Gadget Bali': 'rumahgadget' };
                    return map[name] || name.toLowerCase().replace(/\s+/g, '');
                };

                const _handleMarketAuthError = (error) => {
                    if (error?.status === 401) {
                        clearSessionState(error?.message || 'Sesi login berakhir. Silakan login kembali.', 'warning');
                        return true;
                    }

                    return false;
                };

                const _marketFetch = (url) => jsonApi(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });

                const loadMarketPasar = () => {
                    if (tabDataLoaded.value['marketPasar']) return;
                    _marketFetch('/api/market/pasar')
                        .then(d => {
                            if (d && typeof d === 'object' && !d.message) pasarData.value = d;
                            tabDataLoaded.value = Object.assign({}, tabDataLoaded.value, { marketPasar: true });
                        })
                        .catch((error) => {
                            _handleMarketAuthError(error);
                            tabDataLoaded.value = Object.assign({}, tabDataLoaded.value, { marketPasar: true });
                        });
                };

                const loadMarketIntelijenHarga = () => {
                    if (tabDataLoaded.value['marketIntelijenHarga']) return;
                    _marketFetch('/api/market/intelijen-harga')
                        .then(d => {
                            if (d && typeof d === 'object' && !d.message) intelijenHargaData.value = d;
                            tabDataLoaded.value = Object.assign({}, tabDataLoaded.value, { marketIntelijenHarga: true });
                        })
                        .catch((error) => {
                            _handleMarketAuthError(error);
                            tabDataLoaded.value = Object.assign({}, tabDataLoaded.value, { marketIntelijenHarga: true });
                        });
                    _marketFetch('/api/market/pura-price-changes')
                        .then(d => {
                            if (d && typeof d === 'object' && !d.message) puraPriceChangesData.value = d;
                            tabDataLoaded.value = Object.assign({}, tabDataLoaded.value, { marketPuraPriceChanges: true });
                        })
                        .catch((error) => {
                            _handleMarketAuthError(error);
                            tabDataLoaded.value = Object.assign({}, tabDataLoaded.value, { marketPuraPriceChanges: true });
                        });
                };

                const loadMarketAuditHarga = () => {
                    if (tabDataLoaded.value['marketAuditHarga']) return;
                    _marketFetch('/api/market/audit-harga')
                        .then(d => {
                            if (d && typeof d === 'object' && !d.message) auditHargaData.value = d;
                            tabDataLoaded.value = Object.assign({}, tabDataLoaded.value, { marketAuditHarga: true });
                        })
                        .catch((error) => {
                            _handleMarketAuthError(error);
                            tabDataLoaded.value = Object.assign({}, tabDataLoaded.value, { marketAuditHarga: true });
                        });
                };

                const loadMarketEksternal = () => {
                    tabDataLoaded.value = Object.assign({}, tabDataLoaded.value, { marketEksternal: false });
                    const params = new URLSearchParams();
                    if (eksternalSourceFilter.value) params.set('source', eksternalSourceFilter.value);
                    if (eksternalBrandFilter.value)  params.set('brand', eksternalBrandFilter.value);
                    const url = '/api/market/eksternal' + (params.toString() ? '?' + params.toString() : '');
                    _marketFetch(url)
                        .then(d => {
                            if (d && typeof d === 'object' && !d.message) eksternalData.value = d;
                            tabDataLoaded.value = Object.assign({}, tabDataLoaded.value, { marketEksternal: true });
                        })
                        .catch((error) => {
                            _handleMarketAuthError(error);
                            tabDataLoaded.value = Object.assign({}, tabDataLoaded.value, { marketEksternal: true });
                        });
                };

                const loadMarketEksternalChanges = () => {
                    const params = new URLSearchParams({ limit: '100', direction: eksternalChangesDirection.value, acknowledged: 'all' });
                    if (eksternalSourceFilter.value) params.set('source', eksternalSourceFilter.value);
                    _marketFetch('/api/market/eksternal-changes?' + params.toString())
                        .then(d => {
                            if (d && typeof d === 'object' && !d.message) eksternalChangesData.value = d;
                        })
                        .catch((error) => {
                            _handleMarketAuthError(error);
                        });
                };

                // Auto-set source filter when navigating to per-source sub-tabs
                const _marketEksternalTabSourceMap = {
                    market_ext_goodponsel:  'goodponsel',
                    market_ext_devstore:    'devstore',
                    market_ext_rumahgadget: 'rumahgadget',
                };
@endverbatim
