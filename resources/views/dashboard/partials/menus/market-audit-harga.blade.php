@verbatim
<!-- Audit Harga tab -->
<div v-if="activeTab === 'market_audit_harga' && !tabDataLoaded['marketAuditHarga']"
    class="space-y-6 animate-fadeIn pb-10 animate-pulse">
    <div class="section-card section-card-shell">
        <div class="px-6 py-4 border-b border-slate-50 flex gap-6">
            <div class="h-3 bg-slate-200 rounded-full w-32"></div>
            <div class="h-3 bg-slate-200 rounded-full w-24"></div>
            <div class="h-3 bg-slate-200 rounded-full flex-1"></div>
        </div>
        <div class="divide-y divide-slate-50">
            <div v-for="i in 8" :key="'sk-ah'+i" class="px-6 py-5 flex items-center gap-4">
                <div class="h-4 bg-slate-100 rounded-full w-44"></div>
                <div class="h-4 bg-slate-100 rounded-full w-24"></div>
                <div class="h-4 bg-slate-100 rounded-full w-20"></div>
                <div class="h-4 bg-slate-100 rounded-full w-20"></div>
                <div class="h-4 bg-slate-100 rounded-full flex-1"></div>
            </div>
        </div>
    </div>
</div>
<div v-if="activeTab === 'market_audit_harga' && tabDataLoaded['marketAuditHarga']"
    class="space-y-6 animate-fadeIn pb-10">
    <!-- KPI -->
    <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-3 gap-3">
        <div class="dashboard-summary-card-compact stat-card relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-magnifying-glass-dollar text-[120px]"></i></div>
            <p class="dashboard-summary-title">Total Selisih</p>
            <div class="flex items-baseline gap-2">
                <span class="dashboard-summary-value">{{ (auditHargaData.summary?.diff_count || 0).toLocaleString('id-ID') }}</span>
            </div>
            <p class="text-body-sm text-slate-400 mt-3">item beda harga</p>
        </div>
        <div class="dashboard-summary-card-compact stat-card relative overflow-hidden border-l-4 border-amber-400">
            <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-globe text-[120px]"></i></div>
            <p class="dashboard-summary-title">Selisih Website</p>
            <div class="flex items-baseline gap-2">
                <span class="dashboard-summary-value text-amber-600">{{ (auditHargaData.summary?.web_diff_count || 0).toLocaleString('id-ID') }}</span>
                <span class="dashboard-summary-unit">vs dashboard</span>
            </div>
        </div>
        <div class="dashboard-summary-card-compact stat-card relative overflow-hidden border-l-4 border-indigo-400">
            <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-table text-[120px]"></i></div>
            <p class="dashboard-summary-title">Selisih Spreadsheet</p>
            <div class="flex items-baseline gap-2">
                <span class="dashboard-summary-value text-indigo-600">{{ (auditHargaData.summary?.sheet_diff_count || 0).toLocaleString('id-ID') }}</span>
                <span class="dashboard-summary-unit">vs dashboard</span>
            </div>
        </div>
    </div>

    <!-- Daftar Selisih Harga -->
    <div class="section-card">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-3">
            <div>
                <h3 class="type-label font-semibold text-slate-700">Daftar Selisih Harga</h3>
                <p class="text-body-sm text-slate-400 mt-0.5">Dashboard vs Website vs Spreadsheet</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="ppp-table w-full">
                <thead>
                    <tr class="table-header-row">
                        <th class="table-header-cell table-header-index table-freeze-index">#</th>
                        <th class="table-header-cell">Produk</th>
                        <th class="table-header-cell">Toko</th>
                        <th class="table-header-cell text-right">Dashboard</th>
                        <th class="table-header-cell text-right">Website</th>
                        <th class="table-header-cell text-right">Spreadsheet</th>
                        <th class="table-header-cell text-right">Selisih Web</th>
                        <th class="table-header-cell text-right">Selisih Sheet</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="filteredAuditHarga.length === 0">
                        <td colspan="7" class="text-center text-slate-400 py-10">Belum ada data</td>
                    </tr>
                    <tr v-for="(row, idx) in pagedMarketAuditHarga" :key="'ah-'+idx">
                        <td class="px-4 py-3 text-center text-body-sm font-bold text-slate-400 tabular-nums table-freeze-index">{{ (marketAuditHargaPage - 1) * 15 + idx + 1 }}</td>
                        <td class="font-medium text-body-sm">
                            {{ row['Produk'] || marketProductLabel(row) }}
                        </td>
                        <td class="text-body-sm text-slate-500 whitespace-nowrap">{{ row['Toko'] || '—' }}</td>
                        <td class="text-right text-body-sm font-semibold">{{ row['H. Dashboard'] ? formatCurrency(row['H. Dashboard']) : '—' }}</td>
                        <td class="text-right text-body-sm">{{ row['H. Website'] ? formatCurrency(row['H. Website']) : '—' }}</td>
                        <td class="text-right text-body-sm">{{ row['H. Spreadsheet'] ? formatCurrency(row['H. Spreadsheet']) : '—' }}</td>
                        <td :class="['text-right font-semibold text-body-sm', (row['Selisih Web (Rp)'] || 0) > 0 ? 'text-red-500' : 'text-emerald-600']">
                            {{ row['Selisih Web (Rp)'] === null || row['Selisih Web (Rp)'] === undefined ? '—' : formatCurrency(row['Selisih Web (Rp)']) }}
                        </td>
                        <td :class="['text-right font-semibold text-body-sm', (row['Selisih Sheet (Rp)'] || 0) > 0 ? 'text-red-500' : 'text-emerald-600']">
                            {{ row['Selisih Sheet (Rp)'] === null || row['Selisih Sheet (Rp)'] === undefined ? '—' : formatCurrency(row['Selisih Sheet (Rp)']) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="table-pager-bar">
            <div class="text-body-sm text-slate-400 font-medium">
                <template v-if="filteredAuditHarga.length > 0">{{ (marketAuditHargaPage - 1) * 15 + 1 }}-{{ Math.min(marketAuditHargaPage * 15, filteredAuditHarga.length) }} dari {{ filteredAuditHarga.length }} data</template>
                <template v-else>0 data</template>
            </div>
            <div class="flex items-center gap-1">
                <button @click="marketAuditHargaPage--" :disabled="marketAuditHargaPage <= 1"
                    aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered">
                    <i class="fa-solid fa-chevron-left text-body-sm"></i>
                </button>
                <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ marketAuditHargaPage }} / {{ marketAuditHargaTotalPages }}</span>
                <button @click="marketAuditHargaPage++" :disabled="marketAuditHargaPage >= marketAuditHargaTotalPages"
                    aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered">
                    <i class="fa-solid fa-chevron-right text-body-sm"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endverbatim
