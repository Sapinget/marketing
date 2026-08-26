@verbatim
<div v-if="activeTab === 'meta_followers' && !metaFollowersLoaded" class="meta-analytics-page space-y-4 animate-fadeIn">
                        <section class="section-card section-card-shell px-6 py-16 text-center text-slate-300">
                            <i class="fa-solid fa-spinner fa-spin text-3xl mb-3 opacity-40 block"></i>
                            <p class="text-body-sm font-bold uppercase">Memuat data Followers</p>
                            <p class="text-overline-xs mt-1">Dashboard akan tampil setelah data selesai dibaca</p>
                        </section>
                    </div>
<div v-if="activeTab === 'meta_followers' && metaFollowersLoaded" class="meta-analytics-page space-y-4 animate-fadeIn">
                        <section class="section-card meta-toolbar-card">
                            <div class="table-toolbar-shell">
                                <div class="table-toolbar-shell__left"></div>
                                <div class="table-toolbar-shell__right">
                                    <button type="button" @click="$refs.metaFollowersUpload?.click()" class="primary-cta-button primary-cta-button--accent primary-cta-button--icon-only active:scale-95" :disabled="metaUploading" aria-label="Upload CSV Followers IG" title="Upload CSV">
                                        <i :class="metaUploading ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-upload'"></i>
                                    </button>
                                    <input ref="metaFollowersUpload" id="meta-followers-upload" name="meta_followers_upload" type="file" accept=".csv" class="hidden" aria-label="Upload CSV followers IG" @change="handleMetaFollowersFileInput" />
                                    <button @click="deleteAllMetaFollowers" class="primary-cta-button primary-cta-button--danger primary-cta-button--icon-only active:scale-95" :disabled="metaUploading || !metaFollowersData.length" aria-label="Hapus semua data followers" title="Hapus Semua">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                        </section>
                        <template v-if="metaFollowersData.length">
                            <div class="dashboard-summary-grid-compact grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
                                <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                                    <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-calendar-day text-[120px]"></i></div>
                                    <p class="dashboard-summary-title">Pertama Tercatat</p>
                                    <div class="flex items-baseline gap-2"><span class="dashboard-summary-value">{{ metaFollowersSummary.firstDate }}</span></div>
                                    <p class="text-body-sm font-bold mt-3 text-slate-500">{{ formatNumber(metaFollowersSummary.firstCount) }} followers</p>
                                </div>
                                <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                                    <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-calendar-day text-[120px]"></i></div>
                                    <p class="dashboard-summary-title">Terakhir Tercatat</p>
                                    <div class="flex items-baseline gap-2"><span class="dashboard-summary-value">{{ metaFollowersSummary.latestDate }}</span></div>
                                    <p class="text-body-sm font-bold mt-3 text-ppp-accent">{{ formatNumber(metaFollowersSummary.latestCount) }} followers</p>
                                </div>
                                <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                                    <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-database text-[120px]"></i></div>
                                    <p class="dashboard-summary-title">Total Hari</p>
                                    <div class="flex items-baseline gap-2"><span class="dashboard-summary-value">{{ metaFollowersSummary.total }}</span><span class="dashboard-summary-unit">hari</span></div>
                                    <p class="text-body-sm font-bold mt-3 text-slate-500">Riwayat followers</p>
                                </div>
                            </div>
                            <section class="section-card section-card-shell overflow-hidden">
                                <div class="px-6 py-4 border-b border-slate-50 flex items-center justify-between">
                                    <h3 class="type-title font-semibold text-slate-900">Tren Followers</h3>
                                    <span class="type-body-sm text-slate-400">Pertumbuhan akumulatif</span>
                                </div>
                                <div id="meta-followers-trend" class="min-h-[300px]"></div>
                            </section>
                        </template>
                        <section class="section-card section-card-shell">
                            <div class="px-5 pt-5"><h3 class="text-body font-bold text-slate-700 uppercase mb-3">Data Followers IG</h3></div>
                            <div class="overflow-x-auto"><table class="w-full text-body-sm text-left border-collapse min-w-[480px]">
                                <thead><tr class="table-header-row">
                                    <th class="table-header-cell table-header-index table-freeze-index">#</th>
                                    <th class="table-header-cell">Tanggal</th>
                                    <th class="table-header-cell text-right">Jumlah Followers</th>
                                </tr></thead>
                                <tbody>
                                    <tr v-if="metaFollowersData.length === 0">
                                        <td colspan="3" class="px-4 py-16 text-center text-slate-300">
                                            <i class="fa-brands fa-instagram text-4xl mb-3 opacity-20 block"></i>
                                            <p class="text-body-sm font-bold uppercase">Belum ada data Followers</p>
                                            <p class="text-overline-xs mt-1">Upload CSV untuk memulai</p>
                                        </td>
                                    </tr>
                                    <tr v-for="(r, idx) in metaFollowersData" :key="r.follow_date || idx" class="border-t border-slate-50 hover:bg-slate-50/50">
                                        <td class="px-4 py-2.5 text-center text-body-sm font-bold text-slate-400 tabular-nums table-freeze-index">{{ idx + 1 }}</td>
                                        <td class="px-4 py-2.5 whitespace-nowrap text-slate-500">{{ r.follow_date || '-' }}</td>
                                        <td class="px-4 py-2.5 text-right font-bold text-slate-800 tabular-nums">{{ formatNumber(r.count) }}</td>
                                    </tr>
                                </tbody>
                            </table></div>
                        </section>
                    </div>
@endverbatim
