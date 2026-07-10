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
