@verbatim
<div v-if="activeTab === 'meta_feed'" class="space-y-6 animate-fadeIn pb-10">
                        <template v-if="metaFeedData.length">
                            <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
                                <div v-for="c in metaFeedSummary.cards.slice(0, 5)" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                                    <div class="absolute -right-4 -bottom-4 opacity-5"><i :class="['fa-solid', c.icon, 'text-[120px]']"></i></div>
                                    <p class="dashboard-summary-title">{{ c.label }}</p>
                                    <div class="flex items-baseline gap-2"><span class="dashboard-summary-value">{{ c.value }}</span><span v-if="c.unit" class="dashboard-summary-unit">{{ c.unit }}</span></div>
                                    <p :class="['text-body-sm font-bold mt-3', c.subColor]">{{ c.sub }}</p>
                                </div>
                            </div>
                        </template>


                        <div v-if="metaFeedLoaded && metaFeedData.length === 0" class="bg-white radius-panel border border-dashed border-slate-200 p-16 flex flex-col items-center justify-center text-slate-400">
                            <i class="fa-solid fa-photo-film text-4xl mb-4 opacity-20"></i>
                            <p class="text-body font-bold uppercase tracking-widest">Belum ada data Feed</p>
                            <p class="text-body-sm mt-1">Upload file CSV export Feed dari Meta (tombol Upload CSV di atas).</p>
                        </div>

                        <template v-if="metaFeedData.length">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <section class="bg-white radius-panel border border-slate-100 p-5"><h3 class="text-body font-bold text-slate-700 uppercase tracking-widest mb-3">Tren Views & Reach</h3><div id="meta-feed-trend" class="min-h-[260px]"></div></section>
                                <section class="bg-white radius-panel border border-slate-100 p-5"><h3 class="text-body font-bold text-slate-700 uppercase tracking-widest mb-3">Distribusi Tipe Konten</h3><div id="meta-feed-type" class="min-h-[260px]"></div></section>
                            </div>
                            <section class="bg-white radius-panel border border-slate-100 p-5">
                                <h3 class="text-body font-bold text-slate-700 uppercase tracking-widest mb-3">Insight Feed yang Bisa Dipakai</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                                    <div v-for="insight in metaFeedInsights" :key="insight.label" class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                        <p class="text-overline font-bold uppercase tracking-[0.24em] text-slate-400">{{ insight.label }}</p>
                                        <p class="mt-2 text-display-sm font-bold text-slate-900">{{ insight.value }}</p>
                                        <p class="mt-1 text-body-sm text-slate-500">{{ insight.detail }}</p>
                                    </div>
                                </div>
                            </section>
                            <section v-if="metaFeedMonthlySummary.length" class="section-card section-card-shell">
                                <div class="px-5 pt-5"><h3 class="text-body font-bold text-slate-700 uppercase tracking-widest mb-3">Ringkasan Bulanan per Akun</h3></div>
                                <div class="overflow-x-auto"><table class="w-full text-body-sm text-left border-collapse min-w-[860px]">
                                    <thead><tr class="table-header-row">
                                        <th class="table-header-cell">Bulan</th><th class="table-header-cell">Akun</th><th class="table-header-cell text-right">Post</th><th class="table-header-cell text-right">Views</th><th class="table-header-cell text-right">Reach</th><th class="table-header-cell text-right">Likes</th><th class="table-header-cell text-right">Comments</th><th class="table-header-cell text-right">Shares</th><th class="table-header-cell text-right">Saves</th>
                                    </tr></thead>
                                    <tbody>
                                        <tr v-for="row in metaFeedMonthlySummary" :key="`${row.month}-${row.account}`" class="border-t border-slate-50 hover:bg-slate-50/50">
                                            <td class="px-4 py-2.5 whitespace-nowrap text-slate-500">{{ row.month }}</td><td class="px-4 py-2.5 text-slate-700">{{ row.account }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.posts) }}</td><td class="px-4 py-2.5 text-right font-bold text-slate-800">{{ formatNumber(row.views) }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.reach) }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.likes) }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.comments) }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.shares) }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.saves) }}</td>
                                        </tr>
                                    </tbody>
                                </table></div>
                            </section>
                            <section v-if="metaFeedAccountLeaderboard.length" class="bg-white radius-panel border border-slate-100 p-5">
                                <h3 class="text-body font-bold text-slate-700 uppercase tracking-widest mb-3">Peringkat Akun Feed</h3>
                                <div class="space-y-2">
                                    <div v-for="(row, idx) in metaFeedAccountLeaderboard" :key="row.account" class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-100">
                                        <div class="w-7 h-7 rounded-lg bg-slate-50 flex items-center justify-center text-body font-bold text-slate-500 shrink-0">{{ idx + 1 }}</div>
                                        <div class="flex-1 min-w-0"><p class="text-body font-bold text-slate-800 truncate">{{ row.account }}</p><p class="text-overline text-slate-400">{{ formatNumber(row.posts) }} post | {{ formatNumber(row.reach) }} reach</p></div>
                                        <div class="text-right shrink-0"><p class="text-body font-bold text-amber">{{ formatNumber(row.views) }}</p><p class="text-overline-xs text-slate-400 uppercase">views</p></div>
                                    </div>
                                </div>
                            </section>
                            <section v-if="metaFeedTop.length" class="bg-white radius-panel border border-slate-100 p-5">
                                <h3 class="text-body font-bold text-slate-700 uppercase tracking-widest mb-3"><i class="fa-solid fa-trophy text-amber mr-1"></i> Top Konten (Views)</h3>
                                <div class="space-y-2">
                                    <div v-for="(r, idx) in metaFeedTop" :key="'fts'+idx" class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-100">
                                        <div class="w-7 h-7 rounded-lg bg-slate-50 flex items-center justify-center text-body font-bold text-slate-500 shrink-0">{{ idx + 1 }}</div>
                                        <div class="flex-1 min-w-0"><p class="text-body font-bold text-slate-800 truncate">{{ metaShortDesc(r) }}</p><p class="text-overline text-slate-400">{{ r.post_type }} | {{ r.account }} | {{ r.publish_time ? formatShortDate(r.publish_time) : '-' }}</p></div>
                                        <div class="text-right shrink-0"><p class="text-body font-bold text-amber">{{ formatNumber(r.views) }}</p><p class="text-overline-xs text-slate-400 uppercase">views</p></div>
                                    </div>
                                </div>
                            </section>
                            <section class="section-card section-card-shell">
                                <div class="table-toolbar-shell">
                                    <div class="table-toolbar-shell__left">
                                        <div class="relative search-select-container sm:w-44">
                                            <button @click="toggleSearchSelect($event, 'meta_feed_account')"
                                                class="select-trigger-button toolbar-trigger-field">
                                                <i class="fa-solid fa-user-group text-body-sm text-slate-400"></i>
                                                <span class="truncate">{{ metaFeedAccount || 'Semua Akun' }}</span>
                                                <i v-if="metaFeedAccount" @click.stop="metaFeedAccount = ''"
                                                    class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-danger"></i>
                                                <i v-else class="fa-solid fa-chevron-down ml-auto text-overline text-slate-400"></i>
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
                                        <div class="relative flex-1">
                                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                                            <input id="meta-feed-search" name="meta_feed_search" v-model="metaFeedSearch" type="text" placeholder="Cari konten..." autocomplete="off" aria-label="Cari konten meta feed" class="form-input-search" />
                                        </div>
                                    </div>
                                    <div class="table-toolbar-shell__right">
                                        <button @click="openCalendar($event, 'filter', '', 'metaFeed')" class="select-trigger-button-compact">
                                            <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                            <template v-if="metaFeedDateFilter.start">{{ formatShortDate(metaFeedDateFilter.start) }}<span v-if="metaFeedDateFilter.end"> - {{ formatShortDate(metaFeedDateFilter.end) }}</span></template>
                                            <template v-else>Semua Tanggal</template>
                                            <i v-if="metaFeedDateFilter.start" @click.stop="metaFeedDateFilter = { start: '', end: '' }" class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-danger"></i>
                                        </button>
                                        <label for="meta-feed-upload" class="primary-cta-button primary-cta-button--accent active:scale-95 cursor-pointer">
                                            <i :class="metaUploading ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-upload'"></i> Upload CSV
                                            <input id="meta-feed-upload" name="meta_feed_upload" type="file" accept=".csv" class="hidden" aria-label="Upload CSV meta feed" @change="handleMetaFileInput($event, 'feed')" />
                                        </label>
                                        <button @click="importMetaFolder('feed')" class="primary-cta-button primary-cta-button--neutral active:scale-95" :disabled="metaUploading">
                                            <i :class="metaUploading ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-folder-open'"></i> Import Folder
                                        </button>
                                    </div>
                                </div>
                                <div class="overflow-x-auto"><table class="w-full text-body-sm text-left border-collapse min-w-[840px]">
                                    <thead><tr class="table-header-row">
                                        <th class="table-header-cell">Tanggal</th><th class="table-header-cell">Tipe</th><th class="table-header-cell">Akun</th><th class="table-header-cell">Konten</th>
                                        <th class="table-header-cell text-right">Views</th><th class="table-header-cell text-right">Reach</th><th class="table-header-cell text-right">Likes</th>
                                        <th class="table-header-cell text-right">Komen</th><th class="table-header-cell text-right">Share</th><th class="table-header-cell text-right">Save</th>
                                    </tr></thead>
                                    <tbody>
                                        <tr v-for="(r, idx) in filteredMetaFeed" :key="'ftr'+idx" class="border-t border-slate-50 hover:bg-slate-50/50">
                                            <td class="px-4 py-2.5 whitespace-nowrap text-slate-500">{{ r.publish_time ? formatShortDate(r.publish_time) : '-' }}</td>
                                            <td class="px-4 py-2.5"><span class="px-2 py-0.5 rounded bg-amber text-light text-overline font-bold uppercase">{{ r.post_type || '-' }}</span></td>
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
