@verbatim
<div v-if="activeTab === 'analytics'" class="space-y-6 animate-fadeIn pb-10">
    <!-- Summary cards -->
    <div class="space-y-3">
        <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
            <div v-for="c in analyticsSummary.cards" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i :class="['fa-solid', c.icon, 'text-[120px]']"></i></div>
                <p :class="['text-overline font-bold uppercase tracking-widest mb-3', c.color]">{{ c.label }}</p>
                <div class="flex items-baseline gap-2">
                    <span class="dashboard-summary-value">{{ c.value }}</span>
                    <span v-if="c.unit" :class="['dashboard-summary-unit', c.unitColor]">{{ c.unit }}</span>
                </div>
                <p :class="['text-body-sm font-bold mt-3', c.subColor]">{{ c.sub }}</p>
            </div>
        </div>
    </div>


    <div class="md:hidden space-y-3 p-3">
        <div class="table-toolbar-shell">
            <div class="table-toolbar-shell__left">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                    <input id="analytics-search-mobile" name="analytics_search_mobile" v-model="contentTableSearch" type="text" placeholder="Cari data analitik..." autocomplete="off" aria-label="Cari data analitik mobile" class="form-input-search" />
                </div>
            </div>
            <div class="table-toolbar-shell__right">
                <div class="relative">
                    <button @click="openCalendar($event, 'filter')" class="select-trigger-button-compact">
                        <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                        <template v-if="commonDateFilter.start">{{ formatShortDate(commonDateFilter.start) }}<span v-if="commonDateFilter.end"> - {{ formatShortDate(commonDateFilter.end) }}</span></template>
                        <template v-else>Semua Tanggal</template>
                        <i v-if="commonDateFilter.start" @click.stop="commonDateFilter = { start: '', end: '' }" class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-red-500"></i>
                    </button>
                </div>
                <div class="toolbar-actions">
                    <button @click="exportExcel" class="secondary-cta-button secondary-cta-success active:scale-95"><i class="fa-solid fa-file-excel"></i><span class="ml-1">Excel</span></button>
                    <button @click="exportPdf" class="secondary-cta-button secondary-cta-danger active:scale-95"><i class="fa-solid fa-file-pdf"></i><span class="ml-1">PDF</span></button>
                </div>
            </div>
        </div>
        <div v-for="(row, idx) in pagedAnalyticsData" :key="row.ID"
                class="stat-card mobile-record-card mobile-data-card motion-stagger-item"
                :style="getStaggerStyle(idx)">
                <div class="mobile-data-card__header">
                    <div class="flex-1">
                        <div class="mobile-data-card__title mb-1">{{ row.Judul }}</div>
                        <div class="mobile-data-card__meta">
                            <i
                                :class="getPlatformIcon(row.Platform) + ' text-body text-slate-400'"></i>
                            <span
                                class="text-body-sm font-bold text-slate-500 uppercase tracking-wider">{{ row.Platform }}</span>
                        </div>
                    </div>
                </div>
                <div class="mobile-data-card__summary">
                    <div>
                        <div class="type-body-sm text-slate-400 uppercase font-bold">Views</div>
                        <div class="type-body font-bold text-slate-700">{{ formatNumber(row.Views) }}</div>
                    </div>
                    <div>
                        <div class="type-body-sm text-slate-400 uppercase font-bold">Score</div>
                        <div class="type-body font-bold text-ppp-accent">{{ calculateScore(row) }}%
                        </div>
                    </div>
                </div>
                <div class="mobile-data-card__actions">
                    <div
                        :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-overline font-bold text-white uppercase tracking-wider', getVelocity(row).class]">
                        <i :class="getVelocity(row).icon"></i>
                        {{ getVelocity(row).label }}
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="openAnalyticsModal(row)"
                            class="table-action-button table-action-compact" title="Edit"
                            aria-label="Edit"><i class="fa-solid fa-pen text-body-sm"></i></button>
                        <button @click="deleteAnalytics(row.ID)"
                            class="table-action-button table-action-compact table-action-danger"
                            title="Hapus" aria-label="Hapus"><i
                                class="fa-solid fa-trash text-body-sm"></i></button>
                    </div>
                </div>
            </div>
            <div v-if="filteredAnalyticsData.length === 0"
                class="table-empty-state text-slate-400 text-body uppercase tracking-widest">
                Belum
                ada data analitik</div>
        </div>

    <div class="hidden md:block section-card section-card-shell">
            <div class="table-toolbar-shell">
                <div class="table-toolbar-shell__left">
                    <div class="relative">
                        <i
                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                        <input id="analytics-search-desktop" name="analytics_search_desktop" v-model="contentTableSearch" type="text"
                            autocomplete="off" aria-label="Cari data analitik desktop"
                            placeholder="Cari data analitik..." class="form-input-search" />
                    </div>
                </div>
                <div class="table-toolbar-shell__right">
                    <div class="relative">
                        <button @click="openCalendar($event, 'filter')" class="select-trigger-button-compact">
                            <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
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
            <div class="overflow-x-auto">
            <table class="w-full text-body-sm text-left border-collapse">
                <thead>
                    <tr>
                        <th
                            class="px-6 py-4 text-center text-body-sm font-bold uppercase tracking-widest text-slate-400 w-10">
                            #</th>
                        <th
                            class="px-6 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold">
                            Aksi</th>
                        <th
                            class="px-6 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold">
                            Judul</th>
                        <th
                            class="px-6 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold">
                            Platform</th>
                        <th
                            class="px-6 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold">
                            Tanggal Upload</th>
                        <th
                            class="px-6 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold text-center">
                            Views</th>
                        <th
                            class="px-6 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold text-center">
                            Like</th>
                        <th
                            class="px-6 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold text-center">
                            Komen</th>
                        <th
                            class="px-6 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold text-center">
                            Share</th>
                        <th
                            class="px-6 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold text-center">
                            Skor</th>
                        <th
                            class="px-6 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold text-center">
                            Velocity</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <tr v-for="(row, idx) in pagedAnalyticsData" :key="row.ID"
                        class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 text-center text-body-sm font-bold text-slate-400 tabular-nums">{{ idx + 1 }}</td>
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-2">
                                <button @click="openAnalyticsModal(row)"
                                    class="table-action-button table-action-compact" title="Edit"
                                    aria-label="Edit"><i
                                        class="fa-solid fa-pen text-body-sm"></i></button>
                                <button @click="deleteAnalytics(row.ID)"
                                    class="table-action-button table-action-compact table-action-danger"
                                    title="Hapus" aria-label="Hapus"><i
                                        class="fa-solid fa-trash text-body-sm"></i></button>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="text-body-sm font-bold text-slate-800">{{ row.Judul }}
                            </div>
                        </td>
                        <td class="px-6 py-5 text-left">
                            <div class="flex flex-col items-center gap-1">
                                <i
                                    :class="getPlatformIcon(row.Platform) + ' text-body text-slate-400'"></i>
                                <span
                                    class="text-overline font-bold text-slate-400 uppercase tracking-widest">{{ row.Platform }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="type-body text-slate-500">{{ formatShortDate(row.Tanggal || row.Tanggal_Publish) }}</div>
                        </td>
                        <td class="px-6 py-5 text-center font-bold text-slate-700 text-body">{{ formatNumber(row.Views) }}</td>
                        <td class="px-6 py-5 text-center text-slate-600 text-body">{{ formatNumber(row.Likes) }}</td>
                        <td class="px-6 py-5 text-center text-slate-600 text-body">{{ formatNumber(row.Comments) }}</td>
                        <td class="px-6 py-5 text-center text-slate-600 text-body">{{ formatNumber(row.Shares) }}</td>
                        <td class="px-6 py-5 text-center">
                            <div
                                class="inline-flex items-center justify-center w-8 h-8 rounded-full border border-slate-100 text-body-sm font-bold text-ppp-accent">
                                {{ calculateScore(row) }}%</div>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <div
                                :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-overline-xs font-bold text-white uppercase tracking-wider', getVelocity(row).class]">
                                <i :class="getVelocity(row).icon"></i>
                                {{ getVelocity(row).label }}
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredAnalyticsData.length === 0">
                        <td colspan="11"
                            class="px-6 py-20 text-center text-slate-400 text-body uppercase tracking-widest">
                            Belum ada data analitik</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div
            class="table-pager-bar">
            <div class="text-body-sm text-slate-400 font-medium">
                <template v-if="filteredAnalyticsData.length > 0">{{ (analyticsPage - 1) * 15 + 1 }}-{{ Math.min(analyticsPage * 15, filteredAnalyticsData.length) }} dari {{ filteredAnalyticsData.length }} data</template>
                <template v-else>0 data</template>
            </div>
            <div class="flex items-center gap-1">
                <button @click="analyticsPage--" :disabled="analyticsPage <= 1"
                    aria-label="Halaman sebelumnya"
                    class="icon-utility-button icon-utility-bordered"><i
                        class="fa-solid fa-chevron-left text-body-sm"></i></button>
                <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ analyticsPage }} / {{ analyticsTotalPages }}</span>
                <button @click="analyticsPage++" :disabled="analyticsPage >= analyticsTotalPages"
                    aria-label="Halaman berikutnya"
                    class="icon-utility-button icon-utility-bordered"><i
                        class="fa-solid fa-chevron-right text-body-sm"></i></button>
            </div>
        </div>
    </div>
</div>
@endverbatim
