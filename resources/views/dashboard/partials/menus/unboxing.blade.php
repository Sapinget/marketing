@verbatim
<!-- Unboxing View -->
                    <div v-if="activeTab === 'unboxing' && !tabDataLoaded['unboxing']"
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
                                <div class="h-3 bg-slate-200 rounded-full w-20"></div>
                                <div class="h-3 bg-slate-200 rounded-full w-28"></div>
                                <div class="h-3 bg-slate-200 rounded-full w-24"></div>
                                <div class="h-3 bg-slate-200 rounded-full flex-1"></div>
                                <div class="h-3 bg-slate-200 rounded-full w-16"></div>
                            </div>
                            <div class="divide-y divide-slate-50">
                                <div v-for="i in 8" :key="'sk-ub'+i" class="px-6 py-5 flex items-center gap-4">
                                    <div class="h-4 bg-slate-100 rounded-full w-44"></div>
                                    <div class="h-4 bg-slate-100 rounded-full w-20"></div>
                                    <div class="h-4 bg-slate-100 rounded-full w-24"></div>
                                    <div class="h-4 bg-slate-100 rounded-full flex-1"></div>
                                    <div class="h-6 bg-slate-100 rounded-full w-14"></div>
                                    <div class="flex gap-1">
                                        <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                                        <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="activeTab === 'unboxing' && tabDataLoaded['unboxing']"
                        class="space-y-6 animate-fadeIn pb-10">
                        <!-- Summary cards -->
                        <div class="space-y-3">
                            <div class="dashboard-summary-grid-compact grid grid-cols-3 md:grid-cols-4 gap-3 md:gap-4">
                                <div v-for="c in unboxingSummary.cards" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                                    <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i :class="['fa-solid', c.icon, 'text-[120px]']"></i></div>
                                    <p :class="['text-[9px] font-bold uppercase tracking-widest mb-3', c.color]">{{ c.label }}</p>
                                    <div class="flex items-baseline gap-2">
                                        <span class="dashboard-summary-value">{{ c.value }}</span>
                                        <span v-if="c.unit" :class="['dashboard-summary-unit', c.unitColor]">{{ c.unit }}</span>
                                    </div>
                                    <p :class="['text-[10px] font-bold mt-3', c.subColor]">{{ c.sub }}</p>
                                </div>
                            </div>
                        </div>

                        <section class="section-card section-card-body">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 md:gap-5">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center border border-amber-100">
                                        <i class="fa-solid fa-box-open text-lg"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-semibold text-slate-900">Unboxing</h2>
                                        <p class="type-body text-slate-500">Kelola data konten unboxing produk</p>
                                    </div>
                                </div>

                                <!-- Filters & Export -->
                                <div class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-3">
                                    <div class="relative flex-1 sm:w-64">
                                        <i
                                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                                        <input v-model="unboxingSearch" type="text" placeholder="Cari judul unboxing..."
                                            class="form-input-search" />
                                    </div>
                                    <div class="relative">
                                        <button @click="openCalendar($event, 'filter')" class="filter-trigger-button toolbar-trigger-field">
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
                                    </div>
                                    <div class="toolbar-actions">
                                        <button @click="openUnboxingModal('create')"
                                            class="primary-cta-button primary-cta-button--accent active:scale-95">
                                            <i class="fa-solid fa-plus mr-2"></i>Tambah
                                        </button>
                                        <button @click="exportExcel"
                                            class="secondary-cta-button secondary-cta-success active:scale-95"><i
                                                class="fa-solid fa-file-excel"></i><span
                                                class="ml-1">Excel</span></button>
                                        <button @click="exportPdf"
                                            class="secondary-cta-button secondary-cta-danger active:scale-95"><i
                                                class="fa-solid fa-file-pdf"></i><span
                                                class="ml-1">PDF</span></button>
                                    </div>
                                </div>
                            </div>
                            <div v-if="unboxingSummary.chips.length" class="chips-grid mt-4 pt-4 border-t border-slate-100">
                                <span v-for="ch in unboxingSummary.chips" :key="ch.label" :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold', ch.cls]">{{ ch.label }} <span class="opacity-50">&middot;</span> {{ ch.n }}</span>
                            </div>
                        </section>

                        <div class="md:hidden space-y-3">
                            <div v-if="filteredUnboxingData.length === 0"
                                class="bg-white radius-card border border-slate-100 p-10 text-center text-[11px] text-slate-400">
                                Belum ada data unboxing
                            </div>
                            <div v-for="(row, idx) in pagedUnboxingData" :key="'ub-mobile-' + row.ID"
                                class="stat-card mobile-record-card mobile-data-card motion-stagger-item"
                                :style="getStaggerStyle(idx)">
                                <div class="mobile-data-card__header">
                                    <span
                                        :class="['px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider', row.Status ? getStatusColor(row.Status) : 'bg-slate-100 text-slate-500']">
                                        {{ row.Status || '-' }}
                                    </span>
                                    <span class="type-meta text-slate-400 font-bold uppercase tracking-widest">
                                        {{ row.Upload_Date ? formatShortDate(row.Upload_Date) : '-' }}
                                    </span>
                                </div>
                                <div>
                                    <p class="mobile-data-card__title line-clamp-2">{{ row.Nama || '-' }}</p>
                                    <div class="mobile-data-card__meta mt-2">
                                        <span
                                            class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-[9px] font-bold uppercase">
                                            {{ row.Editor || '-' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mobile-data-card__actions">
                                    <a v-if="row.Link" :href="row.Link" target="_blank" rel="noopener noreferrer"
                                        class="secondary-cta-button secondary-cta-link">Link</a>
                                    <div class="flex items-center gap-2 ml-auto">
                                        <button @click="openUnboxingModal('edit', row)"
                                            class="table-action-button table-action-compact"><i
                                                class="fa-solid fa-pen text-[10px]"></i></button>
                                        <button @click="deleteUnboxing(row.ID)"
                                            class="table-action-button table-action-compact table-action-danger"><i
                                                class="fa-solid fa-trash text-[10px]"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-center gap-2 py-2">
                                <button @click="unboxingPage--" :disabled="unboxingPage <= 1"
                                    aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-left text-[10px]"></i></button>
                                <span class="px-3 text-[10px] font-bold text-ppp-accent">{{ unboxingPage }} / {{ unboxingTotalPages }}</span>
                                <button @click="unboxingPage++" :disabled="unboxingPage >= unboxingTotalPages"
                                    aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-right text-[10px]"></i></button>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="hidden md:block section-card section-card-shell">
                            <div class="overflow-x-auto">
                                <table class="w-full text-[10.5px] text-left border-collapse min-w-[640px]">
                                    <thead>
                                        <tr
                                            class="border-b border-slate-100 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                                            <th class="px-6 py-4 w-24">Aksi</th>
                                            <th class="px-6 py-4">Judul</th>
                                            <th class="px-6 py-4 w-32">Editor</th>
                                            <th class="px-6 py-4 text-center w-28">Status</th>
                                            <th class="px-6 py-4 text-center w-36">Tanggal Upload</th>
                                            <th class="px-6 py-4 text-center w-20">Link</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in pagedUnboxingData" :key="row.ID"
                                            class="border-b border-slate-50 hover:bg-slate-50 transition-colors group">
                                            <td class="px-6 py-3">
                                                <div class="flex items-center gap-2">
                                                    <button @click="openUnboxingModal('edit', row)"
                                                        class="table-action-button table-action-compact">
                                                        <i class="fa-solid fa-pen text-[10px]"></i>
                                                    </button>
                                                    <button @click="deleteUnboxing(row.ID)"
                                                        class="table-action-button table-action-compact table-action-danger">
                                                        <i class="fa-solid fa-trash text-[10px]"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-6 py-3">
                                                <p
                                                    class="text-[10.5px] font-semibold text-slate-900 uppercase tracking-tight">
                                                    {{ row.Nama }}</p>
                                            </td>
                                            <td class="px-6 py-3">
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">{{ row.Editor || '-' }}</span>
                                            </td>
                                            <td class="px-6 py-3 text-center">
                                                <span v-if="row.Status" :class="getStatusColor(row.Status)"
                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold whitespace-nowrap">{{ row.Status }}</span>
                                                <span v-else class="text-[11px] text-slate-400">-</span>
                                            </td>
                                            <td class="px-6 py-3 text-center text-[11px] text-slate-500">{{ formatShortDate(row.Upload_Date) }}</td>
                                            <td class="px-6 py-3 text-center">
                                                <a v-if="row.Link" :href="row.Link" target="_blank" rel="noopener noreferrer"
                                                    class="secondary-cta-button secondary-cta-link">
                                                    <i class="fa-solid fa-external-link-alt text-[10px]"></i> Buka Link
                                                </a>
                                                <span v-else class="text-slate-400 text-[11px]">-</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div
                                class="table-pager-bar">
                                <div class="text-[10px] text-slate-400 font-medium">
                                    <template v-if="filteredUnboxingData.length > 0">{{ (unboxingPage - 1) * 15 + 1 }}-{{ Math.min(unboxingPage * 15, filteredUnboxingData.length) }} dari {{ filteredUnboxingData.length }} data</template>
                                    <template v-else>0 data</template>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button @click="unboxingPage--" :disabled="unboxingPage <= 1"
                                        aria-label="Halaman sebelumnya"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-left text-[10px]"></i></button>
                                    <span class="px-3 text-[10px] font-bold text-ppp-accent">{{ unboxingPage }} / {{ unboxingTotalPages }}</span>
                                    <button @click="unboxingPage++" :disabled="unboxingPage >= unboxingTotalPages"
                                        aria-label="Halaman berikutnya"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-right text-[10px]"></i></button>
                                </div>
                            </div>

                            <!-- Empty state -->
                            <div v-if="unboxingData.length === 0"
                                class="flex flex-col items-center justify-center py-20 text-slate-400">
                                <i class="fa-solid fa-box-open text-4xl mb-4 opacity-20"></i>
                                <p class="text-[11px] font-bold uppercase tracking-widest">Belum ada data unboxing</p>
                            </div>
                        </div>
                    </div>
@endverbatim
