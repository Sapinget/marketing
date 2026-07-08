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
                                <div class="grid grid-cols-1 gap-2 type-body text-slate-500 min-w-[220px]">
                                    <div
                                        class="flex items-center justify-between gap-3 bg-slate-50 rounded-2xl px-4 py-3">
                                        <span>User</span>
                                        <span class="font-medium text-slate-800">{{ currentUser.username }}</span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between gap-3 bg-slate-50 rounded-2xl px-4 py-3">
                                        <span>Role</span>
                                        <span class="font-medium text-slate-800">{{ currentUser.role }}</span>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="dashboard-summary-grid-compact grid grid-cols-3 md:grid-cols-4 gap-3 md:gap-4">
                            <div class="bg-white p-4 md:p-5 radius-card border border-slate-100">
                                <div
                                    class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
                                    <i class="fa-solid fa-layer-group text-[12px]"></i>
                                </div>
                                <div class="text-[9px] uppercase font-medium text-slate-400 mb-1 tracking-[0.12em]">
                                    Total Plan</div>
                                <div class="text-base md:text-lg font-semibold text-slate-900">{{ masterPlanData.length }}</div>
                            </div>
                            <div class="bg-white p-4 md:p-5 radius-card border border-slate-100">
                                <div
                                    class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4">
                                    <i class="fa-solid fa-circle-check text-[12px]"></i>
                                </div>
                                <div class="text-[9px] uppercase font-medium text-slate-400 mb-1 tracking-[0.12em]">
                                    Published</div>
                                <div class="text-base md:text-lg font-semibold text-slate-900">{{ masterPlanData.filter(i => i.Status === 'PUBLISHED').length }}</div>
                            </div>
                            <div class="bg-white p-4 md:p-5 radius-card border border-slate-100">
                                <div
                                    class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mb-4">
                                    <i class="fa-solid fa-pen-nib text-[12px]"></i>
                                </div>
                                <div class="text-[9px] uppercase font-medium text-slate-400 mb-1 tracking-[0.12em]">On
                                    Progress</div>
                                <div class="text-base md:text-lg font-semibold text-slate-900">{{ masterPlanData.filter(i => ['SHOOTING','EDITING'].includes(i.Status)).length }}
                                </div>
                            </div>
                            <div class="bg-white p-4 md:p-5 radius-card border border-slate-100">
                                <div
                                    class="w-10 h-10 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center mb-4">
                                    <i class="fa-solid fa-clapperboard text-[12px]"></i>
                                </div>
                                <div class="text-[9px] uppercase font-medium text-slate-400 mb-1 tracking-[0.12em]">
                                    Jadwal Story</div>
                                <div class="text-base md:text-lg font-semibold text-slate-900">{{ storyData.length }}
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                            <section class="section-card section-card-body xl:col-span-2">
                                <div class="flex items-center justify-between mb-5">
                                    <div>
                                        <div class="type-meta uppercase tracking-[0.2em] text-slate-400">Master Plan
                                        </div>
                                        <h3 class="text-sm font-semibold text-slate-900 mt-1">Konten Terbaru</h3>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div v-if="masterPlanData.length === 0"
                                        class="py-10 text-center text-[11px] text-slate-400">Belum ada data master plan.
                                    </div>
                                    <div v-for="item in masterPlanData.slice(0, 5)" :key="item.ID"
                                        class="p-4 rounded-2xl bg-slate-50 flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="type-title font-semibold text-slate-800 truncate">{{ item.Judul }}</div>
                                            <div class="type-meta text-slate-400 mt-0.5">{{ item.Format_Konten }} | {{ item.Editor }}<span v-if="item.TalentList && item.TalentList.length"> | Talent: {{ item.TalentList.join(', ') }}</span></div>
                                        </div>
                                        <span
                                            :class="['text-[9px] font-bold uppercase tracking-widest px-2.5 py-1 rounded-full whitespace-nowrap', item.Status === 'PUBLISHED' ? 'bg-emerald-50 text-emerald-600' : item.Status === 'EDITING' ? 'bg-amber-50 text-amber-600' : 'bg-indigo-50 text-indigo-500']">{{ item.Status }}</span>
                                    </div>
                                </div>
                            </section>

                            <section class="section-card section-card-body">
                                <div class="text-[10px] uppercase tracking-[0.2em] text-slate-400 mb-1">Status Akun
                                </div>
                                <h3 class="text-sm font-semibold text-slate-900 mb-5">Info Dashboard</h3>
                                <div class="space-y-3">
                                    <div class="p-4 rounded-2xl bg-slate-50 flex items-center justify-between gap-3">
                                        <span class="type-body text-slate-500">User</span>
                                        <span class="text-[11px] font-semibold text-slate-900">{{ currentUser?.username }}</span>
                                    </div>
                                    <div class="p-4 rounded-2xl bg-slate-50 flex items-center justify-between gap-3">
                                        <span class="type-body text-slate-500">Role</span>
                                        <span class="text-[11px] font-semibold text-slate-900">{{ currentUser?.role }}</span>
                                    </div>
                                    <div class="p-4 rounded-2xl bg-slate-50 flex items-center justify-between gap-3">
                                        <span class="type-body text-slate-500">Total Analytics</span>
                                        <span class="text-[11px] font-semibold text-slate-900">{{ analyticsData.length }} Data</span>
                                    </div>
                                    <div class="p-4 rounded-2xl bg-slate-50 flex items-center justify-between gap-3">
                                        <span class="type-body text-slate-500">Status</span>
                                        <span
                                            class="text-[9px] font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full uppercase tracking-widest">Aktif</span>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
@endverbatim
