@verbatim
<div v-if="activeTab === 'dashboard'" class="space-y-4">
                        <div class="section-card section-card-body">
                            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <div class="type-meta uppercase tracking-[0.2em] text-slate-400 mb-2">Ringkasan
                                    </div>
                                    <h2 class="text-xl font-semibold text-slate-900">Dashboard Operasional</h2>
                                    <p class="type-body text-slate-500 mt-2">Dashboard marketing lengkap berjalan di
                                        Laravel
                                        dengan data real dari database yang sudah terhubung.</p>
                                </div>
                                <div class="grid grid-cols-1 gap-2 type-body text-slate-500 w-full min-w-0 md:min-w-[220px]">
                                    <div
                                        class="flex items-center justify-between gap-3 bg-slate-50 rounded-2xl px-4 py-3">
                                        <span>User</span>
                                        <span class="font-medium text-slate-800">{{ currentUser?.username || currentUser?.nama || 'User' }}</span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between gap-3 bg-slate-50 rounded-2xl px-4 py-3">
                                        <span>Role</span>
                                        <span class="font-medium text-slate-800">{{ currentUser?.role || '-' }}</span>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
                            <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                                <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-layer-group text-[120px]"></i></div>
                                <p class="dashboard-summary-title">Total Plan</p>
                                <div class="flex items-baseline gap-2">
                                    <span class="dashboard-summary-value">{{ masterPlanData.length }}</span>
                                </div>
                                <p class="text-body-sm font-bold text-amber mt-3">Master Plan</p>
                            </div>
                            <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                                <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-circle-check text-[120px]"></i></div>
                                <p class="dashboard-summary-title">Published</p>
                                <div class="flex items-baseline gap-2">
                                    <span class="dashboard-summary-value">{{ masterPlanData.filter(i => i.Status === 'PUBLISHED').length }}</span>
                                </div>
                                <p class="text-body-sm font-bold text-success mt-3">Konten terpublish</p>
                            </div>
                            <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                                <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-pen-nib text-[120px]"></i></div>
                                <p class="dashboard-summary-title">On Progress</p>
                                <div class="flex items-baseline gap-2">
                                    <span class="dashboard-summary-value">{{ masterPlanData.filter(i => ['SHOOTING','EDITING'].includes(i.Status)).length }}</span>
                                </div>
                                <p class="text-body-sm font-bold text-amber mt-3">Sedang dikerjakan</p>
                            </div>
                            <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                                <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-clapperboard text-[120px]"></i></div>
                                <p class="dashboard-summary-title">Jadwal Story</p>
                                <div class="flex items-baseline gap-2">
                                    <span class="dashboard-summary-value">{{ storyData.length }}</span>
                                </div>
                                <p class="text-body-sm font-bold text-slate-600 mt-3">Story terjadwal</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                            <section class="section-card section-card-body dashboard-latest-content xl:col-span-2">
                                <div class="dashboard-panel-heading flex items-center justify-between mb-5">
                                    <div>
                                        <div class="type-body-sm uppercase tracking-[0.2em] text-slate-400">Master Plan
                                        </div>
                                        <h3 class="text-sm font-semibold text-slate-900 mt-1">Konten Terbaru</h3>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div v-if="masterPlanData.length === 0"
                                        class="py-10 text-center text-body text-slate-400">Belum ada data master plan.
                                    </div>
                                    <div v-for="item in masterPlanData.slice(0, 5)" :key="item.ID"
                                        class="dashboard-latest-content__item rounded-2xl bg-slate-50 flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="type-title font-semibold text-slate-800 truncate">{{ item.Judul }}</div>
                                            <div class="type-body-sm text-slate-400 mt-0.5">{{ item.Format_Konten }} | {{ item.Editor }}<span v-if="item.TalentList && item.TalentList.length"> | Talent: {{ item.TalentList.join(', ') }}</span></div>
                                        </div>
                                        <span
                                            :class="['inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold whitespace-nowrap', item.Status === 'PUBLISHED' ? 'bg-success text-light' : item.Status === 'EDITING' ? 'bg-amber text-light' : 'bg-amber text-light']">{{ item.Status }}</span>
                                    </div>
                                </div>
                            </section>

                            <section class="section-card section-card-body dashboard-info-panel">
                                <div class="dashboard-panel-heading mb-5">
                                    <div class="type-body-sm uppercase tracking-[0.2em] text-slate-400">Status Akun
                                    </div>
                                    <h3 class="text-sm font-semibold text-slate-900 mt-1">Info Dashboard</h3>
                                </div>
                                <div class="space-y-2">
                                    <div class="dashboard-info-panel__item rounded-2xl bg-slate-50 flex items-center justify-between gap-3">
                                        <span class="type-body text-slate-500">User</span>
                                        <span class="text-body font-semibold text-slate-900">{{ currentUser?.username }}</span>
                                    </div>
                                    <div class="dashboard-info-panel__item rounded-2xl bg-slate-50 flex items-center justify-between gap-3">
                                        <span class="type-body text-slate-500">Role</span>
                                        <span class="text-body font-semibold text-slate-900">{{ currentUser?.role }}</span>
                                    </div>
                                    <div class="dashboard-info-panel__item rounded-2xl bg-slate-50 flex items-center justify-between gap-3">
                                        <span class="type-body text-slate-500">Total Analytics</span>
                                        <span class="text-body font-semibold text-slate-900">{{ analyticsData.length }} Data</span>
                                    </div>
                                    <div class="dashboard-info-panel__item rounded-2xl bg-slate-50 flex items-center justify-between gap-3">
                                        <span class="type-body text-slate-500">Status</span>
                                        <span
                                            class="text-overline font-bold text-light bg-success px-2.5 py-1 rounded-full uppercase tracking-widest">Aktif</span>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
@endverbatim
