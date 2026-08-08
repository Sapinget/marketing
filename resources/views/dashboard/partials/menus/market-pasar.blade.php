@verbatim
<div v-if="activeTab === 'market_pasar' && !tabDataLoaded['marketPasar']"
    class="space-y-6 animate-fadeIn pb-10 animate-pulse">
    <div class="section-card section-card-shell">
        <div class="px-6 py-4 border-b border-slate-50 flex gap-6">
            <div class="h-3 bg-slate-200 rounded-full w-28"></div>
            <div class="h-3 bg-slate-200 rounded-full w-24"></div>
            <div class="h-3 bg-slate-200 rounded-full flex-1"></div>
        </div>
        <div class="divide-y divide-slate-50">
            <div v-for="i in 6" :key="'sk-pasar'+i" class="px-6 py-5 flex items-center gap-4">
                <div class="h-4 bg-slate-100 rounded-full w-44"></div>
                <div class="h-4 bg-slate-100 rounded-full w-24"></div>
                <div class="h-4 bg-slate-100 rounded-full w-20"></div>
                <div class="h-4 bg-slate-100 rounded-full flex-1"></div>
            </div>
        </div>
    </div>
</div>
<div v-if="activeTab === 'market_pasar' && tabDataLoaded['marketPasar']"
    class="space-y-6 animate-fadeIn pb-10">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="type-title text-slate-900">Intelijen Pasar</h1>
            <p class="text-body-sm text-slate-400 mt-1">Market share global & Indonesia, mix penjualan brand, dan kompetitor Bali</p>
        </div>
        <span class="text-body-sm text-slate-400">Update: {{ pasarData.updated_at ? String(pasarData.updated_at).slice(0, 10) : '-' }}</span>
    </div>

    <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
        <div class="dashboard-summary-card-compact stat-card relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-globe text-[120px]"></i></div>
            <p class="dashboard-summary-title">Market Global</p>
            <div class="flex items-baseline gap-2">
                <span class="dashboard-summary-value">{{ (pasarData.market_global || []).length.toLocaleString('id-ID') }}</span>
                <span class="dashboard-summary-unit">brand</span>
            </div>
            <p class="text-body-sm text-slate-400 mt-3">brand terpantau</p>
        </div>
        <div class="dashboard-summary-card-compact stat-card relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-chart-pie text-[120px]"></i></div>
            <p class="dashboard-summary-title">Market Indonesia</p>
            <div class="flex items-baseline gap-2">
                <span class="dashboard-summary-value">{{ (pasarData.market_indonesia || []).length.toLocaleString('id-ID') }}</span>
                <span class="dashboard-summary-unit">brand</span>
            </div>
            <p class="text-body-sm text-slate-400 mt-3">brand terpantau</p>
        </div>
        <div class="dashboard-summary-card-compact stat-card relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-store text-[120px]"></i></div>
            <p class="dashboard-summary-title">Brand Kita</p>
            <div class="flex items-baseline gap-2">
                <span class="dashboard-summary-value">{{ (pasarData.kita_mix || []).length.toLocaleString('id-ID') }}</span>
                <span class="dashboard-summary-unit">brand</span>
            </div>
            <p class="text-body-sm text-slate-400 mt-3">brand terjual</p>
        </div>
        <div class="dashboard-summary-card-compact stat-card relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-shop text-[120px]"></i></div>
            <p class="dashboard-summary-title">Kompetitor Bali</p>
            <div class="flex items-baseline gap-2">
                <span class="dashboard-summary-value">{{ (pasarData.bali_competitor || []).length.toLocaleString('id-ID') }}</span>
                <span class="dashboard-summary-unit">brand</span>
            </div>
            <p class="text-body-sm text-slate-400 mt-3">{{ (pasarData.total || 0).toLocaleString('id-ID') }} listing</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4">
        <div class="section-card section-card-shell overflow-hidden" v-for="chart in pasarCharts" :key="chart.title">
            <div class="px-4 md:px-5 py-3 border-b border-slate-100">
                <h3 class="type-label font-semibold text-slate-900">{{ chart.title }}</h3>
                <p v-if="chart.description" class="text-body-sm text-slate-500 mt-0.5">{{ chart.description }}</p>
            </div>
            <div class="px-4 md:px-5 py-4 overflow-x-auto">
                <div v-if="chart.rows.length === 0" class="text-center text-slate-400 py-8">Tidak ada data</div>
                <div v-else class="min-w-[720px] space-y-1">
                    <div v-for="(row, idx) in chart.rows" :key="chart.title + idx"
                        class="grid grid-cols-[120px_minmax(0,1fr)_56px_44px] items-center gap-3">
                        <span class="text-body-sm font-semibold text-slate-600 truncate">{{ row.label }}</span>
                        <div class="h-2.5 rounded-full bg-slate-200 overflow-hidden">
                            <div class="h-full rounded-full bg-ppp-accent" :style="{ width: Math.max(3, Math.min(100, chart.max ? (Number(row.value || 0) / chart.max * 100) : 0)) + '%' }"></div>
                        </div>
                        <span class="text-body-sm font-semibold text-slate-600 text-right tabular-nums">{{ chart.displayValue(row.value) }}</span>
                        <span class="text-body-sm text-slate-500 text-right tabular-nums">{{ row.share.toLocaleString('id-ID', { maximumFractionDigits: 0 }) }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-4">
            <div>
                <h3 class="type-label font-semibold text-slate-700">Kompetitor Bali</h3>
                <p class="text-body-sm text-slate-400 mt-0.5">Listing dari GoodPonsel dan Rumah Gadget Bali</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="ppp-table w-full">
                <thead>
                    <tr class="table-header-row">
                        <th class="table-header-cell table-header-index table-freeze-index">#</th>
                        <th class="table-header-cell">Brand</th>
                        <th class="table-header-cell text-right">Listing</th>
                        <th class="table-header-cell text-right">Median Harga</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!pasarData.bali_competitor || pasarData.bali_competitor.length === 0">
                        <td colspan="4" class="text-center text-slate-400 py-10">Belum ada data kompetitor</td>
                    </tr>
                    <tr v-for="(comp, idx) in (pasarData.bali_competitor || [])" :key="'comp-'+idx">
                        <td class="px-4 py-3 text-center text-body-sm font-bold text-slate-400 tabular-nums table-freeze-index">{{ idx + 1 }}</td>
                        <td class="font-medium">{{ comp.brand }}</td>
                        <td class="text-right">{{ (comp.count || 0).toLocaleString('id-ID') }}</td>
                        <td class="text-right font-semibold">{{ comp.median_harga ? formatCurrency(comp.median_harga) : '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endverbatim
