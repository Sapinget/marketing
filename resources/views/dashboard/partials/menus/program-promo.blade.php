@verbatim
<!-- Program Promo tab -->
                    <div v-if="activeTab === 'program_promo' && !tabDataLoaded['promo']"
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
                                <div v-for="i in 8" :key="'sk-pp'+i" class="px-6 py-5 flex items-center gap-4">
                                    <div class="h-4 bg-slate-100 rounded-full w-40"></div>
                                    <div class="h-6 bg-slate-100 rounded-full w-20"></div>
                                    <div class="h-4 bg-slate-100 rounded-full w-28"></div>
                                    <div class="h-4 bg-slate-100 rounded-full flex-1"></div>
                                    <div class="h-4 bg-slate-100 rounded-full w-24"></div>
                                    <div class="flex gap-1">
                                        <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                                        <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="activeTab === 'program_promo' && tabDataLoaded['promo']"
                        class="space-y-4 animate-fadeIn">
<!-- Summary cards -->
<div class="space-y-3">
    <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
        <div v-for="c in promoSummary.cards.slice(0, 5)" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
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




                        <!-- Tabel desktop -->
                        <div class="hidden md:block section-card section-card-shell">
                            <div class="table-toolbar-shell">
                                <div class="table-toolbar-shell__left">
                                    <div class="relative flex-1">
                                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                                        <input id="promo-search" name="promo_search" v-model="promoSearch" type="text" placeholder="Cari program..."
                                            autocomplete="off" aria-label="Cari program promo"
                                            class="form-input-search" />
                                    </div>
                                </div>
                                <div class="table-toolbar-shell__right">
                                    <div class="toolbar-actions toolbar-actions--desktop-icon-only">
<button @click="openPromoModal('create')"
                                             class="primary-cta-button primary-cta-button--accent primary-cta-button--icon-only active:scale-95"
                                             aria-label="Tambah Program Promo">
                                             <i class="fa-solid fa-plus"></i>
                                         </button>
                                        <button @click="exportPromoToPDF"
                                            class="primary-cta-button primary-cta-button--danger primary-cta-button--icon-only active:scale-95" aria-label="Export PDF">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-body-sm text-left border-collapse min-w-[860px]">
                                    <thead>
                                        <tr class="table-header-row">
                                            <th class="table-header-cell table-header-index table-freeze-index">
                                                #</th>
                                            <th class="table-header-cell table-header-action table-freeze-action">
                                                Aksi</th>
                                            <th class="table-header-cell">
                                                Program</th>
                                            <th class="table-header-cell w-32 text-center">
                                                Varian</th>
                                            <th class="table-header-cell">
                                                Benefit</th>
                                            <th class="table-header-cell">
                                                Rules</th>
                                            <th class="table-header-cell w-28 text-right">
                                                Harga</th>
                                            <th class="table-header-cell w-36 text-center">
                                                Periode</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="pagedPromoRows.length === 0">
                                            <td colspan="8" class="px-5 py-16 text-center text-body text-slate-400">
                                                <i class="fa-solid fa-bullhorn text-3xl mb-3 opacity-20 block"></i>
                                                Belum ada data program promo
                                            </td>
                                        </tr>
                                        <template v-for="(row, idx) in pagedPromoRows" :key="row.ID || idx">
                                            <tr v-if="row.isCategoryHeader" class="bg-slate-50">
                                                <td colspan="8"
                                                    class="px-5 py-2 text-body-sm font-bold text-slate-500 uppercase border-y border-slate-100">
                                                    <i class="fa-solid fa-layer-group mr-2 text-ppp-accent"></i>{{ row.name }}
                                                </td>
                                            </tr>
                                            <tr v-else
                                                class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors group">
                                                <td class="px-5 py-3.5 text-body-sm text-slate-400 text-center table-freeze-index">{{ row.indexInGroup }}</td>
                                                <td class="px-5 py-3.5 text-left table-freeze-action">
                                                    <div class="flex items-center gap-1.5">
                                                        <button @click="openPromoModal('edit', row)"
                                                            class="table-action-button table-action-compact"
                                                            title="Edit" aria-label="Edit"><i
                                                                class="fa-solid fa-pen-to-square text-overline"></i></button>
                                                        <button @click="deletePromo(row.ID)"
                                                            class="table-action-button table-action-compact table-action-danger"
                                                            title="Hapus" aria-label="Hapus"><i
                                                                class="fa-solid fa-trash-can text-overline"></i></button>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-3.5">
                                                    <p
                                                        class="text-body-sm font-bold text-slate-900 uppercase leading-tight">
                                                        {{ row.Program }}</p>
                                                </td>
                                                <td
                                                    class="px-5 py-3.5 text-center text-body font-semibold text-slate-600">
                                                    {{ row.Warna || '-' }}</td>
                                                <td
                                                    class="px-5 py-3.5 text-body text-slate-600 whitespace-pre-wrap max-w-[200px]">
                                                    {{ row.Benefit || '-' }}</td>
                                                <td
                                                    class="px-5 py-3.5 text-body-sm text-slate-500 whitespace-pre-wrap max-w-[180px]">
                                                    {{ row.Rules || '-' }}</td>
                                                <td
                                                    class="px-5 py-3.5 text-right text-body font-bold text-ppp-accent">
                                                    {{ row.Harga ? formatCurrency(row.Harga) : '-' }}</td>
                                                <td class="px-5 py-3.5 text-center text-body-sm text-slate-500 italic">{{ row.Periode || '-' }}</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <div
                                class="table-pager-bar">
                                <div class="text-body-sm text-slate-400 font-medium">{{ groupedPromoRows.length }}
                                    program</div>
                                <div class="flex items-center gap-1">
                                    <button @click="promoPage--" :disabled="promoPage <= 1"
                                        aria-label="Halaman sebelumnya"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-left text-body-sm"></i></button>
                                    <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ promoPage }} / {{ promoTotalPages }}</span>
                                    <button @click="promoPage++" :disabled="promoPage >= promoTotalPages"
                                        aria-label="Halaman berikutnya"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-right text-body-sm"></i></button>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile cards -->
                        <div class="md:hidden space-y-3">
                            <div v-if="filteredPromoData.length === 0"
                                class="bg-white radius-panel border border-slate-100 p-10 text-center text-body text-slate-400">
                                Belum ada data program promo</div>
                            <template v-for="(row, idx) in pagedPromoRows" :key="'mpr'+idx">
                                <div v-if="row.isCategoryHeader"
                                    class="px-2 py-1 text-overline font-bold text-slate-400 uppercase">
                                    <i class="fa-solid fa-layer-group mr-1 text-ppp-accent"></i>{{ row.name }}
                                </div>
                                <div v-else class="bg-white radius-panel border border-slate-100 p-4 space-y-2">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="text-body font-bold text-slate-900 uppercase">{{ row.Program }}</p>
                                        <span v-if="row.Warna"
                                            class="text-overline font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-lg whitespace-nowrap">{{ row.Warna }}</span>
                                    </div>
                                    <p v-if="row.Benefit" class="text-body-sm text-slate-600 whitespace-pre-wrap">{{ row.Benefit }}</p>
                                    <div class="flex items-center justify-between pt-2 border-t border-slate-50">
                                        <span class="text-body-sm text-slate-400 italic">{{ row.Periode || '-' }}</span>
                                        <div class="flex items-center gap-2">
                                            <span v-if="row.Harga" class="text-body font-bold text-ppp-accent">{{ formatCurrency(row.Harga) }}</span>
                                            <button @click="openPromoModal('edit', row)"
                                                class="table-action-button table-action-compact" title="Edit"
                                                aria-label="Edit"><i class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                                            <button @click="deletePromo(row.ID)"
                                                class="table-action-button table-action-compact table-action-danger"
                                                title="Hapus" aria-label="Hapus"><i
                                                    class="fa-solid fa-trash-can text-body-sm"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                    </div>

    <!-- Program Promo Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="promoModalOpen"
                class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                <div @click="promoModalOpen = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop">
                </div>
                <div
                    class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface">
                    <div
                        class="modal-header-bar modal-header-bar-sticky radius-sheet-top z-[2010]">
                        <div class="modal-header-copy">
                            <div
                                class="modal-header-icon bg-amber text-light border border-amber">
                                <i class="fa-solid fa-bullhorn"></i>
                            </div>
                            <div>
                                <div class="type-heading-sm text-slate-900">{{ promoModalType === 'create' ? 'Tambah Program' : 'Edit Program' }}</div>
                                <div class="type-body-sm text-slate-400 uppercase mt-0.5">Program &
                                    Promo</div>
                            </div>
                        </div>
                        <button @click="promoModalOpen = false" aria-label="Tutup modal"
                            class="icon-utility-button icon-utility-round"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="p-6 overflow-y-auto flex-1 space-y-4">
                        <!-- Kategori -->
                        <div>
                            <label class="type-body-sm font-bold text-slate-400 uppercase mb-2">Kategori
                                Promo</label>
                            <div class="relative search-select-container">
                                <div @click="toggleSearchSelect($event, 'promoKategori')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="promoForm.Kategori ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ promoForm.Kategori || 'Pilih Kategori' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </div>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'promoKategori'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in kategoriPromoOptions" :key="opt"
                                                @click="promoForm.Kategori = opt; searchSelectOpen = null"
                                                :class="['popover-option', promoForm.Kategori === opt ? 'popover-option-active' : '']">
                                                {{ opt }}</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                        </div>
                        <!-- Nama Program -->
                        <div>
                            <label for="promo-program" class="type-body-sm font-bold text-slate-400 uppercase mb-2">Nama
                                Program <span class="text-danger">*</span></label>
                            <input id="promo-program" name="promo_program" v-model="promoForm.Program" type="text" placeholder="Promo Cashback..."
                                class="form-input" />
                        </div>
                        <!-- Varian -->
                        <div>
                            <label for="promo-varian" class="type-body-sm font-bold text-slate-400 uppercase mb-2">Varian
                                / Unit</label>
                            <input id="promo-varian" name="promo_varian" v-model="promoForm.Warna" type="text" placeholder="Semua Tipe / Galaxy S25..."
                                class="form-input" />
                        </div>
                        <!-- Harga -->
                        <div>
                            <label for="promo-harga" class="type-body-sm font-bold text-slate-400 uppercase mb-2">Nominal
                                Potongan (Rp)</label>
                            <input id="promo-harga" name="promo_harga" v-model.number="promoForm.Harga" type="number" min="0"
                                class="form-input text-right" />
                        </div>
                        <!-- Periode -->
                        <div class="surface-panel-soft space-y-3">
                            <label
                                class="block text-body-sm font-bold text-slate-400 uppercase text-center">Periode
                                Berlaku</label>
                            <div class="grid grid-cols-2 gap-2">
                                <!-- Preset dropdown -->
                                <div class="relative search-select-container">
                                    <div @click="toggleSearchSelect($event, 'promoPeriode')"
                                        class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                        <span
                                            :class="promoPeriodePreset ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ promoPeriodePreset === 'stock' ? 'Selama Stok Ada' : promoPeriodePreset === 'custom' ? 'Tanggal Custom' : 'Pilih Preset' }}</span>
                                        <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                    </div>
                                    <transition name="fade">
                                        <div v-if="searchSelectOpen === 'promoPeriode'" :style="popoverStyle"
                                            class="search-select-popover">
                                            <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                                <div @click="promoPeriodePreset = 'stock'; applyPromoPeriodePreset(); searchSelectOpen = null"
                                                    :class="['popover-option', promoPeriodePreset === 'stock' ? 'popover-option-active' : '']">
                                                    Selama Stok Ada</div>
                                                <div @click="promoPeriodePreset = 'custom'; applyPromoPeriodePreset(); searchSelectOpen = null"
                                                    :class="['popover-option', promoPeriodePreset === 'custom' ? 'popover-option-active' : '']">
                                                    Tanggal Custom</div>
                                            </div>
                                        </div>
                                    </transition>
                                </div>
                                <!-- Date range pickers -->
                                <div class="flex gap-1.5">
                                    <button type="button" @click="openCalendar($event, 'form', '', 'promoDate1')"
                                        class="select-trigger-button-form toolbar-trigger-field-form">
                                        <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                        <span
                                            :class="promoTempDate.start ? 'text-slate-700 font-medium' : 'text-slate-400'">{{ promoTempDate.start ? formatShortDate(promoTempDate.start) : 'Mulai' }}</span>
                                    </button>
                                    <button type="button" @click="openCalendar($event, 'form', '', 'promoDate2')"
                                        class="select-trigger-button-form toolbar-trigger-field-form">
                                        <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                        <span
                                            :class="promoTempDate.end ? 'text-slate-700 font-medium' : 'text-slate-400'">{{ promoTempDate.end ? formatShortDate(promoTempDate.end) : 'Selesai' }}</span>
                                    </button>
                                </div>
                            </div>
                            <input id="promo-periode" name="promo_periode" v-model="promoForm.Periode" type="text"
                                placeholder="Ketik periode manual atau pilih preset di atas..."
                                class="form-input bg-white font-medium" />
                        </div>
                        <!-- Rules -->
                        <div>
                            <label for="promo-rules" class="type-body-sm font-bold text-slate-400 uppercase mb-2">S&K
                                / Rules</label>
                            <textarea id="promo-rules" name="promo_rules" v-model="promoForm.Rules" rows="3" placeholder="Syarat dan ketentuan berlaku..."
                                class="form-input resize-none"></textarea>
                        </div>
                        <!-- Benefit -->
                        <div>
                            <label for="promo-benefit"
                                class="type-body-sm font-bold text-slate-400 uppercase mb-2">Benefit</label>
                            <textarea id="promo-benefit" name="promo_benefit" v-model="promoForm.Benefit" rows="3" placeholder="Keuntungan yang didapat..."
                                class="form-input resize-none"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer-bar modal-footer-actions">
                        <button @click="promoModalOpen = false" class="primary-cta-button primary-cta-button--neutral">Batal</button>
                        <button @click="savePromo" :disabled="submitting" class="primary-cta-button">{{ submitting ? 'Menyimpan...' : 'Simpan' }}</button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
@endverbatim
