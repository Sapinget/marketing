@verbatim
<div v-if="activeTab === 'story'" class="space-y-6 animate-fadeIn pb-10">
    <div class="space-y-3">
        <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
            <div v-for="c in storySummary.cards.slice(0, 5)" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
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

    <section class="section-card section-card-body">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 md:gap-5">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-amber text-light flex items-center justify-center border border-amber">
                    <i class="fa-solid fa-clapperboard text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Jadwal Story</h2>
                    <p class="type-body text-slate-500">Pengaturan jadwal story harian Instagram &
                        TikTok</p>
                </div>
            </div>
            <div class="toolbar-actions">
                <button @click="openCreateStoryModal"
                    class="primary-cta-button primary-cta-button--accent active:scale-95">
                    <i class="fa-solid fa-plus mr-2"></i>Tambah Story
                </button>
            </div>
        </div>
    </section>

    <div class="segmented-control segmented-control--ios segmented-control--equal w-full md:w-auto mb-6" :data-index="storyTab === 'Genap' ? 1 : 0">
        <button @click="storyTab = 'Ganjil'"
            :class="['segmented-control__item flex-1 justify-center whitespace-nowrap', storyTab === 'Ganjil' ? 'segmented-control__item--active' : '']">Ganjil</button>
        <button @click="storyTab = 'Genap'"
            :class="['segmented-control__item flex-1 justify-center whitespace-nowrap', storyTab === 'Genap' ? 'segmented-control__item--active' : '']">Genap</button>
    </div>

    <div class="md:hidden grid grid-cols-1 gap-4">
        <div v-for="story in pagedStories" :key="story.ID"
            class="bg-white radius-card border border-slate-100 p-5 space-y-3 transition-all group">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-2">
                    <div
                        class="w-9 h-9 rounded-xl bg-amber text-light flex items-center justify-center border border-amber">
                        <i class="fa-solid fa-clock text-body"></i>
                    </div>
                    <div>
                        <span class="type-heading-sm font-bold text-slate-800">{{ story.Jam }}</span>
                        <div v-if="story.Tanggal"
                            class="text-overline text-slate-400 font-medium mt-0.5">{{ formatShortDate(story.Tanggal) }}</div>
                    </div>
                </div>
                <span v-if="story.Status"
                    class="text-overline font-bold uppercase px-2 py-1 rounded-full bg-secondary text-light">{{ story.Status }}</span>
            </div>
            <h4
                class="text-heading-sm font-bold text-slate-900 leading-tight group-hover:text-danger transition-colors uppercase">
                {{ story.Story_Schedule || story.Story }}</h4>
            <p v-if="story.Catatan"
                class="text-body text-slate-500 bg-slate-50 p-2 rounded-lg italic">{{ story.Catatan }}</p>
            <div class="flex items-center gap-2 pt-2 border-t border-slate-50">
                <a v-if="story.Link" :href="story.Link" target="_blank" rel="noopener noreferrer"
                    class="table-action-button table-action-compact table-action-link"
                    title="Link Story" aria-label="Link Story">
                    <i class="fa-solid fa-link text-body-sm"></i>
                </a>
                <div class="flex items-center gap-1.5 ml-auto">
                    <button @click="openEditStoryModal(story)"
                        class="table-action-button table-action-compact" title="Edit"
                        aria-label="Edit"><i class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                    <button @click="deleteStory(story.ID)"
                        class="table-action-button table-action-compact table-action-danger"
                        title="Hapus" aria-label="Hapus"><i
                            class="fa-solid fa-trash-can text-body-sm"></i></button>
                </div>
            </div>
        </div>
        <div v-if="filteredStories.length === 0"
            class="bg-white radius-card border border-dashed border-slate-200 p-16 flex flex-col items-center justify-center text-slate-400">
            <i class="fa-solid fa-clapperboard text-3xl mb-3 opacity-20"></i>
            <p class="text-body font-bold uppercase">Belum ada jadwal story ({{ storyTab }})</p>
        </div>
    </div>

    <div class="hidden md:block section-card section-card-shell">
        <div class="overflow-x-auto">
            <table class="w-full text-body-sm text-left border-collapse">
                <thead>
                    <tr class="table-header-row">
                        <th class="table-header-cell table-header-index table-freeze-index">
                            #</th>
                        <th class="table-header-cell table-header-action table-freeze-action">
                            Aksi</th>
                        <th class="table-header-cell">
                            Tanggal</th>
                        <th class="table-header-cell">
                            Jam</th>
                        <th class="table-header-cell">
                            Story</th>
                        <th class="table-header-cell text-center">
                            Status</th>
                        <th class="table-header-cell">
                            Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <tr v-for="(story, idx) in pagedStories" :key="story.ID"
                        class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 text-center text-body-sm font-bold text-slate-400 tabular-nums table-freeze-index">{{ idx + 1 }}</td>
                        <td class="px-6 py-4 table-freeze-action">
                            <div class="flex items-center gap-2">
                                <button @click="openEditStoryModal(story)" class="table-action-button table-action-compact"
                                    title="Edit" aria-label="Edit"><i
                                        class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                                <button @click="deleteStory(story.ID)"
                                    class="table-action-button table-action-compact table-action-danger" title="Hapus"
                                    aria-label="Hapus"><i class="fa-solid fa-trash-can text-body-sm"></i></button>
                            </div>
                        </td>
                        <td
                            class="px-6 py-4 text-body text-slate-500 font-medium whitespace-nowrap">
                            {{ story.Tanggal ? formatShortDate(story.Tanggal) : '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="type-heading-sm font-bold text-slate-800">{{ story.Jam }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div
                                class="text-body-sm font-semibold text-slate-800 uppercase leading-tight">
                                {{ story.Story_Schedule || story.Story }}</div>
                            <a v-if="story.Link" :href="story.Link" target="_blank" rel="noopener noreferrer"
                                class="table-action-button table-action-compact table-action-link mt-1"
                                title="Link Story" aria-label="Link Story">
                                <i class="fa-solid fa-link text-body-sm"></i>
                            </a>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span v-if="story.Status"
                                class="text-overline font-bold uppercase px-2.5 py-1 rounded-full bg-secondary text-light">{{ story.Status }}</span>
                            <span v-else class="text-slate-300 text-body">-</span>
                        </td>
                        <td class="px-6 py-4">
                            <p v-if="story.Catatan"
                                class="text-body text-slate-500 italic max-w-xs truncate">{{ story.Catatan }}</p>
                            <span v-else class="text-slate-300 text-body">-</span>
                        </td>
                    </tr>
                    <tr v-if="filteredStories.length === 0">
                        <td colspan="7"
                            class="px-6 py-20 text-center text-slate-400 text-body uppercase">
                            Belum ada jadwal story ({{ storyTab }})</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div
            class="table-pager-bar">
            <div class="text-body-sm text-slate-400 font-medium">
                <template v-if="filteredStories.length > 0">{{ (storyPage - 1) * 15 + 1 }}-{{ Math.min(storyPage * 15, filteredStories.length) }} dari {{ filteredStories.length }} data</template>
                <template v-else>0 data</template>
            </div>
            <div class="flex items-center gap-1">
                <button @click="storyPage--" :disabled="storyPage <= 1"
                    aria-label="Halaman sebelumnya"
                    class="icon-utility-button icon-utility-bordered"><i
                        class="fa-solid fa-chevron-left text-body-sm"></i></button>
                <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ storyPage }} / {{ storyTotalPages }}</span>
                <button @click="storyPage++" :disabled="storyPage >= storyTotalPages"
                    aria-label="Halaman berikutnya"
                    class="icon-utility-button icon-utility-bordered"><i
                        class="fa-solid fa-chevron-right text-body-sm"></i></button>
            </div>
        </div>
    </div>
</div>
@endverbatim
