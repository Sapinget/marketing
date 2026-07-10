@verbatim
<!-- Unit Ditanya View -->
                    <div v-if="activeTab === 'unit_ditanya' && !tabDataLoaded['unitDitanya']"
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
                                <div class="h-9 w-28 bg-slate-200 rounded-xl"></div>
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
                            </div>
                            <div class="divide-y divide-slate-50">
                                <div v-for="i in 8" :key="'sk-ud'+i" class="px-6 py-5 flex items-center gap-4">
                                    <div class="h-4 bg-slate-100 rounded-full w-44"></div>
                                    <div class="h-4 bg-slate-100 rounded-full w-20"></div>
                                    <div class="h-4 bg-slate-100 rounded-full w-28"></div>
                                    <div class="h-4 bg-slate-100 rounded-full flex-1"></div>
                                    <div class="flex gap-1">
                                        <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                                        <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="activeTab === 'unit_ditanya' && tabDataLoaded['unitDitanya']"
                        class="space-y-6 animate-fadeIn pb-10">
                        <!-- Summary cards -->
                        <div class="space-y-3">
                            <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                                <div v-for="c in unitDitanyaSummary.cards" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
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
                                        class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                                        <i class="fa-solid fa-circle-question text-lg"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-semibold text-slate-900">Unit Ditanya</h2>
                                        <p class="type-body text-slate-500">Unit yang sering ditanyakan pelanggan</p>
                                    </div>
                                </div>
                                <div class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-3">
                                    <div class="relative flex-1 sm:w-56">
                                        <i
                                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                                        <input id="unit-ditanya-search" name="unit_ditanya_search" v-model="unitDitanyaSearch" type="text"
                                            autocomplete="off" aria-label="Cari unit ditanya"
                                            placeholder="Cari brand / seri / tipe..." class="form-input-search" />
                                    </div>
                                    <div class="relative group search-select-container">
                                        <button @click="openCalendar($event, 'filter', '', 'unitDitanya')"
                                            class="date-trigger-button date-trigger-button-compact">
                                            <i class="fa-solid fa-calendar-days text-[10px] text-slate-400"></i>
                                            <template v-if="unitDitanyaDateRange.start">
                                                {{ formatShortDate(unitDitanyaDateRange.start) }}
                                                <span v-if="unitDitanyaDateRange.end"> - {{ formatShortDate(unitDitanyaDateRange.end) }}</span>
                                            </template>
                                            <template v-else>Semua Tanggal</template>
                                            <i v-if="unitDitanyaDateRange.start"
                                                @click.stop="unitDitanyaDateRange = { start: '', end: '' }"
                                                class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-red-500"></i>
                                        </button>
                                    </div>
                                    <div class="relative search-select-container">
                                        <button @click="toggleSearchSelect($event, 'filter_available')"
                                            class="select-trigger-button toolbar-trigger-field">
                                            <i class="fa-solid fa-check-circle text-[10px] text-slate-400"></i>
                                            <span class="truncate">{{ unitDitanyaAvailableFilter || 'Semua Available' }}</span>
                                            <i v-if="unitDitanyaAvailableFilter" @click.stop="unitDitanyaAvailableFilter = ''"
                                                class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-red-500"></i>
                                            <i v-else class="fa-solid fa-chevron-down text-[9px] text-slate-400 ml-auto"></i>
                                        </button>
                                        <transition name="fade">
                                            <div v-if="searchSelectOpen === 'filter_available'" :style="popoverStyle"
                                                class="search-select-popover">
                                                <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                                    <div @click="unitDitanyaAvailableFilter = ''; searchSelectOpen = null"
                                                        :class="['popover-option', !unitDitanyaAvailableFilter ? 'popover-option-active' : '']">
                                                        Semua Available</div>
                                                    <div v-for="opt in unitAvailableOptions" :key="opt"
                                                        @click="unitDitanyaAvailableFilter = opt; searchSelectOpen = null"
                                                        :class="['popover-option', unitDitanyaAvailableFilter === opt ? 'popover-option-active' : '']">
                                                        {{ opt }} </div>
                                                </div>
                                            </div>
                                        </transition>
                                    </div>
                                    <div class="toolbar-actions">
                                        <button
                                            @click="unitDitanyaSearch='';unitDitanyaDateRange = getDefaultDateRange();unitDitanyaAvailableFilter=''"
                                            class="reset-filter-button" title="Reset"><i
                                                class="fa-solid fa-rotate-left text-[10px]"></i><span>Reset</span></button>
                                        <button @click="openUnitDitanyaModal('create')"
                                            class="primary-cta-button primary-cta-button--accent active:scale-95"><i
                                                class="fa-solid fa-plus mr-2"></i>Tambah</button>
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
                            <div v-if="unitDitanyaSummary.chips.length" class="chips-grid mt-4 pt-4 border-t border-slate-100">
                                <span v-for="ch in unitDitanyaSummary.chips" :key="ch.label" :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold', ch.cls]">{{ ch.label }} <span class="opacity-50">&middot;</span> {{ ch.n }}</span>
                            </div>
                        </section>
                        <div class="md:hidden space-y-3">
                            <div class="space-y-3">
                                <div v-if="filteredUnitDitanyaData.length === 0"
                                    class="bg-white radius-card border border-slate-100 p-10 text-center text-[11px] text-slate-400">
                                    Belum ada data unit ditanya
                                </div>
                                <div v-for="(row, idx) in pagedUnitDitanyaData" :key="'ud-mobile-' + (row.ID || idx)"
                                    class="stat-card mobile-record-card mobile-data-card motion-stagger-item"
                                    :style="getStaggerStyle(idx)">
                                    <div class="mobile-data-card__header">
                                        <span
                                            :class="(row.AVAILABLE||'').toUpperCase() === 'TERSEDIA' ? 'px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-600' : 'px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider bg-rose-100 text-rose-600'">
                                            {{ row.AVAILABLE || '-' }}
                                        </span>
                                        <span class="type-meta text-slate-400 font-bold uppercase tracking-widest">
                                            {{ row.TANGGAL ? formatShortDate(row.TANGGAL) : '-' }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="mobile-data-card__title line-clamp-2">{{ row.SERI || row.TIPE || row['TYPE UNIT'] || '-' }}</p>
                                        <div class="mobile-data-card__meta mt-2">
                                            <span
                                                class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-[9px] font-bold uppercase">
                                                {{ row.KATEGORI || '-' }}
                                            </span>
                                            <span
                                                class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 text-[9px] font-bold uppercase">
                                                {{ row.BRAND ? row.BRAND + ' | ' + row.SERI : row.SERI || '-' }}
                                            </span>
                                        </div>
                                        <p class="type-meta text-slate-400 mt-2 line-clamp-1">
                                            {{ row.KONDISI || '-' }}{{ row.TIPE || row['TYPE UNIT'] ? ' | ' + (row.TIPE || row['TYPE UNIT']) : '' }}
                                        </p>
                                    </div>
                                    <div class="mobile-data-card__summary">
                                        <div>
                                            <div class="type-meta text-slate-400 uppercase">Ditanya</div>
                                            <div class="type-body font-bold text-ppp-accent">{{ formatNumber(row.DITANYA || 0) }}</div>
                                        </div>
                                        <div>
                                            <div class="type-meta text-slate-400 uppercase">Spek</div>
                                            <div class="type-body font-bold text-slate-700 line-clamp-1">{{ row.RAM || '-' }} / {{ row.INTERNAL || '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="mobile-data-card__actions">
                                        <div class="type-meta text-slate-400 line-clamp-1">
                                            {{ row.WARNA || '-' }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button @click="openUnitDitanyaModal('edit', row)"
                                                class="table-action-button table-action-compact" title="Edit"
                                                aria-label="Edit"><i class="fa-solid fa-pen text-[10px]"></i></button>
                                            <button @click="deleteUnitDitanya(row.ID)"
                                                class="table-action-button table-action-compact table-action-danger"
                                                title="Hapus" aria-label="Hapus"><i
                                                    class="fa-solid fa-trash text-[10px]"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-center gap-2 py-2">
                                <button @click="unitDitanyaPage--" :disabled="unitDitanyaPage <= 1"
                                    aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-left text-[10px]"></i></button>
                                <span class="px-3 text-[10px] font-bold text-ppp-accent">{{ unitDitanyaPage }} / {{ unitDitanyaTotalPages }}</span>
                                <button @click="unitDitanyaPage++" :disabled="unitDitanyaPage >= unitDitanyaTotalPages"
                                    aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-right text-[10px]"></i></button>
                            </div>
                        </div>
                        <div class="hidden md:block section-card section-card-shell">
                            <div class="overflow-x-auto">
                                <table class="w-full text-[10.5px] text-left border-collapse min-w-[1000px]">
                                    <thead>
                                        <tr
                                            class="border-b border-slate-100 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                                            <th class="px-4 py-3">Aksi</th>
                                            <th class="px-4 py-3">Tanggal</th>
                                            <th class="px-4 py-3">Kategori</th>
                                            <th class="px-4 py-3">Brand</th>
                                            <th class="px-4 py-3">Seri</th>
                                            <th class="px-4 py-3">RAM</th>
                                            <th class="px-4 py-3">Internal</th>
                                            <th class="px-4 py-3">Warna</th>
                                            <th class="px-4 py-3">Kondisi</th>
                                            <th class="px-4 py-3">Tipe</th>
                                            <th class="px-4 py-3 text-right">Ditanya</th>
                                            <th class="px-4 py-3 text-center">Available</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(row, idx) in pagedUnitDitanyaData" :key="row.ID || idx"
                                            class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-1.5">
                                                    <button @click="openUnitDitanyaModal('edit', row)"
                                                        class="table-action-button table-action-compact" title="Edit"
                                                        aria-label="Edit"><i
                                                            class="fa-solid fa-pen text-[10px]"></i></button>
                                                    <button @click="deleteUnitDitanya(row.ID)"
                                                        class="table-action-button table-action-compact table-action-danger"
                                                        title="Hapus" aria-label="Hapus"><i
                                                            class="fa-solid fa-trash text-[10px]"></i></button>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 type-body text-slate-500">{{ formatShortDate(row.TANGGAL) }}</td>
                                            <td class="px-4 py-3 text-[11px] text-slate-600">{{ row.KATEGORI || '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-[11px] font-semibold text-slate-800">{{ row.BRAND || '-' }}</td>
                                            <td class="px-4 py-3 text-[11px] text-slate-700">{{ row.SERI || '-' }}</td>
                                            <td class="px-4 py-3 text-[11px] text-slate-600">{{ row.RAM || '-' }}</td>
                                            <td class="px-4 py-3 text-[11px] text-slate-600">{{ row.INTERNAL || '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-[11px] text-slate-600">{{ row.WARNA || '-' }}</td>
                                            <td class="px-4 py-3 text-[11px] text-slate-600">{{ row.KONDISI || '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-[11px] text-slate-700">{{ row.TIPE || row['TYPE UNIT'] || '-' }}</td>
                                            <td class="px-4 py-3 text-right text-[11px] font-bold text-ppp-accent">{{ formatNumber(row.DITANYA || 0) }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <span
                                                    :class="(row.AVAILABLE||'').toUpperCase() === 'TERSEDIA' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600'"
                                                    class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold">{{ row.AVAILABLE || '-' }}</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div
                                class="table-pager-bar">
                                <div class="text-[10px] text-slate-400 font-medium">
                                    <template v-if="filteredUnitDitanyaData.length > 0">{{ (unitDitanyaPage - 1) * 15 + 1 }}-{{ Math.min(unitDitanyaPage * 15, filteredUnitDitanyaData.length) }} dari
                                        {{ filteredUnitDitanyaData.length }} data</template>
                                    <template v-else>0 data</template>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button @click="unitDitanyaPage--" :disabled="unitDitanyaPage <= 1"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-left text-[10px]"></i></button>
                                    <span class="px-3 text-[10px] font-bold text-ppp-accent">{{ unitDitanyaPage }} / {{ unitDitanyaTotalPages }}</span>
                                    <button @click="unitDitanyaPage++"
                                        :disabled="unitDitanyaPage >= unitDitanyaTotalPages"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-right text-[10px]"></i></button>
                                </div>
                            </div>
                            <div v-if="unitDitanyaData.length === 0"
                                class="flex flex-col items-center justify-center py-20 text-slate-400">
                                <i class="fa-solid fa-circle-question text-4xl mb-4 opacity-20"></i>
                                <p class="text-[11px] font-bold uppercase tracking-widest">Belum ada data unit ditanya
                                </p>
                            </div>
                        </div>
                    </div>
@endverbatim
