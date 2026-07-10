@verbatim
<!-- Talent Bonus tab -->
            <div v-if="activeTab === 'talent_bonus' && !bonusConfigLoaded"
                class="space-y-6 animate-fadeIn pb-10 animate-pulse">
                <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                    <div v-for="i in 4" :key="'sk-tb-st'+i" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="h-3 bg-slate-100 rounded-full w-20 mb-2"></div>
                        <div class="h-6 bg-slate-200 rounded-full w-24 mb-1"></div>
                        <div class="h-3 bg-slate-100 rounded-full w-16"></div>
                    </div>
                </div>
                <div class="section-card section-card-body">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-slate-200"></div>
                        <div class="space-y-2">
                            <div class="h-5 bg-slate-200 rounded-full w-44"></div>
                            <div class="h-3 bg-slate-100 rounded-full w-56"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="activeTab === 'talent_bonus' && bonusConfigLoaded" class="space-y-6 animate-fadeIn pb-10">
                <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i class="fa-solid fa-coins text-[120px]"></i></div>
                        <p class="text-[9px] font-bold uppercase tracking-widest text-amber-500 mb-3">Total Bonus</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ formatCurrency(talentDashboardData.totalBonus) }}</span>
                        </div>
                        <p class="text-[10px] font-bold text-amber-600 mt-3">{{ talentDashboardData.totalEntries }} kredit talent</p>
                    </div>
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i class="fa-solid fa-eye text-[120px]"></i></div>
                        <p class="text-[9px] font-bold uppercase tracking-widest text-blue-500 mb-3">Views</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ formatNumber(talentDashboardData.totalViews) }}</span>
                        </div>
                        <p class="text-[10px] font-bold text-blue-600 mt-3">Akumulasi kredit</p>
                    </div>
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i class="fa-solid fa-heart text-[120px]"></i></div>
                        <p class="text-[9px] font-bold uppercase tracking-widest text-rose-500 mb-3">Likes</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ formatNumber(talentDashboardData.totalLikes) }}</span>
                        </div>
                        <p class="text-[10px] font-bold text-rose-600 mt-3">Akumulasi kredit</p>
                    </div>
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i class="fa-solid fa-comment text-[120px]"></i></div>
                        <p class="text-[9px] font-bold uppercase tracking-widest text-emerald-500 mb-3">Komentar</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ formatNumber(talentDashboardData.totalComments) }}</span>
                        </div>
                        <p class="text-[10px] font-bold text-emerald-600 mt-3">Akumulasi kredit</p>
                    </div>
                </div>

                <section class="section-card section-card-body">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="modal-header-copy">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                                <i class="fa-solid fa-user-tag text-[16px]"></i>
                            </div>
                            <div>
                                <h2 class="type-title font-bold text-slate-900">Talent Bonus</h2>
                                <p class="text-[10px] text-amber-500 uppercase tracking-widest font-bold mt-0.5">Full credit per talent</p>
                            </div>
                        </div>
                        <div class="text-[10px] text-slate-400 font-medium">
                            Periode {{ formatShortDate(bonusFilter.start) }} - {{ formatShortDate(bonusFilter.end) }}
                        </div>
                    </div>
                    <div class="mt-4 rounded-2xl border border-amber-100 bg-amber-50/70 px-4 py-3">
                        <p class="text-[9px] font-extrabold uppercase tracking-[0.18em] text-amber-600">Aturan Bonus Talent</p>
                        <p class="mt-1 text-[11px] leading-relaxed text-slate-600">
                            `1-2` video per hari dihitung `Rp150.000`. Jumlah genap di atas `2` dihitung langsung per pasangan video.
                            Jika total harian ganjil seperti `3`, `5`, atau `7`, maka sisa `1` video dibawa ke hari berikutnya sebagai carry-over.
                        </p>
                        <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div class="rounded-2xl border border-white/80 bg-white/80 px-3 py-3">
                                <p class="text-[9px] font-extrabold uppercase tracking-[0.18em] text-slate-500">Contoh Dasar</p>
                                <p class="mt-1 text-[11px] font-bold text-slate-800">1-2 video dalam 1 hari</p>
                                <p class="mt-1 text-[10px] leading-relaxed text-slate-500">Selama total harian masih 1 atau 2 video, bonus tetap 1 paket.</p>
                                <p class="mt-2 text-[11px] font-extrabold text-amber-600">Dibayar hari itu: Rp150.000</p>
                            </div>
                            <div class="rounded-2xl border border-white/80 bg-white/80 px-3 py-3">
                                <p class="text-[9px] font-extrabold uppercase tracking-[0.18em] text-slate-500">Contoh 1</p>
                                <p class="mt-1 text-[11px] font-bold text-slate-800">3 video dalam 1 hari</p>
                                <p class="mt-1 text-[10px] leading-relaxed text-slate-500">2 video pertama = `Rp150.000`, sisa 1 video dibawa ke hari berikutnya.</p>
                                <p class="mt-2 text-[11px] font-extrabold text-amber-600">Dibayar hari itu: Rp150.000</p>
                            </div>
                            <div class="rounded-2xl border border-white/80 bg-white/80 px-3 py-3">
                                <p class="text-[9px] font-extrabold uppercase tracking-[0.18em] text-slate-500">Contoh 2</p>
                                <p class="mt-1 text-[11px] font-bold text-slate-800">Besok 5 video + carry 1</p>
                                <p class="mt-1 text-[10px] leading-relaxed text-slate-500">Total efektif jadi 6 video, berarti 3 paket bonus.</p>
                                <p class="mt-2 text-[11px] font-extrabold text-amber-600">Dibayar hari itu: Rp450.000</p>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
                    <div class="lg:col-span-2 bg-white radius-panel border border-slate-100 p-5">
                        <h3 class="text-[11px] font-bold text-slate-700 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-ranking-star text-amber-500"></i> Ranking Talent
                        </h3>
                        <div v-if="talentDashboardData.leaderboard.length === 0" class="text-center py-10 text-[11px] text-slate-400">
                            Belum ada data talent pada periode ini
                        </div>
                        <div v-for="(talent, idx) in talentDashboardData.leaderboard" :key="talent.name"
                            class="flex items-center gap-3 py-3 border-b border-slate-50 last:border-0">
                            <div :class="['w-7 h-7 rounded-xl flex items-center justify-center text-[10px] font-extrabold shrink-0', idx === 0 ? 'bg-amber-400 text-white' : 'bg-slate-100 text-slate-500']">
                                {{ idx + 1 }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[11px] font-bold text-slate-800 uppercase truncate">{{ talent.name }}</p>
                                <p class="text-[9px] text-slate-400 font-bold">{{ talent.count }} kredit | {{ formatNumber(talent.views) }} views</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-[11px] font-bold text-amber-600">{{ formatCurrency(talent.bonus) }}</p>
                                <p class="text-[8px] text-slate-400 font-bold uppercase">bonus</p>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-3 section-card section-card-shell">
                        <div class="px-5 py-4 border-b border-slate-50 flex items-center justify-between">
                            <h3 class="text-[11px] font-bold text-slate-700 uppercase tracking-widest flex items-center gap-2">
                                <i class="fa-solid fa-address-card text-amber-500"></i> Detail Kredit Talent
                            </h3>
                            <span class="text-[9px] text-slate-400 font-bold">{{ talentDashboardData.rows.length }} baris</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-[10.5px] text-left border-collapse" style="min-width:560px">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-5 py-3 text-[9px] uppercase tracking-widest text-slate-400 font-bold">Talent</th>
                                        <th class="px-5 py-3 text-[9px] uppercase tracking-widest text-slate-400 font-bold">Tanggal</th>
                                        <th class="px-5 py-3 text-[9px] uppercase tracking-widest text-slate-400 font-bold">Detail</th>
                                        <th class="px-5 py-3 text-[9px] uppercase tracking-widest text-slate-400 font-bold text-right">Video</th>
                                        <th class="px-5 py-3 text-[9px] uppercase tracking-widest text-slate-400 font-bold text-right">Bonus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="pagedTalentRows.length === 0">
                                        <td colspan="5" class="px-5 py-12 text-center text-[11px] text-slate-400">Belum ada data bonus talent</td>
                                    </tr>
                                    <tr v-for="(row, idx) in pagedTalentRows" :key="row.id + '-' + row.Talent + '-' + idx" class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                        <td class="px-5 py-3 text-[11px] font-bold text-slate-700 uppercase">{{ row.Talent }}</td>
                                        <td class="px-5 py-3 text-[10px] text-slate-500">{{ formatShortDate(row.date) }}</td>
                                        <td class="px-5 py-3">
                                            <p class="text-[11px] font-semibold text-slate-800 leading-tight">{{ row.videoCount }} video{{ row.videoCount > 1 ? '' : '' }}</p>
                                            <p class="text-[9px] text-slate-400 mt-0.5">{{ row.detailLabel }}</p>
                                        </td>
                                        <td class="px-5 py-3 text-right text-[11px] font-bold text-slate-700">{{ formatNumber(row.videoCount) }}</td>
                                        <td class="px-5 py-3 text-right text-[11px] font-bold text-amber-600">{{ formatCurrency(row.calculatedBonus) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="px-5 py-3.5 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
                            <div class="text-[10px] text-slate-400 font-medium">
                                <template v-if="talentDashboardData.rows.length > 0">{{ (talentPage - 1) * 15 + 1 }}-{{ Math.min(talentPage * 15, talentDashboardData.rows.length) }} dari {{ talentDashboardData.rows.length }}</template>
                                <template v-else>0 data</template>
                            </div>
                            <div class="flex items-center gap-1">
                                <button @click="talentPage--" :disabled="talentPage <= 1" aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-left text-[10px]"></i></button>
                                <span class="px-3 text-[10px] font-bold text-amber-600">{{ talentPage }} / {{ talentTotalPages }}</span>
                                <button @click="talentPage++" :disabled="talentPage >= talentTotalPages" aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-right text-[10px]"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
@endverbatim
