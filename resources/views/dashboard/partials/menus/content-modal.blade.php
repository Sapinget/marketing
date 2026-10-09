@verbatim
<!-- Modal konten generik (master plan / ideation / dst) dan daftar colab; dirender di dashboard lama, pindah ke menu pemilik saat Batch F -->
    <!-- Colab List Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="showColabListModal"
                class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                <div @click="showColabListModal = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop">
                </div>
                <div
                    class="mobile-sheet modal-width-compact radius-sheet modal-sheet-surface">
                    <div class="modal-header-bar radius-sheet-top">
                        <div class="type-heading-sm font-bold text-slate-800">Riwayat Konten Colab</div>
                        <button @click="showColabListModal = false" class="icon-utility-button icon-utility-danger"
                            aria-label="Tutup"><i class="fa-solid fa-xmark text-sm"></i></button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-6">
                        <div v-if="budgetCalculations.colabList.length === 0"
                            class="text-center py-8 text-body text-slate-400">Belum ada konten colab terdaftar.
                        </div>
                        <div v-for="(item, idx) in budgetCalculations.colabList" :key="idx"
                            class="flex items-center justify-between py-2.5 border-b border-slate-50 last:border-0">
                            <div>
                                <p class="text-body font-bold text-slate-800">{{ item.Judul }}</p>
                                <p class="text-overline text-slate-400 mt-0.5">{{ item.Tanggal_Rencana }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-overline font-bold text-slate-600 flex-shrink-0 overflow-hidden">
                                    <img v-if="resolveUserAvatarUrl(item.colabPartner)"
                                        :src="resolveAvatarUrl(resolveUserAvatarUrl(item.colabPartner))"
                                        class="w-full h-full object-cover" alt="Foto Colab"
                                        @error="markMasterPlanEditorAvatarFailed(item.colabPartner)" />
                                    <span v-else>{{ masterPersonInitials(item.colabPartner) }}</span>
                                </div>
                                <div class="text-body text-slate-700 font-semibold truncate max-w-[80px]">{{ personDisplayName(item.colabPartner) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>

    <!-- Master Plan Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="modalOpen"
                class="fixed inset-0 z-[1000] overflow-y-auto custom-scrollbar flex items-end md:items-start justify-center md:p-6 overlay-motion-sheet">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm overlay-backdrop"></div>

                <div
                    class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface-mobile-center z-[1001]">
                <!-- Sticky Header -->
                <div
                    class="modal-header-bar modal-header-bar-sticky radius-sheet-top z-[1010]">
                    <div class="modal-header-copy">
                        <div
                                class="modal-header-icon bg-amber text-light">
                            <i
                                :class="['fa-solid text-body', modalType === 'create' ? 'fa-plus' : 'fa-pen-to-square']"></i>
                        </div>
                        <div>
                            <div class="type-heading-sm text-slate-900">{{ modalType === 'create' ? 'Tambah Plan Baru' : 'Edit Plan Konten' }}</div>
                            <div class="type-body-sm text-amber uppercase font-bold mt-0.5">Marketing Module
                            </div>
                        </div>
                    </div>
                    <button @click="modalOpen = false" aria-label="Tutup modal"
                        class="icon-utility-button icon-utility-round">
                        <i class="fa-solid fa-xmark text-body"></i>
                    </button>
                </div>

                <!-- Scrollable Body -->
                <div class="master-plan-modal-body p-4 md:p-5 overflow-y-auto custom-scrollbar flex-1">
                    <div class="space-y-5">
                        <section class="space-y-3">
                            <div class="master-plan-form-section">
                                <div class="form-section-title">Ringkasan Konten</div>
                                <div class="form-section-copy">Info inti plan, owner, status, dan jadwal kerja.</div>
                            </div>
                            <div class="master-plan-form-grid grid grid-cols-1 md:grid-cols-2 gap-3">
                                <!-- 1. Judul -->
                                <div class="md:col-span-2">
                                    <label for="master-plan-judul" class="type-body-sm font-bold text-slate-400 uppercase mb-2">Judul
                                        Konten <span class="text-danger">*</span></label>
                                    <input id="master-plan-judul" name="master_plan_judul" v-model="masterForm.Judul" type="text" placeholder="Contoh: Review iPhone 15 Pro"
                                        class="form-input-compact" />
                                </div>

                                <!-- 2. Link Folder Drive -->
                                <div class="md:col-span-2">
                                    <label for="master-plan-link-drive" class="type-body-sm font-bold text-slate-400 uppercase mb-2">Link
                                        Folder Drive (Materi/Raw)</label>
                                    <div class="relative">
                                        <i
                                            class="fa-brands fa-google-drive absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-body"></i>
                                        <input id="master-plan-link-drive" name="master_plan_link_drive" v-model="masterForm.Link_Drive" type="text"
                                            placeholder="https://drive.google.com/..." class="form-input-compact form-input-leading-icon" />
                                    </div>
                                </div>

                                <!-- 3. Format & Status Side-by-side -->
                                <div class="relative search-select-container">
                                    <label class="type-body-sm font-bold text-slate-400 uppercase mb-2">Format
                                        Konten <span class="text-danger">*</span></label>
                                    <div @click="toggleSearchSelect($event, 'format')"
                                        class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                        <span
                                            :class="masterForm.Format_Konten ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ masterForm.Format_Konten || 'Pilih Format' }}</span>
                                        <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                    </div>
                                    <transition name="fade">
                                        <div v-if="searchSelectOpen === 'format'" :style="popoverStyle"
                                            class="search-select-popover">
                                            <div class="relative mb-2">
                                                <i
                                                    class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                                <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                    autocomplete="off" aria-label="Cari format konten" placeholder="Cari format..."
                                                    class="form-input-popover" @click.stop />
                                            </div>
                                            <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                                <div v-for="opt in filteredFormatOptions" :key="opt" @click="selectFormat(opt)"
                                                    :class="['popover-option', masterForm.Format_Konten === opt ? 'popover-option-active' : '']">
                                                    {{ opt }} </div>
                                                <div v-if="filteredFormatOptions.length === 0"
                                                    class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                    Tidak ditemukan</div>
                                            </div>
                                        </div>
                                    </transition>
                                </div>

                                <div class="relative search-select-container">
                                    <label class="type-body-sm font-bold text-slate-400 uppercase mb-2">Status
                                        <span class="text-danger">*</span></label>
                                    <div @click="toggleSearchSelect($event, 'status')"
                                        class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                        <span class="text-slate-800 font-medium">{{ masterForm.Status }}</span>
                                        <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                    </div>
                                    <transition name="fade">
                                        <div v-if="searchSelectOpen === 'status'" :style="popoverStyle"
                                            class="search-select-popover">
                                            <div class="relative mb-2">
                                                <i
                                                    class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                                <input v-model="searchSelectQuery" type="text" name="status_search"
                                                    autocomplete="off" aria-label="Cari status master plan" placeholder="Cari status..."
                                                    class="form-input-popover" @click.stop />
                                            </div>
                                            <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                                <div v-for="opt in statusOptions.filter(opt => String(opt || '').toLowerCase().includes(searchSelectQuery.toLowerCase()))" :key="opt" @click="selectStatus(opt)"
                                                    :class="['popover-option', masterForm.Status === opt ? 'popover-option-active' : '']">
                                                    {{ opt }} </div>
                                                <div v-if="statusOptions.filter(opt => String(opt || '').toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                    class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                    Tidak ditemukan</div>
                                            </div>
                                        </div>
                                    </transition>
                                </div>

                                <!-- 6. Talent & Tanggal Rencana & Editor -->
                                <div class="relative search-select-container">
                                    <label class="type-body-sm font-bold text-slate-400 uppercase mb-2">Tanggal
                                        Rencana</label>
                                    <button type="button" @click="openCalendar($event, 'form', '', 'master')"
                                        class="select-trigger-button-form toolbar-trigger-field-form">
                                        <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                        <span
                                            :class="masterForm.Tanggal_Rencana ? 'text-slate-700 font-medium' : 'text-slate-400'">{{ masterForm.Tanggal_Rencana ? formatFullDate(masterForm.Tanggal_Rencana) : 'Pilih Tanggal' }}</span>
                                    </button>
                                </div>

                                <div class="relative search-select-container">
                                    <label class="type-body-sm font-bold text-slate-400 uppercase mb-2">Editor</label>
                                    <div @click="toggleSearchSelect($event, 'editor')"
                                        class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                        <span
                                            :class="masterForm.Editor ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ masterForm.Editor || 'Pilih Editor' }}</span>
                                        <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                    </div>
                                    <transition name="fade">
                                        <div v-if="searchSelectOpen === 'editor'" :style="popoverStyle"
                                            class="search-select-popover">
                                            <div class="relative mb-2">
                                                <i
                                                    class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                                <input v-model="searchSelectQuery" type="text" name="editor_search"
                                                    autocomplete="off" aria-label="Cari editor master plan" placeholder="Cari editor..."
                                                    class="form-input-popover" @click.stop />
                                            </div>
                                            <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                                <div @click="selectEditor('')"
                                                    :class="['popover-option', !masterForm.Editor ? 'popover-option-active' : '']">
                                                    - Tanpa Editor -
                                                </div>
                                                <div v-for="opt in filteredEditorOptions" :key="opt" @click="selectEditor(opt)"
                                                    :class="['popover-option', masterForm.Editor === opt ? 'popover-option-active' : '']">
                                                    {{ opt }} </div>
                                                <div v-if="filteredEditorOptions.length === 0"
                                                    class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                    Tidak ditemukan</div>
                                            </div>
                                        </div>
                                    </transition>
                                </div>

                                <div class="relative md:col-span-2 search-select-container">
                                    <label
                                        class="type-body-sm font-bold text-slate-400 uppercase mb-2">Talent</label>
                                    <div @click="toggleSearchSelect($event, 'talent')"
                                        class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form master-plan-multi-trigger">
                                        <div class="flex flex-wrap gap-1">
                                            <span v-if="!masterForm.Talent.length" class="text-slate-400">Pilih / cari
                                                talent...</span>
                                            <div v-for="talent in masterForm.Talent" :key="talent" class="multi-select-chip">
                                                {{ talent }}
                                                <i @click.stop="toggleTalent(talent)"
                                                    class="fa-solid fa-xmark hover:text-danger cursor-pointer"></i>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-chevron-down text-body-sm text-slate-300 flex-shrink-0"></i>
                                    </div>
                                    <transition name="fade">
                                        <div v-if="searchSelectOpen === 'talent'" :style="popoverStyle"
                                            class="search-select-popover">
                                            <div class="relative mb-2">
                                                <i
                                                    class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                                <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                    autocomplete="off" aria-label="Cari talent master plan" placeholder="Cari talent..."
                                                    class="form-input-popover" @click.stop />
                                            </div>
                                            <div class="max-h-48 overflow-y-auto custom-scrollbar space-y-1 p-1">
                                                <div v-for="opt in filteredTalentOptions" :key="opt" @click="toggleTalent(opt)"
                                                    :class="['popover-option-check', masterForm.Talent.includes(opt) ? 'popover-option-active' : '']">
                                                    <span>{{ opt }}</span>
                                                    <i v-if="masterForm.Talent.includes(opt)"
                                                        class="fa-solid fa-check text-body-sm"></i>
                                                    <div v-else
                                                        class="w-4 h-4 border-2 border-slate-200 rounded group-hover:border-ppp-accent transition-colors">
                                                    </div>
                                                </div>
                                                <div v-if="filteredTalentOptions.length === 0"
                                                    class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                    Tidak ditemukan</div>
                                            </div>
                                        </div>
                                    </transition>
                                </div>
                            </div>
                        </section>

                        <section class="space-y-3">
                            <div class="master-plan-form-section">
                                <div class="form-section-title">Distribusi & Asset</div>
                                <div class="form-section-copy">Platform tayang, link kerja, skrip, caption, dan metadata
                                    publish.</div>
                            </div>
                            <div class="master-plan-form-grid grid grid-cols-1 md:grid-cols-2 gap-3">
                                <!-- 4. Platforms -->
                                <div class="relative md:col-span-2 search-select-container">
                                    <label
                                        class="type-body-sm font-bold text-slate-400 uppercase mb-2">Platforms</label>
                                    <div @click="toggleSearchSelect($event, 'platforms')"
                                        class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form master-plan-multi-trigger">
                                        <div class="flex flex-wrap gap-1">
                                            <span v-if="!masterForm.Platforms.length" class="text-slate-400">Pilih
                                                Platform</span>
                                            <div v-for="p in masterForm.Platforms" :key="p" class="multi-select-chip">
                                                {{ p }}
                                                <i @click.stop="togglePlatform(p)"
                                                    class="fa-solid fa-xmark hover:text-danger"></i>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-chevron-down text-body-sm text-slate-300 flex-shrink-0"></i>
                                    </div>
                                    <transition name="fade">
                                        <div v-if="searchSelectOpen === 'platforms'" :style="popoverStyle"
                                            class="search-select-popover">
                                            <div class="relative mb-2">
                                                <i
                                                    class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                                <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                    autocomplete="off" aria-label="Cari platform master plan" placeholder="Cari platform..."
                                                    class="form-input-popover" @click.stop />
                                            </div>
                                            <div class="max-h-48 overflow-y-auto custom-scrollbar space-y-1 p-1">
                                                <div v-for="opt in filteredPlatformOptions" :key="opt"
                                                    @click="togglePlatform(opt)"
                                                    :class="['popover-option-check', masterForm.Platforms.includes(opt) ? 'popover-option-active' : '']">
                                                    <span>{{ opt }}</span>
                                                    <i v-if="masterForm.Platforms.includes(opt)"
                                                        class="fa-solid fa-check text-body-sm"></i>
                                                    <div v-else
                                                        class="w-4 h-4 border-2 border-slate-200 rounded group-hover:border-ppp-accent transition-colors">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </transition>
                                </div>

                                <!-- 5. Colab -->
                                <div class="relative md:col-span-2 search-select-container">
                                    <label
                                        class="type-body-sm font-bold text-slate-400 uppercase mb-2">Colab</label>
                                    <div @click="toggleSearchSelect($event, 'colab')"
                                        class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form master-plan-multi-trigger">
                                        <div class="flex flex-wrap gap-1">
                                            <span v-if="!masterForm.Colab.length" class="text-slate-400">Pilih / cari
                                                colab...</span>
                                            <div v-for="c in masterForm.Colab" :key="c" class="multi-select-chip">
                                                {{ c }}
                                                <i @click.stop="toggleColab(c)"
                                                    class="fa-solid fa-xmark hover:text-danger cursor-pointer"></i>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-chevron-down text-body-sm text-slate-300 flex-shrink-0"></i>
                                    </div>
                                    <transition name="fade">
                                        <div v-if="searchSelectOpen === 'colab'" :style="popoverStyle"
                                            class="search-select-popover">
                                            <div class="relative mb-2">
                                                <i
                                                    class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                                <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                    autocomplete="off" aria-label="Cari kolaborator master plan" placeholder="Cari colab..."
                                                    class="form-input-popover" @click.stop />
                                            </div>
                                            <div class="max-h-48 overflow-y-auto custom-scrollbar space-y-1 p-1">
                                                <div v-for="opt in filteredColabOptions" :key="opt" @click="toggleColab(opt)"
                                                    :class="['popover-option-check', masterForm.Colab.includes(opt) ? 'popover-option-active' : '']">
                                                    <span>{{ opt }}</span>
                                                    <i v-if="masterForm.Colab.includes(opt)"
                                                        class="fa-solid fa-check text-body-sm"></i>
                                                    <div v-else
                                                        class="w-4 h-4 border-2 border-slate-200 rounded group-hover:border-ppp-accent transition-colors">
                                                    </div>
                                                </div>
                                                <div v-if="filteredColabOptions.length === 0"
                                                    class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                    Tidak ditemukan</div>
                                            </div>
                                        </div>
                                    </transition>
                                </div>

                                <!-- 7. Distribution Meta (show when status SCHEDULE or PUBLISHED) -->
                                <template
                                    v-if="masterForm.Status && (masterForm.Status.toUpperCase() === 'SCHEDULE' || masterForm.Status.toUpperCase() === 'PUBLISHED' || masterForm.Status.toUpperCase() === 'DONE')">
                                    <div class="md:col-span-2">
                                        <div class="master-plan-form-section master-plan-form-section-inner">
                                            <div class="form-section-title">Distribution Details</div>
                                            <div class="form-section-copy">Link post, tipe distribusi, dan tanggal publish per platform.</div>
                                        </div>
                                        <div class="space-y-2">
                                            <div v-for="plat in masterForm.Platforms" :key="plat" class="master-plan-distribution-panel">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <i :class="getPlatformIcon(plat) + ' text-body text-slate-400'"></i>
                                                    <span class="text-body font-bold text-slate-700">{{ platformDisplayName(plat) }}</span>
                                                </div>
                                                <div class="master-plan-distribution-grid">
                                                    <div class="master-plan-distribution-field">
                                                        <label
                                                            :for="`master-distribution-link-${String(plat).toLowerCase().replace(/[^a-z0-9]+/g, '-')}`"
                                                            class="block text-overline font-bold text-slate-400 uppercase mb-1.5">Link
                                                            Post</label>
                                                        <input :id="`master-distribution-link-${String(plat).toLowerCase().replace(/[^a-z0-9]+/g, '-')}`" :name="`master_distribution_link_${String(plat).toLowerCase().replace(/[^a-z0-9]+/g, '_')}`" v-model="masterForm.Distribution_Meta[plat].link" type="text"
                                                            placeholder="https://..." class="form-input-compact" />
                                                    </div>
                                                    <div class="master-plan-distribution-field">
                                                        <label
                                                            class="block text-overline font-bold text-slate-400 uppercase mb-1.5">Type</label>
                                                        <div @click="toggleSearchSelect($event, 'distType_'+plat)"
                                                            class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form relative search-select-container">
                                                            <span
                                                                :class="masterForm.Distribution_Meta[plat].type ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ masterForm.Distribution_Meta[plat].type || 'Pilih Type' }}</span>
                                                            <i class="fa-solid fa-chevron-down text-overline text-slate-300"></i>
                                                            <transition name="fade">
                                                                <div v-if="searchSelectOpen === 'distType_'+plat"
                                                                    :style="popoverStyle"
                                                                    class="bg-white border border-slate-100 rounded-xl overflow-hidden p-1 animate-fadeIn">
                                                                    <div v-for="t in ['Regular','Colab','Ad']" :key="t"
                                                                        @click.stop="masterForm.Distribution_Meta[plat].type = t; searchSelectOpen = null"
                                                                        :class="['px-3 py-2 text-body rounded-lg cursor-pointer transition-all', masterForm.Distribution_Meta[plat].type === t ? 'popover-option-active' : '']">
                                                                        {{ t }} </div>
                                                                </div>
                                                            </transition>
                                                        </div>
                                                    </div>
                                                    <div class="master-plan-distribution-field">
                                                        <label
                                                            class="block text-overline font-bold text-slate-400 uppercase mb-1.5">Tanggal
                                                            Publish</label>
                                                        <button type="button" @click="openCalendar($event, 'published', plat)"
                                                            class="select-trigger-button-form toolbar-trigger-field-form">
                                                            <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                                            <span
                                                                :class="masterForm.Distribution_Meta[plat].date ? 'text-slate-700 font-medium' : 'text-slate-400'">
                                                                {{ masterForm.Distribution_Meta[plat].date ? formatShortDate(masterForm.Distribution_Meta[plat].date) : 'Pilih tanggal publish' }}
                                                            </span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- 8. Skrip & Caption -->
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label for="master-plan-skrip"
                                            class="block text-body-sm font-bold text-slate-400 uppercase">Skrip</label>
                                        <div
                                            class="master-plan-toggle-group">
                                            <button type="button" @click="masterForm.Skrip = ''"
                                                :class="['master-plan-toggle-button', masterForm.Skrip !== 'Tidak' ? 'master-plan-toggle-button-active' : '']">Ya</button>
                                            <button type="button" @click="masterForm.Skrip = 'Tidak'"
                                                :class="['master-plan-toggle-button', masterForm.Skrip === 'Tidak' ? 'master-plan-toggle-button-active' : '']">Tidak</button>
                                        </div>
                                    </div>
                                    <transition name="fade">
                                        <textarea id="master-plan-skrip" name="master_plan_skrip" v-if="masterForm.Skrip !== 'Tidak'" v-model="masterForm.Skrip" rows="4"
                                            placeholder="Isi skrip konten..."
                                            class="form-input-compact resize-none custom-scrollbar"></textarea>
                                </transition>
                            </div>
                            <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label for="master-plan-caption"
                                            class="block text-body-sm font-bold text-slate-400 uppercase">Caption</label>
                                        <div
                                            class="master-plan-toggle-group">
                                            <button type="button" @click="masterForm.Caption = ''"
                                                :class="['master-plan-toggle-button', masterForm.Caption !== 'Tidak' ? 'master-plan-toggle-button-active' : '']">Ya</button>
                                            <button type="button" @click="masterForm.Caption = 'Tidak'"
                                                :class="['master-plan-toggle-button', masterForm.Caption === 'Tidak' ? 'master-plan-toggle-button-active' : '']">Tidak</button>
                                        </div>
                                    </div>
                                    <transition name="fade">
                                        <textarea id="master-plan-caption" name="master_plan_caption" v-if="masterForm.Caption !== 'Tidak'" v-model="masterForm.Caption" rows="4"
                                            placeholder="Caption untuk posting..."
                                            class="form-input-compact resize-none custom-scrollbar"></textarea>
                                    </transition>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                    <!-- Sticky Footer -->
                    <div class="modal-footer-bar modal-footer-actions">
                        <button @click="modalOpen = false" class="primary-cta-button primary-cta-button--neutral flex-1">Batal</button>
                        <button @click="saveMasterPlan" :disabled="submitting" class="primary-cta-button primary-cta-button--accent flex-1">
                            <i v-if="submitting" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                            <i v-else class="fa-solid fa-floppy-disk text-xs"></i>
                            {{ submitting ? 'Menyimpan...' : (modalType === 'create' ? 'Simpan' : 'Update') }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
@endverbatim
