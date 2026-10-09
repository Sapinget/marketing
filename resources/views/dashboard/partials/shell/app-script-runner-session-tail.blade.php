@php($sessionIdleTimeoutMinutes = \App\Support\DashboardAuth::sessionIdleTimeoutMinutes())
@verbatim
                const ensureRunApi = () => createWebRunner();
@endverbatim
                const SESSION_IDLE_TIMEOUT_MINUTES = {{ $sessionIdleTimeoutMinutes }};
@verbatim
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
                const SESSION_IDLE_TIMEOUT_MS = SESSION_IDLE_TIMEOUT_MINUTES * 60 * 1000;
                const SESSION_HEARTBEAT_MS = 60 * 1000;
                let lastClientActivityAt = Date.now();
                let sessionHeartbeatTimerId = null;

                const clearSessionState = (message = "Anda sudah logout", type = "success") => {
                    localStorage.removeItem("ppp_user");
                    currentUser.value = null;
                    chatSessionConfirmed.value = false;
                    masterPlanData.value = [];
                    analyticsData.value = [];
                    distributionData.value = [];
                    storyData.value = [];
                    if (message) showNotification(message, type);
                };

                const markClientActivity = () => {
                    lastClientActivityAt = Date.now();
                };

                const verifyCurrentSession = (message = "") => {
                    if (!currentUser.value || !ensureRunApi().isWebProxy) {
                        return Promise.resolve(false);
                    }

                    return new Promise((resolve) => {
                        ensureRunApi()
                            .withSuccessHandler((result) => {
                                if (result?.user) {
                                    currentUser.value = result.user;
                                    chatSessionConfirmed.value = true;
                                    localStorage.setItem("ppp_user", JSON.stringify(result.user));
                                    resolve(true);
                                    return;
                                }

                                clearSessionState(message, "warning");
                                resolve(false);
                            })
                            .withFailureHandler((error) => {
                                if (error?.status === 401 || error?.expired) {
                                    clearSessionState(message || "Sesi login berakhir. Silakan login kembali.", "warning");
                                    resolve(false);
                                    return;
                                }

                                notifyError('', error, 'Gagal memverifikasi session login.');
                                resolve(false);
                            })
                            .ensureDatabase();
                    });
                };

                const handleBrowserPageShow = (event) => {
                    if (event?.persisted && ensureRunApi().isWebProxy && !localStorage.getItem("ppp_user")) {
                        clearSessionState("", "warning");
                        appLoading.value = false;
                        return;
                    }
                    if (!event?.persisted && !currentUser.value) {
                        return;
                    }

                    appLoading.value = true;
                    verifyCurrentSession("Sesi login sudah berakhir. Silakan login kembali.")
                        .finally(() => {
                            appLoading.value = false;
                        });
                };

                const handleBrowserFocus = () => {
                    verifyCurrentSession();
                };

                const syncSessionHeartbeat = () => {
                    if (!currentUser.value || !ensureRunApi().isWebProxy) {
                        return;
                    }

                    const idleFor = Date.now() - lastClientActivityAt;
                    const runner = ensureRunApi();

                    if (idleFor >= SESSION_IDLE_TIMEOUT_MS) {
                        runner
                            .withSuccessHandler(() => {
                                clearSessionState(`Sesi login berakhir karena tidak ada aktivitas selama ${SESSION_IDLE_TIMEOUT_MINUTES} menit.`, "warning");
                            })
                            .withFailureHandler(() => {
                                clearSessionState(`Sesi login berakhir karena tidak ada aktivitas selama ${SESSION_IDLE_TIMEOUT_MINUTES} menit.`, "warning");
                            })
                            .logout();
                        return;
                    }

                    runner
                        .withSuccessHandler((result) => {
                            if (result?.user) {
                                currentUser.value = result.user;
                                chatSessionConfirmed.value = true;
                                localStorage.setItem("ppp_user", JSON.stringify(result.user));
                            }
                        })
                        .withFailureHandler((error) => {
                            if (error?.status === 401 || error?.expired) {
                                clearSessionState(`Sesi login berakhir karena tidak ada aktivitas selama ${SESSION_IDLE_TIMEOUT_MINUTES} menit.`, "warning");
                                return;
                            }
                            notifyError('', error, 'Gagal memperbarui status online.');
                        })
                        .heartbeat();
                };

                const handleLogin = () => {
                    if (!loginForm.value.username || !loginForm.value.pin) {
                        runtimeError.value = "Username dan Password wajib diisi.";
                        return;
                    }

                    submitting.value = true;
                    runtimeError.value = null;

                    ensureRunApi()
                        .withSuccessHandler(async (result) => {
                            currentUser.value = result.user;
                            chatSessionConfirmed.value = true;
                            localStorage.setItem("ppp_user", JSON.stringify(result.user));
                            markClientActivity();
                            loginForm.value = { username: "", pin: "" };
                            if (canManageSettings.value) {
                                await loadSettings();
                            }
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
                    isMobileViewport.value = window.innerWidth < 1024;
                    if (window.innerWidth >= 1024) settingsDetailModalOpen.value = false;
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
                    if (tab === 'input_claim' && !inputClaimLoaded.value) {
                        loadInputClaimData();
                    }
                    if (tab === 'garansi_cermati' && !garansiCermatiLoaded.value) {
                        loadGaransiCermatiData();
                    }
                    if (tab === 'garansi_resmi' && !garansiResmiLoaded.value) {
                        loadGaransiResmiData();
                    }
                    if (tab === 'meta_story' && !metaStoryLoaded.value) {
                        loadMetaStory();
                    }
                    if (tab === 'meta_feed' && !metaFeedLoaded.value) {
                        loadMetaFeed();
                    }
                    if (tab === 'meta_followers' && !metaFollowersLoaded.value) {
                        loadMetaFollowers();
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
                        if (canManageSettings.value) {
                            loadSettings();
                        }
                    }
                    if (tab === 'settings') {
                        if (canManageSettings.value) {
                            loadSettings();
                        }
                    }
                    if (tab === 'auth_users' && canManageUsers.value) {
                        loadAuthUsers();
                    }
                    if (['master', 'ideation', 'top_content_platform', 'low_content_platform', 'editor_performance', 'talent_bonus', 'bonus_report', 'unboxing', 'budgeting'].includes(tab) && canManageUsers.value && !authUsersLoaded.value) {
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
                    if (tab === 'pricelist_katalog') {
                        loadPricelistCatalogData();
                    }
                    if (tab === 'template_background') {
                        if (!catalogTemplatesLoaded.value) loadCatalogTemplates();
                    }
                    if (tab === 'apple_katalog') {
                        loadAppleData();
                    }
                    if (tab === 'img_repo') {
                        imgRepoBrowse(imgRepoPath.value || '');
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
                        'service': 'service',
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
                    if (tab === 'service') {
                        loadServiceData().then(() => {
                            tabDataLoaded.value = Object.assign({}, tabDataLoaded.value, { service: true });
                        });
                    } else if (dataKey) loadTabData(dataKey);

                    // Market Intelligence — direct fetch to Laravel API (not Apps Script)
                    if (tab === 'market_pasar') loadMarketPasar();
                    if (tab === 'market_intelijen_harga') { loadMarketIntelijenHarga(); loadMarketAuditHarga(); }
                    if (tab === 'market_audit_harga') loadMarketAuditHarga();
                    if (['market_eksternal', 'market_ext_goodponsel', 'market_ext_devstore', 'market_ext_rumahgadget'].includes(tab)) {
                        const srcKey = _marketEksternalTabSourceMap[tab] || '';
                        eksternalSourceFilter.value = srcKey;
                        eksternalChangesDirection.value = 'all';
                        loadMarketEksternal();
                        loadMarketEksternalChanges();
                    }
                };
                const resumeActiveTabAfterBootstrap = () => {
                    if (!currentUser.value) {
                        return;
                    }

                    runActiveTabProtectedLoaders(activeTab.value);
                    // Satu kunjungan per muat halaman (buka URL langsung / refresh). Klik sidebar dicatat di switchTab.
                    trackMenuVisit(activeTab.value);
                };
@endverbatim
