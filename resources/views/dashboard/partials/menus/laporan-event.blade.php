@verbatim
<!-- Laporan Event tab -->
            <div v-if="activeTab === 'laporan_event' && !tabDataLoaded['lpjk']"
                class="space-y-4 animate-fadeIn animate-pulse">
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
            <div v-if="activeTab === 'laporan_event' && tabDataLoaded['lpjk']" class="space-y-4 animate-fadeIn">
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
                                    :class="['px-2.5 py-1 rounded-full text-overline font-bold uppercase', getStatusColor(row.Status)]">
                                    {{ row.Status || '-' }}
                                </span>
                                <span class="type-body-sm text-slate-400 font-bold uppercase">
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
                <div class="hidden md:block section-card section-card-shell lpjk-table-card">
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
                            <div class="toolbar-actions toolbar-actions--desktop-icon-only">
<button @click="openLpjkModal('create')"
                                     class="primary-cta-button primary-cta-button--accent primary-cta-button--icon-only active:scale-95"
                                     aria-label="Tambah Laporan Event"><i
                                         class="fa-solid fa-plus"></i></button>
                                <button @click="exportLpjkDetailToPDF"
                                    class="primary-cta-button primary-cta-button--danger primary-cta-button--icon-only active:scale-95" aria-label="Export PDF"><i
                                        class="fa-solid fa-file-pdf text-[9px]"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-body-sm text-left border-collapse min-w-[1040px]">
                            <thead>
                                <tr class="table-header-row">
                                    <th class="table-header-cell table-header-index table-freeze-index">#</th>
                                    <th class="table-header-cell table-header-action table-freeze-action">Aksi</th>
                                    <th class="table-header-cell lpjk-event-cell">Event</th>
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
                                    <td class="px-4 py-3 text-center text-body text-slate-400 table-freeze-index">{{ (lpjkPage - 1) * 15 + idx + 1 }}</td>
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
                                    <td class="px-4 py-3 lpjk-event-cell">
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
                                            class="status-badge-fixed inline-flex items-center px-2.5 py-1 rounded-lg text-[9px] font-bold uppercase whitespace-nowrap">{{ row.Status }}</span>
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

    <!-- Laporan Event Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="lpjkModalOpen"
                class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                <div @click="lpjkModalOpen = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop"></div>
                <div
                    class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface">
                    <div class="modal-header-bar radius-sheet-top">
                        <div class="modal-header-copy">
                            <div
                                class="modal-header-icon bg-secondary text-light">
                                <i class="fa-solid fa-calendar-check"></i>
                            </div>
                            <div>
                                <div class="type-heading-sm font-bold text-slate-800">{{ lpjkModalType === 'create' ? 'Tambah Event' : 'Edit Event' }}</div>
                                <div class="type-body-sm text-slate-400">Laporan kegiatan & anggaran</div>
                            </div>
                        </div>
                        <button @click="lpjkModalOpen = false" class="icon-utility-button icon-utility-danger"><i
                                class="fa-solid fa-xmark text-sm"></i></button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-6 space-y-4">
                        <div>
                            <label for="lpjk-nama-event" class="type-body-sm font-bold text-slate-400 uppercase mb-1.5">Nama
                                Event</label>
                            <input id="lpjk-nama-event" name="lpjk_nama_event" v-model="lpjkForm.Nama_Event" type="text" class="form-input-compact"
                                placeholder="Contoh: Open Table Mall Hartono" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-1.5">Tanggal</label>
                                <button @click="openCalendar($event, 'form', '', 'lpjkTanggal')"
                                    class="select-trigger-button-form toolbar-trigger-field-form">
                                    <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                    <span :class="lpjkForm.Tanggal ? 'text-slate-700 font-medium' : 'text-slate-400'">{{ lpjkForm.Tanggal || 'Pilih tanggal' }}</span>
                                </button>
                            </div>
                            <div>
                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-1.5">Status</label>
                                <div class="relative search-select-container">
                                    <button type="button" @click="toggleSearchSelect($event, 'lpjk_status')"
                                        class="select-trigger-button toolbar-trigger-field">
                                        <span class="truncate">{{ lpjkForm.Status || 'Pilih Status' }}</span>
                                        <i class="fa-solid fa-chevron-down text-overline text-slate-400"></i>
                                    </button>
                                    <div v-if="searchSelectOpen === 'lpjk_status'" :style="popoverStyle"
                                        class="search-select-popover search-select-popover--compact max-h-60 overflow-y-auto">
                                        <div v-for="s in lpjkStatusOptions" :key="s"
                                            @click="lpjkForm.Status = s; searchSelectOpen = null"
                                            class="popover-option">
                                            {{ s }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="lpjk-budget-rencana" class="type-body-sm font-bold text-slate-400 uppercase mb-1.5">Budget
                                    Rencana</label>
                                <input id="lpjk-budget-rencana" name="lpjk_budget_rencana" v-model.number="lpjkForm.Budget_Rencana" type="number"
                                    class="form-input-compact" />
                            </div>
                            <div>
                                <label for="lpjk-realisasi-biaya" class="type-body-sm font-bold text-slate-400 uppercase mb-1.5">Realisasi
                                    Biaya</label>
                                <input id="lpjk-realisasi-biaya" name="lpjk_realisasi_biaya" v-model.number="lpjkForm.Realisasi_Biaya" type="number"
                                    class="form-input-compact" />
                            </div>
                        </div>
                        <div>
                            <label for="lpjk-keterangan" class="type-body-sm font-bold text-slate-400 uppercase mb-1.5">Keterangan</label>
                            <textarea id="lpjk-keterangan" name="lpjk_keterangan" v-model="lpjkForm.Keterangan" rows="2" class="form-input-compact resize-none"
                                placeholder="Catatan tambahan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer-bar modal-footer-actions">
                        <button @click="lpjkModalOpen = false" class="primary-cta-button primary-cta-button--neutral">Batal</button>
                        <button @click="saveLpjk" :disabled="submitting" class="primary-cta-button">
                            <i v-if="submitting" class="fa-solid fa-circle-notch fa-spin"></i>
                            {{ submitting ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>

    <!-- LPJK Detail Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="lpjkDetailModalOpen"
                class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-dialog">
                <div @click="closeLpjkDetail" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop"></div>
                <div
                    class="modal-width-wide radius-dialog modal-dialog-surface overlay-dialog-surface flex flex-col max-h-[92vh]">
                    <div
                        class="modal-header-bar radius-sheet-top shrink-0">
                        <div class="modal-header-copy">
                            <div
                                class="modal-header-icon bg-slate-500 text-white">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                            </div>
                            <div>
                                <div class="type-heading-sm font-bold text-slate-800">Detail Keuangan: {{ activeLpjkRow && activeLpjkRow.Nama_Event }}</div>
                                <div class="type-body-sm text-slate-400">Input Pengeluaran &amp; Cetak LPJK</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="exportLpjkDetailToPDF" class="primary-cta-button primary-cta-button--danger primary-cta-button--icon-only" aria-label="Export PDF"><i
                                    class="fa-solid fa-file-pdf"></i></button>
                            <button @click="closeLpjkDetail" class="icon-utility-button icon-utility-danger"><i
                                    class="fa-solid fa-xmark text-sm"></i></button>
                        </div>
                    </div>
                    <div class="flex-1 overflow-y-auto p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                            <!-- Add item form -->
                            <div class="space-y-4">
                                <div class="text-body-sm font-bold text-slate-500 uppercase mb-2">Tambah Pengeluaran
                                </div>
                                <div>
                                    <label class="type-body-sm font-bold text-slate-400 uppercase mb-1">Kategori</label>
                                    <div class="relative search-select-container">
                                        <button type="button"
                                            @click="toggleSearchSelect($event, 'lpjk_expense_category')"
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-body outline-none hover:border-ppp-accent transition-all flex items-center justify-between gap-2">
                                            <span class="truncate">{{ lpjkDetailItem.Kategori || 'Pilih Kategori' }}</span>
                                            <i class="fa-solid fa-chevron-down text-overline text-slate-400"></i>
                                        </button>
                                        <div v-if="searchSelectOpen === 'lpjk_expense_category'" :style="popoverStyle"
                                            class="search-select-popover search-select-popover--compact max-h-60 overflow-y-auto">
                                            <div v-for="cat in lpjkExpenseCategories" :key="cat"
                                                @click="lpjkDetailItem.Kategori = cat; searchSelectOpen = null"
                                                class="popover-option">
                                                {{ cat }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label for="lpjk-detail-nama-pengeluaran" class="type-body-sm font-bold text-slate-400 uppercase mb-1">Nama
                                        Pengeluaran</label>
                                    <input id="lpjk-detail-nama-pengeluaran" name="lpjk_detail_nama_pengeluaran" v-model="lpjkDetailItem.Nama_Pengeluaran" type="text"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-body outline-none focus:border-ppp-accent"
                                        placeholder="Contoh: Print Undangan" />
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label for="lpjk-detail-satuan" class="type-body-sm font-bold text-slate-400 uppercase mb-1">Harga
                                            Satuan</label>
                                        <input id="lpjk-detail-satuan" name="lpjk_detail_satuan" v-model.number="lpjkDetailItem.Satuan" type="number"
                                            @input="lpjkDetailItem.Total = (lpjkDetailItem.Satuan||0)*(lpjkDetailItem.Jumlah||0)"
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-body outline-none focus:border-ppp-accent" />
                                    </div>
                                    <div>
                                        <label for="lpjk-detail-jumlah" class="type-body-sm font-bold text-slate-400 uppercase mb-1">Jumlah
                                            (Qty)</label>
                                        <input id="lpjk-detail-jumlah" name="lpjk_detail_jumlah" v-model.number="lpjkDetailItem.Jumlah" type="number"
                                            @input="lpjkDetailItem.Total = (lpjkDetailItem.Satuan||0)*(lpjkDetailItem.Jumlah||0)"
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-body outline-none focus:border-ppp-accent" />
                                    </div>
                                </div>
                                <div>
                                    <label for="lpjk-detail-bukti" class="type-body-sm font-bold text-slate-400 uppercase mb-1">Bukti /
                                        No. Nota</label>
                                    <input id="lpjk-detail-bukti" name="lpjk_detail_bukti" v-model="lpjkDetailItem.Bukti" type="text"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-body outline-none focus:border-ppp-accent"
                                        placeholder="Nota No 01" />
                                </div>
                                <div
                                    class="bg-slate-50 p-3 rounded-xl flex justify-between items-center border border-slate-100">
                                    <span class="text-body-sm font-bold text-slate-500 uppercase">Subtotal</span>
                                    <span class="font-bold text-slate-800">{{ formatCurrency(lpjkDetailItem.Total || 0) }}</span>
                                </div>
                                <button @click="saveLpjkDetail"
                                    :disabled="submitting || !lpjkDetailItem.Nama_Pengeluaran"
                                    class="primary-cta-button w-full active:scale-95 disabled:opacity-50">
                                    <i v-if="submitting" class="fa-solid fa-circle-notch fa-spin"></i>
                                    {{ submitting ? 'Menyimpan...' : 'Tambahkan Item' }}
                                </button>
                            </div>
                            <!-- Print area -->
                            <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl p-4 md:p-8 text-slate-900 overflow-hidden">
                                <div class="text-center mb-8">
                                    <h3 class="dashboard-summary-unit leading-snug">LAPORAN KEGIATAN
                                        PENGGUNAAN<br />DANA {{ activeLpjkRow && activeLpjkRow.Nama_Event }}</h3>
                                </div>
                                <div class="space-y-4 text-sm">
                                    <div class="flex items-start gap-3">
                                        <span class="font-bold">A.</span>
                                        <span class="font-bold">Pengeluaran</span>
                                    </div>
                                    <template v-for="(items, category, catIdx) in lpjkDetailGrouped" :key="category">
                                        <div class="ml-6 space-y-3">
                                            <div class="flex items-start gap-2">
                                                <span class="font-bold">{{ catIdx + 1 }}.</span>
                                                <span class="font-bold">Seksi {{ category }}</span>
                                            </div>
                                            <div class="overflow-x-auto">
                                            <table class="w-full border-collapse text-body-sm min-w-[460px]">
                                                <thead>
                                                    <tr class="table-header-row">
                                                        <th class="table-header-cell w-[5%] text-left">
                                                            NO</th>
                                                        <th class="table-header-cell w-[35%] text-left">
                                                            NAMA PENGELUARAN</th>
                                                        <th class="table-header-cell w-[15%] text-right">
                                                            SATUAN</th>
                                                        <th class="table-header-cell w-[8%] text-center">
                                                            QTY</th>
                                                        <th class="table-header-cell w-[17%] text-right">
                                                            TOTAL BIAYA</th>
                                                        <th class="table-header-cell w-[15%] text-center">
                                                            BUKTI</th>
                                                        <th class="table-header-cell w-[5%] text-center">
                                                            #</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(item, i) in items" :key="item.ID">
                                                        <td class="border-b border-slate-200 px-2 py-2 text-center">
                                                            {{ i + 1 }}</td>
                                                        <td
                                                            class="border-b border-slate-200 px-2 py-2 uppercase text-body-sm">
                                                            {{ item.Nama_Pengeluaran }}</td>
                                                        <td class="border-b border-slate-200 px-2 py-2 text-right">
                                                            {{ formatCurrency(item.Satuan) }}</td>
                                                        <td class="border-b border-slate-200 px-2 py-2 text-center">
                                                            {{ item.Jumlah }}</td>
                                                        <td
                                                            class="border-b border-slate-200 px-2 py-2 text-right font-bold">
                                                            {{ formatCurrency(item.Total) }}</td>
                                                        <td
                                                            class="border-b border-slate-200 px-2 py-2 text-center italic text-body-sm">
                                                            {{ item.Bukti }}</td>
                                                        <td class="border-b border-slate-200 px-1 py-1 text-center">
                                                            <button @click="deleteLpjkDetail(item.ID)"
                                                                class="text-slate-300 hover:text-danger transition px-2 py-1"><i
                                                                    class="fa-solid fa-trash-can text-body-sm"></i></button>
                                                        </td>
                                                    </tr>
                                                    <tr class="font-bold bg-slate-50">
                                                        <td colspan="4"
                                                            class="border-b border-slate-200 px-2 py-2 text-center uppercase text-body-sm">
                                                            JUMLAH</td>
                                                        <td class="border-b border-slate-200 px-2 py-2 text-right">
                                                            {{ formatCurrency(items.reduce((s,d) => s + (Number(d.Total)||0), 0)) }}</td>
                                                        <td colspan="2" class="border-b border-slate-200 px-2 py-2">
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            </div>
                                        </div>
                                    </template>
                                    <div v-if="Object.keys(lpjkDetailGrouped).length === 0"
                                        class="ml-6 text-body text-slate-400 italic py-4">Belum ada pengeluaran.
                                        Tambahkan dari form di sebelah kiri.</div>
                                    <div
                                        class="mt-6 pt-4 border-t-2 border-double border-slate-200 flex justify-between items-center font-bold">
                                        <span class="uppercase text-sm">TOTAL KESELURUHAN PENGELUARAN</span>
                                        <span class="text-heading-sm underline decoration-double underline-offset-4">{{ formatCurrency(lpjkDetailTotal) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
@endverbatim
