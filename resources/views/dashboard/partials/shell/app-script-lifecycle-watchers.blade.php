@verbatim
                const hasBlockingOverlayOpen = computed(() => Boolean(
                    modalOpen.value ||
                    distModalOpen.value ||
                    analyticsModalOpen.value ||
                    storyModalOpen.value ||
                    unboxingModalOpen.value ||
                    orderanOnlineModalOpen.value ||
                    unitDitanyaModalOpen.value ||
                    claimGaransiModalOpen.value ||
                    keepBarangModalOpen.value ||
                    promoModalOpen.value ||
                    sellOutModalOpen.value ||
                    adsModalOpen.value ||
                    hargaKompetitorModalOpen.value ||
                    lpjkModalOpen.value ||
                    lpjkDetailModalOpen.value ||
                    showColabListModal.value ||
                    showNamaStockFormModal.value ||
                    settingsDetailModalOpen.value ||
                    calendarOpen.value ||
                    calendarDayModalOpen.value ||
                    confirmModal.value.open
                ));

                let activityLogFilterDebounceId = null;

                onMounted(async () => {
                    document.addEventListener("click", closeProfileMenu);
                    window.addEventListener("scroll", closeDropdownOnScroll, true);
                    window.addEventListener("resize", handleResize);
                    window.addEventListener("hashchange", handleHashChange);
                    tableSortObserver = new MutationObserver(() => {
                        hydrateSortableTableHeaders();
                    });
                    tableSortObserver.observe(document.getElementById('app'), { childList: true, subtree: true });
                    try {
                        const bootstrap = await new Promise((resolve, reject) => {
                            ensureRunApi().withSuccessHandler(resolve).withFailureHandler(reject).ensureDatabase();
                        });

                        if (bootstrap && bootstrap.user) {
                            currentUser.value = bootstrap.user;
                            localStorage.setItem("ppp_user", JSON.stringify(bootstrap.user));
                        } else if (ensureRunApi().isWebProxy) {
                            localStorage.removeItem("ppp_user");
                            currentUser.value = null;
                        }

                        if (currentUser.value) {
                            // Single RPC: ensureDatabase + getAllData in one server call.
                            // Cache-first: cached data displays instantly, fresh data replaces it.
                            await loadAllData(true);
                        }
                    } catch (error) {
                        handleError(error);
                    } finally {
                        authBootstrapPending.value = false;
                        resumeActiveTabAfterBootstrap();
                        if (window.innerWidth < 768) {
                            isSidebarOpen.value = false;
                        }
                        appLoading.value = false;
                        nextTick(() => {
                            hydrateSortableTableHeaders();
                            requestAnimationFrame(() => stabilizeActivePanelPosition());
                        });
                    }
                });

                onBeforeUnmount(() => {
                    document.removeEventListener("click", closeProfileMenu);
                    window.removeEventListener("scroll", closeDropdownOnScroll, true);
                    window.removeEventListener("resize", handleResize);
                    window.removeEventListener("hashchange", handleHashChange);
                    tableSortObserver?.disconnect();
                    if (activityLogFilterDebounceId) {
                        window.clearTimeout(activityLogFilterDebounceId);
                    }
                    setDocumentScrollLock(false);
                });

                // --- Watchers (Moved to end to ensure all functions/refs are initialized) ---

                // Technician Guard
                watch([currentUser, activeTab], ([user, tab]) => {
                    if (isTeknisi.value && !TEKNISI_TABS.has(tab)) {
                        activeTab.value = 'claim_garansi_asuransi';
                        localStorage.setItem("ppp_active_tab", 'claim_garansi_asuransi');
                    }
                }, { immediate: true });

                watch(hasBlockingOverlayOpen, (locked) => {
                    setDocumentScrollLock(locked);
                }, { immediate: true });

                // Tab Navigation & Data Loading
                watch(() => activeTab.value, (newTab) => {
                    if (newTab === 'auth_users' && currentUser.value && !canManageUsers.value) {
                        activeTab.value = 'settings';
                        localStorage.setItem("ppp_active_tab", 'settings');
                        history.replaceState(null, '', '#settings');
                        showNotification("Akses manajemen user hanya untuk Super Admin", "warning");
                        return;
                    }

                    const menuGroup = groupForTab(newTab);
                    if (menuGroup) {
                        openMenuGroup(menuGroup);
                    } else {
                        closeAllMenuGroups();
                    }

                    // Mobile auto-close
                    if (window.innerWidth < 768) isSidebarOpen.value = false;

                    if (authBootstrapPending.value) {
                        nextTick(() => {
                            hydrateSortableTableHeaders();
                            requestAnimationFrame(() => stabilizeActivePanelPosition());
                        });
                        return;
                    }

                    if (!currentUser.value) {
                        nextTick(() => {
                            hydrateSortableTableHeaders();
                            requestAnimationFrame(() => stabilizeActivePanelPosition());
                        });
                        return;
                    }
                    runActiveTabProtectedLoaders(newTab);
                    nextTick(() => {
                        hydrateSortableTableHeaders();
                        requestAnimationFrame(() => stabilizeActivePanelPosition());
                    });
                }, { immediate: true });

                watch(() => [
                    activeTab.value,
                    activityLogFilters.value.table_name,
                    activityLogFilters.value.action,
                    activityLogFilters.value.record_key,
                ], ([tab]) => {
                    if (tab !== 'activity_logs' || authBootstrapPending.value || !currentUser.value) {
                        return;
                    }
                    activityLogPage.value = 1;
                    if (activityLogFilterDebounceId) {
                        window.clearTimeout(activityLogFilterDebounceId);
                    }
                    activityLogFilterDebounceId = window.setTimeout(() => {
                        loadActivityLogs();
                    }, 250);
                });

                watch([() => activityLogs.value.length, activityLogTotalPages], ([rowCount, totalPages]) => {
                    if (!rowCount) {
                        activityLogPage.value = 1;
                        return;
                    }
                    if (activityLogPage.value > totalPages) activityLogPage.value = totalPages;
                    if (activityLogPage.value < 1) activityLogPage.value = 1;
                });
@endverbatim
