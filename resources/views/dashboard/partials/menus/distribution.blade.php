@verbatim
<div v-if="activeTab === 'distribution'" class="space-y-4 animate-fadeIn">
    <div class="space-y-3">
        <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
            <div v-for="c in distributionSummary.cards.slice(0, 5)" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
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

    <div class="md:hidden space-y-3 p-3">
        <div class="table-toolbar-shell">
            <div class="table-toolbar-shell__left">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                    <input id="distribution-search-mobile" name="distribution_search_mobile" v-model="contentTableSearch" type="text" placeholder="Cari konten..." autocomplete="off" aria-label="Cari konten distribusi mobile" class="form-input-search" />
                </div>
            </div>
            <div class="table-toolbar-shell__right">
                <div class="relative">
                    <button @click="openCalendar($event, 'filter')" class="select-trigger-button-compact">
                        <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                        <template v-if="commonDateFilter.start">{{ formatShortDate(commonDateFilter.start) }}<span v-if="commonDateFilter.end"> - {{ formatShortDate(commonDateFilter.end) }}</span></template>
                        <template v-else>Semua Tanggal</template>
                        <i v-if="commonDateFilter.start" @click.stop="commonDateFilter = { start: '', end: '' }" class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-danger"></i>
                    </button>
                </div>
                <div class="toolbar-actions">
                    <button @click="exportExcel" class="primary-cta-button primary-cta-button--success primary-cta-button--icon-only active:scale-95" aria-label="Export Excel"><i class="fa-solid fa-file-excel"></i></button>
                    <button @click="exportPdf" class="primary-cta-button primary-cta-button--danger primary-cta-button--icon-only active:scale-95" aria-label="Export PDF"><i class="fa-solid fa-file-pdf"></i></button>
                </div>
            </div>
        </div>
        <div v-for="(row, idx) in pagedDistributionData" :key="row.ID"
            class="stat-card mobile-record-card mobile-data-card motion-stagger-item"
            :style="getStaggerStyle(idx)">
            <div class="mobile-data-card__header">
                <div class="flex-1">
                    <div class="mobile-data-card__title mb-1">{{ row.Judul }}</div>
                    <div class="mobile-data-card__meta">
                        <i
                            :class="getPlatformIcon(row.Platform) + ' text-body text-slate-400'"></i>
                        <span
                            class="text-body font-bold text-slate-700">{{ platformDisplayName(row.Platform) }}</span>
                    </div>
                </div>
            </div>
            <div class="mobile-data-card__summary">
                <div>
                    <div class="type-body-sm text-slate-400 uppercase font-bold">
                        Tanggal Publish</div>
                    <div class="type-body font-medium text-slate-600">{{ formatShortDate(row.Tanggal_Publish) }}</div>
                </div>
                <div>
                    <div class="type-body-sm text-slate-400 uppercase font-bold">
                        Link</div>
                    <div class="type-body font-medium text-slate-600">{{ row.Link ? 'Tersedia' : '-' }}</div>
                </div>
            </div>
            <div class="mobile-data-card__actions">
                <a :href="row.Link" target="_blank" rel="noopener noreferrer"
                    class="table-action-button table-action-compact table-action-link"
                    title="Link Distribution" aria-label="Link Distribution">
                    <i class="fa-solid fa-link text-body-sm"></i>
                </a>
                <div class="flex items-center gap-2">
                    <button @click="openDistModal(row)"
                        class="table-action-button table-action-compact" title="Edit"
                        aria-label="Edit"><i class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                    <button @click="deleteDistribution(row.ID)"
                        class="table-action-button table-action-compact table-action-danger"
                        title="Hapus" aria-label="Hapus"><i
                            class="fa-solid fa-trash-can text-body-sm"></i></button>
                </div>
            </div>
        </div>
        <div v-if="filteredDistributionData.length === 0"
            class="table-empty-state text-slate-400 text-body uppercase">
            Belum
            ada data distribusi</div>
    </div>

    <div class="hidden md:block section-card section-card-shell">
        <div class="table-toolbar-shell">
            <div class="table-toolbar-shell__left">
                <div class="relative">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                    <input id="distribution-search-desktop" name="distribution_search_desktop" v-model="contentTableSearch" type="text" placeholder="Cari konten..."
                        autocomplete="off" aria-label="Cari konten distribusi desktop"
                        class="form-input-search" />
                </div>
            </div>
            <div class="table-toolbar-shell__right">
                <div class="relative">
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
                </div>
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
        <div class="overflow-x-auto">
        <table class="w-full text-body-sm text-left border-collapse">
            <thead>
                <tr class="table-header-row">
                    <th class="table-header-cell table-header-index table-freeze-index">
                        #</th>
                    <th class="table-header-cell table-header-action table-freeze-action">
                        Aksi</th>
                    <th class="table-header-cell">
                        Judul</th>
                    <th class="table-header-cell">
                        Platform</th>
                    <th class="table-header-cell">
                        Tanggal Upload</th>
                    <th class="table-header-cell">
                        Link</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <tr v-for="(row, idx) in pagedDistributionData" :key="row.ID"
                    class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-4 text-center text-body-sm font-bold text-slate-400 tabular-nums table-freeze-index">{{ idx + 1 }}</td>
                    <td class="px-6 py-5 table-freeze-action">
                        <div class="flex items-center gap-2">
                            <button @click="openDistModal(row)"
                                class="table-action-button table-action-compact" title="Edit"
                                aria-label="Edit"><i
                                    class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                            <button @click="deleteDistribution(row.ID)"
                                class="table-action-button table-action-compact table-action-danger"
                                title="Hapus" aria-label="Hapus"><i
                                    class="fa-solid fa-trash-can text-body-sm"></i></button>
                        </div>
                    </td>
                    <td class="px-6 py-5 text-left">
                        <div class="text-body-sm font-bold text-slate-800">{{ row.Judul }}
                        </div>
                    </td>
                    <td class="px-6 py-5 text-left">
                        <div class="flex items-center gap-2">
                            <i
                                :class="getPlatformIcon(row.Platform) + ' text-body text-slate-400'"></i>
                            <span class="text-body font-bold text-slate-700">{{ platformDisplayName(row.Platform) }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-5 text-left">
                        <div class="type-body text-slate-500">{{ formatShortDate(row.Tanggal_Publish) }}</div>
                    </td>
                    <td class="px-6 py-5 text-left">
                        <a :href="row.Link" target="_blank" rel="noopener noreferrer"
                            class="table-action-button table-action-compact table-action-link"
                            title="Link Distribution" aria-label="Link Distribution">
                            <i class="fa-solid fa-link text-body-sm"></i>
                        </a>
                    </td>
                </tr>
                <tr v-if="filteredDistributionData.length === 0">
                    <td colspan="6"
                        class="px-6 py-20 text-center text-slate-400 text-body uppercase">
                        Belum ada data distribusi</td>
                </tr>
            </tbody>
        </table>
    </div>
        <div
            class="table-pager-bar">
            <div class="text-body-sm text-slate-400 font-medium">
                <template v-if="filteredDistributionData.length > 0">{{ (distributionPage - 1) * 15 + 1 }}-{{ Math.min(distributionPage * 15, filteredDistributionData.length) }}
                    dari {{ filteredDistributionData.length }} data</template>
                <template v-else>0 data</template>
            </div>
            <div class="flex items-center gap-1">
                <button @click="distributionPage--" :disabled="distributionPage <= 1"
                    aria-label="Halaman sebelumnya"
                    class="icon-utility-button icon-utility-bordered"><i
                        class="fa-solid fa-chevron-left text-body-sm"></i></button>
                <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ distributionPage }} / {{ distributionTotalPages }}</span>
                <button @click="distributionPage++"
                    :disabled="distributionPage >= distributionTotalPages"
                    aria-label="Halaman berikutnya"
                    class="icon-utility-button icon-utility-bordered"><i
                        class="fa-solid fa-chevron-right text-body-sm"></i></button>
            </div>
        </div>
    </div>
</div>
@endverbatim
