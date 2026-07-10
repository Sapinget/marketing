@verbatim
<!-- Settings View -->
                    <div v-if="activeTab === 'settings'" class="animate-fadeIn space-y-4 pb-10 md:pb-0">
                        <div class="grid gap-4 md:grid-cols-[300px_1fr] md:items-start md:h-[calc(100dvh-168px)]">
                            <div class="section-card section-card-body p-4 md:h-[calc(100dvh-168px)] md:min-h-0 md:flex md:flex-col md:overflow-hidden">
                                <div class="flex-shrink-0">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="type-meta uppercase tracking-[0.24em]">Global Settings</p>
                                            <h2 class="type-body mt-1 font-bold text-slate-900">Workspace Pengaturan</h2>
                                            <p class="mt-1 text-[11px] leading-relaxed text-slate-500">Cari kategori cepat, lihat item kosong, lalu edit dari panel kanan.</p>
                                        </div>
                                    </div>

                                    <div class="mt-4 relative">
                                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                        <label for="settings-search-query" class="hidden">Cari kategori settings</label>
                                        <input
                                            id="settings-search-query"
                                            name="settings_search_query"
                                            :value="settingsSearchQuery"
                                            @input="settingsSearchQuery = $event.target.value"
                                            type="text"
                                            placeholder="Cari kategori settings..."
                                            class="form-input-search" />
                                    </div>

                                    <div class="mt-3 grid grid-cols-3 gap-2">
                                        <button
                                            v-for="filter in settingsFilterOptions"
                                            :key="filter.value"
                                            @click="settingsFilterMode = filter.value"
                                            :class="settingsFilterMode === filter.value ? 'bg-ppp-accent text-white border-ppp-accent' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'"
                                            class="segmented-filter-button active:scale-[0.98]">
                                            {{ filter.label }}
                                        </button>
                                    </div>

                                    <div class="mt-4 grid grid-cols-2 gap-2">
                                        <div class="metric-chip-card">
                                            <div class="type-meta uppercase tracking-[0.2em]">Kategori Tampil</div>
                                            <div class="mt-1 text-[11px] font-bold text-slate-800">{{ filteredSettingsTabCount }}</div>
                                        </div>
                                        <div class="metric-chip-card metric-chip-card--align-end">
                                            <div class="type-meta uppercase tracking-[0.2em]">Belum Disimpan</div>
                                            <div class="mt-1 text-[11px] font-bold" :class="settingsDirtyTabCount ? 'text-amber-600' : 'text-slate-500'">{{ settingsDirtyTabCount }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 space-y-4 md:flex-1 md:min-h-0 md:overflow-y-auto md:pr-1">
                                    <div v-for="group in filteredSettingsMenuGroups" :key="group.label" class="space-y-1.5">
                                        <div class="flex items-center justify-between px-1">
                                            <div class="type-meta uppercase tracking-[0.24em]">{{ group.label }}</div>
                                            <div class="type-meta">{{ group.keys.length }}</div>
                                        </div>
                                        <button
                                            v-for="key in group.keys"
                                            :key="key"
                                            @click="setActiveSettingTab(key)"
                                            :class="activeSettingTab === key ? 'bg-ppp-accent text-white border-ppp-accent' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300 hover:bg-slate-50'"
                                            class="w-full text-left rounded-2xl border px-3 py-3 transition-all active:scale-[0.985]">
                                            <div class="flex items-start gap-3">
                                                <div :class="activeSettingTab === key ? 'bg-white/12 text-white' : 'bg-slate-100 text-slate-500'" class="mt-0.5 h-8 w-8 flex-shrink-0 rounded-xl flex items-center justify-center">
                                                    <i class="fa-solid fa-tag text-[10px]"></i>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-[11px] font-bold leading-snug break-words">{{ getSettingTabLabel(key) }}</span>
                                                        <span v-if="isSettingTabDirty(key)" :class="activeSettingTab === key ? 'bg-amber-400/20 text-amber-100' : 'bg-amber-100 text-amber-700'" class="inline-flex items-center rounded-full px-2 py-0.5 text-[8px] font-bold uppercase tracking-[0.16em]">Edit</span>
                                                    </div>
                                                    <div class="mt-1 flex items-center gap-2 type-body">
                                                        <span :class="activeSettingTab === key ? 'text-white/80' : 'text-slate-500'">{{ getSettingFilledCount(key) }} isi</span>
                                                        <span :class="activeSettingTab === key ? 'text-white/40' : 'text-slate-300'">&bull;</span>
                                                        <span :class="getSettingEmptyCount(key) ? (activeSettingTab === key ? 'text-amber-100' : 'text-amber-600') : (activeSettingTab === key ? 'text-white/80' : 'text-slate-500')">{{ getSettingEmptyCount(key) }} kosong</span>
                                                    </div>
                                                </div>
                                                <span :class="activeSettingTab === key ? 'bg-white/12 text-white' : 'bg-slate-100 text-slate-500'" class="rounded-xl px-2 py-1 text-[9px] font-bold">{{ getSettingTabCount(key) }}</span>
                                            </div>
                                        </button>
                                    </div>
                                    <div v-if="filteredSettingsTabCount === 0" class="rounded-2xl border border-dashed border-slate-200 px-4 py-8 text-center text-[11px] text-slate-400">
                                        Kategori tidak ditemukan.
                                    </div>
                                </div>
                            </div>

                            <div class="hidden md:block section-card section-card-shell md:h-[calc(100dvh-168px)] md:min-h-0">
                                <div v-if="activeSettingTab &amp;&amp; settingsDraft[activeSettingTab]" class="flex h-full min-h-0 flex-col">
                                    <div class="shrink-0 border-b border-slate-100 px-4 py-4 md:px-5">
                                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                            <div class="flex items-start gap-3">
                                                <div class="h-12 w-12 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600">
                                                    <i class="fa-solid fa-sliders text-[11px]"></i>
                                                </div>
                                                <div>
                                                    <p class="type-meta uppercase tracking-[0.22em]">Kategori Aktif</p>
                                                    <h3 class="type-body mt-1 font-bold text-slate-900">{{ getSettingTabLabel(activeSettingTab) }}</h3>
                                                    <p class="mt-1 text-[11px] leading-relaxed text-slate-500">Key: <span class="font-semibold text-slate-600">{{ activeSettingTab }}</span> | {{ getSettingTabCount(activeSettingTab) }} total opsi | {{ getSettingFilledCount(activeSettingTab) }} terisi</p>
                                                </div>
                                            </div>
                                            <div class="toolbar-actions">
                                                <button v-if="!isSettingTabObject(activeSettingTab)" @click="addSettingOption(activeSettingTab)" class="primary-cta-button primary-cta-button--accent active:scale-95">
                                                    <i class="fa-solid fa-plus text-[10px]"></i> Tambah
                                                </button>
                                                <button v-if="!isSettingTabObject(activeSettingTab)" @click="toggleSettingsBulkAdd" :class="showSettingsBulkAdd ? 'bg-slate-900 text-white border-slate-900 hover:bg-black hover:border-black' : 'secondary-cta-neutral'" class="secondary-cta-button active:scale-95">
                                                    <i class="fa-solid fa-layer-group text-[10px]"></i> Tambah Banyak
                                                </button>
                                                <button v-if="!isSettingTabObject(activeSettingTab)" @click="sortSettingOptions(activeSettingTab)" class="secondary-cta-button secondary-cta-neutral active:scale-95">
                                                    <i class="fa-solid fa-arrow-down-a-z text-[10px]"></i> Urutkan
                                                </button>
                                                <button v-if="!isSettingTabObject(activeSettingTab)" @click="clearEmptySettingOptions(activeSettingTab)" class="secondary-cta-button secondary-cta-danger active:scale-95">
                                                    <i class="fa-solid fa-eraser text-[10px]"></i> Hapus Kosong
                                                </button>
                                                <button v-if="activeSettingTab === 'BONUS_CONFIG'" @click="activeTab = 'bonus_report'" class="secondary-cta-button secondary-cta-link active:scale-95">
                                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> Buka Bonus
                                                </button>
                                                <button v-if="activeSettingTab === 'BUDGET_CONFIG'" @click="activeTab = 'budgeting'" class="secondary-cta-button secondary-cta-link active:scale-95">
                                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> Buka Budget
                                                </button>
                                            </div>
                                        </div>

                                        <div class="mt-4 space-y-3">
                                            <div class="relative" v-if="!isSettingTabObject(activeSettingTab)">
                                                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                                <label for="settings-active-value-search" class="hidden">Cari isi opsi settings</label>
                                                <input
                                                    id="settings-active-value-search"
                                                    name="settings_active_value_search"
                                                    :value="activeSettingValueSearch"
                                                    @input="activeSettingValueSearch = $event.target.value"
                                                    type="text"
                                                    placeholder="Cari isi opsi pada kategori ini..."
                                                    class="form-input-search" />
                                            </div>
                                            <div v-else class="info-panel-soft">
                                                JSON config tampil ringkas dalam card. Edit detail tetap di menu khusus agar struktur data aman.
                                            </div>
                                            <div class="mini-stat-chip-row">
                                                <span class="mini-stat-chip">
                                                    <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Total</span>
                                                    <span class="text-[11px] font-bold text-slate-800">{{ getSettingTabCount(activeSettingTab) }}</span>
                                                </span>
                                                <span class="mini-stat-chip">
                                                    <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Kosong</span>
                                                    <span class="text-[11px] font-bold text-amber-600">{{ getSettingEmptyCount(activeSettingTab) }}</span>
                                                </span>
                                                <span class="mini-stat-chip">
                                                    <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Edit</span>
                                                    <span class="text-[11px] font-bold" :class="isSettingTabDirty(activeSettingTab) ? 'text-amber-600' : 'text-slate-500'">{{ isSettingTabDirty(activeSettingTab) ? 'Yes' : 'No' }}</span>
                                                </span>
                                            </div>
                                        </div>

                                        <div v-if="showSettingsBulkAdd &amp;&amp; !isSettingTabObject(activeSettingTab)" class="mt-4 rounded-3xl border border-slate-200 bg-slate-50 p-3">
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <div class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">Tambah Banyak</div>
                                                    <p class="mt-1 text-[11px] text-slate-500">Satu baris satu opsi. Baris duplikat atau kosong akan dibuang.</p>
                                                </div>
                                                <button @click="showSettingsBulkAdd = false" class="icon-utility-button icon-utility-bordered">
                                                    <i class="fa-solid fa-xmark text-[11px]"></i>
                                                </button>
                                            </div>
                                            <label for="settings-bulk-add-text" class="sr-only">Tambah banyak opsi settings</label>
                                            <textarea
                                                id="settings-bulk-add-text"
                                                name="settings_bulk_add_text"
                                                :value="settingsBulkAddText"
                                                @input="settingsBulkAddText = $event.target.value"
                                                rows="5"
                                                placeholder="contoh&#10;DRAFT&#10;PENDING&#10;DONE"
                                                class="mt-3 form-textarea"></textarea>
                                            <div class="mt-3 flex flex-wrap justify-end gap-2">
                                                <button @click="settingsBulkAddText = ''" class="secondary-cta-button secondary-cta-neutral active:scale-95">Kosongkan</button>
                                                <button @click="applySettingsBulkAdd(activeSettingTab)" class="secondary-cta-button secondary-cta-link active:scale-95">
                                                    <i class="fa-solid fa-check text-[10px]"></i> Masukkan
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="settings-surface flex-1 min-h-0 overflow-y-auto px-4 py-4 md:px-5">
                                        <div v-if="isSettingTabObject(activeSettingTab)" class="space-y-3">
                                            <div class="grid gap-3 xl:grid-cols-2">
                                                <div
                                                    v-for="section in activeSettingObjectSections"
                                                    :key="section.title"
                                                    class="settings-panel-card">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <div class="text-[11px] font-bold text-slate-800">{{ section.title }}</div>
                                                        <div class="settings-item-counter">{{ section.items.length }} item</div>
                                                    </div>
                                                    <div class="mt-3 space-y-2">
                                                        <div
                                                            v-for="item in section.items"
                                                            :key="section.title + '-' + item.label"
                                                            class="settings-panel-item">
                                                            <div class="text-[8px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ item.label }}</div>
                                                            <div class="mt-1 text-[11px] font-semibold leading-relaxed text-slate-700 whitespace-pre-wrap break-words">{{ item.value }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else-if="filteredActiveSettingEntries.length" class="space-y-2">
                                            <div
                                                v-for="entry in filteredActiveSettingEntries"
                                                :key="activeSettingTab + '-' + entry.idx"
                                                class="settings-entry-card">
                                                <div class="flex items-start gap-3">
                                                    <div class="mt-0.5 h-8 w-8 rounded-2xl bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500">
                                                        {{ entry.idx + 1 }}
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <span v-if="!String(entry.value || '').trim()" class="rounded-full bg-amber-100 px-2 py-0.5 text-[8px] font-bold uppercase tracking-[0.15em] text-amber-700">Kosong</span>
                                                        <input
                                                            :id="`settings-option-${activeSettingTab}-${entry.idx}`"
                                                            :name="`settings_option_${activeSettingTab}_${entry.idx}`"
                                                            :value="settingsDraft[activeSettingTab][entry.idx]"
                                                            @input="updateSettingOption(activeSettingTab, entry.idx, $event.target.value)"
                                                            :data-setting-key="activeSettingTab"
                                                            :data-setting-idx="entry.idx"
                                                            placeholder="Isi nilai opsi..."
                                                            class="mt-2 form-input-compact-white font-bold uppercase" />
                                                    </div>
                                                    <button @click="askSettingAction('delete', activeSettingTab, entry.idx)" class="icon-utility-button icon-utility-bordered icon-utility-danger mt-5">
                                                        <i class="fa-solid fa-trash text-[10px]"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="settings-empty-state">
                                            <div class="settings-empty-state__icon">
                                                <i class="fa-solid fa-folder-open text-[11px]"></i>
                                            </div>
                                            <p class="mt-4 text-[11px] font-bold text-slate-700">Tidak ada opsi yang cocok</p>
                                            <p class="mt-1 text-[11px] leading-relaxed text-slate-500">Coba ubah kata kunci pencarian, atau tambah opsi baru untuk kategori ini.</p>
                                        </div>
                                    </div>

                                    <div class="shrink-0 border-t border-slate-100 bg-white/95 px-4 py-3 backdrop-blur md:px-5">
                                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                            <div>
                                                <div class="text-[10px] font-bold uppercase tracking-[0.18em]" :class="settingsDirty ? 'text-amber-600' : 'text-slate-400'">{{ settingsDirty ? 'Perubahan Belum Disimpan' : 'Semua Perubahan Aman' }}</div>
                                                <p class="mt-1 text-[11px] text-slate-500">{{ settingsDirty ? settingsDirtyTabCount + ' kategori berubah, ' + settingsDirtyValueCount + ' nilai berbeda dari data tersimpan.' : 'Belum ada perubahan pada settings.' }}</p>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2 md:flex">
                                                <button v-if="settingsDirty" @click="resetSettingsDraft" class="secondary-cta-button secondary-cta-neutral active:scale-95">
                                                    <i class="fa-solid fa-rotate-left text-[10px]"></i> Reset
                                                </button>
                                                <button @click="saveSettingsBackend" :disabled="savingSettings || !settingsDirty" class="primary-cta-button primary-cta-button--accent active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed">
                                                    <i v-if="!savingSettings" class="fa-solid fa-floppy-disk text-xs text-blue-400"></i>
                                                    <i v-else class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                                                    {{ savingSettings ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="flex h-full md:min-h-0 items-center justify-center text-slate-400">
                                    <div class="text-center">
                                        <i class="fa-solid fa-folder-open text-2xl mb-3 opacity-30"></i>
                                        <p class="text-[11px]">Pilih kategori settings</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <teleport to="body">
                        <transition name="fade">
                            <div v-if="activeTab === 'settings' &amp;&amp; isMobileViewport &amp;&amp; settingsDetailModalOpen &amp;&amp; activeSettingTab &amp;&amp; settingsDraft[activeSettingTab]"
                                class="fixed inset-0 z-[2000] flex items-end justify-center overlay-motion-sheet">
                                <div @click="closeSettingsDetailModal" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop"></div>
                                <div class="mobile-sheet relative w-full bg-white radius-sheet flex flex-col max-h-[90dvh] animate-fadeIn">
                                    <div class="modal-header-bar radius-sheet-top items-start">
                                        <div class="modal-header-copy items-start">
                                            <div class="modal-header-icon bg-slate-100 text-slate-600 border border-slate-200">
                                                <i class="fa-solid fa-sliders text-[11px]"></i>
                                            </div>
                                            <div>
                                                <div class="type-meta uppercase tracking-[0.22em] text-slate-400">Kategori Aktif</div>
                                                <div class="type-body mt-1 font-bold text-slate-900">{{ getSettingTabLabel(activeSettingTab) }}</div>
                                                <div class="mt-1 text-[11px] leading-relaxed text-slate-500">Key: <span class="font-semibold text-slate-600">{{ activeSettingTab }}</span> | {{ getSettingTabCount(activeSettingTab) }} total opsi | {{ getSettingFilledCount(activeSettingTab) }} terisi</div>
                                            </div>
                                        </div>
                                        <button @click="closeSettingsDetailModal" class="icon-utility-button icon-utility-danger"><i class="fa-solid fa-xmark text-[11px]"></i></button>
                                    </div>

                                    <div class="settings-surface flex-1 overflow-y-auto p-4 space-y-4">
                                        <div class="grid grid-cols-2 gap-2">
                                            <button v-if="!isSettingTabObject(activeSettingTab)" @click="addSettingOption(activeSettingTab)" class="primary-cta-button primary-cta-button--accent active:scale-95">
                                                <i class="fa-solid fa-plus text-[10px]"></i> Tambah
                                            </button>
                                            <button v-if="!isSettingTabObject(activeSettingTab)" @click="toggleSettingsBulkAdd" :class="showSettingsBulkAdd ? 'bg-slate-900 text-white border-slate-900 hover:bg-black hover:border-black' : 'secondary-cta-neutral'" class="secondary-cta-button active:scale-95">
                                                <i class="fa-solid fa-layer-group text-[10px]"></i> Banyak
                                            </button>
                                            <button v-if="!isSettingTabObject(activeSettingTab)" @click="sortSettingOptions(activeSettingTab)" class="secondary-cta-button secondary-cta-neutral active:scale-95">
                                                <i class="fa-solid fa-arrow-down-a-z text-[10px]"></i> Urutkan
                                            </button>
                                            <button v-if="!isSettingTabObject(activeSettingTab)" @click="clearEmptySettingOptions(activeSettingTab)" class="secondary-cta-button secondary-cta-danger active:scale-95">
                                                <i class="fa-solid fa-eraser text-[10px]"></i> Hapus
                                            </button>
                                            <button v-if="activeSettingTab === 'BONUS_CONFIG'" @click="closeSettingsDetailModal(); activeTab = 'bonus_report'" class="secondary-cta-button secondary-cta-link active:scale-95 col-span-2">
                                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> Buka Bonus Editor
                                            </button>
                                            <button v-if="activeSettingTab === 'BUDGET_CONFIG'" @click="closeSettingsDetailModal(); activeTab = 'budgeting'" class="secondary-cta-button secondary-cta-link active:scale-95 col-span-2">
                                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> Buka Budget Editor
                                            </button>
                                        </div>

                                        <div class="mini-stat-chip-row">
                                            <span class="mini-stat-chip mini-stat-chip--outlined">
                                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Total</span>
                                                <span class="text-[11px] font-bold text-slate-800">{{ getSettingTabCount(activeSettingTab) }}</span>
                                            </span>
                                            <span class="mini-stat-chip mini-stat-chip--outlined">
                                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Kosong</span>
                                                <span class="text-[11px] font-bold text-amber-600">{{ getSettingEmptyCount(activeSettingTab) }}</span>
                                            </span>
                                            <span class="mini-stat-chip mini-stat-chip--outlined">
                                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Edit</span>
                                                <span class="text-[11px] font-bold" :class="isSettingTabDirty(activeSettingTab) ? 'text-amber-600' : 'text-slate-500'">{{ isSettingTabDirty(activeSettingTab) ? 'Yes' : 'No' }}</span>
                                            </span>
                                        </div>

                                        <div v-if="!isSettingTabObject(activeSettingTab)" class="relative">
                                            <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                            <label for="settings-active-value-search-mobile" class="hidden">Cari isi opsi settings mobile</label>
                                            <input
                                                id="settings-active-value-search-mobile"
                                                name="settings_active_value_search_mobile"
                                                :value="activeSettingValueSearch"
                                                @input="activeSettingValueSearch = $event.target.value"
                                                type="text"
                                                placeholder="Cari isi opsi..."
                                                class="form-input-search" />
                                        </div>

                                        <div v-if="showSettingsBulkAdd &amp;&amp; !isSettingTabObject(activeSettingTab)" class="settings-entry-card">
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <div class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">Tambah Banyak</div>
                                                    <p class="mt-1 text-[11px] text-slate-500">Satu baris satu opsi.</p>
                                                </div>
                                                <button @click="showSettingsBulkAdd = false" class="icon-utility-button icon-utility-bordered">
                                                    <i class="fa-solid fa-xmark text-[11px]"></i>
                                                </button>
                                            </div>
                                            <label for="settings-bulk-add-text-mobile" class="sr-only">Tambah banyak opsi settings mobile</label>
                                            <textarea
                                                id="settings-bulk-add-text-mobile"
                                                name="settings_bulk_add_text_mobile"
                                                :value="settingsBulkAddText"
                                                @input="settingsBulkAddText = $event.target.value"
                                                rows="5"
                                                placeholder="contoh&#10;DRAFT&#10;PENDING&#10;DONE"
                                                class="mt-3 form-textarea"></textarea>
                                            <div class="mt-3 flex flex-wrap justify-end gap-2">
                                                <button @click="settingsBulkAddText = ''" class="secondary-cta-button secondary-cta-neutral active:scale-95">Kosongkan</button>
                                                <button @click="applySettingsBulkAdd(activeSettingTab)" class="secondary-cta-button secondary-cta-link active:scale-95">
                                                    <i class="fa-solid fa-check text-[10px]"></i> Masukkan
                                                </button>
                                            </div>
                                        </div>

                                        <div v-if="isSettingTabObject(activeSettingTab)" class="space-y-3">
                                            <div class="info-panel-soft bg-white">
                                                JSON config tampil ringkas dalam card. Edit detail tetap di menu khusus agar struktur data aman.
                                            </div>
                                            <div class="grid gap-3">
                                                <div v-for="section in activeSettingObjectSections" :key="'mobile-' + section.title" class="settings-panel-card">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <div class="text-[11px] font-bold text-slate-800">{{ section.title }}</div>
                                                        <div class="settings-item-counter">{{ section.items.length }} item</div>
                                                    </div>
                                                    <div class="mt-3 space-y-2">
                                                        <div v-for="item in section.items" :key="'mobile-' + section.title + '-' + item.label" class="settings-panel-item">
                                                            <div class="text-[8px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ item.label }}</div>
                                                            <div class="mt-1 text-[11px] font-semibold leading-relaxed text-slate-700 whitespace-pre-wrap break-words">{{ item.value }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else-if="filteredActiveSettingEntries.length" class="space-y-2">
                                            <div v-for="entry in filteredActiveSettingEntries" :key="'mobile-' + activeSettingTab + '-' + entry.idx" class="settings-entry-card">
                                                <div class="flex items-start gap-3">
                                                    <div class="mt-0.5 h-8 w-8 rounded-2xl bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500">{{ entry.idx + 1 }}</div>
                                                    <div class="min-w-0 flex-1">
                                                        <span v-if="!String(entry.value || '').trim()" class="rounded-full bg-amber-100 px-2 py-0.5 text-[8px] font-bold uppercase tracking-[0.15em] text-amber-700">Kosong</span>
                                                        <input
                                                            :id="`settings-option-mobile-${activeSettingTab}-${entry.idx}`"
                                                            :name="`settings_option_mobile_${activeSettingTab}_${entry.idx}`"
                                                            :value="settingsDraft[activeSettingTab][entry.idx]"
                                                            @input="updateSettingOption(activeSettingTab, entry.idx, $event.target.value)"
                                                            :data-setting-key="activeSettingTab"
                                                            :data-setting-idx="entry.idx"
                                                            placeholder="Isi nilai opsi..."
                                                            class="mt-2 form-input-compact-white font-bold uppercase" />
                                                    </div>
                                                    <button @click="askSettingAction('delete', activeSettingTab, entry.idx)" class="icon-utility-button icon-utility-bordered icon-utility-danger mt-5">
                                                        <i class="fa-solid fa-trash text-[10px]"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="settings-empty-state">
                                            <div class="settings-empty-state__icon">
                                                <i class="fa-solid fa-folder-open text-[11px]"></i>
                                            </div>
                                            <p class="mt-4 text-[11px] font-bold text-slate-700">Tidak ada opsi yang cocok</p>
                                            <p class="mt-1 text-[11px] leading-relaxed text-slate-500">Coba ubah kata kunci pencarian, atau tambah opsi baru untuk kategori ini.</p>
                                        </div>
                                    </div>

                                    <div class="modal-footer-bar radius-sheet-bottom">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="text-[10px] font-bold uppercase tracking-[0.18em]" :class="settingsDirty ? 'text-amber-600' : 'text-slate-400'">{{ settingsDirty ? 'Perubahan Belum Disimpan' : 'Semua Perubahan Aman' }}</div>
                                                <p class="mt-1 text-[11px] text-slate-500 line-clamp-2">{{ settingsDirty ? settingsDirtyTabCount + ' kategori berubah, ' + settingsDirtyValueCount + ' nilai berbeda.' : 'Belum ada perubahan pada settings.' }}</p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button @click="closeSettingsDetailModal" class="modal-secondary-button">Tutup</button>
                                                <button @click="saveSettingsBackend" :disabled="savingSettings || !settingsDirty" class="modal-primary-button">
                                                    <i v-if="!savingSettings" class="fa-solid fa-floppy-disk text-xs"></i>
                                                    <i v-else class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                                                    {{ savingSettings ? 'Menyimpan...' : 'Simpan' }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </teleport>
@endverbatim
