@verbatim
<div v-if="activeTab === 'activity_logs'" class="space-y-4 animate-fadeIn pb-10">
                        <section class="section-card section-card-body">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 md:gap-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-amber text-light flex items-center justify-center border border-amber">
                                        <i class="fa-solid fa-clock-rotate-left text-body"></i>
                                    </div>
                                    <div>
                                        <h2 class="type-body font-bold text-slate-900">Activity Logs</h2>
                                        <p class="type-body text-slate-500">Riwayat perubahan penting dari modul dashboard dan master data.</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="summary-counter-pill">{{ activityLogs.length }} log</span>
                                </div>
                            </div>
                        </section>

                        <section class="section-card section-card-shell">
                            <div class="py-3 px-4 border-b border-slate-100 flex flex-col md:flex-row items-stretch md:items-center gap-2">
                                <div class="relative flex-1">
                                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                    <label for="activity-log-record-key" class="sr-only">Cari record key</label>
                                    <input id="activity-log-record-key" name="activity_log_record_key" v-model="activityLogFilters.record_key" type="text" placeholder="Cari record key..."
                                        autocomplete="off" aria-label="Cari record key activity log"
                                        class="form-input-search" />
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 md:min-w-[510px]">
                                    <div class="relative">
                                        <label for="activity-log-table-name" class="sr-only">Filter tabel activity log</label>
                                        <select id="activity-log-table-name" name="activity_log_table_name" v-model="activityLogFilters.table_name" aria-label="Filter tabel activity log" class="form-input w-full min-w-0">
                                            <option value="">Semua tabel</option>
                                            <option value="users">users</option>
                                            <option value="marketing_settings">marketing_settings</option>
                                            <option value="master_plans">master_plans</option>
                                            <option value="distributions">distributions</option>
                                            <option value="analytics">analytics</option>
                                            <option value="meta_ig_posts">meta_ig_posts</option>
                                            <option value="unboxing">unboxing</option>
                                            <option value="story_schedules">story_schedules</option>
                                            <option value="calendar_events">calendar_events</option>
                                            <option value="ideation">ideation</option>
                                            <option value="program_promo">program_promo</option>
                                            <option value="sell_out_targets">sell_out_targets</option>
                                            <option value="ads_performance">ads_performance</option>
                                            <option value="harga_kompetitor">harga_kompetitor</option>
                                            <option value="orderan_online">orderan_online</option>
                                            <option value="unit_ditanya">unit_ditanya</option>
                                            <option value="claim_garansi">claim_garansi</option>
                                            <option value="keep_barang">keep_barang</option>
                                            <option value="lpjk">lpjk</option>
                                            <option value="lpjk_detail">lpjk_detail</option>
                                        </select>
                                    </div>
                                    <div class="relative">
                                        <label for="activity-log-action" class="sr-only">Filter aksi activity log</label>
                                        <select id="activity-log-action" name="activity_log_action" v-model="activityLogFilters.action" aria-label="Filter aksi activity log" class="form-input w-full min-w-0">
                                            <option value="">Semua aksi</option>
                                            <option value="create">create</option>
                                            <option value="update">update</option>
                                            <option value="delete">delete</option>
                                        </select>
                                    </div>
                                    <button @click="loadActivityLogs"
                                        class="select-trigger-button select-trigger-button-compact justify-center">
                                        <span class="text-slate-700 font-medium">{{ activityLogsLoaded ? 'Refresh' : 'Memuat' }}</span>
                                        <i class="fa-solid fa-rotate-right text-body-sm text-slate-300"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="px-4 py-2.5 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between gap-2">
                                    <span class="type-body-sm font-bold uppercase text-slate-400">Riwayat aktivitas</span>
                                    <span class="status-pill status-pill--warm">
                                        {{ activityLogsLoaded ? 'terbaru' : 'memuat' }}
                                    </span>
                            </div>
                            <div class="bg-white overflow-hidden">
                                <div class="md:hidden p-3 space-y-3">
                                    <article v-if="!activityLogsLoaded" class="stat-card mobile-record-card mobile-data-card animate-fadeIn">
                                        <div class="mobile-data-card__summary text-slate-400">Memuat activity logs...</div>
                                    </article>
                                    <article v-else-if="!activityLogs.length" class="stat-card mobile-record-card mobile-data-card animate-fadeIn">
                                        <div class="mobile-data-card__summary text-slate-400">Belum ada activity log yang cocok.</div>
                                    </article>
                                    <article v-for="log in pagedActivityLogs" :key="`mobile-log-${log.ID}`" class="stat-card mobile-record-card mobile-data-card animate-fadeIn">
                                        <div class="mobile-data-card__header">
                                            <div>
                                                <div class="mobile-data-card__title">{{ log.table_name }}</div>
                                                <div class="mobile-data-card__meta">{{ formatFullDate(log.created_at) }}</div>
                                            </div>
                                            <span :class="[
                                                'entity-badge',
                                                log.action === 'create' ? 'entity-badge--success' :
                                                log.action === 'update' ? 'entity-badge--warn' :
                                                'entity-badge--danger'
                                            ]">{{ log.action }}</span>
                                        </div>
                                        <div class="mobile-data-card__summary">
                                            <div class="font-semibold text-slate-700">{{ log.record_key }}</div>
                                            <div class="text-slate-500 mt-1">{{ log.actor_label || ('User #' + (log.user_id || '-')) }}</div>
                                        </div>
                                    </article>
                                </div>
                                <div class="md:hidden flex items-center justify-center gap-2 py-2 border-t border-slate-100">
                                    <button @click="activityLogPage--" :disabled="activityLogPage <= 1"
                                        aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-left text-body-sm"></i></button>
                                    <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ activityLogPage }} / {{ activityLogTotalPages }}</span>
                                    <button @click="activityLogPage++" :disabled="activityLogPage >= activityLogTotalPages"
                                        aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-right text-body-sm"></i></button>
                                </div>
                                <div class="hidden md:block overflow-auto">
                                    <table class="w-full text-body-sm">
                                        <thead class="bg-slate-50">
                                            <tr class="table-header-row">
                                                <th class="table-header-cell text-left">Waktu</th>
                                                <th class="table-header-cell text-left">Aksi</th>
                                                <th class="table-header-cell text-left">Tabel</th>
                                                <th class="table-header-cell text-left">Record Key</th>
                                                <th class="table-header-cell text-left">User</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            <tr v-if="!activityLogsLoaded">
                                                <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-body">Memuat activity logs...</td>
                                            </tr>
                                            <tr v-else-if="!activityLogs.length">
                                                <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-body">Belum ada activity log yang cocok.</td>
                                            </tr>
                                            <tr v-for="log in pagedActivityLogs" :key="log.ID" class="hover:bg-slate-50 transition">
                                                <td class="px-4 py-2.5 text-slate-600 whitespace-nowrap">{{ formatFullDate(log.created_at) }}</td>
                                                <td class="px-4 py-2.5">
                                                    <span :class="[
                                                        'entity-badge',
                                                        log.action === 'create' ? 'entity-badge--success' :
                                                        log.action === 'update' ? 'entity-badge--warn' :
                                                        'entity-badge--danger'
                                                    ]">{{ log.action }}</span>
                                                </td>
                                                <td class="px-4 py-2.5 text-slate-700 font-medium whitespace-nowrap">{{ log.table_name }}</td>
                                                <td class="px-4 py-2.5 text-slate-600 min-w-[220px]">{{ log.record_key }}</td>
                                                <td class="px-4 py-2.5 text-slate-600 min-w-[180px]">{{ log.actor_label || ('User #' + (log.user_id || '-')) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="px-4 py-3 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
                                <div class="text-body-sm text-slate-400 font-medium">
                                    <template v-if="activityLogs.length > 0">{{ (activityLogPage - 1) * 15 + 1 }}-{{ Math.min(activityLogPage * 15, activityLogs.length) }} dari {{ activityLogs.length }} log</template>
                                    <template v-else>0 log</template>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button @click="activityLogPage--" :disabled="activityLogPage <= 1"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-left text-body-sm"></i></button>
                                    <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ activityLogPage }} / {{ activityLogTotalPages }}</span>
                                    <button @click="activityLogPage++" :disabled="activityLogPage >= activityLogTotalPages"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-right text-body-sm"></i></button>
                                </div>
                            </div>
                        </section>
                    </div>
@endverbatim
