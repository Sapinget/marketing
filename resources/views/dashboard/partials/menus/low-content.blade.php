@verbatim
<!-- Low Konten View -->
                    <div v-if="activeTab === 'low_content_platform'" class="space-y-4 animate-fadeIn">


                        <div v-if="lowContentByPlatform.length === 0"
                            class="bg-white radius-panel border border-dashed border-slate-200 p-20 flex flex-col items-center justify-center text-slate-400">
                            <i class="fa-solid fa-arrow-trend-down text-4xl mb-4 opacity-20"></i>
                            <p class="text-body font-bold uppercase">Tidak ada data pada periode ini
                            </p>
                        </div>

                        <div v-else class="space-y-4">
                            <!-- Gabungan -->
                            <div class="md:hidden space-y-3">
                                <div v-for="(row, idx) in lowContentCombined" :key="'low-mobile-' + idx"
                                    class="stat-card mobile-record-card mobile-data-card animate-fadeIn">
                                    <div class="mobile-data-card__header">
                                        <span
                                            class="px-2.5 py-1 rounded-full text-overline font-bold uppercase bg-danger text-light">#{{ idx + 1 }}</span>
                                        <div class="flex items-center gap-2">
                                            <i :class="getPlatformIcon(row.platform) + ' text-body text-slate-400'"></i>
                                            <span class="text-body font-bold text-slate-700">{{ platformDisplayName(row.platform) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="mobile-data-card__title line-clamp-2">{{ row.title }}</p>
                                        <div class="mobile-data-card__meta mt-2">
                                            <span
                                                class="px-2.5 py-1 rounded-lg bg-secondary text-light text-overline font-bold uppercase">{{ row.editor }}</span>
                                        </div>
                                    </div>
                                    <div class="mobile-data-card__summary">
                                        <div>
                                            <div class="type-body-sm text-slate-400 uppercase">Views</div>
                                            <div class="type-body font-bold text-danger">{{ formatNumber(row.views) }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="type-body-sm text-slate-400 uppercase">Tanggal</div>
                                            <div class="type-body font-bold text-slate-700">{{ formatShortDate(row.date) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="hidden md:block section-card section-card-shell">
                                <div class="table-toolbar-shell">
                                    <div class="table-toolbar-shell__left"></div>
                                    <div class="table-toolbar-shell__right">
                                        <button @click="openCalendar($event, 'filter')" class="select-trigger-button-compact">
                                            <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                            <template v-if="commonDateFilter.start">
                                                {{ formatShortDate(commonDateFilter.start) }}
                                                <span v-if="commonDateFilter.end"> - {{ formatShortDate(commonDateFilter.end) }}</span>
                                            </template>
                                            <template v-else>Semua Tanggal</template>
                                            <i v-if="commonDateFilter.start"
                                                @click.stop="commonDateFilter = { start: '', end: '' }"
                                                class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-danger"></i>
                                        </button>
                                        <div class="toolbar-actions toolbar-actions--desktop-icon-only">
                                            <button @click="exportExcel"
                                                class="primary-cta-button primary-cta-button--success primary-cta-button--icon-only active:scale-95" aria-label="Export Excel"><i
                                                    class="fa-solid fa-file-excel"></i></button>
                                            <button @click="exportPdf"
                                                class="primary-cta-button primary-cta-button--danger primary-cta-button--icon-only active:scale-95" aria-label="Export PDF"><i
                                                    class="fa-solid fa-file-pdf"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 px-6 py-3 border-b border-slate-50 overflow-x-auto">
                                    <button v-for="tab in lowContentTabs" :key="tab.key"
                                        @click="lowContentView = tab.key"
                                        :class="['toolbar-tab-button border-transparent', lowContentView === tab.key ? 'bg-ppp-accent text-light shadow-sm' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-50']">
                                        {{ tab.label }}
                                        <span class="ml-1.5 opacity-60">({{ tab.count }})</span>
                                    </button>
                                </div>
                                <div class="px-6 py-4 border-b border-slate-50 flex items-center justify-between">
                                    <h3 class="type-title font-semibold text-slate-900">
                                        <template v-if="lowContentView === 'all'">Gabungan Semua Platform</template>
                                        <template v-else>{{ lowContentActiveGroup ? platformDisplayName(lowContentActiveGroup.platform) : '' }}</template>
                                    </h3>
                                    <span class="type-body-sm text-slate-400">{{ lowContentActiveRows.length }} konten</span>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full table-fixed text-body-sm text-left border-collapse min-w-[960px]">
                                        <thead>
                                            <tr class="table-header-row">
                                                <th class="table-header-cell text-center w-10">#</th>
                                                <th class="table-header-cell w-[280px]">Judul</th>
                                                <th class="table-header-cell text-center w-28">
                                                    <template v-if="lowContentView === 'all'">Platform</template>
                                                    <template v-else>Distribution</template>
                                                </th>
                                                <th class="table-header-cell w-40">Editor</th>
                                                <th class="table-header-cell text-right w-24">Views</th>
                                                <th class="table-header-cell text-right w-20">Likes</th>
                                                <th class="table-header-cell text-right w-24">Comments</th>
                                                <th class="table-header-cell text-right w-20">Shares</th>
                                                <th class="table-header-cell text-center w-24">Tanggal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-50">
                                            <tr v-for="(row, idx) in lowContentActiveRows" :key="'lc_' + idx"
                                                class="hover:bg-slate-50/50 transition-colors">
                                                <td class="px-6 py-3 text-center text-body-sm font-bold text-slate-400 tabular-nums">{{ idx + 1 }}</td>
                                                <td class="px-6 py-3 text-body font-semibold text-slate-800 whitespace-normal break-words leading-snug">{{ row.title }}</td>
                                                <td class="px-6 py-3 text-center">
                                                    <template v-if="lowContentView === 'all'">
                                                        <div class="flex items-center justify-center gap-2">
                                                            <i :class="getPlatformIcon(row.platform) + ' text-body text-slate-400'"></i>
                                                            <span class="text-body font-bold text-slate-700">{{ platformDisplayName(row.platform) }}</span>
                                                        </div>
                                                    </template>
                                                    <template v-else>
                                                        <a v-if="row.distributionLink" :href="row.distributionLink" target="_blank" rel="noopener noreferrer"
                                                            class="table-action-button table-action-compact table-action-link"
                                                            title="Link Distribution" aria-label="Link Distribution">
                                                            <i class="fa-solid fa-link text-body-sm"></i>
                                                        </a>
                                                        <span v-else class="text-slate-300 text-body-sm">-</span>
                                                    </template>
                                                </td>
                                                <td class="px-6 py-3">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-overline font-bold text-slate-600 flex-shrink-0 overflow-hidden">
                                                            <img v-if="resolveUserAvatarUrl(row.editor)" :src="resolveAvatarUrl(resolveUserAvatarUrl(row.editor))"
                                                                class="w-full h-full object-cover" alt="Foto Editor"
                                                                @error="markMasterPlanEditorAvatarFailed(row.editor)" />
                                                            <span v-else>{{ masterPersonInitials(row.editor) }}</span>
                                                        </div>
                                                        <span class="text-body text-slate-700 font-semibold whitespace-normal break-words leading-snug">{{ personDisplayName(row.editor) }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-3 text-right text-body font-semibold text-danger">{{ formatNumber(row.views) }}</td>
                                                <td class="px-6 py-3 text-right text-body text-slate-600">{{ formatNumber(row.likes) }}</td>
                                                <td class="px-6 py-3 text-right text-body text-slate-600">{{ formatNumber(row.comments) }}</td>
                                                <td class="px-6 py-3 text-right text-body text-slate-600">{{ formatNumber(row.shares) }}</td>
                                                <td class="px-6 py-3 text-center text-body text-slate-400">{{ formatShortDate(row.date) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
@endverbatim
