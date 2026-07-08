@verbatim
<div v-if="activeTab === 'analytics'" class="space-y-6 animate-fadeIn pb-10">
    <!-- Summary cards -->
    <div class="space-y-3">
        <div class="dashboard-summary-grid-compact grid grid-cols-3 md:grid-cols-4 gap-3 md:gap-4">
            <div v-for="c in analyticsSummary.cards" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i :class="['fa-solid', c.icon, 'text-[120px]']"></i></div>
                <p :class="['text-[9px] font-bold uppercase tracking-widest mb-3', c.color]">{{ c.label }}</p>
                <div class="flex items-baseline gap-2">
                    <span class="dashboard-summary-value">{{ c.value }}</span>
                    <span v-if="c.unit" :class="['dashboard-summary-unit', c.unitColor]">{{ c.unit }}</span>
                </div>
                <p :class="['text-[10px] font-bold mt-3', c.subColor]">{{ c.sub }}</p>
            </div>
        </div>
    </div>

    <section class="section-card section-card-body">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 md:gap-5">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                    <i class="fa-solid fa-chart-line text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Performance Analytics</h2>
                    <p class="type-body text-slate-500">Analisis performa konten berdasarkan
                        engagement dan view</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-3">
                <div class="relative flex-1 sm:w-64">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                    <input v-model="contentTableSearch" type="text"
                        placeholder="Cari data analitik..." class="form-input-search" />
                </div>
                <div class="relative group">
                    <button @click="openCalendar($event, 'filter')" class="filter-trigger-button toolbar-trigger-field">
                        <i class="fa-solid fa-calendar-days text-[10px] text-slate-400"></i>
                        <template v-if="commonDateFilter.start">
                            {{ formatShortDate(commonDateFilter.start) }}
                            <span v-if="commonDateFilter.end"> - {{ formatShortDate(commonDateFilter.end) }}</span>
                        </template>
                        <template v-else>Semua Tanggal</template>
                        <i v-if="commonDateFilter.start"
                            @click.stop="commonDateFilter = { start: '', end: '' }"
                            class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-red-500"></i>
                    </button>
                </div>
                <div class="toolbar-actions">
                    <button @click="exportExcel"
                        class="secondary-cta-button secondary-cta-success active:scale-95"><i
                            class="fa-solid fa-file-excel"></i><span
                            class="ml-1">Excel</span></button>
                    <button @click="exportPdf"
                        class="secondary-cta-button secondary-cta-danger active:scale-95"><i
                            class="fa-solid fa-file-pdf"></i><span
                            class="ml-1">PDF</span></button>
                </div>
            </div>
        </div>
        <div v-if="analyticsSummary.chips.length" class="chips-grid mt-4 pt-4 border-t border-slate-100">
            <span v-for="ch in analyticsSummary.chips" :key="ch.label" :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold', ch.cls]">{{ ch.label }} <span class="opacity-50">&middot;</span> {{ ch.n }}</span>
        </div>
    </section>

    <div class="md:bg-white md:radius-panel md:border md:border-slate-100 md:overflow-hidden">
        <!-- Mobile View (Cards) -->
        <div class="md:hidden space-y-3 p-3">
            <div v-for="(row, idx) in pagedAnalyticsData" :key="row.ID"
                class="stat-card mobile-record-card mobile-data-card motion-stagger-item"
                :style="getStaggerStyle(idx)">
                <div class="mobile-data-card__header">
                    <div class="flex-1">
                        <div class="mobile-data-card__title mb-1">{{ row.Judul }}</div>
                        <div class="mobile-data-card__meta">
                            <i
                                :class="getPlatformIcon(row.Platform) + ' text-[11px] text-slate-400'"></i>
                            <span
                                class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ row.Platform }}</span>
                        </div>
                    </div>
                </div>
                <div class="mobile-data-card__summary">
                    <div>
                        <div class="type-meta text-slate-400 uppercase font-bold">Views</div>
                        <div class="type-body font-bold text-slate-700">{{ formatNumber(row.Views) }}</div>
                    </div>
                    <div>
                        <div class="type-meta text-slate-400 uppercase font-bold">Score</div>
                        <div class="type-body font-bold text-ppp-accent">{{ calculateScore(row) }}%
                        </div>
                    </div>
                </div>
                <div class="mobile-data-card__actions">
                    <div
                        :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[9px] font-bold text-white uppercase tracking-wider', getVelocity(row).class]">
                        <i :class="getVelocity(row).icon"></i>
                        {{ getVelocity(row).label }}
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="openAnalyticsModal(row)"
                            class="table-action-button table-action-compact" title="Edit"
                            aria-label="Edit"><i class="fa-solid fa-pen text-[10px]"></i></button>
                        <button @click="deleteAnalytics(row.ID)"
                            class="table-action-button table-action-compact table-action-danger"
                            title="Hapus" aria-label="Hapus"><i
                                class="fa-solid fa-trash text-[10px]"></i></button>
                    </div>
                </div>
            </div>
            <div v-if="filteredAnalyticsData.length === 0"
                class="table-empty-state text-slate-400 text-[11px] uppercase tracking-widest">
                Belum
                ada data analitik</div>
        </div>

        <!-- Desktop View (Table) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-[10.5px] text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th
                            class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold">
                            Aksi</th>
                        <th
                            class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold">
                            Judul</th>
                        <th
                            class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold">
                            Platform</th>
                        <th
                            class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold">
                            Tanggal Upload</th>
                        <th
                            class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold text-center">
                            Views</th>
                        <th
                            class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold text-center">
                            Like</th>
                        <th
                            class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold text-center">
                            Komen</th>
                        <th
                            class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold text-center">
                            Share</th>
                        <th
                            class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold text-center">
                            Skor</th>
                        <th
                            class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold text-center">
                            Velocity</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <tr v-for="row in pagedAnalyticsData" :key="row.ID"
                        class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-2">
                                <button @click="openAnalyticsModal(row)"
                                    class="table-action-button table-action-compact" title="Edit"
                                    aria-label="Edit"><i
                                        class="fa-solid fa-pen text-[10px]"></i></button>
                                <button @click="deleteAnalytics(row.ID)"
                                    class="table-action-button table-action-compact table-action-danger"
                                    title="Hapus" aria-label="Hapus"><i
                                        class="fa-solid fa-trash text-[10px]"></i></button>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="text-[10.5px] font-bold text-slate-800">{{ row.Judul }}
                            </div>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <div class="flex flex-col items-center gap-1">
                                <i
                                    :class="getPlatformIcon(row.Platform) + ' text-[12px] text-slate-400'"></i>
                                <span
                                    class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ row.Platform }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="type-body text-slate-500">{{ formatShortDate(row.Tanggal || row.Tanggal_Publish) }}</div>
                        </td>
                        <td class="px-6 py-5 text-center font-bold text-slate-700 text-[11px]">{{ formatNumber(row.Views) }}</td>
                        <td class="px-6 py-5 text-center text-slate-600 text-[11px]">{{ formatNumber(row.Likes) }}</td>
                        <td class="px-6 py-5 text-center text-slate-600 text-[11px]">{{ formatNumber(row.Comments) }}</td>
                        <td class="px-6 py-5 text-center text-slate-600 text-[11px]">{{ formatNumber(row.Shares) }}</td>
                        <td class="px-6 py-5 text-center">
                            <div
                                class="inline-flex items-center justify-center w-8 h-8 rounded-full border border-slate-100 text-[10px] font-bold text-ppp-accent">
                                {{ calculateScore(row) }}%</div>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <div
                                :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[8px] font-bold text-white uppercase tracking-wider', getVelocity(row).class]">
                                <i :class="getVelocity(row).icon"></i>
                                {{ getVelocity(row).label }}
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredAnalyticsData.length === 0">
                        <td colspan="10"
                            class="px-6 py-20 text-center text-slate-400 text-[11px] uppercase tracking-widest">
                            Belum ada data analitik</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div
            class="table-pager-bar">
            <div class="text-[10px] text-slate-400 font-medium">
                <template v-if="filteredAnalyticsData.length > 0">{{ (analyticsPage - 1) * 15 + 1 }}-{{ Math.min(analyticsPage * 15, filteredAnalyticsData.length) }} dari {{ filteredAnalyticsData.length }} data</template>
                <template v-else>0 data</template>
            </div>
            <div class="flex items-center gap-1">
                <button @click="analyticsPage--" :disabled="analyticsPage <= 1"
                    aria-label="Halaman sebelumnya"
                    class="icon-utility-button icon-utility-bordered"><i
                        class="fa-solid fa-chevron-left text-[10px]"></i></button>
                <span class="px-3 text-[10px] font-bold text-ppp-accent">{{ analyticsPage }} / {{ analyticsTotalPages }}</span>
                <button @click="analyticsPage++" :disabled="analyticsPage >= analyticsTotalPages"
                    aria-label="Halaman berikutnya"
                    class="icon-utility-button icon-utility-bordered"><i
                        class="fa-solid fa-chevron-right text-[10px]"></i></button>
            </div>
        </div>
    </div>
</div>
@endverbatim
