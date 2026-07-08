@verbatim
<div v-if="activeTab === 'meta_feed'" class="space-y-6 animate-fadeIn pb-10">
                        <template v-if="metaFeedData.length">
                            <div class="dashboard-summary-grid-compact grid grid-cols-3 md:grid-cols-4 gap-3 md:gap-4">
                                <div v-for="c in metaFeedSummary.cards" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
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
                                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-violet-500 text-white flex items-center justify-center"><i class="fa-solid fa-photo-film text-[18px]"></i></div>
                                    <div>
                                        <h2 class="type-title font-bold text-slate-900">Feed Konten Analytics</h2>
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest mt-0.5">Traffic & engagement Feed (Reel/Image/Carousel)</p>
                                    </div>
                                </div>
                                <div class="mobile-toolbar-stack">
                                    <button @click="openCalendar($event, 'filter', '', 'metaFeed')" class="filter-trigger-button toolbar-trigger-field">
                                        <i class="fa-solid fa-calendar-days text-[10px] text-slate-400"></i>
                                        <template v-if="metaFeedDateFilter.start">{{ formatShortDate(metaFeedDateFilter.start) }}<span v-if="metaFeedDateFilter.end"> - {{ formatShortDate(metaFeedDateFilter.end) }}</span></template>
                                        <template v-else>Semua Tanggal</template>
                                        <i v-if="metaFeedDateFilter.start" @click.stop="metaFeedDateFilter = { start: '', end: '' }" class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-red-500"></i>
                                    </button>
                                    <div class="relative search-select-container sm:w-44">
                                        <button @click="toggleSearchSelect($event, 'meta_feed_account')"
                                            class="select-trigger-button toolbar-trigger-field">
                                            <i class="fa-solid fa-user-group text-[10px] text-slate-400"></i>
                                            <span class="truncate">{{ metaFeedAccount || 'Semua Akun' }}</span>
                                            <i v-if="metaFeedAccount" @click.stop="metaFeedAccount = ''"
                                                class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-red-500"></i>
                                            <i v-else class="fa-solid fa-chevron-down ml-auto text-[9px] text-slate-400"></i>
                                        </button>
                                        <transition name="fade">
                                            <div v-if="searchSelectOpen === 'meta_feed_account'" :style="popoverStyle"
                                                class="search-select-popover search-select-popover--compact max-h-72 overflow-y-auto">
                                                <div @click="metaFeedAccount = ''; searchSelectOpen = null"
                                                    :class="['popover-option', !metaFeedAccount ? 'popover-option-active' : '']">
                                                    Semua Akun</div>
                                                <div v-for="a in metaFeedAccounts" :key="a"
                                                    @click="metaFeedAccount = a; searchSelectOpen = null"
                                                    :class="['popover-option', metaFeedAccount === a ? 'popover-option-active' : '']">
                                                    {{ a }}</div>
                                            </div>
                                        </transition>
                                    </div>
                                    <div class="relative flex-1 sm:w-44">
                                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                                        <input v-model="metaFeedSearch" type="text" placeholder="Cari konten..." class="form-input-search" />
                                    </div>
                                    <label class="primary-cta-button primary-cta-button--accent active:scale-95 cursor-pointer">
                                        <i :class="metaUploading ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-upload'"></i> Upload CSV
                                        <input type="file" accept=".csv" class="hidden" @change="handleMetaFileInput($event, 'feed')" />
                                    </label>
                                    <button @click="importMetaFolder('feed')" class="secondary-cta-button secondary-cta-neutral active:scale-95" :disabled="metaUploading">
                                        <i :class="metaUploading ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-folder-open'"></i> Import Folder
                                    </button>
                                </div>
                            </div>
                            <div v-if="metaFeedSummary.chips.length" class="chips-grid mt-4 pt-4 border-t border-slate-100">
                                <span v-for="ch in metaFeedSummary.chips" :key="ch.label" :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold', ch.cls]">{{ ch.label }} <span class="opacity-50">&middot;</span> {{ ch.n }}</span>
                            </div>
                        </section>

                        <div v-if="metaFeedLoaded && metaFeedData.length === 0" class="bg-white radius-panel border border-dashed border-slate-200 p-16 flex flex-col items-center justify-center text-slate-400">
                            <i class="fa-solid fa-photo-film text-4xl mb-4 opacity-20"></i>
                            <p class="text-[11px] font-bold uppercase tracking-widest">Belum ada data Feed</p>
                            <p class="text-[10px] mt-1">Upload file CSV export Feed dari Meta (tombol Upload CSV di atas).</p>
                        </div>

                        <template v-if="metaFeedData.length">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <section class="bg-white radius-panel border border-slate-100 p-5"><h3 class="text-[11px] font-bold text-slate-700 uppercase tracking-widest mb-3">Tren Views & Reach</h3><div id="meta-feed-trend" class="min-h-[260px]"></div></section>
                                <section class="bg-white radius-panel border border-slate-100 p-5"><h3 class="text-[11px] font-bold text-slate-700 uppercase tracking-widest mb-3">Distribusi Tipe Konten</h3><div id="meta-feed-type" class="min-h-[260px]"></div></section>
                            </div>
                            <section class="bg-white radius-panel border border-slate-100 p-5">
                                <h3 class="text-[11px] font-bold text-slate-700 uppercase tracking-widest mb-3">Insight Feed yang Bisa Dipakai</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                                    <div v-for="insight in metaFeedInsights" :key="insight.label" class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                        <p class="text-[9px] font-bold uppercase tracking-[0.24em] text-slate-400">{{ insight.label }}</p>
                                        <p class="mt-2 text-[18px] font-bold text-slate-900">{{ insight.value }}</p>
                                        <p class="mt-1 text-[10px] text-slate-500">{{ insight.detail }}</p>
                                    </div>
                                </div>
                            </section>
                            <section v-if="metaFeedMonthlySummary.length" class="section-card section-card-shell">
                                <div class="px-5 pt-5"><h3 class="text-[11px] font-bold text-slate-700 uppercase tracking-widest mb-3">Ringkasan Bulanan per Akun</h3></div>
                                <div class="overflow-x-auto"><table class="w-full text-[10.5px] text-left border-collapse min-w-[860px]">
                                    <thead class="bg-slate-50"><tr class="text-[9px] uppercase tracking-widest text-slate-400">
                                        <th class="px-4 py-3">Bulan</th><th class="px-4 py-3">Akun</th><th class="px-4 py-3 text-right">Post</th><th class="px-4 py-3 text-right">Views</th><th class="px-4 py-3 text-right">Reach</th><th class="px-4 py-3 text-right">Likes</th><th class="px-4 py-3 text-right">Comments</th><th class="px-4 py-3 text-right">Shares</th><th class="px-4 py-3 text-right">Saves</th>
                                    </tr></thead>
                                    <tbody>
                                        <tr v-for="row in metaFeedMonthlySummary" :key="`${row.month}-${row.account}`" class="border-t border-slate-50 hover:bg-slate-50/50">
                                            <td class="px-4 py-2.5 whitespace-nowrap text-slate-500">{{ row.month }}</td><td class="px-4 py-2.5 text-slate-700">{{ row.account }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.posts) }}</td><td class="px-4 py-2.5 text-right font-bold text-slate-800">{{ formatNumber(row.views) }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.reach) }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.likes) }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.comments) }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.shares) }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.saves) }}</td>
                                        </tr>
                                    </tbody>
                                </table></div>
                            </section>
                            <section v-if="metaFeedAccountLeaderboard.length" class="bg-white radius-panel border border-slate-100 p-5">
                                <h3 class="text-[11px] font-bold text-slate-700 uppercase tracking-widest mb-3">Peringkat Akun Feed</h3>
                                <div class="space-y-2">
                                    <div v-for="(row, idx) in metaFeedAccountLeaderboard" :key="row.account" class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-100">
                                        <div class="w-7 h-7 rounded-lg bg-slate-50 flex items-center justify-center text-[11px] font-bold text-slate-500 shrink-0">{{ idx + 1 }}</div>
                                        <div class="flex-1 min-w-0"><p class="text-[11px] font-bold text-slate-800 truncate">{{ row.account }}</p><p class="text-[9px] text-slate-400">{{ formatNumber(row.posts) }} post | {{ formatNumber(row.reach) }} reach</p></div>
                                        <div class="text-right shrink-0"><p class="text-[12px] font-bold text-blue-600">{{ formatNumber(row.views) }}</p><p class="text-[8px] text-slate-400 uppercase">views</p></div>
                                    </div>
                                </div>
                            </section>
                            <section v-if="metaFeedTop.length" class="bg-white radius-panel border border-slate-100 p-5">
                                <h3 class="text-[11px] font-bold text-slate-700 uppercase tracking-widest mb-3"><i class="fa-solid fa-trophy text-amber-400 mr-1"></i> Top Konten (Views)</h3>
                                <div class="space-y-2">
                                    <div v-for="(r, idx) in metaFeedTop" :key="'fts'+idx" class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-100">
                                        <div class="w-7 h-7 rounded-lg bg-slate-50 flex items-center justify-center text-[11px] font-bold text-slate-500 shrink-0">{{ idx + 1 }}</div>
                                        <div class="flex-1 min-w-0"><p class="text-[11px] font-bold text-slate-800 truncate">{{ metaShortDesc(r) }}</p><p class="text-[9px] text-slate-400">{{ r.post_type }} | {{ r.account }} | {{ r.publish_time ? formatShortDate(r.publish_time) : '-' }}</p></div>
                                        <div class="text-right shrink-0"><p class="text-[12px] font-bold text-blue-600">{{ formatNumber(r.views) }}</p><p class="text-[8px] text-slate-400 uppercase">views</p></div>
                                    </div>
                                </div>
                            </section>
                            <section class="section-card section-card-shell">
                                <div class="overflow-x-auto"><table class="w-full text-[10.5px] text-left border-collapse min-w-[840px]">
                                    <thead class="bg-slate-50"><tr class="text-[9px] uppercase tracking-widest text-slate-400">
                                        <th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Tipe</th><th class="px-4 py-3">Akun</th><th class="px-4 py-3">Konten</th>
                                        <th class="px-4 py-3 text-right">Views</th><th class="px-4 py-3 text-right">Reach</th><th class="px-4 py-3 text-right">Likes</th>
                                        <th class="px-4 py-3 text-right">Komen</th><th class="px-4 py-3 text-right">Share</th><th class="px-4 py-3 text-right">Save</th>
                                    </tr></thead>
                                    <tbody>
                                        <tr v-for="(r, idx) in filteredMetaFeed" :key="'ftr'+idx" class="border-t border-slate-50 hover:bg-slate-50/50">
                                            <td class="px-4 py-2.5 whitespace-nowrap text-slate-500">{{ r.publish_time ? formatShortDate(r.publish_time) : '-' }}</td>
                                            <td class="px-4 py-2.5"><span class="px-2 py-0.5 rounded bg-blue-50 text-blue-600 text-[9px] font-bold uppercase">{{ r.post_type || '-' }}</span></td>
                                            <td class="px-4 py-2.5 whitespace-nowrap text-slate-500">{{ r.account || '-' }}</td>
                                            <td class="px-4 py-2.5 max-w-[260px]"><p class="truncate font-medium text-slate-700">{{ metaShortDesc(r) }}</p></td>
                                            <td class="px-4 py-2.5 text-right font-bold text-slate-800">{{ formatNumber(r.views) }}</td>
                                            <td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(r.reach) }}</td>
                                            <td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(r.likes) }}</td>
                                            <td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(r.comments) }}</td>
                                            <td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(r.shares) }}</td>
                                            <td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(r.saves) }}</td>
                                        </tr>
                                    </tbody>
                                </table></div>
                            </section>
                        </template>
                    </div>
@endverbatim
