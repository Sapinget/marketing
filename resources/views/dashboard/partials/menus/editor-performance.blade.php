@verbatim
<!-- Editor Performance tab -->
            <div v-if="activeTab === 'editor_performance' && !bonusConfigLoaded"
                class="space-y-6 animate-fadeIn pb-10 animate-pulse">
                <div class="section-card section-card-body">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-slate-200"></div>
                        <div class="space-y-2">
                            <div class="h-5 bg-slate-200 rounded-full w-44"></div>
                            <div class="h-3 bg-slate-100 rounded-full w-56"></div>
                        </div>
                    </div>
                    <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
                        <div v-for="i in 4" :key="'sk-ep-st'+i"
                            class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                            <div class="h-3 bg-slate-200 rounded-full w-20 mb-2"></div>
                            <div class="h-6 bg-slate-200 rounded-full w-24 mb-1"></div>
                            <div class="h-3 bg-slate-100 rounded-full w-16"></div>
                        </div>
                    </div>
                </div>
                <div class="section-card section-card-shell">
                    <div class="px-6 py-4 border-b border-slate-50 flex gap-6">
                        <div class="h-3 bg-slate-200 rounded-full w-24"></div>
                        <div class="h-3 bg-slate-200 rounded-full w-20"></div>
                        <div class="h-3 bg-slate-200 rounded-full w-20"></div>
                        <div class="h-3 bg-slate-200 rounded-full w-16"></div>
                        <div class="h-3 bg-slate-200 rounded-full flex-1"></div>
                    </div>
                    <div class="divide-y divide-slate-50">
                        <div v-for="i in 6" :key="'sk-ep'+i" class="px-6 py-5 flex items-center gap-4">
                            <div class="h-4 bg-slate-100 rounded-full w-44"></div>
                            <div class="h-4 bg-slate-100 rounded-full w-20"></div>
                            <div class="h-4 bg-slate-100 rounded-full w-20"></div>
                            <div class="h-4 bg-slate-100 rounded-full w-16"></div>
                            <div class="h-4 bg-slate-100 rounded-full flex-1"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="activeTab === 'editor_performance' && bonusConfigLoaded" class="space-y-6 animate-fadeIn pb-10">

                <!-- Summary Stats (di atas judul, konsisten) -->
                <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-photo-film text-[120px]"></i></div>
                        <p class="dashboard-summary-title">Total Output</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ editorDashboardData.totalVideos }}</span>
                            <span class="dashboard-summary-unit">Videos</span>
                        </div>
                        <p class="text-body-sm font-bold text-amber mt-3">Dalam periode aktif</p>
                    </div>
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-eye text-[120px]"></i></div>
                        <p class="dashboard-summary-title">Total Reach</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ formatNumber(editorDashboardData.totalViews) }}</span>
                            <span class="dashboard-summary-unit">Views</span>
                        </div>
                        <p class="text-body-sm font-bold text-success mt-3">Total views gabungan</p>
                    </div>
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-trophy text-[120px]"></i></div>
                        <p class="dashboard-summary-title">Top Editor</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ editorDashboardData.leaderboard.length > 0 ? editorDashboardData.leaderboard[0].name : '-' }}</span>
                        </div>
                        <p class="text-body-sm font-bold text-amber mt-3">{{ editorDashboardData.leaderboard.length > 0 ? editorDashboardData.leaderboard[0].count + ' proyek | ' + formatNumber(editorDashboardData.leaderboard[0].views) + ' views' : 'Belum ada data' }}</p>
                    </div>
                </div>

                <!-- Header -->
                <section class="section-card section-card-body">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 md:gap-5">
                        <div class="modal-header-copy">
                            <div class="w-12 h-12 rounded-2xl bg-amber text-light flex items-center justify-center border border-amber">
                                <i class="fa-solid fa-clapperboard text-heading-lg"></i>
                            </div>
                            <div>
                                <h2 class="type-heading-sm font-bold text-slate-900">Editor Performance</h2>
                                <p class="text-body-sm text-amber uppercase tracking-widest font-bold mt-0.5">
                                    Creative Command | Live Metrics</p>
                            </div>
                        </div>
                        <div class="mobile-toolbar-stack">
                            <div class="compact-period-toolbar">
                                <div class="compact-period-toolbar__label">
                                    <i class="fa-regular fa-calendar text-body-sm"></i>
                                    <span>Periode</span>
                                </div>
                                <div class="compact-period-toolbar__controls">
                                    <div class="relative search-select-container">
                                        <button type="button" @click="toggleSearchSelect($event, 'editor_month')"
                                            class="select-trigger-button select-trigger-button-compact">
                                            <span>{{ monthOptionLabel(editorMonth) }}</span>
                                            <i class="fa-solid fa-chevron-down text-overline text-slate-400"></i>
                                        </button>
                                        <div v-if="searchSelectOpen === 'editor_month'" :style="popoverStyle"
                                            class="search-select-popover search-select-popover--compact max-h-72 overflow-y-auto">
                                            <div v-for="(name, idx) in monthNames" :key="'em'+idx"
                                                @click="editorMonth = idx + 1; searchSelectOpen = null"
                                                class="popover-option">
                                                {{ name }}</div>
                                        </div>
                                    </div>
                                    <div class="relative search-select-container">
                                        <button type="button" @click="toggleSearchSelect($event, 'editor_year')"
                                            class="select-trigger-button select-trigger-button-compact">
                                            <span>{{ editorYear }}</span>
                                            <i class="fa-solid fa-chevron-down text-overline text-slate-400"></i>
                                        </button>
                                        <div v-if="searchSelectOpen === 'editor_year'" :style="popoverStyle"
                                            class="search-select-popover search-select-popover--compact max-h-60 overflow-y-auto">
                                            <div v-for="y in availableYears" :key="'ey'+y"
                                                @click="editorYear = y; searchSelectOpen = null" class="popover-option">
                                                {{ y }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Leaderboard + Project Ledger -->
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
                    <!-- Leaderboard -->
                    <div class="lg:col-span-2 bg-white radius-panel border border-slate-100 p-5">
                        <h3
                            class="text-body font-bold text-slate-700 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-ranking-star text-ppp-accent"></i> Editor Ranking
                        </h3>
                        <div v-if="editorDashboardData.leaderboard.length === 0"
                            class="text-center py-10 text-body text-slate-400">Belum ada data</div>
                        <div v-for="(editor, idx) in editorDashboardData.leaderboard" :key="editor.name"
                            class="flex items-center gap-3 py-3 border-b border-slate-50 last:border-0">
                            <div
                                :class="['w-7 h-7 rounded-xl flex items-center justify-center text-body-sm font-extrabold shrink-0', idx === 0 ? 'bg-amber text-white' : idx === 1 ? 'bg-slate-300 text-white' : idx === 2 ? 'bg-amber text-white' : 'bg-secondary text-light']">
                                {{ idx + 1 }}</div>
                            <div class="flex-1 min-w-0">
                                <p class="text-body font-bold text-slate-800 uppercase truncate">{{ editor.name }}</p>
                                <p class="text-overline text-slate-400 font-bold">{{ editor.count }} proyek</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-body font-bold text-slate-700">{{ formatNumber(editor.views) }}</p>
                                <p class="text-overline-xs text-slate-400 font-bold uppercase">views</p>
                            </div>
                            <div
                                :class="['w-14 text-center px-1.5 py-0.5 rounded-lg text-overline font-bold shrink-0', editor.avgScore >= 80 ? 'bg-danger text-light' : editor.avgScore >= 50 ? 'bg-success text-light' : editor.avgScore >= 20 ? 'bg-amber text-light' : 'bg-secondary text-light']">
                                {{ editor.avgScore }}
                            </div>
                        </div>
                    </div>

                    <!-- Project Ledger -->
                    <div class="lg:col-span-3 section-card section-card-shell">
                        <div class="px-5 py-4 border-b border-slate-50 flex items-center justify-between">
                            <h3
                                class="text-body font-bold text-slate-700 uppercase tracking-widest flex items-center gap-2">
                                <i class="fa-solid fa-book-open text-ppp-accent"></i> Project Ledger
                            </h3>
                            <span class="text-overline text-slate-400 font-bold">{{ editorDashboardData.videoList.length }} proyek</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-body-sm text-left border-collapse min-w-[480px]">
                                <thead class="bg-slate-50">
                                    <tr class="table-header-row">
                                        <th class="table-header-cell">
                                            Tanggal</th>
                                        <th class="table-header-cell">
                                            Proyek</th>
                                        <th class="table-header-cell">
                                            Editor</th>
                                        <th class="table-header-cell text-right">
                                            Views</th>
                                        <th class="table-header-cell text-center">
                                            KPI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="pagedEditorRows.length === 0">
                                        <td colspan="5" class="px-5 py-12 text-center text-body text-slate-400">
                                            <i class="fa-solid fa-clapperboard text-3xl mb-3 opacity-20 block"></i>
                                            Belum ada data pada periode ini
                                        </td>
                                    </tr>
                                    <tr v-for="(video, idx) in pagedEditorRows" :key="video.ID || idx"
                                        class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                        <td class="px-5 py-3 text-body-sm text-slate-400 whitespace-nowrap">{{ formatShortDate(video.DisplayDate || video.Tanggal_Rencana) }}</td>
                                        <td class="px-5 py-3">
                                            <p
                                                class="text-body font-semibold text-slate-800 leading-tight line-clamp-2">
                                                {{ video.Judul }}</p>
                                            <div class="mt-1 flex items-center gap-2">
                                                <i :class="getPlatformIcon((video.Platforms || '').split(',')[0]) + ' text-body text-slate-400'"></i>
                                                <span class="text-body font-bold text-slate-700">{{ platformDisplayName(video.Platforms) }}</span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3 text-left">
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-overline font-bold text-slate-600 flex-shrink-0 overflow-hidden">
                                                    <img v-if="resolveUserAvatarUrl(video.Editor)"
                                                        :src="resolveAvatarUrl(resolveUserAvatarUrl(video.Editor))"
                                                        class="w-full h-full object-cover" alt="Foto Editor"
                                                        @error="markMasterPlanEditorAvatarFailed(video.Editor)" />
                                                    <span v-else>{{ masterPersonInitials(video.Editor) }}</span>
                                                </div>
                                                <div
                                                    class="text-body text-slate-700 font-semibold truncate max-w-[80px]">
                                                    {{ personDisplayName(video.Editor) }}</div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3 text-right text-body font-bold text-slate-700">{{ formatNumber(video.totalViews) }}</td>
                                        <td class="px-5 py-3 text-center">
                                            <span
                                                :class="['inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-overline font-bold', getVelocity(video).label === 'Viral' ? 'bg-danger text-light' : getVelocity(video).label === 'High' ? 'bg-success text-light' : getVelocity(video).label === 'Avg' ? 'bg-amber text-light' : getVelocity(video).label === 'New' ? 'bg-secondary text-light' : 'bg-secondary text-light']">
                                                <i :class="getVelocity(video).icon + ' text-overline-xs'"></i> {{ getVelocity(video).label }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div
                            class="px-5 py-3.5 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
                            <div class="text-body-sm text-slate-400 font-medium">
                                <template v-if="editorDashboardData.videoList.length > 0">{{ (editorPage - 1) * 15 + 1 }}-{{ Math.min(editorPage * 15, editorDashboardData.videoList.length) }} dari {{ editorDashboardData.videoList.length }}</template>
                                <template v-else>0 data</template>
                            </div>
                            <div class="flex items-center gap-1">
                                <button @click="editorPage--" :disabled="editorPage <= 1"
                                    aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-left text-body-sm"></i></button>
                                <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ editorPage }} / {{ editorTotalPages }}</span>
                                <button @click="editorPage++" :disabled="editorPage >= editorTotalPages"
                                    aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-right text-body-sm"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
@endverbatim
