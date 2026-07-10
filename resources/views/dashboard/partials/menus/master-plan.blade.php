@verbatim
<!-- Master Plan View -->
                    <div v-if="activeTab === 'master'" class="space-y-6 animate-fadeIn pb-10">
                        <!-- Summary cards -->
                        <div class="space-y-3">
                            <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                                <div v-for="c in masterSummary.cards" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                                    <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-700"><i :class="['fa-solid', c.icon, 'text-[120px]']"></i></div>
                                    <p :class="['text-overline font-bold uppercase tracking-widest mb-3', c.color]">{{ c.label }}</p>
                                    <div class="flex items-baseline gap-2">
                                        <span class="dashboard-summary-value">{{ c.value }}</span>
                                        <span v-if="c.unit" :class="['dashboard-summary-unit', c.unitColor]">{{ c.unit }}</span>
                                    </div>
                                    <p :class="['text-body-sm font-bold mt-3', c.subColor]">{{ c.sub }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="md:hidden section-card section-card-shell">
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
                                                class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-red-500"></i>
                                        </button>
                                    </div>
                                    <div class="toolbar-actions">
                                        <button @click="exportExcel"
                                            class="secondary-cta-button secondary-cta-success active:scale-95"><i
                                                class="fa-solid fa-file-excel"></i><span
                                                class="ml-1">Excel</span></button>
                                        <button @click="exportPdf"
                                            class="secondary-cta-button secondary-cta-danger active:scale-95"><i
                                                class="fa-solid fa-file-pdf"></i><span
                                                class="ml-1">PDF</span></button>
                                        <button @click="openCreateModal"
                                            class="primary-cta-button primary-cta-button--accent active:scale-95">
                                            <i class="fa-solid fa-plus mr-2"></i>Tambah Plan </button>
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
                                                class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-red-500"></i>
                                        </button>
                                    </div>
                                    <div class="toolbar-actions">
                                        <button @click="exportExcel"
                                            class="secondary-cta-button secondary-cta-success active:scale-95"><i
                                                class="fa-solid fa-file-excel"></i><span
                                                class="ml-1">Excel</span></button>
                                        <button @click="exportPdf"
                                            class="secondary-cta-button secondary-cta-danger active:scale-95"><i
                                                class="fa-solid fa-file-pdf"></i><span
                                                class="ml-1">PDF</span></button>
                                        <button @click="openCreateModal"
                                            class="primary-cta-button primary-cta-button--accent active:scale-95">
                                            <i class="fa-solid fa-plus mr-2"></i>Tambah Plan </button>
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[1480px] table-fixed text-body-sm text-left border-collapse">
                                    <thead>
                                        <tr>
                                            <th
                                                class="px-4 py-4 text-center text-body-sm font-bold uppercase tracking-widest text-slate-400 w-[56px]">
                                                #</th>
                                            <th
                                                class="px-4 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold border-b border-slate-50 text-left w-[96px]">
                                                Aksi</th>
                                            <th
                                                class="px-4 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold border-b border-slate-50 text-left w-[24%]">
                                                Judul</th>
                                            <th
                                                class="px-4 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold border-b border-slate-50 text-left w-[110px]">
                                                Format</th>
                                            <th
                                                class="px-4 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold border-b border-slate-50 text-left w-[120px]">
                                                Editor</th>
                                            <th
                                                class="px-4 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold border-b border-slate-50 text-left w-[150px]">
                                                Talent</th>
                                            <th
                                                class="px-4 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold border-b border-slate-50 text-left w-[116px]">
                                                Link</th>
                                            <th
                                                class="px-4 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold border-b border-slate-50 text-left w-[16%]">
                                                Platform</th>
                                            <th
                                                class="px-4 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold border-b border-slate-50 text-left w-[120px]">
                                                Status</th>
                                            <th
                                                class="px-4 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold border-b border-slate-50 text-left w-[110px]">
                                                Tgl Target</th>
                                            <th
                                                class="px-4 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold border-b border-slate-50 text-center w-[84px]">
                                                Skrip</th>
                                            <th
                                                class="px-4 py-4 text-body-sm uppercase tracking-widest text-slate-400 font-bold border-b border-slate-50 text-center w-[92px]">
                                                Caption</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <tr v-for="(item, idx) in pagedMasterPlanData" :key="item.ID"
                                            class="group hover:bg-slate-50/30 transition-colors">
                                            <td class="px-4 py-4 text-center text-body-sm font-bold text-slate-400 tabular-nums">{{ idx + 1 }}</td>
                                            <td class="px-4 py-5 text-left">
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
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-lg bg-slate-100 text-slate-600 text-body-sm font-bold tracking-wide whitespace-nowrap">
                                                    {{ item.Format_Konten }} </span>
                                            </td>
                                            <td class="px-4 py-5 text-left">
                                                <div class="flex items-center gap-2">
                                                    <div
                                                        class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-overline font-bold text-slate-600 flex-shrink-0">
                                                        {{ (item.Editor || 'U')[0] }}</div>
                                                    <div
                                                        class="text-body text-slate-700 font-semibold truncate max-w-[80px]">
                                                        {{ item.Editor }}</div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-5 text-left">
                                                <div class="text-body text-slate-600 font-medium break-words">
                                                    {{ item.TalentList && item.TalentList.length ? item.TalentList.join(', ') : '-' }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-5 text-left">
                                                <div class="flex items-center gap-1.5">
                                                    <a v-if="item.Link_Drive" :href="item.Link_Drive" target="_blank" rel="noopener noreferrer"
                                                        class="table-action-button table-action-compact table-action-link"
                                                        title="Link Drive" aria-label="Link Drive">
                                                        <i class="fa-solid fa-folder-open text-body-sm"></i>
                                                    </a>
                                                    <template v-for="(detail, plat) in item.Distribution_Meta"
                                                        :key="plat">
                                                        <a v-if="detail && typeof detail === 'object' && detail.link && typeof detail.link === 'string'"
                                                            :href="detail.link" target="_blank" rel="noopener noreferrer"
                                                            class="table-action-button table-action-compact table-action-link"
                                                            :title="plat">
                                                            <i :class="getPlatformIcon(plat) + ' text-body-sm'"></i>
                                                        </a>
                                                    </template>
                                                    <span v-if="!hasAnyMasterLink(item)"
                                                        class="text-body-sm text-slate-300">-</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-5 text-left">
                                                <div class="flex flex-wrap gap-1">
                                                    <span v-for="plat in (item.Platforms || '').split(',')" :key="plat"
                                                        class="text-overline text-slate-400 border border-slate-100 px-1.5 py-0.5 rounded font-medium uppercase tracking-wider">
                                                        {{ (plat || '').trim() }} </span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-5 text-left">
                                                <span
                                                    :class="['inline-flex items-center px-2.5 py-1 rounded-full text-overline font-bold uppercase tracking-wider whitespace-nowrap', getStatusColor(item.Status)]">
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
                                                    :class="['text-body font-bold', (item.Skrip === 'Ada' || item.Skrip === 'Ya') ? 'text-emerald-500' : 'text-slate-300']">{{ (item.Skrip === 'Ada' || item.Skrip === 'Ya') ? 'Ada' : '-' }}</span>
                                            </td>
                                            <td class="px-4 py-5 text-center">
                                                <span
                                                    :class="['text-body font-bold', (item.Caption === 'Ada' || item.Caption === 'Ya') ? 'text-emerald-500' : 'text-slate-300']">{{ (item.Caption === 'Ada' || item.Caption === 'Ya') ? 'Ada' : '-' }}</span>
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
                                                        class="text-slate-400 text-body font-medium uppercase tracking-widest">
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
                                        :class="['px-3 py-1.5 rounded-full text-overline font-bold uppercase tracking-wider', getStatusColor(item.Status)]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current mr-2 opacity-70"></span>
                                        {{ item.Status }}
                                    </span>
                                    <div class="text-body-sm text-slate-400 font-bold uppercase tracking-widest">{{ formatShortDate(item.Tanggal_Rencana) }}</div>
                                </div>
                                <div>
                                    <div class="mobile-data-card__title">{{ item.Judul }}</div>
                                    <div class="mobile-data-card__meta mt-3">
                                        <span
                                            class="px-2 py-1 rounded-lg bg-slate-50 text-slate-500 text-overline font-bold border border-slate-100">
                                            {{ item.Format_Konten }} </span>
                                        <div
                                            class="flex items-center gap-1.5 px-2 py-1 rounded-lg bg-slate-50 border border-slate-100">
                                            <div
                                                class="w-4 h-4 rounded-full bg-slate-200 flex items-center justify-center text-overline-xs font-bold text-slate-600">
                                                {{ (item.Editor || 'U')[0] }}</div>
                                            <span class="text-overline font-bold text-slate-500">{{ item.Editor }}</span>
                                        </div>
                                    </div>
                                    <div class="mobile-data-card__meta mt-2">
                                        <span v-for="plat in (item.Platforms || '').split(',')" :key="plat"
                                            class="text-overline text-slate-400 border border-slate-100 px-2 py-0.5 rounded-lg font-bold uppercase">
                                            {{ (plat || '').trim() }} </span>
                                    </div>
                                </div>
                                <div class="mobile-data-card__summary">
                                    <div>
                                        <div class="type-body-sm text-slate-400 uppercase font-bold tracking-widest">Editor
                                        </div>
                                        <div class="type-body font-bold text-slate-700">{{ item.Editor || '-' }}</div>
                                    </div>
                                    <div>
                                        <div class="type-body-sm text-slate-400 uppercase font-bold tracking-widest">Asset
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
                                        <template v-for="(detail, plat) in item.Distribution_Meta" :key="plat">
                                            <a v-if="detail && typeof detail === 'object' && detail.link && typeof detail.link === 'string'"
                                                :href="detail.link" target="_blank" rel="noopener noreferrer"
                                                class="table-action-button table-action-compact table-action-link"
                                                :title="plat" :aria-label="plat">
                                                <i :class="getPlatformIcon(plat) + ' text-body-sm'"></i>
                                            </a>
                                        </template>
                                        <span v-if="!hasAnyMasterLink(item)" class="text-body-sm text-slate-300">-</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button @click="openEditModal(item)"
                                            class="table-action-button table-action-compact active:scale-90"><i
                                                class="fa-solid fa-pen-to-square text-body"></i></button>
                                        <button @click="deleteMasterPlan(item.ID)"
                                            class="table-action-button table-action-compact table-action-danger active:scale-90"><i
                                                class="fa-solid fa-trash-can text-body"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div v-if="filteredMasterPlanData.length === 0"
                                class="bg-white radius-panel border border-slate-100 p-10 text-center">
                                <div class="text-slate-400 text-body-sm font-medium uppercase tracking-widest">Data
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
