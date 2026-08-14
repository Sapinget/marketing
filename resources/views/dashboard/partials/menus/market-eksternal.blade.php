@verbatim
<!-- Intelijen Eksternal tab — shared by market_eksternal, market_ext_goodponsel, market_ext_devstore, market_ext_rumahgadget -->
<template v-if="['market_eksternal','market_ext_goodponsel','market_ext_devstore','market_ext_rumahgadget'].includes(activeTab)">
    <div v-if="!tabDataLoaded['marketEksternal']"
        class="space-y-6 animate-fadeIn pb-10 animate-pulse">
        <div class="section-card section-card-shell">
            <div class="px-6 py-4 border-b border-slate-50 flex gap-6">
                <div class="h-3 bg-slate-200 rounded-full w-28"></div>
                <div class="h-3 bg-slate-200 rounded-full w-32"></div>
                <div class="h-3 bg-slate-200 rounded-full flex-1"></div>
            </div>
            <div class="divide-y divide-slate-50">
                <div v-for="i in 8" :key="'sk-ek'+i" class="px-6 py-5 flex items-center gap-4">
                    <div class="h-4 bg-slate-100 rounded-full w-44"></div>
                    <div class="h-4 bg-slate-100 rounded-full w-24"></div>
                    <div class="h-4 bg-slate-100 rounded-full w-20"></div>
                    <div class="h-4 bg-slate-100 rounded-full flex-1"></div>
                </div>
            </div>
        </div>
    </div>
    <div v-if="tabDataLoaded['marketEksternal']" class="space-y-6 animate-fadeIn pb-10">

        <div v-if="activeTab === 'market_eksternal'" class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="text-[18px] font-bold ppp-page-title">Summary Eksternal</h1>
                <p class="text-[11px] ppp-page-subtitle mt-0.5">Listing kompetitor, top model, top brand, dan produk eksternal terpantau</p>
            </div>
        </div>

        <!-- KPI summary -->
        <div v-if="activeTab === 'market_eksternal'" class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-3 gap-3">
            <div class="dashboard-summary-card-compact stat-card relative overflow-hidden">
                <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-globe text-[120px]"></i></div>
                <p class="dashboard-summary-title">Listing Eksternal</p>
                <div class="flex items-baseline gap-2">
                    <span class="dashboard-summary-value">{{ (eksternalData.summary?.listing_count || eksternalData.total || 0).toLocaleString('id-ID') }}</span>
                </div>
                <p class="text-body-sm text-slate-400 mt-3">{{ (eksternalData.summary?.source_count || (eksternalData.sources || []).length) }} sumber</p>
            </div>
            <div class="dashboard-summary-card-compact stat-card relative overflow-hidden">
                <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-tags text-[120px]"></i></div>
                <p class="dashboard-summary-title">Brand Terpantau</p>
                <div class="flex items-baseline gap-2">
                    <span class="dashboard-summary-value">{{ (eksternalData.summary?.brand_count || 0).toLocaleString('id-ID') }}</span>
                    <span class="dashboard-summary-unit">brand</span>
                </div>
                <p class="text-body-sm text-slate-400 mt-3">brand kompetitor</p>
            </div>
            <div class="dashboard-summary-card-compact stat-card relative overflow-hidden">
                <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-mobile-screen text-[120px]"></i></div>
                <p class="dashboard-summary-title">Model Terpantau</p>
                <div class="flex items-baseline gap-2">
                    <span class="dashboard-summary-value">{{ (eksternalData.summary?.model_count || 0).toLocaleString('id-ID') }}</span>
                    <span class="dashboard-summary-unit">model</span>
                </div>
                <p class="text-body-sm text-slate-400 mt-3">model kompetitor</p>
            </div>
        </div>

        <div v-if="activeTab === 'market_eksternal' && (eksternalData.source_rows || []).length" class="section-card">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-3">
                <h3 class="type-label font-semibold text-slate-700">Source Data</h3>
                <span class="text-body-sm text-slate-400">
                    {{ (eksternalData.source_rows || []).filter(row => row.ok).length }}/{{ (eksternalData.source_rows || []).length }} aktif · {{ (eksternalData.total || 0).toLocaleString('id-ID') }} listing
                </span>
            </div>
            <div class="px-6 py-4 flex flex-wrap gap-3">
                <div v-for="source in eksternalData.source_rows" :key="'src-status-'+source.source" class="flex items-center gap-2 text-body-sm">
                    <span class="font-semibold text-slate-700">{{ source.source }}</span>
                    <span :class="['font-bold', source.ok ? 'text-emerald-600' : 'text-red-500']">{{ (source.count || 0).toLocaleString('id-ID') }}</span>
                    <span class="text-overline text-slate-400 uppercase">{{ source.cache || (source.ok ? 'live' : 'error') }}</span>
                </div>
            </div>
        </div>

        <!-- KPI per source -->
        <div v-if="activeTab !== 'market_eksternal'" class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            <div v-for="(count, src) in eksternalData.by_source" :key="src"
                class="dashboard-summary-card-compact stat-card relative overflow-hidden">
                <p class="dashboard-summary-title">{{ src }}</p>
                <div class="flex items-baseline gap-2">
                    <span class="dashboard-summary-value">{{ (count || 0).toLocaleString('id-ID') }}</span>
                    <span class="dashboard-summary-unit">listing</span>
                </div>
            </div>
        </div>

        <!-- Perubahan Harga Eksternal section -->
        <div class="section-card">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h3 class="type-label font-semibold text-slate-700">Perubahan Harga Eksternal</h3>
                    <p class="text-body-sm text-slate-400 mt-0.5">Deteksi otomatis perubahan harga dari kompetitor</p>
                </div>
                <div class="flex gap-1 bg-slate-100 rounded-lg p-1">
                    <button @click="eksternalChangesDirection = 'all'; loadMarketEksternalChanges()"
                        :class="['toolbar-segment-button rounded-md border-transparent', eksternalChangesDirection === 'all' ? 'bg-white shadow text-slate-700' : 'text-slate-500 hover:text-slate-700']">
                        Semua
                    </button>
                    <button @click="eksternalChangesDirection = 'naik'; loadMarketEksternalChanges()"
                        :class="['toolbar-segment-button rounded-md border-transparent', eksternalChangesDirection === 'naik' ? 'bg-white shadow text-red-600' : 'text-slate-500 hover:text-slate-700']">
                        Naik
                    </button>
                    <button @click="eksternalChangesDirection = 'turun'; loadMarketEksternalChanges()"
                        :class="['toolbar-segment-button rounded-md border-transparent', eksternalChangesDirection === 'turun' ? 'bg-white shadow text-emerald-600' : 'text-slate-500 hover:text-slate-700']">
                        Turun
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="ppp-table w-full">
                    <thead>
                        <tr v-if="activeTab === 'market_eksternal'" class="table-header-row">
                            <th class="table-header-cell table-header-index table-freeze-index">#</th>
                            <th class="table-header-cell">Produk</th>
                            <th class="table-header-cell">Sumber</th>
                            <th class="table-header-cell text-right">Harga Lama</th>
                            <th class="table-header-cell text-right">Harga Baru</th>
                            <th class="table-header-cell text-right">Selisih</th>
                            <th class="table-header-cell text-right">%</th>
                            <th class="table-header-cell">Terdeteksi</th>
                        </tr>
                        <tr v-else class="table-header-row">
                            <th class="table-header-cell table-header-index table-freeze-index">#</th>
                            <th class="table-header-cell">Produk</th>
                            <th class="table-header-cell text-right">Selisih</th>
                            <th class="table-header-cell text-right">Harga Lama</th>
                            <th class="table-header-cell text-right">Harga Baru</th>
                            <th class="table-header-cell text-right">%</th>
                            <th class="table-header-cell">Terdeteksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="filteredEksternalChanges.length === 0">
                            <td :colspan="activeTab === 'market_eksternal' ? 8 : 7" class="text-center text-slate-400 py-8">
                                Belum ada data perubahan harga eksternal
                            </td>
                        </tr>
                        <template v-if="activeTab === 'market_eksternal'">
                            <tr v-for="(row, idx) in pagedMarketEksternalChanges" :key="'epc-sum-'+idx">
                                <td class="px-4 py-3 text-center text-body-sm font-bold text-slate-400 tabular-nums table-freeze-index">{{ (marketEksternalChangesPage - 1) * 15 + idx + 1 }}</td>
                                <td class="font-medium text-body-sm">
                                    {{ marketProductLabel(row) }}
                                </td>
                                <td class="text-body-sm text-slate-500">{{ row.sumber }}</td>
                                <td class="text-right text-body-sm">Rp {{ Math.round(row.harga_lama || 0).toLocaleString('id-ID') }}</td>
                                <td class="text-right text-body-sm font-semibold">Rp {{ Math.round(row.harga_baru || 0).toLocaleString('id-ID') }}</td>
                                <td :class="['text-right font-semibold text-body-sm', (row.selisih || 0) > 0 ? 'text-red-500' : 'text-emerald-600']">
                                    {{ (row.selisih > 0 ? '+' : '') + Math.round(row.selisih || 0).toLocaleString('id-ID') }}
                                </td>
                                <td :class="['text-right text-body-sm', (row.selisih_pct || 0) > 0 ? 'text-red-500' : 'text-emerald-600']">
                                    {{ (row.selisih_pct > 0 ? '+' : '') + (row.selisih_pct || 0).toFixed(2) + '%' }}
                                </td>
                                <td class="text-body-sm text-slate-500 whitespace-nowrap">
                                    {{ row.detected_at ? row.detected_at.slice(0,16).replace('T',' ') : '—' }}
                                </td>
                            </tr>
                        </template>
                        <template v-else>
                            <tr v-for="(row, idx) in pagedMarketEksternalChanges" :key="'epc-src-'+idx">
                                <td class="px-4 py-3 text-center text-body-sm font-bold text-slate-400 tabular-nums table-freeze-index">{{ (marketEksternalChangesPage - 1) * 15 + idx + 1 }}</td>
                                <td class="font-medium text-body-sm">
                                    {{ marketProductLabel(row) }}
                                </td>
                                <td :class="['text-right font-semibold text-body-sm', (row.selisih || 0) > 0 ? 'text-red-500' : 'text-emerald-600']">
                                    {{ (row.selisih > 0 ? '+' : '') + Math.round(row.selisih || 0).toLocaleString('id-ID') }}
                                </td>
                                <td class="text-right text-body-sm">Rp {{ Math.round(row.harga_lama || 0).toLocaleString('id-ID') }}</td>
                                <td class="text-right text-body-sm font-semibold">Rp {{ Math.round(row.harga_baru || 0).toLocaleString('id-ID') }}</td>
                                <td :class="['text-right text-body-sm', (row.selisih_pct || 0) > 0 ? 'text-red-500' : 'text-emerald-600']">
                                    {{ (row.selisih_pct > 0 ? '+' : '') + (row.selisih_pct || 0).toFixed(2) + '%' }}
                                </td>
                                <td class="text-body-sm text-slate-500 whitespace-nowrap">
                                    {{ row.detected_at ? row.detected_at.slice(0,16).replace('T',' ') : '—' }}
                                </td>
                            </tr>
                        </template>
                </tbody>
            </table>
        </div>
            <div class="table-pager-bar">
                <div class="text-body-sm text-slate-400 font-medium">
                    <template v-if="filteredEksternalChanges.length > 0">{{ (marketEksternalChangesPage - 1) * 15 + 1 }}-{{ Math.min(marketEksternalChangesPage * 15, filteredEksternalChanges.length) }} dari {{ filteredEksternalChanges.length }} data</template>
                    <template v-else>0 data</template>
                </div>
                <div class="flex items-center gap-1">
                    <button @click="marketEksternalChangesPage--" :disabled="marketEksternalChangesPage <= 1"
                        aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered">
                        <i class="fa-solid fa-chevron-left text-body-sm"></i>
                    </button>
                    <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ marketEksternalChangesPage }} / {{ marketEksternalChangesTotalPages }}</span>
                    <button @click="marketEksternalChangesPage++" :disabled="marketEksternalChangesPage >= marketEksternalChangesTotalPages"
                        aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered">
                        <i class="fa-solid fa-chevron-right text-body-sm"></i>
                    </button>
                </div>
            </div>
    </div>

        <!-- Daftar Produk Eksternal (summary: Produk | Sumber | Harga) -->
        <div v-if="activeTab === 'market_eksternal'" class="section-card">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="type-label font-semibold text-slate-700">Produk Eksternal</h3>
                <span class="text-body-sm text-slate-400">{{ (eksternalData.rows || []).length }} produk</span>
            </div>
            <div class="overflow-x-auto">
                <table class="ppp-table w-full">
                    <thead>
                        <tr class="table-header-row">
                            <th class="table-header-cell table-header-index table-freeze-index">#</th>
                            <th class="table-header-cell">Produk</th>
                            <th class="table-header-cell">Sumber</th>
                            <th class="table-header-cell text-right">Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!eksternalData.rows || eksternalData.rows.length === 0">
                            <td colspan="4" class="text-center text-slate-400 py-10">Belum ada data eksternal</td>
                        </tr>
                        <tr v-for="(row, idx) in pagedMarketEksternalRows" :key="'eks-sum-'+idx">
                            <td class="px-4 py-3 text-center text-body-sm font-bold text-slate-400 tabular-nums table-freeze-index">{{ (marketEksternalRowsPage - 1) * 15 + idx + 1 }}</td>
                            <td class="font-medium text-body-sm">
                                {{ marketProductLabel(row) }}
                            </td>
                            <td class="text-body-sm text-slate-500">{{ row.sumber }}</td>
                            <td class="text-right font-semibold text-body-sm">
                                Rp {{ Math.round(row.harga || 0).toLocaleString('id-ID') }}
                            </td>
                        </tr>
                </tbody>
            </table>
        </div>
            <div class="table-pager-bar">
                <div class="text-body-sm text-slate-400 font-medium">
                    <template v-if="marketEksternalRows.length > 0">{{ (marketEksternalRowsPage - 1) * 15 + 1 }}-{{ Math.min(marketEksternalRowsPage * 15, marketEksternalRows.length) }} dari {{ marketEksternalRows.length }} data</template>
                    <template v-else>0 data</template>
                </div>
                <div class="flex items-center gap-1">
                    <button @click="marketEksternalRowsPage--" :disabled="marketEksternalRowsPage <= 1"
                        aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered">
                        <i class="fa-solid fa-chevron-left text-body-sm"></i>
                    </button>
                    <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ marketEksternalRowsPage }} / {{ marketEksternalRowsTotalPages }}</span>
                    <button @click="marketEksternalRowsPage++" :disabled="marketEksternalRowsPage >= marketEksternalRowsTotalPages"
                        aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered">
                        <i class="fa-solid fa-chevron-right text-body-sm"></i>
                    </button>
                </div>
            </div>
    </div>

        <!-- Daftar Produk per-source (Produk | Brand | Harga) -->
        <div v-if="['market_ext_goodponsel','market_ext_devstore','market_ext_rumahgadget'].includes(activeTab)" class="section-card">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="type-label font-semibold text-slate-700">Daftar Produk</h3>
                <span class="text-body-sm text-slate-400">{{ (eksternalData.rows || []).length }} produk</span>
            </div>
            <div class="overflow-x-auto">
                <table class="ppp-table w-full">
                    <thead>
                        <tr class="table-header-row">
                            <th class="table-header-cell table-header-index table-freeze-index">#</th>
                            <th class="table-header-cell">Produk</th>
                            <th class="table-header-cell">Brand</th>
                            <th class="table-header-cell text-right">Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!eksternalData.rows || eksternalData.rows.length === 0">
                            <td colspan="4" class="text-center text-slate-400 py-10">Belum ada data</td>
                        </tr>
                        <tr v-for="(row, idx) in pagedMarketEksternalRows" :key="'eks-src-'+idx">
                            <td class="px-4 py-3 text-center text-body-sm font-bold text-slate-400 tabular-nums table-freeze-index">{{ (marketEksternalRowsPage - 1) * 15 + idx + 1 }}</td>
                            <td class="font-medium text-body-sm">
                                {{ marketProductLabel(row) }}
                            </td>
                            <td class="text-body-sm font-medium text-slate-600">{{ row.brand || '—' }}</td>
                            <td class="text-right font-semibold text-body-sm">
                                Rp {{ Math.round(row.harga || 0).toLocaleString('id-ID') }}
                            </td>
                        </tr>
                </tbody>
            </table>
        </div>
            <div class="table-pager-bar">
                <div class="text-body-sm text-slate-400 font-medium">
                    <template v-if="marketEksternalRows.length > 0">{{ (marketEksternalRowsPage - 1) * 15 + 1 }}-{{ Math.min(marketEksternalRowsPage * 15, marketEksternalRows.length) }} dari {{ marketEksternalRows.length }} data</template>
                    <template v-else>0 data</template>
                </div>
                <div class="flex items-center gap-1">
                    <button @click="marketEksternalRowsPage--" :disabled="marketEksternalRowsPage <= 1"
                        aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered">
                        <i class="fa-solid fa-chevron-left text-body-sm"></i>
                    </button>
                    <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ marketEksternalRowsPage }} / {{ marketEksternalRowsTotalPages }}</span>
                    <button @click="marketEksternalRowsPage++" :disabled="marketEksternalRowsPage >= marketEksternalRowsTotalPages"
                        aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered">
                        <i class="fa-solid fa-chevron-right text-body-sm"></i>
                    </button>
                </div>
            </div>
    </div>

    </div>
</template>
@endverbatim
