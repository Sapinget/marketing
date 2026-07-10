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
                            return date.toLocaleDateString("id-ID", { day: "2-digit", month: "short" });
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
                            if (s === "NOT STARTED") return "bg-slate-100 text-slate-600 border border-slate-200";
                            if (s === "PENDING") return "bg-amber-100 text-amber-600 border border-amber-200";
                            if (s === "PROCESED" || s === "PROCESSED") return "bg-indigo-100 text-indigo-600 border border-indigo-200";
                            if (s === "CLAIM") return "bg-emerald-100 text-emerald-600 border border-emerald-200";
                            if (s === "DRAFT") return "bg-slate-100 text-slate-500";
                            if (s === "ONGOING") return "bg-amber-100 text-amber-700";
                            if (s === "SELESAI") return "bg-emerald-100 text-emerald-700";
                            if (s === "CANCEL") return "bg-rose-100 text-rose-500";
                            const sl = s?.toLowerCase();
                            if (sl === "editing" || sl === "progres") return "bg-blue-100 text-blue-600";
                            if (sl === "shooting") return "bg-amber-100 text-amber-600";
                            if (sl === "ide") return "bg-slate-100 text-slate-600";
                            if (sl === "done" || sl === "published") return "bg-emerald-100 text-emerald-600";
                            return "bg-slate-100 text-slate-600";
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
                                if (response.status === 401) {
                                    const unauthorizedError = new Error('Sesi login berakhir. Silakan login kembali.');
                                    unauthorizedError.status = 401;
                                    throw unauthorizedError;
                                }
                                let payload = null;
                                try { payload = await response.json(); } catch (error) { payload = null; }
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
                            authUsers: makeRef([]),
                            authUsersLoaded: makeRef(false),
                            activityLogs: makeRef([]),
                            activityLogsLoaded: makeRef(false),
                            activityLogFilters: makeRef({ table_name: "", action: "", record_key: "" }),
                            submittingAuthUser: makeRef(false),
                            authUserForm: makeRef({ username: "", nama: "", email: "", pin: "", confirmPin: "" }),
                        }),
                    };
                }

                const {
                    profileForm,
                    authUsers,
                    authUsersLoaded,
                    activityLogs,
                    activityLogsLoaded,
                    activityLogFilters,
                    submittingAuthUser,
                    authUserForm,
                } = window.MarketingDashboardRuntimeHelpers.createAdminUserSettingsState(ref);

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
                    if (isTeknisi.value && !TEKNISI_TABS.has(tab)) {
                        showNotification("Akses dibatasi untuk role Teknisi", "warning");
                        return;
                    }
                    activeTab.value = tab;
                    localStorage.setItem("ppp_active_tab", tab);
                    history.replaceState(null, '', '#' + tab);
                    if (window.innerWidth < 768) {
                        isSidebarOpen.value = false;
                    }
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
                    Editor: "",
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
                        { chip: "bg-blue-500 text-white", card: "border-blue-500" },
                        { chip: "bg-emerald-500 text-white", card: "border-emerald-500" },
                        { chip: "bg-amber-500 text-white", card: "border-amber-500" },
                        { chip: "bg-violet-500 text-white", card: "border-violet-500" },
                        { chip: "bg-rose-500 text-white", card: "border-rose-500" }
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
                    if (score >= 80) return { label: "Viral", class: "bg-red-500", icon: "fas fa-fire" };
                    if (score >= 50) return { label: "High", class: "bg-emerald-500", icon: "fas fa-arrow-up" };
                    if (score >= 20) return { label: "Avg", class: "bg-amber-500", icon: "fas fa-minus" };
                    return { label: "Low", class: "bg-violet-500", icon: "fas fa-arrow-down" };
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
