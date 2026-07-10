@verbatim
<div v-if="activeTab === 'story'" class="space-y-6 animate-fadeIn pb-10">
    <div class="space-y-3">
        <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
            <div v-for="c in storySummary.cards" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
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
        <div v-if="storySummary.chips.length" class="chips-grid mt-4 pt-4 border-t border-slate-100">
            <span v-for="ch in storySummary.chips" :key="ch.label" :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold', ch.cls]">{{ ch.label }} <span class="opacity-50">&middot;</span> {{ ch.n }}</span>
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
                        class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                        <i class="fa-solid fa-clock text-[11px]"></i>
                    </div>
                    <div>
                        <span class="type-title font-bold text-slate-800">{{ story.Jam }}</span>
                        <div v-if="story.Tanggal"
                            class="text-[9px] text-slate-400 font-medium mt-0.5">{{ formatShortDate(story.Tanggal) }}</div>
                    </div>
                </div>
                <span v-if="story.Status"
                    class="text-[9px] font-bold uppercase tracking-widest px-2 py-1 rounded-full bg-slate-100 text-slate-500">{{ story.Status }}</span>
            </div>
            <h4
                class="text-[13px] font-bold text-slate-900 leading-tight group-hover:text-rose-500 transition-colors uppercase">
                {{ story.Story_Schedule || story.Story }}</h4>
            <p v-if="story.Catatan"
                class="text-[11px] text-slate-500 bg-slate-50 p-2 rounded-lg italic">{{ story.Catatan }}</p>
            <div class="flex items-center gap-2 pt-2 border-t border-slate-50">
                <a v-if="story.Link" :href="story.Link" target="_blank" rel="noopener noreferrer"
                    class="secondary-cta-button secondary-cta-link">
                    <i class="fa-solid fa-external-link-alt text-[9px]"></i> Buka Link
                </a>
                <div class="flex items-center gap-1.5 ml-auto">
                    <button @click="openEditStoryModal(story)"
                        class="table-action-button table-action-compact" title="Edit"
                        aria-label="Edit"><i class="fa-solid fa-pen text-[10px]"></i></button>
                    <button @click="deleteStory(story.ID)"
                        class="table-action-button table-action-compact table-action-danger"
                        title="Hapus" aria-label="Hapus"><i
                            class="fa-solid fa-trash text-[10px]"></i></button>
                </div>
            </div>
        </div>
        <div v-if="filteredStories.length === 0"
            class="bg-white radius-card border border-dashed border-slate-200 p-16 flex flex-col items-center justify-center text-slate-400">
            <i class="fa-solid fa-clapperboard text-3xl mb-3 opacity-20"></i>
            <p class="text-[11px] font-bold uppercase tracking-widest">Belum ada jadwal story ({{ storyTab }})</p>
        </div>
    </div>

    <div class="hidden md:block section-card section-card-shell">
        <div class="overflow-x-auto">
            <table class="w-full text-[10.5px] text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th
                            class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold w-24">
                            Aksi</th>
                        <th
                            class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold">
                            Tanggal</th>
                        <th
                            class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold">
                            Jam</th>
                        <th
                            class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold">
                            Story</th>
                        <th
                            class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold text-center">
                            Status</th>
                        <th
                            class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold">
                            Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <tr v-for="story in pagedStories" :key="story.ID"
                        class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <button @click="openEditStoryModal(story)"
                                    class="w-8 h-8 rounded-lg border border-slate-100 flex items-center justify-center text-slate-400 hover:text-ppp-accent transition-all"><i
                                        class="fa-solid fa-pen text-[10px]"></i></button>
                                <button @click="deleteStory(story.ID)"
                                    class="w-8 h-8 rounded-lg border border-slate-100 flex items-center justify-center text-slate-400 hover:text-red-500 transition-all"><i
                                        class="fa-solid fa-trash text-[10px]"></i></button>
                            </div>
                        </td>
                        <td
                            class="px-6 py-4 text-[11px] text-slate-500 font-medium whitespace-nowrap">
                            {{ story.Tanggal ? formatShortDate(story.Tanggal) : '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="type-title font-bold text-slate-800">{{ story.Jam }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div
                                class="text-[10.5px] font-semibold text-slate-800 uppercase leading-tight">
                                {{ story.Story_Schedule || story.Story }}</div>
                            <a v-if="story.Link" :href="story.Link" target="_blank" rel="noopener noreferrer"
                                class="secondary-cta-button secondary-cta-link mt-1">
                                <i class="fa-solid fa-external-link-alt text-[9px]"></i> Buka Link
                            </a>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span v-if="story.Status"
                                class="text-[9px] font-bold uppercase tracking-widest px-2.5 py-1 rounded-full bg-slate-100 text-slate-500">{{ story.Status }}</span>
                            <span v-else class="text-slate-300 text-[11px]">-</span>
                        </td>
                        <td class="px-6 py-4">
                            <p v-if="story.Catatan"
                                class="text-[11px] text-slate-500 italic max-w-xs truncate">{{ story.Catatan }}</p>
                            <span v-else class="text-slate-300 text-[11px]">-</span>
                        </td>
                    </tr>
                    <tr v-if="filteredStories.length === 0">
                        <td colspan="6"
                            class="px-6 py-20 text-center text-slate-400 text-[11px] uppercase tracking-widest">
                            Belum ada jadwal story ({{ storyTab }})</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div
            class="table-pager-bar">
            <div class="text-[10px] text-slate-400 font-medium">
                <template v-if="filteredStories.length > 0">{{ (storyPage - 1) * 15 + 1 }}-{{ Math.min(storyPage * 15, filteredStories.length) }} dari {{ filteredStories.length }} data</template>
                <template v-else>0 data</template>
            </div>
            <div class="flex items-center gap-1">
                <button @click="storyPage--" :disabled="storyPage <= 1"
                    aria-label="Halaman sebelumnya"
                    class="icon-utility-button icon-utility-bordered"><i
                        class="fa-solid fa-chevron-left text-[10px]"></i></button>
                <span class="px-3 text-[10px] font-bold text-ppp-accent">{{ storyPage }} / {{ storyTotalPages }}</span>
                <button @click="storyPage++" :disabled="storyPage >= storyTotalPages"
                    aria-label="Halaman berikutnya"
                    class="icon-utility-button icon-utility-bordered"><i
                        class="fa-solid fa-chevron-right text-[10px]"></i></button>
            </div>
        </div>
    </div>
</div>
@endverbatim
