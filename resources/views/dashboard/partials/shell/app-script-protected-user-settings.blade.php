@verbatim
                if (!window.MarketingDashboardRuntimeHelpers || typeof window.MarketingDashboardRuntimeHelpers.formatNumber !== 'function' || typeof window.MarketingDashboardRuntimeHelpers.formatWaNumber !== 'function' || typeof window.MarketingDashboardRuntimeHelpers.calcAdminPct !== 'function') {
                    window.MarketingDashboardRuntimeHelpers = {
                        ...(window.MarketingDashboardRuntimeHelpers || {}),
                        formatNumber: (value) => new Intl.NumberFormat("id-ID").format(Number(value || 0)),
                        formatCurrency: (value) => {
                            const n = Number(value || 0);
                            if (isNaN(n)) return '-';
                            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n);
                        },
                        formatWaNumber: (phone) => {
                            let s = String(phone || '');
                            if (s.startsWith('0')) s = s.slice(1);
                            return s.replace(/[^0-9]/g, '');
                        },
                        calcAdminPct: (row) => {
                            if (row['ADMIN %'] || row.ADMIN_PCT) return row['ADMIN %'] || row.ADMIN_PCT;
                            const h = Number(row['HARGA ONLINE'] || row.HARGA_ONLINE || 0);
                            const c = Number(row['NOMINAL CAIR'] || row.NOMINAL_CAIR || 0);
                            if (h > 0 && c >= 0) return ((h - c) / h * 100).toFixed(1) + '%';
                            return '-';
                        },
                        formatShortDate: (dateStr) => {
                            if (!dateStr) return "-";
                            const date = new Date(dateStr);
                            if (isNaN(date.getTime())) return "-";
                            const day = String(date.getDate()).padStart(2, '0');
                            const month = String(date.getMonth() + 1).padStart(2, '0');
                            const year = date.getFullYear();
                            return `${day}/${month}/${year}`;
                        },
                        formatFullDate: (dateStr) => {
                            if (!dateStr) return "-";
                            const date = new Date(dateStr);
                            if (isNaN(date.getTime())) return "-";
                            return date.toLocaleDateString("id-ID", { day: "numeric", month: "long", year: "numeric" });
                        },
                        formatMonthLabel: (val) => {
                            if (!val) return "";
                            const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                            const [y, m] = val.split("-").map(Number);
                            return `${monthNames[m - 1]} ${y}`;
                        },
                        getStatusColor: (status) => {
                            const s = status?.toUpperCase();
                            if (s === "NOT STARTED") return "bg-secondary text-light border border-slate-200";
                            if (s === "PENDING") return "bg-amber text-light border border-amber";
                            if (s === "PROCESED" || s === "PROCESSED") return "bg-amber text-light border border-amber";
                            if (s === "CLAIM") return "bg-success text-light border border-success";
                            if (s === "DRAFT") return "bg-secondary text-light";
                            if (s === "ONGOING") return "bg-amber text-light";
                            if (s === "SELESAI") return "bg-success text-light";
                            if (s === "CANCEL") return "bg-danger text-light";
                            const sl = s?.toLowerCase();
                            if (sl === "editing" || sl === "progres") return "bg-amber text-light";
                            if (sl === "shooting") return "bg-amber text-light";
                            if (sl === "ide") return "bg-secondary text-light";
                            if (sl === "done" || sl === "published") return "bg-success text-light";
                            return "bg-secondary text-light";
                        },
                        resolveAppUrl: (url) => {
                            if (!url || /^https?:\/\//i.test(url)) return url;
                            if (window.MARKETING_BACKEND_URL) {
                                return `${String(window.MARKETING_BACKEND_URL).replace(/\/+$/, '')}${url}`;
                            }
                            return url;
                        },
                        jsonApi: async (url, options = {}) => {
                            const cookie = document.cookie.split('; ').find((row) => row.startsWith('XSRF-TOKEN='));
                            const token = cookie ? decodeURIComponent(cookie.split('=')[1]) : '';
                            const response = await fetch(window.MarketingDashboardRuntimeHelpers.resolveAppUrl(url), {
                                ...options,
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    ...(token ? { 'X-XSRF-TOKEN': token } : {}),
                                    ...(options.headers || {})
                                },
                            });
                            if (!response.ok) {
                                let payload = null;
                                try { payload = await response.json(); } catch (error) { payload = null; }
                                if (response.status === 401) {
                                    const unauthorizedError = new Error((payload && payload.message) || 'Sesi login berakhir. Silakan login kembali.');
                                    unauthorizedError.status = 401;
                                    unauthorizedError.payload = payload;
                                    unauthorizedError.expired = payload?.expired === true;
                                    unauthorizedError.superseded = payload?.superseded === true;
                                    throw unauthorizedError;
                                }
                                const errorMessages = payload && payload.errors && typeof payload.errors === 'object'
                                    ? Object.values(payload.errors).flat().filter(Boolean)
                                    : [];
                                const message = errorMessages[0] || (payload && payload.message) || `HTTP ${response.status}`;
                                const requestError = new Error(message);
                                requestError.status = response.status;
                                requestError.payload = payload;
                                throw requestError;
                            }
                            return response.status === 204 ? null : response.json();
                        },
                    };
                }

                if (!window.MarketingDashboardRuntimeHelpers || !window.MarketingDashboardRuntimeHelpers.createAdminUserSettingsState) {
                        window.MarketingDashboardRuntimeHelpers = {
                            ...(window.MarketingDashboardRuntimeHelpers || {}),
                            createAdminUserSettingsState: (makeRef) => ({
                                profileForm: makeRef({ namaLengkap: "", oldPin: "", newPin: "", confirmPin: "" }),
                                submittingAvatar: makeRef(false),
                                authUsers: makeRef([]),
                                authUsersLoaded: makeRef(false),
                                authUserSearchQuery: makeRef(''),
                                activityLogs: makeRef([]),
                                activityLogsLoaded: makeRef(false),
                                activityLogFilters: makeRef({ table_name: "", action: "", record_key: "" }),
                                submittingAuthUser: makeRef(false),
                                submittingAuthUserAvatar: makeRef(false),
                                showAuthUserModal: makeRef(false),
                                authUserFormMode: makeRef("create"),
                                authUserForm: makeRef({ ID: null, username: "", nama: "", email: "", role: "operasional", pin: "", confirmPin: "", avatar_url: null }),
                        }),
                    };
                }

                const adminUserSettingsState = window.MarketingDashboardRuntimeHelpers.createAdminUserSettingsState(ref);
                const profileForm = adminUserSettingsState.profileForm || ref({ namaLengkap: "", oldPin: "", newPin: "", confirmPin: "" });
                const submittingAvatar = adminUserSettingsState.submittingAvatar || ref(false);
                const authUsers = adminUserSettingsState.authUsers || ref([]);
                const authUsersLoaded = adminUserSettingsState.authUsersLoaded || ref(false);
                const authUserSearchQuery = adminUserSettingsState.authUserSearchQuery || ref('');
                const activityLogs = adminUserSettingsState.activityLogs || ref([]);
                const activityLogsLoaded = adminUserSettingsState.activityLogsLoaded || ref(false);
                const activityLogFilters = adminUserSettingsState.activityLogFilters || ref({ table_name: "", action: "", record_key: "" });
                const submittingAuthUser = adminUserSettingsState.submittingAuthUser || ref(false);
                const submittingAuthUserAvatar = adminUserSettingsState.submittingAuthUserAvatar || ref(false);
                const showAuthUserModal = adminUserSettingsState.showAuthUserModal || ref(false);
                const authUserFormMode = adminUserSettingsState.authUserFormMode || ref("create");
                const authUserForm = adminUserSettingsState.authUserForm || ref({ ID: null, username: "", nama: "", email: "", role: "operasional", pin: "", confirmPin: "", avatar_url: null });
                const authUserRoleOptions = [
                    { value: 'super_admin', label: 'Super Admin' },
                    { value: 'admin', label: 'Admin' },
                    { value: 'kasir', label: 'Kasir' },
                    { value: 'operasional', label: 'Operasional' },
                    { value: 'brand_ambasador', label: 'Brand Ambasador' },
                    { value: 'talent', label: 'Talent' },
                ];
                const filteredAuthUsers = computed(() => {
                    const q = String(authUserSearchQuery.value || '').trim().toLowerCase();
                    return (Array.isArray(authUsers.value) ? authUsers.value : [])
                        .filter(Boolean)
                        .filter((user) => {
                            if (!q) return true;
                            return [user?.username, user?.nama, user?.email]
                                .some((value) => String(value || '').toLowerCase().includes(q));
                        });
                });
                const activeTeamUsers = computed(() => {
                    const chatOnline = Array.isArray(chatOnlineUsers.value) && chatOnlineUsers.value.length
                        ? chatOnlineUsers.value.filter((user) => user?.is_online === true)
                        : [];
                    const sourceUsers = Array.isArray(authUsers.value) && authUsers.value.length
                        ? authUsers.value
                        : [];
                    const usersByKey = new Map();

                    // Prefer chat online users (always fresh, excludes self)
                    chatOnline.forEach((user) => {
                        const key = String(user?.username || user?.email || user?.nama || '').trim().toLowerCase();
                        if (!key || usersByKey.has(key)) return;
                        usersByKey.set(key, user);
                    });

                    // Fallback: auth users filtered for online + not self
                    if (!usersByKey.size) {
                        sourceUsers
                            .filter(Boolean)
                            .filter((user) => {
                                const username = String(user?.username || '').trim().toLowerCase();
                                const currentUsername = String(currentUser.value?.username || '').trim().toLowerCase();
                                return user?.is_online === true && username !== currentUsername;
                            })
                            .forEach((user) => {
                                const key = String(user?.username || user?.email || user?.nama || '').trim().toLowerCase();
                                if (!key || usersByKey.has(key)) return;
                                usersByKey.set(key, user);
                            });
                    }

                    return Array.from(usersByKey.values());
                });
                const visibleActiveTeamUsers = computed(() => activeTeamUsers.value.slice(0, 5));
                const hiddenActiveTeamUsersCount = computed(() => Math.max(0, activeTeamUsers.value.length - visibleActiveTeamUsers.value.length));
                const filteredAuthUserRoleOptions = computed(() => {
                    const q = String(searchSelectQuery.value || '').trim().toLowerCase();
                    return authUserRoleOptions.filter((option) => {
                        return [option.value, option.label]
                            .some((value) => String(value || '').toLowerCase().includes(q));
                    });
                });
                const ACTIVITY_LOG_PAGE_SIZE = 15;
                const activityLogPage = ref(1);
                const activityLogTotalPages = computed(() => Math.max(1, Math.ceil(activityLogs.value.length / ACTIVITY_LOG_PAGE_SIZE)));
                const pagedActivityLogs = computed(() => activityLogs.value.slice((activityLogPage.value - 1) * ACTIVITY_LOG_PAGE_SIZE, activityLogPage.value * ACTIVITY_LOG_PAGE_SIZE));

                // Nama Stock State
                if (!window.MarketingDashboardRuntimeHelpers || !window.MarketingDashboardRuntimeHelpers.createNamaStockState) {
                    window.MarketingDashboardRuntimeHelpers = {
                        ...(window.MarketingDashboardRuntimeHelpers || {}),
                        createNamaStockState: (makeRef) => ({
                            namaStockRows: makeRef([]),
                            namaStockSearchQuery: makeRef(''),
                            namaStockKategoriFilter: makeRef(''),
                            namaStockBrandFilter: makeRef(''),
                            namaStockSaving: makeRef(false),
                            showNamaStockFormModal: makeRef(false),
                            namaStockFormMode: makeRef('create'),
                            namaStockForm: makeRef({ ID: '', KATEGORI: '', BRAND: '', SERI: '' }),
                            namaStockLoaded: makeRef(false),
                        }),
                    };
                }
                const {
                    namaStockRows,
                    namaStockSearchQuery,
                    namaStockKategoriFilter,
                    namaStockBrandFilter,
                    namaStockSaving,
                    showNamaStockFormModal,
                    namaStockFormMode,
                    namaStockForm,
                    namaStockLoaded,
                } = window.MarketingDashboardRuntimeHelpers.createNamaStockState(ref);
                const filteredStories = computed(() => {
                    const wantGenap = storyTab.value === 'Genap';
                    return storyData.value.filter(s => {
                        const genap = s.is_genap === 1 || s.is_genap === true || s.is_genap === "1" || s.is_genap === "TRUE" || s.is_genap === "true" || s.is_genap === "Genap";
                        return wantGenap ? genap : !genap;
                    }).sort((a, b) => (a.Jam || "").localeCompare(b.Jam || ""));
                });

                const switchTab = (tab) => {
                    if (tab === 'auth_users' && !canManageUsers.value) {
                        showNotification("Akses manajemen user hanya untuk Super Admin", "warning");
                        activeTab.value = 'settings';
                        localStorage.setItem("ppp_active_tab", 'settings');
                        history.replaceState(null, '', '#settings');
                        return;
                    }
                    localStorage.setItem("ppp_active_tab", tab);

                    const currentPath = window.location.pathname.replace(/\/$/, '') || '/';
                    const dashboardPath = currentPath === '/8090' || currentPath.startsWith('/8090/') ? '/8090/' : '/';
                    if (currentPath !== '/' && currentPath !== '/8090') {
                        window.location.assign(dashboardPath + '#' + encodeURIComponent(tab));
                        return;
                    }

                    activeTab.value = tab;
                    history.replaceState(null, '', '#' + tab);
                    if (window.innerWidth < 1024) {
                        sidebarOpen.value = false;
                    }

                    const tabDataKey = {
                        unboxing: 'unboxing',
                        orderan_online: 'orderanOnline',
                        unit_ditanya: 'unitDitanya',
                        claim_garansi_asuransi: 'claimGaransi',
                        program_promo: 'promo',
                        sell_out: 'sellOut',
                        laporan_event: 'lpjk',
                        ads_log: 'ads',
                        harga_kompetitor: 'hargaKompetitor',
                         asset_vendor_inventory: 'assetVendorInventory',
                         proses_claim: 'serviceClaims',
                         calendar: 'calendar',

                    }[tab];

                    if (tabDataKey) loadTabData(tabDataKey);

                    // Track menu visit
                    fetch(window.MarketingDashboardRuntimeHelpers.resolveAppUrl('/api/menu-visits'), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({ tab_key: tab })
                    }).catch(() => {});

                    // Calendar butuh story data (master plan & events sudah dimuat saat init).
                    if (tab === 'calendar' && storyData.value.length === 0) {
                        loadStoryData();
                    }
                };

                // Modal & Form States
                const modalOpen = ref(false);
                const modalType = ref("create");
                const settings = ref({});
                const settingsLoaded = ref(false);
                const settingsDraft = ref({});
                const settingsDirty = ref(false);
                const activeSettingTab = ref(null);
                const savingSettings = ref(false);
                const settingsSearchQuery = ref('');
                const settingsFilterMode = ref('all');
                const activeSettingValueSearch = ref('');
                const settingsDetailModalOpen = ref(false);
                const showSettingsBulkAdd = ref(false);
                const settingsBulkAddText = ref('');
                let settingsLoadPromise = null;
                const ideationDraftLabel = computed(() => (settings.value.Status || [])[0] || 'Draft');
                const ideationProgressLabel = 'In Progress';
                const ideationDoneLabel = 'Done';
                const createBoardPages = () => ({
                    [ideationDraftLabel.value]: 1,
                    [ideationProgressLabel]: 1,
                    [ideationDoneLabel]: 1
                });
                const masterForm = ref({
                    ID: null,
                    Judul: "",
                    Format_Konten: "",
                    Platforms: [],
                    Colab: [],
                    Talent: [],
                    Status: "",
                    Tanggal_Rencana: todayStr(),
                    Skrip: "Tidak",
                    Caption: "Tidak",
                    Distribution_Meta: {},
                    Link_Drive: ""
                });

@endverbatim
@include('dashboard.partials.shell.app-script-content-list-computed')
@verbatim
                const getKanbanItems = (statusGroup) => {
                    return kanbanBuckets.value[statusGroup] || [];
                };

                const getIdeaAgeLabel = (item) => {
                    const rawDate = String(item?.Tanggal_Rencana || '').trim();
                    if (!rawDate) return 'Umur -';
                    const source = new Date(rawDate);
                    if (Number.isNaN(source.getTime())) return 'Umur -';
                    const today = new Date();
                    source.setHours(0, 0, 0, 0);
                    today.setHours(0, 0, 0, 0);
                    const diffDays = Math.max(0, Math.floor((today.getTime() - source.getTime()) / 86400000));
                    return `Umur ${diffDays}h`;
                };

                const getIdeationTypeTone = (item) => {
                    const raw = String(item?.Format_Konten || item?.ContentType || item?.Status || "general").trim().toUpperCase();
                    const palettes = [
                        { chip: "bg-amber text-white", card: "border-amber" },
                        { chip: "bg-success text-white", card: "border-success" },
                        { chip: "bg-amber text-white", card: "border-amber" },
                        { chip: "bg-slate-500 text-white", card: "border-slate-500" },
                        { chip: "bg-danger text-white", card: "border-danger" }
                    ];
                    const fixedMap = { "AD": 0, "COLAB": 1, "PROMO": 2, "EDUKASI": 3, "STORY": 4 };
                    let idx = fixedMap[raw];
                    if (idx === undefined) idx = raw.length % palettes.length;
                    return palettes[idx];
                };

                const calculateScore = (row) => {
                    if (!row) return 0;
                    const likes = Number(row.Likes || 0);
                    const comments = Number(row.Comments || 0);
                    const shares = Number(row.Shares || 0);
                    const rawScore = likes * 1 + comments * 3 + shares * 5;
                    const finalScore = rawScore / 5000 * 100; // Simplified KPI target
                    return parseFloat(Math.min(finalScore, 100).toFixed(1));
                };

                const getVelocity = (row) => {
                    const score = calculateScore(row);
                    if (!row.Views || row.Views == 0) return { label: "New", class: "bg-slate-500", icon: "fas fa-clock" };
                    if (score >= 80) return { label: "Viral", class: "bg-danger", icon: "fas fa-fire" };
                    if (score >= 50) return { label: "High", class: "bg-success", icon: "fas fa-arrow-up" };
                    if (score >= 20) return { label: "Avg", class: "bg-amber", icon: "fas fa-minus" };
                    return { label: "Low", class: "bg-slate-500", icon: "fas fa-arrow-down" };
                };



                const { formatNumber, formatWaNumber, calcAdminPct } = window.MarketingDashboardRuntimeHelpers;
@endverbatim
@include('dashboard.partials.shell.app-script-notification-error-utils')
@include('dashboard.partials.shell.app-script-master-content-operations')
@include('dashboard.partials.shell.app-script-distribution-analytics-operations')
@include('dashboard.partials.shell.app-script-content-insight-trends')
@verbatim
@endverbatim
@include('dashboard.partials.shell.app-script-shell-interaction-helpers')
@verbatim
@endverbatim
