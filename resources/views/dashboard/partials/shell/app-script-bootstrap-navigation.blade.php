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
@endverbatim
                const _serverTab = @json($activeTab ?? null);
                const urlRouting = @json((bool) config('dashboard.url_routing'));
                const tabUrls = @json(config('dashboard.tab_urls', []));
@verbatim
                const _hashTab = window.location.hash.slice(1);
                // Menus hidden from the sidebar must not be restored as the default landing tab.
                const _hiddenTabs = ['pricelist_katalog', 'apple_katalog', 'template_background', 'img_repo', 'bonus_report', 'talent_bonus', 'editor_performance', 'market_pasar', 'market_intelijen_harga', 'market_audit_harga', 'market_eksternal', 'market_ext_goodponsel', 'market_ext_devstore', 'market_ext_rumahgadget', 'top_content_platform', 'low_content_platform', 'analisa_insight'];
                const _restoredTab = localStorage.getItem("ppp_active_tab");
                const _savedTab = _serverTab || _hashTab || (_hiddenTabs.includes(_restoredTab) ? null : _restoredTab) || "dashboard";
                // Link lama `/#tab`, bookmark, dan tab tersimpan: pindahkan ke URL menu yang sudah dimigrasi.
                const _migratedUrl = !_serverTab && urlRouting ? tabUrls[_savedTab] : null;
                if (_migratedUrl) {
                    const _prefix = window.location.pathname === '/8090' || window.location.pathname.startsWith('/8090/') ? '/8090' : '';
                    window.location.replace(_prefix + _migratedUrl);
                } else if (!_serverTab && !_hashTab && _savedTab) {
                    history.replaceState(null, '', '#' + _savedTab);
                }
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
                    promo_pamflet: { label: 'Promo Pamflet', category: 'Market' },
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
                    service: { label: 'Service', category: 'Customer Service' },
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
                    template_background: { label: 'Template Background', category: null },
                    apple_katalog: { label: 'Katalog Apple', category: null },
                    img_repo: { label: 'Repo Gambar', category: null },
                    tiktok_template: { label: 'Template TikTok', category: null },
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
                const isMobileViewport = ref(window.innerWidth < 1024);
                const savedSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === '1';
                const sidebarOpen = ref(false);
                const sidebarCollapsed = ref(savedSidebarCollapsed);
                const marketExternalTabs = ['market_eksternal', 'market_ext_goodponsel', 'market_ext_devstore', 'market_ext_rumahgadget'];
                const toggleMenuGroup = () => {};
                const closeAllMenuGroups = () => {};
                const openMenuGroup = () => {};
                const groupForTab = () => null;

                const promoPamflet = Vue.reactive({
                    categories: [],
                    pamflets: [],
                    loadingCategories: false,
                    loadingPamflets: false,
                    submittingCategory: false,
                    submittingPamflet: false,
                    deleting: false,
                    search: '',
                    selectedCategory: '',
                    categorySelectOpen: false,
                    categorySelectQuery: '',
                    categorySelectStyle: {},

                    // Modals
                    showCatModal: false,
                    catModalMode: 'create',
                    catForm: { id: null, nama: '' },
                    catError: '',

                    showPamfletModal: false,
                    pamfletModalMode: 'create',
                    pamfletForm: { id: null, nama: '', kategori: '', deskripsi: '', desain: null },
                    desainPreview: '',
                    desainFileName: '',
                    desainFileSize: '',
                    uploadingDesain: false,
                    pamfletError: '',

                    showDetailModal: false,
                    selectedPamflet: null,

                    showDeleteModal: false,
                    deleteType: '',
                    deleteTarget: null,

                    get filteredCategoryOptions() {
                        const query = this.categorySelectQuery.trim().toLowerCase();
                        return (this.categories || []).filter(category => !query || String(category.nama || category.name || '').toLowerCase().includes(query));
                    },

                    openCategorySelect(event) {
                        this.categorySelectOpen = !this.categorySelectOpen;
                        this.categorySelectQuery = '';
                        if (!this.categorySelectOpen) {
                            return;
                        }
                        const rect = event.currentTarget.getBoundingClientRect();
                        this.categorySelectStyle = {
                            position: 'fixed',
                            left: `${rect.left}px`,
                            top: `${rect.bottom + 4}px`,
                            width: `${rect.width}px`,
                            zIndex: 3000,
                        };
                    },

                    get filteredPamflets() {
                        let result = this.pamflets || [];
                        if (this.selectedCategory) {
                            result = result.filter(p => String(p.kategori || p.kategori_id || p.category_id || p.category || '') === String(this.selectedCategory));
                        }
                        if (this.search) {
                            const q = this.search.toLowerCase();
                            result = result.filter(p =>
                                (p.nama && String(p.nama).toLowerCase().includes(q)) ||
                                (p.deskripsi && String(p.deskripsi).toLowerCase().includes(q)) ||
                                (p.kategori && String(p.kategori).toLowerCase().includes(q))
                            );
                        }
                        return result;
                    },

                    async fetchCategories() {
                        this.loadingCategories = true;
                        try {
                            const res = await fetch(window.MarketingDashboardRuntimeHelpers.resolveAppUrl('/api/promo-pamflet-categories'), {
                                headers: { 'Accept': 'application/json' }
                            });
                            if (res.ok) {
                                const data = await res.json();
                                this.categories = Array.isArray(data) ? data : (data.data || []);
                            }
                        } catch (e) {
                            console.error('Error fetching categories:', e);
                        } finally {
                            this.loadingCategories = false;
                        }
                    },

                    async fetchPamflets() {
                        this.loadingPamflets = true;
                        try {
                            const res = await fetch(window.MarketingDashboardRuntimeHelpers.resolveAppUrl('/api/promo-pamflets'), {
                                headers: { 'Accept': 'application/json' }
                            });
                            if (res.ok) {
                                const data = await res.json();
                                this.pamflets = Array.isArray(data) ? data : (data.data || []);
                            }
                        } catch (e) {
                            console.error('Error fetching pamflets:', e);
                        } finally {
                            this.loadingPamflets = false;
                        }
                    },

                    async fetchData() {
                        await Promise.all([this.fetchCategories(), this.fetchPamflets()]);
                    },

                    openCatModal(mode = 'create', cat = null) {
                        this.catModalMode = mode;
                        this.catError = '';
                        if (mode === 'edit' && cat) {
                            this.catForm = { id: cat.id, nama: cat.nama || cat.name || '' };
                        } else {
                            this.catForm = { id: null, nama: '' };
                        }
                        this.showCatModal = true;
                    },

                    closeCatModal() {
                        this.showCatModal = false;
                        this.catForm = { id: null, nama: '' };
                        this.catError = '';
                    },

                    async saveCategory() {
                        if (!this.catForm.nama || !this.catForm.nama.trim()) {
                            this.catError = 'Nama kategori wajib diisi';
                            return;
                        }
                        this.submittingCategory = true;
                        this.catError = '';
                        try {
                            const isEdit = this.catModalMode === 'edit' && this.catForm.id;
                            const url = isEdit ? `/api/promo-pamflet-categories/${this.catForm.id}` : '/api/promo-pamflet-categories';
                            const method = isEdit ? 'PUT' : 'POST';

                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                            const res = await fetch(window.MarketingDashboardRuntimeHelpers.resolveAppUrl(url), {
                                method: method,
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: JSON.stringify({
                                    id: this.catForm.id,
                                    nama: this.catForm.nama.trim()
                                })
                            });

                            if (res.ok) {
                                this.closeCatModal();
                                await this.fetchCategories();
                            } else {
                                const errData = await res.json().catch(() => ({}));
                                this.catError = errData.message || 'Gagal menyimpan kategori';
                            }
                        } catch (e) {
                            this.catError = 'Terjadi kesalahan sistem';
                        } finally {
                            this.submittingCategory = false;
                        }
                    },

                    confirmDeleteCat(cat) {
                        this.deleteType = 'category';
                        this.deleteTarget = cat;
                        this.showDeleteModal = true;
                    },

                    openPamfletModal(mode = 'create', pamflet = null) {
                        this.pamfletModalMode = mode;
                        this.pamfletError = '';
                        this.desainPreview = '';
                        this.desainFileName = '';
                        this.desainFileSize = '';
                        this.uploadingDesain = false;
                        this.categorySelectOpen = false;
                        this.categorySelectQuery = '';
                        if (mode === 'edit' && pamflet) {
                            this.pamfletForm = {
                                id: pamflet.id,
                                nama: pamflet.nama || '',
                                kategori: pamflet.kategori || pamflet.kategori_id || pamflet.category || '',
                                deskripsi: pamflet.deskripsi || '',
                                desain: null
                            };
                            this.desainPreview = pamflet.desain_url || pamflet.desain || pamflet.image_url || '';
                        } else {
                            this.pamfletForm = { id: null, nama: '', kategori: '', deskripsi: '', desain: null };
                        }
                        this.showPamfletModal = true;
                    },

                    closePamfletModal() {
                        this.showPamfletModal = false;
                        this.pamfletForm = { id: null, nama: '', kategori: '', deskripsi: '', desain: null };
                        this.desainPreview = '';
                        this.desainFileName = '';
                        this.desainFileSize = '';
                        this.uploadingDesain = false;
                        this.pamfletError = '';
                    },

                    handleFileChange(event) {
                        const file = event.target.files?.[0];
                        if (file) {
                            if (file.size > 10 * 1024 * 1024) {
                                this.pamfletError = 'Ukuran file gambar maksimal 10MB';
                                event.target.value = '';
                                return;
                            }
                            this.uploadingDesain = true;
                            this.pamfletError = '';
                            this.pamfletForm.desain = file;
                            this.desainFileName = file.name;
                            this.desainFileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.desainPreview = e.target.result;
                                this.uploadingDesain = false;
                            };
                            reader.onerror = () => {
                                this.pamfletError = 'Gagal membaca file gambar';
                                this.uploadingDesain = false;
                            };
                            reader.readAsDataURL(file);
                        }
                    },

                    async savePamflet() {
                        if (this.uploadingDesain) {
                            this.pamfletError = 'Harap tunggu hingga proses pra-unggah gambar selesai';
                            return;
                        }
                        if (!this.pamfletForm.nama || !this.pamfletForm.nama.trim()) {
                            this.pamfletError = 'Nama pamflet wajib diisi';
                            return;
                        }
                        if (!this.pamfletForm.kategori) {
                            this.pamfletError = 'Kategori pamflet wajib dipilih';
                            return;
                        }
                        this.submittingPamflet = true;
                        this.pamfletError = '';

                        try {
                            const isEdit = this.pamfletModalMode === 'edit' && this.pamfletForm.id;
                            const url = isEdit ? `/api/promo-pamflets/${this.pamfletForm.id}` : '/api/promo-pamflets';

                            const formData = new FormData();
                            if (isEdit) {
                                formData.append('id', this.pamfletForm.id);
                            }
                            formData.append('nama', this.pamfletForm.nama.trim());
                            formData.append('kategori', this.pamfletForm.kategori || '');
                            formData.append('deskripsi', this.pamfletForm.deskripsi || '');
                            if (this.pamfletForm.desain) {
                                formData.append('desain', this.pamfletForm.desain);
                            }

                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                            const res = await fetch(window.MarketingDashboardRuntimeHelpers.resolveAppUrl(url), {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: formData
                            });

                            if (res.ok) {
                                this.closePamfletModal();
                                await this.fetchPamflets();
                            } else {
                                const errData = await res.json().catch(() => ({}));
                                this.pamfletError = errData.message || 'Gagal menyimpan promo pamflet';
                            }
                        } catch (e) {
                            this.pamfletError = 'Terjadi kesalahan sistem';
                        } finally {
                            this.submittingPamflet = false;
                        }
                    },

                    confirmDeletePamflet(pamflet) {
                        this.deleteType = 'pamflet';
                        this.deleteTarget = pamflet;
                        this.showDeleteModal = true;
                    },

                    async executeDelete() {
                        if (!this.deleteTarget) return;
                        this.deleting = true;
                        try {
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                            const isCat = this.deleteType === 'category';
                            const url = isCat
                                ? `/api/promo-pamflet-categories/${this.deleteTarget.id}`
                                : `/api/promo-pamflets/${this.deleteTarget.id}`;

                            const res = await fetch(window.MarketingDashboardRuntimeHelpers.resolveAppUrl(url), {
                                method: 'DELETE',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                }
                            });

                            if (res.ok) {
                                this.showDeleteModal = false;
                                this.deleteTarget = null;
                                if (isCat) {
                                    await this.fetchCategories();
                                } else {
                                    await this.fetchPamflets();
                                }
                            }
                        } catch (e) {
                            console.error('Error deleting:', e);
                        } finally {
                            this.deleting = false;
                        }
                    },

                    openDetail(pamflet) {
                        this.selectedPamflet = pamflet;
                        this.showDetailModal = true;
                    },

                    closeDetail() {
                        this.showDetailModal = false;
                        this.selectedPamflet = null;
                    }
                });

                const _appInst = Vue.getCurrentInstance();
                if (_appInst && _appInst.appContext) {
                    _appInst.appContext.config.globalProperties.promoPamflet = promoPamflet;
                }

@endverbatim
