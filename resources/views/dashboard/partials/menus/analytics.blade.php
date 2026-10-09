@verbatim
<div v-if="activeTab === 'analytics'" class="space-y-4 animate-fadeIn">
    <!-- Summary cards -->
    <div class="space-y-3">
        <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
            <div v-for="c in analyticsSummary.cards.slice(0, 5)" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
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
                        <i v-if="commonDateFilter.start" @click.stop="commonDateFilter = { start: '', end: '' }" class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-danger"></i>
                    </button>
                </div>
                <div class="toolbar-actions">
                    <button @click="exportExcel" class="primary-cta-button primary-cta-button--success primary-cta-button--icon-only active:scale-95" aria-label="Export Excel"><i class="fa-solid fa-file-excel"></i></button>
                    <button @click="exportPdf" class="primary-cta-button primary-cta-button--danger primary-cta-button--icon-only active:scale-95" aria-label="Export PDF"><i class="fa-solid fa-file-pdf"></i></button>
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
                                class="text-body font-bold text-slate-700">{{ platformDisplayName(row.Platform) }}</span>
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
                        :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-overline font-bold text-white uppercase', getVelocity(row).class]">
                        <i :class="getVelocity(row).icon"></i>
                        {{ getVelocity(row).label }}
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="openAnalyticsModal(row)"
                            class="table-action-button table-action-compact" title="Edit"
                            aria-label="Edit"><i class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                        <button @click="deleteAnalytics(row.ID)"
                            class="table-action-button table-action-compact table-action-danger"
                            title="Hapus" aria-label="Hapus"><i
                                class="fa-solid fa-trash-can text-body-sm"></i></button>
                    </div>
                </div>
            </div>
            <div v-if="filteredAnalyticsData.length === 0"
                class="table-empty-state text-slate-400 text-body uppercase">
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
                                class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-danger"></i>
                        </button>
                    </div>
                    <div class="toolbar-actions toolbar-actions--desktop-icon-only">
                        <button @click="exportExcel"
                            class="primary-cta-button primary-cta-button--success primary-cta-button--icon-only active:scale-95" aria-label="Export Excel"><i
                                class="fa-solid fa-file-excel"></i></button>
                        <button @click="exportPdf"
                            class="primary-cta-button primary-cta-button--danger primary-cta-button--icon-only active:scale-95" aria-label="Export PDF"><i
                                class="fa-solid fa-file-pdf"></i></button>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
            <table class="w-full min-w-[1180px] table-fixed text-body-sm text-left border-collapse">
                <thead>
                    <tr class="table-header-row">
                        <th class="table-header-cell table-header-index table-freeze-index">
                            #</th>
                        <th class="table-header-cell table-header-action table-freeze-action">
                            Aksi</th>
                        <th class="table-header-cell">
                            Judul</th>
                        <th class="table-header-cell">
                            Platform</th>
                        <th class="table-header-cell">
                            ID Post</th>
                        <th class="table-header-cell">
                            Tanggal Upload</th>
                        <th class="table-header-cell text-center">
                            Views</th>
                        <th class="table-header-cell text-center">
                            Like</th>
                        <th class="table-header-cell text-center">
                            Komen</th>
                        <th class="table-header-cell text-center">
                            Share</th>
                        <th class="table-header-cell text-center">
                            Skor</th>
                        <th class="table-header-cell text-center">
                            Velocity</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <tr v-for="(row, idx) in pagedAnalyticsData" :key="row.ID"
                        class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 text-center text-body-sm font-bold text-slate-400 tabular-nums table-freeze-index">{{ idx + 1 }}</td>
                        <td class="px-6 py-5 table-freeze-action">
                            <div class="flex items-center gap-2">
                                <button @click="openAnalyticsModal(row)"
                                    class="table-action-button table-action-compact" title="Edit"
                                    aria-label="Edit"><i
                                        class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                                <button @click="deleteAnalytics(row.ID)"
                                    class="table-action-button table-action-compact table-action-danger"
                                    title="Hapus" aria-label="Hapus"><i
                                        class="fa-solid fa-trash-can text-body-sm"></i></button>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="text-body-sm font-bold text-slate-800">{{ row.Judul }}
                            </div>
                        </td>
                        <td class="px-6 py-5 text-left">
                            <div class="flex items-center gap-2">
                                <i
                                    :class="getPlatformIcon(row.Platform) + ' text-body text-slate-400'"></i>
                                <span
                                    class="text-body font-bold text-slate-700">{{ platformDisplayName(row.Platform) }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="type-body text-slate-500">{{ row.ID_Post || '-' }}</div>
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
                                :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-overline-xs font-bold text-white uppercase', getVelocity(row).class]">
                                <i :class="getVelocity(row).icon"></i>
                                {{ getVelocity(row).label }}
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredAnalyticsData.length === 0">
                        <td colspan="12"
                            class="px-6 py-20 text-center text-slate-400 text-body uppercase">
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

    <!-- Analytics Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="analyticsModalOpen"
                class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                <div @click="analyticsModalOpen = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop">
                </div>
                <div
                    class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface z-[2001]">
                    <div
                        class="modal-header-bar modal-header-bar-sticky radius-sheet-top z-[2010]">
                        <div class="modal-header-copy">
                            <div class="modal-header-icon text-white bg-ppp-accent">
                                <i class="fa-solid fa-chart-simple text-body"></i>
                            </div>
                            <div>
                                <div class="type-heading-sm text-slate-900">{{ analyticsForm.ID ? 'Edit Analitik' : 'Tambah Analitik' }}</div>
                                <div class="type-body-sm text-slate-400 uppercase mt-0.5">Konten Module
                                </div>
                            </div>
                        </div>
                        <button @click="analyticsModalOpen = false" aria-label="Tutup modal"
                            class="icon-utility-button icon-utility-round"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="md:col-span-2">
                                <label for="analytics-judul" class="type-body-sm font-bold text-slate-400 uppercase mb-2">Judul
                                    <span class="text-danger">*</span></label>
                                <input id="analytics-judul" name="analytics_judul" v-model="analyticsForm.Judul" type="text" placeholder="Judul konten"
                                    class="form-input" />
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Platform</label>
                                <div @click="toggleSearchSelect($event, 'analyticsPlatform')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="analyticsForm.Platform ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ analyticsForm.Platform || 'Pilih Platform' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </div>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'analyticsPlatform'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in (platformOptions)" :key="opt"
                                                @click="analyticsForm.Platform = opt; searchSelectOpen = null"
                                                :class="['popover-option', analyticsForm.Platform === opt ? 'popover-option-active' : '']">
                                                {{ opt }}</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div>
                                <label for="analytics-id-post"
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">ID Post</label>
                                <input id="analytics-id-post" name="analytics_id_post" v-model="analyticsForm.ID_Post" @input="queueAnalyticsIdPostSync($event.target.value)" type="text" placeholder="Post ID (sinkron dari Feed Konten)" class="form-input" />
                            </div>
                            <div>
                                <label for="analytics-views"
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Views</label>
                                <input id="analytics-views" name="analytics_views" v-model.number="analyticsForm.Views" type="number" min="0" class="form-input" />
                            </div>
                            <div>
                                <label for="analytics-likes"
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Likes</label>
                                <input id="analytics-likes" name="analytics_likes" v-model.number="analyticsForm.Likes" type="number" min="0" class="form-input" />
                            </div>
                            <div>
                                <label for="analytics-comments"
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Comments</label>
                                <input id="analytics-comments" name="analytics_comments" v-model.number="analyticsForm.Comments" type="number" min="0"
                                    class="form-input" />
                            </div>
                            <div>
                                <label for="analytics-shares"
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Shares</label>
                                <input id="analytics-shares" name="analytics_shares" v-model.number="analyticsForm.Shares" type="number" min="0" class="form-input" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer-bar modal-footer-actions">
                        <button @click="analyticsModalOpen = false" class="primary-cta-button primary-cta-button--neutral">Batal</button>
                        <button @click="saveAnalytics" :disabled="submitting" class="primary-cta-button">{{ submitting ? 'Menyimpan...' : 'Simpan' }}</button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
@endverbatim
