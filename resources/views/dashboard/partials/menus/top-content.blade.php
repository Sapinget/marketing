@verbatim
<!-- Top Konten View -->
                    <div v-if="activeTab === 'top_content_platform'" class="space-y-6 animate-fadeIn pb-10">
                        <section class="section-card section-card-body">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 md:gap-5">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                                        <i class="fa-solid fa-trophy text-lg"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-semibold text-slate-900">Top Konten</h2>
                                        <p class="type-body text-slate-500">Top 5 konten per platform berdasarkan
                                            views terbanyak</p>
                                    </div>
                                </div>
                                <div class="mobile-toolbar-stack">
                                    <button @click="openCalendar($event, 'filter')" class="date-trigger-button date-trigger-button-compact">
                                        <i class="fa-solid fa-calendar-days text-[10px] text-slate-400"></i>
                                        <template v-if="commonDateFilter.start">
                                            {{ formatShortDate(commonDateFilter.start) }}
                                            <span v-if="commonDateFilter.end"> - {{ formatShortDate(commonDateFilter.end) }}</span>
                                        </template>
                                        <template v-else>Semua Tanggal</template>
                                        <i v-if="commonDateFilter.start"
                                            @click.stop="commonDateFilter = { start: '', end: '' }"
                                            class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-red-500"></i>
                                    </button>
                                    <div class="toolbar-actions">
                                        <button @click="exportExcel"
                                            class="secondary-cta-button secondary-cta-success active:scale-95"><i
                                                class="fa-solid fa-file-excel"></i><span class="ml-1">Excel</span></button>
                                        <button @click="exportPdf"
                                            class="secondary-cta-button secondary-cta-danger active:scale-95"><i
                                                class="fa-solid fa-file-pdf"></i><span class="ml-1">PDF</span></button>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <div v-if="topContentByPlatform.length === 0"
                            class="bg-white radius-panel border border-dashed border-slate-200 p-20 flex flex-col items-center justify-center text-slate-400">
                            <i class="fa-solid fa-trophy text-4xl mb-4 opacity-20"></i>
                            <p class="text-[11px] font-bold uppercase tracking-widest">Tidak ada data analytics pada
                                periode ini</p>
                        </div>

                        <div v-else class="space-y-4">
                            <!-- Gabungan -->
                            <div class="md:hidden space-y-3">
                                <div v-for="(row, idx) in topContentCombined" :key="'top-mobile-' + idx"
                                    class="stat-card mobile-record-card mobile-data-card animate-fadeIn">
                                    <div class="mobile-data-card__header">
                                        <span
                                            class="px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider bg-yellow-100 text-yellow-700">#{{ idx + 1 }}</span>
                                        <span class="type-meta text-slate-400 font-bold uppercase tracking-widest">{{ row.platform }}</span>
                                    </div>
                                    <div>
                                        <p class="mobile-data-card__title line-clamp-2">{{ row.title }}</p>
                                        <div class="mobile-data-card__meta mt-2">
                                            <span
                                                class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-[9px] font-bold uppercase">{{ row.editor }}</span>
                                        </div>
                                    </div>
                                    <div class="mobile-data-card__summary">
                                        <div>
                                            <div class="type-meta text-slate-400 uppercase">Views</div>
                                            <div class="type-body font-bold text-ppp-accent">{{ formatNumber(row.views) }}</div>
                                        </div>
                                        <div>
                                            <div class="type-meta text-slate-400 uppercase">Tanggal</div>
                                            <div class="type-body font-bold text-slate-700">{{ formatShortDate(row.date) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="hidden md:block section-card section-card-shell">
                                <div class="px-6 py-4 border-b border-slate-50 flex items-center justify-between">
                                    <h3 class="type-title font-semibold text-slate-900">Gabungan Semua Platform</h3>
                                    <span class="type-meta text-slate-400">{{ topContentCombined.length }}
                                        konten</span>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-[10.5px] text-left border-collapse min-w-[640px]">
                                        <thead>
                                            <tr
                                                class="text-[10px] font-bold uppercase tracking-widest text-slate-400 border-b border-slate-50">
                                                <th class="px-6 py-3 text-center w-10">#</th>
                                                <th class="px-6 py-3">Judul</th>
                                                <th class="px-6 py-3 w-24">Platform</th>
                                                <th class="px-6 py-3 w-24">Editor</th>
                                                <th class="px-6 py-3 text-right w-24">Views</th>
                                                <th class="px-6 py-3 text-right w-20">Likes</th>
                                                <th class="px-6 py-3 text-right w-24">Comments</th>
                                                <th class="px-6 py-3 text-right w-20">Shares</th>
                                                <th class="px-6 py-3 text-center w-24">Tanggal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-50">
                                            <tr v-for="(row, idx) in topContentCombined" :key="'tc_' + idx"
                                                class="hover:bg-slate-50/50 transition-colors">
                                                <td class="px-6 py-3 text-center">
                                                    <span
                                                        :class="['inline-flex items-center justify-center w-6 h-6 rounded-full text-[10px] font-bold', idx === 0 ? 'bg-yellow-100 text-yellow-600' : idx === 1 ? 'bg-slate-100 text-slate-500' : idx === 2 ? 'bg-amber-50 text-amber-600' : 'text-slate-400']">{{ idx + 1 }}</span>
                                                </td>
                                                <td
                                                    class="px-6 py-3 text-[11px] font-semibold text-slate-800 max-w-[200px] truncate">
                                                    {{ row.title }}</td>
                                                <td class="px-6 py-3"><span
                                                        class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-600 text-[10px] font-bold">{{ row.platform }}</span></td>
                                                <td class="px-6 py-3 text-[11px] text-slate-600">{{ row.editor }}</td>
                                                <td
                                                    class="px-6 py-3 text-right text-[11px] font-semibold text-ppp-accent">
                                                    {{ formatNumber(row.views) }}</td>
                                                <td class="px-6 py-3 text-right text-[11px] text-slate-600">{{ formatNumber(row.likes) }}</td>
                                                <td class="px-6 py-3 text-right text-[11px] text-slate-600">{{ formatNumber(row.comments) }}</td>
                                                <td class="px-6 py-3 text-right text-[11px] text-slate-600">{{ formatNumber(row.shares) }}</td>
                                                <td class="px-6 py-3 text-center text-[11px] text-slate-400">{{ formatShortDate(row.date) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Per Platform -->
                            <div v-for="group in topContentByPlatform" :key="group.platform"
                                class="hidden md:block section-card section-card-shell">
                                <div class="px-6 py-4 border-b border-slate-50 flex items-center justify-between">
                                    <h3 class="type-title font-semibold text-slate-900">{{ group.platform }}</h3>
                                    <span class="type-meta text-slate-400">{{ group.rows.length }} konten</span>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-[10.5px] text-left border-collapse min-w-[580px]">
                                        <thead>
                                            <tr
                                                class="text-[10px] font-bold uppercase tracking-widest text-slate-400 border-b border-slate-50">
                                                <th class="px-6 py-3 text-center w-10">#</th>
                                                <th class="px-6 py-3">Judul</th>
                                                <th class="px-6 py-3 w-24">Editor</th>
                                                <th class="px-6 py-3 text-right w-24">Views</th>
                                                <th class="px-6 py-3 text-right w-20">Likes</th>
                                                <th class="px-6 py-3 text-right w-24">Comments</th>
                                                <th class="px-6 py-3 text-right w-20">Shares</th>
                                                <th class="px-6 py-3 text-center w-24">Tanggal</th>
                                                <th class="px-6 py-3 text-center w-16">Drive</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-50">
                                            <tr v-for="(row, idx) in group.rows" :key="group.platform + '_' + idx"
                                                class="hover:bg-slate-50/50 transition-colors">
                                                <td class="px-6 py-3 text-center">
                                                    <span
                                                        :class="['inline-flex items-center justify-center w-6 h-6 rounded-full text-[10px] font-bold', idx === 0 ? 'bg-yellow-100 text-yellow-600' : idx === 1 ? 'bg-slate-100 text-slate-500' : idx === 2 ? 'bg-amber-50 text-amber-600' : 'text-slate-400']">{{ idx + 1 }}</span>
                                                </td>
                                                <td
                                                    class="px-6 py-3 text-[11px] font-semibold text-slate-800 max-w-[200px] truncate">
                                                    {{ row.title }}</td>
                                                <td class="px-6 py-3 text-[11px] text-slate-600">{{ row.editor }}</td>
                                                <td
                                                    class="px-6 py-3 text-right text-[11px] font-semibold text-ppp-accent">
                                                    {{ formatNumber(row.views) }}</td>
                                                <td class="px-6 py-3 text-right text-[11px] text-slate-600">{{ formatNumber(row.likes) }}</td>
                                                <td class="px-6 py-3 text-right text-[11px] text-slate-600">{{ formatNumber(row.comments) }}</td>
                                                <td class="px-6 py-3 text-right text-[11px] text-slate-600">{{ formatNumber(row.shares) }}</td>
                                                <td class="px-6 py-3 text-center text-[11px] text-slate-400">{{ formatShortDate(row.date) }}</td>
                                                <td class="px-6 py-3 text-center">
                                                    <a v-if="row.driveLink" :href="row.driveLink" target="_blank" rel="noopener noreferrer"
                                                        class="w-7 h-7 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center hover:bg-blue-500 hover:text-white transition-all mx-auto">
                                                        <i class="fa-solid fa-folder-open text-[10px]"></i>
                                                    </a>
                                                    <span v-else class="text-slate-300 text-[10px]">-</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
@endverbatim
