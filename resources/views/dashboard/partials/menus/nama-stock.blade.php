@verbatim
<!-- Nama Stock View -->
                    <div v-if="activeTab === 'nama_stock' && !namaStockLoaded"
                        class="space-y-6 animate-fadeIn pb-10 animate-pulse">
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
                    <div v-if="activeTab === 'nama_stock' && namaStockLoaded" class="animate-fadeIn">
                        <div class="section-card section-card-shell">
                            <div class="py-3 px-4 border-b border-slate-100 flex items-center justify-between gap-2">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-xl bg-teal-50 flex items-center justify-center">
                                        <i class="fa-solid fa-list-check text-teal-500 text-sm"></i>
                                    </div>
                                    <div>
                                        <h2 class="type-title font-bold text-slate-900">Nama Stock</h2>
                                        <p class="type-meta text-slate-400">Master relasi Kategori, Brand, dan Seri
                                        </p>
                                    </div>
                                </div>
                                <span class="type-meta text-slate-400">{{ namaStockRows.length }} baris</span>
                            </div>
                            <div class="py-3 px-4 border-b border-slate-100 flex flex-col md:flex-row items-stretch md:items-center gap-2">
                                <div class="relative flex-1">
                                    <i
                                        class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                    <input :value="namaStockSearchQuery"
                                        @input="namaStockSearchQuery = $event.target.value" type="text"
                                        placeholder="Cari kategori / brand / seri..."
                                        class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 bg-white text-xs text-slate-700 outline-none focus:border-ppp-accent focus:ring-2 focus:ring-ppp-accent/10" />
                                </div>
                                <div class="grid grid-cols-2 gap-2 md:flex md:items-center md:gap-2">
                                    <div class="relative search-select-container">
                                        <button type="button" @click="toggleSearchSelect($event, 'nama_stock_filter_kategori')"
                                            :aria-expanded="searchSelectOpen === 'nama_stock_filter_kategori' ? 'true' : 'false'"
                                            class="select-trigger-button select-trigger-button-compact">
                                            <span
                                                :class="namaStockKategoriFilter ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ namaStockKategoriFilter || 'Filter Kategori' }}</span>
                                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                        </button>
                                        <transition name="fade">
                                            <div v-if="searchSelectOpen === 'nama_stock_filter_kategori'" :style="popoverStyle"
                                                class="search-select-popover">
                                                <div class="relative mb-2">
                                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                                    <input v-model="searchSelectQuery" type="text" placeholder="Cari kategori..."
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
                                                        class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
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
                                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                        </button>
                                        <transition name="fade">
                                            <div v-if="searchSelectOpen === 'nama_stock_filter_brand'" :style="popoverStyle"
                                                class="search-select-popover">
                                                <div class="relative mb-2">
                                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                                    <input v-model="searchSelectQuery" type="text" placeholder="Cari brand..."
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
                                                        class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                        Tidak ditemukan
                                                    </div>
                                                </div>
                                            </div>
                                        </transition>
                                    </div>
                                </div>
                                <button @click="openNamaStockFormModal('create')"
                                    class="px-3 py-2 rounded-xl bg-ppp-accent hover:bg-ppp-accent/90 text-white text-xs font-semibold transition flex items-center gap-1.5">
                                    <i class="fa-solid fa-plus text-[10px]"></i> Tambah
                                </button>
                            </div>
                            <div class="md:hidden space-y-3 p-3">
                                <div v-if="namaStockFilteredRows.length === 0"
                                    class="bg-white radius-card border border-slate-100 p-10 text-center text-[11px] text-slate-400">
                                    Tidak ada data nama stock
                                </div>
                                <div v-for="(row, idx) in pagedNamaStockRows" :key="'ns-mobile-' + row.ID"
                                    class="stat-card mobile-record-card mobile-data-card motion-stagger-item"
                                    :style="getStaggerStyle(idx)">
                                    <div class="mobile-data-card__header">
                                        <span
                                            class="px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600">
                                            {{ row.KATEGORI || '-' }}
                                        </span>
                                        <span class="type-meta text-slate-400 font-bold uppercase tracking-widest">
                                            Stock
                                        </span>
                                    </div>
                                    <div>
                                        <p class="mobile-data-card__title line-clamp-2">{{ row.BRAND ? row.BRAND + ' | ' + row.SERI : row.SERI || '-' }}</p>
                                        <p class="type-meta text-slate-400 mt-2 line-clamp-1">{{ row.BRAND || '-' }}</p>
                                    </div>
                                    <div class="mobile-data-card__summary">
                                        <div>
                                            <div class="type-meta text-slate-400 uppercase">Kategori</div>
                                            <div class="type-body font-bold text-slate-700 line-clamp-1">{{ row.KATEGORI || '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="type-meta text-slate-400 uppercase">Seri</div>
                                            <div class="type-body font-bold text-slate-700 line-clamp-1">{{ row.SERI || '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="mobile-data-card__actions">
                                        <div class="type-meta text-slate-400 line-clamp-1">{{ row.BRAND || '-' }}</div>
                                        <div class="flex items-center gap-2">
                                            <button @click="openNamaStockFormModal('edit', row)"
                                                class="table-action-button table-action-compact">
                                                <i class="fa-solid fa-pen text-[9px]"></i>
                                            </button>
                                            <button @click="removeNamaStockRow(row.ID)"
                                                class="table-action-button table-action-compact table-action-danger">
                                                <i class="fa-solid fa-trash text-[9px]"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="md:hidden flex items-center justify-center gap-2 py-2 border-t border-slate-100">
                                <button @click="namaStockPage--" :disabled="namaStockPage <= 1"
                                    aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-left text-[10px]"></i></button>
                                <span class="px-3 text-[10px] font-bold text-ppp-accent">{{ namaStockPage }} / {{ namaStockTotalPages }}</span>
                                <button @click="namaStockPage++" :disabled="namaStockPage >= namaStockTotalPages"
                                    aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-right text-[10px]"></i></button>
                            </div>
                            <div class="hidden md:block overflow-auto">
                                <table class="w-full text-[10.5px]">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-2.5 text-center text-slate-500 font-semibold w-20">Aksi
                                            </th>
                                            <th class="px-4 py-2.5 text-left text-slate-500 font-semibold">Kategori</th>
                                            <th class="px-4 py-2.5 text-left text-slate-500 font-semibold">Brand</th>
                                            <th class="px-4 py-2.5 text-left text-slate-500 font-semibold">Seri</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <tr v-if="namaStockFilteredRows.length === 0">
                                            <td colspan="4" class="px-4 py-8 text-center text-slate-400 text-[11px]">
                                                Tidak ada data</td>
                                        </tr>
                                        <tr v-for="row in pagedNamaStockRows" :key="row.ID"
                                            class="hover:bg-slate-50 transition">
                                            <td class="px-3 py-2 text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    <button @click="openNamaStockFormModal('edit', row)"
                                                        class="table-action-button table-action-compact">
                                                        <i class="fa-solid fa-pen text-[9px]"></i>
                                                    </button>
                                                    <button @click="removeNamaStockRow(row.ID)"
                                                        class="table-action-button table-action-compact table-action-danger">
                                                        <i class="fa-solid fa-trash text-[9px]"></i>
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
                                <div class="text-[10px] text-slate-400 font-medium">
                                    <template v-if="namaStockFilteredRows.length > 0">{{ (namaStockPage - 1) * 15 + 1 }}-{{ Math.min(namaStockPage * 15, namaStockFilteredRows.length) }} dari {{ namaStockFilteredRows.length }} data</template>
                                    <template v-else>0 data</template>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button @click="namaStockPage--" :disabled="namaStockPage <= 1"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-left text-[10px]"></i></button>
                                    <span class="px-3 text-[10px] font-bold text-ppp-accent">{{ namaStockPage }} / {{ namaStockTotalPages }}</span>
                                    <button @click="namaStockPage++" :disabled="namaStockPage >= namaStockTotalPages"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-right text-[10px]"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
@endverbatim
