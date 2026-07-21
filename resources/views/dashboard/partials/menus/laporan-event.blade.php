@verbatim
<!-- Laporan Event tab -->
            <div v-if="activeTab === 'laporan_event' && !tabDataLoaded['lpjk']"
                class="space-y-6 animate-fadeIn pb-10 animate-pulse">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div v-for="i in 4" :key="'sk-le-st'+i"
                        class="bg-white p-4 radius-card border border-slate-100 animate-pulse">
                        <div class="w-10 h-10 rounded-2xl bg-slate-200 mb-4"></div>
                        <div class="h-3 bg-slate-100 rounded-full w-20 mb-2"></div>
                        <div class="h-5 bg-slate-200 rounded-full w-12"></div>
                    </div>
                </div>
                <div class="section-card section-card-shell">
                    <div class="px-6 py-4 border-b border-slate-50 flex gap-6">
                        <div class="h-3 bg-slate-200 rounded-full w-20"></div>
                        <div class="h-3 bg-slate-200 rounded-full w-28"></div>
                        <div class="h-3 bg-slate-200 rounded-full w-24"></div>
                        <div class="h-3 bg-slate-200 rounded-full flex-1"></div>
                    </div>
                    <div class="divide-y divide-slate-50">
                        <div v-for="i in 6" :key="'sk-le'+i" class="px-6 py-5 flex items-center gap-4">
                            <div class="h-4 bg-slate-100 rounded-full w-40"></div>
                            <div class="h-4 bg-slate-100 rounded-full w-24"></div>
                            <div class="h-6 bg-slate-100 rounded-full w-16"></div>
                            <div class="h-4 bg-slate-100 rounded-full flex-1"></div>
                            <div class="flex gap-1">
                                <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                                <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="activeTab === 'laporan_event' && tabDataLoaded['lpjk']" class="space-y-6 animate-fadeIn pb-10">
                <!-- Summary cards -->
                <div class="space-y-3">
                    <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
                        <div v-for="c in lpjkSummary.cards.slice(0, 5)" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
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
                        <div v-if="pagedLpjkData.length === 0"
                            class="bg-white radius-card border border-slate-100 p-10 text-center text-body text-slate-400">
                            Belum ada data event
                        </div>
                        <div v-for="(row, idx) in pagedLpjkData" :key="'lpjk-mobile-' + (row.ID || idx)"
                            class="stat-card mobile-record-card mobile-data-card motion-stagger-item"
                            :style="getStaggerStyle(idx)">
                            <div class="mobile-data-card__header">
                                <span
                                    :class="['px-2.5 py-1 rounded-full text-overline font-bold uppercase tracking-wider', getStatusColor(row.Status)]">
                                    {{ row.Status || '-' }}
                                </span>
                                <span class="type-body-sm text-slate-400 font-bold uppercase tracking-widest">
                                    {{ row.Tanggal ? formatShortDate(row.Tanggal) : '-' }}
                                </span>
                            </div>
                            <div>
                                <p class="mobile-data-card__title line-clamp-2">{{ row.Nama_Event || '-' }}</p>
                                <div class="mobile-data-card__meta mt-2">
                                    <span
                                        class="px-2.5 py-1 rounded-lg bg-secondary text-light text-overline font-bold uppercase">
                                        Event
                                    </span>
                                    <span
                                        class="px-2.5 py-1 rounded-lg bg-secondary text-light text-overline font-bold uppercase">
                                        Budget
                                    </span>
                                </div>
                                <p v-if="row.Keterangan" class="type-body-sm text-slate-400 mt-2 line-clamp-1">{{ row.Keterangan }}</p>
                            </div>
                            <div class="mobile-data-card__summary">
                                <div>
                                    <div class="type-body-sm text-slate-400 uppercase">Budget</div>
                                    <div class="type-body font-bold text-slate-700">{{ formatCurrency(row.Budget_Rencana) }}</div>
                                </div>
                                <div>
                                    <div class="type-body-sm text-slate-400 uppercase">Realisasi</div>
                                    <div class="type-body font-bold text-slate-800">{{ formatCurrency(row.Realisasi_Biaya) }}</div>
                                </div>
                            </div>
                            <div class="mobile-data-card__actions">
                                <div class="type-body-sm font-bold"
                                    :class="(row.Selisih || 0) >= 0 ? 'text-success' : 'text-danger'">
                                    {{ formatCurrency(row.Selisih) }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="openLpjkDetail(row)"
                                        class="table-action-button table-action-compact table-action-view"
                                        title="Detail Pengeluaran" aria-label="Detail Pengeluaran"><i
                                            class="fa-solid fa-eye text-body-sm"></i></button>
                                    <button @click="openLpjkModal('edit', row)"
                                        class="table-action-button table-action-compact" title="Edit"
                                        aria-label="Edit"><i class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                                    <button @click="deleteLpjk(row.ID)"
                                        class="table-action-button table-action-compact table-action-danger"
                                        title="Hapus" aria-label="Hapus"><i
                                            class="fa-solid fa-trash-can text-body-sm"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center gap-2 py-2">
                        <button @click="lpjkPage = 1" :disabled="lpjkPage <= 1" aria-label="Halaman pertama"
                            class="icon-utility-button icon-utility-bordered"><i
                                class="fa-solid fa-angles-left text-body-sm"></i></button>
                        <button @click="lpjkPage--" :disabled="lpjkPage <= 1" aria-label="Halaman sebelumnya"
                            class="icon-utility-button icon-utility-bordered"><i
                                class="fa-solid fa-chevron-left text-body-sm"></i></button>
                        <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ lpjkPage }} / {{ lpjkTotalPages }}</span>
                        <button @click="lpjkPage++" :disabled="lpjkPage >= lpjkTotalPages"
                            aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered"><i
                                class="fa-solid fa-chevron-right text-body-sm"></i></button>
                        <button @click="lpjkPage = lpjkTotalPages" :disabled="lpjkPage >= lpjkTotalPages"
                            aria-label="Halaman terakhir" class="icon-utility-button icon-utility-bordered"><i
                                class="fa-solid fa-angles-right text-body-sm"></i></button>
                    </div>
                </div>
                <div class="hidden md:block section-card section-card-shell">
                    <div class="table-toolbar-shell">
                        <div class="table-toolbar-shell__left">
                            <div class="relative">
                                <i
                                    class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                                <input id="lpjk-search" name="lpjk_search" v-model="lpjkSearch" type="text" placeholder="Cari event..."
                                    autocomplete="off" aria-label="Cari laporan event"
                                    class="form-input-search" />
                            </div>
                        </div>
                        <div class="table-toolbar-shell__right">
                            <div class="toolbar-actions">
                                <button @click="openLpjkModal('create')"
                                    class="primary-cta-button primary-cta-button--accent active:scale-95"><i
                                        class="fa-solid fa-plus text-overline"></i> Tambah</button>
                                <button @click="exportLpjkDetailToPDF"
                                    class="primary-cta-button primary-cta-button--danger active:scale-95"><i
                                        class="fa-solid fa-file-pdf text-[9px]"></i><span
                                        class="ml-1">PDF</span></button>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-body-sm text-left border-collapse min-w-[900px]">
                            <thead>
                                <tr class="table-header-row">
                                    <th class="table-header-cell table-header-index table-freeze-index">#</th>
                                    <th class="table-header-cell table-header-action table-freeze-action">Aksi</th>
                                    <th class="table-header-cell">Event</th>
                                    <th class="table-header-cell text-center w-28">Tanggal</th>
                                    <th class="table-header-cell text-center w-28">Budget</th>
                                    <th class="table-header-cell text-center w-28">Realisasi</th>
                                    <th class="table-header-cell text-center w-24">Selisih</th>
                                    <th class="table-header-cell text-center w-24">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="pagedLpjkData.length === 0">
                                    <td colspan="8" class="px-4 py-12 text-center text-body text-slate-400">
                                        Belum ada data event</td>
                                </tr>
                                <tr v-for="(row, idx) in pagedLpjkData" :key="row.ID"
                                    class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 text-center text-body text-slate-400 table-freeze-index">{{ (lpjkPage - 1) * 20 + idx + 1 }}</td>
                                    <td class="px-4 py-3 table-freeze-action">
                                        <div class="flex items-center gap-1.5">
                                            <button @click="openLpjkDetail(row)"
                                                class="table-action-button table-action-compact table-action-view"
                                                title="Detail Pengeluaran" aria-label="Detail Pengeluaran"><i
                                                    class="fa-solid fa-eye text-body-sm"></i></button>
                                            <button @click="openLpjkModal('edit', row)"
                                                class="table-action-button table-action-compact" title="Edit"
                                                aria-label="Edit"><i class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                                            <button @click="deleteLpjk(row.ID)"
                                                class="table-action-button table-action-compact table-action-danger"
                                                title="Hapus" aria-label="Hapus"><i
                                                    class="fa-solid fa-trash-can text-body-sm"></i></button>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-slate-800 uppercase text-body-sm">{{ row.Nama_Event }}</p>
                                        <p v-if="row.Keterangan" class="text-body-sm text-slate-400 mt-0.5 line-clamp-1">
                                            {{ row.Keterangan }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-center text-body text-slate-500">{{ formatShortDate(row.Tanggal) }}</td>
                                    <td class="px-4 py-3 text-center text-body font-bold text-slate-600">{{ formatCurrency(row.Budget_Rencana) }}</td>
                                    <td class="px-4 py-3 text-center text-body font-bold text-slate-800">{{ formatCurrency(row.Realisasi_Biaya) }}</td>
                                    <td class="px-4 py-3 text-center text-body font-bold"
                                        :class="(row.Selisih || 0) >= 0 ? 'text-success' : 'text-danger'">
                                        {{ formatCurrency(row.Selisih) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span :class="getStatusColor(row.Status)"
                                            class="inline-flex items-center px-2.5 py-1 rounded-lg text-[9px] font-bold uppercase whitespace-nowrap">{{ row.Status }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-pager-bar">
                        <span class="text-body-sm font-bold text-slate-400">{{ filteredLpjkData.length }}
                            event</span>
                        <div class="flex items-center gap-1">
                            <button @click="lpjkPage = 1" :disabled="lpjkPage <= 1"
                                class="icon-utility-button icon-utility-bordered"><i
                                    class="fa-solid fa-angles-left text-body-sm"></i></button>
                            <button @click="lpjkPage--" :disabled="lpjkPage <= 1" aria-label="Halaman sebelumnya"
                                class="icon-utility-button icon-utility-bordered"><i
                                    class="fa-solid fa-chevron-left text-body-sm"></i></button>
                            <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ lpjkPage }} / {{ lpjkTotalPages }}</span>
                            <button @click="lpjkPage++" :disabled="lpjkPage >= lpjkTotalPages"
                                aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered"><i
                                    class="fa-solid fa-chevron-right text-body-sm"></i></button>
                            <button @click="lpjkPage = lpjkTotalPages" :disabled="lpjkPage >= lpjkTotalPages"
                                class="icon-utility-button icon-utility-bordered"><i
                                    class="fa-solid fa-angles-right text-body-sm"></i></button>
                        </div>
                    </div>
                </div>
            </div>
@endverbatim
