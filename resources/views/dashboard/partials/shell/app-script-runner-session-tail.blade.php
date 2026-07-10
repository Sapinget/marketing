@verbatim
                const ensureRunApi = () => createWebRunner();
                const _setRunnerFactory = window.MarketingDashboardRuntimeHelpers?.setRunnerFactory;
                if (typeof _setRunnerFactory === 'function') {
                    _setRunnerFactory(() => createWebRunner());
                }

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
                const runActiveTabProtectedLoaders = (tab) => {
                    if (!currentUser.value) {
                        return;
                    }

                    if (tab === 'nama_stock' && !namaStockLoaded.value) {
                        loadNamaStockData();
                    }
                    if (tab === 'meta_story' && !metaStoryLoaded.value) {
                        loadMetaStory();
                    }
                    if (tab === 'meta_feed' && !metaFeedLoaded.value) {
                        loadMetaFeed();
                    }
                    if (tab === 'budgeting' && !budgetConfigLoaded.value) {
                        loadBudgetingConfig();
                    }
                    if (['bonus_report', 'talent_bonus', 'editor_performance'].includes(tab)) {
                        if (!bonusConfigLoaded.value) {
                            loadBonusConfig();
                        }
                        refreshBonusSourceData();
                    }
                    if (tab === 'keep_barang' && !keepBarangLoaded.value) {
                        keepBarangLoaded.value = true;
                        loadKeepBarangData();
                    }
                    if (!settingsLoaded.value && tab !== 'dashboard') {
                        loadSettings();
                    }
                    if (tab === 'settings') {
                        loadSettings();
                    }
                    if (tab === 'auth_users') {
                        loadAuthUsers();
                    }
                    if (tab === 'master' || tab === 'ideation' || tab === 'top_content_platform' || tab === 'low_content_platform') {
                        loadMasterPlanData();
                    }
                    if (tab === 'story' || tab === 'calendar') {
                        loadStoryData();
                    }
                    if (tab === 'asset_vendor_inventory') {
                        if (!window._loadAssetVendorInventory) {
                            window._loadAssetVendorInventory = () => {
                                ensureRunApi().withSuccessHandler(d => { aviData.value = Array.isArray(d) ? d : []; }).withFailureHandler(() => {}).getAssetVendorInventoryData();
                            };
                        }
                        window._loadAssetVendorInventory();
                    }
                    if (tab === 'activity_logs') {
                        loadActivityLogs();
                    }
                    if (tab === 'distribution') {
                        loadDistributionData();
                    }
                    if (tab === 'analytics') {
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
                        'harga_kompetitor': 'hargaKompetitor',
                        'asset_vendor_inventory': 'assetVendorInventory',
                        'calendar': 'calendar'
                    };
                    const dataKey = TAB_DATA_MAP[tab];
                    if (dataKey) loadTabData(dataKey);
                };
                const resumeActiveTabAfterBootstrap = () => {
                    if (!currentUser.value) {
                        return;
                    }

                    runActiveTabProtectedLoaders(activeTab.value);
                };
@endverbatim
