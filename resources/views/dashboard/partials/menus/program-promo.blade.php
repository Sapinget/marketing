@verbatim
<!-- Program Promo tab -->
                    <div v-if="activeTab === 'program_promo' && !tabDataLoaded['promo']"
                        class="space-y-6 animate-fadeIn pb-10 animate-pulse">
                        <div class="section-card section-card-shell">
                            <div class="px-6 py-4 border-b border-slate-50 flex gap-6">
                                <div class="h-3 bg-slate-200 rounded-full w-20"></div>
                                <div class="h-3 bg-slate-200 rounded-full w-28"></div>
                                <div class="h-3 bg-slate-200 rounded-full w-24"></div>
                                <div class="h-3 bg-slate-200 rounded-full flex-1"></div>
                                <div class="h-3 bg-slate-200 rounded-full w-16"></div>
                            </div>
                            <div class="divide-y divide-slate-50">
                                <div v-for="i in 8" :key="'sk-pp'+i" class="px-6 py-5 flex items-center gap-4">
                                    <div class="h-4 bg-slate-100 rounded-full w-40"></div>
                                    <div class="h-6 bg-slate-100 rounded-full w-20"></div>
                                    <div class="h-4 bg-slate-100 rounded-full w-28"></div>
                                    <div class="h-4 bg-slate-100 rounded-full flex-1"></div>
                                    <div class="h-4 bg-slate-100 rounded-full w-24"></div>
                                    <div class="flex gap-1">
                                        <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                                        <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="activeTab === 'program_promo' && tabDataLoaded['promo']"
                        class="space-y-6 animate-fadeIn pb-10">
<!-- Summary cards -->
<div class="space-y-3">
    <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
        <div v-for="c in promoSummary.cards.slice(0, 5)" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 opacity-5"><i :class="['fa-solid', c.icon, 'text-[120px]']"></i></div>
            <p class="dashboard-summary-title">{{ c.label }}</p>
            <div class="flex items-baseline gap-2">
                <span class="dashboard-summary-value">{{ c.value }}</span>
                <span v-if="c.unit" class="dashboard-summary-unit">{{ c.unit }}</span>
            </div>
            <p :class="['text-body-sm font-bold mt-3', c.subColor]">{{ c.sub }}</p>
        </div>
    </div>
</div>




                        <!-- Tabel desktop -->
                        <div class="hidden md:block section-card section-card-shell">
                            <div class="table-toolbar-shell">
                                <div class="table-toolbar-shell__left">
                                    <div class="relative flex-1">
                                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                                        <input id="promo-search" name="promo_search" v-model="promoSearch" type="text" placeholder="Cari program..."
                                            autocomplete="off" aria-label="Cari program promo"
                                            class="form-input-search" />
                                    </div>
                                </div>
                                <div class="table-toolbar-shell__right">
                                    <div class="toolbar-actions">
                                        <button @click="openPromoModal('create')"
                                            class="primary-cta-button primary-cta-button--accent active:scale-95">
                                            <i class="fa-solid fa-plus"></i> Tambah
                                        </button>
                                        <button @click="exportPromoToPDF"
                                            class="primary-cta-button primary-cta-button--danger active:scale-95">
                                            <i class="fa-solid fa-file-pdf"></i> PDF
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-body-sm text-left border-collapse min-w-[860px]">
                                    <thead>
                                        <tr class="table-header-row">
                                            <th class="table-header-cell table-header-index table-freeze-index">
                                                #</th>
                                            <th class="table-header-cell table-header-action table-freeze-action">
                                                Aksi</th>
                                            <th class="table-header-cell">
                                                Program</th>
                                            <th class="table-header-cell w-32 text-center">
                                                Varian</th>
                                            <th class="table-header-cell">
                                                Benefit</th>
                                            <th class="table-header-cell">
                                                Rules</th>
                                            <th class="table-header-cell w-28 text-right">
                                                Harga</th>
                                            <th class="table-header-cell w-36 text-center">
                                                Periode</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="pagedPromoRows.length === 0">
                                            <td colspan="8" class="px-5 py-16 text-center text-body text-slate-400">
                                                <i class="fa-solid fa-bullhorn text-3xl mb-3 opacity-20 block"></i>
                                                Belum ada data program promo
                                            </td>
                                        </tr>
                                        <template v-for="(row, idx) in pagedPromoRows" :key="row.ID || idx">
                                            <tr v-if="row.isCategoryHeader" class="bg-slate-50">
                                                <td colspan="8"
                                                    class="px-5 py-2 text-body-sm font-bold text-slate-500 uppercase border-y border-slate-100">
                                                    <i class="fa-solid fa-layer-group mr-2 text-ppp-accent"></i>{{ row.name }}
                                                </td>
                                            </tr>
                                            <tr v-else
                                                class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors group">
                                                <td class="px-5 py-3.5 text-body-sm text-slate-400 text-center table-freeze-index">{{ row.indexInGroup }}</td>
                                                <td class="px-5 py-3.5 text-left table-freeze-action">
                                                    <div class="flex items-center gap-1.5">
                                                        <button @click="openPromoModal('edit', row)"
                                                            class="table-action-button table-action-compact"
                                                            title="Edit" aria-label="Edit"><i
                                                                class="fa-solid fa-pen-to-square text-overline"></i></button>
                                                        <button @click="deletePromo(row.ID)"
                                                            class="table-action-button table-action-compact table-action-danger"
                                                            title="Hapus" aria-label="Hapus"><i
                                                                class="fa-solid fa-trash-can text-overline"></i></button>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-3.5">
                                                    <p
                                                        class="text-body-sm font-bold text-slate-900 uppercase leading-tight">
                                                        {{ row.Program }}</p>
                                                </td>
                                                <td
                                                    class="px-5 py-3.5 text-center text-body font-semibold text-slate-600">
                                                    {{ row.Warna || '-' }}</td>
                                                <td
                                                    class="px-5 py-3.5 text-body text-slate-600 whitespace-pre-wrap max-w-[200px]">
                                                    {{ row.Benefit || '-' }}</td>
                                                <td
                                                    class="px-5 py-3.5 text-body-sm text-slate-500 whitespace-pre-wrap max-w-[180px]">
                                                    {{ row.Rules || '-' }}</td>
                                                <td
                                                    class="px-5 py-3.5 text-right text-body font-bold text-ppp-accent">
                                                    {{ row.Harga ? formatCurrency(row.Harga) : '-' }}</td>
                                                <td class="px-5 py-3.5 text-center text-body-sm text-slate-500 italic">{{ row.Periode || '-' }}</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <div
                                class="table-pager-bar">
                                <div class="text-body-sm text-slate-400 font-medium">{{ groupedPromoRows.length }}
                                    program</div>
                                <div class="flex items-center gap-1">
                                    <button @click="promoPage--" :disabled="promoPage <= 1"
                                        aria-label="Halaman sebelumnya"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-left text-body-sm"></i></button>
                                    <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ promoPage }} / {{ promoTotalPages }}</span>
                                    <button @click="promoPage++" :disabled="promoPage >= promoTotalPages"
                                        aria-label="Halaman berikutnya"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-right text-body-sm"></i></button>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile cards -->
                        <div class="md:hidden space-y-3">
                            <div v-if="filteredPromoData.length === 0"
                                class="bg-white radius-panel border border-slate-100 p-10 text-center text-body text-slate-400">
                                Belum ada data program promo</div>
                            <template v-for="(row, idx) in pagedPromoRows" :key="'mpr'+idx">
                                <div v-if="row.isCategoryHeader"
                                    class="px-2 py-1 text-overline font-bold text-slate-400 uppercase">
                                    <i class="fa-solid fa-layer-group mr-1 text-ppp-accent"></i>{{ row.name }}
                                </div>
                                <div v-else class="bg-white radius-panel border border-slate-100 p-4 space-y-2">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="text-body font-bold text-slate-900 uppercase">{{ row.Program }}</p>
                                        <span v-if="row.Warna"
                                            class="text-overline font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-lg whitespace-nowrap">{{ row.Warna }}</span>
                                    </div>
                                    <p v-if="row.Benefit" class="text-body-sm text-slate-600 whitespace-pre-wrap">{{ row.Benefit }}</p>
                                    <div class="flex items-center justify-between pt-2 border-t border-slate-50">
                                        <span class="text-body-sm text-slate-400 italic">{{ row.Periode || '-' }}</span>
                                        <div class="flex items-center gap-2">
                                            <span v-if="row.Harga" class="text-body font-bold text-ppp-accent">{{ formatCurrency(row.Harga) }}</span>
                                            <button @click="openPromoModal('edit', row)"
                                                class="table-action-button table-action-compact" title="Edit"
                                                aria-label="Edit"><i class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                                            <button @click="deletePromo(row.ID)"
                                                class="table-action-button table-action-compact table-action-danger"
                                                title="Hapus" aria-label="Hapus"><i
                                                    class="fa-solid fa-trash-can text-body-sm"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                    </div>
@endverbatim
