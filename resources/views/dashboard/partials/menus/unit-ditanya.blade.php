@verbatim
<!-- Unit Ditanya View -->
                    <div v-if="activeTab === 'unit_ditanya' && !tabDataLoaded['unitDitanya']"
                        class="space-y-4 animate-fadeIn animate-pulse">
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
                        class="space-y-4 animate-fadeIn">
                        <!-- Summary cards -->
                        <div class="space-y-3">
                            <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
                                <div v-for="c in unitDitanyaSummary.cards.slice(0, 5)" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                                    <div class="absolute -right-4 -bottom-4 opacity-5"><i :class="['fa-solid', c.icon, 'text-[120px]']"></i></div>
                                    <p class="dashboard-summary-title">{{ c.label }}</p>
                                    <div class="flex items-baseline gap-2">
                                        <span class="dashboard-summary-value">{{ c.value }}</span>
                                        <span v-if="c.unit" class="dashboard-summary-unit">{{ c.unit }}</span>
                                    </div>
                                    <p :class="['text-body-sm font-bold mt-3', c.subColor]">{{ c.sub }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="md:hidden space-y-3">
                            <div class="table-toolbar-shell">
                                <div class="table-toolbar-shell__left">
                                    <div class="relative">
                                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                                        <input id="unit-ditanya-search-mobile" name="unit_ditanya_search_mobile" v-model="unitDitanyaSearch" type="text" placeholder="Cari brand / seri / tipe..." autocomplete="off" aria-label="Cari unit ditanya mobile" class="form-input-search" />
                                    </div>
                                </div>
                                <div class="table-toolbar-shell__right">
                                    <div class="relative group search-select-container">
                                        <button @click="openCalendar($event, 'filter', '', 'unitDitanya')" class="select-trigger-button-compact">
                                            <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                            <template v-if="unitDitanyaDateRange.start">{{ formatShortDate(unitDitanyaDateRange.start) }}<span v-if="unitDitanyaDateRange.end"> - {{ formatShortDate(unitDitanyaDateRange.end) }}</span></template>
                                            <template v-else>Semua Tanggal</template>
                                            <i v-if="unitDitanyaDateRange.start" @click.stop="unitDitanyaDateRange = { start: '', end: '' }" class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-danger"></i>
                                        </button>
                                    </div>
                                    <div class="toolbar-actions">
                                        <button type="button" data-testid="unit-ditanya-create" @click.stop="openUnitDitanyaModal('create')" class="primary-cta-button primary-cta-button--accent primary-cta-button--icon-only active:scale-95" aria-label="Tambah Unit Ditanya"><i class="fa-solid fa-plus"></i></button>
                                        <button @click="exportExcel" class="primary-cta-button primary-cta-button--success primary-cta-button--icon-only active:scale-95" aria-label="Export Excel"><i class="fa-solid fa-file-excel"></i></button>
                                        <button @click="exportPdf" class="primary-cta-button primary-cta-button--danger primary-cta-button--icon-only active:scale-95" aria-label="Export PDF"><i class="fa-solid fa-file-pdf"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div v-if="filteredUnitDitanyaData.length === 0"
                                    class="bg-white radius-card border border-slate-100 p-10 text-center text-body text-slate-400">
                                    Belum ada data unit ditanya
                                </div>
                                <div v-for="(row, idx) in pagedUnitDitanyaData" :key="'ud-mobile-' + (row.ID || idx)"
                                    class="stat-card mobile-record-card mobile-data-card motion-stagger-item"
                                    :style="getStaggerStyle(idx)">
                                    <div class="mobile-data-card__header">
                                        <span
                                            :class="(row.AVAILABLE||'').toUpperCase() === 'TERSEDIA' ? 'px-2.5 py-1 rounded-full text-overline font-bold uppercase bg-success text-light' : 'px-2.5 py-1 rounded-full text-overline font-bold uppercase bg-danger text-light'">
                                            {{ row.AVAILABLE || '-' }}
                                        </span>
                                        <span class="type-body-sm text-slate-400 font-bold uppercase">
                                            {{ row.TANGGAL ? formatShortDate(row.TANGGAL) : '-' }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="mobile-data-card__title line-clamp-2">{{ row.SERI || row.TIPE || row['TYPE UNIT'] || '-' }}</p>
                                        <div class="mobile-data-card__meta mt-2">
                                            <span
                                                class="px-2.5 py-1 rounded-lg bg-secondary text-light text-overline font-bold uppercase">
                                                {{ row.KATEGORI || '-' }}
                                            </span>
                                            <span
                                                class="px-2.5 py-1 rounded-lg bg-amber text-light text-overline font-bold uppercase">
                                                {{ row.BRAND ? row.BRAND + ' | ' + row.SERI : row.SERI || '-' }}
                                            </span>
                                        </div>
                                        <p class="type-body-sm text-slate-400 mt-2 line-clamp-1">
                                            {{ row.KONDISI || '-' }}{{ row.TIPE || row['TYPE UNIT'] ? ' | ' + (row.TIPE || row['TYPE UNIT']) : '' }}
                                        </p>
                                    </div>
                                    <div class="mobile-data-card__summary">
                                        <div>
                                            <div class="type-body-sm text-slate-400 uppercase">Ditanya</div>
                                            <div class="type-body font-bold text-ppp-accent">{{ formatNumber(row.DITANYA || 0) }}</div>
                                        </div>
                                        <div>
                                            <div class="type-body-sm text-slate-400 uppercase">Spek</div>
                                            <div class="type-body font-bold text-slate-700 line-clamp-1">{{ row.RAM || '-' }} / {{ row.INTERNAL || '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="mobile-data-card__actions">
                                        <div class="type-body-sm text-slate-400 line-clamp-1">
                                            {{ row.WARNA || '-' }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button @click="openUnitDitanyaModal('edit', row)"
                                                class="table-action-button table-action-compact" title="Edit"
                                                aria-label="Edit"><i class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                                            <button @click="deleteUnitDitanya(row.ID)"
                                                class="table-action-button table-action-compact table-action-danger"
                                                title="Hapus" aria-label="Hapus"><i
                                                    class="fa-solid fa-trash-can text-body-sm"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-center gap-2 py-2">
                                <button @click="unitDitanyaPage--" :disabled="unitDitanyaPage <= 1"
                                    aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-left text-body-sm"></i></button>
                                <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ unitDitanyaPage }} / {{ unitDitanyaTotalPages }}</span>
                                <button @click="unitDitanyaPage++" :disabled="unitDitanyaPage >= unitDitanyaTotalPages"
                                    aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-right text-body-sm"></i></button>
                            </div>
                        </div>
                        <div class="hidden md:block section-card section-card-shell">
                            <div class="table-toolbar-shell">
                                <div class="table-toolbar-shell__left">
                                    <div class="relative">
                                        <i
                                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                                        <input id="unit-ditanya-search-desktop" name="unit_ditanya_search_desktop" v-model="unitDitanyaSearch" type="text"
                                            autocomplete="off" aria-label="Cari unit ditanya desktop"
                                            placeholder="Cari brand / seri / tipe..." class="form-input-search" />
                                    </div>
                                </div>
                                <div class="table-toolbar-shell__right">
                                    <div class="relative group search-select-container">
                                        <button @click="openCalendar($event, 'filter', '', 'unitDitanya')"
                                            class="select-trigger-button-compact">
                                            <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                            <template v-if="unitDitanyaDateRange.start">
                                                {{ formatShortDate(unitDitanyaDateRange.start) }}
                                                <span v-if="unitDitanyaDateRange.end"> - {{ formatShortDate(unitDitanyaDateRange.end) }}</span>
                                            </template>
                                            <template v-else>Semua Tanggal</template>
                                            <i v-if="unitDitanyaDateRange.start"
                                                @click.stop="unitDitanyaDateRange = { start: '', end: '' }"
                                                class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-danger"></i>
                                        </button>
                                    </div>
                                    <div class="relative search-select-container">
                                        <button @click="toggleSearchSelect($event, 'filter_available')"
                                            class="select-trigger-button toolbar-trigger-field">
                                            <i class="fa-solid fa-check-circle text-body-sm text-slate-400"></i>
                                            <span class="truncate">{{ unitDitanyaAvailableFilter || 'Semua Available' }}</span>
                                            <i v-if="unitDitanyaAvailableFilter" @click.stop="unitDitanyaAvailableFilter = ''"
                                                class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-danger"></i>
                                            <i v-else class="fa-solid fa-chevron-down text-overline text-slate-400 ml-auto"></i>
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
                                    <div class="toolbar-actions toolbar-actions--desktop-icon-only">
<button @click="openUnitDitanyaModal('create')"
                                             class="primary-cta-button primary-cta-button--accent primary-cta-button--icon-only active:scale-95"
                                             aria-label="Tambah Unit Ditanya"><i
                                                 class="fa-solid fa-plus"></i></button>
                                        <button @click="exportExcel"
                                            class="primary-cta-button primary-cta-button--success primary-cta-button--icon-only active:scale-95" aria-label="Export Excel"><i
                                                class="fa-solid fa-file-excel"></i></button>
                                        <button @click="exportPdf"
                                            class="primary-cta-button primary-cta-button--danger primary-cta-button--icon-only active:scale-95" aria-label="Export PDF"><i
                                                class="fa-solid fa-file-pdf"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[1320px] table-fixed text-body-sm text-left border-collapse">
                                    <thead>
                                        <tr class="table-header-row">
                                            <th class="table-header-cell table-header-index table-freeze-index">#</th>
                                            <th class="table-header-cell table-header-action table-freeze-action">Aksi</th>
                                            <th class="table-header-cell text-left w-[110px]">Tanggal</th>
                                            <th class="table-header-cell text-left w-[110px]">Kategori</th>
                                            <th class="table-header-cell text-left w-[110px]">Brand</th>
                                            <th class="table-header-cell text-left w-[16%]">Seri</th>
                                            <th class="table-header-cell text-left w-[88px]">RAM</th>
                                            <th class="table-header-cell text-left w-[96px]">Internal</th>
                                            <th class="table-header-cell text-left w-[110px]">Warna</th>
                                            <th class="table-header-cell text-left w-[110px]">Kondisi</th>
                                            <th class="table-header-cell text-left w-[14%]">Tipe</th>
                                            <th class="table-header-cell text-right w-[90px]">Ditanya</th>
                                            <th class="table-header-cell text-center w-[110px]">Available</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(row, idx) in pagedUnitDitanyaData" :key="row.ID || idx"
                                            class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                            <td class="px-4 py-3 text-center text-body-sm font-bold text-slate-400 tabular-nums table-freeze-index">{{ idx + 1 }}</td>
                                            <td class="px-4 py-3 text-left table-freeze-action">
                                                <div class="flex items-center gap-1.5">
                                                    <button @click="openUnitDitanyaModal('edit', row)"
                                                        class="table-action-button table-action-compact" title="Edit"
                                                        aria-label="Edit"><i
                                                            class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                                                    <button @click="deleteUnitDitanya(row.ID)"
                                                        class="table-action-button table-action-compact table-action-danger"
                                                        title="Hapus" aria-label="Hapus"><i
                                                            class="fa-solid fa-trash-can text-body-sm"></i></button>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-left type-body text-slate-500 whitespace-nowrap">{{ formatShortDate(row.TANGGAL) }}</td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600">{{ row.KATEGORI || '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-left text-body font-semibold text-slate-800">{{ row.BRAND || '-' }}</td>
                                            <td class="px-4 py-3 text-left text-body text-slate-700 break-words">{{ row.SERI || '-' }}</td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600">{{ row.RAM || '-' }}</td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600">{{ row.INTERNAL || '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600 break-words">{{ row.WARNA || '-' }}</td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600">{{ row.KONDISI || '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-left text-body text-slate-700 break-words">{{ row.TIPE || row['TYPE UNIT'] || '-' }}</td>
                                            <td class="px-4 py-3 text-right text-body font-bold text-ppp-accent">{{ formatNumber(row.DITANYA || 0) }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <span
                                                    :class="(row.AVAILABLE||'').toUpperCase() === 'TERSEDIA' ? 'bg-success text-light' : 'bg-danger text-light'"
                                                    class="inline-flex px-2 py-0.5 rounded-full text-body-sm font-bold">{{ row.AVAILABLE || '-' }}</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div
                                class="table-pager-bar">
                                <div class="text-body-sm text-slate-400 font-medium">
                                    <template v-if="filteredUnitDitanyaData.length > 0">{{ (unitDitanyaPage - 1) * 15 + 1 }}-{{ Math.min(unitDitanyaPage * 15, filteredUnitDitanyaData.length) }} dari
                                        {{ filteredUnitDitanyaData.length }} data</template>
                                    <template v-else>0 data</template>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button @click="unitDitanyaPage--" :disabled="unitDitanyaPage <= 1"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-left text-body-sm"></i></button>
                                    <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ unitDitanyaPage }} / {{ unitDitanyaTotalPages }}</span>
                                    <button @click="unitDitanyaPage++"
                                        :disabled="unitDitanyaPage >= unitDitanyaTotalPages"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-right text-body-sm"></i></button>
                                </div>
                            </div>
                            <div v-if="unitDitanyaData.length === 0"
                                class="flex flex-col items-center justify-center py-20 text-slate-400">
                                <i class="fa-solid fa-circle-question text-4xl mb-4 opacity-20"></i>
                                <p class="text-body font-bold uppercase">Belum ada data unit ditanya
                                </p>
                            </div>
                        </div>
                    </div>

    <!-- Unit Ditanya Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="unitDitanyaModalOpen"
                class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                <div @click="unitDitanyaModalOpen = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop">
                </div>
                <div
                    class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface">
                    <div
                        class="modal-header-bar modal-header-bar-sticky radius-sheet-top z-[2010]">
                        <div class="modal-header-copy">
                            <div
                                class="modal-header-icon bg-amber text-light border border-amber">
                                <i class="fa-solid fa-circle-question"></i>
                            </div>
                            <div>
                                <div class="type-heading-sm text-slate-900">{{ csModalType === 'create' ? 'Tambah' : 'Edit' }} Unit Ditanya</div>
                                <div class="type-body-sm text-slate-400 uppercase mt-0.5">Customer
                                    Service</div>
                            </div>
                        </div>
                        <button @click="unitDitanyaModalOpen = false" aria-label="Tutup modal"
                            class="icon-utility-button icon-utility-round"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="p-6 overflow-y-auto space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Tanggal</label>
                                <button type="button" @click="openCalendar($event, 'form', '', 'unitDitanya1')"
                                    class="select-trigger-button-form toolbar-trigger-field-form">
                                    <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                    <span
                                        :class="unitDitanyaForm['TANGGAL'] ? 'text-slate-700 font-medium' : 'text-slate-400'">{{ unitDitanyaForm['TANGGAL'] ? formatFullDate(unitDitanyaForm['TANGGAL']) : 'Pilih Tanggal' }}</span>
                                </button>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Kategori</label>
                                <button type="button" @click="toggleSearchSelect($event, 'unit_kategori')"
                                    :aria-expanded="searchSelectOpen === 'unit_kategori' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="unitDitanyaForm['KATEGORI'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ unitDitanyaForm['KATEGORI'] || 'Pilih Kategori' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'unit_kategori'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari kategori unit ditanya"
                                                placeholder="Cari kategori..." class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unitKategoriOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="unitDitanyaForm['KATEGORI'] = opt; unitDitanyaForm['BRAND'] = ''; unitDitanyaForm['SERI'] = ''; searchSelectOpen = null"
                                                :class="['popover-option', unitDitanyaForm['KATEGORI'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="unitKategoriOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Brand</label>
                                <button type="button" @click="toggleSearchSelect($event, 'unit_brand')"
                                    :aria-expanded="searchSelectOpen === 'unit_brand' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="unitDitanyaForm['BRAND'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ unitDitanyaForm['BRAND'] || 'Pilih Brand' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'unit_brand'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari brand unit ditanya" placeholder="Cari brand..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unitBrandOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="unitDitanyaForm['BRAND'] = opt; unitDitanyaForm['SERI'] = ''; searchSelectOpen = null"
                                                :class="['popover-option', unitDitanyaForm['BRAND'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="unitBrandOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Seri</label>
                                <button type="button" @click="toggleSearchSelect($event, 'unit_seri')"
                                    :aria-expanded="searchSelectOpen === 'unit_seri' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="unitDitanyaForm['SERI'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ unitDitanyaForm['SERI'] || 'Pilih Seri' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'unit_seri'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari seri unit ditanya" placeholder="Cari seri..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-if="nsSeriOptions.length === 0"
                                                class="px-3 py-2 text-body text-slate-400 italic">Pilih Kategori &
                                                Brand dulu</div>
                                            <div v-for="opt in nsSeriOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="unitDitanyaForm['SERI'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', unitDitanyaForm['SERI'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="nsSeriOptions.length > 0 && nsSeriOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">RAM</label>
                                <button type="button" @click="toggleSearchSelect($event, 'unit_ram')"
                                    :aria-expanded="searchSelectOpen === 'unit_ram' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="unitDitanyaForm['RAM'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ unitDitanyaForm['RAM'] || 'Pilih RAM' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'unit_ram'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari RAM unit ditanya" placeholder="Cari RAM..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unitRAMOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="unitDitanyaForm['RAM'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', unitDitanyaForm['RAM'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="unitRAMOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Internal</label>
                                <button type="button" @click="toggleSearchSelect($event, 'unit_internal')"
                                    :aria-expanded="searchSelectOpen === 'unit_internal' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="unitDitanyaForm['INTERNAL'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ unitDitanyaForm['INTERNAL'] || 'Pilih Internal' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'unit_internal'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari internal unit ditanya"
                                                placeholder="Cari internal..." class="form-input-popover"
                                                @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unitInternalOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="unitDitanyaForm['INTERNAL'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', unitDitanyaForm['INTERNAL'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="unitInternalOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Size</label>
                                <button type="button" @click="toggleSearchSelect($event, 'unit_size')"
                                    :aria-expanded="searchSelectOpen === 'unit_size' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="unitDitanyaForm['SIZE'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ unitDitanyaForm['SIZE'] || 'Pilih Size' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'unit_size'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari size unit ditanya"
                                                placeholder="Cari size..." class="form-input-popover"
                                                @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unitSizeOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="unitDitanyaForm['SIZE'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', unitDitanyaForm['SIZE'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="unitSizeOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div>
                                <label for="unit-ditanya-warna"
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Warna</label>
                                <input id="unit-ditanya-warna" name="unit_ditanya_warna" v-model="unitDitanyaForm['WARNA']" type="text" class="form-input" />
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Kondisi</label>
                                <button type="button" @click="toggleSearchSelect($event, 'kondisi')"
                                    :aria-expanded="searchSelectOpen === 'kondisi' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="unitDitanyaForm['KONDISI'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ unitDitanyaForm['KONDISI'] || 'Pilih Kondisi' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'kondisi'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unitKondisiOptions" :key="opt"
                                                @click="unitDitanyaForm['KONDISI'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', unitDitanyaForm['KONDISI'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Tipe</label>
                                <button type="button" @click="toggleSearchSelect($event, 'unit_tipe')"
                                    :aria-expanded="searchSelectOpen === 'unit_tipe' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="unitDitanyaForm['TIPE'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ unitDitanyaForm['TIPE'] || 'Pilih Tipe' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'unit_tipe'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari tipe unit ditanya" placeholder="Cari tipe..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in sharedUnitTypeOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="unitDitanyaForm['TIPE'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', unitDitanyaForm['TIPE'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="sharedUnitTypeOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div>
                                <label for="unit-ditanya-ditanya" class="type-body-sm font-bold text-slate-400 uppercase mb-2">Jumlah
                                    Ditanya</label>
                                <input id="unit-ditanya-ditanya" name="unit_ditanya_jumlah" v-model.number="unitDitanyaForm['DITANYA']" type="number" min="1"
                                    class="form-input" />
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Available</label>
                                <button type="button" @click="toggleSearchSelect($event, 'available')"
                                    :aria-expanded="searchSelectOpen === 'available' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="unitDitanyaForm['AVAILABLE'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ unitDitanyaForm['AVAILABLE'] || 'Pilih Available' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'available'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unitAvailableOptions" :key="opt"
                                                @click="unitDitanyaForm['AVAILABLE'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', unitDitanyaForm['AVAILABLE'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer-bar modal-footer-actions">
                        <button @click="unitDitanyaModalOpen = false" class="primary-cta-button primary-cta-button--neutral">Batal</button>
                        <button @click="saveUnitDitanya" :disabled="submitting" class="primary-cta-button">{{ submitting ? 'Menyimpan...' : 'Simpan' }}</button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
@endverbatim
