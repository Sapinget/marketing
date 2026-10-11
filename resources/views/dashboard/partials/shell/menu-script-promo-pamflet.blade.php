@verbatim
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

                // Dipanggil runActiveTabProtectedLoaders saat tab dibuka (juga saat halaman dibuka langsung).
                menuLoaders.promo_pamflet = () => { promoPamflet.fetchData(); };
@endverbatim
