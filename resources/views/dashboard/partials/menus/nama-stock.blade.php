@verbatim
<!-- Nama Stock View -->
                    <div v-if="activeTab === 'nama_stock' && !namaStockLoaded"
                        class="space-y-4 animate-fadeIn animate-pulse">
                        <div class="section-card section-card-body">
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-200"></div>
                                    <div class="space-y-2">
                                        <div class="h-5 bg-slate-200 rounded-full w-40"></div>
                                        <div class="h-3 bg-slate-100 rounded-full w-56"></div>
                                    </div>
                                </div>
                                <div class="h-9 w-28 bg-slate-200 rounded-xl"></div>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div v-for="i in 8" :key="'sk-ns'+i" class="h-10 bg-slate-100 rounded-xl"></div>
                            </div>
                        </div>
                    </div>
                    <div v-if="activeTab === 'nama_stock' && namaStockLoaded" class="space-y-4 animate-fadeIn">
                        <section class="section-card section-card-body">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 md:gap-5">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-amber text-light flex items-center justify-center border border-amber">
                                        <i class="fa-solid fa-list-check text-body"></i>
                                    </div>
                                    <div>
                                        <h2 class="type-body font-bold text-slate-900">Nama Stock</h2>
                                        <p class="type-body text-slate-500">Master relasi kategori, brand, dan seri untuk form dashboard.</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="summary-counter-pill">{{ namaStockRows.length }} baris</span>
<button @click="openNamaStockFormModal('create')"
                                         class="primary-cta-button primary-cta-button--link primary-cta-button--icon-only active:scale-95"
                                         aria-label="Tambah Nama Stock">
                                         <i class="fa-solid fa-plus"></i>
                                     </button>
                                </div>
                            </div>
                        </section>

                        <section class="section-card section-card-shell">
                            <div class="py-3 px-4 border-b border-slate-100 flex flex-col md:flex-row items-stretch md:items-center gap-2">
                                <div class="relative flex-1">
                                    <i
                                        class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                    <input id="nama-stock-search" name="nama_stock_search" :value="namaStockSearchQuery"
                                        @input="namaStockSearchQuery = $event.target.value" type="text"
                                        placeholder="Cari kategori / brand / seri..."
                                        class="form-input-search" />
                                </div>
                                <div class="grid grid-cols-2 gap-2 md:flex md:items-center md:gap-2">
                                    <div class="relative search-select-container">
                                        <button type="button" @click="toggleSearchSelect($event, 'nama_stock_filter_kategori')"
                                            :aria-expanded="searchSelectOpen === 'nama_stock_filter_kategori' ? 'true' : 'false'"
                                            class="select-trigger-button select-trigger-button-compact">
                                            <span
                                                :class="namaStockKategoriFilter ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ namaStockKategoriFilter || 'Filter Kategori' }}</span>
                                            <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                        </button>
                                        <transition name="fade">
                                            <div v-if="searchSelectOpen === 'nama_stock_filter_kategori'" :style="popoverStyle"
                                                class="search-select-popover">
                                                <div class="relative mb-2">
                                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                                    <input id="nama-stock-filter-kategori-search" name="search_select_query" v-model="searchSelectQuery" type="text" placeholder="Cari kategori..." autocomplete="off" aria-label="Cari filter kategori nama stock"
                                                        class="form-input-popover" @click.stop />
                                                </div>
                                                <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                                    <div @click="namaStockKategoriFilter = ''; namaStockBrandFilter = ''; searchSelectOpen = null"
                                                        :class="['popover-option', !namaStockKategoriFilter ? 'popover-option-active' : '']">
                                                        Semua Kategori
                                                    </div>
                                                    <div v-for="opt in namaStockFilterKategoriOptions.filter(o => !searchSelectQuery || String(o || '').toLowerCase().includes(String(searchSelectQuery || '').toLowerCase()))"
                                                        :key="'ns-filter-kat-'+opt"
                                                        @click="namaStockKategoriFilter = opt; namaStockBrandFilter = ''; searchSelectOpen = null"
                                                        :class="['popover-option', namaStockKategoriFilter === opt ? 'popover-option-active' : '']">
                                                        {{ opt }}
                                                    </div>
                                                    <div v-if="namaStockFilterKategoriOptions.filter(o => !searchSelectQuery || String(o || '').toLowerCase().includes(String(searchSelectQuery || '').toLowerCase())).length === 0"
                                                        class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                        Tidak ditemukan
                                                    </div>
                                                </div>
                                            </div>
                                        </transition>
                                    </div>
                                    <div class="relative search-select-container">
                                        <button type="button" @click="toggleSearchSelect($event, 'nama_stock_filter_brand')"
                                            :aria-expanded="searchSelectOpen === 'nama_stock_filter_brand' ? 'true' : 'false'"
                                            class="select-trigger-button select-trigger-button-compact">
                                            <span
                                                :class="namaStockBrandFilter ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ namaStockBrandFilter || 'Filter Brand' }}</span>
                                            <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                        </button>
                                        <transition name="fade">
                                            <div v-if="searchSelectOpen === 'nama_stock_filter_brand'" :style="popoverStyle"
                                                class="search-select-popover">
                                                <div class="relative mb-2">
                                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                                    <input id="nama-stock-filter-brand-search" name="search_select_query" v-model="searchSelectQuery" type="text" placeholder="Cari brand..." autocomplete="off" aria-label="Cari filter brand nama stock"
                                                        class="form-input-popover" @click.stop />
                                                </div>
                                                <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                                    <div @click="namaStockBrandFilter = ''; searchSelectOpen = null"
                                                        :class="['popover-option', !namaStockBrandFilter ? 'popover-option-active' : '']">
                                                        Semua Brand
                                                    </div>
                                                    <div v-for="opt in namaStockFilterBrandOptions.filter(o => !searchSelectQuery || String(o || '').toLowerCase().includes(String(searchSelectQuery || '').toLowerCase()))"
                                                        :key="'ns-filter-brand-'+opt"
                                                        @click="namaStockBrandFilter = opt; searchSelectOpen = null"
                                                        :class="['popover-option', namaStockBrandFilter === opt ? 'popover-option-active' : '']">
                                                        {{ opt }}
                                                    </div>
                                                    <div v-if="namaStockFilterBrandOptions.filter(o => !searchSelectQuery || String(o || '').toLowerCase().includes(String(searchSelectQuery || '').toLowerCase())).length === 0"
                                                        class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                        Tidak ditemukan
                                                    </div>
                                                </div>
                                            </div>
                                        </transition>
                                    </div>
                                </div>
                            </div>
                            <div class="md:hidden space-y-3 p-3">
                                <div v-if="namaStockFilteredRows.length === 0"
                                    class="bg-white radius-card border border-slate-100 p-10 text-center text-body text-slate-400">
                                    Tidak ada data nama stock
                                </div>
                                <div v-for="(row, idx) in pagedNamaStockRows" :key="'ns-mobile-' + row.ID"
                                    class="stat-card mobile-record-card mobile-data-card motion-stagger-item"
                                    :style="getStaggerStyle(idx)">
                                    <div class="mobile-data-card__header">
                                        <span
                                            class="px-2.5 py-1 rounded-full text-overline font-bold uppercase bg-secondary text-light">
                                            {{ row.KATEGORI || '-' }}
                                        </span>
                                        <span class="type-body-sm text-slate-400 font-bold uppercase">
                                            Stock
                                        </span>
                                    </div>
                                    <div>
                                        <p class="mobile-data-card__title line-clamp-2">{{ row.BRAND ? row.BRAND + ' | ' + row.SERI : row.SERI || '-' }}</p>
                                        <p class="type-body-sm text-slate-400 mt-2 line-clamp-1">{{ row.BRAND || '-' }}</p>
                                    </div>
                                    <div class="mobile-data-card__summary">
                                        <div>
                                            <div class="type-body-sm text-slate-400 uppercase">Kategori</div>
                                            <div class="type-body font-bold text-slate-700 line-clamp-1">{{ row.KATEGORI || '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="type-body-sm text-slate-400 uppercase">Seri</div>
                                            <div class="type-body font-bold text-slate-700 line-clamp-1">{{ row.SERI || '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="mobile-data-card__actions">
                                        <div class="type-body-sm text-slate-400 line-clamp-1">{{ row.BRAND || '-' }}</div>
                                        <div class="flex items-center gap-2">
                                            <button @click="openNamaStockFormModal('edit', row)"
                                                class="table-action-button table-action-compact" title="Edit" aria-label="Edit">
                                                <i class="fa-solid fa-pen-to-square text-overline"></i>
                                            </button>
                                            <button @click="removeNamaStockRow(row.ID)"
                                                class="table-action-button table-action-compact table-action-danger" title="Hapus" aria-label="Hapus">
                                                <i class="fa-solid fa-trash-can text-overline"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="md:hidden flex items-center justify-center gap-2 py-2 border-t border-slate-100">
                                <button @click="namaStockPage--" :disabled="namaStockPage <= 1"
                                    aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-left text-body-sm"></i></button>
                                <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ namaStockPage }} / {{ namaStockTotalPages }}</span>
                                <button @click="namaStockPage++" :disabled="namaStockPage >= namaStockTotalPages"
                                    aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-right text-body-sm"></i></button>
                            </div>
                            <div class="hidden md:block overflow-auto">
                                <table class="w-full text-body-sm">
                                    <thead class="bg-slate-50">
                                        <tr class="table-header-row">
                                            <th class="table-header-cell text-center w-20">Aksi
                                            </th>
                                            <th class="table-header-cell text-left">Kategori</th>
                                            <th class="table-header-cell text-left">Brand</th>
                                            <th class="table-header-cell text-left">Seri</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <tr v-if="namaStockFilteredRows.length === 0">
                                            <td colspan="4" class="px-4 py-8 text-center text-slate-400 text-body">
                                                Tidak ada data</td>
                                        </tr>
                                        <tr v-for="row in pagedNamaStockRows" :key="row.ID"
                                            class="hover:bg-slate-50 transition">
                                            <td class="px-3 py-2 text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    <button @click="openNamaStockFormModal('edit', row)"
                                                        class="table-action-button table-action-compact" title="Edit" aria-label="Edit">
                                                        <i class="fa-solid fa-pen-to-square text-overline"></i>
                                                    </button>
                                                    <button @click="removeNamaStockRow(row.ID)"
                                                        class="table-action-button table-action-compact table-action-danger" title="Hapus" aria-label="Hapus">
                                                        <i class="fa-solid fa-trash-can text-overline"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-4 py-2.5 text-slate-700 font-medium">{{ row.KATEGORI || '-' }}
                                            </td>
                                            <td class="px-4 py-2.5 text-slate-600">{{ row.BRAND || '-' }}</td>
                                            <td class="px-4 py-2.5 text-slate-600">{{ row.SERI || '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div
                                class="px-4 py-3 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
                                <div class="text-body-sm text-slate-400 font-medium">
                                    <template v-if="namaStockFilteredRows.length > 0">{{ (namaStockPage - 1) * 15 + 1 }}-{{ Math.min(namaStockPage * 15, namaStockFilteredRows.length) }} dari {{ namaStockFilteredRows.length }} data</template>
                                    <template v-else>0 data</template>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button @click="namaStockPage--" :disabled="namaStockPage <= 1"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-left text-body-sm"></i></button>
                                    <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ namaStockPage }} / {{ namaStockTotalPages }}</span>
                                    <button @click="namaStockPage++" :disabled="namaStockPage >= namaStockTotalPages"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-right text-body-sm"></i></button>
                                </div>
                            </div>
                        </section>
                    </div>

    <!-- Nama Stock Form Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="showNamaStockFormModal"
                class="fixed inset-0 z-[2500] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                <div @click="closeNamaStockFormModal" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm overlay-backdrop">
                </div>
                <div
                    class="mobile-sheet modal-width-form relative w-full bg-white radius-sheet border border-slate-200 overflow-hidden animate-fadeIn flex flex-col max-h-[90dvh]">
                    <div
                        class="modal-header-bar modal-header-bar-sticky radius-sheet-top z-[2010]">
                        <div class="modal-header-copy">
                            <div
                                class="modal-header-icon bg-secondary text-light border border-slate-200">
                                <i class="fa-solid fa-boxes-stacked text-heading-sm"></i>
                            </div>
                            <div>
                                <div class="type-title text-slate-900">{{ namaStockFormMode === 'create' ? 'Tambah Nama Stock' : 'Edit Nama Stock' }}</div>
                                <div class="type-body-sm text-slate-400 uppercase mt-0.5">Master Stock</div>
                            </div>
                        </div>
                        <button @click="closeNamaStockFormModal" class="icon-utility-button icon-utility-round">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>
                    <form @submit.prevent="submitNamaStockForm" class="flex flex-1 flex-col min-h-0">
                        <div class="flex-1 overflow-y-auto p-6 space-y-4">
                        <div class="space-y-1 relative search-select-container">
                            <label
                                class="type-meta font-semibold text-slate-500 uppercase">Kategori</label>
                            <div @click="toggleSearchSelect($event, 'nama_stock_kategori')"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white cursor-pointer flex items-center justify-between hover:bg-slate-50 transition-all">
                                <span
                                    :class="namaStockForm.KATEGORI ? 'text-slate-700 font-semibold' : 'text-slate-400'">{{ namaStockForm.KATEGORI || 'Pilih Kategori' }}</span>
                                <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                            </div>
                            <transition name="fade">
                                <div v-if="searchSelectOpen === 'nama_stock_kategori'" :style="popoverStyle"
                                    class="search-select-popover">
                                    <div class="relative mb-2">
                                        <i
                                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                        <input id="nama-stock-kategori-search" name="search_select_query" v-model="searchSelectQuery" type="text" placeholder="Cari kategori..." autocomplete="off" aria-label="Cari kategori nama stock"
                                            class="form-input-popover" @click.stop />
                                    </div>
                                    <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                        <div v-if="namaStockKategoriOptions.length === 0"
                                            class="px-3 py-2 text-body text-slate-400 italic">Belum ada opsi kategori
                                            di setting</div>
                                        <div v-for="opt in namaStockKategoriOptions.filter(o => !searchSelectQuery || String(o || '').toLowerCase().includes(String(searchSelectQuery || '').toLowerCase()))"
                                            :key="`ns-kat-${opt}`"
                                            @click="namaStockForm.KATEGORI = opt; namaStockForm.BRAND = ''; namaStockForm.SERI = ''; searchSelectOpen = null"
                                            :class="['popover-option', namaStockForm.KATEGORI === opt ? 'popover-option-active' : '']">
                                            {{ opt }}
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </div>
                        <div class="space-y-1 relative search-select-container">
                            <label
                                class="text-body-sm font-semibold text-slate-500 uppercase">Brand</label>
                            <div @click="toggleSearchSelect($event, 'nama_stock_brand')"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white cursor-pointer flex items-center justify-between hover:bg-slate-50 transition-all">
                                <span
                                    :class="namaStockForm.BRAND ? 'text-slate-700 font-semibold' : 'text-slate-400'">{{ namaStockForm.BRAND || 'Pilih Brand' }}</span>
                                <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                            </div>
                            <transition name="fade">
                                <div v-if="searchSelectOpen === 'nama_stock_brand'" :style="popoverStyle"
                                    class="search-select-popover">
                                    <div class="relative mb-2">
                                        <i
                                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                        <input id="nama-stock-brand-search" name="search_select_query" v-model="searchSelectQuery" type="text" placeholder="Cari brand..." autocomplete="off" aria-label="Cari brand nama stock"
                                            class="form-input-popover" @click.stop />
                                    </div>
                                    <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                        <div v-if="namaStockBrandOptions.length === 0"
                                            class="px-3 py-2 text-body text-slate-400 italic">Pilih kategori dulu atau
                                            lengkapi setting brand</div>
                                        <div v-for="opt in namaStockBrandOptions.filter(o => !searchSelectQuery || String(o || '').toLowerCase().includes(String(searchSelectQuery || '').toLowerCase()))"
                                            :key="`ns-brand-${opt}`"
                                            @click="namaStockForm.BRAND = opt; namaStockForm.SERI = ''; searchSelectOpen = null"
                                            :class="['popover-option', namaStockForm.BRAND === opt ? 'popover-option-active' : '']">
                                            {{ opt }}
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </div>
                        <div class="space-y-1">
                            <label for="nama-stock-seri" class="text-body-sm font-semibold text-slate-500 uppercase">Seri</label>
                            <input id="nama-stock-seri" name="nama_stock_seri" v-model.trim="namaStockForm.SERI" type="text" placeholder="Ketik seri"
                                class="form-input-compact-white" />
                        </div>
                        </div>
                        <div class="modal-footer-bar modal-footer-actions">
                            <button type="button" @click="closeNamaStockFormModal"
                                class="primary-cta-button primary-cta-button--neutral">Batal</button>
                            <button type="submit"
                                class="primary-cta-button">{{ namaStockFormMode === 'create' ? 'Tambah' : 'Simpan' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </teleport>
@endverbatim
