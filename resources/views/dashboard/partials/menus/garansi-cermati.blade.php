@verbatim
<div v-if="activeTab === 'garansi_cermati'" class="space-y-4 animate-fadeIn">
    <!-- KPI Summary Cards -->
    <div class="dashboard-summary-grid-compact grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
        <div v-for="c in garansiCermatiSummary.cards.slice(0, 5)" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
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
                    <input v-model="garansiCermatiSearch" type="search" name="garansi_cermati_search" aria-label="Cari garansi cermati" placeholder="Cari nama, unit, status..." autocomplete="off" class="form-input-search" />
                </div>
            </div>
            <div class="table-toolbar-shell__right">
                <div class="toolbar-actions">
                    <button @click="syncClaimSheets" :disabled="claimSheetSyncing" class="primary-cta-button primary-cta-button--accent active:scale-95" :title="claimSheetSyncing ? 'Sedang sinkronisasi...' : 'Import ulang dari Google Sheets'">
                        <i :class="claimSheetSyncing ? 'fa-solid fa-rotate fa-spin' : 'fa-solid fa-cloud-arrow-down'"></i>
                        <span class="ml-1">{{ claimSheetSyncing ? 'Sinkronisasi...' : 'Import' }}</span>
                    </button>
                    <button @click="exportGaransiCermatiToExcel" class="primary-cta-button primary-cta-button--success active:scale-95"><i class="fa-solid fa-file-excel"></i><span class="ml-1">Excel</span></button>
                </div>
            </div>
        </div>
        <div v-if="!garansiCermatiLoaded" class="p-6 text-center type-body text-slate-500">Memuat data...</div>
        <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-body-sm min-w-[1060px]">
                <thead>
                    <tr class="table-header-row">
                        <th class="table-header-cell table-header-index table-freeze-index">No</th>
                        <th class="table-header-cell text-left">Tanggal Masuk</th>
                        <th class="table-header-cell text-left">Type Unit</th>
                        <th class="table-header-cell text-left">Nama Customer</th>
                        <th class="table-header-cell text-left">No Telp</th>
                        <th class="table-header-cell text-left">Kendala Unit</th>
                        <th class="table-header-cell text-left">Proses Klaim</th>
                        <th class="table-header-cell text-left">Status Perbaikan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, idx) in pagedGaransiCermatiRows" :key="row._row_number" class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                        <td class="px-4 py-3 type-body text-slate-500 table-freeze-index">{{ (garansiCermatiPage - 1) * 15 + idx + 1 }}</td>
                        <td class="px-4 py-3 text-body text-slate-600 whitespace-nowrap">{{ row['Tanggal masuk'] || '-' }}</td>
                        <td class="px-4 py-3 text-body text-slate-800 font-semibold whitespace-normal break-words">{{ row['Type unit'] || '-' }}</td>
                        <td class="px-4 py-3 text-body text-slate-800 whitespace-normal break-words">{{ row['Nama customer'] || '-' }}</td>
                        <td class="px-4 py-3 text-body text-slate-600">
                            <div class="flex items-center gap-2">
                                <span>{{ row['Nomor hp'] || '-' }}</span>
                                <a v-if="row['Nomor hp'] && row['Nomor hp'] !== '-'" :href="'https://wa.me/' + (formatWaNumber(row['Nomor hp']).startsWith('62') ? formatWaNumber(row['Nomor hp']) : '62' + formatWaNumber(row['Nomor hp']))" target="_blank" rel="noopener noreferrer" class="text-success hover:text-success/80" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-body text-slate-600 whitespace-normal break-words">{{ row['Kendala unit'] || '-' }}</td>
                        <td class="px-4 py-3 text-body text-slate-600 whitespace-normal break-words">{{ row['Proses claim'] || '-' }}</td>
                        <td class="px-4 py-3">
                            <span v-if="row['Status perbaikan']" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap"
                                :class="{
                                    'bg-success/10 text-success': row['Status perbaikan'] === 'Service Done',
                                    'bg-amber/10 text-amber-700': row['Status perbaikan'] === 'Proses Perbaikan',
                                    'bg-slate-100 text-slate-500': row['Status perbaikan'] === 'Belum Service',
                                }">{{ row['Status perbaikan'] }}</span>
                            <span v-else class="text-body text-slate-400">-</span>
                        </td>
                    </tr>
                    <tr v-if="filteredGaransiCermatiRows.length === 0">
                        <td colspan="8" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center text-slate-400">
                                <i class="fa-solid fa-shield-halved text-4xl mb-3 opacity-20"></i>
                                <p class="type-body font-bold uppercase">Tidak ada data garansi cermati</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="table-pager-bar">
            <div class="text-body-sm text-slate-400 font-medium">
                <template v-if="filteredGaransiCermatiRows.length > 0">{{ (garansiCermatiPage - 1) * 15 + 1 }}-{{ Math.min(garansiCermatiPage * 15, filteredGaransiCermatiRows.length) }} dari {{ filteredGaransiCermatiRows.length }} data</template>
                <template v-else>0 data</template>
            </div>
            <div class="flex items-center gap-1">
                <button @click="garansiCermatiPage--" :disabled="garansiCermatiPage <= 1" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-left text-body-sm"></i></button>
                <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ garansiCermatiPage }} / {{ garansiCermatiTotalPages }}</span>
                <button @click="garansiCermatiPage++" :disabled="garansiCermatiPage >= garansiCermatiTotalPages" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-right text-body-sm"></i></button>
            </div>
        </div>
    </div>
</div>
@endverbatim
