@verbatim
<div v-if="activeTab === 'garansi_resmi'" class="space-y-4 animate-fadeIn">
    <!-- KPI Summary Cards -->
    <div class="dashboard-summary-grid-compact grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
        <div v-for="c in garansiResmiSummary.cards.slice(0, 5)" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 opacity-5"><i :class="['fa-solid', c.icon, 'text-[120px]']"></i></div>
            <p class="dashboard-summary-title">{{ c.label }}</p>
            <div class="flex items-baseline gap-2">
                <span class="dashboard-summary-value">{{ c.value }}</span>
                <span v-if="c.unit" class="dashboard-summary-unit">{{ c.unit }}</span>
            </div>
            <p :class="['text-body-sm font-bold mt-3', c.subColor]">{{ c.sub }}</p>
        </div>
    </div>
    <!-- Table -->
    <div class="section-card section-card-shell">
        <div class="table-toolbar-shell">
            <div class="table-toolbar-shell__left">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                    <input v-model="garansiResmiSearch" type="search" name="garansi_resmi_search" aria-label="Cari garansi resmi" placeholder="Cari nama, IMEI, status..." autocomplete="off" class="form-input-search" />
                </div>
            </div>
            <div class="table-toolbar-shell__right">
                <div class="toolbar-actions">
                    <button @click="syncClaimSheets" :disabled="claimSheetSyncing" class="primary-cta-button primary-cta-button--accent active:scale-95" :title="claimSheetSyncing ? 'Sedang sinkronisasi...' : 'Import ulang dari Google Sheets'">
                        <i :class="claimSheetSyncing ? 'fa-solid fa-rotate fa-spin' : 'fa-solid fa-cloud-arrow-down'"></i>
                        <span class="ml-1">{{ claimSheetSyncing ? 'Sinkronisasi...' : 'Import' }}</span>
                    </button>
                    <button @click="exportGaransiResmiToExcel" class="primary-cta-button primary-cta-button--success active:scale-95"><i class="fa-solid fa-file-excel"></i><span class="ml-1">Excel</span></button>
                </div>
            </div>
        </div>
        <div v-if="!garansiResmiLoaded" class="p-6 text-center type-body text-slate-500">Memuat data...</div>
        <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-body-sm min-w-[1060px]">
                <thead>
                    <tr class="table-header-row">
                        <th class="table-header-cell table-header-index table-freeze-index">No</th>
                        <th class="table-header-cell text-left">Nama</th>
                        <th class="table-header-cell text-left">HP</th>
                        <th class="table-header-cell text-left">Type Unit</th>
                        <th class="table-header-cell text-left">IMEI</th>
                        <th class="table-header-cell text-left">Kerusakan</th>
                        <th class="table-header-cell text-left">Status Perbaikan</th>
                        <th class="table-header-cell text-left">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, idx) in pagedGaransiResmiRows" :key="row._row_number" class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                        <td class="px-4 py-3 type-body text-slate-500 table-freeze-index">{{ (garansiResmiPage - 1) * 15 + idx + 1 }}</td>
                        <td class="px-4 py-3 text-body text-slate-800 font-semibold whitespace-normal break-words">{{ row['Nama'] || '-' }}</td>
                        <td class="px-4 py-3 text-body text-slate-600">
                            <div class="flex items-center gap-2">
                                <span>{{ row['Hp'] || '-' }}</span>
                                <a v-if="row['Hp'] && row['Hp'] !== '-'" :href="'https://wa.me/' + (formatWaNumber(row['Hp']).startsWith('62') ? formatWaNumber(row['Hp']) : '62' + formatWaNumber(row['Hp']))" target="_blank" rel="noopener noreferrer" class="text-success hover:text-success/80" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-body text-slate-800 font-semibold whitespace-normal break-words">{{ row['Type unit'] || '-' }}</td>
                        <td class="px-4 py-3 text-body text-slate-600 font-mono whitespace-normal break-words">{{ row['IMEI'] || '-' }}</td>
                        <td class="px-4 py-3 text-body text-slate-600 whitespace-normal break-words">{{ row['Kerusakan'] || '-' }}</td>
                        <td class="px-4 py-3">
                            <span v-if="row['Status perbaikan']" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap"
                                :class="{
                                    'bg-success/10 text-success': row['Status perbaikan'] === 'Done',
                                    'bg-amber/10 text-amber-700': row['Status perbaikan'] === 'Progress Claim',
                                    'bg-slate-100 text-slate-500': row['Status perbaikan'] === 'Not Started',
                                    'bg-danger/10 text-danger': row['Status perbaikan'] === 'Cancel',
                                }">{{ row['Status perbaikan'] }}</span>
                            <span v-else class="text-body text-slate-400">-</span>
                        </td>
                        <td class="px-4 py-3 text-body text-slate-600 whitespace-normal break-words">{{ row['Keterangan'] || '-' }}</td>
                    </tr>
                    <tr v-if="filteredGaransiResmiRows.length === 0">
                        <td colspan="8" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center text-slate-400">
                                <i class="fa-solid fa-screwdriver-wrench text-4xl mb-3 opacity-20"></i>
                                <p class="type-body font-bold uppercase">Tidak ada data garansi resmi</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="table-pager-bar">
            <div class="text-body-sm text-slate-400 font-medium">
                <template v-if="filteredGaransiResmiRows.length > 0">{{ (garansiResmiPage - 1) * 15 + 1 }}-{{ Math.min(garansiResmiPage * 15, filteredGaransiResmiRows.length) }} dari {{ filteredGaransiResmiRows.length }} data</template>
                <template v-else>0 data</template>
            </div>
            <div class="flex items-center gap-1">
                <button @click="garansiResmiPage--" :disabled="garansiResmiPage <= 1" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-left text-body-sm"></i></button>
                <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ garansiResmiPage }} / {{ garansiResmiTotalPages }}</span>
                <button @click="garansiResmiPage++" :disabled="garansiResmiPage >= garansiResmiTotalPages" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-right text-body-sm"></i></button>
            </div>
        </div>
    </div>
</div>
@endverbatim
