@verbatim
            <!-- SELL OUT TAB -->
            <div v-if="activeTab === 'sell_out' && !tabDataLoaded['sellOut']"
                class="space-y-4 animate-fadeIn animate-pulse">
                <div class="section-card section-card-shell">
                    <div class="px-6 py-4 border-b border-slate-50 flex gap-6">
                        <div class="h-3 bg-slate-200 rounded-full w-28"></div>
                        <div class="h-3 bg-slate-200 rounded-full w-20"></div>
                        <div class="h-3 bg-slate-200 rounded-full w-24"></div>
                        <div class="h-3 bg-slate-200 rounded-full w-20"></div>
                        <div class="h-3 bg-slate-200 rounded-full flex-1"></div>
                    </div>
                    <div class="divide-y divide-slate-50">
                        <div v-for="i in 8" :key="'sk-so'+i" class="px-6 py-5 flex items-center gap-4">
                            <div class="h-4 bg-slate-100 rounded-full w-36"></div>
                            <div class="h-4 bg-slate-100 rounded-full w-16"></div>
                            <div class="w-20 h-2 bg-slate-100 rounded-full"></div>
                            <div class="h-4 bg-slate-100 rounded-full w-8"></div>
                            <div class="h-6 bg-slate-100 rounded-lg w-16"></div>
                            <div class="h-4 bg-slate-100 rounded-full flex-1"></div>
                            <div class="flex gap-1">
                                <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                                <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="activeTab === 'sell_out' && tabDataLoaded['sellOut']" class="space-y-4 animate-fadeIn">

                <!-- Summary Cards (di atas judul, konsisten) -->
                <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-bullseye text-[120px]"></i></div>
                        <p class="dashboard-summary-title">Total Target</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ sellOutSummary.totalTargets }}</span>
                            <span class="dashboard-summary-unit">Program</span>
                        </div>
                        <p class="text-body-sm font-bold text-amber mt-3">Program aktif</p>
                    </div>
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-check-double text-[120px]"></i></div>
                        <p class="dashboard-summary-title">Tercapai</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ sellOutSummary.achieved }}</span>
                            <span class="dashboard-summary-unit">Target</span>
                        </div>
                        <p class="text-body-sm font-bold text-success mt-3">Dari {{ sellOutSummary.totalTargets }} target</p>
                    </div>
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-box text-[120px]"></i></div>
                        <p class="dashboard-summary-title">Total Realisasi</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ formatNumber(sellOutSummary.totalQty) }}</span>
                            <span class="dashboard-summary-unit">Unit</span>
                        </div>
                        <p class="text-body-sm font-bold text-slate-600 mt-3">Unit terjual</p>
                    </div>
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-sack-dollar text-[120px]"></i></div>
                        <p class="dashboard-summary-title">Total Bonus</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ formatCurrency(sellOutSummary.totalBonus) }}</span>
                        </div>
                        <p class="text-body-sm font-bold text-amber mt-3">Estimasi payout</p>
                    </div>
                </div>



                <!-- Table Desktop -->
                <div class="hidden md:block section-card section-card-shell">
                    <div class="table-toolbar-shell">
                        <div class="table-toolbar-shell__left">
                            <div class="relative">
                                <i
                                    class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                                <input id="sell-out-search" name="sell_out_search" v-model="sellOutSearch" type="text" placeholder="Cari vendor / produk..."
                                    autocomplete="off" aria-label="Cari vendor atau produk sell out"
                                    class="form-input-search" />
                            </div>
                        </div>
                        <div class="table-toolbar-shell__right">
                            <div class="relative group search-select-container">
                                <button @click="toggleSearchSelect($event, 'sellOutVendor')"
                                    class="select-trigger-button toolbar-trigger-field">
                                    <i class="fa-solid fa-building text-body-sm text-slate-400"></i>
                                    <span class="truncate">{{ sellOutVendorFilter || 'Semua Vendor' }}</span>
                                    <i v-if="sellOutVendorFilter" @click.stop="sellOutVendorFilter = ''"
                                        class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-danger"></i>
                                    <i v-else class="fa-solid fa-chevron-down ml-auto text-[9px] text-slate-400"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'sellOutVendor'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div @click="sellOutVendorFilter = ''; searchSelectOpen = null"
                                                :class="['popover-option', !sellOutVendorFilter ? 'popover-option-active' : '']">
                                                Semua Vendor</div>
                                            <div v-for="v in sellOutVendorOptions" :key="v"
                                                @click="sellOutVendorFilter = v; sellOutPage = 1; searchSelectOpen = null"
                                                :class="['popover-option', sellOutVendorFilter === v ? 'popover-option-active' : '']">
                                                {{ v }}</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative group search-select-container">
                                <button @click="toggleSearchSelect($event, 'sellOutMonth')"
                                    class="select-trigger-button toolbar-trigger-field">
                                    <i class="fa-solid fa-calendar text-body-sm text-slate-400"></i>
                                    <span class="truncate">{{ formatMonthLabel(sellOutMonth) || 'Semua Bulan' }}</span>
                                    <i v-if="sellOutMonth" @click.stop="sellOutMonth = ''"
                                        class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-danger"></i>
                                    <i v-else class="fa-solid fa-chevron-down ml-auto text-[9px] text-slate-400"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'sellOutMonth'" :style="popoverStyle"
                                        class="search-select-popover w-48">
                                        <div class="max-h-64 overflow-y-auto custom-scrollbar p-1">
                                            <div @click="sellOutMonth = ''; searchSelectOpen = null"
                                                :class="['popover-option mb-1', !sellOutMonth ? 'popover-option-active' : '']">
                                                Semua Bulan</div>
                                            <div v-for="m in last12Months" :key="m.value"
                                                @click="sellOutMonth = m.value; sellOutPage = 1; searchSelectOpen = null"
                                                :class="['popover-option mb-1', sellOutMonth === m.value ? 'popover-option-active' : '']">
                                                {{ m.label }}</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="toolbar-actions toolbar-actions--desktop-icon-only">
<button @click="openSellOutModal('create')"
                                     class="primary-cta-button primary-cta-button--accent primary-cta-button--icon-only active:scale-95"
                                     aria-label="Tambah Target Sell Out"><i
                                         class="fa-solid fa-plus"></i></button>
                                <button @click="exportSellOutToExcel"
                                    class="primary-cta-button primary-cta-button--success primary-cta-button--icon-only active:scale-95" aria-label="Export Excel"><i
                                        class="fa-solid fa-file-excel"></i></button>
                                <button @click="exportSellOutToPDF"
                                    class="primary-cta-button primary-cta-button--danger primary-cta-button--icon-only active:scale-95" aria-label="Export PDF"><i
                                        class="fa-solid fa-file-pdf"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                    <table class="w-full text-body-sm">
                        <thead>
                            <tr class="table-header-row">
                                <th class="table-header-cell table-header-index table-freeze-index">
                                    #</th>
                                <th class="table-header-cell table-header-action table-freeze-action">
                                    Aksi</th>
                                <th class="table-header-cell">
                                    Vendor</th>
                                <th class="table-header-cell">
                                    Nama Produk</th>
                                <th class="table-header-cell text-center">
                                    Target</th>
                                <th class="table-header-cell text-center">
                                    Terjual</th>
                                <th class="table-header-cell text-center">
                                    Progres</th>
                                <th class="table-header-cell text-center">
                                    Status</th>
                                <th class="table-header-cell text-right">
                                    Bonus/Unit</th>
                                <th class="table-header-cell text-right">
                                    Total Bonus</th>
                                <th class="table-header-cell text-center">
                                    Periode</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="pagedSellOutData.length === 0">
                                <td colspan="12" class="px-5 py-16 text-center text-body text-slate-400">
                                    <i class="fa-solid fa-arrow-trend-up text-3xl mb-3 opacity-20 block"></i>
                                    Belum ada data sell out target
                                </td>
                            </tr>
                            <tr v-for="(row, idx) in pagedSellOutData" :key="row.ID || idx"
                                class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors group">
                                <td class="px-5 py-3.5 text-body-sm text-slate-400 text-center table-freeze-index">{{ (sellOutPage - 1) * 15 + idx + 1 }}</td>
                                <td class="px-5 py-3.5 text-left table-freeze-action">
                                    <div class="flex items-center gap-1.5">
                                        <button @click="openSellOutModal('edit', row)"
                                            class="table-action-button table-action-compact" title="Edit"
                                            aria-label="Edit"><i class="fa-solid fa-pen-to-square text-overline"></i></button>
                                        <button @click="deleteSellOut(row.ID)"
                                            class="table-action-button table-action-compact table-action-danger"
                                            title="Hapus" aria-label="Hapus"><i
                                                class="fa-solid fa-trash-can text-overline"></i></button>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-body font-bold text-slate-700">{{ row.Vendor || '-' }}</td>
                                <td class="px-5 py-3.5">
                                    <p class="text-body font-semibold text-slate-800">{{ row.Nama_Produk || row.Seri || '-' }}</p>
                                    <p v-if="row.Catatan" class="text-overline text-slate-400 mt-0.5">{{ row.Catatan }}</p>
                                </td>
                                <td class="px-5 py-3.5 text-center text-body font-bold text-slate-700">{{ formatNumber(row.Target_Unit) }}</td>
                                <td class="px-5 py-3.5 text-center text-body font-bold text-amber">{{ formatNumber(row.Realisasi_Unit || 0) }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <div class="flex items-center gap-1.5 justify-center">
                                        <div class="w-20 h-2 bg-slate-100 rounded-full overflow-hidden">
                                            <div :style="`width:${getSellOutProgress(row).pct}%`"
                                                :class="['h-full rounded-full transition-all', getSellOutProgress(row).achieved ? 'bg-success' : 'bg-amber']">
                                            </div>
                                        </div>
                                        <span class="text-overline font-bold text-slate-500">{{ getSellOutProgress(row).pct }}%</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span :class="['px-2.5 py-1 rounded-lg text-overline font-bold uppercase', 
                                                    getSellOutProgress(row).status === 'TERPAKAI' ? 'bg-success text-light' : 
                                                    getSellOutProgress(row).status === 'TIDAK DIPAKAI' ? 'bg-amber text-light' :
                                                    getSellOutProgress(row).status === 'PROGRESS' ? 'bg-amber text-light' : 
                                                    'bg-secondary text-light']">
                                        {{ getSellOutProgress(row).status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right text-body text-slate-600">{{ row.Bonus_Nominal ? formatCurrency(row.Bonus_Nominal) : '-' }}</td>
                                <td class="px-5 py-3.5 text-right text-body font-bold text-success">{{ getSellOutProgress(row).bonusTotal > 0 ? formatCurrency(getSellOutProgress(row).bonusTotal) : '-' }}</td>
                                <td class="px-5 py-3.5 text-center text-body-sm text-slate-400">
                                    <template v-if="row.Periode_Start">{{ formatShortDate(row.Periode_Start) }}</template>
                                    <template v-if="row.Periode_Start && row.Periode_End"> - </template>
                                    <template v-if="row.Periode_End">{{ formatShortDate(row.Periode_End) }}</template>
                                    <template v-if="!row.Periode_Start && !row.Periode_End">-</template>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <div class="table-pager-bar">
                        <div class="text-body-sm text-slate-400 font-medium">{{ filteredSellOutData.length }}
                            target</div>
                        <div class="flex items-center gap-1">
                            <button @click="sellOutPage--" :disabled="sellOutPage <= 1" aria-label="Halaman sebelumnya"
                                class="icon-utility-button icon-utility-bordered"><i
                                    class="fa-solid fa-chevron-left text-body-sm"></i></button>
                            <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ sellOutPage }} / {{ sellOutTotalPages }}</span>
                            <button @click="sellOutPage++" :disabled="sellOutPage >= sellOutTotalPages"
                                aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered"><i
                                    class="fa-solid fa-chevron-right text-body-sm"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Mobile Cards -->
                <div class="md:hidden space-y-3">
                    <div v-if="filteredSellOutData.length === 0"
                        class="bg-white radius-panel border border-slate-100 p-10 text-center text-body text-slate-400">
                        Belum ada data sell out</div>
                    <div v-for="(row, idx) in pagedSellOutData" :key="'smo'+idx"
                        class="stat-card mobile-record-card mobile-data-card motion-stagger-item"
                        :style="getStaggerStyle(idx)">
                        <div class="mobile-data-card__header">
                            <div>
                                <p class="mobile-data-card__title">{{ row.Nama_Produk || row.Seri || '-' }}</p>
                                <p class="text-overline font-bold text-slate-400 uppercase mt-0.5">{{ row.Vendor || '-' }}</p>
                            </div>
                            <span
                                :class="['px-2.5 py-1 rounded-lg text-overline font-bold uppercase shrink-0', getSellOutProgress(row).status === 'TERCAPAI' ? 'bg-success text-light' : getSellOutProgress(row).status === 'PROGRESS' ? 'bg-amber text-light' : 'bg-secondary text-light']">{{ getSellOutProgress(row).status }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div :style="`width:${getSellOutProgress(row).pct}%`"
                                    :class="['h-full rounded-full', getSellOutProgress(row).achieved ? 'bg-success' : 'bg-amber']">
                                </div>
                            </div>
                            <span class="text-body-sm font-bold text-slate-500 w-10 text-right">{{ getSellOutProgress(row).pct }}%</span>
                        </div>
                        <div class="mobile-data-card__summary">
                            <div class="bg-slate-50 rounded-xl p-2">
                                <p class="text-overline text-slate-400 font-bold uppercase">Target</p>
                                <p class="text-body font-bold text-slate-800">{{ formatNumber(row.Target_Unit) }}</p>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-2">
                                <p class="text-overline text-slate-400 font-bold uppercase">Terjual</p>
                                <p class="text-body font-bold text-amber">{{ formatNumber(row.Realisasi_Unit || 0) }}</p>
                            </div>
                        </div>
                        <div class="mobile-data-card__actions">
                            <p class="text-overline text-slate-400">{{ row.Periode_Start ? formatShortDate(row.Periode_Start) : '' }}{{ (row.Periode_Start && row.Periode_End) ? ' - ' : '' }}{{ row.Periode_End ? formatShortDate(row.Periode_End) : '' }}</p>
                            <div class="flex gap-1.5">
                                <button @click="openSellOutModal('edit', row)"
                                    class="table-action-button table-action-compact" title="Edit" aria-label="Edit"><i
                                        class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                                <button @click="deleteSellOut(row.ID)"
                                    class="table-action-button table-action-compact table-action-danger" title="Hapus"
                                    aria-label="Hapus"><i class="fa-solid fa-trash-can text-body-sm"></i></button>
                            </div>
                        </div>
                    </div>
                    <!-- Mobile Pagination -->
                    <div class="flex items-center justify-center gap-2 py-2">
                        <button @click="sellOutPage--" :disabled="sellOutPage <= 1"
                            class="icon-utility-button icon-utility-bordered"><i
                                class="fa-solid fa-chevron-left text-body-sm"></i></button>
                        <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ sellOutPage }} / {{ sellOutTotalPages }}</span>
                        <button @click="sellOutPage++" :disabled="sellOutPage >= sellOutTotalPages"
                            class="icon-utility-button icon-utility-bordered"><i
                                class="fa-solid fa-chevron-right text-body-sm"></i></button>
                    </div>
                </div>

            </div>

    <!-- Sell Out Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="sellOutModalOpen"
                class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                <div @click="sellOutModalOpen = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop">
                </div>
                <div
                    class="mobile-sheet modal-width-detail radius-sheet modal-sheet-surface">
                    <div
                        class="modal-header-bar modal-header-bar-sticky radius-sheet-top z-[2010]">
                        <div class="flex items-center gap-3">
                            <div
                                class="modal-header-icon bg-success text-light border border-success">
                                <i class="fa-solid fa-arrow-trend-up"></i>
                            </div>
                            <div>
                                <div class="type-heading-sm text-slate-900">{{ sellOutModalType === 'create' ? 'Tambah Target' : 'Edit Target' }}</div>
                                <div class="type-body-sm text-slate-400 uppercase mt-0.5">Sell Out
                                    Target</div>
                            </div>
                        </div>
                        <button @click="sellOutModalOpen = false" aria-label="Tutup modal"
                            class="icon-utility-button icon-utility-round"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="p-6 overflow-y-auto flex-1 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Vendor -->
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Vendor</label>
                                <div @click="toggleSearchSelect($event, 'sotVendor')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="sellOutForm.Vendor ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ sellOutForm.Vendor || 'Pilih / Ketik Vendor' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </div>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'sotVendor'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari vendor sell out" placeholder="Cari vendor..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in sellOutVendorOptions.filter(o => !searchSelectQuery || String(o || '').toLowerCase().includes(String(searchSelectQuery || '').toLowerCase()))"
                                                :key="opt" @click="sellOutForm.Vendor = opt; searchSelectOpen = null"
                                                :class="['popover-option', sellOutForm.Vendor === opt ? 'popover-option-active' : '']">
                                                {{ opt }}</div>
                                        </div>
                                    </div>
                                </transition>
                                <label for="sell-out-vendor-manual" class="sr-only">Vendor sell out manual</label>
                                <input id="sell-out-vendor-manual" name="sell_out_vendor_manual" v-model="sellOutForm.Vendor" type="text" placeholder="atau ketik manual..." autocomplete="off"
                                    class="w-full bg-transparent border-0 px-4 pt-1 pb-0 text-body-sm text-slate-400 outline-none" />
                            </div>
                            <!-- Kategori -->
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Kategori</label>
                                <div @click="toggleSearchSelect($event, 'sotKategori')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="sellOutForm.Kategori ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ sellOutForm.Kategori || 'Pilih Kategori' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </div>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'sotKategori'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari opsi sell out" placeholder="Cari..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unitKategoriOptions.filter(o => !searchSelectQuery || String(o || '').toLowerCase().includes(String(searchSelectQuery || '').toLowerCase()))"
                                                :key="opt"
                                                @click="sellOutForm.Kategori = opt; sellOutForm.Brand = ''; sellOutForm.Seri = ''; buildSellOutProductName(); searchSelectOpen = null"
                                                :class="['popover-option', sellOutForm.Kategori === opt ? 'popover-option-active' : '']">
                                                {{ opt }}</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <!-- Brand -->
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Brand</label>
                                <div @click="toggleSearchSelect($event, 'sotBrand')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="sellOutForm.Brand ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ sellOutForm.Brand || 'Pilih Brand' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </div>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'sotBrand'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari opsi sell out" placeholder="Cari..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in getBrandOptions(sellOutForm.Kategori).filter(o => !searchSelectQuery || String(o || '').toLowerCase().includes(String(searchSelectQuery || '').toLowerCase()))"
                                                :key="opt"
                                                @click="sellOutForm.Brand = opt; sellOutForm.Seri = ''; buildSellOutProductName(); searchSelectOpen = null"
                                                :class="['popover-option', sellOutForm.Brand === opt ? 'popover-option-active' : '']">
                                                {{ opt }}</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <!-- Seri -->
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Seri</label>
                                <div @click="toggleSearchSelect($event, 'sotSeri')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span :class="sellOutForm.Seri ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ sellOutForm.Seri || 'Pilih / Ketik Seri' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </div>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'sotSeri'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari atau ketik seri sell out" placeholder="Cari / Ketik..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in getSeriOptions(sellOutForm.Kategori, sellOutForm.Brand).filter(o => !searchSelectQuery || String(o || '').toLowerCase().includes(String(searchSelectQuery || '').toLowerCase()))"
                                                :key="opt"
                                                @click="sellOutForm.Seri = opt; buildSellOutProductName(); searchSelectOpen = null"
                                                :class="['popover-option', sellOutForm.Seri === opt ? 'popover-option-active' : '']">
                                                {{ opt }}</div>
                                        </div>
                                    </div>
                                </transition>
                                <label for="sell-out-seri-manual" class="sr-only">Seri sell out manual</label>
                                <input id="sell-out-seri-manual" name="sell_out_seri_manual" v-model="sellOutForm.Seri" @input="buildSellOutProductName" type="text" autocomplete="off"
                                    placeholder="tambah data di menu Nama Stock"
                                    class="w-full bg-transparent border-0 px-4 pt-1 pb-0 text-body-sm text-slate-400 outline-none" />
                            </div>
                            <!-- RAM -->
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">RAM</label>
                                <div @click="toggleSearchSelect($event, 'sotRAM')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span :class="sellOutForm.RAM ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ sellOutForm.RAM || 'Pilih RAM' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </div>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'sotRAM'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari opsi sell out" placeholder="Cari..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unitRAMOptions.filter(o => !searchSelectQuery || String(o || '').toLowerCase().includes(String(searchSelectQuery || '').toLowerCase()))"
                                                :key="opt"
                                                @click="sellOutForm.RAM = opt; buildSellOutProductName(); searchSelectOpen = null"
                                                :class="['popover-option', sellOutForm.RAM === opt ? 'popover-option-active' : '']">
                                                {{ opt }}</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <!-- Internal -->
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Internal</label>
                                <div @click="toggleSearchSelect($event, 'sotInternal')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="sellOutForm.Internal ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ sellOutForm.Internal || 'Pilih Internal' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </div>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'sotInternal'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari opsi sell out" placeholder="Cari..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unitInternalOptions.filter(o => !searchSelectQuery || String(o || '').toLowerCase().includes(String(searchSelectQuery || '').toLowerCase()))"
                                                :key="opt"
                                                @click="sellOutForm.Internal = opt; buildSellOutProductName(); searchSelectOpen = null"
                                                :class="['popover-option', sellOutForm.Internal === opt ? 'popover-option-active' : '']">
                                                {{ opt }}</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <!-- Size -->
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Ukuran</label>
                                <div @click="toggleSearchSelect($event, 'sotSize')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span :class="sellOutForm.Size ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ sellOutForm.Size || 'Pilih Ukuran' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </div>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'sotSize'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari opsi sell out" placeholder="Cari..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unitSizeOptions.filter(o => !searchSelectQuery || String(o || '').toLowerCase().includes(String(searchSelectQuery || '').toLowerCase()))"
                                                :key="opt"
                                                @click="sellOutForm.Size = opt; buildSellOutProductName(); searchSelectOpen = null"
                                                :class="['popover-option', sellOutForm.Size === opt ? 'popover-option-active' : '']">
                                                {{ opt }}</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <!-- Kondisi -->
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Kondisi</label>
                                <div @click="toggleSearchSelect($event, 'sotKondisi')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="sellOutForm.Kondisi ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ sellOutForm.Kondisi || 'Pilih Kondisi' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </div>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'sotKondisi'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari opsi sell out" placeholder="Cari..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unitKondisiOptions.filter(o => !searchSelectQuery || String(o || '').toLowerCase().includes(String(searchSelectQuery || '').toLowerCase()))"
                                                :key="opt"
                                                @click="sellOutForm.Kondisi = opt; buildSellOutProductName(); searchSelectOpen = null"
                                                :class="['popover-option', sellOutForm.Kondisi === opt ? 'popover-option-active' : '']">
                                                {{ opt }}</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <!-- Nama Produk (auto) -->
                            <div class="col-span-2">
                                <label for="sell-out-nama-produk" class="type-body-sm font-bold text-slate-400 uppercase mb-2">Nama
                                    Produk <span class="text-ppp-accent">(auto)</span></label>
                                <input id="sell-out-nama-produk" name="sell_out_nama_produk" v-model="sellOutForm.Nama_Produk" type="text" disabled
                                    class="w-full bg-slate-100 border border-slate-200 rounded-2xl px-4 py-3 text-body font-bold text-slate-500 outline-none cursor-not-allowed"
                                    placeholder="Terisi otomatis dari field di atas..." />
                            </div>
                            <!-- Target Unit -->
                            <div>
                                <label for="sell-out-target-unit" class="type-body-sm font-bold text-slate-400 uppercase mb-2">Target
                                    Unit</label>
                                <input id="sell-out-target-unit" name="sell_out_target_unit" v-model.number="sellOutForm.Target_Unit" type="number" min="0"
                                    class="form-input text-right" />
                            </div>
                            <!-- Bonus per Unit -->
                            <div>
                                <label for="sell-out-bonus-nominal" class="type-body-sm font-bold text-slate-400 uppercase mb-2">Bonus
                                    / Unit (Rp)</label>
                                <input id="sell-out-bonus-nominal" name="sell_out_bonus_nominal" v-model.number="sellOutForm.Bonus_Nominal" type="number" min="0"
                                    class="form-input text-right" />
                            </div>
                            <!-- Realisasi -->
                            <div>
                                <label for="sell-out-realisasi-unit"
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Realisasi
                                    (unit terjual)</label>
                                <input id="sell-out-realisasi-unit" name="sell_out_realisasi_unit" v-model.number="sellOutForm.Realisasi_Unit" type="number" min="0"
                                    class="form-input text-right" />
                            </div>
                            <!-- Preview bonus -->
                            <div
                                class="bg-success border border-success rounded-2xl px-4 py-3 flex items-center justify-between">
                                <span class="text-body-sm font-bold text-success uppercase">Est.
                                    Bonus</span>
                                <span class="text-heading-sm font-bold text-success">{{ formatCurrency((sellOutForm.Realisasi_Unit || 0) >= (sellOutForm.Target_Unit || 0) && (sellOutForm.Target_Unit || 0) > 0 ? (sellOutForm.Realisasi_Unit || 0) * (sellOutForm.Bonus_Nominal || 0) : 0) }}</span>
                            </div>
                            <!-- Periode Start -->
                            <div class="relative search-select-container">
                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-2">Periode
                                    Mulai</label>
                                <button type="button" @click="openCalendar($event, 'form', '', 'sotDate1')"
                                    class="select-trigger-button-form toolbar-trigger-field-form">
                                    <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                    <span
                                        :class="sellOutForm.Periode_Start ? 'text-slate-700 font-medium' : 'text-slate-400'">{{ sellOutForm.Periode_Start ? formatFullDate(sellOutForm.Periode_Start) : 'Pilih Tanggal' }}</span>
                                </button>
                            </div>
                            <!-- Periode End -->
                            <div class="relative search-select-container">
                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-2">Periode
                                    Selesai</label>
                                <button type="button" @click="openCalendar($event, 'form', '', 'sotDate2')"
                                    class="select-trigger-button-form toolbar-trigger-field-form">
                                    <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                    <span
                                        :class="sellOutForm.Periode_End ? 'text-slate-700 font-medium' : 'text-slate-400'">{{ sellOutForm.Periode_End ? formatFullDate(sellOutForm.Periode_End) : 'Pilih Tanggal' }}</span>
                                </button>
                            </div>
                            <!-- Catatan -->
                            <div class="col-span-2">
                                <label for="sell-out-catatan"
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Catatan</label>
                                <textarea id="sell-out-catatan" name="sell_out_catatan" v-model="sellOutForm.Catatan" rows="2" placeholder="Catatan tambahan..."
                                    class="form-input resize-none"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer-bar modal-footer-actions">
                        <button @click="sellOutModalOpen = false" class="primary-cta-button primary-cta-button--neutral">Batal</button>
                        <button @click="saveSellOut" :disabled="submitting"
                            class="primary-cta-button primary-cta-button--success">{{ submitting ? 'Menyimpan...' : 'Simpan' }}</button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
@endverbatim
