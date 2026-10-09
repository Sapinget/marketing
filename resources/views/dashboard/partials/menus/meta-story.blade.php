@verbatim
<div v-if="activeTab === 'meta_story' && !metaStoryLoaded" class="meta-analytics-page space-y-4 animate-fadeIn">
                        <section class="section-card section-card-shell px-6 py-16 text-center text-slate-300">
                            <i class="fa-solid fa-spinner fa-spin text-3xl mb-3 opacity-40 block"></i>
                            <p class="text-body-sm font-bold uppercase">Memuat data Story</p>
                            <p class="text-overline-xs mt-1">Dashboard akan tampil setelah data selesai dibaca</p>
                        </section>
                    </div>
<div v-if="activeTab === 'meta_story' && metaStoryLoaded" class="meta-analytics-page space-y-4 animate-fadeIn">
                        <section class="section-card meta-toolbar-card">
                            <div class="table-toolbar-shell">
                                <div class="table-toolbar-shell__left">
                                    <div class="relative flex-1">
                                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                                        <input id="meta-story-search" name="meta_story_search" v-model="metaStorySearch" type="text" placeholder="Cari konten / Post ID..." autocomplete="off" aria-label="Cari konten atau Post ID meta story" class="form-input-search" />
                                    </div>
                                </div>
                                <div class="table-toolbar-shell__right">
                                    <button @click="openCalendar($event, 'filter', '', 'metaStory')" class="select-trigger-button-compact">
                                        <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                        <template v-if="metaStoryDateFilter.start">{{ formatShortDate(metaStoryDateFilter.start) }}<span v-if="metaStoryDateFilter.end"> - {{ formatShortDate(metaStoryDateFilter.end) }}</span></template>
                                        <template v-else>Semua Tanggal</template>
                                        <i v-if="metaStoryDateFilter.start" @click.stop="metaStoryDateFilter = { start: '', end: '' }" class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-danger"></i>
                                    </button>
                                    <button type="button" @click="$refs.metaStoryUpload?.click()" class="primary-cta-button primary-cta-button--accent primary-cta-button--icon-only active:scale-95" :disabled="metaUploading" aria-label="Upload CSV Story IG">
                                        <i :class="metaUploading ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-upload'"></i>
                                    </button>
                                    <input ref="metaStoryUpload" id="meta-story-upload" name="meta_story_upload" type="file" accept=".csv" class="hidden" aria-label="Upload CSV meta story" @change="handleMetaFileInput($event, 'story')" />
                                    <button @click="importMetaFolder('story')" class="primary-cta-button primary-cta-button--neutral primary-cta-button--icon-only active:scale-95" :disabled="metaUploading" aria-label="Import Folder Story IG">
                                        <i :class="metaUploading ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-folder-open'"></i>
                                    </button>
                                </div>
                            </div>
                        </section>
                        <template v-if="metaStoryData.length">
                            <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
                                <div v-for="c in metaStorySummary.cards.slice(0, 5)" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                                    <div class="absolute -right-4 -bottom-4 opacity-5"><i :class="['fa-solid', c.icon, 'text-[120px]']"></i></div>
                                    <p class="dashboard-summary-title">{{ c.label }}</p>
                                    <div class="flex items-baseline gap-2"><span class="dashboard-summary-value">{{ c.value }}</span><span v-if="c.unit" class="dashboard-summary-unit">{{ c.unit }}</span></div>
                                    <p :class="['text-body-sm font-bold mt-3', c.subColor]">{{ c.sub }}</p>
                                </div>
                            </div>
                        </template>

                        <template v-if="metaStoryData.length">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <section class="bg-white radius-panel border border-slate-100 p-5"><h3 class="text-body font-bold text-slate-700 uppercase mb-3">Average Views & Reach per Konten</h3><div id="meta-story-trend" class="min-h-[260px]"></div></section>
                                <section class="bg-white radius-panel border border-slate-100 p-5"><h3 class="text-body font-bold text-slate-700 uppercase mb-3">Komposisi Story Actions</h3><div id="meta-story-actions" class="min-h-[260px]"></div></section>
                            </div>
                            <section class="bg-white radius-panel border border-slate-100 p-5">
                                <h3 class="text-body font-bold text-slate-700 uppercase mb-3">Insight Story yang Bisa Dipakai</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                                    <div v-for="insight in metaStoryInsights" :key="insight.label" class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                        <p class="text-overline font-bold uppercase text-slate-400">{{ insight.label }}</p>
                                        <p class="mt-2 text-display-sm font-bold text-slate-900">{{ insight.value }}</p>
                                        <p class="mt-1 text-body-sm text-slate-500">{{ insight.detail }}</p>
                                    </div>
                                </div>
                            </section>
                            </template>
                            <section class="section-card section-card-shell">
                                <div class="px-5 pt-5"><h3 class="text-body font-bold text-slate-700 uppercase mb-3">Data Story IG</h3></div>
                                <div class="overflow-x-auto"><table class="w-full text-body-sm text-left border-collapse min-w-[820px]">
                                    <thead><tr class="table-header-row">
                                        <th class="table-header-cell table-header-index table-freeze-index">#</th>
                                        <th class="table-header-cell">Tanggal</th><th class="table-header-cell">Tipe</th><th class="table-header-cell">Konten</th>
                                        <th class="table-header-cell text-right">Views</th><th class="table-header-cell text-right">Reach</th><th class="table-header-cell text-right">Likes</th>
                                        <th class="table-header-cell text-right">Navigasi</th><th class="table-header-cell text-right">Link</th><th class="table-header-cell text-right">Follows</th>
                                    </tr></thead>
                                    <tbody>
                                        <tr v-if="pagedMetaStory.length === 0">
                                            <td colspan="10" class="px-4 py-16 text-center text-slate-300">
                                                <i class="fa-brands fa-instagram text-4xl mb-3 opacity-20 block"></i>
                                                <p class="text-body-sm font-bold uppercase">Belum ada data Story</p>
                                                <p class="text-overline-xs mt-1">Upload CSV atau Import Folder untuk memulai</p>
                                            </td>
                                        </tr>
                                        <tr v-for="(r, idx) in pagedMetaStory" :key="'str'+idx" class="border-t border-slate-50 hover:bg-slate-50/50">
                                            <td class="px-4 py-2.5 text-center text-body-sm font-bold text-slate-400 tabular-nums table-freeze-index">{{ (metaStoryPage - 1) * 15 + idx + 1 }}</td>
                                            <td class="px-4 py-2.5 whitespace-nowrap text-slate-500">{{ r.publish_time ? formatShortDate(r.publish_time) : '-' }}</td>
                                            <td class="px-4 py-2.5"><span class="px-2 py-0.5 rounded bg-danger text-light text-overline font-bold uppercase">{{ r.post_type || '-' }}</span></td>
                                            <td class="px-4 py-2.5 max-w-[280px]"><p class="truncate font-medium text-slate-700">{{ metaShortDesc(r) }}</p></td>
                                            <td class="px-4 py-2.5 text-right font-bold text-slate-800">{{ formatNumber(r.views) }}</td>
                                            <td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(r.reach) }}</td>
                                            <td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(r.likes) }}</td>
                                            <td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(r.navigation) }}</td>
                                            <td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(r.link_clicks) }}</td>
                                            <td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(r.follows) }}</td>
                                        </tr>
                                    </tbody>
                                </table></div>
                                <div class="table-pager-bar">
                                    <div class="text-body-sm text-slate-400 font-medium">
                                        <template v-if="filteredMetaStory.length > 0">{{ (metaStoryPage - 1) * 15 + 1 }}-{{ Math.min(metaStoryPage * 15, filteredMetaStory.length) }} dari {{ filteredMetaStory.length }} data</template>
                                        <template v-else>0 data</template>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button @click="metaStoryPage--" :disabled="metaStoryPage <= 1" aria-label="Halaman sebelumnya"
                                            class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-left text-body-sm"></i></button>
                                        <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ metaStoryPage }} / {{ metaStoryTotalPages }}</span>
                                        <button @click="metaStoryPage++" :disabled="metaStoryPage >= metaStoryTotalPages" aria-label="Halaman berikutnya"
                                            class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-right text-body-sm"></i></button>
                                    </div>
                                </div>
                            </section>
                            <template v-if="metaStoryData.length">
                            <section v-if="metaStoryMonthlySummary.length" class="section-card section-card-shell">
                                <div class="px-5 pt-5"><h3 class="text-body font-bold text-slate-700 uppercase mb-3">Ringkasan Bulanan per Akun</h3></div>
                                <div class="overflow-x-auto"><table class="w-full text-body-sm text-left border-collapse min-w-[820px]">
                                    <thead><tr class="table-header-row">
                                        <th class="table-header-cell table-header-index table-freeze-index">#</th>
                                        <th class="table-header-cell">Bulan</th><th class="table-header-cell">Akun</th><th class="table-header-cell text-right">Story</th><th class="table-header-cell text-right">Views</th><th class="table-header-cell text-right">Reach</th><th class="table-header-cell text-right">Link</th><th class="table-header-cell text-right">Follow</th><th class="table-header-cell text-right">Navigation</th>
                                    </tr></thead>
                                    <tbody>
                                        <tr v-for="(row, idx) in metaStoryMonthlySummary" :key="`${row.month}-${row.account}`" class="border-t border-slate-50 hover:bg-slate-50/50">
                                            <td class="px-4 py-2.5 text-center text-body-sm font-bold text-slate-400 tabular-nums table-freeze-index">{{ idx + 1 }}</td>
                                            <td class="px-4 py-2.5 whitespace-nowrap text-slate-500">{{ row.month }}</td><td class="px-4 py-2.5 text-slate-700">{{ row.account }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.posts) }}</td><td class="px-4 py-2.5 text-right font-bold text-slate-800">{{ formatNumber(row.views) }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.reach) }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.link_clicks) }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.follows) }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.navigation) }}</td>
                                        </tr>
                                    </tbody>
                                </table></div>
                            </section>
                            <section v-if="metaStoryTop.length" class="bg-white radius-panel border border-slate-100 p-5">
                                <h3 class="text-body font-bold text-slate-700 uppercase mb-3"><i class="fa-solid fa-trophy text-amber mr-1"></i> Top Story (Views)</h3>
                                <div class="space-y-2">
                                    <div v-for="(r, idx) in metaStoryTop" :key="'sts'+idx" class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-100">
                                        <div class="w-7 h-7 rounded-lg bg-slate-50 flex items-center justify-center text-body font-bold text-slate-500 shrink-0">{{ idx + 1 }}</div>
                                        <div class="flex-1 min-w-0"><p class="text-body font-bold text-slate-800 truncate">{{ metaShortDesc(r) }}</p><p class="text-overline text-slate-400">{{ r.publish_time ? formatShortDate(r.publish_time) : '-' }}</p></div>
                                        <div class="text-right shrink-0"><p class="text-body font-bold text-amber">{{ formatNumber(r.views) }}</p><p class="text-overline-xs text-slate-400 uppercase">views</p></div>
                                    </div>
                                </div>
                            </section>
                            </template>
                    </div>

                    <!-- Meta: Feed Konten -->
@endverbatim
