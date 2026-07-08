@verbatim
<div v-if="activeTab === 'activity_logs'" class="space-y-6 animate-fadeIn pb-10">
                        <section class="section-card section-card-body">
                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100">
                                        <i class="fa-solid fa-clock-rotate-left text-lg"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-semibold text-slate-900">Activity Logs</h2>
                                        <p class="type-body text-slate-500">Riwayat create, update, dan delete dari modul inti dashboard.</p>
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <input v-model="activityLogFilters.record_key" type="text" placeholder="Cari record key..."
                                        class="form-input min-w-[180px] md:min-w-[220px]" />
                                    <select v-model="activityLogFilters.table_name" class="form-input min-w-[170px]">
                                        <option value="">Semua tabel</option>
                                        <option value="master_plans">master_plans</option>
                                        <option value="distributions">distributions</option>
                                        <option value="analytics">analytics</option>
                                        <option value="lpjk">lpjk</option>
                                        <option value="lpjk_detail">lpjk_detail</option>
                                    </select>
                                    <select v-model="activityLogFilters.action" class="form-input min-w-[150px]">
                                        <option value="">Semua aksi</option>
                                        <option value="create">create</option>
                                        <option value="update">update</option>
                                        <option value="delete">delete</option>
                                    </select>
                                    <button @click="loadActivityLogs"
                                        class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-2 text-[11px] font-bold uppercase tracking-widest text-slate-600 hover:border-slate-300 hover:text-slate-900 transition-colors">
                                        <i class="fa-solid fa-rotate-right"></i>
                                        Refresh
                                    </button>
                                </div>
                            </div>
                        </section>

                        <section class="bg-white radius-dialog border border-slate-100 p-0 shadow-sm overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-100">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Waktu</th>
                                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Aksi</th>
                                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Tabel</th>
                                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Record Key</th>
                                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">User</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        <tr v-if="!activityLogsLoaded">
                                            <td colspan="5" class="px-4 py-6 text-center text-[11px] font-medium text-slate-400">Memuat activity logs...</td>
                                        </tr>
                                        <tr v-else-if="!activityLogs.length">
                                            <td colspan="5" class="px-4 py-6 text-center text-[11px] font-medium text-slate-400">Belum ada activity log yang cocok.</td>
                                        </tr>
                                        <tr v-for="log in activityLogs" :key="log.id">
                                            <td class="px-4 py-3 text-[11px] text-slate-500 whitespace-nowrap">{{ formatFullDate(log.created_at) }}</td>
                                            <td class="px-4 py-3">
                                                <span :class="[
                                                    'inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest',
                                                    log.action === 'create' ? 'bg-emerald-50 text-emerald-700' :
                                                    log.action === 'update' ? 'bg-amber-50 text-amber-700' :
                                                    'bg-rose-50 text-rose-700'
                                                ]">{{ log.action }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-[11px] font-semibold text-slate-700">{{ log.table_name }}</td>
                                            <td class="px-4 py-3 text-[11px] text-slate-600">{{ log.record_key }}</td>
                                            <td class="px-4 py-3 text-[11px] text-slate-500">{{ log.actor_label || ('User #' + (log.user_id || '-')) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
@endverbatim
