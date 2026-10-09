@verbatim
<style>
    .table-action-button.table-action-link:hover {
        color: var(--ppp-table-action-link-hover) !important;
        border-color: var(--ppp-table-action-link-hover) !important;
    }

    .table-action-button.table-action-static:hover {
        color: rgb(100 116 139) !important;
        border-color: var(--ppp-line) !important;
        cursor: default;
    }
</style>
<!-- Master Plan View -->
                    <div v-if="activeTab === 'master'" class="space-y-4 animate-fadeIn">
                        <!-- Summary cards -->
                        <div class="space-y-3">
                            <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
                                <div v-for="c in masterSummary.cards.slice(0, 5)" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
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

                        <div class="md:hidden section-card section-card-shell master-plan-mobile-toolbar">
                            <div class="table-toolbar-shell">
                                <div class="table-toolbar-shell__left">
                                    <div class="relative">
                                        <i
                                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                                        <input id="master-search" name="master_search" v-model="masterSearch" type="text" placeholder="Cari judul atau colab..."
                                            autocomplete="off" aria-label="Cari judul atau kolaborator master plan"
                                            class="form-input-search" />
                                    </div>
                                </div>
                                <div class="table-toolbar-shell__right">
                                    <div class="relative">
                                        <button @click="openCalendar($event, 'filter')" class="select-trigger-button-compact">
                                            <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                            <template v-if="masterFilterRange.start">
                                                {{ formatShortDate(masterFilterRange.start) }}
                                                <span v-if="masterFilterRange.end"> - {{ formatShortDate(masterFilterRange.end) }}</span>
                                            </template>
                                            <template v-else>Semua Tanggal</template>
                                            <i v-if="masterFilterRange.start"
                                                @click.stop="masterFilterRange = { start: '', end: '' }; saveFilterRange()"
                                                class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-danger"></i>
                                        </button>
                                    </div>
                                    <div class="toolbar-actions">
                                        <button @click="openCreateModal"
                                            class="primary-cta-button primary-cta-button--accent primary-cta-button--icon-only active:scale-95"
                                            aria-label="Tambah Plan">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                        <button @click="exportExcel"
                                            class="primary-cta-button primary-cta-button--success primary-cta-button--icon-only active:scale-95" aria-label="Export Excel"><i
                                                class="fa-solid fa-file-excel"></i></button>
                                        <button @click="exportPdf"
                                            class="primary-cta-button primary-cta-button--danger primary-cta-button--icon-only active:scale-95" aria-label="Export PDF"><i
                                                class="fa-solid fa-file-pdf"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Main Table Container (Desktop) -->
                        <div class="hidden md:block section-card section-card-shell">
                            <div class="table-toolbar-shell">
                                <div class="table-toolbar-shell__left">
                                    <div class="relative">
                                        <i
                                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                                        <input id="master-search-desktop" name="master_search_desktop" v-model="masterSearch" type="text" placeholder="Cari judul atau colab..."
                                            autocomplete="off" aria-label="Cari judul atau kolaborator master plan desktop"
                                            class="form-input-search" />
                                    </div>
                                </div>
                                <div class="table-toolbar-shell__right">
                                    <div class="relative">
                                        <button @click="openCalendar($event, 'filter')" class="select-trigger-button-compact">
                                            <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                            <template v-if="masterFilterRange.start">
                                                {{ formatShortDate(masterFilterRange.start) }}
                                                <span v-if="masterFilterRange.end"> - {{ formatShortDate(masterFilterRange.end) }}</span>
                                            </template>
                                            <template v-else>Semua Tanggal</template>
                                            <i v-if="masterFilterRange.start"
                                                @click.stop="masterFilterRange = { start: '', end: '' }; saveFilterRange()"
                                                class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-danger"></i>
                                        </button>
                                    </div>
                                    <div class="toolbar-actions toolbar-actions--desktop-icon-only">
                                        <button @click="openCreateModal"
                                            class="primary-cta-button primary-cta-button--accent primary-cta-button--icon-only active:scale-95"
                                            aria-label="Tambah Plan">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
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
                                <table class="w-full min-w-[1480px] table-fixed text-body-sm text-left border-collapse">
                                    <thead>
                                        <tr class="table-header-row">
                                            <th class="table-header-cell table-header-index table-freeze-index">
                                                #</th>
                                            <th class="table-header-cell table-header-action table-freeze-action">
                                                Aksi</th>
                                            <th class="table-header-cell text-left w-[24%]">
                                                Judul</th>
                                            <th class="table-header-cell text-left w-[110px]">
                                                Format</th>
                                            <th class="table-header-cell text-left w-[120px]">
                                                Editor</th>
                                            <th class="table-header-cell text-left w-[150px]">
                                                Talent</th>
                                            <th class="table-header-cell text-left w-[116px]">
                                                Link</th>
                                            <th class="table-header-cell text-left w-[16%]">
                                                Platform</th>
                                            <th class="table-header-cell text-left w-[120px]">
                                                Status</th>
                                            <th class="table-header-cell text-left w-[110px]">
                                                Tgl Target</th>
                                            <th class="table-header-cell text-center w-[84px]">
                                                Skrip</th>
                                            <th class="table-header-cell text-center w-[92px]">
                                                Caption</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <tr v-for="(item, idx) in pagedMasterPlanData" :key="item.ID"
                                            class="group hover:bg-slate-50/30 transition-colors">
                                            <td class="px-4 py-4 text-center text-body-sm font-bold text-slate-400 tabular-nums table-freeze-index">{{ idx + 1 }}</td>
                                            <td class="px-4 py-5 text-left table-freeze-action">
                                                <div class="flex items-center gap-2">
                                                    <button @click="openEditModal(item)"
                                                        class="table-action-button table-action-compact" title="Edit"
                                                        aria-label="Edit">
                                                        <i class="fa-solid fa-pen-to-square text-body-sm"></i>
                                                    </button>
                                                    <button @click="deleteMasterPlan(item.ID)"
                                                        class="table-action-button table-action-compact table-action-danger"
                                                        title="Hapus" aria-label="Hapus">
                                                        <i class="fa-solid fa-trash-can text-body-sm"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-4 py-5 text-left">
                                                <div
                                                    class="font-semibold text-heading-sm text-slate-800 group-hover:text-ppp-accent transition-colors leading-tight break-words">
                                                    {{ item.Judul }}</div>
                                            </td>
                                            <td class="px-4 py-5 text-left">
                                                <span class="text-body text-slate-600 font-medium break-words">{{ toProperCase(item.Format_Konten || '-') }}</span>
                                            </td>
                                            <td class="px-4 py-5 text-left">
                                                <div class="flex items-center gap-2">
                                                    <div
                                                        class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-overline font-bold text-slate-600 flex-shrink-0 overflow-hidden">
                                                        <img v-if="resolveUserAvatarUrl(item.Editor)"
                                                            :src="resolveAvatarUrl(resolveUserAvatarUrl(item.Editor))"
                                                            class="w-full h-full object-cover" alt="Foto Editor"
                                                            @error="markMasterPlanEditorAvatarFailed(item.Editor)" />
                                                        <span v-else>{{ masterPersonInitials(item.Editor) }}</span>
                                                    </div>
                                                    <div
                                                        class="text-body text-slate-700 font-semibold truncate max-w-[80px]">
                                                        {{ personDisplayName(item.Editor) }}</div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-5 text-left">
                                                <div v-if="item.TalentList && item.TalentList.length" class="flex flex-wrap gap-1.5">
                                                    <div v-for="talent in item.TalentList" :key="`${item.ID}-talent-${talent}`"
                                                        class="flex items-center gap-2">
                                                        <div
                                                            class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-overline font-bold text-slate-600 flex-shrink-0 overflow-hidden">
                                                            <img v-if="resolveUserAvatarUrl(talent)"
                                                                :src="resolveAvatarUrl(resolveUserAvatarUrl(talent))"
                                                                class="w-full h-full object-cover" alt="Foto Talent"
                                                                @error="markMasterPlanEditorAvatarFailed(talent)" />
                                                            <span v-else>{{ masterPersonInitials(talent) }}</span>
                                                        </div>
                                                        <span class="text-body text-slate-700 font-semibold truncate max-w-[80px]">{{ personDisplayName(talent) }}</span>
                                                    </div>
                                                </div>
                                                <span v-else class="text-body text-slate-300">-</span>
                                            </td>
                                            <td class="px-4 py-5 text-left">
                                                <div class="flex items-center gap-1.5">
                                                    <a v-if="item.Link_Drive" :href="item.Link_Drive" target="_blank" rel="noopener noreferrer"
                                                        class="table-action-button table-action-compact table-action-link"
                                                        title="Link Drive" aria-label="Link Drive">
                                                        <i class="fa-solid fa-folder-open text-body-sm"></i>
                                                    </a>
                                                    <span v-else class="text-body-sm text-slate-300">-</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-5 text-left">
                                                <div class="flex flex-col items-start gap-1.5">
                                                    <span v-for="plat in (item.Platforms || '').split(',')" :key="plat"
                                                        class="flex items-center gap-2"
                                                        :title="(plat || '').trim()">
                                                        <i :class="getPlatformIcon(plat) + ' text-body text-slate-400'"></i>
                                                        <span class="text-body font-bold text-slate-700">{{ platformDisplayName(plat) }}</span>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-5 text-left">
                                                <span
                                                    :class="['inline-flex items-center px-2.5 py-1 rounded-full text-overline font-bold uppercase whitespace-nowrap', getStatusColor(item.Status)]">
                                                    <span
                                                        class="w-1.5 h-1.5 rounded-full bg-current mr-1.5 opacity-70"></span>
                                                    {{ item.Status }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-5 text-left">
                                                <div class="text-body text-slate-600 font-bold whitespace-nowrap">{{ formatShortDate(item.Tanggal_Rencana) }}</div>
                                            </td>
                                            <td class="px-4 py-5 text-center">
                                                <span
                                                    :class="['text-body font-bold', (item.Skrip === 'Ada' || item.Skrip === 'Ya') ? 'text-success' : 'text-slate-300']">{{ (item.Skrip === 'Ada' || item.Skrip === 'Ya') ? 'Ada' : '-' }}</span>
                                            </td>
                                            <td class="px-4 py-5 text-center">
                                                <span
                                                    :class="['text-body font-bold', (item.Caption === 'Ada' || item.Caption === 'Ya') ? 'text-success' : 'text-slate-300']">{{ (item.Caption === 'Ada' || item.Caption === 'Ya') ? 'Ada' : '-' }}</span>
                                            </td>
                                        </tr>
                                        <tr v-if="filteredMasterPlanData.length === 0">
                                            <td colspan="12" class="px-6 py-20 text-center">
                                                <div class="flex flex-col items-center gap-3">
                                                    <div
                                                        class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-200 text-2xl">
                                                        <i class="fa-solid fa-folder-open"></i>
                                                    </div>
                                                    <div
                                                        class="text-slate-400 text-body font-medium uppercase">
                                                        Tidak ada data ditemukan</div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div
                                class="table-pager-bar">
                                <div class="text-body-sm text-slate-400 font-medium">
                                    <template v-if="filteredMasterPlanData.length > 0">{{ (masterPage - 1) * 15 + 1 }}-{{ Math.min(masterPage * 15, filteredMasterPlanData.length) }} dari {{ filteredMasterPlanData.length }} data</template>
                                    <template v-else>0 data</template>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button @click="masterPage--" :disabled="masterPage <= 1"
                                        aria-label="Halaman sebelumnya"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-left text-body-sm"></i></button>
                                    <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ masterPage }} / {{ masterTotalPages }}</span>
                                    <button @click="masterPage++" :disabled="masterPage >= masterTotalPages"
                                        aria-label="Halaman berikutnya"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-right text-body-sm"></i></button>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Cards (Mobile Only) -->
                        <div class="md:hidden space-y-3">
                            <div v-for="(item, idx) in pagedMasterPlanData" :key="item.ID"
                                class="stat-card mobile-record-card mobile-data-card motion-stagger-item"
                                :style="getStaggerStyle(idx)">
                                <div class="mobile-data-card__header">
                                    <span
                                        :class="['px-3 py-1.5 rounded-full text-overline font-bold uppercase', getStatusColor(item.Status)]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current mr-2 opacity-70"></span>
                                        {{ item.Status }}
                                    </span>
                                    <div class="text-body-sm text-slate-400 font-bold uppercase">{{ formatShortDate(item.Tanggal_Rencana) }}</div>
                                </div>
                                <div>
                                    <div class="mobile-data-card__title">{{ item.Judul }}</div>
                                    <div class="mobile-data-card__meta mt-3">
                                        <span
                                            class="px-2 py-1 rounded-lg bg-secondary text-light text-overline font-bold border border-slate-100">
                                            {{ item.Format_Konten }} </span>
                                        <div
                                            class="flex items-center gap-1.5 px-2 py-1 rounded-lg bg-slate-50 border border-slate-100">
                                            <div
                                                class="w-4 h-4 rounded-full bg-slate-200 flex items-center justify-center text-overline-xs font-bold text-slate-600 overflow-hidden">
                                                <img v-if="resolveUserAvatarUrl(item.Editor)"
                                                    :src="resolveAvatarUrl(resolveUserAvatarUrl(item.Editor))"
                                                    class="w-full h-full object-cover" alt="Foto Editor"
                                                    @error="markMasterPlanEditorAvatarFailed(item.Editor)" />
                                                <span v-else>{{ masterPersonInitials(item.Editor) }}</span>
                                            </div>
                                            <span class="text-body text-slate-700 font-semibold truncate max-w-[80px]">{{ personDisplayName(item.Editor) }}</span>
                                        </div>
                                    </div>
                                    <div v-if="item.TalentList && item.TalentList.length" class="mobile-data-card__meta mt-2">
                                        <div v-for="talent in item.TalentList" :key="`${item.ID}-mobile-talent-${talent}`"
                                            class="flex items-center gap-2">
                                            <div
                                                class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-overline font-bold text-slate-600 flex-shrink-0 overflow-hidden">
                                                <img v-if="resolveUserAvatarUrl(talent)"
                                                    :src="resolveAvatarUrl(resolveUserAvatarUrl(talent))"
                                                    class="w-full h-full object-cover" alt="Foto Talent"
                                                    @error="markMasterPlanEditorAvatarFailed(talent)" />
                                                <span v-else>{{ masterPersonInitials(talent) }}</span>
                                            </div>
                                            <span class="text-body text-slate-700 font-semibold truncate max-w-[80px]">{{ personDisplayName(talent) }}</span>
                                        </div>
                                    </div>
                                    <div class="mobile-data-card__meta mobile-data-card__meta--stacked mt-2">
                                        <span v-for="plat in (item.Platforms || '').split(',')" :key="plat"
                                            class="flex items-center gap-2"
                                            :title="(plat || '').trim()">
                                            <i :class="getPlatformIcon(plat) + ' text-body text-slate-400'"></i>
                                            <span class="text-body font-bold text-slate-700">{{ platformDisplayName(plat) }}</span>
                                        </span>
                                    </div>
                                </div>
                                <div class="mobile-data-card__summary">
                                    <div>
                                        <div class="type-body-sm text-slate-400 uppercase font-bold">Editor
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-overline font-bold text-slate-600 flex-shrink-0 overflow-hidden">
                                                <img v-if="resolveUserAvatarUrl(item.Editor)"
                                                    :src="resolveAvatarUrl(resolveUserAvatarUrl(item.Editor))"
                                                    class="w-full h-full object-cover" alt="Foto Editor"
                                                    @error="markMasterPlanEditorAvatarFailed(item.Editor)" />
                                                <span v-else>{{ masterPersonInitials(item.Editor) }}</span>
                                            </div>
                                            <div class="text-body text-slate-700 font-semibold truncate max-w-[80px]">{{ personDisplayName(item.Editor) }}</div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="type-body-sm text-slate-400 uppercase font-bold">Asset
                                        </div>
                                        <div class="type-body font-bold text-slate-700">{{ (item.Skrip === 'Ada' || item.Skrip === 'Ya') ? 'Skrip siap' : ((item.Caption === 'Ada' || item.Caption === 'Ya') ? 'Caption siap' : '-') }}</div>
                                    </div>
                                </div>
                                    <div class="mobile-data-card__actions">
                                        <div class="flex items-center gap-1">
                                            <a v-if="item.Link_Drive" :href="item.Link_Drive" target="_blank" rel="noopener noreferrer"
                                                class="table-action-button table-action-compact table-action-link"
                                                title="Link Drive" aria-label="Link Drive">
                                                <i class="fa-solid fa-folder-open text-body-sm"></i>
                                            </a>
                                            <span v-else class="text-body-sm text-slate-300">-</span>
                                        </div>
                                    <div class="flex items-center gap-2">
                                        <button @click="openEditModal(item)"
                                            class="table-action-button table-action-compact active:scale-90" title="Edit" aria-label="Edit"><i
                                                class="fa-solid fa-pen-to-square text-body"></i></button>
                                        <button @click="deleteMasterPlan(item.ID)"
                                            class="table-action-button table-action-compact table-action-danger active:scale-90" title="Hapus" aria-label="Hapus"><i
                                                class="fa-solid fa-trash-can text-body"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div v-if="filteredMasterPlanData.length === 0"
                                class="bg-white radius-panel border border-slate-100 p-10 text-center">
                                <div class="text-slate-400 text-body-sm font-medium uppercase">Data
                                    Kosong</div>
                            </div>
                            <div v-if="masterTotalPages > 1"
                                class="bg-white radius-panel border border-slate-100 px-6 py-4 flex items-center justify-between">
                                <div class="text-body-sm text-slate-400 font-medium">
                                    <template v-if="filteredMasterPlanData.length > 0">{{ (masterPage - 1) * 15 + 1 }}-{{ Math.min(masterPage * 15, filteredMasterPlanData.length) }} dari {{ filteredMasterPlanData.length }}</template>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button @click="masterPage--" :disabled="masterPage <= 1"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-left text-body-sm"></i></button>
                                    <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ masterPage }} / {{ masterTotalPages }}</span>
                                    <button @click="masterPage++" :disabled="masterPage >= masterTotalPages"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-right text-body-sm"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
@endverbatim
