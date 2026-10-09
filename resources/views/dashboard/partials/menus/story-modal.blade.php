@verbatim
<!-- Modal story: dipakai menu story dan calendar (openEditStoryModal) -->
    <!-- Story Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="storyModalOpen"
                class="fixed inset-0 z-[1000] overflow-y-auto custom-scrollbar flex items-end md:items-start justify-center md:p-6 overlay-motion-sheet">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm overlay-backdrop"></div>
                <div
                    class="mobile-sheet modal-width-form relative w-full md:m-auto bg-white radius-sheet border border-slate-200 animate-fadeIn z-[1001] flex flex-col">
                    <!-- Sticky Header -->
                    <div
                        class="modal-header-bar modal-header-bar-sticky radius-sheet-top z-[1010]">
                        <div class="modal-header-copy">
                            <div
                                :class="['modal-header-icon text-white', storyModalType === 'create' ? 'bg-success' : 'bg-danger']">
                                <i
                                    :class="['fa-solid text-body', storyModalType === 'create' ? 'fa-plus' : 'fa-pen-to-square']"></i>
                            </div>
                            <div>
                                <div class="type-heading-sm text-slate-900">{{ storyModalType === 'create' ? 'Tambah Jadwal Story' : 'Edit Jadwal Story' }}</div>
                            </div>
                        </div>
                        <button @click="storyModalOpen = false" aria-label="Tutup modal"
                            class="icon-utility-button icon-utility-round">
                            <i class="fa-solid fa-xmark text-body"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-5 flex-1 overflow-y-auto custom-scrollbar">
                        <div class="bg-slate-50 border border-slate-100 p-4 rounded-2xl">
                            <label
                                class="block text-body-sm font-bold text-slate-500 uppercase mb-3 text-center">Kelompok
                                Jadwal</label>
                            <div class="segmented-control segmented-control--ios segmented-control--equal w-full justify-center" :data-index="storyForm.is_genap === 'Genap' ? 1 : 0">
                                <button type="button" @click="storyForm.is_genap = 'Ganjil'"
                                    :class="['segmented-control__item flex-1 text-center', storyForm.is_genap === 'Ganjil' ? 'segmented-control__item--active' : '']">GANJIL</button>
                                <button type="button" @click="storyForm.is_genap = 'Genap'"
                                    :class="['segmented-control__item flex-1 text-center', storyForm.is_genap === 'Genap' ? 'segmented-control__item--active' : '']">GENAP</button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Tanggal</label>
                                <button type="button" @click="openCalendar($event, 'form', '', 'story')"
                                    class="select-trigger-button-form toolbar-trigger-field-form">
                                    <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                    <span
                                        :class="storyForm.Tanggal ? 'text-slate-700 font-medium' : 'text-slate-400'">{{ storyForm.Tanggal ? formatFullDate(storyForm.Tanggal) : 'Pilih Tanggal' }}</span>
                                </button>
                            </div>
                            <div>
                                <label for="story-jam" class="type-body-sm font-bold text-slate-400 uppercase mb-2">Waktu
                                    Tayang (Jam) <span class="text-danger">*</span></label>
                                <input id="story-jam" name="story_jam" v-model="storyForm.Jam" type="time" class="form-input" />
                            </div>
                        </div>
                        <div>
                            <label for="story-konten" class="type-body-sm font-bold text-slate-400 uppercase mb-2">Story
                                (Konten) <span class="text-danger">*</span></label>
                            <input id="story-konten" name="story_konten" v-model="storyForm.Story" type="text" placeholder="Ketik ide konten..."
                                class="form-input uppercase" />
                        </div>
                        <div>
                            <label for="story-catatan" class="type-body-sm font-bold text-slate-400 uppercase mb-2">Internal
                                Note (Opsional)</label>
                            <textarea id="story-catatan" name="story_catatan" v-model="storyForm.Catatan" rows="3" placeholder="Catatan singkat..."
                                class="form-input custom-scrollbar"></textarea>
                        </div>
                        <div>
                            <label for="story-link" class="type-body-sm font-bold text-slate-400 uppercase mb-2">Link
                                Reference (Opsional)</label>
                            <input id="story-link" name="story_link" v-model="storyForm.Link" type="url" placeholder="https://..." class="form-input" />
                        </div>
                            <div class="relative search-select-container">
                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-1.5">Editor</label>
                                <div @click="toggleSearchSelect($event, 'unboxingEditor')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="unboxingForm.Editor ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ unboxingForm.Editor || 'Pilih Editor' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </div>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'unboxingEditor'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="unboxing_editor_search"
                                                autocomplete="off" aria-label="Cari editor unboxing" placeholder="Cari editor..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div @click="unboxingForm.Editor = ''; searchSelectOpen = null"
                                                :class="['popover-option', !unboxingForm.Editor ? 'popover-option-active' : '']">
                                                - Tanpa Editor -
                                            </div>
                                            <div v-for="opt in filteredEditorOptions" :key="opt"
                                                @click="unboxingForm.Editor = opt; searchSelectOpen = null"
                                                :class="['popover-option', unboxingForm.Editor === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="filteredEditorOptions.length === 0"
                                                class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                Tidak ditemukan</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-1.5">Status</label>
                            <div @click="toggleSearchSelect($event, 'storyStatus')"
                                class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                <span :class="storyForm.Status ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ storyForm.Status || 'Pilih Status' }}</span>
                                <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                            </div>
                            <transition name="fade">
                                <div v-if="searchSelectOpen === 'storyStatus'" :style="popoverStyle"
                                    class="search-select-popover">
                                    <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                        <div v-for="opt in (statusOptions.length ? statusOptions : ['DRAFT','TERJADWAL','PUBLISH'])"
                                            :key="opt" @click="storyForm.Status = opt; searchSelectOpen = null"
                                            :class="['popover-option', storyForm.Status === opt ? 'bg-danger text-white font-semibold' : 'text-slate-600 hover:bg-slate-50']">
                                            {{ opt }}</div>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>

                    <div class="modal-footer-bar modal-footer-actions">
                        <button @click="storyModalOpen = false" class="primary-cta-button primary-cta-button--neutral">Batal</button>
                        <button @click="saveStory" :disabled="submitting"
                            class="primary-cta-button primary-cta-button--danger">
                            <i v-if="submitting" class="fa-solid fa-spinner fa-spin"></i>
                            Simpan Story
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
@endverbatim
