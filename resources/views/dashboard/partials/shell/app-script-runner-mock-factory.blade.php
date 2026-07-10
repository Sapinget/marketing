@verbatim
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
                        calendarEvents: [],
                        assetVendorInventory: [],
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
                                keepBarang: mockState.keepBarang,
                                calendarEvents: mockState.calendarEvents,
                                assetVendorInventory: mockState.assetVendorInventory
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
                            if (tabName === 'assetVendorInventory') return { assetVendorInventory: mockState.assetVendorInventory };
                            if (tabName === 'calendar') return { calendarEvents: mockState.calendarEvents };
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
                        getAssetVendorInventoryData() { return mockState.assetVendorInventory; },
                        saveAvi(d) { return { status: 'success', id: d.ID || ('AVI' + Date.now()) }; },
                        deleteAvi(id) { return { status: 'success' }; },
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
@endverbatim
