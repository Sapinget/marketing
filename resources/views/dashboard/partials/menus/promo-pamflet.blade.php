@verbatim
<!-- Promo Pamflet tab -->
<div v-if="activeTab === 'promo_pamflet'" class="space-y-4 animate-fadeIn">
    <!-- Summary Cards -->
    <div class="dashboard-summary-grid-compact grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4">
        <div class="dashboard-summary-card-compact stat-card relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-image text-[120px]"></i></div>
            <p class="dashboard-summary-title">Total Pamflet</p>
            <div class="flex items-baseline gap-2">
                <span class="dashboard-summary-value">{{ promoPamflet.pamflets.length }}</span>
                <span class="dashboard-summary-unit">Desain</span>
            </div>
        </div>
        <div class="dashboard-summary-card-compact stat-card relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-folder-tree text-[120px]"></i></div>
            <p class="dashboard-summary-title">Kategori</p>
            <div class="flex items-baseline gap-2">
                <span class="dashboard-summary-value">{{ promoPamflet.categories.length }}</span>
                <span class="dashboard-summary-unit">Kategori</span>
            </div>
        </div>
        <div class="dashboard-summary-card-compact stat-card relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-filter text-[120px]"></i></div>
            <p class="dashboard-summary-title">Hasil Filter</p>
            <div class="flex items-baseline gap-2">
                <span class="dashboard-summary-value">{{ promoPamflet.filteredPamflets.length }}</span>
                <span class="dashboard-summary-unit">Pamflet</span>
            </div>
        </div>
    </div>

    <!-- Main Content Section Card -->
    <div class="section-card section-card-shell">
        <!-- Table / Grid Toolbar -->
        <div class="table-toolbar-shell">
            <div class="table-toolbar-shell__left">
                <div class="relative flex-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                    <input id="pamflet-search" name="pamflet_search" v-model="promoPamflet.search" type="text" placeholder="Cari pamflet..."
                        autocomplete="off" aria-label="Cari pamflet"
                        class="form-input-search" />
                </div>
            </div>
            <div class="table-toolbar-shell__right">
                <div class="toolbar-actions toolbar-actions--desktop-icon-only">
                    <button @click="promoPamflet.openPamfletModal('create')"
                        class="primary-cta-button primary-cta-button--accent primary-cta-button--icon-only active:scale-95"
                        aria-label="Tambah Pamflet">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                    <button @click="promoPamflet.openCatModal('create')"
                        class="primary-cta-button primary-cta-button--neutral active:scale-95"
                        aria-label="Kelola Kategori">
                        <i class="fa-solid fa-folder-tree mr-1"></i>
                        <span>Kategori ({{ promoPamflet.categories.length }})</span>
                    </button>
                    <button @click="promoPamflet.fetchData()"
                        class="primary-cta-button primary-cta-button--neutral primary-cta-button--icon-only active:scale-95"
                        aria-label="Refresh Data">
                        <i :class="['fa-solid fa-arrows-rotate', (promoPamflet.loadingPamflets || promoPamflet.loadingCategories) ? 'animate-spin' : '']"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Category Filter Bar -->
        <div class="px-6 py-1.5 border-b border-slate-100 flex items-center gap-1.5 overflow-x-auto custom-scrollbar bg-slate-50/50">
            <span class="text-overline text-slate-400 shrink-0 mr-1">Filter Kategori:</span>
            <button @click="promoPamflet.selectedCategory = ''"
                :class="['px-2 py-0.5 rounded text-overline transition shrink-0', promoPamflet.selectedCategory === '' ? 'bg-ppp-accent text-white font-bold' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100']">
                Semua ({{ promoPamflet.pamflets.length }})
            </button>
            <button v-for="cat in promoPamflet.categories" :key="cat.id"
                @click="promoPamflet.selectedCategory = (cat.nama || cat.name || cat.id)"
                :class="['px-2 py-0.5 rounded text-overline transition shrink-0 flex items-center gap-1', promoPamflet.selectedCategory === (cat.nama || cat.name || cat.id) ? 'bg-ppp-accent text-white font-bold' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100']">
                <span>{{ cat.nama || cat.name }}</span>
            </button>
        </div>

        <!-- Loading State Skeleton -->
        <div v-if="promoPamflet.loadingPamflets" class="p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 animate-pulse">
            <div v-for="i in 8" :key="'sk-pf'+i" class="rounded-2xl border border-slate-100 overflow-hidden p-3 space-y-3">
                <div class="h-44 bg-slate-100 rounded-xl w-full"></div>
                <div class="h-4 bg-slate-100 rounded-full w-3/4"></div>
                <div class="h-3 bg-slate-100 rounded-full w-1/2"></div>
            </div>
        </div>

        <!-- Pamflet Grid -->
        <div v-else class="p-6">
            <div v-if="promoPamflet.filteredPamflets.length === 0" class="py-16 text-center text-slate-400">
                <i class="fa-solid fa-image text-4xl mb-3 opacity-20 block"></i>
                <p class="text-body font-medium text-slate-600">Belum ada promo pamflet</p>
                <p class="text-body-sm text-slate-400 mt-1">Silakan klik "Tambah Pamflet" untuk mengunggah pamflet baru.</p>
                <button @click="promoPamflet.openPamfletModal('create')" class="mt-4 primary-cta-button primary-cta-button--accent">
                    <i class="fa-solid fa-plus text-xs mr-1"></i> Tambah Pamflet Pertama
                </button>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                <div v-for="item in promoPamflet.filteredPamflets" :key="item.id"
                    class="group rounded-2xl border border-slate-100 bg-white overflow-hidden shadow-sm hover:shadow-md transition-all duration-200 flex flex-col">
                    <!-- Thumbnail Area -->
                    <div class="relative bg-slate-100 aspect-[4/5] overflow-hidden flex items-center justify-center cursor-pointer"
                        @click="promoPamflet.openDetail(item)">
                        <img v-if="item.desain_url || item.desain || item.image_url"
                            :src="item.desain_url || item.desain || item.image_url"
                            :alt="item.nama"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            loading="lazy" />
                        <div v-else class="flex flex-col items-center justify-center text-slate-300">
                            <i class="fa-solid fa-image text-4xl mb-2"></i>
                            <span class="text-[11px] font-medium text-slate-400">Tidak ada gambar</span>
                        </div>
                        <span class="absolute top-2.5 left-2.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-black/60 text-white backdrop-blur-sm shadow-sm">
                            {{ item.kategori || item.category || 'Promo' }}
                        </span>
                        <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                            <button @click.stop="promoPamflet.openDetail(item)" class="table-action-button table-action-compact" aria-label="Lihat Preview">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </button>
                            <button @click.stop="promoPamflet.openPamfletModal('edit', item)" class="table-action-button table-action-compact" aria-label="Edit">
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Details Area -->
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-slate-800 text-body truncate group-hover:text-ppp-accent transition" :title="item.nama">
                                {{ item.nama }}
                            </h3>
                            <p v-if="item.deskripsi" class="text-slate-500 text-[11px] line-clamp-2 mt-1 leading-relaxed">
                                {{ item.deskripsi }}
                            </p>
                            <p v-else class="text-slate-300 text-[11px] italic mt-1">
                                Tidak ada deskripsi
                            </p>
                        </div>

                        <!-- Card Footer / Actions -->
                        <div class="mt-4 pt-3 border-t border-slate-50 flex items-center justify-between text-slate-400">
                            <span class="text-[10px] font-medium text-slate-400 truncate max-w-[120px]">
                                #{{ item.id }}
                            </span>
                            <div class="flex items-center gap-1">
                                <button @click="promoPamflet.openPamfletModal('edit', item)"
                                    class="table-action-button table-action-compact" aria-label="Edit">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </button>
                                <button @click="promoPamflet.confirmDeletePamflet(item)"
                                    class="table-action-button table-action-compact table-action-danger" aria-label="Hapus">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form Pamflet (Create / Edit) -->
    <div v-if="promoPamflet.showPamfletModal" class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
        <div @click="promoPamflet.closePamfletModal()" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop"></div>
        <div class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface relative z-10 flex flex-col max-h-[90vh]">
            <!-- Modal Header -->
            <div class="modal-header-bar modal-header-bar-sticky radius-sheet-top z-[2010]">
                <div class="modal-header-copy">
                    <div class="modal-header-icon bg-amber text-light border border-amber">
                        <i :class="['fa-solid text-body-sm', promoPamflet.pamfletModalMode === 'edit' ? 'fa-pen-to-square' : 'fa-plus']"></i>
                    </div>
                    <div>
                        <div class="type-heading-sm text-slate-900">{{ promoPamflet.pamfletModalMode === 'edit' ? 'Edit Promo Pamflet' : 'Tambah Promo Pamflet' }}</div>
                        <div class="type-body-sm text-slate-400 uppercase mt-0.5">Promo Pamflet</div>
                    </div>
                </div>
                <button @click="promoPamflet.closePamfletModal()" aria-label="Tutup modal" class="icon-utility-button icon-utility-round">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Modal Body Form -->
            <form @submit.prevent="promoPamflet.savePamflet()" class="p-6 space-y-4 overflow-y-auto flex-1">
                <div v-if="promoPamflet.pamfletError" class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-body-sm flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation shrink-0"></i>
                    <span>{{ promoPamflet.pamfletError }}</span>
                </div>

                <div>
                    <label for="pamflet-nama" class="type-body-sm font-bold text-slate-400 uppercase mb-2 block">
                        Nama Pamflet <span class="text-danger">*</span>
                    </label>
                    <input id="pamflet-nama" name="pamflet_nama" v-model="promoPamflet.pamfletForm.nama" type="text" required placeholder="Contoh: Promo Gajian Berkah"
                        class="form-input" />
                </div>

                <div>
                    <label for="pamflet-kategori" class="type-body-sm font-bold text-slate-400 uppercase mb-2 block">
                        Kategori <span class="text-danger">*</span>
                    </label>
                    <div class="flex gap-2">
                        <div class="flex-1 search-select-container">
                            <button type="button" @click="promoPamflet.openCategorySelect($event)"
                                class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form w-full">
                                <span :class="promoPamflet.pamfletForm.kategori ? 'text-slate-800 font-medium' : 'text-slate-400'">
                                    {{ promoPamflet.pamfletForm.kategori || 'Pilih kategori' }}
                                </span>
                                <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                            </button>
                        </div>
                        <button type="button" @click="promoPamflet.openCatModal('create')" class="primary-cta-button primary-cta-button--neutral" aria-label="Tambah Kategori">
                            <i class="fa-solid fa-plus text-xs"></i>
                        </button>
                    </div>
                </div>

                <teleport to="body">
                    <transition name="fade">
                        <div v-if="promoPamflet.categorySelectOpen" :style="promoPamflet.categorySelectStyle" class="search-select-popover">
                            <div class="relative mb-2">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                <input v-model="promoPamflet.categorySelectQuery" type="search" name="promo_pamflet_category_search"
                                    autocomplete="off" aria-label="Cari kategori pamflet" placeholder="Cari kategori..."
                                    class="form-input-popover" @click.stop />
                            </div>
                            <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                <button type="button" @click="promoPamflet.pamfletForm.kategori = ''; promoPamflet.categorySelectOpen = false"
                                    class="popover-option w-full text-left" :class="!promoPamflet.pamfletForm.kategori ? 'popover-option-active' : ''">
                                    Pilih kategori
                                </button>
                                <button type="button" v-for="cat in promoPamflet.filteredCategoryOptions" :key="cat.id"
                                    @click="promoPamflet.pamfletForm.kategori = cat.nama || cat.name || cat.id; promoPamflet.categorySelectOpen = false"
                                    :class="['popover-option w-full text-left', promoPamflet.pamfletForm.kategori === (cat.nama || cat.name || cat.id) ? 'popover-option-active' : '']">
                                    {{ cat.nama || cat.name }}
                                </button>
                                <div v-if="promoPamflet.filteredCategoryOptions.length === 0" class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                    Tidak ditemukan
                                </div>
                            </div>
                        </div>
                    </transition>
                </teleport>

                <div class="relative z-0">
                    <label class="type-body-sm font-bold text-slate-400 uppercase mb-2 block">
                        File Desain (Gambar) <span v-if="promoPamflet.pamfletModalMode === 'create'" class="text-danger">*</span>
                    </label>
                    <div class="border-2 border-dashed border-slate-200 hover:border-ppp-accent rounded-2xl p-4 text-center transition bg-slate-50/50 relative">
                        <input type="file" accept="image/*" @change="promoPamflet.handleFileChange($event)"
                            :disabled="promoPamflet.uploadingDesain || promoPamflet.submittingPamflet"
                            :required="promoPamflet.pamfletModalMode === 'create' && !promoPamflet.desainPreview"
                            class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10 disabled:cursor-not-allowed" />
                        <div v-if="promoPamflet.uploadingDesain" class="py-6 flex flex-col items-center justify-center gap-2">
                            <i class="fa-solid fa-spinner animate-spin text-2xl text-ppp-accent"></i>
                            <p class="text-body-sm font-semibold text-slate-700">Sedang memproses gambar...</p>
                            <p class="text-[11px] text-slate-400">{{ promoPamflet.desainFileName }} ({{ promoPamflet.desainFileSize }})</p>
                        </div>
                        <div v-else-if="promoPamflet.desainPreview" class="flex flex-col items-center">
                            <img :src="promoPamflet.desainPreview" alt="Preview" class="max-h-44 rounded-xl object-contain shadow-sm border border-slate-200 mb-2" />
                            <div class="flex items-center gap-1.5 text-[11px] font-semibold text-success">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Gambar siap disimpan</span>
                            </div>
                            <p v-if="promoPamflet.desainFileName" class="text-[10px] text-slate-400 mt-0.5">{{ promoPamflet.desainFileName }} ({{ promoPamflet.desainFileSize }})</p>
                            <p class="text-[10px] text-ppp-accent mt-1">Klik atau tarik file untuk mengganti</p>
                        </div>
                        <div v-else class="py-4">
                            <i class="fa-solid fa-cloud-arrow-up text-3xl text-slate-300 mb-2 block"></i>
                            <p class="text-body-sm font-medium text-slate-600">Klik untuk upload file desain</p>
                            <p class="text-[11px] text-slate-400 mt-1">PNG, JPG, JPEG, WEBP hingga 10MB</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="pamflet-deskripsi" class="type-body-sm font-bold text-slate-400 uppercase mb-2 block">Deskripsi</label>
                    <textarea id="pamflet-deskripsi" name="pamflet_deskripsi" v-model="promoPamflet.pamfletForm.deskripsi" rows="3" placeholder="Tuliskan keterangan promo atau syarat ketentuan..."
                        class="form-input resize-none"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="promoPamflet.closePamfletModal()" class="primary-cta-button primary-cta-button--neutral">
                        Batal
                    </button>
                    <button type="submit"
                        :disabled="promoPamflet.submittingPamflet || promoPamflet.uploadingDesain || (promoPamflet.pamfletModalMode === 'create' && (!promoPamflet.desainPreview || !promoPamflet.pamfletForm.desain))"
                        class="primary-cta-button primary-cta-button--accent disabled:opacity-50 disabled:cursor-not-allowed">
                        <i v-if="promoPamflet.submittingPamflet" class="fa-solid fa-spinner animate-spin text-xs"></i>
                        <span>{{ promoPamflet.submittingPamflet ? 'Menyimpan...' : promoPamflet.uploadingDesain ? 'Memproses Gambar...' : 'Simpan' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Kelola Kategori -->
    <div v-if="promoPamflet.showCatModal" class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
        <div @click="promoPamflet.closeCatModal()" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop"></div>
        <div class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface relative z-10 flex flex-col max-h-[90vh]">
            <!-- Modal Header -->
            <div class="modal-header-bar modal-header-bar-sticky radius-sheet-top z-[2010]">
                <div class="modal-header-copy">
                    <div class="modal-header-icon bg-secondary text-light border border-slate-200">
                        <i class="fa-solid fa-folder-tree text-body-sm"></i>
                    </div>
                    <div>
                        <div class="type-heading-sm text-slate-900">Kelola Kategori Promo</div>
                        <div class="type-body-sm text-slate-400 uppercase mt-0.5">Master Data Kategori</div>
                    </div>
                </div>
                <button @click="promoPamflet.closeCatModal()" aria-label="Tutup modal" class="icon-utility-button icon-utility-round">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-6 space-y-4 overflow-y-auto">
                <!-- Add / Edit Category Input Form -->
                <form @submit.prevent="promoPamflet.saveCategory()" class="space-y-2">
                    <label for="cat-nama" class="type-body-sm font-bold text-slate-400 uppercase mb-2 block">
                        {{ promoPamflet.catModalMode === 'edit' ? 'Edit Nama Kategori' : 'Tambah Kategori Baru' }}
                    </label>
                    <div class="flex gap-2">
                        <input id="cat-nama" name="cat_nama" v-model="promoPamflet.catForm.nama" type="text" required placeholder="Nama kategori..."
                            class="form-input flex-1" />
                        <button type="submit" :disabled="promoPamflet.submittingCategory" class="primary-cta-button primary-cta-button--accent">
                            <i v-if="promoPamflet.submittingCategory" class="fa-solid fa-spinner animate-spin text-xs"></i>
                            <span>{{ promoPamflet.catModalMode === 'edit' ? 'Update' : 'Tambah' }}</span>
                        </button>
                        <button v-if="promoPamflet.catModalMode === 'edit'" type="button" @click="promoPamflet.openCatModal('create')"
                            class="primary-cta-button primary-cta-button--neutral">
                            Batal
                        </button>
                    </div>
                    <p v-if="promoPamflet.catError" class="text-danger text-[11px] font-medium mt-1">{{ promoPamflet.catError }}</p>
                </form>

                <!-- Category List -->
                <div class="pt-3 border-t border-slate-100">
                    <p class="type-body-sm font-bold text-slate-400 uppercase mb-2">Daftar Kategori Saat Ini</p>
                    <div class="max-h-56 overflow-y-auto custom-scrollbar divide-y divide-slate-100 rounded-xl border border-slate-100">
                        <div v-if="promoPamflet.categories.length === 0" class="p-4 text-center text-slate-400 text-body-sm">
                            Belum ada kategori
                        </div>
                        <div v-for="cat in promoPamflet.categories" :key="cat.id" class="p-3 flex items-center justify-between hover:bg-slate-50 transition">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-folder text-amber text-xs"></i>
                                <span class="text-body-sm font-medium text-slate-800">{{ cat.nama || cat.name }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button @click="promoPamflet.openCatModal('edit', cat)" class="table-action-button table-action-compact" aria-label="Edit">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </button>
                                <button @click="promoPamflet.confirmDeleteCat(cat)" class="table-action-button table-action-compact table-action-danger" aria-label="Hapus">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="button" @click="promoPamflet.closeCatModal()" class="primary-cta-button primary-cta-button--neutral">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail / Lightbox Preview -->
    <div v-if="promoPamflet.showDetailModal && promoPamflet.selectedPamflet" class="fixed inset-0 z-[2000] flex items-center justify-center p-4 overlay-motion-sheet" @click.self="promoPamflet.closeDetail()">
        <div @click="promoPamflet.closeDetail()" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop"></div>
        <div class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface relative z-10 flex flex-col max-h-[90vh]">
            <div class="modal-header-bar modal-header-bar-sticky radius-sheet-top z-[2010]">
                <div class="modal-header-copy">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-ppp-accent text-white mr-2">
                        {{ promoPamflet.selectedPamflet.kategori || promoPamflet.selectedPamflet.category || 'Promo' }}
                    </span>
                    <div class="type-heading-sm text-slate-900 truncate">
                        {{ promoPamflet.selectedPamflet.nama }}
                    </div>
                </div>
                <button @click="promoPamflet.closeDetail()" aria-label="Tutup modal" class="icon-utility-button icon-utility-round">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 flex flex-col items-center space-y-4">
                <div class="w-full flex items-center justify-center bg-slate-900/5 rounded-2xl p-2 max-h-[55vh] overflow-hidden">
                    <img v-if="promoPamflet.selectedPamflet.desain_url || promoPamflet.selectedPamflet.desain || promoPamflet.selectedPamflet.image_url"
                        :src="promoPamflet.selectedPamflet.desain_url || promoPamflet.selectedPamflet.desain || promoPamflet.selectedPamflet.image_url"
                        :alt="promoPamflet.selectedPamflet.nama"
                        class="max-h-[50vh] w-auto max-w-full object-contain rounded-xl shadow-sm" />
                    <div v-else class="py-12 text-slate-300 flex flex-col items-center">
                        <i class="fa-solid fa-image text-5xl mb-2"></i>
                        <span class="text-body-sm">Tidak ada gambar</span>
                    </div>
                </div>

                <div class="w-full space-y-2 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <div class="flex items-center justify-between text-[11px] text-slate-400">
                        <span>ID Pamflet: #{{ promoPamflet.selectedPamflet.id }}</span>
                        <a v-if="promoPamflet.selectedPamflet.desain_url || promoPamflet.selectedPamflet.desain || promoPamflet.selectedPamflet.image_url"
                            :href="promoPamflet.selectedPamflet.desain_url || promoPamflet.selectedPamflet.desain || promoPamflet.selectedPamflet.image_url"
                            target="_blank" download class="text-ppp-accent font-semibold hover:underline flex items-center gap-1">
                            <i class="fa-solid fa-download text-[10px]"></i> Download Gambar
                        </a>
                    </div>
                    <div>
                        <p class="type-body-sm font-bold text-slate-400 uppercase">Deskripsi / Keterangan</p>
                        <p class="text-body-sm text-slate-700 whitespace-pre-line mt-1">
                            {{ promoPamflet.selectedPamflet.deskripsi || 'Tidak ada deskripsi' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-3 border-t border-slate-100 flex items-center justify-end gap-2 shrink-0 bg-slate-50/50">
                <button @click="promoPamflet.openPamfletModal('edit', promoPamflet.selectedPamflet); promoPamflet.closeDetail()"
                    class="primary-cta-button primary-cta-button--neutral">
                    <i class="fa-solid fa-pen-to-square text-xs mr-1"></i> Edit
                </button>
                <button @click="promoPamflet.closeDetail()" class="primary-cta-button primary-cta-button--accent">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div v-if="promoPamflet.showDeleteModal" class="fixed inset-0 z-[2000] flex items-center justify-center p-4 overlay-motion-sheet">
        <div @click="promoPamflet.showDeleteModal = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop"></div>
        <div class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface relative z-10 p-6 text-center space-y-4 max-w-sm">
            <div class="mx-auto w-12 h-12 rounded-2xl bg-red-50 text-danger flex items-center justify-center text-xl">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <div class="type-heading-sm text-slate-900">
                    Hapus {{ promoPamflet.deleteType === 'category' ? 'Kategori' : 'Pamflet' }}?
                </div>
                <p class="text-body-sm text-slate-500 mt-1">
                    Apakah Anda yakin ingin menghapus <strong class="text-slate-800">{{ promoPamflet.deleteTarget?.nama || promoPamflet.deleteTarget?.name || 'item ini' }}</strong>? Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>
            <div class="flex items-center justify-center gap-2 pt-2">
                <button type="button" @click="promoPamflet.showDeleteModal = false" class="primary-cta-button primary-cta-button--neutral flex-1">
                    Batal
                </button>
                <button type="button" @click="promoPamflet.executeDelete()" :disabled="promoPamflet.deleting"
                    class="primary-cta-button primary-cta-button--danger flex-1">
                    <i v-if="promoPamflet.deleting" class="fa-solid fa-spinner animate-spin text-xs"></i>
                    <span>{{ promoPamflet.deleting ? 'Menghapus...' : 'Hapus' }}</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endverbatim
