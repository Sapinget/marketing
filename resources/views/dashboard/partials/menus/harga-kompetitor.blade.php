@verbatim
<!-- Harga & Kompetitor tab -->
            <div v-if="activeTab === 'harga_kompetitor' && !tabDataLoaded['hargaKompetitor']"
                class="space-y-4 animate-fadeIn animate-pulse">
                <div class="section-card section-card-shell">
                    <div class="px-6 py-4 border-b border-slate-50 flex gap-6">
                        <div class="h-3 bg-slate-200 rounded-full w-28"></div>
                        <div class="h-3 bg-slate-200 rounded-full w-24"></div>
                        <div class="h-3 bg-slate-200 rounded-full w-20"></div>
                        <div class="h-3 bg-slate-200 rounded-full flex-1"></div>
                    </div>
                    <div class="divide-y divide-slate-50">
                        <div v-for="i in 8" :key="'sk-hk'+i" class="px-6 py-5 flex items-center gap-4">
                            <div class="h-4 bg-slate-100 rounded-full w-44"></div>
                            <div class="h-4 bg-slate-100 rounded-full w-24"></div>
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
            <div v-if="activeTab === 'harga_kompetitor' && tabDataLoaded['hargaKompetitor']"
                class="space-y-4 animate-fadeIn">
                <!-- Summary cards -->
                <div class="space-y-3">
                    <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
                        <div v-for="c in hargaSummary.cards.slice(0, 5)" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                            <div class="absolute -right-4 -bottom-4 opacity-5"><i :class="['fa-solid', c.icon, 'text-[120px]']"></i></div>
                            <p class="dashboard-summary-title">{{ c.label }}</p>
                            <div class="flex items-baseline gap-2">
                                <span class="dashboard-summary-value">{{ c.value }}</span>
                                <span v-if="c.unit" class="dashboard-summary-unit">{{ c.unit }}</span>
                            </div>
                            <p :class="['text-body-sm font-bold mt-3', c.subColor]">{{ c.sub }}</p>
                        </div>
                    </div>
                </div>


                <div class="md:hidden space-y-3">
                    <div class="space-y-3">
                        <div v-if="pagedHargaKompetitorData.length === 0"
                            class="bg-white radius-card border border-slate-100 p-10 text-center text-body text-slate-400">
                            Belum ada data harga
                        </div>
                        <div v-for="(row, idx) in pagedHargaKompetitorData" :key="'hk-mobile-' + (row.ID || idx)"
                            class="stat-card mobile-record-card mobile-data-card motion-stagger-item"
                            :style="getStaggerStyle(idx)">
                            <div class="mobile-data-card__header">
                                <span
                                    class="px-2.5 py-1 rounded-full text-overline font-bold uppercase bg-amber text-light">
                                    {{ row.Tanggal_Cek ? formatShortDate(row.Tanggal_Cek) : '-' }}
                                </span>
                                <span class="type-body-sm text-slate-400 font-bold uppercase">
                                    Harga
                                </span>
                            </div>
                            <div>
                                <p class="mobile-data-card__title line-clamp-2">{{ row.SERI || row.Nama_Produk || '-' }}</p>
                                <p class="text-overline font-bold text-slate-400 uppercase mt-0.5">{{ [row.BRAND, row.RAM, row.INTERNAL, row.SIZE, row.WARNA].filter(Boolean).join(' / ') || row.KATEGORI || '-' }}</p>
                                <div class="mobile-data-card__meta mt-2">
                                    <span
                                        class="px-2.5 py-1 rounded-lg bg-secondary text-light text-overline font-bold uppercase">
                                        Kompetitor
                                    </span>
                                    <span
                                        class="px-2.5 py-1 rounded-lg bg-success text-light text-overline font-bold uppercase">
                                        Jual
                                    </span>
                                </div>
                            </div>
                            <div class="mobile-data-card__summary">
                                <div>
                                    <div class="type-body-sm text-slate-400 uppercase">Kompetitor</div>
                                    <div class="type-body font-bold text-slate-700">{{ formatCurrency(row.Harga_Kompetitor) }}</div>
                                </div>
                                <div>
                                    <div class="type-body-sm text-slate-400 uppercase">Rencana</div>
                                    <div class="type-body font-bold text-amber">{{ formatCurrency(row.Harga_Rencana_Jual) }}</div>
                                </div>
                            </div>
                            <div class="mobile-data-card__actions">
                                <div class="type-body-sm font-bold"
                                    :class="(row.Selisih || 0) >= 0 ? 'text-success' : 'text-danger'">
                                    {{ formatCurrency(row.Selisih) }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="openHargaKompetitorModal('edit', row)"
                                        class="table-action-button table-action-compact" title="Edit"
                                        aria-label="Edit"><i class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                                    <button @click="deleteHargaKompetitor(row.ID)"
                                        class="table-action-button table-action-compact table-action-danger"
                                        title="Hapus" aria-label="Hapus"><i
                                            class="fa-solid fa-trash-can text-body-sm"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center gap-2 py-2">
                        <button @click="hargaKompetitorPage = 1" :disabled="hargaKompetitorPage <= 1"
                            aria-label="Halaman pertama" class="icon-utility-button icon-utility-bordered"><i
                                class="fa-solid fa-angles-left text-body-sm"></i></button>
                        <button @click="hargaKompetitorPage--" :disabled="hargaKompetitorPage <= 1"
                            aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered"><i
                                class="fa-solid fa-chevron-left text-body-sm"></i></button>
                        <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ hargaKompetitorPage }} /
                            {{ hargaKompetitorTotalPages }}</span>
                        <button @click="hargaKompetitorPage++" aria-label="Halaman berikutnya"
                            :disabled="hargaKompetitorPage >= hargaKompetitorTotalPages"
                            class="icon-utility-button icon-utility-bordered"><i
                                class="fa-solid fa-chevron-right text-body-sm"></i></button>
                        <button @click="hargaKompetitorPage = hargaKompetitorTotalPages" aria-label="Halaman terakhir"
                            :disabled="hargaKompetitorPage >= hargaKompetitorTotalPages"
                            class="icon-utility-button icon-utility-bordered"><i
                                class="fa-solid fa-angles-right text-body-sm"></i></button>
                    </div>
                </div>
                <div class="hidden md:block section-card section-card-shell">
                    <div class="table-toolbar-shell">
                        <div class="table-toolbar-shell__left">
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                                <input id="harga-kompetitor-search" name="harga_kompetitor_search" v-model="hargaKompetitorSearch" type="text" placeholder="Cari produk..."
                                    autocomplete="off" aria-label="Cari produk harga kompetitor"
                                    class="form-input-search" />
                            </div>
                        </div>
                        <div class="table-toolbar-shell__right">
                            <div class="relative group search-select-container">
                                <button @click="openCalendar($event, 'filter', '', 'hargaKompetitor')"
                                    class="select-trigger-button-compact">
                                    <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                    <template v-if="hargaKompetitorDateFilter.start">
                                        {{ formatShortDate(hargaKompetitorDateFilter.start) }}
                                        <span v-if="hargaKompetitorDateFilter.end"> - {{ formatShortDate(hargaKompetitorDateFilter.end) }}</span>
                                    </template>
                                    <template v-else>Semua Tanggal</template>
                                    <i v-if="hargaKompetitorDateFilter.start"
                                        @click.stop="hargaKompetitorDateFilter = {start:'',end:''}"
                                        class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-danger"></i>
                                </button>
                            </div>
                            <div class="toolbar-actions toolbar-actions--desktop-icon-only">
<button @click="openHargaKompetitorModal('create')"
                                     class="primary-cta-button primary-cta-button--accent primary-cta-button--icon-only active:scale-95"
                                     aria-label="Tambah Harga Kompetitor"><i
                                         class="fa-solid fa-plus"></i></button>
                                <button @click="exportPriceComparisonToPDF"
                                    class="primary-cta-button primary-cta-button--danger primary-cta-button--icon-only active:scale-95" aria-label="Export PDF"><i
                                        class="fa-solid fa-file-pdf"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-body-sm text-left border-collapse min-w-[1180px]">
                            <thead>
                                <tr class="table-header-row">
                                    <th class="table-header-cell table-header-index table-freeze-index">#</th>
                                    <th class="table-header-cell table-header-action table-freeze-action">Aksi</th>
                                    <th class="table-header-cell">Produk</th>
                                    <th class="table-header-cell text-center w-28">Brand</th>
                                    <th class="table-header-cell text-center w-40">Spesifikasi</th>
                                    <th class="table-header-cell text-center w-28">Tanggal Cek</th>
                                    <th class="table-header-cell text-center w-28">Dist 1</th>
                                    <th class="table-header-cell text-center w-28">Dist 2</th>
                                    <th class="table-header-cell text-center w-28">Kompetitor</th>
                                    <th class="table-header-cell text-center w-28">Rencana Jual</th>
                                    <th class="table-header-cell text-center w-24">Margin</th>
                                    <th class="table-header-cell text-center w-24">Selisih</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="pagedHargaKompetitorData.length === 0">
                                    <td colspan="12" class="px-4 py-12 text-center text-body text-slate-400">
                                        Belum ada data harga</td>
                                </tr>
                                <tr v-for="(row, idx) in pagedHargaKompetitorData" :key="row.ID"
                                    class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 text-center text-body text-slate-400 table-freeze-index">{{ (hargaKompetitorPage - 1) * 15 + idx + 1 }}</td>
                                    <td class="px-4 py-3 table-freeze-action">
                                        <div class="flex items-center gap-1.5">
                                            <button @click="openHargaKompetitorModal('edit', row)"
                                                class="table-action-button table-action-compact" title="Edit"
                                                aria-label="Edit"><i class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                                            <button @click="deleteHargaKompetitor(row.ID)"
                                                class="table-action-button table-action-compact table-action-danger"
                                                title="Hapus" aria-label="Hapus"><i
                                                    class="fa-solid fa-trash-can text-body-sm"></i></button>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-slate-800 uppercase text-body">{{ row.SERI || row.Nama_Produk }}</div>
                                        <div class="type-body-sm text-slate-400 mt-0.5">{{ row.KATEGORI || '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center text-body font-bold text-slate-600">{{ row.BRAND || '-' }}</td>
                                    <td class="px-4 py-3 text-center text-body text-slate-500">
                                        <div class="font-semibold text-slate-700">{{ [row.RAM, row.INTERNAL, row.SIZE].filter(Boolean).join(' / ') || '-' }}</div>
                                        <div v-if="row.WARNA" class="type-body-sm text-slate-400 mt-0.5">{{ row.WARNA }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center text-body text-slate-500">{{ formatShortDate(row.Tanggal_Cek) }}</td>
                                    <td class="px-4 py-3 text-center text-body font-bold text-slate-600">{{ formatCurrency(row.Harga_Distributor_1) }}</td>
                                    <td class="px-4 py-3 text-center text-body font-bold text-slate-600">{{ formatCurrency(row.Harga_Distributor_2) }}</td>
                                    <td class="px-4 py-3 text-center text-body font-bold text-slate-600">{{ formatCurrency(row.Harga_Kompetitor) }}</td>
                                    <td class="px-4 py-3 text-center text-body font-bold text-amber">{{ formatCurrency(row.Harga_Rencana_Jual) }}</td>
                                    <td class="px-4 py-3 text-center text-body font-bold text-slate-600">{{ formatCurrency(row.Margin_Profit) }}</td>
                                    <td class="px-4 py-3 text-center text-body font-bold"
                                        :class="(row.Selisih || 0) >= 0 ? 'text-success' : 'text-danger'">
                                        {{ formatCurrency(row.Selisih) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-pager-bar">
                        <span class="text-body-sm font-bold text-slate-400">{{ filteredHargaKompetitorData.length }} data</span>
                        <div class="flex items-center gap-1">
                            <button @click="hargaKompetitorPage = 1" :disabled="hargaKompetitorPage <= 1"
                                class="icon-utility-button icon-utility-bordered"><i
                                    class="fa-solid fa-angles-left text-body-sm"></i></button>
                            <button @click="hargaKompetitorPage--" :disabled="hargaKompetitorPage <= 1"
                                aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered"><i
                                    class="fa-solid fa-chevron-left text-body-sm"></i></button>
                            <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ hargaKompetitorPage }} /
                                {{ hargaKompetitorTotalPages }}</span>
                            <button @click="hargaKompetitorPage++"
                                :disabled="hargaKompetitorPage >= hargaKompetitorTotalPages"
                                aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered"><i
                                    class="fa-solid fa-chevron-right text-body-sm"></i></button>
                            <button @click="hargaKompetitorPage = hargaKompetitorTotalPages"
                                :disabled="hargaKompetitorPage >= hargaKompetitorTotalPages"
                                class="icon-utility-button icon-utility-bordered"><i
                                    class="fa-solid fa-angles-right text-body-sm"></i></button>
                        </div>
                    </div>
                </div>
            </div>

    <!-- Harga Kompetitor Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="hargaKompetitorModalOpen"
                class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                <div @click="hargaKompetitorModalOpen = false"
                    class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop"></div>
                <div
                    class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface">
                    <div class="modal-header-bar radius-sheet-top">
                        <div class="modal-header-copy">
                            <div
                                class="modal-header-icon bg-amber text-light">
                                <i class="fa-solid fa-calculator"></i>
                            </div>
                            <div>
                                <div class="type-heading-sm font-bold text-slate-800">{{ hargaKompetitorModalType === 'create' ? 'Tambah Data Harga' : 'Edit Data Harga' }}</div>
                                <div class="type-body-sm text-slate-400">Analisa harga dan kompetitor</div>
                            </div>
                        </div>
                        <button @click="hargaKompetitorModalOpen = false"
                            class="icon-utility-button icon-utility-danger"><i
                                class="fa-solid fa-xmark text-sm"></i></button>
                    </div>
                    <div class="harga-kompetitor-modal-body flex-1 overflow-y-auto p-4 space-y-3">
                        <label for="harga-kompetitor-nama-produk" class="sr-only">Nama Produk</label>
                        <input id="harga-kompetitor-nama-produk" name="harga_kompetitor_nama_produk" type="hidden"
                            :value="[hargaKompetitorForm.BRAND, hargaKompetitorForm.SERI, hargaKompetitorForm.RAM, hargaKompetitorForm.INTERNAL, hargaKompetitorForm.SIZE, hargaKompetitorForm.WARNA].filter(Boolean).join(' ') || hargaKompetitorForm.Nama_Produk || ''" />
                        <div class="grid grid-cols-2 gap-2.5">
                            <div class="relative search-select-container">
                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-1.5">Kategori</label>
                                <button type="button" @click="toggleSearchSelect($event, 'harga_kategori')"
                                    :aria-expanded="searchSelectOpen === 'harga_kategori' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span :class="hargaKompetitorForm.KATEGORI ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ hargaKompetitorForm.KATEGORI || 'Pilih kategori' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'harga_kategori'" :style="popoverStyle" class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="harga_kategori_search" autocomplete="off" aria-label="Cari kategori harga" placeholder="Cari kategori..." class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in hargaKategoriOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))" :key="opt"
                                                @click="hargaKompetitorForm.KATEGORI = opt; hargaKompetitorForm.BRAND = ''; hargaKompetitorForm.SERI = ''; searchSelectOpen = null"
                                                :class="['popover-option', hargaKompetitorForm.KATEGORI === opt ? 'popover-option-active' : '']">{{ opt }}</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-1.5">Brand</label>
                                <button type="button" @click="toggleSearchSelect($event, 'harga_brand')"
                                    :aria-expanded="searchSelectOpen === 'harga_brand' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span :class="hargaKompetitorForm.BRAND ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ hargaKompetitorForm.BRAND || 'Pilih brand' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'harga_brand'" :style="popoverStyle" class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="harga_brand_search" autocomplete="off" aria-label="Cari brand harga" placeholder="Cari brand..." class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in hargaBrandOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))" :key="opt"
                                                @click="hargaKompetitorForm.BRAND = opt; hargaKompetitorForm.SERI = ''; searchSelectOpen = null"
                                                :class="['popover-option', hargaKompetitorForm.BRAND === opt ? 'popover-option-active' : '']">{{ opt }}</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-1.5">Seri</label>
                                <button type="button" @click="toggleSearchSelect($event, 'harga_seri')"
                                    :aria-expanded="searchSelectOpen === 'harga_seri' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span :class="hargaKompetitorForm.SERI ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ hargaKompetitorForm.SERI || 'Pilih seri' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'harga_seri'" :style="popoverStyle" class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="harga_seri_search" autocomplete="off" aria-label="Cari seri harga" placeholder="Cari seri..." class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in hargaSeriOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))" :key="opt"
                                                @click="hargaKompetitorForm.SERI = opt; searchSelectOpen = null"
                                                :class="['popover-option', hargaKompetitorForm.SERI === opt ? 'popover-option-active' : '']">{{ opt }}</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div>
                                <label for="harga-kompetitor-warna" class="type-body-sm font-bold text-slate-400 uppercase mb-1.5">Warna</label>
                                <input id="harga-kompetitor-warna" name="harga_kompetitor_warna" v-model="hargaKompetitorForm.WARNA" type="text" class="form-input-compact" placeholder="Warna" />
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-2.5">
                            <div class="relative search-select-container">
                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-1.5">RAM</label>
                                <button type="button" @click="toggleSearchSelect($event, 'harga_ram')" :aria-expanded="searchSelectOpen === 'harga_ram' ? 'true' : 'false'" class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span :class="hargaKompetitorForm.RAM ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ hargaKompetitorForm.RAM || 'RAM' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade"><div v-if="searchSelectOpen === 'harga_ram'" :style="popoverStyle" class="search-select-popover"><div class="max-h-48 overflow-y-auto custom-scrollbar"><div v-for="opt in hargaRAMOptions" :key="opt" @click="hargaKompetitorForm.RAM = opt; searchSelectOpen = null" :class="['popover-option', hargaKompetitorForm.RAM === opt ? 'popover-option-active' : '']">{{ opt }}</div></div></div></transition>
                            </div>
                            <div class="relative search-select-container">
                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-1.5">Internal</label>
                                <button type="button" @click="toggleSearchSelect($event, 'harga_internal')" :aria-expanded="searchSelectOpen === 'harga_internal' ? 'true' : 'false'" class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span :class="hargaKompetitorForm.INTERNAL ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ hargaKompetitorForm.INTERNAL || 'Internal' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade"><div v-if="searchSelectOpen === 'harga_internal'" :style="popoverStyle" class="search-select-popover"><div class="max-h-48 overflow-y-auto custom-scrollbar"><div v-for="opt in hargaInternalOptions" :key="opt" @click="hargaKompetitorForm.INTERNAL = opt; searchSelectOpen = null" :class="['popover-option', hargaKompetitorForm.INTERNAL === opt ? 'popover-option-active' : '']">{{ opt }}</div></div></div></transition>
                            </div>
                            <div class="relative search-select-container">
                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-1.5">Size</label>
                                <button type="button" @click="toggleSearchSelect($event, 'harga_size')" :aria-expanded="searchSelectOpen === 'harga_size' ? 'true' : 'false'" class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span :class="hargaKompetitorForm.SIZE ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ hargaKompetitorForm.SIZE || 'Size' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade"><div v-if="searchSelectOpen === 'harga_size'" :style="popoverStyle" class="search-select-popover"><div class="max-h-48 overflow-y-auto custom-scrollbar"><div v-for="opt in hargaSizeOptions" :key="opt" @click="hargaKompetitorForm.SIZE = opt; searchSelectOpen = null" :class="['popover-option', hargaKompetitorForm.SIZE === opt ? 'popover-option-active' : '']">{{ opt }}</div></div></div></transition>
                            </div>
                        </div>
                        <div class="harga-kompetitor-info-card bg-slate-50 p-2.5 rounded-xl text-body text-slate-600">
                            <span class="font-bold">Nama Produk: </span>
                            <span class="font-semibold text-slate-800">{{ [hargaKompetitorForm.BRAND, hargaKompetitorForm.SERI, hargaKompetitorForm.RAM, hargaKompetitorForm.INTERNAL, hargaKompetitorForm.SIZE, hargaKompetitorForm.WARNA].filter(Boolean).join(' ') || hargaKompetitorForm.Nama_Produk || '-' }}</span>
                        </div>
                        <div>
                            <label class="type-body-sm font-bold text-slate-400 uppercase mb-1.5">Tanggal
                                Cek</label>
                            <button @click="openCalendar($event, 'form', '', 'hargaKompetitorCek')"
                                class="select-trigger-button-form toolbar-trigger-field-form">
                                <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                <span
                                    :class="hargaKompetitorForm.Tanggal_Cek ? 'text-slate-700 font-medium' : 'text-slate-400'">{{ hargaKompetitorForm.Tanggal_Cek || 'Pilih tanggal' }}</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label for="harga-kompetitor-distributor-1" class="type-body-sm font-bold text-slate-400 uppercase mb-1.5">Harga
                                    Distributor 1</label>
                                <input id="harga-kompetitor-distributor-1" name="harga_kompetitor_distributor_1" v-model.number="hargaKompetitorForm.Harga_Distributor_1" type="number"
                                    class="form-input-compact" />
                            </div>
                            <div>
                                <label for="harga-kompetitor-distributor-2" class="type-body-sm font-bold text-slate-400 uppercase mb-1.5">Harga
                                    Distributor 2</label>
                                <input id="harga-kompetitor-distributor-2" name="harga_kompetitor_distributor_2" v-model.number="hargaKompetitorForm.Harga_Distributor_2" type="number"
                                    class="form-input-compact" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label for="harga-kompetitor-kompetitor" class="type-body-sm font-bold text-slate-400 uppercase mb-1.5">Harga
                                    Kompetitor</label>
                                <input id="harga-kompetitor-kompetitor" name="harga_kompetitor_harga_kompetitor" v-model.number="hargaKompetitorForm.Harga_Kompetitor" type="number"
                                    class="form-input-compact" />
                            </div>
                            <div>
                                <label for="harga-kompetitor-rencana-jual" class="type-body-sm font-bold text-slate-400 uppercase mb-1.5">Harga
                                    Rencana Jual</label>
                                <input id="harga-kompetitor-rencana-jual" name="harga_kompetitor_harga_rencana_jual" v-model.number="hargaKompetitorForm.Harga_Rencana_Jual" type="number"
                                    class="form-input-compact" />
                            </div>
                        </div>
                        <div class="harga-kompetitor-info-card bg-slate-50 p-2.5 rounded-xl text-body text-slate-600">
                            <span class="font-bold">Margin Profit Otomatis: </span>
                            <span
                                :class="hargaKompetitorCalculatedMargin >= 0 ? 'text-success font-bold' : 'text-danger font-bold'">
                                {{ formatCurrency(hargaKompetitorCalculatedMargin) }}
                            </span>
                            <div class="type-body-sm text-slate-400 mt-1">
                                Dihitung dari Harga Rencana Jual - harga distributor tertinggi.
                            </div>
                        </div>
                        <div class="harga-kompetitor-info-card rounded-xl border border-slate-100 bg-slate-50 p-2.5">
                            <div class="flex items-start justify-between gap-2.5">
                                <div class="min-w-0">
                                    <div class="type-body-sm font-bold uppercase text-slate-400">Saran Harga</div>
                                    <div class="mt-1 text-heading-sm font-bold text-slate-900">
                                        {{ hargaKompetitorSuggestion.canSuggest ? formatCurrency(hargaKompetitorSuggestion.suggestedPrice) : '-' }}
                                    </div>
                                    <div class="mt-1 text-body-sm leading-relaxed text-slate-500">
                                        <template v-if="hargaKompetitorSuggestion.canSuggest">
                                            <template v-if="hargaKompetitorSuggestion.useCompetitiveSuggestion">
                                                Kompetitor - Rp100.000, margin kompetitor {{ formatCurrency(hargaKompetitorSuggestion.competitorProfit) }}, estimasi profit {{ formatCurrency(hargaKompetitorSuggestion.suggestedProfit) }}.
                                            </template>
                                            <template v-else>
                                                Modal + Rp100.000, karena margin kompetitor {{ formatCurrency(hargaKompetitorSuggestion.competitorProfit) }} <= Rp200.000.
                                            </template>
                                        </template>
                                        <template v-else-if="!hargaKompetitorSuggestion.competitorPrice || !hargaKompetitorSuggestion.distributorCost">
                                            Isi harga distributor dan kompetitor untuk menghitung saran.
                                        </template>
                                    </div>
                                </div>
                                <button @click="applyHargaKompetitorSuggestion" :disabled="!hargaKompetitorSuggestion.canSuggest"
                                    class="primary-cta-button primary-cta-button--accent shrink-0">
                                    Gunakan
                                </button>
                            </div>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl text-body text-slate-600">
                            <span class="font-bold">Selisih (Rencana Jual - Kompetitor): </span>
                            <span
                                :class="(hargaKompetitorForm.Harga_Rencana_Jual - hargaKompetitorForm.Harga_Kompetitor) >= 0 ? 'text-success font-bold' : 'text-danger font-bold'">
                                {{ formatCurrency((hargaKompetitorForm.Harga_Rencana_Jual || 0) - (hargaKompetitorForm.Harga_Kompetitor || 0)) }}
                            </span>
                        </div>
                    </div>
                    <div class="modal-footer-bar modal-footer-actions">
                        <button @click="hargaKompetitorModalOpen = false" class="primary-cta-button primary-cta-button--neutral">Batal</button>
                        <button @click="saveHargaKompetitor" :disabled="submitting" class="primary-cta-button">
                            <i v-if="submitting" class="fa-solid fa-circle-notch fa-spin"></i>
                            {{ submitting ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
@endverbatim
