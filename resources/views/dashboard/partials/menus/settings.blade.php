@verbatim
<!-- Settings View -->
                    <div v-if="activeTab === 'settings'" class="animate-fadeIn space-y-4 pb-10">
                        <div class="grid gap-4 md:grid-cols-[300px_1fr] md:items-start md:h-[calc(100dvh-168px)]">
                            <div class="bg-white radius-panel border border-slate-100 p-4 md:sticky md:top-4 md:h-[calc(100dvh-168px)] md:min-h-0 md:flex md:flex-col md:overflow-hidden">
                                <div class="flex-shrink-0">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-[9px] font-bold uppercase tracking-[0.24em] text-slate-400">Global Settings</p>
                                            <h2 class="mt-1 text-[16px] font-bold text-slate-900">Workspace Pengaturan</h2>
                                            <p class="mt-1 text-[11px] leading-relaxed text-slate-500">Cari kategori cepat, lihat item kosong, lalu edit dari panel kanan.</p>
                                        </div>
                                        <div class="h-10 w-10 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-500">
                                            <i class="fa-solid fa-sliders text-sm"></i>
                                        </div>
                                    </div>

                                    <div class="mt-4 relative">
                                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                        <input
                                            :value="settingsSearchQuery"
                                            @input="settingsSearchQuery = $event.target.value"
                                            type="text"
                                            placeholder="Cari kategori settings..."
                                            class="w-full pl-9 pr-3 py-2.5 rounded-2xl border border-slate-200 bg-white text-[11px] text-slate-700 outline-none focus:border-ppp-accent focus:ring-2 focus:ring-ppp-accent/10" />
                                    </div>

                                    <div class="mt-3 grid grid-cols-3 gap-2">
                                        <button
                                            v-for="filter in settingsFilterOptions"
                                            :key="filter.value"
                                            @click="settingsFilterMode = filter.value"
                                            :class="settingsFilterMode === filter.value ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'"
                                            class="min-h-[38px] rounded-2xl border px-2 py-2 text-[10px] font-bold uppercase tracking-[0.14em] transition-all active:scale-[0.98]">
                                            {{ filter.label }}
                                        </button>
                                    </div>

                                    <div class="mt-4 flex items-center justify-between rounded-2xl bg-slate-50 px-3 py-2">
                                        <div>
                                            <div class="text-[9px] font-bold uppercase tracking-[0.2em] text-slate-400">Kategori Tampil</div>
                                            <div class="mt-1 text-[14px] font-bold text-slate-800">{{ filteredSettingsTabCount }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-[9px] font-bold uppercase tracking-[0.2em] text-slate-400">Belum Disimpan</div>
                                            <div class="mt-1 text-[14px] font-bold" :class="settingsDirtyTabCount ? 'text-amber-600' : 'text-slate-500'">{{ settingsDirtyTabCount }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 space-y-4 md:flex-1 md:min-h-0 md:overflow-y-auto md:pr-1">
                                    <div v-for="group in filteredSettingsMenuGroups" :key="group.label" class="space-y-1.5">
                                        <div class="flex items-center justify-between px-1">
                                            <div class="text-[9px] font-bold uppercase tracking-[0.24em] text-slate-400">{{ group.label }}</div>
                                            <div class="text-[9px] font-semibold text-slate-400">{{ group.keys.length }}</div>
                                        </div>
                                        <button
                                            v-for="key in group.keys"
                                            :key="key"
                                            @click="setActiveSettingTab(key)"
                                            :class="activeSettingTab === key ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300 hover:bg-slate-50'"
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
                                                    <div class="mt-1 flex items-center gap-2 text-[9px] font-medium">
                                                        <span :class="activeSettingTab === key ? 'text-white/80' : 'text-slate-500'">{{ getSettingFilledCount(key) }} isi</span>
                                                        <span :class="activeSettingTab === key ? 'text-white/40' : 'text-slate-300'">&bull;</span>
                                                        <span :class="getSettingEmptyCount(key) ? (activeSettingTab === key ? 'text-amber-100' : 'text-amber-600') : (activeSettingTab === key ? 'text-white/80' : 'text-slate-500')">{{ getSettingEmptyCount(key) }} kosong</span>
                                                    </div>
                                                </div>
                                                <span :class="activeSettingTab === key ? 'bg-white/12 text-white' : 'bg-slate-100 text-slate-500'" class="rounded-xl px-2 py-1 text-[9px] font-bold">{{ getSettingTabCount(key) }}</span>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                    <div v-if="filteredSettingsTabCount === 0" class="rounded-2xl border border-dashed border-slate-200 px-4 py-8 text-center text-[11px] text-slate-400">
                                        Kategori tidak ditemukan.
                                    </div>
                            </div>

                            <div class="hidden md:block section-card section-card-shell min-h-[calc(100dvh-220px)] md:min-h-0 md:h-[calc(100dvh-168px)]">
                                <div v-if="activeSettingTab && settingsDraft[activeSettingTab]" class="flex h-full min-h-0 flex-col">
                                    <div class="border-b border-slate-100 px-4 py-4 md:px-5">
                                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                            <div class="flex items-start gap-3">
                                                <div class="h-11 w-11 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-600">
                                                    <i class="fa-solid fa-sliders text-sm"></i>
                                                </div>
                                                <div>
                                                    <p class="text-[9px] font-bold uppercase tracking-[0.22em] text-slate-400">Kategori Aktif</p>
                                                    <h3 class="mt-1 text-[18px] font-bold text-slate-900">{{ getSettingTabLabel(activeSettingTab) }}</h3>
                                                    <p class="mt-1 text-[11px] leading-relaxed text-slate-500">Key: <span class="font-semibold text-slate-600">{{ activeSettingTab }}</span> | {{ getSettingTabCount(activeSettingTab) }} total opsi | {{ getSettingFilledCount(activeSettingTab) }} terisi</p>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:justify-end">
                                                <button v-if="!isSettingTabObject(activeSettingTab)" @click="addSettingOption(activeSettingTab)" class="secondary-cta-button secondary-cta-link active:scale-95">
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

                                        <div class="mt-4 grid gap-3 lg:grid-cols-[minmax(0,1fr)_220px]">
                                            <div class="relative" v-if="!isSettingTabObject(activeSettingTab)">
                                                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                                <input
                                                    :value="activeSettingValueSearch"
                                                    @input="activeSettingValueSearch = $event.target.value"
                                                    type="text"
                                                    placeholder="Cari isi opsi pada kategori ini..."
                                                    class="w-full pl-9 pr-3 py-2.5 rounded-2xl border border-slate-200 bg-white text-[11px] text-slate-700 outline-none focus:border-ppp-accent focus:ring-2 focus:ring-ppp-accent/10" />
                                            </div>
                                            <div v-else class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-[11px] leading-relaxed text-slate-500">
                                                JSON config tampil ringkas dalam card. Edit detail tetap di menu khusus agar struktur data aman.
                                            </div>
                                            <div class="grid grid-cols-3 gap-2">
                                                <div class="rounded-2xl bg-slate-50 px-3 py-2 text-center">
                                                    <div class="text-[8px] font-bold uppercase tracking-[0.18em] text-slate-400">Total</div>
                                                    <div class="mt-1 text-[13px] font-bold text-slate-800">{{ getSettingTabCount(activeSettingTab) }}</div>
                                                </div>
                                                <div class="rounded-2xl bg-slate-50 px-3 py-2 text-center">
                                                    <div class="text-[8px] font-bold uppercase tracking-[0.18em] text-slate-400">Kosong</div>
                                                    <div class="mt-1 text-[13px] font-bold text-amber-600">{{ getSettingEmptyCount(activeSettingTab) }}</div>
                                                </div>
                                                <div class="rounded-2xl bg-slate-50 px-3 py-2 text-center">
                                                    <div class="text-[8px] font-bold uppercase tracking-[0.18em] text-slate-400">Edit</div>
                                                    <div class="mt-1 text-[13px] font-bold" :class="isSettingTabDirty(activeSettingTab) ? 'text-amber-600' : 'text-slate-500'">{{ isSettingTabDirty(activeSettingTab) ? 'Yes' : 'No' }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-if="showSettingsBulkAdd && !isSettingTabObject(activeSettingTab)" class="mt-4 rounded-3xl border border-slate-200 bg-slate-50 p-3">
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <div class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">Tambah Banyak</div>
                                                    <p class="mt-1 text-[11px] text-slate-500">Satu baris satu opsi. Baris duplikat atau kosong akan dibuang.</p>
                                                </div>
                                                <button @click="showSettingsBulkAdd = false" class="icon-utility-button icon-utility-bordered">
                                                    <i class="fa-solid fa-xmark text-[11px]"></i>
                                                </button>
                                            </div>
                                            <textarea
                                                :value="settingsBulkAddText"
                                                @input="settingsBulkAddText = $event.target.value"
                                                rows="5"
                                                placeholder="contoh&#10;DRAFT&#10;PENDING&#10;DONE"
                                                class="mt-3 w-full rounded-2xl border border-slate-200 bg-white px-3 py-3 text-[11px] text-slate-700 outline-none focus:border-ppp-accent focus:ring-2 focus:ring-ppp-accent/10"></textarea>
                                            <div class="mt-3 flex flex-wrap justify-end gap-2">
                                                <button @click="settingsBulkAddText = ''" class="secondary-cta-button secondary-cta-neutral active:scale-95">Kosongkan</button>
                                                <button @click="applySettingsBulkAdd(activeSettingTab)" class="secondary-cta-button secondary-cta-link active:scale-95">
                                                    <i class="fa-solid fa-check text-[10px]"></i> Masukkan
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex-1 min-h-0 overflow-y-auto bg-slate-50/60 px-4 py-4 md:px-5">
                                        <div v-if="isSettingTabObject(activeSettingTab)" class="space-y-3">
                                            <div class="grid gap-3 xl:grid-cols-2">
                                                <div
                                                    v-for="section in activeSettingObjectSections"
                                                    :key="section.title"
                                                    class="rounded-3xl border border-slate-200 bg-white p-4">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <div class="text-[11px] font-bold text-slate-800">{{ section.title }}</div>
                                                        <div class="rounded-full bg-slate-100 px-2 py-0.5 text-[8px] font-bold uppercase tracking-[0.15em] text-slate-500">{{ section.items.length }} item</div>
                                                    </div>
                                                    <div class="mt-3 space-y-2">
                                                        <div
                                                            v-for="item in section.items"
                                                            :key="section.title + '-' + item.label"
                                                            class="rounded-2xl bg-slate-50 px-3 py-2">
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
                                                class="rounded-3xl border border-slate-200 bg-white p-3 transition-all hover:border-slate-300">
                                                <div class="flex items-start gap-3">
                                                    <div class="mt-0.5 h-8 w-8 rounded-2xl bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500">
                                                        {{ entry.idx + 1 }}
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex items-center justify-between gap-3">
                                                            <label class="text-[9px] font-bold uppercase tracking-[0.18em] text-slate-400">Nilai Opsi</label>
                                                            <span v-if="!String(entry.value || '').trim()" class="rounded-full bg-amber-100 px-2 py-0.5 text-[8px] font-bold uppercase tracking-[0.15em] text-amber-700">Kosong</span>
                                                        </div>
                                                        <input
                                                            :value="settingsDraft[activeSettingTab][entry.idx]"
                                                            @input="updateSettingOption(activeSettingTab, entry.idx, $event.target.value)"
                                                            :data-setting-key="activeSettingTab"
                                                            :data-setting-idx="entry.idx"
                                                            placeholder="Isi nilai opsi..."
                                                            class="mt-2 form-input-compact-white font-bold uppercase shadow-sm" />
                                                    </div>
                                                    <button @click="askSettingAction('delete', activeSettingTab, entry.idx)" class="icon-utility-button icon-utility-bordered icon-utility-danger mt-5">
                                                        <i class="fa-solid fa-trash text-[10px]"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="rounded-3xl border border-dashed border-slate-200 bg-white px-6 py-12 text-center">
                                            <div class="mx-auto h-12 w-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                                                <i class="fa-solid fa-folder-open text-sm"></i>
                                            </div>
                                            <p class="mt-4 text-[13px] font-bold text-slate-700">Tidak ada opsi yang cocok</p>
                                            <p class="mt-1 text-[11px] leading-relaxed text-slate-500">Coba ubah kata kunci pencarian, atau tambah opsi baru untuk kategori ini.</p>
                                        </div>
                                    </div>

                                    <div class="border-t border-slate-100 bg-white/95 px-4 py-3 backdrop-blur md:px-5">
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
                                <div v-else class="flex h-full min-h-[calc(100dvh-220px)] md:min-h-0 items-center justify-center text-slate-400">
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
                            <div v-if="activeTab === 'settings' && isMobileViewport && settingsDetailModalOpen && activeSettingTab && settingsDraft[activeSettingTab]"
                                class="fixed inset-0 z-[2000] flex items-end justify-center overlay-motion-sheet">
                                <div @click="closeSettingsDetailModal" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop"></div>
                                <div class="mobile-sheet relative w-full bg-white radius-sheet shadow-2xl flex flex-col max-h-[90dvh] animate-fadeIn">
                                    <div class="modal-header-bar radius-sheet-top items-start">
                                        <div class="modal-header-copy items-start">
                                            <div class="modal-header-icon bg-slate-100 text-slate-600">
                                                <i class="fa-solid fa-sliders text-[14px]"></i>
                                            </div>
                                            <div>
                                                <div class="type-meta uppercase tracking-[0.22em] text-slate-400">Kategori Aktif</div>
                                                <div class="type-title mt-1 text-slate-900">{{ getSettingTabLabel(activeSettingTab) }}</div>
                                                <div class="mt-1 text-[11px] leading-relaxed text-slate-500">Key: <span class="font-semibold text-slate-600">{{ activeSettingTab }}</span> | {{ getSettingTabCount(activeSettingTab) }} total opsi | {{ getSettingFilledCount(activeSettingTab) }} terisi</div>
                                            </div>
                                        </div>
                                        <button @click="closeSettingsDetailModal" class="icon-utility-button icon-utility-danger"><i class="fa-solid fa-xmark text-sm"></i></button>
                                    </div>

                                    <div class="flex-1 overflow-y-auto bg-slate-50/60 p-4 space-y-4">
                                        <div class="grid grid-cols-2 gap-2">
                                            <button v-if="!isSettingTabObject(activeSettingTab)" @click="addSettingOption(activeSettingTab)" class="secondary-cta-button secondary-cta-link active:scale-95">
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

                                        <div class="grid grid-cols-3 gap-2">
                                            <div class="rounded-2xl bg-white border border-slate-200 px-3 py-2 text-center">
                                                <div class="text-[8px] font-bold uppercase tracking-[0.18em] text-slate-400">Total</div>
                                                <div class="mt-1 text-[13px] font-bold text-slate-800">{{ getSettingTabCount(activeSettingTab) }}</div>
                                            </div>
                                            <div class="rounded-2xl bg-white border border-slate-200 px-3 py-2 text-center">
                                                <div class="text-[8px] font-bold uppercase tracking-[0.18em] text-slate-400">Kosong</div>
                                                <div class="mt-1 text-[13px] font-bold text-amber-600">{{ getSettingEmptyCount(activeSettingTab) }}</div>
                                            </div>
                                            <div class="rounded-2xl bg-white border border-slate-200 px-3 py-2 text-center">
                                                <div class="text-[8px] font-bold uppercase tracking-[0.18em] text-slate-400">Edit</div>
                                                <div class="mt-1 text-[13px] font-bold" :class="isSettingTabDirty(activeSettingTab) ? 'text-amber-600' : 'text-slate-500'">{{ isSettingTabDirty(activeSettingTab) ? 'Yes' : 'No' }}</div>
                                            </div>
                                        </div>

                                        <div v-if="!isSettingTabObject(activeSettingTab)" class="relative">
                                            <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                            <input
                                                :value="activeSettingValueSearch"
                                                @input="activeSettingValueSearch = $event.target.value"
                                                type="text"
                                                placeholder="Cari isi opsi..."
                                                class="w-full pl-9 pr-3 py-2.5 rounded-2xl border border-slate-200 bg-white text-[11px] text-slate-700 outline-none focus:border-ppp-accent focus:ring-2 focus:ring-ppp-accent/10" />
                                        </div>

                                        <div v-if="showSettingsBulkAdd && !isSettingTabObject(activeSettingTab)" class="rounded-3xl border border-slate-200 bg-white p-3">
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <div class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">Tambah Banyak</div>
                                                    <p class="mt-1 text-[11px] text-slate-500">Satu baris satu opsi.</p>
                                                </div>
                                                <button @click="showSettingsBulkAdd = false" class="icon-utility-button icon-utility-bordered">
                                                    <i class="fa-solid fa-xmark text-[11px]"></i>
                                                </button>
                                            </div>
                                            <textarea
                                                :value="settingsBulkAddText"
                                                @input="settingsBulkAddText = $event.target.value"
                                                rows="5"
                                                placeholder="contoh&#10;DRAFT&#10;PENDING&#10;DONE"
                                                class="mt-3 w-full rounded-2xl border border-slate-200 bg-white px-3 py-3 text-[11px] text-slate-700 outline-none focus:border-ppp-accent focus:ring-2 focus:ring-ppp-accent/10"></textarea>
                                            <div class="mt-3 flex flex-wrap justify-end gap-2">
                                                <button @click="settingsBulkAddText = ''" class="secondary-cta-button secondary-cta-neutral active:scale-95">Kosongkan</button>
                                                <button @click="applySettingsBulkAdd(activeSettingTab)" class="secondary-cta-button secondary-cta-link active:scale-95">
                                                    <i class="fa-solid fa-check text-[10px]"></i> Masukkan
                                                </button>
                                            </div>
                                        </div>

                                        <div v-if="isSettingTabObject(activeSettingTab)" class="space-y-3">
                                            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-[11px] leading-relaxed text-slate-500">
                                                JSON config tampil ringkas dalam card. Edit detail tetap di menu khusus agar struktur data aman.
                                            </div>
                                            <div class="grid gap-3">
                                                <div v-for="section in activeSettingObjectSections" :key="'mobile-' + section.title" class="rounded-3xl border border-slate-200 bg-white p-4">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <div class="text-[11px] font-bold text-slate-800">{{ section.title }}</div>
                                                        <div class="rounded-full bg-slate-100 px-2 py-0.5 text-[8px] font-bold uppercase tracking-[0.15em] text-slate-500">{{ section.items.length }} item</div>
                                                    </div>
                                                    <div class="mt-3 space-y-2">
                                                        <div v-for="item in section.items" :key="'mobile-' + section.title + '-' + item.label" class="rounded-2xl bg-slate-50 px-3 py-2">
                                                            <div class="text-[8px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ item.label }}</div>
                                                            <div class="mt-1 text-[11px] font-semibold leading-relaxed text-slate-700 whitespace-pre-wrap break-words">{{ item.value }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else-if="filteredActiveSettingEntries.length" class="space-y-2">
                                            <div v-for="entry in filteredActiveSettingEntries" :key="'mobile-' + activeSettingTab + '-' + entry.idx" class="rounded-3xl border border-slate-200 bg-white p-3">
                                                <div class="flex items-start gap-3">
                                                    <div class="mt-0.5 h-8 w-8 rounded-2xl bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500">{{ entry.idx + 1 }}</div>
                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex items-center justify-between gap-3">
                                                            <label class="text-[9px] font-bold uppercase tracking-[0.18em] text-slate-400">Nilai Opsi</label>
                                                            <span v-if="!String(entry.value || '').trim()" class="rounded-full bg-amber-100 px-2 py-0.5 text-[8px] font-bold uppercase tracking-[0.15em] text-amber-700">Kosong</span>
                                                        </div>
                                                        <input
                                                            :value="settingsDraft[activeSettingTab][entry.idx]"
                                                            @input="updateSettingOption(activeSettingTab, entry.idx, $event.target.value)"
                                                            :data-setting-key="activeSettingTab"
                                                            :data-setting-idx="entry.idx"
                                                            placeholder="Isi nilai opsi..."
                                                            class="mt-2 form-input-compact-white font-bold uppercase shadow-sm" />
                                                    </div>
                                                    <button @click="askSettingAction('delete', activeSettingTab, entry.idx)" class="icon-utility-button icon-utility-bordered icon-utility-danger mt-5">
                                                        <i class="fa-solid fa-trash text-[10px]"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="rounded-3xl border border-dashed border-slate-200 bg-white px-6 py-12 text-center">
                                            <div class="mx-auto h-12 w-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                                                <i class="fa-solid fa-folder-open text-sm"></i>
                                            </div>
                                            <p class="mt-4 text-[13px] font-bold text-slate-700">Tidak ada opsi yang cocok</p>
                                            <p class="mt-1 text-[11px] leading-relaxed text-slate-500">Coba ubah kata kunci pencarian, atau tambah opsi baru untuk kategori ini.</p>
                                        </div>
                                    </div>

                                    <div class="modal-footer-bar modal-footer-actions">
                                        <button @click="closeSettingsDetailModal" class="modal-secondary-button">Tutup</button>
                                        <button @click="saveSettingsBackend" :disabled="savingSettings || !settingsDirty" class="modal-primary-button">
                                            <i v-if="!savingSettings" class="fa-solid fa-floppy-disk text-xs"></i>
                                            <i v-else class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                                            {{ savingSettings ? 'Menyimpan...' : 'Simpan' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </teleport>
@endverbatim
