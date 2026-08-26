@verbatim
                const submitting = ref(false);
                const submittingInfo = ref(false);
                const submittingPin = ref(false);
                const runtimeError = ref(null);
                const notification = ref({ open: false, message: '', type: 'success', icon: 'fa-circle-check' });
                const loadStoredUser = () => {
                    try {
                        const user = JSON.parse(localStorage.getItem("ppp_user") || "null");
                        if (!user || typeof user !== "object") {
                            return null;
                        }
                        if (typeof user.username !== "string" || !user.username.trim()) {
                            localStorage.removeItem("ppp_user");
                            return null;
                        }
                        if (user?.nama === ["Admin", "istrator"].join("")) {
                            user.nama = user.username || "User";
                            localStorage.setItem("ppp_user", JSON.stringify(user));
                        }

                        return user;
                    } catch (error) {
                        localStorage.removeItem("ppp_user");

                        return null;
                    }
                };
                const currentUser = ref(loadStoredUser());
                const isTeknisi = computed(() => {
                    const role = currentUser.value?.role || '';
                    return role.trim().toLowerCase() === 'teknisi';
                });
                const canManageUsers = computed(() => {
                    const roleKey = String(currentUser.value?.role_key || '').trim().toLowerCase();
                    return roleKey === 'super_admin';
                });
                const canManageSettings = computed(() => {
                    const roleKey = String(currentUser.value?.role_key || '').trim().toLowerCase();
                    return roleKey === 'super_admin' || roleKey === 'admin';
                });
                const hasPermission = (tab, action) => {
                    if (isTeknisi.value && tab !== 'claim_garansi_asuransi') return false;
                    const permissions = currentUser.value?.permissions;
                    if (!permissions || Object.keys(permissions).length === 0) return !isTeknisi.value;
                    const rule = permissions[tab];
                    if (Array.isArray(rule)) return rule.includes(action);
                    if (rule && typeof rule === 'object') return rule[action] === true;
                    return false;
                };
                const authBootstrapPending = ref(true);
                const TEKNISI_TABS = new Set(['claim_garansi_asuransi', 'profile']);
                const loginForm = ref({ username: "", pin: "" });
                const showPin = ref(false);
                const rememberUsername = ref(false);
                const loadRememberedUsername = () => {
                    try {
                        const remembered = localStorage.getItem('ppp_login_remember_username');
                        if (remembered && typeof remembered === 'string') {
                            loginForm.value.username = remembered;
                            rememberUsername.value = true;
                        }
                    } catch (error) {}
                };
                const handleRememberUsernameChange = () => {
                    try {
                        if (rememberUsername.value && loginForm.value.username) {
                            localStorage.setItem('ppp_login_remember_username', loginForm.value.username);
                        } else {
                            localStorage.removeItem('ppp_login_remember_username');
                        }
                    } catch (error) {}
                };
                loadRememberedUsername();
                const dashboardTodayLabel = new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(new Date());
                const dashboardGreeting = computed(() => {
                    const hour = new Date().getHours();
                    const waktu = hour < 11 ? 'pagi' : hour < 15 ? 'siang' : hour < 19 ? 'sore' : 'malam';
                    const nama = String(currentUser.value?.nama || currentUser.value?.username || '').trim() || 'Tim';
                    return `Selamat ${waktu}, ${nama}`;
                });
                const publishedPlanCount = computed(() => (masterPlanData.value || []).filter(i => i.Status === 'PUBLISHED').length);
@endverbatim
