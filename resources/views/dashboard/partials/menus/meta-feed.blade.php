@verbatim
<div v-if="activeTab === 'meta_feed' && !metaFeedLoaded" class="meta-analytics-page space-y-6 animate-fadeIn pb-10">
                        <section class="section-card section-card-shell px-6 py-16 text-center text-slate-300">
                            <i class="fa-solid fa-spinner fa-spin text-3xl mb-3 opacity-40 block"></i>
                            <p class="text-body-sm font-bold uppercase">Memuat data Feed</p>
                            <p class="text-overline-xs mt-1">Dashboard akan tampil setelah data selesai dibaca</p>
                        </section>
                    </div>
<div v-if="activeTab === 'meta_feed' && metaFeedLoaded" class="meta-analytics-page space-y-6 animate-fadeIn pb-10">
                        <section class="section-card meta-toolbar-card">
                            <div class="table-toolbar-shell">
                                <div class="table-toolbar-shell__left">
                                    <div class="relative search-select-container sm:w-44">
                                        <button @click="toggleSearchSelect($event, 'meta_feed_account')"
                                            class="select-trigger-button toolbar-trigger-field">
                                            <i class="fa-solid fa-user-group text-body-sm text-slate-400"></i>
                                            <span class="truncate">{{ metaFeedAccount || 'Semua Akun' }}</span>
                                            <i v-if="metaFeedAccount" @click.stop="metaFeedAccount = ''; metaFeedAccountSearch = ''"
                                                class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-danger"></i>
                                            <i v-else class="fa-solid fa-chevron-down ml-auto text-overline text-slate-400"></i>
                                        </button>
                                        <transition name="fade">
                                            <div v-if="searchSelectOpen === 'meta_feed_account'" :style="popoverStyle"
                                                class="search-select-popover search-select-popover--compact overflow-hidden">
                                                <div class="p-2 border-b border-slate-100">
                                                    <div class="relative">
                                                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-body-xs"></i>
                                                        <input v-model="metaFeedAccountSearch" type="text" placeholder="Cari akun..." autocomplete="off" class="form-input-search form-input-search--compact text-body-sm pl-8" />
                                                    </div>
                                                </div>
                                                <div class="max-h-[240px] overflow-y-auto custom-scrollbar">
                                                    <div @click="metaFeedAccount = ''; metaFeedAccountSearch = ''; searchSelectOpen = null"
                                                        :class="['popover-option', !metaFeedAccount ? 'popover-option-active' : '']">
                                                        Semua Akun</div>
                                                    <div v-for="a in filteredMetaFeedAccounts" :key="a"
                                                        @click="metaFeedAccount = a; metaFeedAccountSearch = ''; searchSelectOpen = null"
                                                        :class="['popover-option', metaFeedAccount === a ? 'popover-option-active' : '']">
                                                        {{ a }}</div>
                                                    <div v-if="!filteredMetaFeedAccounts.length" class="px-3 py-4 text-center text-body-sm text-slate-400">Tidak ada akun</div>
                                                </div>
                                            </div>
                                        </transition>
                                    </div>

                                </div>
                                <div class="table-toolbar-shell__right">
                                    <button @click="openCalendar($event, 'filter', '', 'metaFeed')" class="select-trigger-button-compact">
                                        <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                        <template v-if="metaFeedDateFilter.start">{{ formatShortDate(metaFeedDateFilter.start) }}<span v-if="metaFeedDateFilter.end"> - {{ formatShortDate(metaFeedDateFilter.end) }}</span></template>
                                        <template v-else>Semua Tanggal</template>
                                        <i v-if="metaFeedDateFilter.start" @click.stop="metaFeedDateFilter = { start: '', end: '' }" class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-danger"></i>
                                    </button>
                                    <button type="button" @click="$refs.metaFeedUpload?.click()" class="primary-cta-button primary-cta-button--accent primary-cta-button--icon-only active:scale-95" :disabled="metaUploading" aria-label="Upload CSV Feed Konten" title="Upload CSV">
                                        <i :class="metaUploading ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-upload'"></i>
                                    </button>
                                    <input ref="metaFeedUpload" id="meta-feed-upload" name="meta_feed_upload" type="file" accept=".csv" class="hidden" aria-label="Upload CSV meta feed" @change="handleMetaFileInput($event, 'feed')" />
                                    <button @click="importMetaFolder('feed')" class="primary-cta-button primary-cta-button--neutral primary-cta-button--icon-only active:scale-95" :disabled="metaUploading" aria-label="Import Folder Feed Konten" title="Import Folder">
                                        <i :class="metaUploading ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-folder-open'"></i>
                                    </button>
                                </div>
                            </div>
                        </section>
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

                        <template v-if="metaFeedData.length">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <section class="bg-white radius-panel border border-slate-100 p-5"><h3 class="text-body font-bold text-slate-700 uppercase mb-3">Tren Views & Reach</h3><div id="meta-feed-trend" class="min-h-[260px]"></div></section>
                                <section class="bg-white radius-panel border border-slate-100 p-5"><h3 class="text-body font-bold text-slate-700 uppercase mb-3">Distribusi Tipe Konten</h3><div id="meta-feed-type" class="min-h-[260px]"></div></section>
                            </div>
                            <section class="bg-white radius-panel border border-slate-100 p-5">
                                <h3 class="text-body font-bold text-slate-700 uppercase mb-3">Insight Feed yang Bisa Dipakai</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                                    <div v-for="insight in metaFeedInsights" :key="insight.label" class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                        <p class="text-overline font-bold uppercase text-slate-400">{{ insight.label }}</p>
                                        <p class="mt-2 text-display-sm font-bold text-slate-900">{{ insight.value }}</p>
                                        <p class="mt-1 text-body-sm text-slate-500">{{ insight.detail }}</p>
                                    </div>
                                </div>
                            </section>
                            </template>
                            <section class="section-card section-card-shell">
                                <div class="px-5 pt-5"><h3 class="text-body font-bold text-slate-700 uppercase mb-3">Data Feed Konten</h3></div>
                                <div class="overflow-x-auto"><table class="w-full text-body-sm text-left border-collapse min-w-[900px]">
                                    <thead><tr class="table-header-row">
                                        <th class="table-header-cell table-header-index table-freeze-index">#</th>
                                        <th class="table-header-cell">Tanggal</th><th class="table-header-cell">Tipe</th><th class="table-header-cell">Akun</th><th class="table-header-cell">Konten</th>
                                        <th class="table-header-cell text-right">Views</th><th class="table-header-cell text-right">Reach</th><th class="table-header-cell text-right">Likes</th>
                                        <th class="table-header-cell text-right">Komen</th><th class="table-header-cell text-right">Share</th><th class="table-header-cell text-right">Save</th>
                                    </tr></thead>
                                    <tbody>
                                        <tr v-if="filteredMetaFeed.length === 0">
                                            <td colspan="11" class="px-4 py-16 text-center text-slate-300">
                                                <i class="fa-solid fa-photo-film text-4xl mb-3 opacity-20 block"></i>
                                                <p class="text-body-sm font-bold uppercase">Belum ada data Feed</p>
                                                <p class="text-overline-xs mt-1">Upload CSV atau Import Folder untuk memulai</p>
                                            </td>
                                        </tr>
                                        <tr v-for="(r, idx) in pagedMetaFeed" :key="'ftr'+idx" class="border-t border-slate-50 hover:bg-slate-50/50">
                                            <td class="px-4 py-2.5 text-center text-body-sm font-bold text-slate-400 tabular-nums table-freeze-index">{{ (metaFeedPage - 1) * 15 + idx + 1 }}</td>
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
                                <div class="table-pager-bar">
                                    <div class="text-body-sm text-slate-400 font-medium">
                                        <template v-if="filteredMetaFeed.length > 0">{{ (metaFeedPage - 1) * 15 + 1 }}-{{ Math.min(metaFeedPage * 15, filteredMetaFeed.length) }} dari {{ filteredMetaFeed.length }} data</template>
                                        <template v-else>0 data</template>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button @click="metaFeedPage--" :disabled="metaFeedPage <= 1" aria-label="Halaman sebelumnya"
                                            class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-left text-body-sm"></i></button>
                                        <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ metaFeedPage }} / {{ metaFeedTotalPages }}</span>
                                        <button @click="metaFeedPage++" :disabled="metaFeedPage >= metaFeedTotalPages" aria-label="Halaman berikutnya"
                                            class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-right text-body-sm"></i></button>
                                    </div>
                                </div>
                            </section>
                            <template v-if="metaFeedData.length">
                            <section v-if="metaFeedMonthlySummary.length" class="section-card section-card-shell">
                                <div class="px-5 pt-5"><h3 class="text-body font-bold text-slate-700 uppercase mb-3">Ringkasan Bulanan per Akun</h3></div>
                                <div class="overflow-x-auto"><table class="w-full text-body-sm text-left border-collapse min-w-[920px]">
                                    <thead><tr class="table-header-row">
                                        <th class="table-header-cell table-header-index table-freeze-index">#</th>
                                        <th class="table-header-cell">Bulan</th><th class="table-header-cell">Akun</th><th class="table-header-cell text-right">Post</th><th class="table-header-cell text-right">Views</th><th class="table-header-cell text-right">Reach</th><th class="table-header-cell text-right">Likes</th><th class="table-header-cell text-right">Comments</th><th class="table-header-cell text-right">Shares</th><th class="table-header-cell text-right">Saves</th>
                                    </tr></thead>
                                    <tbody>
                                        <tr v-for="(row, idx) in metaFeedMonthlySummary" :key="`${row.month}-${row.account}`" class="border-t border-slate-50 hover:bg-slate-50/50">
                                            <td class="px-4 py-2.5 text-center text-body-sm font-bold text-slate-400 tabular-nums table-freeze-index">{{ idx + 1 }}</td>
                                            <td class="px-4 py-2.5 whitespace-nowrap text-slate-500">{{ row.month }}</td><td class="px-4 py-2.5 text-slate-700">{{ row.account }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.posts) }}</td><td class="px-4 py-2.5 text-right font-bold text-slate-800">{{ formatNumber(row.views) }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.reach) }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.likes) }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.comments) }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.shares) }}</td><td class="px-4 py-2.5 text-right text-slate-600">{{ formatNumber(row.saves) }}</td>
                                        </tr>
                                    </tbody>
                                </table></div>
                            </section>
                            <section v-if="metaFeedAccountLeaderboard.length" class="bg-white radius-panel border border-slate-100 p-5">
                                <h3 class="text-body font-bold text-slate-700 uppercase mb-3">Peringkat Akun Feed</h3>
                                <div class="space-y-2">
                                    <div v-for="(row, idx) in metaFeedAccountLeaderboard" :key="row.account" class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-100">
                                        <div class="w-7 h-7 rounded-lg bg-slate-50 flex items-center justify-center text-body font-bold text-slate-500 shrink-0">{{ idx + 1 }}</div>
                                        <div class="flex-1 min-w-0"><p class="text-body font-bold text-slate-800 truncate">{{ row.account }}</p><p class="text-overline text-slate-400">{{ formatNumber(row.posts) }} post | {{ formatNumber(row.reach) }} reach</p></div>
                                        <div class="text-right shrink-0"><p class="text-body font-bold text-amber">{{ formatNumber(row.views) }}</p><p class="text-overline-xs text-slate-400 uppercase">views</p></div>
                                    </div>
                                </div>
                            </section>
                            <section v-if="metaFeedTop.length" class="bg-white radius-panel border border-slate-100 p-5">
                                <h3 class="text-body font-bold text-slate-700 uppercase mb-3"><i class="fa-solid fa-trophy text-amber mr-1"></i> Top Konten (Views)</h3>
                                <div class="space-y-2">
                                    <div v-for="(r, idx) in metaFeedTop" :key="'fts'+idx" class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-100">
                                        <div class="w-7 h-7 rounded-lg bg-slate-50 flex items-center justify-center text-body font-bold text-slate-500 shrink-0">{{ idx + 1 }}</div>
                                        <div class="flex-1 min-w-0"><p class="text-body font-bold text-slate-800 truncate">{{ metaShortDesc(r) }}</p><p class="text-overline text-slate-400">{{ r.post_type }} | {{ r.account }} | {{ r.publish_time ? formatShortDate(r.publish_time) : '-' }}</p></div>
                                        <div class="text-right shrink-0"><p class="text-body font-bold text-amber">{{ formatNumber(r.views) }}</p><p class="text-overline-xs text-slate-400 uppercase">views</p></div>
                                    </div>
                                </div>
                            </section>
                            </template>
                            <transition name="fade">
                                <div v-if="metaFeedManualModalOpen" class="fixed inset-0 z-[90] flex items-end md:items-center justify-center glass-backdrop p-0 md:p-6" @click.self="closeMetaFeedManualModal">
                                    <form @submit.prevent="saveMetaFeedManual" class="w-full md:max-w-3xl bg-white radius-panel border border-slate-100 shadow-xl max-h-[92dvh] overflow-y-auto">
                                        <div class="sticky top-0 z-10 bg-white border-b border-slate-100 px-5 py-4 flex items-center justify-between">
                                            <div>
                                                <p class="text-overline-xs font-bold uppercase text-slate-400">Feed Konten</p>
                                                <h3 class="text-body font-bold text-slate-800">Tambah Data Manual</h3>
                                            </div>
                                            <button type="button" @click="closeMetaFeedManualModal" class="icon-utility-button icon-utility-bordered" aria-label="Tutup modal tambah feed">
                                                <i class="fa-solid fa-xmark text-body-sm"></i>
                                            </button>
                                        </div>
                                        <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label for="meta-feed-manual-post-id" class="form-label">Post ID</label>
                                                <input id="meta-feed-manual-post-id" name="meta_feed_manual_post_id" v-model="metaFeedManualForm.post_id" type="text" class="form-input" autocomplete="off" required />
                                            </div>
                                            <div>
                                                <label for="meta-feed-manual-account" class="form-label">Akun</label>
                                                <input id="meta-feed-manual-account" name="meta_feed_manual_account" v-model="metaFeedManualForm.account" type="text" class="form-input" autocomplete="off" required />
                                            </div>
                                            <div>
                                                <label for="meta-feed-manual-publish-time" class="form-label">Tanggal Publish</label>
                                                <input id="meta-feed-manual-publish-time" name="meta_feed_manual_publish_time" v-model="metaFeedManualForm.publish_time" type="datetime-local" class="form-input" required />
                                            </div>
                                            <div>
                                                <label for="meta-feed-manual-post-type" class="form-label">Tipe Konten</label>
                                                <input id="meta-feed-manual-post-type" name="meta_feed_manual_post_type" v-model="metaFeedManualForm.post_type" type="text" class="form-input" placeholder="IG reel / IG image / IG carousel" autocomplete="off" />
                                            </div>
                                            <div class="md:col-span-2">
                                                <label for="meta-feed-manual-description" class="form-label">Konten</label>
                                                <textarea id="meta-feed-manual-description" name="meta_feed_manual_description" v-model="metaFeedManualForm.description" rows="3" class="form-input resize-none"></textarea>
                                            </div>
                                            <div v-for="metric in metaFeedManualMetricFields" :key="metric.key">
                                                <label :for="`meta-feed-manual-${metric.key}`" class="form-label">{{ metric.label }}</label>
                                                <input :id="`meta-feed-manual-${metric.key}`" :name="`meta_feed_manual_${metric.key}`" v-model="metaFeedManualForm[metric.key]" type="number" min="0" step="1" class="form-input" />
                                            </div>
                                        </div>
                                        <div class="sticky bottom-0 bg-white border-t border-slate-100 px-5 py-4 flex items-center justify-end gap-2">
                                            <button type="button" @click="closeMetaFeedManualModal" class="secondary-cta-button">Batal</button>
                                            <button type="submit" :disabled="submitting" class="primary-cta-button primary-cta-button--accent">
                                                <i :class="submitting ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-floppy-disk'"></i>
                                                {{ submitting ? 'Menyimpan...' : 'Simpan' }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </transition>
                    </div>
@endverbatim
