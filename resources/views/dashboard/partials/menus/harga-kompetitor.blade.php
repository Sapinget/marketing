@verbatim
<!-- Harga & Kompetitor tab -->
            <div v-if="activeTab === 'harga_kompetitor' && !tabDataLoaded['hargaKompetitor']"
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
                class="space-y-6 animate-fadeIn pb-10">
                <!-- Summary cards -->
                <div class="space-y-3">
                    <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                        <div v-for="c in hargaSummary.cards" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                            <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i :class="['fa-solid', c.icon, 'text-[120px]']"></i></div>
                            <p :class="['text-[9px] font-bold uppercase tracking-widest mb-3', c.color]">{{ c.label }}</p>
                            <div class="flex items-baseline gap-2">
                                <span class="dashboard-summary-value">{{ c.value }}</span>
                                <span v-if="c.unit" :class="['dashboard-summary-unit', c.unitColor]">{{ c.unit }}</span>
                            </div>
                            <p :class="['text-[10px] font-bold mt-3', c.subColor]">{{ c.sub }}</p>
                        </div>
                    </div>
                </div>

                <section class="section-card section-card-body">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 md:gap-5">
                        <div class="modal-header-copy">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                                <i class="fa-solid fa-calculator text-[16px]"></i>
                            </div>
                            <div>
                                <h2 class="type-title font-bold text-slate-900">Harga &amp; Kompetitor</h2>
                                <p class="text-[10px] text-slate-400 uppercase tracking-widest mt-0.5">Analisa
                                    Harga &amp; Profit Margin</p>
                            </div>
                        </div>
                        <div class="mobile-toolbar-stack">
                            <div class="relative group search-select-container">
                                <button @click="openCalendar($event, 'filter', '', 'hargaKompetitor')"
                                    class="date-trigger-button date-trigger-button-compact">
                                    <i class="fa-solid fa-calendar-days text-[10px] text-slate-400"></i>
                                    <template v-if="hargaKompetitorDateFilter.start">
                                        {{ formatShortDate(hargaKompetitorDateFilter.start) }}
                                        <span v-if="hargaKompetitorDateFilter.end"> - {{ formatShortDate(hargaKompetitorDateFilter.end) }}</span>
                                    </template>
                                    <template v-else>Semua Tanggal</template>
                                    <i v-if="hargaKompetitorDateFilter.start"
                                        @click.stop="hargaKompetitorDateFilter = {start:'',end:''}"
                                        class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-red-500"></i>
                                </button>
                            </div>
                            <div class="relative flex-1 sm:w-44">
                                <i
                                    class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                                <input id="harga-kompetitor-search" name="harga_kompetitor_search" v-model="hargaKompetitorSearch" type="text" placeholder="Cari produk..."
                                    autocomplete="off" aria-label="Cari produk harga kompetitor"
                                    class="form-input-search" />
                            </div>
                            <div class="toolbar-actions">
                                <button @click="openHargaKompetitorModal('create')"
                                    class="primary-cta-button primary-cta-button--accent active:scale-95"><i
                                        class="fa-solid fa-plus"></i> Tambah</button>
                                <button @click="exportPriceComparisonToPDF"
                                    class="secondary-cta-button secondary-cta-danger active:scale-95"><i
                                        class="fa-solid fa-file-pdf"></i><span
                                        class="ml-1">PDF</span></button>
                            </div>
                        </div>
                    </div>
                    <div v-if="hargaSummary.chips.length" class="chips-grid mt-4 pt-4 border-t border-slate-100">
                        <span v-for="ch in hargaSummary.chips" :key="ch.label" :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold', ch.cls]">{{ ch.label }} <span class="opacity-50">&middot;</span> {{ ch.n }}</span>
                    </div>
                </section>
                <div class="md:hidden space-y-3">
                    <div class="space-y-3">
                        <div v-if="pagedHargaKompetitorData.length === 0"
                            class="bg-white radius-card border border-slate-100 p-10 text-center text-[11px] text-slate-400">
                            Belum ada data harga
                        </div>
                        <div v-for="(row, idx) in pagedHargaKompetitorData" :key="'hk-mobile-' + (row.ID || idx)"
                            class="stat-card mobile-record-card mobile-data-card motion-stagger-item"
                            :style="getStaggerStyle(idx)">
                            <div class="mobile-data-card__header">
                                <span
                                    class="px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700">
                                    {{ row.Tanggal_Cek ? formatShortDate(row.Tanggal_Cek) : '-' }}
                                </span>
                                <span class="type-meta text-slate-400 font-bold uppercase tracking-widest">
                                    Harga
                                </span>
                            </div>
                            <div>
                                <p class="mobile-data-card__title line-clamp-2">{{ row.Nama_Produk || '-' }}</p>
                                <div class="mobile-data-card__meta mt-2">
                                    <span
                                        class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-[9px] font-bold uppercase">
                                        Kompetitor
                                    </span>
                                    <span
                                        class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-[9px] font-bold uppercase">
                                        Jual
                                    </span>
                                </div>
                            </div>
                            <div class="mobile-data-card__summary">
                                <div>
                                    <div class="type-meta text-slate-400 uppercase">Kompetitor</div>
                                    <div class="type-body font-bold text-slate-700">{{ formatCurrency(row.Harga_Kompetitor) }}</div>
                                </div>
                                <div>
                                    <div class="type-meta text-slate-400 uppercase">Rencana</div>
                                    <div class="type-body font-bold text-blue-600">{{ formatCurrency(row.Harga_Rencana_Jual) }}</div>
                                </div>
                            </div>
                            <div class="mobile-data-card__actions">
                                <div class="type-meta font-bold"
                                    :class="(row.Selisih || 0) >= 0 ? 'text-emerald-600' : 'text-rose-500'">
                                    {{ formatCurrency(row.Selisih) }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="openHargaKompetitorModal('edit', row)"
                                        class="table-action-button table-action-compact" title="Edit"
                                        aria-label="Edit"><i class="fa-solid fa-pen text-[10px]"></i></button>
                                    <button @click="deleteHargaKompetitor(row.ID)"
                                        class="table-action-button table-action-compact table-action-danger"
                                        title="Hapus" aria-label="Hapus"><i
                                            class="fa-solid fa-trash text-[10px]"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center gap-2 py-2">
                        <button @click="hargaKompetitorPage = 1" :disabled="hargaKompetitorPage <= 1"
                            aria-label="Halaman pertama" class="icon-utility-button icon-utility-bordered"><i
                                class="fa-solid fa-angles-left text-[10px]"></i></button>
                        <button @click="hargaKompetitorPage--" :disabled="hargaKompetitorPage <= 1"
                            aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered"><i
                                class="fa-solid fa-chevron-left text-[10px]"></i></button>
                        <span class="px-3 text-[10px] font-bold text-ppp-accent">{{ hargaKompetitorPage }} /
                            {{ hargaKompetitorTotalPages }}</span>
                        <button @click="hargaKompetitorPage++" aria-label="Halaman berikutnya"
                            :disabled="hargaKompetitorPage >= hargaKompetitorTotalPages"
                            class="icon-utility-button icon-utility-bordered"><i
                                class="fa-solid fa-chevron-right text-[10px]"></i></button>
                        <button @click="hargaKompetitorPage = hargaKompetitorTotalPages" aria-label="Halaman terakhir"
                            :disabled="hargaKompetitorPage >= hargaKompetitorTotalPages"
                            class="icon-utility-button icon-utility-bordered"><i
                                class="fa-solid fa-angles-right text-[10px]"></i></button>
                    </div>
                </div>
                <div class="hidden md:block section-card section-card-shell">
                    <div class="overflow-x-auto">
                        <table class="w-full text-[10.5px] text-left border-collapse min-w-[1000px]">
                            <thead>
                                <tr
                                    class="border-b border-slate-100 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                                    <th class="px-4 py-3 text-center w-12">#</th>
                                    <th class="px-4 py-3">Produk</th>
                                    <th class="px-4 py-3 text-center w-28">Tanggal Cek</th>
                                    <th class="px-4 py-3 text-center w-28">Dist 1</th>
                                    <th class="px-4 py-3 text-center w-28">Dist 2</th>
                                    <th class="px-4 py-3 text-center w-28">Kompetitor</th>
                                    <th class="px-4 py-3 text-center w-28">Rencana Jual</th>
                                    <th class="px-4 py-3 text-center w-24">Margin</th>
                                    <th class="px-4 py-3 text-center w-24">Selisih</th>
                                    <th class="px-4 py-3 text-right w-24">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="pagedHargaKompetitorData.length === 0">
                                    <td colspan="10" class="px-4 py-12 text-center text-[11px] text-slate-400">
                                        Belum ada data harga</td>
                                </tr>
                                <tr v-for="(row, idx) in pagedHargaKompetitorData" :key="row.ID"
                                    class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 text-center text-[11px] text-slate-400">{{ (hargaKompetitorPage - 1) * 20 + idx + 1 }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-800 uppercase text-[11px]">{{ row.Nama_Produk }}</td>
                                    <td class="px-4 py-3 text-center text-[11px] text-slate-500">{{ formatShortDate(row.Tanggal_Cek) }}</td>
                                    <td class="px-4 py-3 text-center text-[11px] font-bold text-slate-600">{{ formatCurrency(row.Harga_Distributor_1) }}</td>
                                    <td class="px-4 py-3 text-center text-[11px] font-bold text-slate-600">{{ formatCurrency(row.Harga_Distributor_2) }}</td>
                                    <td class="px-4 py-3 text-center text-[11px] font-bold text-slate-600">{{ formatCurrency(row.Harga_Kompetitor) }}</td>
                                    <td class="px-4 py-3 text-center text-[11px] font-bold text-blue-600">{{ formatCurrency(row.Harga_Rencana_Jual) }}</td>
                                    <td class="px-4 py-3 text-center text-[11px] font-bold text-slate-600">{{ formatCurrency(row.Margin_Profit) }}</td>
                                    <td class="px-4 py-3 text-center text-[11px] font-bold"
                                        :class="(row.Selisih || 0) >= 0 ? 'text-emerald-600' : 'text-rose-500'">
                                        {{ formatCurrency(row.Selisih) }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button @click="openHargaKompetitorModal('edit', row)"
                                                class="table-action-button table-action-compact" title="Edit"
                                                aria-label="Edit"><i class="fa-solid fa-pen text-[10px]"></i></button>
                                            <button @click="deleteHargaKompetitor(row.ID)"
                                                class="table-action-button table-action-compact table-action-danger"
                                                title="Hapus" aria-label="Hapus"><i
                                                    class="fa-solid fa-trash text-[10px]"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-pager-bar-compact">
                        <span class="text-[10px] font-bold text-slate-400">{{ filteredHargaKompetitorData.length }} data</span>
                        <div class="flex items-center gap-1">
                            <button @click="hargaKompetitorPage = 1" :disabled="hargaKompetitorPage <= 1"
                                class="icon-utility-button icon-utility-bordered"><i
                                    class="fa-solid fa-angles-left text-[10px]"></i></button>
                            <button @click="hargaKompetitorPage--" :disabled="hargaKompetitorPage <= 1"
                                aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered"><i
                                    class="fa-solid fa-chevron-left text-[10px]"></i></button>
                            <span class="px-3 text-[10px] font-bold text-ppp-accent">{{ hargaKompetitorPage }} /
                                {{ hargaKompetitorTotalPages }}</span>
                            <button @click="hargaKompetitorPage++"
                                :disabled="hargaKompetitorPage >= hargaKompetitorTotalPages"
                                aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered"><i
                                    class="fa-solid fa-chevron-right text-[10px]"></i></button>
                            <button @click="hargaKompetitorPage = hargaKompetitorTotalPages"
                                :disabled="hargaKompetitorPage >= hargaKompetitorTotalPages"
                                class="icon-utility-button icon-utility-bordered"><i
                                    class="fa-solid fa-angles-right text-[10px]"></i></button>
                        </div>
                    </div>
                </div>
            </div>
@endverbatim
