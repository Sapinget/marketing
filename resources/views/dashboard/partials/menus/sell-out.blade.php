@verbatim
            <!-- SELL OUT TAB -->
            <div v-if="activeTab === 'sell_out' && !tabDataLoaded['sellOut']"
                class="space-y-6 animate-fadeIn pb-10 animate-pulse">
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
            <div v-if="activeTab === 'sell_out' && tabDataLoaded['sellOut']" class="space-y-6 animate-fadeIn pb-10">

                <!-- Summary Cards (di atas judul, konsisten) -->
                <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i class="fa-solid fa-bullseye text-[120px]"></i></div>
                        <p class="text-overline font-bold uppercase tracking-widest text-blue-500 mb-3">Total Target</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ sellOutSummary.totalTargets }}</span>
                            <span class="dashboard-summary-unit text-blue-400">Program</span>
                        </div>
                        <p class="text-body-sm font-bold text-blue-600 mt-3">Program aktif</p>
                    </div>
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i class="fa-solid fa-check-double text-[120px]"></i></div>
                        <p class="text-overline font-bold uppercase tracking-widest text-emerald-500 mb-3">Tercapai</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ sellOutSummary.achieved }}</span>
                            <span class="dashboard-summary-unit text-emerald-400">Target</span>
                        </div>
                        <p class="text-body-sm font-bold text-emerald-600 mt-3">Dari {{ sellOutSummary.totalTargets }} target</p>
                    </div>
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i class="fa-solid fa-box text-[120px]"></i></div>
                        <p class="text-overline font-bold uppercase tracking-widest text-violet-500 mb-3">Total Realisasi</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ formatNumber(sellOutSummary.totalQty) }}</span>
                            <span class="dashboard-summary-unit text-violet-400">Unit</span>
                        </div>
                        <p class="text-body-sm font-bold text-violet-600 mt-3">Unit terjual</p>
                    </div>
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i class="fa-solid fa-sack-dollar text-[120px]"></i></div>
                        <p class="text-overline font-bold uppercase tracking-widest text-amber-500 mb-3">Total Bonus</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ formatCurrency(sellOutSummary.totalBonus) }}</span>
                        </div>
                        <p class="text-body-sm font-bold text-amber-600 mt-3">Estimasi payout</p>
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
                                        class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-red-500"></i>
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
                                        class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-red-500"></i>
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
                            <div class="toolbar-actions">
                                <button @click="openSellOutModal('create')"
                                    class="primary-cta-button primary-cta-button--accent active:scale-95"><i
                                        class="fa-solid fa-plus"></i> Tambah</button>
                                <button @click="exportSellOutToExcel"
                                    class="secondary-cta-button secondary-cta-success active:scale-95"><i
                                        class="fa-solid fa-file-excel"></i><span
                                        class="ml-1">Excel</span></button>
                                <button @click="exportSellOutToPDF"
                                    class="secondary-cta-button secondary-cta-danger active:scale-95"><i
                                        class="fa-solid fa-file-pdf"></i><span
                                        class="ml-1">PDF</span></button>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                    <table class="w-full text-body-sm">
                        <thead>
                            <tr>
                                <th
                                    class="px-5 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100 w-20 text-left">
                                    Aksi</th>
                                <th
                                    class="px-5 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100 w-10 text-center">
                                    #</th>
                                <th
                                    class="px-5 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100">
                                    Vendor</th>
                                <th
                                    class="px-5 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100">
                                    Nama Produk</th>
                                <th
                                    class="px-5 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100 text-center">
                                    Target</th>
                                <th
                                    class="px-5 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100 text-center">
                                    Terjual</th>
                                <th
                                    class="px-5 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100 text-center">
                                    Progres</th>
                                <th
                                    class="px-5 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100 text-center">
                                    Status</th>
                                <th
                                    class="px-5 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100 text-right">
                                    Bonus/Unit</th>
                                <th
                                    class="px-5 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100 text-right">
                                    Total Bonus</th>
                                <th
                                    class="px-5 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100 text-center">
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
                                <td class="px-5 py-3.5 text-left">
                                    <div class="flex items-center gap-1.5">
                                        <button @click="openSellOutModal('edit', row)"
                                            class="table-action-button table-action-compact" title="Edit"
                                            aria-label="Edit"><i class="fa-solid fa-pen text-overline"></i></button>
                                        <button @click="deleteSellOut(row.ID)"
                                            class="table-action-button table-action-compact table-action-danger"
                                            title="Hapus" aria-label="Hapus"><i
                                                class="fa-solid fa-trash text-overline"></i></button>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-body-sm text-slate-400 text-center">{{ (sellOutPage - 1) * 20 + idx + 1 }}</td>
                                <td class="px-5 py-3.5 text-body font-bold text-slate-700">{{ row.Vendor || '-' }}</td>
                                <td class="px-5 py-3.5">
                                    <p class="text-body font-semibold text-slate-800">{{ row.Nama_Produk || row.Seri || '-' }}</p>
                                    <p v-if="row.Catatan" class="text-overline text-slate-400 mt-0.5">{{ row.Catatan }}</p>
                                </td>
                                <td class="px-5 py-3.5 text-center text-body font-bold text-slate-700">{{ formatNumber(row.Target_Unit) }}</td>
                                <td class="px-5 py-3.5 text-center text-body font-bold text-blue-600">{{ formatNumber(row.Realisasi_Unit || 0) }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <div class="flex items-center gap-1.5 justify-center">
                                        <div class="w-20 h-2 bg-slate-100 rounded-full overflow-hidden">
                                            <div :style="`width:${getSellOutProgress(row).pct}%`"
                                                :class="['h-full rounded-full transition-all', getSellOutProgress(row).achieved ? 'bg-emerald-500' : 'bg-amber-400']">
                                            </div>
                                        </div>
                                        <span class="text-overline font-bold text-slate-500">{{ getSellOutProgress(row).pct }}%</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span :class="['px-2.5 py-1 rounded-lg text-overline font-bold uppercase', 
                                                    getSellOutProgress(row).status === 'TERPAKAI' ? 'bg-emerald-100 text-emerald-700' : 
                                                    getSellOutProgress(row).status === 'TIDAK DIPAKAI' ? 'bg-blue-100 text-blue-700' :
                                                    getSellOutProgress(row).status === 'PROGRESS' ? 'bg-amber-100 text-amber-700' : 
                                                    'bg-slate-100 text-slate-500']">
                                        {{ getSellOutProgress(row).status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right text-body text-slate-600">{{ row.Bonus_Nominal ? formatCurrency(row.Bonus_Nominal) : '-' }}</td>
                                <td class="px-5 py-3.5 text-right text-body font-bold text-emerald-600">{{ getSellOutProgress(row).bonusTotal > 0 ? formatCurrency(getSellOutProgress(row).bonusTotal) : '-' }}</td>
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
                                :class="['px-2.5 py-1 rounded-lg text-overline font-bold uppercase shrink-0', getSellOutProgress(row).status === 'TERCAPAI' ? 'bg-emerald-100 text-emerald-700' : getSellOutProgress(row).status === 'PROGRESS' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500']">{{ getSellOutProgress(row).status }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div :style="`width:${getSellOutProgress(row).pct}%`"
                                    :class="['h-full rounded-full', getSellOutProgress(row).achieved ? 'bg-emerald-500' : 'bg-amber-400']">
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
                                <p class="text-body font-bold text-blue-600">{{ formatNumber(row.Realisasi_Unit || 0) }}</p>
                            </div>
                        </div>
                        <div class="mobile-data-card__actions">
                            <p class="text-overline text-slate-400">{{ row.Periode_Start ? formatShortDate(row.Periode_Start) : '' }}{{ (row.Periode_Start && row.Periode_End) ? ' - ' : '' }}{{ row.Periode_End ? formatShortDate(row.Periode_End) : '' }}</p>
                            <div class="flex gap-1.5">
                                <button @click="openSellOutModal('edit', row)"
                                    class="table-action-button table-action-compact" title="Edit" aria-label="Edit"><i
                                        class="fa-solid fa-pen text-body-sm"></i></button>
                                <button @click="deleteSellOut(row.ID)"
                                    class="table-action-button table-action-compact table-action-danger" title="Hapus"
                                    aria-label="Hapus"><i class="fa-solid fa-trash text-body-sm"></i></button>
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
@endverbatim
