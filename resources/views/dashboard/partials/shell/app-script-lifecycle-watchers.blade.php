@verbatim
                const hasBlockingOverlayOpen = computed(() => Boolean(
                    modalOpen.value ||
                    distModalOpen.value ||
                    analyticsModalOpen.value ||
                    metaFeedManualModalOpen.value ||
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
                    calendarDayModalOpen.value ||
                    chatOpen.value ||
                    chatShowPicker.value ||
                    confirmModal.value.open
                ));

                const scrollToActiveSidebarItem = () => {
                    nextTick(() => {
                        requestAnimationFrame(() => {
                            const nav = document.querySelector('.dashboard-sidebar-nav');
                            const activeItem = nav?.querySelector('.sidebar-nav-item-active');
                            if (!nav || !activeItem) return;

                            const targetTop = activeItem.offsetTop - (nav.clientHeight / 2) + (activeItem.clientHeight / 2);
                            nav.scrollTo({ top: Math.max(0, targetTop), behavior: 'smooth' });
                        });
                    });
                };
                let activityLogFilterDebounceId = null;
                let handleChatVisibilityChange = null;

                onMounted(async () => {
                    document.addEventListener("click", closeProfileMenu);
                    document.addEventListener("click", closeCalendarOnOutsideClick);
                    window.addEventListener("scroll", closeDropdownOnScroll, true);
                    window.addEventListener("scroll", updateCalendarAnchorPosition, true);
                    window.addEventListener("scroll", clampRootHorizontalScroll, true);
                    window.addEventListener("scroll", clampTableScrollBounds, true);
                    window.addEventListener("wheel", containTableHorizontalWheel, { passive: false, capture: true });
                    window.addEventListener("wheel", containRootHorizontalWheel, { passive: false, capture: true });
                    window.addEventListener("wheel", markActivePanelScrollUserIntent, { passive: true, capture: true });
                    window.addEventListener("touchstart", rememberHorizontalPanStart, { passive: true, capture: true });
                    window.addEventListener("touchmove", containHorizontalTouchPan, { passive: false, capture: true });
                    window.addEventListener("touchmove", markActivePanelScrollUserIntent, { passive: true, capture: true });
                    window.addEventListener("touchend", clearHorizontalPanStart, true);
                    window.addEventListener("touchcancel", clearHorizontalPanStart, true);
                    window.addEventListener("click", markClientActivity, true);
                    window.addEventListener("keydown", markClientActivity, true);
                    window.addEventListener("keydown", markActivePanelScrollUserIntent, true);
                    window.addEventListener("pointermove", markClientActivity, true);
                    window.addEventListener("touchstart", markClientActivity, true);
                    window.addEventListener("resize", handleResize);
                    window.addEventListener("resize", updateCalendarAnchorPosition);
                    window.addEventListener("hashchange", handleHashChange);
                    window.addEventListener("pageshow", handleBrowserPageShow);
                    window.addEventListener("focus", handleBrowserFocus, true);
                    window.addEventListener("pointermove", catalogLayoutDragMove, true);
                    window.addEventListener("pointerup", catalogLayoutDragEnd, true);
                    handleChatVisibilityChange = () => {
                        if (document.hidden) {
                            chatStopPolling();
                            return;
                        }
                        if (!authBootstrapPending.value && currentUser.value?.ID) {
                            chatPollUnread();
                            if (chatOpen.value && chatTargetUser.value?.ID) {
                                chatStartPolling();
                            }
                        }
                    };
                    document.addEventListener("visibilitychange", handleChatVisibilityChange);
                    sessionHeartbeatTimerId = window.setInterval(syncSessionHeartbeat, SESSION_HEARTBEAT_MS);
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
                        chatSessionConfirmed.value = !!currentUser.value?.ID;
                        resumeActiveTabAfterBootstrap();
                        if (window.innerWidth < 1024) {
                            sidebarOpen.value = false;
                        }
                        appLoading.value = false;
                        nextTick(() => {
                            hydrateSortableTableHeaders();
                            scrollToActiveSidebarItem();
                            requestAnimationFrame(() => stabilizeActivePanelPosition());
                        });
                    }
                });

                onBeforeUnmount(() => {
                    document.removeEventListener("click", closeProfileMenu);
                    document.removeEventListener("click", closeCalendarOnOutsideClick);
                    window.removeEventListener("scroll", closeDropdownOnScroll, true);
                    window.removeEventListener("scroll", updateCalendarAnchorPosition, true);
                    window.removeEventListener("scroll", clampRootHorizontalScroll, true);
                    window.removeEventListener("scroll", clampTableScrollBounds, true);
                    window.removeEventListener("wheel", containTableHorizontalWheel, true);
                    window.removeEventListener("wheel", containRootHorizontalWheel, true);
                    window.removeEventListener("wheel", markActivePanelScrollUserIntent, true);
                    window.removeEventListener("touchstart", rememberHorizontalPanStart, true);
                    window.removeEventListener("touchmove", containHorizontalTouchPan, true);
                    window.removeEventListener("touchmove", markActivePanelScrollUserIntent, true);
                    window.removeEventListener("touchend", clearHorizontalPanStart, true);
                    window.removeEventListener("touchcancel", clearHorizontalPanStart, true);
                    window.removeEventListener("click", markClientActivity, true);
                    window.removeEventListener("keydown", markClientActivity, true);
                    window.removeEventListener("keydown", markActivePanelScrollUserIntent, true);
                    window.removeEventListener("pointermove", markClientActivity, true);
                    window.removeEventListener("touchstart", markClientActivity, true);
                    window.removeEventListener("resize", handleResize);
                    window.removeEventListener("resize", updateCalendarAnchorPosition);
                    window.removeEventListener("hashchange", handleHashChange);
                    window.removeEventListener("pageshow", handleBrowserPageShow);
                    window.removeEventListener("focus", handleBrowserFocus, true);
                    window.removeEventListener("pointermove", catalogLayoutDragMove, true);
                    window.removeEventListener("pointerup", catalogLayoutDragEnd, true);
                    document.removeEventListener("visibilitychange", handleChatVisibilityChange);
                    tableSortObserver?.disconnect();
                    if (sessionHeartbeatTimerId) {
                        window.clearInterval(sessionHeartbeatTimerId);
                    }
                    if (activityLogFilterDebounceId) {
                        window.clearTimeout(activityLogFilterDebounceId);
                    }
                    chatStopPolling();
                    setDocumentScrollLock(false);
                });

                // --- Watchers (Moved to end to ensure all functions/refs are initialized) ---

                watch(hasBlockingOverlayOpen, (locked) => {
                    setDocumentScrollLock(locked);
                }, { immediate: true });

                watch(calendarOpen, (open) => {
                    if (open) return;
                    clearCalendarAnchorActive();
                    calendarAnchorElement = null;
                });

                // Tab Navigation & Data Loading
                watch(() => activeTab.value, (newTab) => {
                    if (newTab === 'auth_users' && currentUser.value && !canManageUsers.value) {
                        activeTab.value = 'settings';
                        localStorage.setItem("ppp_active_tab", 'settings');
                        history.replaceState(null, '', '#settings');
                        showNotification("Akses manajemen user hanya untuk Super Admin", "warning");
                        return;
                    }

                    // Mobile auto-close
                    if (window.innerWidth < 1024) sidebarOpen.value = false;

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
                    if (newTab === 'promo_pamflet') {
                        promoPamflet.fetchData();
                    }
                    runActiveTabProtectedLoaders(newTab);
                        nextTick(() => {
                            hydrateSortableTableHeaders();
                            scrollToActiveSidebarItem();
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
