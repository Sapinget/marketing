@verbatim
                const toggleSidebar = () => {
                    isSidebarOpen.value = !isSidebarOpen.value;
                };

                const closeSidebar = () => {
                    isSidebarOpen.value = false;
                };

                const clearPopoverTriggerState = () => {
                    document.querySelectorAll('[data-popover-open="true"]').forEach((element) => {
                        element.removeAttribute('data-popover-open');
                    });
                };

                let activePanelScrollRetryTimers = [];
                let activePanelScrollGuardInterval = null;
                let activePanelScrollGuardTimeout = null;
                const scrollActivePanelToTop = () => {
                    window.scrollTo({ top: 0, left: 0, behavior: 'auto' });
                    if (document.documentElement) document.documentElement.scrollTop = 0;
                    if (document.body) document.body.scrollTop = 0;

                    const main = document.querySelector('#app main');
                    const activePanel = main?.querySelector(':scope > .animate-fadeIn');
                    if (activePanel instanceof HTMLElement) {
                        activePanel.scrollIntoView({ block: 'start', inline: 'nearest', behavior: 'auto' });
                    }
                };

                const stabilizeActivePanelPosition = () => {
                    activePanelScrollRetryTimers.forEach((timerId) => clearTimeout(timerId));
                    activePanelScrollRetryTimers = [];
                    if (activePanelScrollGuardInterval) clearInterval(activePanelScrollGuardInterval);
                    if (activePanelScrollGuardTimeout) clearTimeout(activePanelScrollGuardTimeout);
                    activePanelScrollGuardInterval = null;
                    activePanelScrollGuardTimeout = null;

                    [0, 120, 280, 520, 900].forEach((delay) => {
                        const timerId = window.setTimeout(() => {
                            const main = document.querySelector('#app main');
                            const activePanel = main?.querySelector(':scope > .animate-fadeIn');
                            if (!(activePanel instanceof HTMLElement)) {
                                return;
                            }

                            const panelTop = activePanel.getBoundingClientRect().top;
                            if (panelTop > 140 || window.scrollY > 16) {
                                scrollActivePanelToTop();
                            }
                        }, delay);
                        activePanelScrollRetryTimers.push(timerId);
                    });

                    // Keep the initial tab pinned to the top while async data and
                    // browser scroll restoration settle. Guard stops automatically.
                    activePanelScrollGuardInterval = window.setInterval(() => {
                        const main = document.querySelector('#app main');
                        const activePanel = main?.querySelector(':scope > .animate-fadeIn');
                        if (!(activePanel instanceof HTMLElement)) {
                            return;
                        }

                        const panelTop = activePanel.getBoundingClientRect().top;
                        if (panelTop > 140 || window.scrollY > 16) {
                            scrollActivePanelToTop();
                        }
                    }, 180);

                    activePanelScrollGuardTimeout = window.setTimeout(() => {
                        if (activePanelScrollGuardInterval) clearInterval(activePanelScrollGuardInterval);
                        activePanelScrollGuardInterval = null;
                        activePanelScrollGuardTimeout = null;
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
                    loadAuthUsers();
                    localStorage.setItem("ppp_active_tab", "profile");
                };

@endverbatim
