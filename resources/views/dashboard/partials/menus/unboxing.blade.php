@verbatim
<!-- Unboxing View -->
                    <div v-if="activeTab === 'unboxing' && !tabDataLoaded['unboxing']"
                        class="space-y-4 animate-fadeIn animate-pulse">
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
                        class="space-y-4 animate-fadeIn">
                        <!-- Summary cards -->
                        <div class="space-y-3">
                            <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
                                <div v-for="c in unboxingSummary.cards.slice(0, 5)" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
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
                                        <input id="unboxing-search-mobile" name="unboxing_search_mobile" v-model="unboxingSearch" type="text" placeholder="Cari judul unboxing..." autocomplete="off" aria-label="Cari judul unboxing mobile" class="form-input-search" />
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
                                        <button @click="openUnboxingModal('create')" class="primary-cta-button primary-cta-button--accent active:scale-95"><i class="fa-solid fa-plus mr-2"></i>Tambah</button>
                                        <button @click="exportExcel" class="primary-cta-button primary-cta-button--success active:scale-95"><i class="fa-solid fa-file-excel"></i><span class="ml-1">Excel</span></button>
                                        <button @click="exportPdf" class="primary-cta-button primary-cta-button--danger active:scale-95"><i class="fa-solid fa-file-pdf"></i><span class="ml-1">PDF</span></button>
                                    </div>
                                </div>
                            </div>
                            <div v-if="filteredUnboxingData.length === 0"
                                class="bg-white radius-card border border-slate-100 p-10 text-center text-body text-slate-400">
                                Belum ada data unboxing
                            </div>
                            <div v-for="(row, idx) in pagedUnboxingData" :key="'ub-mobile-' + row.ID"
                                class="stat-card mobile-record-card mobile-data-card motion-stagger-item"
                                :style="getStaggerStyle(idx)">
                                <div class="mobile-data-card__header">
                                    <span
                                        :class="['px-2.5 py-1 rounded-full text-overline font-bold uppercase', row.Status ? getStatusColor(row.Status) : 'bg-secondary text-light']">
                                        {{ row.Status || '-' }}
                                    </span>
                                    <span class="type-body-sm text-slate-400 font-bold uppercase">
                                        {{ row.Upload_Date ? formatShortDate(row.Upload_Date) : '-' }}
                                    </span>
                                </div>
                                <div>
                                    <p class="mobile-data-card__title line-clamp-2">{{ row.Nama || '-' }}</p>
                                    <div class="mobile-data-card__meta mt-2">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-overline font-bold text-slate-600 flex-shrink-0 overflow-hidden">
                                                <img v-if="resolveUserAvatarUrl(row.Editor)"
                                                    :src="resolveAvatarUrl(resolveUserAvatarUrl(row.Editor))"
                                                    class="w-full h-full object-cover" alt="Foto Editor"
                                                    @error="markMasterPlanEditorAvatarFailed(row.Editor)" />
                                                <span v-else>{{ masterPersonInitials(row.Editor) }}</span>
                                            </div>
                                            <div class="text-body text-slate-700 font-semibold truncate max-w-[80px]">{{ personDisplayName(row.Editor) }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mobile-data-card__actions">
                                    <a v-if="row.Link" :href="row.Link" target="_blank" rel="noopener noreferrer"
                                        class="table-action-button table-action-compact table-action-link"
                                        title="Link Unboxing" aria-label="Link Unboxing">
                                        <i class="fa-solid fa-link text-body-sm"></i>
                                    </a>
                                    <div class="flex items-center gap-2 ml-auto">
                                        <button @click="openUnboxingModal('edit', row)"
                                            class="table-action-button table-action-compact" title="Edit" aria-label="Edit"><i
                                                class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                                        <button @click="deleteUnboxing(row.ID)"
                                            class="table-action-button table-action-compact table-action-danger" title="Hapus" aria-label="Hapus"><i
                                                class="fa-solid fa-trash-can text-body-sm"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-center gap-2 py-2">
                                <button @click="unboxingPage--" :disabled="unboxingPage <= 1"
                                    aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-left text-body-sm"></i></button>
                                <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ unboxingPage }} / {{ unboxingTotalPages }}</span>
                                <button @click="unboxingPage++" :disabled="unboxingPage >= unboxingTotalPages"
                                    aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-right text-body-sm"></i></button>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="hidden md:block section-card section-card-shell">
                            <div class="table-toolbar-shell">
                                <div class="table-toolbar-shell__left">
                                    <div class="relative">
                                        <i
                                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                                        <input id="unboxing-search-desktop" name="unboxing_search_desktop" v-model="unboxingSearch" type="text" placeholder="Cari judul unboxing..."
                                            autocomplete="off" aria-label="Cari judul unboxing desktop"
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
                                    <div class="toolbar-actions">
                                        <button @click="openUnboxingModal('create')"
                                            class="primary-cta-button primary-cta-button--accent active:scale-95">
                                            <i class="fa-solid fa-plus mr-2"></i>Tambah
                                        </button>
                                        <button @click="exportExcel"
                                            class="primary-cta-button primary-cta-button--success active:scale-95"><i
                                                class="fa-solid fa-file-excel"></i><span
                                                class="ml-1">Excel</span></button>
                                        <button @click="exportPdf"
                                            class="primary-cta-button primary-cta-button--danger active:scale-95"><i
                                                class="fa-solid fa-file-pdf"></i><span
                                                class="ml-1">PDF</span></button>
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[1040px] table-fixed text-body-sm text-left border-collapse">
                                    <thead>
                                        <tr class="table-header-row">
                                            <th class="table-header-cell table-header-index table-freeze-index">#</th>
                                            <th class="table-header-cell table-header-action table-freeze-action">Aksi</th>
                                            <th class="table-header-cell text-left w-[42%]">Judul</th>
                                            <th class="table-header-cell text-left w-[140px]">Editor</th>
                                            <th class="table-header-cell text-center w-[140px]">Status</th>
                                            <th class="table-header-cell text-left w-[120px]">Tanggal Upload</th>
                                            <th class="table-header-cell text-right w-[160px]">Link</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(row, idx) in pagedUnboxingData" :key="row.ID"
                                            class="border-b border-slate-50 hover:bg-slate-50 transition-colors group">
                                            <td class="px-4 py-3 text-center text-body-sm font-bold text-slate-400 tabular-nums table-freeze-index">{{ idx + 1 }}</td>
                                            <td class="px-4 py-3 text-left table-freeze-action">
                                                <div class="flex items-center gap-2">
                                                    <button @click="openUnboxingModal('edit', row)"
                                                        class="table-action-button table-action-compact" title="Edit" aria-label="Edit">
                                                        <i class="fa-solid fa-pen-to-square text-body-sm"></i>
                                                    </button>
                                                    <button @click="deleteUnboxing(row.ID)"
                                                        class="table-action-button table-action-compact table-action-danger" title="Hapus" aria-label="Hapus">
                                                        <i class="fa-solid fa-trash-can text-body-sm"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-left">
                                                <p
                                                    class="text-body-sm font-semibold text-slate-900 uppercase break-words">
                                                    {{ row.Nama }}</p>
                                            </td>
                                            <td class="px-4 py-3 text-left">
                                                <div class="flex items-center gap-2">
                                                    <div
                                                        class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-overline font-bold text-slate-600 flex-shrink-0 overflow-hidden">
                                                        <img v-if="resolveUserAvatarUrl(row.Editor)"
                                                            :src="resolveAvatarUrl(resolveUserAvatarUrl(row.Editor))"
                                                            class="w-full h-full object-cover" alt="Foto Editor"
                                                            @error="markMasterPlanEditorAvatarFailed(row.Editor)" />
                                                        <span v-else>{{ masterPersonInitials(row.Editor) }}</span>
                                                    </div>
                                                    <div class="text-body text-slate-700 font-semibold truncate max-w-[80px]">{{ personDisplayName(row.Editor) }}</div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span v-if="row.Status" :class="getStatusColor(row.Status)"
                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold whitespace-nowrap">{{ row.Status }}</span>
                                                <span v-else class="text-body text-slate-400">-</span>
                                            </td>
                                            <td class="px-4 py-3 text-left text-body text-slate-500 whitespace-nowrap">{{ formatShortDate(row.Upload_Date) }}</td>
                                            <td class="px-4 py-3 text-right">
                                                <a v-if="row.Link" :href="row.Link" target="_blank" rel="noopener noreferrer"
                                                    class="table-action-button table-action-compact table-action-link"
                                                    title="Link Unboxing" aria-label="Link Unboxing">
                                                    <i class="fa-solid fa-link text-body-sm"></i>
                                                </a>
                                                <span v-else class="text-slate-400 text-body">-</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div
                                class="table-pager-bar">
                                <div class="text-body-sm text-slate-400 font-medium">
                                    <template v-if="filteredUnboxingData.length > 0">{{ (unboxingPage - 1) * 15 + 1 }}-{{ Math.min(unboxingPage * 15, filteredUnboxingData.length) }} dari {{ filteredUnboxingData.length }} data</template>
                                    <template v-else>0 data</template>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button @click="unboxingPage--" :disabled="unboxingPage <= 1"
                                        aria-label="Halaman sebelumnya"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-left text-body-sm"></i></button>
                                    <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ unboxingPage }} / {{ unboxingTotalPages }}</span>
                                    <button @click="unboxingPage++" :disabled="unboxingPage >= unboxingTotalPages"
                                        aria-label="Halaman berikutnya"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-right text-body-sm"></i></button>
                                </div>
                            </div>

                            <!-- Empty state -->
                            <div v-if="unboxingData.length === 0"
                                class="flex flex-col items-center justify-center py-20 text-slate-400">
                                <i class="fa-solid fa-box-open text-4xl mb-4 opacity-20"></i>
                                <p class="text-body font-bold uppercase">Belum ada data unboxing</p>
                            </div>
                        </div>
                    </div>
@endverbatim
