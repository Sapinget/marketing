
                const createMockRunner = () => {
                    const mockState = {
                        masterPlan: [],
                        distribution: [],
                        analytics: [],
                        story: [],
                        unboxing: [],
                        orderanOnline: [],
                        unitDitanya: [],
                        claimGaransi: [],
                        keepBarang: [],
                        promo: [],
                        sellOut: [],
                        hargaKompetitor: [],
                        lpjk: [],
                        lpjkDetail: [],
                        ads: [],
                        nama: "User",
                        pin: "admin"
                    };

                    const mockApi = {
                        ensureDatabase() {
                            return { status: "ok" };
                        },
                        login(username, pin) {
                            if (username === "admin" && pin === mockState.pin) {
                                return {
                                    status: "success",
                                    user: {
                                        username: "admin",
                                        nama: mockState.nama || "User",
                                        role: "Super Admin",
                                        outlet_id: "OUTLET-01",
                                    },
                                };
                            }
                            throw new Error("Username atau PIN salah.");
                        },

                        getMasterPlanData() {
                            return mockState.masterPlan;
                        },
                        getAnalyticsData() {
                            return mockState.analytics;
                        },
                        getDistributionData() {
                            return mockState.distribution;
                        },
                        saveMasterPlan(data) {
                            if (data.ID) {
                                const idx = mockState.masterPlan.findIndex(i => i.ID === data.ID);
                                if (idx !== -1) mockState.masterPlan[idx] = data;
                            } else {
                                data.ID = mockState.masterPlan.length + 1;
                                mockState.masterPlan.push(data);
                            }
                            return { status: "success" };
                        },
                        deleteMasterPlan(id) {
                            mockState.masterPlan = mockState.masterPlan.filter(i => i.ID !== id);
                            return { status: "success" };
                        },
                        saveDistribution(data) {
                            if (data.ID) {
                                const idx = mockState.distribution.findIndex(i => i.ID === data.ID);
                                if (idx !== -1) mockState.distribution[idx] = data;
                            } else {
                                data.ID = Date.now();
                                mockState.distribution.push(data);
                            }
                            return { status: "success" };
                        },
                        deleteDistribution(id) {
                            mockState.distribution = mockState.distribution.filter(i => i.ID !== id);
                            return { status: "success" };
                        },
                        saveAnalytics(data) {
                            if (data.ID) {
                                const idx = mockState.analytics.findIndex(i => i.ID === data.ID);
                                if (idx !== -1) mockState.analytics[idx] = data;
                            } else {
                                data.ID = Date.now();
                                mockState.analytics.push(data);
                            }
                            return { status: "success" };
                        },
                        deleteAnalytics(id) {
                            mockState.analytics = mockState.analytics.filter(i => i.ID !== id);
                            return { status: "success" };
                        },
                        getStorySchedule() {
                            return mockState.story;
                        },
                        saveStory(data) {
                            if (data.ID) {
                                const idx = mockState.story.findIndex(i => i.ID === data.ID);
                                if (idx !== -1) mockState.story[idx] = data;
                            } else {
                                data.ID = Date.now();
                                mockState.story.push(data);
                            }
                            return { status: "success" };
                        },
                        deleteStory(id) {
                            mockState.story = mockState.story.filter(i => i.ID !== id);
                            return { status: "success" };
                        },
                        getSettings() {
                            return {};
                        },
                        saveSettings(data) {
                            return { status: 'success' };
                        },
                        getUnboxingData() { return mockState.unboxing; },
                        getOrderanOnlineData() { return mockState.orderanOnline; },
                        getUnitDitanyaData() { return mockState.unitDitanya; },
                        getClaimGaransiData() { return mockState.claimGaransi; },
                        getKeepBarangData() { return mockState.keepBarang; },
                        getPromoData() { return mockState.promo; },
                        getSellOutTargetData() { return mockState.sellOut; },
                        saveUnboxing(d) { return { status: 'success' }; },
                        saveOrderanOnline(d) { return { status: 'success' }; },
                        saveUnitDitanya(d) { return { status: 'success' }; },
                        saveClaimGaransi(d) { return { status: 'success' }; },
                        saveKeepBarang(d) { return { status: 'success' }; },
                        savePromo(data) {
                            if (data.ID) {
                                const idx = mockState.promo.findIndex(i => i.ID === data.ID);
                                if (idx !== -1) mockState.promo[idx] = data;
                            } else {
                                data.ID = 'PRO' + Date.now();
                                mockState.promo.push(data);
                            }
                            return { status: 'success' };
                        },
                        saveSellOutTarget(data) {
                            if (data.ID) {
                                const idx = mockState.sellOut.findIndex(i => i.ID === data.ID);
                                if (idx !== -1) mockState.sellOut[idx] = data;
                            } else {
                                data.ID = 'SOT' + Date.now();
                                mockState.sellOut.push(data);
                            }
                            return { status: 'success' };
                        },
                        deleteUnboxing(id) { return { status: 'success' }; },
                        deleteOrderanOnline(id) { return { status: 'success' }; },
                        deleteUnitDitanya(id) { return { status: 'success' }; },
                        deleteClaimGaransi(id) { return { status: 'success' }; },
                        deleteKeepBarang(id) { return { status: 'success' }; },
                        deletePromo(id) {
                            mockState.promo = mockState.promo.filter(i => i.ID !== id);
                            return { status: 'success' };
                        },
                        deleteSellOutTarget(id) {
                            mockState.sellOut = mockState.sellOut.filter(i => i.ID !== id);
                            return { status: 'success' };
                        },
                        getAllData() {
                            const stored = localStorage.getItem('ppp_bonusConfig');
                            let bc = null;
                            try { bc = stored ? JSON.parse(stored) : null; } catch (e) { }
                            const bbc = (() => { try { const s = localStorage.getItem('ppp_budgetConfig'); return s ? JSON.parse(s) : null; } catch (e) { return null; } })();
                            return {
                                settings: mockApi.getSettings(),
                                masterPlan: mockState.masterPlan,
                                analytics: mockState.analytics,
                                distribution: mockState.distribution,
                                story: mockState.story || [],
                                unboxing: mockState.unboxing,
                                orderanOnline: mockState.orderanOnline,
                                unitDitanya: mockState.unitDitanya,
                                claimGaransi: mockState.claimGaransi,
                                promo: mockState.promo,
                                sellOut: mockState.sellOut,
                                bonusConfig: bc,
                                hargaKompetitor: mockState.hargaKompetitor,
                                lpjk: mockState.lpjk,
                                lpjkDetail: mockState.lpjkDetail,
                                budgetingConfig: bbc,
                                ads: mockState.ads,
                                keepBarang: mockState.keepBarang
                            };
                        },
                        initAndGetAllData() { return this.getAllData(); },
                        getCriticalData() { return this.getAllData(); },
                        initAndGetCriticalData() { return this.getAllData(); },
                        getTabData(tabName) {
                            if (tabName === 'unboxing') return { unboxing: mockState.unboxing };
                            if (tabName === 'orderanOnline') return { orderanOnline: mockState.orderanOnline };
                            if (tabName === 'unitDitanya') return { unitDitanya: mockState.unitDitanya };
                            if (tabName === 'claimGaransi') return { claimGaransi: mockState.claimGaransi };
                            if (tabName === 'promo') return { promo: mockState.promo };
                            if (tabName === 'sellOut') return { sellOut: mockState.sellOut };
                            if (tabName === 'hargaKompetitor') return { hargaKompetitor: mockState.hargaKompetitor };
                            if (tabName === 'lpjk') return { lpjk: mockState.lpjk, lpjkDetail: mockState.lpjkDetail };
                            if (tabName === 'ads') return { ads: mockState.ads };
                            if (tabName === 'keepBarang') return { keepBarang: mockState.keepBarang };
                            return {};
                        },
                        getBonusConfig() {
                            const stored = localStorage.getItem('ppp_bonusConfig');
                            try { return stored ? JSON.parse(stored) : null; } catch (e) { return null; }
                        },
                        saveBonusConfig(cfg) {
                            localStorage.setItem('ppp_bonusConfig', JSON.stringify(cfg));
                            return { status: 'success' };
                        },
                        getBudgetingConfig() {
                            const stored = localStorage.getItem('ppp_budgetConfig');
                            try { return stored ? JSON.parse(stored) : null; } catch (e) { return null; }
                        },
                        saveBudgetingConfig(cfg) {
                            localStorage.setItem('ppp_budgetConfig', JSON.stringify(cfg));
                            return { status: 'success' };
                        },
                        saveAds(d) { return { status: 'success', data: { ...d, ID: d.ID || ('AD' + Date.now()) } }; },
                        deleteAds(id) { return { status: 'success' }; },
                        saveHargaKompetitor(d) { return { status: 'success', id: d.ID || ('HK' + Date.now()) }; },
                        deleteHargaKompetitor(id) { return { status: 'success' }; },
                        saveLpjk(d) { return { status: 'success', id: d.ID || ('LJ' + Date.now()) }; },
                        deleteLpjk(id) { return { status: 'success' }; },
                        getLpjkDetailData(lpjkId) { return mockState.lpjkDetail.filter(row => String(row.Master_ID) === String(lpjkId)); },
                        saveLpjkDetail(d) { return { status: 'success', id: 'LD' + Date.now() }; },
                        deleteLpjkDetail(id) { return { status: 'success' }; },
                        saveBudgetingConfig(cfg) {
                            localStorage.setItem('ppp_budgetConfig', JSON.stringify(cfg));
                            return { status: 'success' };
                        },
                        updateUserNama(username, nama) {
                            mockState.nama = nama;
                            return { status: 'success' };
                        },
                        changePin(username, oldPin, newPin) {
                            if (oldPin !== mockState.pin) throw new Error('PIN saat ini salah.');
                            mockState.pin = newPin;
                            return { status: 'success' };
                        },
                        getAuthUsers() {
                            return [
                                { id: 1, username: 'admin', nama: mockState.nama || 'User', email: 'admin@dashboard.local' }
                            ];
                        },
                        createAuthUser(payload) {
                            return {
                                status: 'success',
                                data: {
                                    id: Date.now(),
                                    username: payload.username,
                                    nama: payload.nama,
                                    email: payload.email || '',
                                },
                            };
                        },
                        exportToExcel(tab, data) {
                            if (!data || !data.length) { showNotification('Tidak ada data untuk diekspor'); return null; }
                            const esc = (v) => `"${String(v ?? '').replace(/"/g, '""')}"`;
                            const headers = Object.keys(data[0]).filter(k => !k.startsWith('_'));
                            const lines = [
                                headers.map(esc).join(','),
                                ...data.map(row => headers.map(h => esc(row[h])).join(','))
                            ];
                            const csv = '﻿' + lines.join('\r\n');
                            const today = new Date().toISOString().slice(0, 10);
                            const a = document.createElement('a');
                            a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
                            a.download = `Export_${tab}_${today}.csv`;
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            return null;
                        }
                    };

                    const createRunner = (successHandler, failureHandler) => {
                        const runner = {
                            withSuccessHandler(callback) {
                                return createRunner(callback, failureHandler);
                            },
                            withFailureHandler(callback) {
                                return createRunner(successHandler, callback);
                            },
                            isMock: true,
                        };

                        Object.keys(mockApi).forEach((methodName) => {
                            runner[methodName] = (...args) => {
                                try {
                                    const result = mockApi[methodName](...args);
                                    if (typeof successHandler === "function") {
                                        setTimeout(() => successHandler(result), 0);
                                    }
                                } catch (error) {
                                    if (typeof failureHandler === "function") {
                                        setTimeout(() => failureHandler(error), 0);
                                    } else {
                                        throw error;
                                    }
                                }
                                return runner;
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
                            keepBarang: Array.isArray(r.keepBarang) ? r.keepBarang : [],
                            lpjk: Array.isArray(r.lpjk) ? r.lpjk : [],
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
                        saveBonusConfig(cfg) {
                            return jsonApi('/api/bonus-config', { method: 'PUT', body: JSON.stringify(cfg) })
                                .then(r => { try { localStorage.setItem('ppp_bonusConfig', JSON.stringify(cfg)); } catch (e) { } return r; });
                        },
                        // Data fetchers
                        getStorySchedule() { return fetchJson_('/api/story-schedules').then(r => r.data || []); },
                        getUnboxingData() { return fetchJson_('/api/unboxing').then(r => r.data || []); },
                        getOrderanOnlineData() { return fetchJson_('/api/orderan-online').then(r => r.data || []); },
                        getUnitDitanyaData() { return fetchJson_('/api/unit-ditanya').then(r => r.data || []); },
                        getClaimGaransiData() { return fetchJson_('/api/claim-garansi').then(r => r.data || []); },
                        getKeepBarangData() { return fetchJson_('/api/keep-barang').then(r => r.data || []); },
                        getPromoData() { return fetchJson_('/api/program-promo').then(r => r.data || []); },
                        getSellOutTargetData() { return fetchJson_('/api/sell-out-targets').then(r => r.data || []); },
                        // Save operations
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
                        // Delete operations
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
                        // LPJK & LPJK Detail
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
                        // Analytics
                        saveAnalytics(data) {
                            const id = data.ID; const url = id ? `/api/analytics/${encodeURIComponent(id)}` : '/api/analytics';
                            return jsonApi(url, { method: id ? 'PUT' : 'POST', body: JSON.stringify(data) });
                        },
                        deleteAnalytics(id) { return jsonApi(`/api/analytics/${encodeURIComponent(id)}`, { method: 'DELETE' }); },
                        // Distribution
                        saveDistribution(data) {
                            const id = data.ID; const url = id ? `/api/distributions/${encodeURIComponent(id)}` : '/api/distributions';
                            return jsonApi(url, { method: id ? 'PUT' : 'POST', body: JSON.stringify(data) });
                        },
                        deleteDistribution(id) { return jsonApi(`/api/distributions/${encodeURIComponent(id)}`, { method: 'DELETE' }); },
                        // Settings
                        saveSettings(data) { return jsonApi('/api/settings', { method: 'PUT', body: JSON.stringify({ data }) }); },
                        // Nama Stock
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

                const ensureRunApi = () => createWebRunner();

                const deleteMasterPlan = (id) => {
                    showConfirm(
                        "Hapus Plan Konten?",
                        "Data yang dihapus tidak dapat dikembalikan. Apakah Anda yakin ingin melanjutkan?",
                        () => {
                            submitting.value = true;
                            jsonApi(`/api/master-plans/${encodeURIComponent(id)}`, { method: 'DELETE' })
                                .then(() => {
                                    masterPlanData.value = masterPlanData.value.filter((item) => item.ID !== id);
                                    showNotification("Plan berhasil dihapus");
                                })
                                .catch(() => {
                                    ensureRunApi()
                                        .withSuccessHandler((response) => {
                                            if (response.status === "success") {
                                                masterPlanData.value = masterPlanData.value.filter((item) => item.ID !== id);
                                                showNotification("Plan berhasil dihapus");
                                            } else {
                                                runtimeError.value = response.message;
                                            }
                                        })
                                        .deleteMasterPlan(id);
                                })
                                .finally(() => {
                                    submitting.value = false;
                                });
                        },
                        "danger"
                    );
                };

                const refreshDashboard = () => Promise.resolve();

                const handleLogin = () => {
                    if (!loginForm.value.username || !loginForm.value.pin) {
                        runtimeError.value = "Username dan PIN wajib diisi.";
                        return;
                    }

                    submitting.value = true;
                    runtimeError.value = null;

                    ensureRunApi()
                        .withSuccessHandler(async (result) => {
                            currentUser.value = result.user;
                            localStorage.setItem("ppp_user", JSON.stringify(result.user));
                            loginForm.value = { username: "", pin: "" };
                            if (isTeknisi.value) {
                                activeTab.value = 'claim_garansi_asuransi';
                                localStorage.setItem("ppp_active_tab", 'claim_garansi_asuransi');
                            }
                            await loadSettings();
                            await loadMasterPlanData();
                            await loadAnalyticsData();
                            await loadDistributionData();
                            await loadStoryData();
                            submitting.value = false;
                            showNotification("Login berhasil");
                        })
                        .withFailureHandler(handleError)
                        .login(loginForm.value.username, loginForm.value.pin);
                };

                const logout = () => {
                    const clearSessionState = () => {
                        localStorage.removeItem("ppp_user");
                        currentUser.value = null;
                        masterPlanData.value = [];
                        analyticsData.value = [];
                        distributionData.value = [];
                        storyData.value = [];
                        showNotification("Anda sudah logout");
                    };

                    const runner = ensureRunApi();

                    if (runner.isWebProxy) {
                        runner
                            .withSuccessHandler(() => {
                                clearSessionState();
                            })
                            .withFailureHandler((error) => {
                                clearSessionState();
                                notifyError('', error, 'Session server belum berhasil diakhiri, tetapi akses lokal sudah dibersihkan.');
                            })
                            .logout();

                        return;
                    }

                    clearSessionState();
                };

                const closeDropdownOnScroll = (e) => {
                    if (e.target && e.target.closest && e.target.closest('.search-select-container')) return;
                    searchSelectOpen.value = null;
                    clearPopoverTriggerState();
                };
                const handleResize = () => {
                    isMobileViewport.value = window.innerWidth < 768;
                    if (window.innerWidth >= 768) settingsDetailModalOpen.value = false;
                    searchSelectOpen.value = null;
                    clearPopoverTriggerState();
                };
                const handleHashChange = () => {
                    const h = window.location.hash.slice(1);
                    if (h && h !== activeTab.value) switchTab(h);
                };
                const resumeActiveTabAfterBootstrap = () => {
                    if (!currentUser.value) {
                        return;
                    }

                    if (!settingsLoaded.value && activeTab.value !== 'dashboard') {
                        loadSettings();
                    }
                    if (activeTab.value === 'settings') {
                        loadSettings();
                    }
                    if (activeTab.value === 'auth_users') {
                        loadAuthUsers();
                    }
                    if (activeTab.value === 'activity_logs') {
                        loadActivityLogs();
                    }
                };
