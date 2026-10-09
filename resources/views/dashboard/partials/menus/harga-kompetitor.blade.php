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
@endverbatim
