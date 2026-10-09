@verbatim
<!-- Keep Barang / Retur View -->
                    <div v-if="activeTab === 'keep_barang' && !keepBarangLoaded"
                        class="space-y-4 animate-fadeIn animate-pulse">
                        <div class="section-card section-card-shell">
                            <div class="px-6 py-4 border-b border-slate-50 flex gap-6">
                                <div class="h-3 bg-slate-200 rounded-full w-20"></div>
                                <div class="h-3 bg-slate-200 rounded-full w-28"></div>
                                <div class="h-3 bg-slate-200 rounded-full w-24"></div>
                                <div class="h-3 bg-slate-200 rounded-full flex-1"></div>
                            </div>
                            <div class="divide-y divide-slate-50">
                                <div v-for="i in 8" :key="'sk-kb'+i" class="px-6 py-5 flex items-center gap-4">
                                    <div class="h-4 bg-slate-100 rounded-full w-44"></div>
                                    <div class="h-4 bg-slate-100 rounded-full w-20"></div>
                                    <div class="h-6 bg-slate-100 rounded-full w-14"></div>
                                    <div class="h-4 bg-slate-100 rounded-full flex-1"></div>
                                    <div class="flex gap-1">
                                        <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                                        <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="activeTab === 'keep_barang' && keepBarangLoaded" class="space-y-4 animate-fadeIn">
                        <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
                                <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                                    <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-boxes-stacked text-[120px]"></i></div>
                                    <p class="dashboard-summary-title">Total</p>
                                    <div class="flex items-baseline gap-2">
                                        <span class="dashboard-summary-value">{{ formatNumber(keepBarangSummary.total) }}</span>
                                        <span class="dashboard-summary-unit">Item</span>
                                    </div>
                                    <p class="text-body-sm font-bold mt-3 text-slate-600">Semua keep barang</p>
                                </div>
                                <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                                    <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-hourglass-half text-[120px]"></i></div>
                                    <p class="dashboard-summary-title">Pending</p>
                                    <div class="flex items-baseline gap-2">
                                        <span class="dashboard-summary-value">{{ formatNumber(keepBarangSummary.pending) }}</span>
                                        <span class="dashboard-summary-unit">Item</span>
                                    </div>
                                    <p class="text-body-sm font-bold mt-3 text-amber">Menunggu pengambilan</p>
                                </div>
                                <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                                    <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-circle-check text-[120px]"></i></div>
                                    <p class="dashboard-summary-title">Done</p>
                                    <div class="flex items-baseline gap-2">
                                        <span class="dashboard-summary-value">{{ formatNumber(keepBarangSummary.done) }}</span>
                                        <span class="dashboard-summary-unit">Item</span>
                                    </div>
                                    <p class="text-body-sm font-bold mt-3 text-success">Sudah selesai</p>
                                </div>
                                <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                                    <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-circle-xmark text-[120px]"></i></div>
                                    <p class="dashboard-summary-title">Cancel</p>
                                    <div class="flex items-baseline gap-2">
                                        <span class="dashboard-summary-value">{{ formatNumber(keepBarangSummary.cancel) }}</span>
                                        <span class="dashboard-summary-unit">Item</span>
                                    </div>
                                    <p class="text-body-sm font-bold mt-3 text-danger">Dibatalkan</p>
                                </div>
                            </div>
                        <div class="md:hidden space-y-3">
                            <div class="table-toolbar-shell">
                                <div class="table-toolbar-shell__left">
                                    <div class="relative">
                                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                                        <input id="keep-barang-search-mobile" name="keep_barang_search_mobile" v-model="keepBarangSearch" type="text" placeholder="Cari nama / HP / tipe / IMEI..." autocomplete="off" aria-label="Cari keep barang mobile" class="form-input-search" />
                                    </div>
                                </div>
                                <div class="table-toolbar-shell__right">
                                    <div class="toolbar-actions">
                                        <button @click="openKeepBarangModal('create')" class="primary-cta-button primary-cta-button--accent primary-cta-button--icon-only active:scale-95" aria-label="Tambah Barang Ditahan"><i class="fa-solid fa-plus"></i></button>
                                        <button @click="exportKeepBarangToExcel" class="primary-cta-button primary-cta-button--success primary-cta-button--icon-only active:scale-95" aria-label="Export Excel"><i class="fa-solid fa-file-excel"></i></button>
                                        <button @click="exportKeepBarangToPDF" class="primary-cta-button primary-cta-button--danger primary-cta-button--icon-only active:scale-95" aria-label="Export PDF"><i class="fa-solid fa-file-pdf"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div v-if="filteredKeepBarangData.length === 0"
                                    class="bg-white radius-card border border-slate-100 p-10 text-center text-body text-slate-400">
                                    Tidak ada data keep barang
                                </div>
                                <div v-for="(row, idx) in pagedKeepBarangData" :key="'kb-mobile-' + (row.ID || idx)"
                                    class="stat-card mobile-record-card mobile-data-card motion-stagger-item"
                                    :style="getStaggerStyle(idx)">
                                    <div class="mobile-data-card__header">
                                        <span
                                            :class="['px-2.5 py-1 rounded-full text-overline font-bold uppercase', keepBarangStatusClass(row.STATUS)]">
                                            {{ row.STATUS || '-' }}
                                        </span>
                                        <span class="type-body-sm text-slate-400 font-bold uppercase">
                                            {{ row.SISA_HARI_PENGAMBILAN || '-' }} hari
                                        </span>
                                    </div>
                                    <div>
                                        <p class="mobile-data-card__title line-clamp-2">{{ row.NAMA || row.TYPE_HP || '-' }}</p>
                                        <div class="mobile-data-card__meta mt-2">
                                            <span
                                                class="px-2.5 py-1 rounded-lg bg-secondary text-light text-overline font-bold uppercase">
                                                {{ row.HANDLE_BY || '-' }}
                                            </span>
                                            <span
                                                class="px-2.5 py-1 rounded-lg bg-amber text-light text-overline font-bold uppercase">
                                                {{ row.TYPE_HP || '-' }}
                                            </span>
                                        </div>
                                        <p class="type-body-sm text-slate-400 mt-2 line-clamp-1">
                                            {{ row.NOMOR_HP || '-' }}
                                        </p>
                                    </div>
                                    <div class="mobile-data-card__summary">
                                        <div>
                                            <div class="type-body-sm text-slate-400 uppercase">Tgl Keep</div>
                                            <div class="type-body font-bold text-slate-700">{{ row.TANGGAL_KEEP || '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="type-body-sm text-slate-400 uppercase">DP</div>
                                            <div class="type-body font-bold text-ppp-accent">{{ row.DP_UANG_MUKA ? formatCurrency(row.DP_UANG_MUKA) : '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="mobile-data-card__actions">
                                        <div class="type-body-sm text-slate-400 line-clamp-1">
                                            {{ row.HANDLE_BY || row.KASIR_BY || '-' }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a v-if="row.NOMOR_HP" :href="'https://wa.me/62' + formatWaNumber(row.NOMOR_HP)"
                                                target="_blank" rel="noopener noreferrer" class="primary-cta-button primary-cta-button--link">WA 1</a>
                                            <a v-if="row.NOMOR_HP_2" :href="'https://wa.me/62' + formatWaNumber(row.NOMOR_HP_2)"
                                                target="_blank" rel="noopener noreferrer" class="primary-cta-button primary-cta-button--success">WA 2</a>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end gap-2">
                                        <div class="flex items-center gap-2">
                                            <button @click="openKeepBarangModal('edit', row)"
                                                class="table-action-button table-action-compact" title="Edit"
                                                aria-label="Edit"><i class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                                            <button @click="deleteKeepBarang(row.ID)"
                                                class="table-action-button table-action-compact table-action-danger"
                                                title="Hapus" aria-label="Hapus"><i
                                                    class="fa-solid fa-trash-can text-body-sm"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-center gap-2 py-2">
                                <button @click="keepBarangPage--" :disabled="keepBarangPage <= 1"
                                    aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-left text-body-sm"></i></button>
                                <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ keepBarangPage }} / {{ keepBarangTotalPages }}</span>
                                <button @click="keepBarangPage++" :disabled="keepBarangPage >= keepBarangTotalPages"
                                    aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-right text-body-sm"></i></button>
                            </div>
                        </div>
                        <div class="hidden md:block section-card section-card-shell">
                            <div class="table-toolbar-shell">
                                <div class="table-toolbar-shell__left">
                                    <div class="relative">
                                        <i
                                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                                        <input id="keep-barang-search-desktop" name="keep_barang_search_desktop" v-model="keepBarangSearch" type="text"
                                            autocomplete="off" aria-label="Cari keep barang desktop"
                                            placeholder="Cari nama / HP / tipe / IMEI..." class="form-input-search" />
                                    </div>
                                </div>
                                <div class="table-toolbar-shell__right">
                                    <div class="relative search-select-container">
                                        <button type="button" @click="toggleSearchSelect($event, 'keep_status_filter')"
                                            class="select-trigger-button sm:w-40">
                                            <span class="truncate">{{ keepBarangStatusFilter || 'Semua Status' }}</span>
                                            <i class="fa-solid fa-chevron-down text-overline text-slate-400"></i>
                                        </button>
                                        <div v-if="searchSelectOpen === 'keep_status_filter'" :style="popoverStyle"
                                            class="search-select-popover search-select-popover--compact max-h-60 overflow-y-auto">
                                            <div @click="keepBarangStatusFilter = ''; searchSelectOpen = null"
                                                class="popover-option">
                                                Semua Status</div>
                                            <div v-for="s in keepBarangUniqueStatus" :key="s"
                                                @click="keepBarangStatusFilter = s; searchSelectOpen = null"
                                                class="popover-option">
                                                {{ s }}</div>
                                        </div>
                                    </div>
                                    <div class="relative search-select-container">
                                        <button type="button" @click="toggleSearchSelect($event, 'keep_handle_filter')"
                                            class="select-trigger-button sm:w-44">
                                            <span class="truncate">{{ keepBarangHandleByFilter || 'Semua Handle By' }}</span>
                                            <i class="fa-solid fa-chevron-down text-overline text-slate-400"></i>
                                        </button>
                                        <div v-if="searchSelectOpen === 'keep_handle_filter'" :style="popoverStyle"
                                            class="search-select-popover search-select-popover--compact max-h-60 overflow-y-auto">
                                            <div @click="keepBarangHandleByFilter = ''; searchSelectOpen = null"
                                                class="popover-option">
                                                Semua Handle By</div>
                                            <div v-for="h in keepBarangUniqueHandleBy" :key="h"
                                                @click="keepBarangHandleByFilter = h; searchSelectOpen = null"
                                                class="popover-option">
                                                {{ h }}</div>
                                        </div>
                                    </div>
                                    <div class="toolbar-actions toolbar-actions--desktop-icon-only">
<button @click="openKeepBarangModal('create')"
                                             class="primary-cta-button primary-cta-button--accent primary-cta-button--icon-only active:scale-95"
                                             aria-label="Tambah Barang Ditahan"><i
                                                 class="fa-solid fa-plus"></i></button>
                                        <button @click="exportKeepBarangToExcel"
                                            class="primary-cta-button primary-cta-button--success primary-cta-button--icon-only active:scale-95" aria-label="Export Excel"><i
                                                class="fa-solid fa-file-excel"></i></button>
                                        <button @click="exportKeepBarangToPDF"
                                            class="primary-cta-button primary-cta-button--danger primary-cta-button--icon-only active:scale-95" aria-label="Export PDF"><i
                                                class="fa-solid fa-file-pdf"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[1650px] table-fixed text-body-sm text-left border-collapse">
                                    <thead>
                                        <tr class="table-header-row">
                                            <th class="table-header-cell table-header-index table-freeze-index">#</th>
                                            <th class="table-header-cell table-header-action table-freeze-action">Aksi</th>
                                            <th class="table-header-cell text-left w-[100px]">Tgl Keep</th>
                                            <th class="table-header-cell text-left w-[16%]">Nama</th>
                                            <th class="table-header-cell text-left w-[120px]">No HP</th>
                                            <th class="table-header-cell text-left w-[14%]">Type HP</th>
                                            <th class="table-header-cell text-left w-[130px]">IMEI</th>
                                            <th class="table-header-cell text-right w-[110px]">DP</th>
                                            <th class="table-header-cell text-right w-[120px]">Harga Jual</th>
                                            <th class="table-header-cell text-left w-[110px]">Rencana Ambil</th>
                                            <th class="table-header-cell text-right w-[90px]">Sisa Hari</th>
                                            <th class="table-header-cell text-left w-[110px]">Tgl Expired</th>
                                            <th class="table-header-cell text-left w-[120px]">Handle By</th>
                                            <th class="table-header-cell text-center w-[110px]">Status</th>
                                            <th class="table-header-cell text-left w-[130px]">Follow Up</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="filteredKeepBarangData.length === 0">
                                            <td colspan="15" class="px-4 py-12 text-center text-body text-slate-400">
                                                <i
                                                    class="fa-solid fa-box-archive text-2xl mb-3 block opacity-20"></i>Tidak
                                                ada data
                                            </td>
                                        </tr>
                                        <tr v-for="(row, idx) in pagedKeepBarangData" :key="row.ID || idx"
                                            class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                            <td class="px-4 py-3 text-center text-body-sm font-bold text-slate-400 tabular-nums table-freeze-index">{{ idx + 1 }}</td>
                                            <td class="px-4 py-3 text-left table-freeze-action">
                                                <div class="flex items-center gap-1.5">
                                                    <button @click="openKeepBarangModal('edit', row)"
                                                        class="table-action-button table-action-compact" title="Edit"
                                                        aria-label="Edit"><i
                                                            class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                                                    <button @click="deleteKeepBarang(row.ID)"
                                                        class="table-action-button table-action-compact table-action-danger"
                                                        title="Hapus" aria-label="Hapus"><i
                                                            class="fa-solid fa-trash-can text-body-sm"></i></button>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-left type-body text-slate-500 whitespace-nowrap">{{ row.TANGGAL_KEEP || '-' }}</td>
                                            <td class="px-4 py-3 text-left text-body font-semibold text-slate-800 break-words">{{ row.NAMA || '-' }}</td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600">{{ row.NOMOR_HP || '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-left text-body text-slate-700 font-semibold break-words">{{ row.TYPE_HP || '-' }}</td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600 break-words">{{ row.IMEI_FULL || '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-body text-slate-600">{{ row.DP_UANG_MUKA ? formatCurrency(row.DP_UANG_MUKA) : '-' }}</td>
                                            <td class="px-4 py-3 text-right text-body text-slate-600">{{ row.HARGA_JUAL ? formatCurrency(row.HARGA_JUAL) : '-' }}</td>
                                            <td class="px-4 py-3 text-left type-body text-slate-500 whitespace-nowrap">{{ row.RENCANA_PENGAMBILAN || '-' }}</td>
                                            <td class="px-4 py-3 text-right text-body">
                                                <span :class="keepBarangSisaHariClass(row.SISA_HARI_PENGAMBILAN)">{{ row.SISA_HARI_PENGAMBILAN || '-' }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-left type-body text-slate-500 whitespace-nowrap">{{ row.TANGGAL_EXPIRED || '-' }}</td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600">{{ row.HANDLE_BY || '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span :class="keepBarangStatusClass(row.STATUS)"
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap">{{ row.STATUS || '-' }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-left">
                                                <div class="flex gap-1">
                                                    <a v-if="row.NOMOR_HP"
                                                        :href="'https://wa.me/62' + formatWaNumber(row.NOMOR_HP)"
                                                        target="_blank" rel="noopener noreferrer"
                                                        class="px-2 py-1 rounded-lg text-body-sm font-bold bg-success text-white hover:bg-success transition-all">WA
                                                        1</a>
                                                    <a v-if="row.NOMOR_HP_2"
                                                        :href="'https://wa.me/62' + formatWaNumber(row.NOMOR_HP_2)"
                                                        target="_blank" rel="noopener noreferrer"
                                                        class="px-2 py-1 rounded-lg text-body-sm font-bold bg-info text-white hover:bg-info transition-all">WA
                                                        2</a>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div
                                class="table-pager-bar">
                                <div class="text-body-sm text-slate-400 font-medium">
                                    <template v-if="filteredKeepBarangData.length > 0">{{ (keepBarangPage - 1) * 15 + 1 }}-{{ Math.min(keepBarangPage * 15, filteredKeepBarangData.length) }} dari {{ filteredKeepBarangData.length }} data</template>
                                    <template v-else>0 data</template>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button @click="keepBarangPage--" :disabled="keepBarangPage <= 1"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-left text-body-sm"></i></button>
                                    <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ keepBarangPage }} / {{ keepBarangTotalPages }}</span>
                                    <button @click="keepBarangPage++" :disabled="keepBarangPage >= keepBarangTotalPages"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-right text-body-sm"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

    <!-- Keep Barang Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="keepBarangModalOpen"
                class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                <div @click="keepBarangModalOpen = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop">
                </div>
                <div
                    class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface">
                    <div
                        class="modal-header-bar modal-header-bar-sticky radius-sheet-top z-[2010]">
                        <div class="modal-header-copy">
                            <div
                                class="modal-header-icon bg-amber text-light border border-amber">
                                <i class="fa-solid fa-box-archive"></i>
                            </div>
                            <div>
                                <div class="type-heading-sm text-slate-900">{{ keepBarangModalType === 'create' ? 'Tambah' : 'Edit' }} Barang Ditahan</div>
                                <div class="type-body-sm text-slate-400 uppercase mt-0.5">Customer
                                    Service</div>
                            </div>
                        </div>
                        <button @click="keepBarangModalOpen = false" aria-label="Tutup modal"
                            class="icon-utility-button icon-utility-round"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="p-6 overflow-y-auto space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-2">Tanggal
                                    Keep</label>
                                <button type="button" @click="openCalendar($event, 'form', '', 'keepBarangTanggalKeep')"
                                    class="select-trigger-button-form toolbar-trigger-field-form">
                                    <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                    <span
                                        :class="keepBarangForm['TANGGAL_KEEP'] ? 'text-slate-700 font-medium' : 'text-slate-400'">
                                        {{ keepBarangForm['TANGGAL_KEEP'] ? formatShortDate(keepBarangForm['TANGGAL_KEEP']) : 'Pilih tanggal keep' }}
                                    </span>
                                </button>
                            </div>
                            <div>
                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-2">Rencana
                                    Pengambilan</label>
                                <button type="button"
                                    @click="openCalendar($event, 'form', '', 'keepBarangRencanaAmbil')"
                                    class="select-trigger-button-form toolbar-trigger-field-form">
                                    <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                    <span
                                        :class="keepBarangForm['RENCANA_PENGAMBILAN'] ? 'text-slate-700 font-medium' : 'text-slate-400'">
                                        {{ keepBarangForm['RENCANA_PENGAMBILAN'] ? formatShortDate(keepBarangForm['RENCANA_PENGAMBILAN']) : 'Pilih rencana pengambilan' }}
                                    </span>
                                </button>
                            </div>
                            <div class="col-span-2">
                                <label for="keep-nama-customer" class="type-body-sm font-bold text-slate-400 uppercase mb-2">Nama
                                    Customer</label>
                                <input id="keep-nama-customer" name="keep_nama_customer" v-model="keepBarangForm['NAMA']" type="text" class="form-input" />
                            </div>
                            <div>
                                <label for="keep-nomor-hp" class="type-body-sm font-bold text-slate-400 uppercase mb-2">No
                                    HP</label>
                                <input id="keep-nomor-hp" name="keep_nomor_hp" v-model="keepBarangForm['NOMOR_HP']" type="text" placeholder="08xxx"
                                    class="form-input" />
                            </div>
                            <div>
                                <label for="keep-nomor-hp-2" class="type-body-sm font-bold text-slate-400 uppercase mb-2">No
                                    HP 2</label>
                                <input id="keep-nomor-hp-2" name="keep_nomor_hp_2" v-model="keepBarangForm['NOMOR_HP_2']" type="text" placeholder="08xxx (opsional)"
                                    class="form-input" />
                            </div>
                            <div class="relative search-select-container">
                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-2">Type
                                    HP</label>
                                <button type="button" @click="toggleSearchSelect($event, 'keep_type_hp')"
                                    :aria-expanded="searchSelectOpen === 'keep_type_hp' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="keepBarangForm['TYPE_HP'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ keepBarangForm['TYPE_HP'] || 'Pilih Type HP' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'keep_type_hp'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari type HP keep barang" placeholder="Cari type HP..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in keepBarangTypeHpOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="keepBarangForm['TYPE_HP'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', keepBarangForm['TYPE_HP'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="keepBarangTypeHpOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                Belum ada opsi Type HP
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div>
                                <label for="keep-imei-full"
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">IMEI</label>
                                <input id="keep-imei-full" name="keep_imei_full" v-model="keepBarangForm['IMEI_FULL']" type="text" class="form-input" />
                            </div>
                            <div>
                                <label for="keep-dp-uang-muka" class="type-body-sm font-bold text-slate-400 uppercase mb-2">DP
                                    (Uang Muka)</label>
                                <input id="keep-dp-uang-muka" name="keep_dp_uang_muka" v-model.number="keepBarangForm['DP_UANG_MUKA']" type="number" min="0"
                                    class="form-input" />
                            </div>
                            <div>
                                <label for="keep-harga-jual" class="type-body-sm font-bold text-slate-400 uppercase mb-2">Harga
                                    Jual</label>
                                <input id="keep-harga-jual" name="keep_harga_jual" v-model.number="keepBarangForm['HARGA_JUAL']" type="number" min="0"
                                    class="form-input" />
                            </div>
                            <div class="relative search-select-container">
                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-2">Handle
                                    By</label>
                                <button type="button" @click="toggleSearchSelect($event, 'keep_handle_by')"
                                    :aria-expanded="searchSelectOpen === 'keep_handle_by' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="keepBarangForm['HANDLE_BY'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ keepBarangForm['HANDLE_BY'] || 'Pilih Handle By' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'keep_handle_by'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari handle by keep barang"
                                                placeholder="Cari handle by..." class="form-input-popover"
                                                @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in keepBarangHandleByOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="keepBarangForm['HANDLE_BY'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', keepBarangForm['HANDLE_BY'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="keepBarangHandleByOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-2">Kasir
                                    By</label>
                                <button type="button" @click="toggleSearchSelect($event, 'keep_kasir_by')"
                                    :aria-expanded="searchSelectOpen === 'keep_kasir_by' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="keepBarangForm['KASIR_BY'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ keepBarangForm['KASIR_BY'] || 'Pilih Kasir By' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'keep_kasir_by'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari kasir by keep barang"
                                                placeholder="Cari kasir by..." class="form-input-popover"
                                                @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in keepBarangKasirByOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="keepBarangForm['KASIR_BY'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', keepBarangForm['KASIR_BY'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="keepBarangKasirByOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-2">Team
                                    Gudang</label>
                                <button type="button" @click="toggleSearchSelect($event, 'keep_team_gudang')"
                                    :aria-expanded="searchSelectOpen === 'keep_team_gudang' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="keepBarangForm['TEAM_GUDANG'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ keepBarangForm['TEAM_GUDANG'] || 'Pilih Team Gudang' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'keep_team_gudang'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari team gudang keep barang"
                                                placeholder="Cari team gudang..." class="form-input-popover"
                                                @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in keepBarangTeamGudangOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="keepBarangForm['TEAM_GUDANG'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', keepBarangForm['TEAM_GUDANG'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="keepBarangTeamGudangOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div>
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Deadline
                                    Gudang</label>
                                <button type="button"
                                    @click="openCalendar($event, 'form', '', 'keepBarangDeadlineGudang')"
                                    class="select-trigger-button-form toolbar-trigger-field-form">
                                    <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                    <span
                                        :class="keepBarangForm['DEADLINE_TEAM_GUDANG'] ? 'text-slate-700 font-medium' : 'text-slate-400'">
                                        {{ keepBarangForm['DEADLINE_TEAM_GUDANG'] ? formatShortDate(keepBarangForm['DEADLINE_TEAM_GUDANG']) : 'Pilih deadline gudang' }}
                                    </span>
                                </button>
                            </div>
                            <div class="col-span-2">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-1.5">Status</label>
                                <div class="relative search-select-container">
                                    <button type="button" @click="toggleSearchSelect($event, 'keep_form_status')"
                                        class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                        <span class="truncate">{{ keepBarangForm['STATUS'] || 'Pilih Status' }}</span>
                                        <i class="fa-solid fa-chevron-down text-overline text-slate-400"></i>
                                    </button>
                                    <div v-if="searchSelectOpen === 'keep_form_status'" :style="popoverStyle"
                                        class="search-select-popover search-select-popover--compact max-h-60 overflow-y-auto">
                                        <div v-for="status in keepBarangStatusOptions" :key="status"
                                            @click="keepBarangForm['STATUS'] = status; searchSelectOpen = null"
                                            class="popover-option">
                                            {{ status }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer-bar modal-footer-actions">
                        <button @click="keepBarangModalOpen = false" class="primary-cta-button primary-cta-button--neutral">Batal</button>
                        <button @click="saveKeepBarang" :disabled="submitting" class="primary-cta-button">{{ submitting ? 'Menyimpan...' : 'Simpan' }}</button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
@endverbatim
