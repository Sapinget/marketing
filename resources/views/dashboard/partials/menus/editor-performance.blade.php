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
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div v-for="i in 4" :key="'sk-ep-st'+i"
                            class="bg-slate-50 radius-card p-4 border border-slate-100">
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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i class="fa-solid fa-photo-film text-[120px]"></i></div>
                        <p class="text-[9px] font-bold uppercase tracking-widest text-blue-500 mb-3">Total Output</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ editorDashboardData.totalVideos }}</span>
                            <span class="dashboard-summary-unit text-blue-400">Videos</span>
                        </div>
                        <p class="text-[10px] font-bold text-blue-600 mt-3">Dalam periode aktif</p>
                    </div>
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i class="fa-solid fa-eye text-[120px]"></i></div>
                        <p class="text-[9px] font-bold uppercase tracking-widest text-emerald-500 mb-3">Total Reach</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ formatNumber(editorDashboardData.totalViews) }}</span>
                            <span class="text-[14px] font-bold text-emerald-400 uppercase">Views</span>
                        </div>
                        <p class="text-[10px] font-bold text-emerald-600 mt-3">Total views gabungan</p>
                    </div>
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i class="fa-solid fa-trophy text-[120px]"></i></div>
                        <p class="text-[9px] font-bold uppercase tracking-widest text-amber-500 mb-3">Top Editor</p>
                        <div class="flex items-baseline gap-2">
                            <span class="text-[18px] font-bold text-slate-900 leading-none tracking-tight uppercase truncate">{{ editorDashboardData.leaderboard.length > 0 ? editorDashboardData.leaderboard[0].name : '-' }}</span>
                        </div>
                        <p class="text-[10px] font-bold text-amber-600 mt-3">{{ editorDashboardData.leaderboard.length > 0 ? editorDashboardData.leaderboard[0].count + ' proyek | ' + formatNumber(editorDashboardData.leaderboard[0].views) + ' views' : 'Belum ada data' }}</p>
                    </div>
                </div>

                <!-- Header -->
                <section class="section-card section-card-body">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 md:gap-5">
                        <div class="modal-header-copy">
                            <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center">
                                <i class="fa-solid fa-clapperboard text-[16px]"></i>
                            </div>
                            <div>
                                <h2 class="type-title font-bold text-slate-900">Editor Performance</h2>
                                <p class="text-[10px] text-blue-500 uppercase tracking-widest font-bold mt-0.5">
                                    Creative Command | Live Metrics</p>
                            </div>
                        </div>
                        <div class="mobile-toolbar-stack">
                            <div class="compact-period-toolbar">
                                <div class="compact-period-toolbar__label">
                                    <i class="fa-regular fa-calendar text-[10px]"></i>
                                    <span>Periode</span>
                                </div>
                                <div class="compact-period-toolbar__controls">
                                    <div class="relative search-select-container">
                                        <button type="button" @click="toggleSearchSelect($event, 'editor_month')"
                                            class="select-trigger-button select-trigger-button-compact">
                                            <span>{{ monthOptionLabel(editorMonth) }}</span>
                                            <i class="fa-solid fa-chevron-down text-[9px] text-slate-400"></i>
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
                                            <i class="fa-solid fa-chevron-down text-[9px] text-slate-400"></i>
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
                            class="text-[11px] font-bold text-slate-700 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-ranking-star text-ppp-accent"></i> Editor Ranking
                        </h3>
                        <div v-if="editorDashboardData.leaderboard.length === 0"
                            class="text-center py-10 text-[11px] text-slate-400">Belum ada data</div>
                        <div v-for="(editor, idx) in editorDashboardData.leaderboard" :key="editor.name"
                            class="flex items-center gap-3 py-3 border-b border-slate-50 last:border-0">
                            <div
                                :class="['w-7 h-7 rounded-xl flex items-center justify-center text-[10px] font-extrabold shrink-0', idx === 0 ? 'bg-amber-400 text-white' : idx === 1 ? 'bg-slate-300 text-white' : idx === 2 ? 'bg-orange-400 text-white' : 'bg-slate-100 text-slate-500']">
                                {{ idx + 1 }}</div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[11px] font-bold text-slate-800 uppercase truncate">{{ editor.name }}</p>
                                <p class="text-[9px] text-slate-400 font-bold">{{ editor.count }} proyek</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-[11px] font-bold text-slate-700">{{ formatNumber(editor.views) }}</p>
                                <p class="text-[8px] text-slate-400 font-bold uppercase">views</p>
                            </div>
                            <div
                                :class="['w-14 text-center px-1.5 py-0.5 rounded-lg text-[9px] font-bold shrink-0', editor.avgScore >= 80 ? 'bg-red-100 text-red-600' : editor.avgScore >= 50 ? 'bg-emerald-100 text-emerald-600' : editor.avgScore >= 20 ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-500']">
                                {{ editor.avgScore }}
                            </div>
                        </div>
                    </div>

                    <!-- Project Ledger -->
                    <div class="lg:col-span-3 section-card section-card-shell">
                        <div class="px-5 py-4 border-b border-slate-50 flex items-center justify-between">
                            <h3
                                class="text-[11px] font-bold text-slate-700 uppercase tracking-widest flex items-center gap-2">
                                <i class="fa-solid fa-book-open text-ppp-accent"></i> Project Ledger
                            </h3>
                            <span class="text-[9px] text-slate-400 font-bold">{{ editorDashboardData.videoList.length }} proyek</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-[10.5px] text-left border-collapse" style="min-width:480px">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th
                                            class="px-5 py-3 text-[9px] uppercase tracking-widest text-slate-400 font-bold">
                                            Tanggal</th>
                                        <th
                                            class="px-5 py-3 text-[9px] uppercase tracking-widest text-slate-400 font-bold">
                                            Proyek</th>
                                        <th
                                            class="px-5 py-3 text-[9px] uppercase tracking-widest text-slate-400 font-bold">
                                            Editor</th>
                                        <th
                                            class="px-5 py-3 text-[9px] uppercase tracking-widest text-slate-400 font-bold text-right">
                                            Views</th>
                                        <th
                                            class="px-5 py-3 text-[9px] uppercase tracking-widest text-slate-400 font-bold text-center">
                                            KPI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="pagedEditorRows.length === 0">
                                        <td colspan="5" class="px-5 py-12 text-center text-[11px] text-slate-400">
                                            <i class="fa-solid fa-clapperboard text-3xl mb-3 opacity-20 block"></i>
                                            Belum ada data pada periode ini
                                        </td>
                                    </tr>
                                    <tr v-for="(video, idx) in pagedEditorRows" :key="video.ID || idx"
                                        class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                        <td class="px-5 py-3 text-[10px] text-slate-400 whitespace-nowrap">{{ formatShortDate(video.DisplayDate || video.Tanggal_Rencana) }}</td>
                                        <td class="px-5 py-3">
                                            <p
                                                class="text-[11px] font-semibold text-slate-800 leading-tight line-clamp-2">
                                                {{ video.Judul }}</p>
                                            <p class="text-[9px] text-slate-400 mt-0.5">{{ video.Platforms }}
                                            </p>
                                        </td>
                                        <td class="px-5 py-3 text-[11px] font-bold text-slate-600 uppercase">{{ video.Editor }}</td>
                                        <td class="px-5 py-3 text-right text-[11px] font-bold text-slate-700">{{ formatNumber(video.totalViews) }}</td>
                                        <td class="px-5 py-3 text-center">
                                            <span
                                                :class="['inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[9px] font-bold', getVelocity(video).label === 'Viral' ? 'bg-red-100 text-red-600' : getVelocity(video).label === 'High' ? 'bg-emerald-100 text-emerald-600' : getVelocity(video).label === 'Avg' ? 'bg-amber-100 text-amber-600' : getVelocity(video).label === 'New' ? 'bg-slate-100 text-slate-500' : 'bg-violet-100 text-violet-600']">
                                                <i :class="getVelocity(video).icon + ' text-[8px]'"></i> {{ getVelocity(video).label }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div
                            class="px-5 py-3.5 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
                            <div class="text-[10px] text-slate-400 font-medium">
                                <template v-if="editorDashboardData.videoList.length > 0">{{ (editorPage - 1) * 15 + 1 }}-{{ Math.min(editorPage * 15, editorDashboardData.videoList.length) }} dari {{ editorDashboardData.videoList.length }}</template>
                                <template v-else>0 data</template>
                            </div>
                            <div class="flex items-center gap-1">
                                <button @click="editorPage--" :disabled="editorPage <= 1"
                                    aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-left text-[10px]"></i></button>
                                <span class="px-3 text-[10px] font-bold text-ppp-accent">{{ editorPage }} / {{ editorTotalPages }}</span>
                                <button @click="editorPage++" :disabled="editorPage >= editorTotalPages"
                                    aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-right text-[10px]"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- SELL OUT TAB -->
            <div v-if="activeTab === 'sell_out' && !tabDataLoaded['sellOut']"
                class="space-y-6 animate-fadeIn pb-10 animate-pulse">
                <div class="section-card section-card-body">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-slate-200"></div>
                            <div class="space-y-2">
                                <div class="h-5 bg-slate-200 rounded-full w-40"></div>
                                <div class="h-3 bg-slate-100 rounded-full w-56"></div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <div class="h-9 w-24 bg-slate-100 rounded-xl"></div>
                            <div class="h-9 w-28 bg-slate-200 rounded-xl"></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="h-9 flex-1 bg-slate-100 rounded-xl"></div>
                        <div class="h-9 w-28 bg-slate-100 rounded-xl"></div>
                    </div>
                </div>
                <div class="section-card section-card-shell">
                    <div class="px-6 py-4 border-b border-slate-50 flex gap-6">
                        <div class="h-3 bg-slate-200 rounded-full w-28"></div>
                        <div class="h-3 bg-slate-200 rounded-full w-20"></div>
                        <div class="h-3 bg-slate-200 rounded-full w-24"></div>
                        <div class="h-3 bg-slate-200 rounded-full w-20"></div>
                        <div class="h-3 bg-slate-200 rounded-full flex-1"></div>
                    </div>
                    <div class="divide-y divide-slate-50">
                        <div v-for="i in 8" :key="'sk-so'+i" class="px-6 py-5 flex items-center gap-4">
                            <div class="h-4 bg-slate-100 rounded-full w-36"></div>
                            <div class="h-4 bg-slate-100 rounded-full w-16"></div>
                            <div class="w-20 h-2 bg-slate-100 rounded-full"></div>
                            <div class="h-4 bg-slate-100 rounded-full w-8"></div>
                            <div class="h-6 bg-slate-100 rounded-lg w-16"></div>
                            <div class="h-4 bg-slate-100 rounded-full flex-1"></div>
                            <div class="flex gap-1">
                                <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                                <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="activeTab === 'sell_out' && tabDataLoaded['sellOut']" class="space-y-6 animate-fadeIn pb-10">

                <!-- Summary Cards (di atas judul, konsisten) -->
                <div class="dashboard-summary-grid-compact grid grid-cols-3 md:grid-cols-4 gap-3 md:gap-4">
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i class="fa-solid fa-bullseye text-[120px]"></i></div>
                        <p class="text-[9px] font-bold uppercase tracking-widest text-blue-500 mb-3">Total Target</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ sellOutSummary.totalTargets }}</span>
                            <span class="dashboard-summary-unit text-blue-400">Program</span>
                        </div>
                        <p class="text-[10px] font-bold text-blue-600 mt-3">Program aktif</p>
                    </div>
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i class="fa-solid fa-check-double text-[120px]"></i></div>
                        <p class="text-[9px] font-bold uppercase tracking-widest text-emerald-500 mb-3">Tercapai</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ sellOutSummary.achieved }}</span>
                            <span class="text-[14px] font-bold text-emerald-400 uppercase">Target</span>
                        </div>
                        <p class="text-[10px] font-bold text-emerald-600 mt-3">Dari {{ sellOutSummary.totalTargets }} target</p>
                    </div>
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i class="fa-solid fa-box text-[120px]"></i></div>
                        <p class="text-[9px] font-bold uppercase tracking-widest text-violet-500 mb-3">Total Realisasi</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ formatNumber(sellOutSummary.totalQty) }}</span>
                            <span class="text-[14px] font-bold text-violet-400 uppercase">Unit</span>
                        </div>
                        <p class="text-[10px] font-bold text-violet-600 mt-3">Unit terjual</p>
                    </div>
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i class="fa-solid fa-sack-dollar text-[120px]"></i></div>
                        <p class="text-[9px] font-bold uppercase tracking-widest text-amber-500 mb-3">Total Bonus</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ formatCurrency(sellOutSummary.totalBonus) }}</span>
                        </div>
                        <p class="text-[10px] font-bold text-amber-600 mt-3">Estimasi payout</p>
                    </div>
                </div>

                <!-- Header -->
                <section class="section-card section-card-body">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 md:gap-5">
                        <div class="modal-header-copy">
                            <div
                                class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center">
                                <i class="fa-solid fa-arrow-trend-up text-[16px]"></i>
                            </div>
                            <div>
                                <h2 class="type-title font-bold text-slate-900">Sell Out Target</h2>
                                <p class="text-[10px] text-slate-400 uppercase tracking-widest mt-0.5">Target &
                                    Realisasi Penjualan Vendor</p>
                            </div>
                        </div>
                        <div class="mobile-toolbar-stack">
                            <div class="relative group search-select-container">
                                <button @click="toggleSearchSelect($event, 'sellOutVendor')"
                                    class="select-trigger-button toolbar-trigger-field">
                                    <i class="fa-solid fa-building text-[10px] text-slate-400"></i>
                                    <span class="truncate">{{ sellOutVendorFilter || 'Semua Vendor' }}</span>
                                    <i v-if="sellOutVendorFilter" @click.stop="sellOutVendorFilter = ''"
                                        class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-red-500"></i>
                                    <i v-else class="fa-solid fa-chevron-down ml-auto text-[9px] text-slate-400"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'sellOutVendor'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div @click="sellOutVendorFilter = ''; searchSelectOpen = null"
                                                :class="['popover-option', !sellOutVendorFilter ? 'popover-option-active' : '']">
                                                Semua Vendor</div>
                                            <div v-for="v in sellOutVendorOptions" :key="v"
                                                @click="sellOutVendorFilter = v; sellOutPage = 1; searchSelectOpen = null"
                                                :class="['popover-option', sellOutVendorFilter === v ? 'popover-option-active' : '']">
                                                {{ v }}</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative group search-select-container">
                                <button @click="toggleSearchSelect($event, 'sellOutMonth')"
                                    class="select-trigger-button toolbar-trigger-field">
                                    <i class="fa-solid fa-calendar text-[10px] text-slate-400"></i>
                                    <span class="truncate">{{ formatMonthLabel(sellOutMonth) || 'Semua Bulan' }}</span>
                                    <i v-if="sellOutMonth" @click.stop="sellOutMonth = ''"
                                        class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-red-500"></i>
                                    <i v-else class="fa-solid fa-chevron-down ml-auto text-[9px] text-slate-400"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'sellOutMonth'" :style="popoverStyle"
                                        class="search-select-popover w-48">
                                        <div class="max-h-64 overflow-y-auto custom-scrollbar p-1">
                                            <div @click="sellOutMonth = ''; searchSelectOpen = null"
                                                :class="['popover-option mb-1', !sellOutMonth ? 'popover-option-active' : '']">
                                                Semua Bulan</div>
                                            <div v-for="m in last12Months" :key="m.value"
                                                @click="sellOutMonth = m.value; sellOutPage = 1; searchSelectOpen = null"
                                                :class="['popover-option mb-1', sellOutMonth === m.value ? 'popover-option-active' : '']">
                                                {{ m.label }}</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative flex-1 sm:w-48">
                                <i
                                    class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                                <input v-model="sellOutSearch" type="text" placeholder="Cari vendor / produk..."
                                    class="form-input-search" />
                            </div>
                            <div class="toolbar-actions">
                                <button @click="openSellOutModal('create')"
                                    class="primary-cta-button primary-cta-button--accent active:scale-95"><i
                                        class="fa-solid fa-plus"></i> Tambah</button>
                                <button @click="exportSellOutToExcel"
                                    class="secondary-cta-button secondary-cta-success active:scale-95"><i
                                        class="fa-solid fa-file-excel"></i><span
                                        class="ml-1">Excel</span></button>
                                <button @click="exportSellOutToPDF"
                                    class="secondary-cta-button secondary-cta-danger active:scale-95"><i
                                        class="fa-solid fa-file-pdf"></i><span
                                        class="ml-1">PDF</span></button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Table Desktop -->
                <div class="hidden md:block section-card section-card-shell">
                    <table class="w-full text-[10.5px]">
                        <thead>
                            <tr>
                                <th
                                    class="px-5 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100 w-20 text-left">
                                    Aksi</th>
                                <th
                                    class="px-5 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100 w-10 text-center">
                                    #</th>
                                <th
                                    class="px-5 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100">
                                    Vendor</th>
                                <th
                                    class="px-5 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100">
                                    Nama Produk</th>
                                <th
                                    class="px-5 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100 text-center">
                                    Target</th>
                                <th
                                    class="px-5 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100 text-center">
                                    Terjual</th>
                                <th
                                    class="px-5 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100 text-center">
                                    Progres</th>
                                <th
                                    class="px-5 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100 text-center">
                                    Status</th>
                                <th
                                    class="px-5 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100 text-right">
                                    Bonus/Unit</th>
                                <th
                                    class="px-5 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100 text-right">
                                    Total Bonus</th>
                                <th
                                    class="px-5 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100 text-center">
                                    Periode</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="pagedSellOutData.length === 0">
                                <td colspan="12" class="px-5 py-16 text-center text-[11px] text-slate-400">
                                    <i class="fa-solid fa-arrow-trend-up text-3xl mb-3 opacity-20 block"></i>
                                    Belum ada data sell out target
                                </td>
                            </tr>
                            <tr v-for="(row, idx) in pagedSellOutData" :key="row.ID || idx"
                                class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors group">
                                <td class="px-5 py-3.5 text-left">
                                    <div class="flex items-center gap-1.5">
                                        <button @click="openSellOutModal('edit', row)"
                                            class="table-action-button table-action-compact" title="Edit"
                                            aria-label="Edit"><i class="fa-solid fa-pen text-[9px]"></i></button>
                                        <button @click="deleteSellOut(row.ID)"
                                            class="table-action-button table-action-compact table-action-danger"
                                            title="Hapus" aria-label="Hapus"><i
                                                class="fa-solid fa-trash text-[9px]"></i></button>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-[10px] text-slate-400 text-center">{{ (sellOutPage - 1) * 20 + idx + 1 }}</td>
                                <td class="px-5 py-3.5 text-[11px] font-bold text-slate-700">{{ row.Vendor || '-' }}</td>
                                <td class="px-5 py-3.5">
                                    <p class="text-[11px] font-semibold text-slate-800">{{ row.Nama_Produk || row.Seri || '-' }}</p>
                                    <p v-if="row.Catatan" class="text-[9px] text-slate-400 mt-0.5">{{ row.Catatan }}</p>
                                </td>
                                <td class="px-5 py-3.5 text-center text-[11px] font-bold text-slate-700">{{ formatNumber(row.Target_Unit) }}</td>
                                <td class="px-5 py-3.5 text-center text-[11px] font-bold text-blue-600">{{ formatNumber(row.Realisasi_Unit || 0) }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <div class="flex items-center gap-1.5 justify-center">
                                        <div class="w-20 h-2 bg-slate-100 rounded-full overflow-hidden">
                                            <div :style="`width:${getSellOutProgress(row).pct}%`"
                                                :class="['h-full rounded-full transition-all', getSellOutProgress(row).achieved ? 'bg-emerald-500' : 'bg-amber-400']">
                                            </div>
                                        </div>
                                        <span class="text-[9px] font-bold text-slate-500">{{ getSellOutProgress(row).pct }}%</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span :class="['px-2.5 py-1 rounded-lg text-[9px] font-bold uppercase', 
                                                    getSellOutProgress(row).status === 'TERPAKAI' ? 'bg-emerald-100 text-emerald-700' : 
                                                    getSellOutProgress(row).status === 'TIDAK DIPAKAI' ? 'bg-blue-100 text-blue-700' :
                                                    getSellOutProgress(row).status === 'PROGRESS' ? 'bg-amber-100 text-amber-700' : 
                                                    'bg-slate-100 text-slate-500']">
                                        {{ getSellOutProgress(row).status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right text-[11px] text-slate-600">{{ row.Bonus_Nominal ? formatCurrency(row.Bonus_Nominal) : '-' }}</td>
                                <td class="px-5 py-3.5 text-right text-[11px] font-bold text-emerald-600">{{ getSellOutProgress(row).bonusTotal > 0 ? formatCurrency(getSellOutProgress(row).bonusTotal) : '-' }}</td>
                                <td class="px-5 py-3.5 text-center text-[10px] text-slate-400">
                                    <template v-if="row.Periode_Start">{{ formatShortDate(row.Periode_Start) }}</template>
                                    <template v-if="row.Periode_Start && row.Periode_End"> - </template>
                                    <template v-if="row.Periode_End">{{ formatShortDate(row.Periode_End) }}</template>
                                    <template v-if="!row.Periode_Start && !row.Periode_End">-</template>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="table-pager-bar">
                        <div class="text-[10px] text-slate-400 font-medium">{{ filteredSellOutData.length }}
                            target</div>
                        <div class="flex items-center gap-1">
                            <button @click="sellOutPage--" :disabled="sellOutPage <= 1" aria-label="Halaman sebelumnya"
                                class="icon-utility-button icon-utility-bordered"><i
                                    class="fa-solid fa-chevron-left text-[10px]"></i></button>
                            <span class="px-3 text-[10px] font-bold text-ppp-accent">{{ sellOutPage }} / {{ sellOutTotalPages }}</span>
                            <button @click="sellOutPage++" :disabled="sellOutPage >= sellOutTotalPages"
                                aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered"><i
                                    class="fa-solid fa-chevron-right text-[10px]"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Mobile Cards -->
                <div class="md:hidden space-y-3">
                    <div v-if="filteredSellOutData.length === 0"
                        class="bg-white radius-panel border border-slate-100 p-10 text-center text-[11px] text-slate-400">
                        Belum ada data sell out</div>
                    <div v-for="(row, idx) in pagedSellOutData" :key="'smo'+idx"
                        class="stat-card mobile-record-card mobile-data-card motion-stagger-item"
                        :style="getStaggerStyle(idx)">
                        <div class="mobile-data-card__header">
                            <div>
                                <p class="mobile-data-card__title">{{ row.Nama_Produk || row.Seri || '-' }}</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase mt-0.5">{{ row.Vendor || '-' }}</p>
                            </div>
                            <span
                                :class="['px-2.5 py-1 rounded-lg text-[9px] font-bold uppercase shrink-0', getSellOutProgress(row).status === 'TERCAPAI' ? 'bg-emerald-100 text-emerald-700' : getSellOutProgress(row).status === 'PROGRESS' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500']">{{ getSellOutProgress(row).status }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div :style="`width:${getSellOutProgress(row).pct}%`"
                                    :class="['h-full rounded-full', getSellOutProgress(row).achieved ? 'bg-emerald-500' : 'bg-amber-400']">
                                </div>
                            </div>
                            <span class="text-[10px] font-bold text-slate-500 w-10 text-right">{{ getSellOutProgress(row).pct }}%</span>
                        </div>
                        <div class="mobile-data-card__summary">
                            <div class="bg-slate-50 rounded-xl p-2">
                                <p class="text-[9px] text-slate-400 font-bold uppercase">Target</p>
                                <p class="text-[12px] font-bold text-slate-800">{{ formatNumber(row.Target_Unit) }}</p>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-2">
                                <p class="text-[9px] text-slate-400 font-bold uppercase">Terjual</p>
                                <p class="text-[12px] font-bold text-blue-600">{{ formatNumber(row.Realisasi_Unit || 0) }}</p>
                            </div>
                        </div>
                        <div class="mobile-data-card__actions">
                            <p class="text-[9px] text-slate-400">{{ row.Periode_Start ? formatShortDate(row.Periode_Start) : '' }}{{ (row.Periode_Start && row.Periode_End) ? ' - ' : '' }}{{ row.Periode_End ? formatShortDate(row.Periode_End) : '' }}</p>
                            <div class="flex gap-1.5">
                                <button @click="openSellOutModal('edit', row)"
                                    class="table-action-button table-action-compact" title="Edit" aria-label="Edit"><i
                                        class="fa-solid fa-pen text-[10px]"></i></button>
                                <button @click="deleteSellOut(row.ID)"
                                    class="table-action-button table-action-compact table-action-danger" title="Hapus"
                                    aria-label="Hapus"><i class="fa-solid fa-trash text-[10px]"></i></button>
                            </div>
                        </div>
                    </div>
                    <!-- Mobile Pagination -->
                    <div class="flex items-center justify-center gap-2 py-2">
                        <button @click="sellOutPage--" :disabled="sellOutPage <= 1"
                            class="icon-utility-button icon-utility-bordered"><i
                                class="fa-solid fa-chevron-left text-[10px]"></i></button>
                        <span class="px-3 text-[10px] font-bold text-ppp-accent">{{ sellOutPage }} / {{ sellOutTotalPages }}</span>
                        <button @click="sellOutPage++" :disabled="sellOutPage >= sellOutTotalPages"
                            class="icon-utility-button icon-utility-bordered"><i
                                class="fa-solid fa-chevron-right text-[10px]"></i></button>
                    </div>
                </div>

            </div>
@endverbatim
