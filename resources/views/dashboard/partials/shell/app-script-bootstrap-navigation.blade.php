@verbatim
                const appLoading = ref(true);
                const pppBootScreen = document.getElementById('ppp-boot-screen');
                if (pppBootScreen && !pppBootScreen.classList.contains('is-hidden')) {
                    pppBootScreen.classList.add('is-hidden');
                    setTimeout(function () { pppBootScreen.remove(); }, 300);
                }
                if ('scrollRestoration' in history) {
                    history.scrollRestoration = 'manual';
                }
                const _hashTab = window.location.hash.slice(1);
                const _savedTab = _hashTab || localStorage.getItem("ppp_active_tab") || "dashboard";
                if (!_hashTab && _savedTab) history.replaceState(null, '', '#' + _savedTab);
                const setDocumentScrollLock = (locked) => {
                    document.documentElement.classList.toggle('modal-scroll-lock', locked);
                    document.body.classList.toggle('modal-scroll-lock', locked);
                };
                const activeTab = ref(_savedTab);
                const tabConfig = {
                    dashboard: { label: 'Dashboard', category: null },
                    master: { label: 'Master Plan', category: 'Konten' },
                    unboxing: { label: 'Unboxing', category: 'Konten' },
                    ideation: { label: 'Ideation', category: 'Konten' },
                    distribution: { label: 'Distribution', category: 'Konten' },
                    analytics: { label: 'Analytics', category: 'Konten' },
                    calendar: { label: 'Kalender', category: 'Konten' },
                    story: { label: 'Jadwal Story', category: 'Konten' },
                    program_promo: { label: 'Program Promo', category: 'Market' },
                    sell_out: { label: 'Sell Out Target', category: 'Market' },
                    ads_log: { label: 'Ads Log', category: 'Market' },
                    budgeting: { label: 'Budgeting', category: 'Market' },
                    market_pasar: { label: 'Pasar', breadcrumb: ['Marketing', 'Intelijen Pasar', 'Pasar'] },
                    market_intelijen_harga: { label: 'Intelijen Harga', breadcrumb: ['Marketing', 'Intelijen Pasar', 'Intelijen Harga'] },
                    market_audit_harga: { label: 'Audit Harga', breadcrumb: ['Marketing', 'Intelijen Pasar', 'Audit Harga'] },
                    market_eksternal: { label: 'Semua Kompetitor', breadcrumb: ['Marketing', 'Intelijen Pasar', 'Kompetitor', 'Semua Kompetitor'] },
                    market_ext_goodponsel: { label: 'Good Ponsel', breadcrumb: ['Marketing', 'Intelijen Pasar', 'Kompetitor', 'Good Ponsel'] },
                    market_ext_devstore: { label: 'Devstore', breadcrumb: ['Marketing', 'Intelijen Pasar', 'Kompetitor', 'Devstore'] },
                    market_ext_rumahgadget: { label: 'Rumah Gadget Bali', breadcrumb: ['Marketing', 'Intelijen Pasar', 'Kompetitor', 'Rumah Gadget Bali'] },
                    top_content_platform: { label: 'Top Konten', category: 'Analisa Konten' },
                    low_content_platform: { label: 'Low Konten', category: 'Analisa Konten' },
                    analisa_insight: { label: 'Insight & Tren', category: 'Analisa Konten' },
                    meta_story: { label: 'Story IG', category: 'Analisa Konten' },
                    meta_feed: { label: 'Feed Konten', category: 'Analisa Konten' },
                    meta_followers: { label: 'Followers IG', category: 'Analisa Konten' },
                    orderan_online: { label: 'Order Online', category: 'Customer Service' },
                    unit_ditanya: { label: 'Unit Ditanya', category: 'Customer Service' },
                    claim_garansi_asuransi: { label: 'Claim Garansi', category: 'Customer Service' },
                    keep_barang: { label: 'Keep Barang', category: 'Customer Service' },
                    input_claim: { label: 'Input Claim', category: 'Complain Traker' },
                    garansi_cermati: { label: 'Garansi Cermati', category: 'Complain Traker' },
                    garansi_resmi: { label: 'Garansi Resmi', category: 'Complain Traker' },
                    bonus_report: { label: 'Bonus Report', category: 'Performa' },
                    talent_bonus: { label: 'Talent Bonus', category: 'Performa' },
                    editor_performance: { label: 'Editor Performance', category: 'Performa' },
                    settings: { label: 'Settings', category: 'Settings' },
                    nama_stock: { label: 'Nama Stock', category: 'Settings' },
                    auth_users: { label: 'Manajemen User', category: 'Settings' },
                    activity_logs: { label: 'Activity Logs', category: 'Settings' },
                    harga_kompetitor: { label: 'Harga & Kompetitor', category: null },
                    pricelist_katalog: { label: 'Katalog Android', category: null },
                    apple_katalog: { label: 'Katalog Apple', category: null },
                    img_repo: { label: 'Repo Gambar', category: null },
                    laporan_event: { label: 'Laporan Event', category: null },
                    asset_vendor_inventory: { label: 'Asset Inventory', category: 'Inventory' },
                    profile: { label: 'Profile', category: null }
                };

                const breadcrumbItems = computed(() => {
                    const config = tabConfig[activeTab.value] || { label: activeTab.value, category: null };
                    if (Array.isArray(config.breadcrumb) && config.breadcrumb.length) {
                        return config.breadcrumb;
                    }
                    const items = ['Marketing'];
                    if (config.category) items.push(config.category);
                    items.push(config.label.replace(/_/g, ' '));
                    return items;
                });
                const profileMenuOpen = ref(false);
                const bottomNavMoreOpen = ref(false);
                const openBottomNavMore = () => {
                    bottomNavMoreOpen.value = true;
                };
                const closeBottomNavMore = () => {
                    bottomNavMoreOpen.value = false;
                };
                const isMobileViewport = ref(window.innerWidth < 768);
                const savedSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                const isSidebarOpen = ref(window.innerWidth >= 768 ? !savedSidebarCollapsed : false);
                const marketExternalTabs = ['market_eksternal', 'market_ext_goodponsel', 'market_ext_devstore', 'market_ext_rumahgadget'];
                const toggleMenuGroup = () => {};
                const closeAllMenuGroups = () => {};
                const openMenuGroup = () => {};
                const groupForTab = () => null;
@endverbatim
