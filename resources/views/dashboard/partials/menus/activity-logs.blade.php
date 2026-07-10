@verbatim
<div v-if="activeTab === 'activity_logs'" class="space-y-4 animate-fadeIn pb-10">
                        <section class="section-card section-card-body">
                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 md:w-14 md:h-14 rounded-[20px] bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                                        <i class="fa-solid fa-clock-rotate-left text-[13px] md:text-[15px]"></i>
                                    </div>
                                    <div class="space-y-1">
                                        <h2 class="type-body font-bold text-slate-900">Activity Logs</h2>
                                        <p class="type-body max-w-3xl text-slate-500">Riwayat create, update, dan delete dari modul inti dashboard, termasuk perubahan user dan konfigurasi penting.</p>
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="summary-counter-pill">{{ activityLogs.length }} log</span>
                                    <button @click="loadActivityLogs"
                                        class="secondary-cta-button secondary-cta-neutral active:scale-95">
                                        <i class="fa-solid fa-rotate-right text-[10px]"></i> Refresh
                                    </button>
                                </div>
                            </div>
                        </section>

                        <section class="section-card section-card-shell">
                            <div class="px-4 py-4 md:px-5 border-b border-slate-100 space-y-3">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                                    <div>
                                        <div class="type-meta font-bold uppercase tracking-[0.18em] text-slate-400">Filter Log</div>
                                        <p class="type-body text-slate-500">Perbesar filter dan hasil log supaya record penting lebih cepat ditemukan.</p>
                                    </div>
                                    <span class="status-pill status-pill--warm">
                                        {{ activityLogsLoaded ? 'terbaru' : 'memuat' }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-[minmax(0,1.4fr)_minmax(190px,0.8fr)_minmax(170px,0.7fr)] gap-2.5">
                                    <div class="relative">
                                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                        <label for="activity-log-record-key" class="sr-only">Cari record key</label>
                                        <input id="activity-log-record-key" name="activity_log_record_key" v-model="activityLogFilters.record_key" type="text" placeholder="Cari record key..."
                                            autocomplete="off" aria-label="Cari record key activity log"
                                            class="form-input-search" />
                                    </div>
                                    <div class="relative search-select-container">
                                        <label for="activity-log-table-name" class="sr-only">Filter tabel activity log</label>
                                        <button id="activity-log-table-name" name="activity_log_table_name" type="button"
                                            @click="toggleSearchSelect($event, 'activity_log_table_name')"
                                            :aria-expanded="searchSelectOpen === 'activity_log_table_name' ? 'true' : 'false'"
                                            aria-label="Filter tabel activity log"
                                            class="select-trigger-button select-trigger-button-compact">
                                            <span :class="activityLogFilters.table_name ? 'text-slate-800 font-medium' : 'text-slate-400'">
                                                {{ activityLogFilters.table_name || 'Semua tabel' }}
                                            </span>
                                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                        </button>
                                        <transition name="fade">
                                            <div v-if="searchSelectOpen === 'activity_log_table_name'" :style="popoverStyle"
                                                class="search-select-popover">
                                                <div class="relative mb-2 search-select-popover__search">
                                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                                    <input id="activity-log-table-name-search" name="search_select_query" v-model="searchSelectQuery" type="text" placeholder="Cari tabel..." autocomplete="off"
                                                        aria-label="Cari tabel activity log" class="form-input-popover" @click.stop />
                                                </div>
                                                <div class="max-h-48 overflow-y-auto custom-scrollbar search-select-popover__options">
                                                    <div @click="activityLogFilters.table_name = ''; searchSelectOpen = null"
                                                        :class="['popover-option', !activityLogFilters.table_name ? 'popover-option-active' : '']">
                                                        Semua tabel
                                                    </div>
                                                    <div v-for="opt in ['users', 'marketing_settings', 'master_plans', 'distributions', 'analytics', 'meta_ig_posts', 'unboxing', 'story_schedules', 'calendar_events', 'ideation', 'program_promo', 'sell_out_targets', 'ads_performance', 'harga_kompetitor', 'orderan_online', 'unit_ditanya', 'claim_garansi', 'keep_barang', 'lpjk', 'lpjk_detail'].filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                        :key="'activity-log-table-'+opt"
                                                        @click="activityLogFilters.table_name = opt; searchSelectOpen = null"
                                                        :class="['popover-option', activityLogFilters.table_name === opt ? 'popover-option-active' : '']">
                                                        {{ opt }}
                                                    </div>
                                                    <div v-if="['users', 'marketing_settings', 'master_plans', 'distributions', 'analytics', 'meta_ig_posts', 'unboxing', 'story_schedules', 'calendar_events', 'ideation', 'program_promo', 'sell_out_targets', 'ads_performance', 'harga_kompetitor', 'orderan_online', 'unit_ditanya', 'claim_garansi', 'keep_barang', 'lpjk', 'lpjk_detail'].filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                        class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                        Tidak ditemukan
                                                    </div>
                                                </div>
                                            </div>
                                        </transition>
                                    </div>
                                    <div class="relative search-select-container">
                                        <label for="activity-log-action" class="sr-only">Filter aksi activity log</label>
                                        <button id="activity-log-action" name="activity_log_action" type="button"
                                            @click="toggleSearchSelect($event, 'activity_log_action')"
                                            :aria-expanded="searchSelectOpen === 'activity_log_action' ? 'true' : 'false'"
                                            aria-label="Filter aksi activity log"
                                            class="select-trigger-button select-trigger-button-compact">
                                            <span :class="activityLogFilters.action ? 'text-slate-800 font-medium' : 'text-slate-400'">
                                                {{ activityLogFilters.action || 'Semua aksi' }}
                                            </span>
                                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                        </button>
                                        <transition name="fade">
                                            <div v-if="searchSelectOpen === 'activity_log_action'" :style="popoverStyle"
                                                class="search-select-popover search-select-popover--compact">
                                                <div class="max-h-48 overflow-y-auto custom-scrollbar search-select-popover__options">
                                                    <div @click="activityLogFilters.action = ''; searchSelectOpen = null"
                                                        :class="['popover-option', !activityLogFilters.action ? 'popover-option-active' : '']">
                                                        Semua aksi
                                                    </div>
                                                    <div v-for="opt in ['create', 'update', 'delete']"
                                                        :key="'activity-log-action-'+opt"
                                                        @click="activityLogFilters.action = opt; searchSelectOpen = null"
                                                        :class="['popover-option', activityLogFilters.action === opt ? 'popover-option-active' : '']">
                                                        {{ opt }}
                                                    </div>
                                                </div>
                                            </div>
                                        </transition>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white overflow-hidden">
                                <div class="md:hidden p-3 space-y-3">
                                    <article v-if="!activityLogsLoaded" class="stat-card mobile-record-card mobile-data-card animate-fadeIn">
                                        <div class="mobile-data-card__summary text-slate-400">Memuat activity logs...</div>
                                    </article>
                                    <article v-else-if="!activityLogs.length" class="stat-card mobile-record-card mobile-data-card animate-fadeIn">
                                        <div class="mobile-data-card__summary text-slate-400">Belum ada activity log yang cocok.</div>
                                    </article>
                                    <article v-for="log in activityLogs" :key="`mobile-log-${log.ID}`" class="stat-card mobile-record-card mobile-data-card animate-fadeIn">
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
                                <div class="overflow-x-auto">
                                    <table class="hidden md:table min-w-full divide-y divide-slate-100">
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
                                            <tr v-for="log in activityLogs" :key="log.ID">
                                                <td class="px-4 py-3 text-[12px] text-slate-500 whitespace-nowrap">{{ formatFullDate(log.created_at) }}</td>
                                                <td class="px-4 py-3">
                                                    <span :class="[
                                                        'entity-badge',
                                                        log.action === 'create' ? 'entity-badge--success' :
                                                        log.action === 'update' ? 'entity-badge--warn' :
                                                        'entity-badge--danger'
                                                    ]">{{ log.action }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-[12px] font-semibold text-slate-700 whitespace-nowrap">{{ log.table_name }}</td>
                                                <td class="px-4 py-3 text-[12px] text-slate-600 min-w-[220px]">{{ log.record_key }}</td>
                                                <td class="px-4 py-3 text-[12px] text-slate-500 min-w-[180px]">{{ log.actor_label || ('User #' + (log.user_id || '-')) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </section>
                    </div>
@endverbatim
