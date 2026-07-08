@verbatim
<div v-if="activeTab === 'meta_story'" class="space-y-6 animate-fadeIn pb-10">
                        <template v-if="metaStoryData.length">
                            <div class="dashboard-summary-grid-compact grid grid-cols-3 md:grid-cols-4 gap-3 md:gap-4">
                                <div v-for="c in metaStorySummary.cards" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                                    <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i :class="['fa-solid', c.icon, 'text-[120px]']"></i></div>
                                    <p :class="['text-[9px] font-bold uppercase tracking-widest mb-3', c.color]">{{ c.label }}</p>
                                    <div class="flex items-baseline gap-2"><span class="dashboard-summary-value">{{ c.value }}</span><span v-if="c.unit" :class="['dashboard-summary-unit', c.unitColor]">{{ c.unit }}</span></div>
                                    <p :class="['text-[10px] font-bold mt-3', c.subColor]">{{ c.sub }}</p>
                                </div>
                            </div>
                        </template>
                        <section class="section-card section-card-body">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-pink-500 to-rose-500 text-white flex items-center justify-center"><i class="fa-brands fa-instagram text-[18px]"></i></div>
                                    <div>
                                        <h2 class="type-title font-bold text-slate-900">Story IG Analytics</h2>
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest mt-0.5">Traffic & engagement Instagram Story</p>
                                    </div>
                                </div>
                                <div class="mobile-toolbar-stack">
                                    <button @click="openCalendar($event, 'filter', '', 'metaStory')" class="filter-trigger-button toolbar-trigger-field">
                                        <i class="fa-solid fa-calendar-days text-[10px] text-slate-400"></i>
                                        <template v-if="metaStoryDateFilter.start">{{ formatShortDate(metaStoryDateFilter.start) }}<span v-if="metaStoryDateFilter.end"> - {{ formatShortDate(metaStoryDateFilter.end) }}</span></template>
                                        <template v-else>Semua Tanggal</template>
                                        <i v-if="metaStoryDateFilter.start" @click.stop="metaStoryDateFilter = { start: '', end: '' }" class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-red-500"></i>
                                    </button>
                                    <div class="relative flex-1 sm:w-48">
                                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                                        <input v-model="metaStorySearch" type="text" placeholder="Cari konten / Post ID..." class="form-input-search" />
                                    </div>
                                    <label class="primary-cta-button primary-cta-button--accent active:scale-95 cursor-pointer">
                                        <i :class="metaUploading ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-upload'"></i> Upload CSV
                                        <input type="file" accept=".csv" class="hidden" @change="handleMetaFileInput($event, 'story')" />
                                    </label>
                                    <button @click="importMetaFolder('story')" class="secondary-cta-button secondary-cta-neutral active:scale-95" :disabled="metaUploading">
                                        <i :class="metaUploading ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-folder-open'"></i> Import Folder
                                    </button>
                                </div>
                            </div>
                            <div v-if="metaStorySummary.chips.length" class="chips-grid mt-4 pt-4 border-t border-slate-100">
                                <span v-for="ch in metaStorySummary.chips" :key="ch.label" :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold', ch.cls]">{{ ch.label }} <span class="opacity-50">&middot;</span> {{ ch.n }}</span>
                            </div>
                        </section>

                        <div v-if="metaStoryLoaded && metaStoryData.length === 0" class="bg-white radius-panel border border-dashed border-slate-200 p-16 flex flex-col items-center justify-center text-slate-400">
                            <i class="fa-brands fa-instagram text-4xl mb-4 opacity-20"></i>
                            <p class="text-[11px] font-bold uppercase tracking-widest">Belum ada data Story</p>
                            <p class="text-[10px] mt-1">Upload file CSV export Story dari Meta (tombol Upload CSV di atas).</p>
                        </div>

                        <template v-if="metaStoryData.length">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <section class="bg-white radius-panel border border-slate-100 p-5"><h3 class="text-[11px] font-bold text-slate-700 uppercase tracking-widest mb-3">Average Views & Reach per Konten</h3><div id="meta-story-trend" class="min-h-[260px]"></div></section>
                                <section class="bg-white radius-panel border border-slate-100 p-5"><h3 class="text-[11px] font-bold text-slate-700 uppercase tracking-widest mb-3">Komposisi Story Actions</h3><div id="meta-story-actions" class="min-h-[260px]"></div></section>
                            </div>
                            <section class="bg-white radius-panel border border-slate-100 p-5">
                                <h3 class="text-[11px] font-bold text-slate-700 uppercase tracking-widest mb-3">Insight Story yang Bisa Dipakai</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                                    <div v-for="insight in metaStoryInsights" :key="insight.label" class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                        <p class="text-[9px] font-bold uppercase tracking-[0.24em] text-slate-400">{{ insight.label }}</p>
                                        <p class="mt-2 text-[18px] font-bold text-slate-900">{{ insight.value }}</p>
                                        <p class="mt-1 text-[10px] text-slate-500">{{ insight.detail }}</p>
                                    </div>
                                </div>
                            </section>
                            <section v-if="metaStoryMonthlySummary.length" class="section-card section-card-shell">
                                <div class="px-5 pt-5"><h3 class="text-[11px] font-bold text-slate-700 uppercase tracking-widest mb-3">Ringkasan Bulanan per Akun</h3></div>
                                <div class="overflow-x-auto"><table class="w-full text-[10.5px] text-left border-collapse min-w-[760px]">
                                    <thead class="bg-slate-50"><tr class="text-[9px] uppercase tracking-widest text-slate-400">
                                        <th class="px-4 py-3">Bulan</th><th class="px-4 py-3">Akun</th><th class="px-4 py-3 text-right">Story</th><th class="px-4 py-3 text-right">Views</th><th class="px-4 py-3 text-right">Reach</th><th class="px-4 py-3 text-right">Link</th><th class="px-4 py-3 text-right">Follow</th><th class="px-4 py-3 text-right">Navigation</th>
                                    </tr></thead>
                                    <tbody>
                                        <tr v-for="row in metaStoryMonthlySummary" :key="`${row.month}-${row.account}`" class="border-t border-slate-50 hover:bg-slate-50/50">
                                            <td class="px-4 py-2.5 whitespace-nowrap text-slate-500">{{ row.month }}</td><td class="px-4 py-2.5 text-slate-700">{{ row.account }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.posts) }}</td><td class="px-4 py-2.5 text-right font-bold text-slate-800">{{ formatNumber(row.views) }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.reach) }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.link_clicks) }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.follows) }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.navigation) }}</td>
                                        </tr>
                                    </tbody>
                                </table></div>
                            </section>
                            <section v-if="metaStoryTop.length" class="bg-white radius-panel border border-slate-100 p-5">
                                <h3 class="text-[11px] font-bold text-slate-700 uppercase tracking-widest mb-3"><i class="fa-solid fa-trophy text-amber-400 mr-1"></i> Top Story (Views)</h3>
                                <div class="space-y-2">
                                    <div v-for="(r, idx) in metaStoryTop" :key="'sts'+idx" class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-100">
                                        <div class="w-7 h-7 rounded-lg bg-slate-50 flex items-center justify-center text-[11px] font-bold text-slate-500 shrink-0">{{ idx + 1 }}</div>
                                        <div class="flex-1 min-w-0"><p class="text-[11px] font-bold text-slate-800 truncate">{{ metaShortDesc(r) }}</p><p class="text-[9px] text-slate-400">{{ r.publish_time ? formatShortDate(r.publish_time) : '-' }}</p></div>
                                        <div class="text-right shrink-0"><p class="text-[12px] font-bold text-blue-600">{{ formatNumber(r.views) }}</p><p class="text-[8px] text-slate-400 uppercase">views</p></div>
                                    </div>
                                </div>
                            </section>
                            <section class="section-card section-card-shell">
                                <div class="overflow-x-auto"><table class="w-full text-[10.5px] text-left border-collapse min-w-[760px]">
                                    <thead class="bg-slate-50"><tr class="text-[9px] uppercase tracking-widest text-slate-400">
                                        <th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Tipe</th><th class="px-4 py-3">Konten</th>
                                        <th class="px-4 py-3 text-right">Views</th><th class="px-4 py-3 text-right">Reach</th><th class="px-4 py-3 text-right">Likes</th>
                                        <th class="px-4 py-3 text-right">Navigasi</th><th class="px-4 py-3 text-right">Link</th><th class="px-4 py-3 text-right">Follows</th>
                                    </tr></thead>
                                    <tbody>
                                        <tr v-for="(r, idx) in pagedMetaStory" :key="'str'+idx" class="border-t border-slate-50 hover:bg-slate-50/50">
                                            <td class="px-4 py-2.5 whitespace-nowrap text-slate-500">{{ r.publish_time ? formatShortDate(r.publish_time) : '-' }}</td>
                                            <td class="px-4 py-2.5"><span class="px-2 py-0.5 rounded bg-rose-50 text-rose-600 text-[9px] font-bold uppercase">{{ r.post_type || '-' }}</span></td>
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
                                <div class="px-4 py-3 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
                                    <div class="text-[10px] text-slate-400 font-medium">
                                        <template v-if="filteredMetaStory.length > 0">{{ (metaStoryPage - 1) * 15 + 1 }}-{{ Math.min(metaStoryPage * 15, filteredMetaStory.length) }} dari {{ filteredMetaStory.length }} data</template>
                                        <template v-else>0 data</template>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button @click="metaStoryPage--" :disabled="metaStoryPage <= 1" aria-label="Halaman sebelumnya"
                                            class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-left text-[10px]"></i></button>
                                        <span class="px-3 text-[10px] font-bold text-ppp-accent">{{ metaStoryPage }} / {{ metaStoryTotalPages }}</span>
                                        <button @click="metaStoryPage++" :disabled="metaStoryPage >= metaStoryTotalPages" aria-label="Halaman berikutnya"
                                            class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-right text-[10px]"></i></button>
                                    </div>
                                </div>
                            </section>
                        </template>
                    </div>

                    <!-- Meta: Feed Konten -->
@endverbatim
