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
@endverbatim
