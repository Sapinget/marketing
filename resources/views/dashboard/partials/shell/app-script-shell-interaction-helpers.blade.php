@verbatim
                const persistSidebarState = () => {
                    if (isMobileViewport.value) return;
                    localStorage.setItem('sidebarCollapsed', String(!isSidebarOpen.value));
                };

                const toggleSidebar = () => {
                    isSidebarOpen.value = !isSidebarOpen.value;
                    persistSidebarState();
                };

                const closeSidebar = () => {
                    isSidebarOpen.value = false;
                };

                let horizontalPanStart = null;

                const getBoundedTableScroller = (target) => {
                    const element = target instanceof Element ? target : null;
                    const scroller = element?.closest?.('.section-card-shell .overflow-x-auto');
                    if (!(scroller instanceof HTMLElement)) return null;
                    if (!scroller.querySelector('.table-freeze-index, .table-freeze-action')) return null;
                    return scroller;
                };

                const clampTableScroller = (scroller) => {
                    if (!(scroller instanceof HTMLElement)) return;
                    const maxScrollLeft = Math.max(0, scroller.scrollWidth - scroller.clientWidth);
                    if (scroller.scrollLeft < 0) {
                        scroller.scrollLeft = 0;
                        return;
                    }
                    if (scroller.scrollLeft > maxScrollLeft) {
                        scroller.scrollLeft = maxScrollLeft;
                    }
                };

                const clampTableScrollBounds = (event) => {
                    const scroller = getBoundedTableScroller(event.target);
                    if (!scroller) return;
                    clampTableScroller(scroller);
                    requestAnimationFrame(() => clampTableScroller(scroller));
                };

                const containTableHorizontalWheel = (event) => {
                    const scroller = getBoundedTableScroller(event.target);
                    if (!scroller) return;
                    const horizontalDelta = Math.abs(event.deltaX) >= Math.abs(event.deltaY) ? event.deltaX : 0;
                    if (!horizontalDelta) return;
                    const maxScrollLeft = Math.max(0, scroller.scrollWidth - scroller.clientWidth);
                    const atStart = scroller.scrollLeft <= 0;
                    const atEnd = scroller.scrollLeft >= maxScrollLeft;
                    if ((horizontalDelta < 0 && atStart) || (horizontalDelta > 0 && atEnd)) {
                        event.preventDefault();
                        scroller.scrollLeft = horizontalDelta < 0 ? 0 : maxScrollLeft;
                    }
                };

                const clampRootHorizontalScroll = () => {
                    if (window.scrollX !== 0) {
                        window.scrollTo({ left: 0, top: window.scrollY, behavior: 'auto' });
                    }
                    if (document.documentElement && document.documentElement.scrollLeft !== 0) {
                        document.documentElement.scrollLeft = 0;
                    }
                    if (document.body && document.body.scrollLeft !== 0) {
                        document.body.scrollLeft = 0;
                    }
                };

                const containRootHorizontalWheel = (event) => {
                    if (getBoundedTableScroller(event.target)) return;
                    const horizontalDelta = Math.abs(event.deltaX) >= Math.abs(event.deltaY) ? event.deltaX : 0;
                    if (!horizontalDelta) return;
                    event.preventDefault();
                    clampRootHorizontalScroll();
                };

                const rememberHorizontalPanStart = (event) => {
                    const point = event.touches?.[0];
                    if (!point) return;
                    horizontalPanStart = {
                        x: point.clientX,
                        y: point.clientY,
                        scroller: getBoundedTableScroller(event.target),
                    };
                };

                const containHorizontalTouchPan = (event) => {
                    const point = event.touches?.[0];
                    if (!point || !horizontalPanStart) return;
                    const deltaX = point.clientX - horizontalPanStart.x;
                    const deltaY = point.clientY - horizontalPanStart.y;
                    if (Math.abs(deltaX) <= Math.abs(deltaY)) return;

                    const scroller = horizontalPanStart.scroller;
                    if (scroller) {
                        const maxScrollLeft = Math.max(0, scroller.scrollWidth - scroller.clientWidth);
                        const atStart = scroller.scrollLeft <= 0;
                        const atEnd = scroller.scrollLeft >= maxScrollLeft;
                        if ((deltaX > 0 && atStart) || (deltaX < 0 && atEnd)) {
                            event.preventDefault();
                            scroller.scrollLeft = deltaX > 0 ? 0 : maxScrollLeft;
                        }
                        return;
                    }

                    event.preventDefault();
                    clampRootHorizontalScroll();
                };

                const clearHorizontalPanStart = () => {
                    horizontalPanStart = null;
                };

                const clearPopoverTriggerState = () => {
                    document.querySelectorAll('[data-popover-open="true"]').forEach((element) => {
                        element.removeAttribute('data-popover-open');
                    });
                };

                let activePanelScrollRetryTimers = [];
                let activePanelScrollGuardInterval = null;
                let activePanelScrollGuardTimeout = null;
                let activePanelScrollUserInteracted = false;
                const getDashboardMainScroller = () => {
                    const main = document.querySelector('#app main');
                    return main instanceof HTMLElement ? main : null;
                };

                const clearActivePanelScrollGuard = () => {
                    activePanelScrollRetryTimers.forEach((timerId) => clearTimeout(timerId));
                    activePanelScrollRetryTimers = [];
                    if (activePanelScrollGuardInterval) clearInterval(activePanelScrollGuardInterval);
                    if (activePanelScrollGuardTimeout) clearTimeout(activePanelScrollGuardTimeout);
                    activePanelScrollGuardInterval = null;
                    activePanelScrollGuardTimeout = null;
                };

                const markActivePanelScrollUserIntent = () => {
                    if (!activePanelScrollRetryTimers.length && !activePanelScrollGuardInterval && !activePanelScrollGuardTimeout) {
                        return;
                    }
                    activePanelScrollUserInteracted = true;
                    clearActivePanelScrollGuard();
                };

                const scrollActivePanelToTop = () => {
                    const main = getDashboardMainScroller();
                    if (main) main.scrollTop = 0;
                    window.scrollTo({ top: 0, left: 0, behavior: 'auto' });
                    if (document.documentElement) document.documentElement.scrollTop = 0;
                    if (document.body) document.body.scrollTop = 0;

                    const activePanel = main?.querySelector(':scope > .animate-fadeIn');
                    if (activePanel instanceof HTMLElement) {
                        activePanel.scrollIntoView({ block: 'start', inline: 'nearest', behavior: 'auto' });
                        if (main) main.scrollTop = 0;
                    }
                };

                const stabilizeActivePanelPosition = () => {
                    clearActivePanelScrollGuard();
                    activePanelScrollUserInteracted = false;
                    scrollActivePanelToTop();

                    [0, 120, 280, 520, 900].forEach((delay) => {
                        const timerId = window.setTimeout(() => {
                            if (activePanelScrollUserInteracted) {
                                return;
                            }
                            const main = getDashboardMainScroller();
                            const activePanel = main?.querySelector(':scope > .animate-fadeIn');
                            if (!(activePanel instanceof HTMLElement)) {
                                return;
                            }

                            const panelTop = activePanel.getBoundingClientRect().top;
                            if (main.scrollTop > 2 || panelTop > 140 || window.scrollY > 16) {
                                scrollActivePanelToTop();
                            }
                        }, delay);
                        activePanelScrollRetryTimers.push(timerId);
                    });

                    // Keep the initial tab pinned to the top while async data and
                    // browser scroll restoration settle. Guard stops automatically.
                    activePanelScrollGuardInterval = window.setInterval(() => {
                        if (activePanelScrollUserInteracted) {
                            clearActivePanelScrollGuard();
                            return;
                        }
                        const main = getDashboardMainScroller();
                        const activePanel = main?.querySelector(':scope > .animate-fadeIn');
                        if (!(activePanel instanceof HTMLElement)) {
                            return;
                        }

                        const panelTop = activePanel.getBoundingClientRect().top;
                        if (main.scrollTop > 2 || panelTop > 140 || window.scrollY > 16) {
                            scrollActivePanelToTop();
                        }
                    }, 180);

                    activePanelScrollGuardTimeout = window.setTimeout(() => {
                        clearActivePanelScrollGuard();
                    }, 3200);
                };

                const markPopoverTriggerState = (element) => {
                    clearPopoverTriggerState();
                    if (element instanceof HTMLElement) {
                        element.setAttribute('data-popover-open', 'true');
                    }
                };

                const closeProfileMenu = (e) => {
                    const profileWrapper = document.getElementById("profile-menu-wrapper");
                    if (profileWrapper && !profileWrapper.contains(e.target)) {
                        profileMenuOpen.value = false;
                    }

                    if (!e.target.closest(".search-select-container")) {
                        searchSelectOpen.value = null;
                        clearPopoverTriggerState();
                    }
                };
                const openProfileSetting = () => {
                    profileMenuOpen.value = false;
                    activeTab.value = "profile";
                    profileForm.value.namaLengkap = currentUser.value?.nama || "";
                    localStorage.setItem("ppp_active_tab", "profile");
                };

@endverbatim
