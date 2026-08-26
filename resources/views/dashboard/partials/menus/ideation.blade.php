@verbatim
<div v-if="activeTab === 'ideation'" class="space-y-4 animate-fadeIn">
    <section class="section-card section-card-body">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 md:gap-5">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-amber text-light flex items-center justify-center border border-amber">
                    <i class="fa-solid fa-lightbulb text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Ideation Board</h2>
                    <p class="type-body text-slate-500">Brainstorming ide konten dan tracking
                        status produksi</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-3">
                <div class="segmented-control segmented-control--ios segmented-control--equal" :data-index="ideationViewMode === 'list' ? 1 : 0">
                    <button @click="ideationViewMode = 'board'"
                        :class="['segmented-control__item', ideationViewMode === 'board' ? 'segmented-control__item--active' : '']">Board</button>
                    <button @click="ideationViewMode = 'list'"
                        :class="['segmented-control__item', ideationViewMode === 'list' ? 'segmented-control__item--active' : '']">List</button>
                </div>
                <div class="relative flex-1 sm:w-64">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                    <input id="ideation-search" name="ideation_search" v-model="masterSearch" type="text" placeholder="Cari ide..."
                        autocomplete="off" aria-label="Cari ide"
                        class="form-input-search" />
                </div>
                <div class="toolbar-actions">
                    <button @click="openCreateModal"
                        class="primary-cta-button primary-cta-button--accent active:scale-95">
                        <i class="fa-solid fa-plus mr-2"></i>Buat Ide </button>
                    <button @click="exportExcel"
                        class="primary-cta-button primary-cta-button--success active:scale-95"><i
                            class="fa-solid fa-file-excel"></i><span
                            class="ml-1">Excel</span></button>
                </div>
            </div>
        </div>
    </section>

    <div v-if="ideationViewMode === 'board'" class="space-y-4">
        <div class="md:hidden">
            <div class="segmented-control segmented-control--ios segmented-control--equal w-full"
                data-count="3"
                :data-index="ideationBoardMobileTab === 'In Progress' ? 1 : ideationBoardMobileTab === 'Done' ? 2 : 0">
                <button @click="ideationBoardMobileTab = ideationDraftLabel"
                    :class="['segmented-control__item flex-1 text-center', ideationBoardMobileTab === ideationDraftLabel ? 'segmented-control__item--active' : '']">{{ ideationDraftLabel }}</button>
                <button @click="ideationBoardMobileTab = 'In Progress'"
                    :class="['segmented-control__item flex-1 text-center', ideationBoardMobileTab === 'In Progress' ? 'segmented-control__item--active' : '']">In
                    Progress</button>
                <button @click="ideationBoardMobileTab = 'Done'"
                    :class="['segmented-control__item flex-1 text-center', ideationBoardMobileTab === 'Done' ? 'segmented-control__item--active' : '']">Done</button>
            </div>
        </div>
        <div class="ideation-kanban-board grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
            <div v-for="(items, status) in kanbanBuckets" :key="status"
                v-show="status === ideationBoardMobileTab || !isMobileViewport"
                class="bg-white radius-panel border border-slate-100 p-5 flex flex-col min-h-[400px]">
                <div class="flex items-center justify-between px-2 mb-5">
                    <div class="flex items-center gap-2">
                        <div
                            :class="['w-2 h-2 rounded-full', status === ideationDraftLabel ? 'bg-amber' : status === 'In Progress' ? 'bg-amber' : 'bg-success']">
                        </div>
                        <h3 class="text-body font-bold text-slate-700 uppercase">
                            {{ status }}</h3>
                        <span
                            class="px-2 py-0.5 rounded-full bg-secondary text-light text-body-sm font-bold">{{ items.length }}</span>
                    </div>
                </div>

                <div class="space-y-3 flex-1">
                    <div v-for="item in pagedKanbanBuckets[status]" :key="item.ID"
                        @click="openEditModal(item)"
                        class="ideation-kanban-card bg-white p-3 radius-card border border-slate-100 hover:border-ppp-accent/20 transition-all cursor-pointer group animate-fadeIn">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <span
                                :class="['px-1.5 py-0.5 rounded-md text-overline-xs font-bold uppercase', getIdeationTypeTone(item).chip]">
                                {{ item.Format_Konten }}
                            </span>
                            <div class="flex items-center gap-1.5">
                                <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-overline font-bold text-slate-600 flex-shrink-0 overflow-hidden"
                                    :title="personDisplayName(item.Editor)">
                                    <img v-if="resolveUserAvatarUrl(item.Editor)"
                                        :src="resolveAvatarUrl(resolveUserAvatarUrl(item.Editor))"
                                        class="w-full h-full object-cover" alt="Foto Editor"
                                        @error="markMasterPlanEditorAvatarFailed(item.Editor)" />
                                    <span v-else>{{ masterPersonInitials(item.Editor) }}</span>
                                </div>
                                <button @click.stop="deleteMasterPlan(item.ID)"
                                    class="w-5 h-5 rounded-full bg-danger border border-danger text-light hover:bg-danger hover:text-white hover:border-danger transition-all flex items-center justify-center">
                                    <i class="fa-solid fa-trash-can text-overline-xs"></i>
                                </button>
                            </div>
                        </div>
                        <div
                            :class="['type-body-sm font-medium text-slate-800 leading-tight mb-2 group-hover:text-ppp-accent transition-colors', (item.Status||'').toLowerCase() === 'done' || (item.Status||'').toLowerCase() === 'published' ? 'line-through opacity-60' : '']">
                            {{ item.Judul }}</div>
                        <div class="flex items-center gap-1.5 mb-2">
                            <span
                                class="px-1.5 py-0.5 rounded-md bg-slate-50 border border-slate-100 text-slate-500 text-overline-xs font-medium uppercase">
                                {{ getIdeaAgeLabel(item) }}
                            </span>
                        </div>
                        <div v-if="item.Skrip === 'Ada' || item.Skrip === 'Ya' || item.Caption === 'Ada' || item.Caption === 'Ya'"
                            class="flex items-center gap-1.5 pt-2 border-t border-slate-50">
                            <i v-if="item.Skrip === 'Ada' || item.Skrip === 'Ya'"
                                class="fa-solid fa-file-lines text-success text-body-sm"
                                title="Skrip Ada"></i>
                            <i v-if="item.Caption === 'Ada' || item.Caption === 'Ya'"
                                class="fa-solid fa-closed-captioning text-success text-body-sm"
                                title="Caption Ada"></i>
                        </div>
                    </div>

                    <div v-if="items.length === 0"
                        class="flex flex-col items-center justify-center h-full min-h-[150px] opacity-40">
                        <div
                            class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center mb-3">
                            <i class="fa-solid fa-box-open text-xl text-slate-300"></i>
                        </div>
                        <div class="text-body-sm font-bold uppercase text-slate-400">
                            Belum ada ide</div>
                    </div>
                </div>
                <div v-if="items.length > 10"
                    class="mt-3 pt-3 border-t border-slate-100/70 flex items-center justify-between">
                    <span class="text-overline text-slate-400 font-bold">{{ (boardPages[status]-1)*10+1 }}-{{ Math.min(boardPages[status]*10, items.length) }} / {{ items.length }}</span>
                    <div class="flex items-center gap-1">
                        <button @click.stop="boardPages[status] = Math.max(1, boardPages[status]-1)"
                            :disabled="boardPages[status] <= 1" aria-label="Kolom sebelumnya"
                            class="w-6 h-6 rounded-md flex items-center justify-center text-slate-400 hover:bg-white transition disabled:opacity-30"><i
                                class="fa-solid fa-chevron-left text-overline"></i></button>
                        <span class="px-2 text-overline font-bold text-ppp-accent">{{ boardPages[status] }}/{{ Math.ceil(items.length/10) }}</span>
                        <button
                            @click.stop="boardPages[status] = Math.min(Math.ceil(items.length/10), boardPages[status]+1)"
                            :disabled="boardPages[status] >= Math.ceil(items.length/10)"
                            aria-label="Kolom berikutnya"
                            class="w-6 h-6 rounded-md flex items-center justify-center text-slate-400 hover:bg-white transition disabled:opacity-30"><i
                                class="fa-solid fa-chevron-right text-overline"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div v-else
        class="md:bg-white md:radius-panel md:border md:border-slate-100 md:overflow-hidden">
        <div class="md:hidden space-y-3 p-3">
            <div v-for="(item, idx) in pagedMasterPlanData" :key="item.ID" @click="openEditModal(item)"
                class="stat-card mobile-record-card mobile-data-card cursor-pointer hover:bg-slate-50 transition-all motion-stagger-item"
                :style="getStaggerStyle(idx)">
                <div class="mobile-data-card__header">
                    <div
                        :class="['mobile-data-card__title', (item.Status||'').toLowerCase() === 'done' || (item.Status||'').toLowerCase() === 'published' ? 'line-through opacity-60' : '']">
                        {{ item.Judul }}</div>
                    <span
                        :class="['px-2 py-0.5 rounded-lg text-overline font-bold uppercase', getStatusColor(item.Status)]">{{ item.Status }}</span>
                </div>
                <div class="mobile-data-card__meta">
                    <span
                        class="px-2 py-0.5 rounded-lg bg-secondary text-light text-overline font-bold">{{ item.Format_Konten }}</span>
                </div>
                <div class="mobile-data-card__summary">
                    <div>
                        <div class="type-body-sm text-slate-400 uppercase">Editor</div>
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
                        <div class="type-body-sm text-slate-400 uppercase">Status</div>
                        <div class="type-body font-bold text-slate-600">{{ item.Status || '-' }}
                        </div>
                    </div>
                </div>
                <div class="mobile-data-card__actions">
                    <div class="type-body-sm text-slate-400">{{ formatShortDate(item.Tanggal_Rencana) }}</div>
                    <div class="flex items-center gap-2">
                        <button @click.stop="openEditModal(item)"
                            class="table-action-button table-action-compact" title="Edit" aria-label="Edit"><i
                                class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                        <button @click.stop="deleteMasterPlan(item.ID)"
                            class="table-action-button table-action-compact table-action-danger" title="Hapus" aria-label="Hapus"><i
                                class="fa-solid fa-trash-can text-body-sm"></i></button>
                    </div>
                </div>
            </div>
            <div v-if="filteredMasterPlanData.length === 0"
                class="table-empty-state text-slate-400 text-body uppercase">
                Data
                Kosong</div>
        </div>

        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-body-sm text-left border-collapse">
                <thead>
                    <tr class="table-header-row">
                        <th class="table-header-cell">
                            Aksi</th>
                        <th class="table-header-cell">
                            Judul & Headline</th>
                        <th class="table-header-cell">
                            Format</th>
                        <th class="table-header-cell">
                            Responsible</th>
                        <th class="table-header-cell text-center">
                            Asset</th>
                        <th class="table-header-cell">
                            Platforms</th>
                        <th class="table-header-cell text-center">
                            State</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <tr v-for="item in pagedMasterPlanData" :key="item.ID"
                        class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-2">
                                <button @click="openEditModal(item)"
                                    class="table-action-button table-action-compact" title="Edit" aria-label="Edit"><i
                                        class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                                <button @click="deleteMasterPlan(item.ID)"
                                    class="table-action-button table-action-compact table-action-danger" title="Hapus" aria-label="Hapus"><i
                                        class="fa-solid fa-trash-can text-body-sm"></i></button>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div @click="openEditModal(item)"
                                :class="['text-body-sm font-bold text-slate-800 cursor-pointer hover:text-ppp-accent', (item.Status||'').toLowerCase() === 'done' || (item.Status||'').toLowerCase() === 'published' ? 'line-through opacity-60' : '']">
                                {{ item.Judul }}</div>
                        </td>
                        <td class="px-6 py-5">
                            <span
                                class="px-2 py-1 rounded-lg bg-secondary text-light text-overline font-bold uppercase">{{ item.Format_Konten }}</span>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-overline font-bold text-slate-600 flex-shrink-0 overflow-hidden">
                                    <img v-if="resolveUserAvatarUrl(item.Editor)"
                                        :src="resolveAvatarUrl(resolveUserAvatarUrl(item.Editor))"
                                        class="w-full h-full object-cover" alt="Foto Editor"
                                        @error="markMasterPlanEditorAvatarFailed(item.Editor)" />
                                    <span v-else>{{ masterPersonInitials(item.Editor) }}</span>
                                </div>
                                <span class="text-body text-slate-700 font-semibold truncate max-w-[80px]">{{ personDisplayName(item.Editor) }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <i v-if="item.Skrip === 'Ada' || item.Skrip === 'Ya'"
                                    class="fa-solid fa-file-lines text-success text-body-sm"
                                    title="Skrip Ada"></i>
                                <i v-else
                                    class="fa-solid fa-file-lines text-slate-200 text-body-sm"></i>
                                <i v-if="item.Caption === 'Ada' || item.Caption === 'Ya'"
                                    class="fa-solid fa-closed-captioning text-success text-body-sm"
                                    title="Caption Ada"></i>
                                <i v-else
                                    class="fa-solid fa-closed-captioning text-slate-200 text-body-sm"></i>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex flex-col items-start gap-1.5 max-w-[160px]">
                                <span v-for="plat in (item.Platforms || '').split(',')" :key="plat"
                                    class="flex items-center gap-2">
                                    <i :class="getPlatformIcon(plat) + ' text-body text-slate-400'"></i>
                                    <span class="text-body font-bold text-slate-700">{{ platformDisplayName(plat) }}</span>
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <span
                                :class="['px-2.5 py-1 rounded-full text-overline font-bold uppercase', getStatusColor(item.Status)]">{{ item.Status }}</span>
                        </td>
                    </tr>
                    <tr v-if="filteredMasterPlanData.length === 0">
                        <td colspan="7"
                            class="px-6 py-20 text-center text-slate-400 text-body uppercase">
                            Data Kosong</td>
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
