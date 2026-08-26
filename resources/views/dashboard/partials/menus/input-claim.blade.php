@verbatim
<div v-if="activeTab === 'input_claim'" class="space-y-4 animate-fadeIn">
    <!-- KPI Summary Cards -->
    <div class="dashboard-summary-grid-compact grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
        <div v-for="c in inputClaimSummary.cards.slice(0, 5)" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
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
                    <input v-model="inputClaimSearch" type="search" name="input_claim_search" aria-label="Cari input claim" placeholder="Cari customer, invoice, status..." autocomplete="off" class="form-input-search" />
                </div>
            </div>
            <div class="table-toolbar-shell__right">
                <div class="toolbar-actions">
                    <button @click="syncClaimSheets" :disabled="claimSheetSyncing" class="primary-cta-button primary-cta-button--accent active:scale-95" :title="claimSheetSyncing ? 'Sedang sinkronisasi...' : 'Import ulang dari Google Sheets'">
                        <i :class="claimSheetSyncing ? 'fa-solid fa-rotate fa-spin' : 'fa-solid fa-cloud-arrow-down'"></i>
                        <span class="ml-1">{{ claimSheetSyncing ? 'Sinkronisasi...' : 'Import' }}</span>
                    </button>
                    <button @click="exportInputClaimToExcel" class="primary-cta-button primary-cta-button--success active:scale-95"><i class="fa-solid fa-file-excel"></i><span class="ml-1">Excel</span></button>
                </div>
            </div>
        </div>
        <div v-if="!inputClaimLoaded" class="p-6 text-center type-body text-slate-500">Memuat data...</div>
        <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-body-sm min-w-[1160px]">
                <thead>
                    <tr class="table-header-row">
                        <th class="table-header-cell table-header-index table-freeze-index">No</th>
                        <th class="table-header-cell text-left">Toko</th>
                        <th class="table-header-cell text-left">Tanggal Masuk</th>
                        <th class="table-header-cell text-left">Nama Customer</th>
                        <th class="table-header-cell text-left">Nomor HP Customer</th>
                        <th class="table-header-cell text-left">No Invoice</th>
                        <th class="table-header-cell text-left">Keterangan</th>
                        <th class="table-header-cell text-left">Keterangan Complain</th>
                        <th class="table-header-cell text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, idx) in pagedInputClaimRows" :key="row._row_number" class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                        <td class="px-4 py-3 type-body text-slate-500 table-freeze-index">{{ (inputClaimPage - 1) * 15 + idx + 1 }}</td>
                        <td class="px-4 py-3 text-body text-slate-600 font-semibold whitespace-normal break-words">{{ row['Toko'] || '-' }}</td>
                        <td class="px-4 py-3 text-body text-slate-600 whitespace-nowrap">{{ row['Tanggal masuk'] || '-' }}</td>
                        <td class="px-4 py-3 text-body text-slate-800 font-semibold whitespace-normal break-words">{{ row['Nama Customer'] || '-' }}</td>
                        <td class="px-4 py-3 text-body text-slate-600">
                            <div class="flex items-center gap-2">
                                <span>{{ row['Nomor Hp customer'] || '-' }}</span>
                                <a v-if="row['Nomor Hp customer'] && row['Nomor Hp customer'] !== '-'" :href="'https://wa.me/' + (formatWaNumber(row['Nomor Hp customer']).startsWith('62') ? formatWaNumber(row['Nomor Hp customer']) : '62' + formatWaNumber(row['Nomor Hp customer']))" target="_blank" rel="noopener noreferrer" class="text-success hover:text-success/80" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-body text-slate-600 whitespace-normal break-words">{{ row['No Invoice'] || '-' }}</td>
                        <td class="px-4 py-3 text-body text-slate-600 whitespace-normal break-words">{{ row['Keterangan'] || '-' }}</td>
                        <td class="px-4 py-3 text-body text-slate-600 whitespace-normal break-words">{{ row['Keterangan complain'] || '-' }}</td>
                        <td class="px-4 py-3">
                            <span v-if="row['Status']" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap"
                                :class="{
                                    'bg-success/10 text-success': row['Status'] === 'Selesai',
                                    'bg-amber/10 text-amber-700': row['Status'] === 'Proses',
                                }">{{ row['Status'] }}</span>
                            <span v-else class="text-body text-slate-400">-</span>
                        </td>
                    </tr>
                    <tr v-if="filteredInputClaimRows.length === 0">
                        <td colspan="9" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center text-slate-400">
                                <i class="fa-solid fa-file-pen text-4xl mb-3 opacity-20"></i>
                                <p class="type-body font-bold uppercase">Tidak ada data input claim</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="table-pager-bar">
            <div class="text-body-sm text-slate-400 font-medium">
                <template v-if="filteredInputClaimRows.length > 0">{{ (inputClaimPage - 1) * 15 + 1 }}-{{ Math.min(inputClaimPage * 15, filteredInputClaimRows.length) }} dari {{ filteredInputClaimRows.length }} data</template>
                <template v-else>0 data</template>
            </div>
            <div class="flex items-center gap-1">
                <button @click="inputClaimPage--" :disabled="inputClaimPage <= 1" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-left text-body-sm"></i></button>
                <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ inputClaimPage }} / {{ inputClaimTotalPages }}</span>
                <button @click="inputClaimPage++" :disabled="inputClaimPage >= inputClaimTotalPages" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-right text-body-sm"></i></button>
            </div>
        </div>
    </div>
</div>
@endverbatim
