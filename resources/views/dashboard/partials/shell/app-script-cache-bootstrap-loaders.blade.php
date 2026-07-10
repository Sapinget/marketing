@verbatim
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
                    if (d.namaStock) { initNamaStockRows(Array.isArray(d.namaStock) ? d.namaStock : []); namaStockLoaded.value = true; }
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
                    if (d.assetVendorInventory) { aviData.value = Array.isArray(d.assetVendorInventory) ? d.assetVendorInventory : []; _tabUpdates.assetVendorInventory = true; }
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
                        }).withFailureHandler(() => {
                            const cachedCfg = (() => {
                                try {
                                    const stored = localStorage.getItem('ppp_bonusConfig');
                                    return stored ? JSON.parse(stored) : null;
                                } catch (e) {
                                    return null;
                                }
                            })();
                            bonusConfig.value = _mergeBonusConfig(cachedCfg);
                            bonusConfigLoaded.value = true;
                            showNotification(
                                cachedCfg && typeof cachedCfg === 'object'
                                    ? 'Konfigurasi bonus server tidak tersedia. Menggunakan konfigurasi lokal terakhir.'
                                    : 'Konfigurasi bonus server tidak tersedia. Menggunakan konfigurasi default.',
                                'warning'
                            );
                            resolve();
                        }).getBonusConfig();
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
                            .withFailureHandler(() => {
                                tabDataLoaded.value = Object.assign({}, tabDataLoaded.value, { [tabName]: true });
                                resolve();
                            })
                            .getTabData(tabName);
                    });
                };
@endverbatim
