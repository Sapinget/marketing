@verbatim
                const createWebRunner = () => {
                    const fetchJson_ = (url) => fetch(resolveAppUrl(url), { headers: { Accept: 'application/json' } })
                        .then((r) => r.ok ? r.json() : { data: [] })
                        .catch(() => ({ data: [] }));

                    const fetchAllData_ = async () => {
                        const r = await fetchJson_('/api/all-data');
                        return {
                            settings: r.settings || {},
                            masterPlan: Array.isArray(r.masterPlan) ? r.masterPlan : [],
                            analytics: Array.isArray(r.analytics) ? r.analytics : [],
                            distribution: Array.isArray(r.distribution) ? r.distribution : [],
                            unboxing: Array.isArray(r.unboxing) ? r.unboxing : [],
                            story: Array.isArray(r.story) ? r.story : [],
                            ideation: Array.isArray(r.ideation) ? r.ideation : [],
                            promo: Array.isArray(r.promo) ? r.promo : [],
                            sellOut: Array.isArray(r.sellOut) ? r.sellOut : [],
                            ads: Array.isArray(r.ads) ? r.ads : [],
                            hargaKompetitor: Array.isArray(r.hargaKompetitor) ? r.hargaKompetitor : [],
                            orderanOnline: Array.isArray(r.orderanOnline) ? r.orderanOnline : [],
                            unitDitanya: Array.isArray(r.unitDitanya) ? r.unitDitanya : [],
                            claimGaransi: Array.isArray(r.claimGaransi) ? r.claimGaransi : [],
                            namaStock: Array.isArray(r.namaStock) ? r.namaStock : [],
                            keepBarang: Array.isArray(r.keepBarang) ? r.keepBarang : [],
                            lpjk: Array.isArray(r.lpjk) ? r.lpjk : [],
                            assetVendorInventory: Array.isArray(r.assetVendorInventory) ? r.assetVendorInventory : [],
                            lpjkDetail: Array.isArray(r.lpjkDetail) ? r.lpjkDetail : [],
                            calendarEvents: Array.isArray(r.calendarEvents) ? r.calendarEvents : [],
                            bonusConfig: (r.bonusConfig && typeof r.bonusConfig === 'object' && !Array.isArray(r.bonusConfig)) ? r.bonusConfig : null,
                            budgetingConfig: (r.budgetingConfig && typeof r.budgetingConfig === 'object' && !Array.isArray(r.budgetingConfig)) ? r.budgetingConfig : null,
                        };
                    };

                    const fetchTabData = async () => {
                        const r = await fetchAllData_();
                        const { settings, masterPlan, analytics, distribution, ...tabFields } = r;
                        return tabFields;
                    };

                    const buildCriticalData = () => fetchAllData_();

                    const webApi = {
                        ensureDatabase() {
                            return fetchJson_('/api/auth/session').then((payload) => ({
                                status: 'ok',
                                mode: 'web-proxy',
                                user: payload && payload.authenticated ? payload.user || null : null,
                            }));
                        },
                        login(username, pin) {
                            return jsonApi('/api/auth/login', {
                                method: 'POST',
                                body: JSON.stringify({
                                    username: String(username || '').trim(),
                                    pin: String(pin || ''),
                                }),
                            });
                        },
                        logout() {
                            return jsonApi('/api/auth/logout', {
                                method: 'POST',
                                body: JSON.stringify({}),
                            });
                        },
                        getSettings() {
                            return fetch(resolveAppUrl('/api/settings'), { headers: { Accept: 'application/json' } })
                                .then((response) => response.ok ? response.json() : Promise.reject(new Error(`HTTP ${response.status}`)))
                                .then((payload) => payload.data || {});
                        },
                        getMasterPlanData() {
                            return fetchMasterPlansFromDatabase();
                        },
                        getAnalyticsData() {
                            return fetch(resolveAppUrl('/api/analytics'), { headers: { Accept: 'application/json' } })
                                .then((response) => response.ok ? response.json() : Promise.reject(new Error(`HTTP ${response.status}`)))
                                .then((payload) => Array.isArray(payload.data) ? payload.data : []);
                        },
                        getDistributionData() {
                            return fetch(resolveAppUrl('/api/distributions'), { headers: { Accept: 'application/json' } })
                                .then((response) => response.ok ? response.json() : Promise.reject(new Error(`HTTP ${response.status}`)))
                                .then((payload) => Array.isArray(payload.data) ? payload.data : []);
                        },
                        getCriticalData() {
                            return buildCriticalData();
                        },
                        initAndGetCriticalData() {
                            return buildCriticalData();
                        },
                        getAllData() {
                            return buildCriticalData();
                        },
                        initAndGetAllData() {
                            return buildCriticalData();
                        },
                        getTabData() {
                            return fetchTabData();
                        },
                        getBudgetingConfig() {
                            return fetchJson_('/api/budgeting-config').then(r => r.data || null);
                        },
                        saveBudgetingConfig(cfg) {
                            return jsonApi('/api/budgeting-config', { method: 'PUT', body: JSON.stringify(cfg) })
                                .then(r => { try { localStorage.setItem('ppp_budgetConfig', JSON.stringify(cfg)); } catch (e) { } return r; });
                        },
                        getBonusConfig() {
                            return fetchJson_('/api/bonus-config').then(r => r.data || null);
                        },
                        getAssetVendorInventoryData() { return fetchJson_('/api/asset-vendor-inventory').then(r => r.data || []); },
                        saveAvi(data) {
                            const id = data.ID;
                            const url = id ? `/api/asset-vendor-inventory/${encodeURIComponent(id)}` : '/api/asset-vendor-inventory';
                            return jsonApi(url, { method: id ? 'PUT' : 'POST', body: JSON.stringify(data) }).then(r => ({
                                status: r && r.status,
                                id: (r && r.data && r.data.source_id) || id || ('AVI' + Date.now())
                            }));
                        },
                        deleteAvi(id) { return jsonApi(`/api/asset-vendor-inventory/${encodeURIComponent(id)}`, { method: 'DELETE' }); },
                        saveBonusConfig(cfg) {
                            return jsonApi('/api/bonus-config', { method: 'PUT', body: JSON.stringify(cfg) })
                                .then(r => { try { localStorage.setItem('ppp_bonusConfig', JSON.stringify(cfg)); } catch (e) { } return r; });
                        },
                        getStorySchedule() { return fetchJson_('/api/story-schedules').then(r => r.data || []); },
                        getUnboxingData() { return fetchJson_('/api/unboxing').then(r => r.data || []); },
                        getOrderanOnlineData() { return fetchJson_('/api/orderan-online').then(r => r.data || []); },
                        getUnitDitanyaData() { return fetchJson_('/api/unit-ditanya').then(r => r.data || []); },
                        getClaimGaransiData() { return fetchJson_('/api/claim-garansi').then(r => r.data || []); },
                        getKeepBarangData() { return fetchJson_('/api/keep-barang').then(r => r.data || []); },
                        getPromoData() { return fetchJson_('/api/program-promo').then(r => r.data || []); },
                        getSellOutTargetData() { return fetchJson_('/api/sell-out-targets').then(r => r.data || []); },
                        saveStory(data) {
                            const id = data.ID; const url = id ? `/api/story-schedules/${encodeURIComponent(id)}` : '/api/story-schedules';
                            return jsonApi(url, { method: id ? 'PUT' : 'POST', body: JSON.stringify(data) });
                        },
                        saveUnboxing(data) {
                            const id = data.ID; const url = id ? `/api/unboxing/${encodeURIComponent(id)}` : '/api/unboxing';
                            return jsonApi(url, { method: id ? 'PUT' : 'POST', body: JSON.stringify(data) });
                        },
                        saveOrderanOnline(data) {
                            const id = data.ID; const url = id ? `/api/orderan-online/${encodeURIComponent(id)}` : '/api/orderan-online';
                            return jsonApi(url, { method: id ? 'PUT' : 'POST', body: JSON.stringify(data) });
                        },
                        saveUnitDitanya(data) {
                            const id = data.ID; const url = id ? `/api/unit-ditanya/${encodeURIComponent(id)}` : '/api/unit-ditanya';
                            return jsonApi(url, { method: id ? 'PUT' : 'POST', body: JSON.stringify(data) });
                        },
                        saveClaimGaransi(data) {
                            const id = data.ID; const url = id ? `/api/claim-garansi/${encodeURIComponent(id)}` : '/api/claim-garansi';
                            return jsonApi(url, { method: id ? 'PUT' : 'POST', body: JSON.stringify(data) });
                        },
                        saveKeepBarang(data) {
                            const id = data.ID; const url = id ? `/api/keep-barang/${encodeURIComponent(id)}` : '/api/keep-barang';
                            return jsonApi(url, { method: id ? 'PUT' : 'POST', body: JSON.stringify(data) });
                        },
                        savePromo(data) {
                            const id = data.ID; const url = id ? `/api/program-promo/${encodeURIComponent(id)}` : '/api/program-promo';
                            return jsonApi(url, { method: id ? 'PUT' : 'POST', body: JSON.stringify(data) });
                        },
                        saveSellOutTarget(data) {
                            const id = data.ID; const url = id ? `/api/sell-out-targets/${encodeURIComponent(id)}` : '/api/sell-out-targets';
                            return jsonApi(url, { method: id ? 'PUT' : 'POST', body: JSON.stringify(data) });
                        },
                        saveAds(data) {
                            const id = data.ID; const url = id ? `/api/ads-performance/${encodeURIComponent(id)}` : '/api/ads-performance';
                            return jsonApi(url, { method: id ? 'PUT' : 'POST', body: JSON.stringify(data) });
                        },
                        saveMasterPlan(data) {
                            const id = data.ID; const url = id ? `/api/master-plans/${encodeURIComponent(id)}` : '/api/master-plans';
                            return jsonApi(url, { method: id ? 'PUT' : 'POST', body: JSON.stringify(data) });
                        },
                        deleteStory(id) { return jsonApi(`/api/story-schedules/${encodeURIComponent(id)}`, { method: 'DELETE' }); },
                        deleteUnboxing(id) { return jsonApi(`/api/unboxing/${encodeURIComponent(id)}`, { method: 'DELETE' }); },
                        deleteOrderanOnline(id) { return jsonApi(`/api/orderan-online/${encodeURIComponent(id)}`, { method: 'DELETE' }); },
                        deleteUnitDitanya(id) { return jsonApi(`/api/unit-ditanya/${encodeURIComponent(id)}`, { method: 'DELETE' }); },
                        deleteClaimGaransi(id) { return jsonApi(`/api/claim-garansi/${encodeURIComponent(id)}`, { method: 'DELETE' }); },
                        deleteKeepBarang(id) { return jsonApi(`/api/keep-barang/${encodeURIComponent(id)}`, { method: 'DELETE' }); },
                        deletePromo(id) { return jsonApi(`/api/program-promo/${encodeURIComponent(id)}`, { method: 'DELETE' }); },
                        deleteSellOutTarget(id) { return jsonApi(`/api/sell-out-targets/${encodeURIComponent(id)}`, { method: 'DELETE' }); },
                        deleteAds(id) { return jsonApi(`/api/ads-performance/${encodeURIComponent(id)}`, { method: 'DELETE' }); },
                        deleteMasterPlan(id) { return jsonApi(`/api/master-plans/${encodeURIComponent(id)}`, { method: 'DELETE' }); },
                        getLpjkDetailData(masterId) {
                            return fetchJson_(`/api/lpjk-detail?master_id=${encodeURIComponent(masterId)}`).then(r => r.data || []);
                        },
                        saveLpjkDetail(data) {
                            const id = data.ID;
                            const url = id ? `/api/lpjk-detail/${encodeURIComponent(id)}` : '/api/lpjk-detail';
                            return jsonApi(url, { method: id ? 'PUT' : 'POST', body: JSON.stringify(data) }).then(r => ({
                                status: r && r.status,
                                id: (r && r.data && r.data.source_id) || id || ('LD' + Date.now())
                            }));
                        },
                        deleteLpjkDetail(id) { return jsonApi(`/api/lpjk-detail/${encodeURIComponent(id)}`, { method: 'DELETE' }); },
                        saveLpjk(data) {
                            const id = data.ID;
                            const url = id ? `/api/lpjk/${encodeURIComponent(id)}` : '/api/lpjk';
                            return jsonApi(url, { method: id ? 'PUT' : 'POST', body: JSON.stringify(data) }).then(r => ({
                                status: r && r.status,
                                id: (r && r.data && r.data.source_id) || id || ('LJ' + Date.now())
                            }));
                        },
                        deleteLpjk(id) { return jsonApi(`/api/lpjk/${encodeURIComponent(id)}`, { method: 'DELETE' }); },
                        saveAnalytics(data) {
                            const id = data.ID; const url = id ? `/api/analytics/${encodeURIComponent(id)}` : '/api/analytics';
                            return jsonApi(url, { method: id ? 'PUT' : 'POST', body: JSON.stringify(data) });
                        },
                        deleteAnalytics(id) { return jsonApi(`/api/analytics/${encodeURIComponent(id)}`, { method: 'DELETE' }); },
                        saveDistribution(data) {
                            const id = data.ID; const url = id ? `/api/distributions/${encodeURIComponent(id)}` : '/api/distributions';
                            return jsonApi(url, { method: id ? 'PUT' : 'POST', body: JSON.stringify(data) });
                        },
                        deleteDistribution(id) { return jsonApi(`/api/distributions/${encodeURIComponent(id)}`, { method: 'DELETE' }); },
                        saveSettings(data) { return jsonApi('/api/settings', { method: 'PUT', body: JSON.stringify({ data }) }); },
                        getNamaStockData() { return fetchJson_('/api/raw-sheets/Nama_Stock').then(r => Array.isArray(r.data) ? r.data : []); },
                        saveNamaStockRows(rows) { return jsonApi('/api/raw-sheets/Nama_Stock', { method: 'PUT', body: JSON.stringify({ data: rows }) }); },
                        getMetaStoryData() { return fetchJson_('/api/meta-posts/story').then(r => Array.isArray(r.data) ? r.data : []); },
                        getMetaFeedData() { return fetchJson_('/api/meta-posts/feed').then(r => Array.isArray(r.data) ? r.data : []); },
                        importMetaStory(rows, options = {}) { return jsonApi('/api/meta-posts/story/import', { method: 'POST', body: JSON.stringify({ rows, overwrite: !!options.overwrite }) }); },
                        importMetaFeed(rows, options = {}) { return jsonApi('/api/meta-posts/feed/import', { method: 'POST', body: JSON.stringify({ rows, overwrite: !!options.overwrite }) }); },
                        importMetaStoryFolder(options = {}) { return jsonApi('/api/meta-posts/story/import-folder', { method: 'POST', body: JSON.stringify({ overwrite: !!options.overwrite }) }); },
                        importMetaFeedFolder(options = {}) { return jsonApi('/api/meta-posts/feed/import-folder', { method: 'POST', body: JSON.stringify({ overwrite: !!options.overwrite }) }); },
                        updateUserNama(username, nama) {
                            return jsonApi('/api/auth/profile', {
                                method: 'PUT',
                                body: JSON.stringify({ nama }),
                            });
                        },
                        changePin(username, oldPin, newPin) {
                            return jsonApi('/api/auth/pin', {
                                method: 'PUT',
                                body: JSON.stringify({
                                    old_pin: oldPin,
                                    new_pin: newPin,
                                    new_pin_confirmation: newPin,
                                }),
                            });
                        },
                        getAuthUsers() {
                            return fetchJson_('/api/auth/users').then(r => Array.isArray(r.data) ? r.data : []);
                        },
                        getActivityLogs(filters = {}) {
                            const params = new URLSearchParams();
                            if (filters.table_name) params.set('table_name', filters.table_name);
                            if (filters.action) params.set('action', filters.action);
                            if (filters.record_key) params.set('record_key', filters.record_key);
                            const query = params.toString();
                            return fetchJson_(`/api/activity-logs${query ? `?${query}` : ''}`).then(r => Array.isArray(r.data) ? r.data : []);
                        },
                        createAuthUser(payload) {
                            return jsonApi('/api/auth/users', {
                                method: 'POST',
                                body: JSON.stringify(payload),
                            });
                        },
                        exportToExcel() {
                            return null;
                        },
                    };

                    const createRunner = (successHandler, failureHandler) => {
                        const runner = {
                            withSuccessHandler(callback) {
                                return createRunner(callback, failureHandler);
                            },
                            withFailureHandler(callback) {
                                return createRunner(successHandler, callback);
                            },
                            isWebProxy: true,
                        };

                        Object.keys(webApi).forEach((methodName) => {
                            runner[methodName] = (...args) => {
                                Promise.resolve()
                                    .then(() => webApi[methodName](...args))
                                    .then((result) => {
                                        if (typeof successHandler === 'function') {
                                            successHandler(result);
                                        }
                                    })
                                    .catch((error) => {
                                        if (typeof failureHandler === 'function') {
                                            failureHandler(error);
                                            return;
                                        }
                                        throw error;
                                    });

                                return proxyRunner;
                            };
                        });

                        let proxyRunner = null;
                        proxyRunner = new Proxy(runner, {
                            get(target, property, receiver) {
                                if (Reflect.has(target, property)) {
                                    return Reflect.get(target, property, receiver);
                                }

                                if (typeof property === 'string') {
                                    return () => {
                                        if (typeof failureHandler === 'function') {
                                            failureHandler(new Error(`RPC method unavailable in web proxy mode: ${property}`));
                                        }
                                        return proxyRunner;
                                    };
                                }

                                return Reflect.get(target, property, receiver);
                            },
                        });

                        return proxyRunner;
                    };

                    return createRunner();
                };
@endverbatim
