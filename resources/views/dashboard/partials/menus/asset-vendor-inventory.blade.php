@verbatim
<!-- Asset Vendor Inventory tab -->
            <div v-if="activeTab === 'asset_vendor_inventory' && !tabDataLoaded['assetVendorInventory']"
                class="space-y-6 animate-fadeIn pb-10 animate-pulse">
                <div class="section-card section-card-body">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-slate-200"></div>
                            <div class="space-y-2">
                                <div class="h-5 bg-slate-200 rounded-full w-40"></div>
                                <div class="h-3 bg-slate-100 rounded-full w-56"></div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <div class="h-9 w-24 bg-slate-100 rounded-xl"></div>
                            <div class="h-9 w-28 bg-slate-200 rounded-xl"></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="h-9 flex-1 bg-slate-100 rounded-xl"></div>
                        <div class="h-9 w-28 bg-slate-100 rounded-xl"></div>
                    </div>
                </div>
                <div class="section-card section-card-shell">
                    <div class="px-6 py-4 border-b border-slate-50 flex gap-6">
                        <div class="h-3 bg-slate-200 rounded-full w-32"></div>
                        <div class="h-3 bg-slate-200 rounded-full w-28"></div>
                        <div class="h-3 bg-slate-200 rounded-full w-20"></div>
                        <div class="h-3 bg-slate-200 rounded-full w-24"></div>
                        <div class="h-3 bg-slate-200 rounded-full flex-1"></div>
                    </div>
                    <div class="divide-y divide-slate-50">
                        <div v-for="i in 8" :key="'sk-avi'+i" class="px-6 py-5 flex items-center gap-4">
                            <div class="h-4 bg-slate-100 rounded-full w-36"></div>
                            <div class="h-4 bg-slate-100 rounded-full w-24"></div>
                            <div class="h-4 bg-slate-100 rounded-full w-28"></div>
                            <div class="h-4 bg-slate-100 rounded-full w-20"></div>
                            <div class="h-4 bg-slate-100 rounded-full flex-1"></div>
                            <div class="flex gap-1">
                                <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                                <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="activeTab === 'asset_vendor_inventory' && tabDataLoaded['assetVendorInventory']"
                class="space-y-6 animate-fadeIn pb-10">
                <!-- Summary cards -->
                <div class="space-y-3">
                    <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                        <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                            <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i class="fa-solid fa-cubes text-[120px]"></i></div>
                            <p class="text-[9px] font-bold uppercase tracking-widest mb-3 text-blue-500">Total Asset</p>
                            <div class="flex items-baseline gap-2">
                                <span class="dashboard-summary-value">{{ formatNumber(aviData.length) }}</span>
                                <span class="dashboard-summary-unit text-blue-400">Item</span>
                            </div>
                            <p class="text-[10px] font-bold mt-3 text-blue-600">Semua data inventory</p>
                        </div>
                        <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                            <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i class="fa-solid fa-tag text-[120px]"></i></div>
                            <p class="text-[9px] font-bold uppercase tracking-widest mb-3 text-violet-500">Unique Vendor</p>
                            <div class="flex items-baseline gap-2">
                                <span class="dashboard-summary-value">{{ formatNumber(aviUniqueVendors) }}</span>
                                <span class="dashboard-summary-unit text-violet-400">Vendor</span>
                            </div>
                            <p class="text-[10px] font-bold mt-3 text-violet-600">Supplier</p>
                        </div>
                        <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                            <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i class="fa-solid fa-mobile-screen text-[120px]"></i></div>
                            <p class="text-[9px] font-bold uppercase tracking-widest mb-3 text-emerald-500">Vendor Brand</p>
                            <div class="flex items-baseline gap-2">
                                <span class="dashboard-summary-value">{{ formatNumber(aviUniqueBrands) }}</span>
                                <span class="dashboard-summary-unit text-emerald-400">Brand</span>
                            </div>
                            <p class="text-[10px] font-bold mt-3 text-emerald-600">Unique brand</p>
                        </div>
                        <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                            <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i class="fa-solid fa-boxes-stacked text-[120px]"></i></div>
                            <p class="text-[9px] font-bold uppercase tracking-widest mb-3 text-amber-500">Total Quantity</p>
                            <div class="flex items-baseline gap-2">
                                <span class="dashboard-summary-value">{{ formatNumber(aviTotalQuantity) }}</span>
                                <span class="dashboard-summary-unit text-amber-400">Unit</span>
                            </div>
                            <p class="text-[10px] font-bold mt-3 text-amber-600">Akumulasi</p>
                        </div>
                    </div>
                </div>

                <section class="section-card section-card-body">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 md:gap-5">
                        <div class="modal-header-copy">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                                <i class="fa-solid fa-cubes text-[16px]"></i>
                            </div>
                            <div>
                                <h2 class="type-title font-bold text-slate-900">Asset Vendor Inventory</h2>
                                <p class="text-[10px] text-slate-400 uppercase tracking-widest mt-0.5">Data inventory dari vendor &amp; supplier</p>
                            </div>
                        </div>
                        <div class="mobile-toolbar-stack">
                            <div class="relative flex-1 sm:w-44">
                                <i
                                    class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                                <input id="avi-search" name="avi_search" v-model="aviSearch" type="text" placeholder="Cari vendor / brand / seri..."
                                    autocomplete="off" aria-label="Cari asset vendor inventory"
                                    class="form-input-search" />
                            </div>
                            <div class="toolbar-actions">
                                <button @click="openAviModal('create')"
                                    class="primary-cta-button primary-cta-button--accent active:scale-95"><i
                                        class="fa-solid fa-plus"></i> Tambah</button>
                            </div>
                        </div>
                    </div>
                    <div v-if="aviSummaryChips.length" class="chips-grid mt-4 pt-4 border-t border-slate-100">
                        <span v-for="ch in aviSummaryChips" :key="ch.label" :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold', ch.cls]">{{ ch.label }} <span class="opacity-50">&middot;</span> {{ ch.n }}</span>
                    </div>
                </section>
                <div class="md:hidden space-y-3">
                    <div class="space-y-3">
                        <div v-if="pagedAviData.length === 0"
                            class="bg-white radius-card border border-slate-100 p-10 text-center text-[11px] text-slate-400">
                            Belum ada data inventory
                        </div>
                        <div v-for="(row, idx) in pagedAviData" :key="'avi-mobile-' + (row.ID || idx)"
                            class="stat-card mobile-record-card mobile-data-card motion-stagger-item"
                            :style="getStaggerStyle(idx)">
                            <div class="mobile-data-card__header">
                                <span class="px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700">
                                    {{ row.vendor || '-' }}
                                </span>
                                <span class="type-meta text-slate-400 font-bold uppercase tracking-widest">
                                    {{ row.brand || '-' }}
                                </span>
                            </div>
                            <div>
                                <p class="mobile-data-card__title line-clamp-2">{{ row.seri || '-' }}</p>
                                <div class="mobile-data-card__meta mt-2">
                                    <span v-if="row.imei" class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-[9px] font-bold uppercase">
                                        {{ row.imei }}
                                    </span>
                                    <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 text-[9px] font-bold uppercase">
                                        {{ row.quantity || 0 }}x
                                    </span>
                                </div>
                            </div>
                            <div class="mobile-data-card__summary">
                                <div>
                                    <div class="type-meta text-slate-400 uppercase">Kondisi</div>
                                    <div class="type-body font-bold text-slate-700">{{ row.condition || '-' }}</div>
                                </div>
                                <div>
                                    <div class="type-meta text-slate-400 uppercase">{{ row.purchase_date ? formatShortDate(row.purchase_date) : '-' }}</div>
                                </div>
                            </div>
                            <div class="mobile-data-card__actions">
                                <div class="type-meta font-bold text-slate-400">
                                    #{{ row.source_id || '-' }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="openAviModal('edit', row)"
                                        class="table-action-button table-action-compact" title="Edit"
                                        aria-label="Edit"><i class="fa-solid fa-pen text-[10px]"></i></button>
                                    <button @click="deleteAvi(row.ID)"
                                        class="table-action-button table-action-compact table-action-danger"
                                        title="Hapus" aria-label="Hapus"><i
                                            class="fa-solid fa-trash text-[10px]"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center gap-2 py-2">
                        <button @click="aviPage = 1" :disabled="aviPage <= 1"
                            aria-label="Halaman pertama" class="icon-utility-button icon-utility-bordered"><i
                                class="fa-solid fa-angles-left text-[10px]"></i></button>
                        <button @click="aviPage--" :disabled="aviPage <= 1"
                            aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered"><i
                                class="fa-solid fa-chevron-left text-[10px]"></i></button>
                        <span class="px-3 text-[10px] font-bold text-ppp-accent">{{ aviPage }} /
                            {{ aviTotalPages }}</span>
                        <button @click="aviPage++" aria-label="Halaman berikutnya"
                            :disabled="aviPage >= aviTotalPages"
                            class="icon-utility-button icon-utility-bordered"><i
                                class="fa-solid fa-chevron-right text-[10px]"></i></button>
                        <button @click="aviPage = aviTotalPages" aria-label="Halaman terakhir"
                            :disabled="aviPage >= aviTotalPages"
                            class="icon-utility-button icon-utility-bordered"><i
                                class="fa-solid fa-angles-right text-[10px]"></i></button>
                    </div>
                </div>
                <div class="hidden md:block section-card section-card-shell">
                    <div class="overflow-x-auto">
                        <table class="w-full text-[10.5px] text-left border-collapse min-w-[900px]">
                            <thead>
                                <tr
                                    class="border-b border-slate-100 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                                    <th class="px-4 py-3 text-center w-12">#</th>
                                    <th class="px-4 py-3">Vendor</th>
                                    <th class="px-4 py-3">Brand</th>
                                    <th class="px-4 py-3">Seri</th>
                                    <th class="px-4 py-3">IMEI</th>
                                    <th class="px-4 py-3 text-center w-20">Qty</th>
                                    <th class="px-4 py-3 text-center w-24">Kondisi</th>
                                    <th class="px-4 py-3 text-center w-24">Tgl Beli</th>
                                    <th class="px-4 py-3 text-center w-20">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="(row, idx) in pagedAviData" :key="'avi-' + (row.ID || idx)"
                                    class="hover:bg-slate-50/50 transition-colors duration-150">
                                    <td class="px-4 py-3 text-center text-slate-400 font-mono text-[10px]">{{ (aviPage - 1) * 20 + idx + 1 }}</td>
                                    <td class="px-4 py-3 font-medium text-slate-700">{{ row.vendor || '-' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ row.brand || '-' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ row.seri || '-' }}</td>
                                    <td class="px-4 py-3 font-mono text-[10px] text-slate-500">{{ row.imei || '-' }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-slate-700">{{ row.quantity || 0 }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span :class="['px-2 py-0.5 rounded-full text-[9px] font-bold uppercase', row.condition === 'New' ? 'bg-emerald-50 text-emerald-700' : row.condition === 'Used' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-500']">
                                            {{ row.condition || '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-500">{{ row.purchase_date ? formatShortDate(row.purchase_date) : '-' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-1">
                                            <button @click="openAviModal('edit', row)"
                                                class="table-action-button table-action-compact" title="Edit"
                                                aria-label="Edit"><i class="fa-solid fa-pen text-[10px]"></i></button>
                                            <button @click="deleteAvi(row.ID)"
                                                class="table-action-button table-action-compact table-action-danger"
                                                title="Hapus" aria-label="Hapus"><i
                                                    class="fa-solid fa-trash text-[10px]"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="hidden md:flex items-center justify-center gap-2 py-3">
                    <button @click="aviPage = 1" :disabled="aviPage <= 1"
                        aria-label="Halaman pertama" class="icon-utility-button icon-utility-bordered"><i
                            class="fa-solid fa-angles-left text-[10px]"></i></button>
                    <button @click="aviPage--" :disabled="aviPage <= 1"
                        aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered"><i
                            class="fa-solid fa-chevron-left text-[10px]"></i></button>
                    <span class="px-3 text-[10px] font-bold text-ppp-accent">{{ aviPage }} /
                        {{ aviTotalPages }}</span>
                    <button @click="aviPage++" aria-label="Halaman berikutnya"
                        :disabled="aviPage >= aviTotalPages"
                        class="icon-utility-button icon-utility-bordered"><i
                            class="fa-solid fa-chevron-right text-[10px]"></i></button>
                    <button @click="aviPage = aviTotalPages" aria-label="Halaman terakhir"
                        :disabled="aviPage >= aviTotalPages"
                        class="icon-utility-button icon-utility-bordered"><i
                            class="fa-solid fa-angles-right text-[10px]"></i></button>
                </div>

                <!-- Modal Form -->
                <teleport to="body">
                    <transition name="fade">
                        <div v-if="aviModalOpen"
                            class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                            <div @click="aviModalOpen = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop"></div>
                            <div
                                class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface">
                                <div class="modal-header-bar radius-sheet-top">
                                    <div class="modal-header-copy">
                                        <div
                                            class="modal-header-icon bg-emerald-50 text-emerald-600">
                                            <i class="fa-solid fa-cubes"></i>
                                        </div>
                                        <div>
                                            <div class="type-title font-bold text-slate-800">{{ aviModalType === 'create' ? 'Tambah Asset' : 'Edit Asset' }}</div>
                                            <div class="type-meta text-slate-400">Vendor inventory barang</div>
                                        </div>
                                    </div>
                                    <button @click="aviModalOpen = false" class="icon-utility-button icon-utility-danger"><i
                                            class="fa-solid fa-xmark text-sm"></i></button>
                                </div>
                                <div class="flex-1 overflow-y-auto p-6 space-y-4">
                                    <div>
                                        <label for="avi-vendor" class="type-meta font-bold text-slate-400 uppercase mb-1.5">Vendor</label>
                                        <input id="avi-vendor" name="avi_vendor" v-model="aviForm.vendor" type="text" class="form-input-compact"
                                            placeholder="Nama vendor / supplier" />
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label for="avi-brand" class="type-meta font-bold text-slate-400 uppercase mb-1.5">Brand</label>
                                            <input id="avi-brand" name="avi_brand" v-model="aviForm.brand" type="text" class="form-input-compact"
                                                placeholder="Brand" />
                                        </div>
                                        <div>
                                            <label for="avi-seri" class="type-meta font-bold text-slate-400 uppercase mb-1.5">Seri</label>
                                            <input id="avi-seri" name="avi_seri" v-model="aviForm.seri" type="text" class="form-input-compact"
                                                placeholder="Tipe / seri" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-3">
                                        <div>
                                            <label for="avi-imei" class="type-meta font-bold text-slate-400 uppercase mb-1.5">IMEI</label>
                                            <input id="avi-imei" name="avi_imei" v-model="aviForm.imei" type="text" class="form-input-compact"
                                                placeholder="IMEI" />
                                        </div>
                                        <div>
                                            <label for="avi-quantity" class="type-meta font-bold text-slate-400 uppercase mb-1.5">Quantity</label>
                                            <input id="avi-quantity" name="avi_quantity" v-model.number="aviForm.quantity" type="number" min="1"
                                                class="form-input-compact" />
                                        </div>
                                        <div>
                                            <label for="avi-condition" class="type-meta font-bold text-slate-400 uppercase mb-1.5">Kondisi</label>
                                            <div class="relative search-select-container">
                                                <button type="button" @click="toggleSearchSelect($event, 'avi_condition')"
                                                    class="select-trigger-button toolbar-trigger-field">
                                                    <span class="truncate">{{ aviForm.condition || 'Pilih Kondisi' }}</span>
                                                    <i class="fa-solid fa-chevron-down text-[9px] text-slate-400"></i>
                                                </button>
                                                <div v-if="searchSelectOpen === 'avi_condition'" :style="popoverStyle"
                                                    class="search-select-popover search-select-popover--compact max-h-60 overflow-y-auto">
                                                    <div v-for="c in aviConditionOptions" :key="c"
                                                        @click="aviForm.condition = c; searchSelectOpen = null"
                                                        class="popover-option">{{ c }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">Tanggal Pembelian</label>
                                        <button @click="openCalendar($event, 'form', '', 'aviTanggal')"
                                            class="date-trigger-button toolbar-trigger-field">
                                            <i class="fa-solid fa-calendar-days text-[10px] text-slate-400"></i>
                                            <span :class="aviForm.purchase_date ? 'text-slate-700 font-medium' : 'text-slate-400'">{{ aviForm.purchase_date || 'Pilih tanggal' }}</span>
                                        </button>
                                    </div>
                                    <div>
                                        <label for="avi-notes" class="type-meta font-bold text-slate-400 uppercase mb-1.5">Catatan</label>
                                        <textarea id="avi-notes" name="avi_notes" v-model="aviForm.notes" rows="2" class="form-input-compact resize-none"
                                            placeholder="Catatan tambahan..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer-bar modal-footer-actions">
                                    <button @click="aviModalOpen = false" class="modal-secondary-button">Batal</button>
                                    <button @click="saveAvi" :disabled="submitting" class="modal-primary-button">
                                        <i v-if="submitting" class="fa-solid fa-circle-notch fa-spin"></i>
                                        {{ submitting ? 'Menyimpan...' : 'Simpan' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </transition>
                </teleport>
            </div>
@endverbatim
