@verbatim
<!-- Intelijen Harga tab -->
<div v-if="activeTab === 'market_intelijen_harga' && !tabDataLoaded['marketIntelijenHarga']"
    class="space-y-6 animate-fadeIn pb-10 animate-pulse">
    <div class="section-card section-card-shell">
        <div class="px-6 py-4 border-b border-slate-50 flex gap-6">
            <div class="h-3 bg-slate-200 rounded-full w-28"></div>
            <div class="h-3 bg-slate-200 rounded-full w-32"></div>
            <div class="h-3 bg-slate-200 rounded-full flex-1"></div>
        </div>
        <div class="divide-y divide-slate-50">
            <div v-for="i in 8" :key="'sk-ih'+i" class="px-6 py-5 flex items-center gap-4">
                <div class="h-4 bg-slate-100 rounded-full w-44"></div>
                <div class="h-4 bg-slate-100 rounded-full w-24"></div>
                <div class="h-4 bg-slate-100 rounded-full w-20"></div>
                <div class="h-4 bg-slate-100 rounded-full w-20"></div>
                <div class="h-4 bg-slate-100 rounded-full flex-1"></div>
            </div>
        </div>
    </div>
</div>
<div v-if="activeTab === 'market_intelijen_harga' && tabDataLoaded['marketIntelijenHarga']"
    class="space-y-6 animate-fadeIn pb-10">
    <!-- KPI -->
    <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-3">
        <div class="dashboard-summary-card-compact stat-card relative overflow-hidden border-l-4 border-red-400">
            <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-arrow-trend-up text-[120px]"></i></div>
            <p class="dashboard-summary-title">Alert Terlalu Mahal</p>
            <div class="flex items-baseline gap-2">
                <span class="dashboard-summary-value text-red-500">{{ (intelijenHargaData.summary?.too_expensive || 0).toLocaleString('id-ID') }}</span>
            </div>
            <p class="text-body-sm text-red-400 mt-3">di atas market</p>
        </div>
        <div class="dashboard-summary-card-compact stat-card relative overflow-hidden border-l-4 border-amber-400">
            <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-arrow-trend-down text-[120px]"></i></div>
            <p class="dashboard-summary-title">Alert Terlalu Murah</p>
            <div class="flex items-baseline gap-2">
                <span class="dashboard-summary-value text-amber-500">{{ (intelijenHargaData.summary?.too_cheap || 0).toLocaleString('id-ID') }}</span>
            </div>
            <p class="text-body-sm text-amber-400 mt-3">di bawah market</p>
        </div>
        <div class="dashboard-summary-card-compact stat-card relative overflow-hidden border-l-4 border-indigo-400">
            <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-scale-balanced text-[120px]"></i></div>
            <p class="dashboard-summary-title">Matched Produk</p>
            <div class="flex items-baseline gap-2">
                <span class="dashboard-summary-value text-indigo-600">{{ (intelijenHargaData.summary?.matched || 0).toLocaleString('id-ID') }}</span>
            </div>
            <p class="text-body-sm text-indigo-400 mt-3">punya pembanding</p>
        </div>
        <div class="dashboard-summary-card-compact stat-card relative overflow-hidden border-l-4 border-emerald-400">
            <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-tag text-[120px]"></i></div>
            <p class="dashboard-summary-title">Total</p>
            <div class="flex items-baseline gap-2">
                <span class="dashboard-summary-value text-emerald-600">{{ (intelijenHargaData.summary?.total || 0).toLocaleString('id-ID') }}</span>
            </div>
            <p class="text-body-sm text-slate-400 mt-3">produk terbandingkan</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="section-card px-3 py-3">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
            <button @click="ihFilter = ''"
                :class="['min-h-[46px] rounded-md px-3 py-2 text-left transition-all border flex items-center justify-between gap-3',
                    ihFilter === '' ? 'bg-ppp-accent text-white border-ppp-accent shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:border-ppp-accent/40 hover:text-ppp-accent']">
                <span class="flex items-center gap-2 min-w-0">
                    <i class="fa-solid fa-list-check text-body-sm shrink-0"></i>
                    <span class="text-body-sm font-semibold truncate">Semua</span>
                </span>
                <span :class="['text-[11px] font-bold px-2 py-0.5 rounded-full shrink-0',
                    ihFilter === '' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500']">
                    {{ (intelijenHargaData.summary?.total || 0).toLocaleString('id-ID') }}
                </span>
            </button>
            <button @click="ihFilter = 'mahal'"
                :class="['min-h-[46px] rounded-md px-3 py-2 text-left transition-all border flex items-center justify-between gap-3',
                    ihFilter === 'mahal' ? 'bg-red-500 text-white border-red-500 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:border-red-200 hover:text-red-600']">
                <span class="flex items-center gap-2 min-w-0">
                    <i class="fa-solid fa-arrow-trend-up text-body-sm shrink-0"></i>
                    <span class="text-body-sm font-semibold truncate">Terlalu Mahal</span>
                </span>
                <span :class="['text-[11px] font-bold px-2 py-0.5 rounded-full shrink-0',
                    ihFilter === 'mahal' ? 'bg-white/20 text-white' : 'bg-red-50 text-red-600']">
                    {{ (intelijenHargaData.summary?.too_expensive || 0).toLocaleString('id-ID') }}
                </span>
            </button>
            <button @click="ihFilter = 'murah'"
                :class="['min-h-[46px] rounded-md px-3 py-2 text-left transition-all border flex items-center justify-between gap-3',
                    ihFilter === 'murah' ? 'bg-amber-500 text-white border-amber-500 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:border-amber-200 hover:text-amber-600']">
                <span class="flex items-center gap-2 min-w-0">
                    <i class="fa-solid fa-arrow-trend-down text-body-sm shrink-0"></i>
                    <span class="text-body-sm font-semibold truncate">Terlalu Murah</span>
                </span>
                <span :class="['text-[11px] font-bold px-2 py-0.5 rounded-full shrink-0',
                    ihFilter === 'murah' ? 'bg-white/20 text-white' : 'bg-amber-50 text-amber-600']">
                    {{ (intelijenHargaData.summary?.too_cheap || 0).toLocaleString('id-ID') }}
                </span>
            </button>
            <button @click="ihFilter = 'ok'"
                :class="['min-h-[46px] rounded-md px-3 py-2 text-left transition-all border flex items-center justify-between gap-3',
                    ihFilter === 'ok' ? 'bg-indigo-500 text-white border-indigo-500 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-200 hover:text-indigo-600']">
                <span class="flex items-center gap-2 min-w-0">
                    <i class="fa-solid fa-scale-balanced text-body-sm shrink-0"></i>
                    <span class="text-body-sm font-semibold truncate">Matched</span>
                </span>
                <span :class="['text-[11px] font-bold px-2 py-0.5 rounded-full shrink-0',
                    ihFilter === 'ok' ? 'bg-white/20 text-white' : 'bg-indigo-50 text-indigo-600']">
                    {{ (intelijenHargaData.summary?.matched || 0).toLocaleString('id-ID') }}
                </span>
            </button>
        </div>
    </div>

    <!-- Daftar Alert Harga Table -->
    <div class="section-card">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="type-label font-semibold text-slate-700">Daftar Alert Harga</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="ppp-table w-full">
                <thead>
                    <tr class="table-header-row">
                        <th class="table-header-cell table-header-index table-freeze-index">#</th>
                        <th class="table-header-cell">Produk</th>
                        <th class="table-header-cell text-right">Pura Pura Ponsel</th>
                        <th class="table-header-cell text-right">Good Ponsel</th>
                        <th class="table-header-cell text-right">Rumah Gadget</th>
                        <th class="table-header-cell text-right">Devstore</th>
                        <th class="table-header-cell text-right">Selisih</th>
                        <th class="table-header-cell text-right">Gap%</th>
                        <th class="table-header-cell">Prioritas</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="filteredIntelijenHarga.length === 0">
                        <td colspan="9" class="text-center text-slate-400 py-10">Tidak ada data</td>
                    </tr>
                    <tr v-for="(row, idx) in pagedIntelijenHarga" :key="'ih-'+idx"
                        :class="row.gap_pct !== null && row.gap_pct > 5 ? 'bg-red-50' : (row.gap_pct !== null && row.gap_pct < -5 ? 'bg-amber-50' : '')">
                        <td class="px-4 py-3 text-center text-body-sm font-bold text-slate-400 tabular-nums table-freeze-index">{{ (marketIntelijenHargaPage - 1) * 15 + idx + 1 }}</td>
                        <td class="font-medium text-body-sm">
                            {{ marketProductLabel(row) }}
                        </td>
                        <td class="text-right font-semibold text-body-sm">
                            {{ row.harga_pura ? 'Rp ' + Math.round(row.harga_pura).toLocaleString('id-ID') : '—' }}
                        </td>
                        <td class="text-right text-body-sm text-slate-600">
                            {{ row.harga_goodponsel ? 'Rp ' + row.harga_goodponsel.toLocaleString('id-ID') : '—' }}
                        </td>
                        <td class="text-right text-body-sm text-slate-600">
                            {{ row.harga_rumahgadget ? 'Rp ' + row.harga_rumahgadget.toLocaleString('id-ID') : '—' }}
                        </td>
                        <td class="text-right text-body-sm text-slate-600">
                            {{ row.harga_devstore ? 'Rp ' + row.harga_devstore.toLocaleString('id-ID') : '—' }}
                        </td>
                        <td :class="['text-right font-semibold text-body-sm', (row.selisih || 0) > 0 ? 'text-red-500' : ((row.selisih || 0) < 0 ? 'text-amber-500' : 'text-slate-400')]">
                            {{ row.selisih !== null ? ((row.selisih > 0 ? '+' : '') + Math.round(row.selisih).toLocaleString('id-ID')) : '—' }}
                        </td>
                        <td :class="['text-right font-semibold text-body-sm', row.gap_pct > 5 ? 'text-red-500' : (row.gap_pct < -5 ? 'text-amber-500' : 'text-indigo-600')]">
                            {{ row.gap_pct !== null ? (row.gap_pct > 0 ? '+' : '') + row.gap_pct + '%' : '—' }}
                        </td>
                        <td>
                            <span :class="['px-2 py-0.5 rounded-full text-overline font-bold uppercase text-[9px]',
                                row.prioritas === 'Mahal' ? 'bg-red-100 text-red-700' :
                                row.prioritas === 'Murah' ? 'bg-amber-100 text-amber-700' :
                                row.prioritas === 'OK'    ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-500']">
                                {{ row.prioritas || '—' }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="table-pager-bar">
            <div class="text-body-sm text-slate-400 font-medium">
                <template v-if="filteredIntelijenHarga.length > 0">{{ (marketIntelijenHargaPage - 1) * 15 + 1 }}-{{ Math.min(marketIntelijenHargaPage * 15, filteredIntelijenHarga.length) }} dari {{ filteredIntelijenHarga.length }} data</template>
                <template v-else>0 data</template>
            </div>
            <div class="flex items-center gap-1">
                <button @click="marketIntelijenHargaPage--" :disabled="marketIntelijenHargaPage <= 1"
                    aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered">
                    <i class="fa-solid fa-chevron-left text-body-sm"></i>
                </button>
                <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ marketIntelijenHargaPage }} / {{ marketIntelijenHargaTotalPages }}</span>
                <button @click="marketIntelijenHargaPage++" :disabled="marketIntelijenHargaPage >= marketIntelijenHargaTotalPages"
                    aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered">
                    <i class="fa-solid fa-chevron-right text-body-sm"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Perubahan Harga Pura section -->
    <div v-if="tabDataLoaded['marketPuraPriceChanges']" class="section-card">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-3">
            <div>
                <h3 class="type-label font-semibold text-slate-700">Perubahan Harga Pura</h3>
                <p class="text-body-sm text-slate-400 mt-0.5">Otomatis mendeteksi perubahan harga pricelist</p>
            </div>
            <div class="flex gap-1 bg-slate-100 rounded-lg p-1">
                <button @click="puraPriceChangesDirection = 'all'"
                    :class="['toolbar-segment-button rounded-md border-transparent', puraPriceChangesDirection === 'all' ? 'bg-white shadow text-slate-700' : 'text-slate-500 hover:text-slate-700']">
                    Semua
                </button>
                <button @click="puraPriceChangesDirection = 'naik'"
                    :class="['toolbar-segment-button rounded-md border-transparent', puraPriceChangesDirection === 'naik' ? 'bg-white shadow text-red-600' : 'text-slate-500 hover:text-slate-700']">
                    Naik
                </button>
                <button @click="puraPriceChangesDirection = 'turun'"
                    :class="['toolbar-segment-button rounded-md border-transparent', puraPriceChangesDirection === 'turun' ? 'bg-white shadow text-emerald-600' : 'text-slate-500 hover:text-slate-700']">
                    Turun
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="ppp-table w-full">
                <thead>
                    <tr class="table-header-row">
                        <th class="table-header-cell table-header-index table-freeze-index">#</th>
                        <th class="table-header-cell">Produk</th>
                        <th class="table-header-cell text-right">Harga Lama</th>
                        <th class="table-header-cell text-right">Harga Baru</th>
                        <th class="table-header-cell text-right">Selisih</th>
                        <th class="table-header-cell text-right">Selisih %</th>
                        <th class="table-header-cell">Terdeteksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="filteredPuraPriceChanges.length === 0">
                        <td colspan="7" class="text-center text-slate-400 py-8">Tidak ada data perubahan harga</td>
                    </tr>
                    <tr v-for="(row, idx) in pagedIntelijenHargaAudit" :key="'ppc-'+idx">
                        <td class="px-4 py-3 text-center text-body-sm font-bold text-slate-400 tabular-nums table-freeze-index">{{ (marketIntelijenHargaAuditPage - 1) * 15 + idx + 1 }}</td>
                        <td class="font-medium text-body-sm">
                            {{ marketProductLabel(row) }}
                        </td>
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
                </tbody>
            </table>
        </div>
        <div class="table-pager-bar">
            <div class="text-body-sm text-slate-400 font-medium">
                <template v-if="filteredPuraPriceChanges.length > 0">{{ (marketIntelijenHargaAuditPage - 1) * 15 + 1 }}-{{ Math.min(marketIntelijenHargaAuditPage * 15, filteredPuraPriceChanges.length) }} dari {{ filteredPuraPriceChanges.length }} data</template>
                <template v-else>0 data</template>
            </div>
            <div class="flex items-center gap-1">
                <button @click="marketIntelijenHargaAuditPage--" :disabled="marketIntelijenHargaAuditPage <= 1"
                    aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered">
                    <i class="fa-solid fa-chevron-left text-body-sm"></i>
                </button>
                <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ marketIntelijenHargaAuditPage }} / {{ marketIntelijenHargaAuditTotalPages }}</span>
                <button @click="marketIntelijenHargaAuditPage++" :disabled="marketIntelijenHargaAuditPage >= marketIntelijenHargaAuditTotalPages"
                    aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered">
                    <i class="fa-solid fa-chevron-right text-body-sm"></i>
                </button>
            </div>
        </div>
    </div>
    <div v-else class="section-card px-6 py-8 text-center text-slate-400 text-body-sm">
        Data perubahan harga Pura belum termuat.
    </div>
</div>
@endverbatim
