@verbatim
<div v-if="activeTab === 'ideation'" class="space-y-6 animate-fadeIn pb-10">
    <div class="space-y-3">
        <div class="dashboard-summary-grid-compact grid grid-cols-3 md:grid-cols-4 gap-3 md:gap-4">
            <div v-for="c in ideationSummary.cards" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
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
                    class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
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
                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                    <input v-model="masterSearch" type="text" placeholder="Cari ide..."
                        class="form-input-search" />
                </div>
                <div class="toolbar-actions">
                    <button @click="openCreateModal"
                        class="primary-cta-button primary-cta-button--accent active:scale-95">
                        <i class="fa-solid fa-plus mr-2"></i>Buat Ide </button>
                    <button @click="exportExcel"
                        class="secondary-cta-button secondary-cta-success active:scale-95"><i
                            class="fa-solid fa-file-excel"></i><span
                            class="ml-1">Excel</span></button>
                </div>
            </div>
        </div>
        <div v-if="ideationSummary.chips.length" class="chips-grid mt-4 pt-4 border-t border-slate-100">
            <span v-for="ch in ideationSummary.chips" :key="ch.label" :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold', ch.cls]">{{ ch.label }} <span class="opacity-50">&middot;</span> {{ ch.n }}</span>
        </div>
    </section>

    <div v-if="ideationViewMode === 'board'" class="space-y-4">
        <div class="md:hidden">
            <div class="segmented-control w-full">
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
                            :class="['w-2 h-2 rounded-full shadow-sm', status === ideationDraftLabel ? 'bg-blue-500' : status === 'In Progress' ? 'bg-amber-500' : 'bg-emerald-500']">
                        </div>
                        <h3 class="text-[11px] font-bold text-slate-700 uppercase tracking-widest">
                            {{ status }}</h3>
                        <span
                            class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[10px] font-bold">{{ items.length }}</span>
                    </div>
                </div>

                <div class="space-y-3 flex-1">
                    <div v-for="item in pagedKanbanBuckets[status]" :key="item.ID"
                        @click="openEditModal(item)"
                        class="bg-white p-4 radius-card border border-slate-100 shadow-sm hover:shadow-md hover:border-ppp-accent/20 transition-all cursor-pointer group animate-fadeIn">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <span
                                :class="['px-2 py-0.5 rounded-lg text-[9px] font-bold uppercase', getIdeationTypeTone(item).chip]">
                                {{ item.Format_Konten }}
                            </span>
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-slate-100 border-2 border-white flex items-center justify-center text-[8px] font-bold text-slate-600"
                                    :title="item.Editor">
                                    {{ (item.Editor || 'U')[0] }}
                                </div>
                                <button @click.stop="deleteMasterPlan(item.ID)"
                                    class="w-6 h-6 rounded-full bg-rose-50 border border-rose-100 text-rose-400 hover:bg-rose-500 hover:text-white hover:border-rose-500 transition-all flex items-center justify-center">
                                    <i class="fa-solid fa-trash text-[8px]"></i>
                                </button>
                            </div>
                        </div>
                        <div
                            :class="['text-[12px] font-bold text-slate-800 leading-tight mb-3 group-hover:text-ppp-accent transition-colors', (item.Status||'').toLowerCase() === 'done' || (item.Status||'').toLowerCase() === 'published' ? 'line-through opacity-60' : '']">
                            {{ item.Judul }}</div>
                        <div class="flex items-center gap-1.5 mb-3">
                            <span
                                class="px-2 py-0.5 rounded-full bg-slate-50 border border-slate-100 text-slate-500 text-[8px] font-bold uppercase tracking-wide">
                                {{ getIdeaAgeLabel(item) }}
                            </span>
                        </div>
                        <div v-if="item.Skrip === 'Ada' || item.Skrip === 'Ya' || item.Caption === 'Ada' || item.Caption === 'Ya'"
                            class="flex items-center gap-2 pt-3 border-t border-slate-50">
                            <i v-if="item.Skrip === 'Ada' || item.Skrip === 'Ya'"
                                class="fa-solid fa-file-lines text-emerald-500 text-[10px]"
                                title="Skrip Ada"></i>
                            <i v-if="item.Caption === 'Ada' || item.Caption === 'Ya'"
                                class="fa-solid fa-closed-captioning text-emerald-500 text-[10px]"
                                title="Caption Ada"></i>
                        </div>
                    </div>

                    <div v-if="items.length === 0"
                        class="flex flex-col items-center justify-center h-full min-h-[150px] opacity-40">
                        <div
                            class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center mb-3">
                            <i class="fa-solid fa-box-open text-xl text-slate-300"></i>
                        </div>
                        <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                            Belum ada ide</div>
                    </div>
                </div>
                <div v-if="items.length > 10"
                    class="mt-3 pt-3 border-t border-slate-100/70 flex items-center justify-between">
                    <span class="text-[9px] text-slate-400 font-bold">{{ (boardPages[status]-1)*10+1 }}-{{ Math.min(boardPages[status]*10, items.length) }} / {{ items.length }}</span>
                    <div class="flex items-center gap-1">
                        <button @click.stop="boardPages[status] = Math.max(1, boardPages[status]-1)"
                            :disabled="boardPages[status] <= 1" aria-label="Kolom sebelumnya"
                            class="w-6 h-6 rounded-md flex items-center justify-center text-slate-400 hover:bg-white transition disabled:opacity-30"><i
                                class="fa-solid fa-chevron-left text-[9px]"></i></button>
                        <span class="px-2 text-[9px] font-bold text-ppp-accent">{{ boardPages[status] }}/{{ Math.ceil(items.length/10) }}</span>
                        <button
                            @click.stop="boardPages[status] = Math.min(Math.ceil(items.length/10), boardPages[status]+1)"
                            :disabled="boardPages[status] >= Math.ceil(items.length/10)"
                            aria-label="Kolom berikutnya"
                            class="w-6 h-6 rounded-md flex items-center justify-center text-slate-400 hover:bg-white transition disabled:opacity-30"><i
                                class="fa-solid fa-chevron-right text-[9px]"></i></button>
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
                        :class="['px-2 py-0.5 rounded-lg text-[9px] font-bold uppercase', getStatusColor(item.Status)]">{{ item.Status }}</span>
                </div>
                <div class="mobile-data-card__meta">
                    <span
                        class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-500 text-[9px] font-bold">{{ item.Format_Konten }}</span>
                </div>
                <div class="mobile-data-card__summary">
                    <div>
                        <div class="type-meta text-slate-400 uppercase">Editor</div>
                        <div class="type-body font-bold text-slate-600">{{ item.Editor || '-' }}
                        </div>
                    </div>
                    <div>
                        <div class="type-meta text-slate-400 uppercase">Status</div>
                        <div class="type-body font-bold text-slate-600">{{ item.Status || '-' }}
                        </div>
                    </div>
                </div>
                <div class="mobile-data-card__actions">
                    <div class="type-meta text-slate-400">{{ formatShortDate(item.Tanggal_Rencana) }}</div>
                    <div class="flex items-center gap-2">
                        <button @click.stop="openEditModal(item)"
                            class="table-action-button table-action-compact"><i
                                class="fa-solid fa-pen text-[10px]"></i></button>
                        <button @click.stop="deleteMasterPlan(item.ID)"
                            class="table-action-button table-action-compact table-action-danger"><i
                                class="fa-solid fa-trash text-[10px]"></i></button>
                    </div>
                </div>
            </div>
            <div v-if="filteredMasterPlanData.length === 0"
                class="table-empty-state text-slate-400 text-[11px] uppercase tracking-widest">
                Data
                Kosong</div>
        </div>

        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-[10.5px] text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th
                            class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold">
                            Aksi</th>
                        <th
                            class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold">
                            Judul & Headline</th>
                        <th
                            class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold">
                            Format</th>
                        <th
                            class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold">
                            Responsible</th>
                        <th
                            class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold text-center">
                            Asset</th>
                        <th
                            class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold">
                            Platforms</th>
                        <th
                            class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold text-center">
                            State</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <tr v-for="item in pagedMasterPlanData" :key="item.ID"
                        class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-2">
                                <button @click="openEditModal(item)"
                                    class="table-action-button table-action-compact"><i
                                        class="fa-solid fa-pen text-[10px]"></i></button>
                                <button @click="deleteMasterPlan(item.ID)"
                                    class="table-action-button table-action-compact table-action-danger"><i
                                        class="fa-solid fa-trash text-[10px]"></i></button>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div @click="openEditModal(item)"
                                :class="['text-[10.5px] font-bold text-slate-800 cursor-pointer hover:text-ppp-accent', (item.Status||'').toLowerCase() === 'done' || (item.Status||'').toLowerCase() === 'published' ? 'line-through opacity-60' : '']">
                                {{ item.Judul }}</div>
                        </td>
                        <td class="px-6 py-5">
                            <span
                                class="px-2 py-1 rounded-lg bg-slate-100 text-slate-600 text-[9px] font-bold uppercase">{{ item.Format_Konten }}</span>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-6 h-6 rounded-full bg-ppp-accent/10 text-ppp-accent flex items-center justify-center text-[9px] font-bold uppercase">
                                    {{ (item.Editor || 'U')[0] }}</div>
                                <span class="text-[11px] font-medium text-slate-600">{{ item.Editor }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <i v-if="item.Skrip === 'Ada' || item.Skrip === 'Ya'"
                                    class="fa-solid fa-file-lines text-emerald-500 text-[10px]"
                                    title="Skrip Ada"></i>
                                <i v-else
                                    class="fa-solid fa-file-lines text-slate-200 text-[10px]"></i>
                                <i v-if="item.Caption === 'Ada' || item.Caption === 'Ya'"
                                    class="fa-solid fa-closed-captioning text-emerald-500 text-[10px]"
                                    title="Caption Ada"></i>
                                <i v-else
                                    class="fa-solid fa-closed-captioning text-slate-200 text-[10px]"></i>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex flex-wrap gap-1 max-w-[120px]">
                                <span v-for="plat in (item.Platforms || '').split(',')" :key="plat"
                                    class="text-[8px] text-slate-400 border border-slate-100 px-1.5 py-0.5 rounded-md font-bold uppercase">{{ (plat || '').trim() }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <span
                                :class="['px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider', getStatusColor(item.Status)]">{{ item.Status }}</span>
                        </td>
                    </tr>
                    <tr v-if="filteredMasterPlanData.length === 0">
                        <td colspan="7"
                            class="px-6 py-20 text-center text-slate-400 text-[11px] uppercase tracking-widest">
                            Data Kosong</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div
            class="table-pager-bar">
            <div class="text-[10px] text-slate-400 font-medium">
                <template v-if="filteredMasterPlanData.length > 0">{{ (masterPage - 1) * 15 + 1 }}-{{ Math.min(masterPage * 15, filteredMasterPlanData.length) }} dari {{ filteredMasterPlanData.length }} data</template>
                <template v-else>0 data</template>
            </div>
            <div class="flex items-center gap-1">
                <button @click="masterPage--" :disabled="masterPage <= 1"
                    class="icon-utility-button icon-utility-bordered"><i
                        class="fa-solid fa-chevron-left text-[10px]"></i></button>
                <span class="px-3 text-[10px] font-bold text-ppp-accent">{{ masterPage }} / {{ masterTotalPages }}</span>
                <button @click="masterPage++" :disabled="masterPage >= masterTotalPages"
                    class="icon-utility-button icon-utility-bordered"><i
                        class="fa-solid fa-chevron-right text-[10px]"></i></button>
            </div>
        </div>
    </div>
</div>
@endverbatim
