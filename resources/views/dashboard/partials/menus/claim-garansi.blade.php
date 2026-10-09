@verbatim
<!-- Claim Garansi View -->
                    <div v-if="activeTab === 'claim_garansi_asuransi' && !tabDataLoaded['claimGaransi']"
                        class="space-y-4 animate-fadeIn animate-pulse">
                        <div class="section-card section-card-shell">
                            <div class="px-6 py-4 border-b border-slate-50 flex gap-6">
                                <div class="h-3 bg-slate-200 rounded-full w-20"></div>
                                <div class="h-3 bg-slate-200 rounded-full w-28"></div>
                                <div class="h-3 bg-slate-200 rounded-full w-32"></div>
                                <div class="h-3 bg-slate-200 rounded-full flex-1"></div>
                                <div class="h-3 bg-slate-200 rounded-full w-16"></div>
                            </div>
                            <div class="divide-y divide-slate-50">
                                <div v-for="i in 8" :key="'sk-cg'+i" class="px-6 py-5 flex items-center gap-4">
                                    <div class="h-4 bg-slate-100 rounded-full w-44"></div>
                                    <div class="h-4 bg-slate-100 rounded-full w-24"></div>
                                    <div class="h-4 bg-slate-100 rounded-full w-28"></div>
                                    <div class="h-6 bg-slate-100 rounded-full w-16"></div>
                                    <div class="h-4 bg-slate-100 rounded-full flex-1"></div>
                                    <div class="flex gap-1">
                                        <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                                        <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="activeTab === 'claim_garansi_asuransi' && tabDataLoaded['claimGaransi']"
                        class="space-y-4 animate-fadeIn">
                        <!-- Summary cards -->
                        <div class="space-y-3">
                            <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
                                <div v-for="c in claimSummary.cards.slice(0, 5)" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
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
                                        <input id="claim-garansi-search-mobile" name="claim_garansi_search_mobile" v-model="claimGaransiSearch" type="text" placeholder="Cari customer / tipe / IMEI..." autocomplete="off" aria-label="Cari claim garansi mobile" class="form-input-search" />
                                    </div>
                                </div>
                                <div class="table-toolbar-shell__right">
                                    <div class="toolbar-actions">
                                        <button @click="openClaimGaransiModal('create')" class="primary-cta-button primary-cta-button--accent primary-cta-button--icon-only active:scale-95" aria-label="Tambah Claim Garansi"><i class="fa-solid fa-plus"></i></button>
                                        <button @click="exportExcel" class="primary-cta-button primary-cta-button--success primary-cta-button--icon-only active:scale-95" aria-label="Export Excel"><i class="fa-solid fa-file-excel"></i></button>
                                        <button @click="exportPdf" class="primary-cta-button primary-cta-button--danger primary-cta-button--icon-only active:scale-95" aria-label="Export PDF"><i class="fa-solid fa-file-pdf"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div v-if="filteredClaimGaransiData.length === 0"
                                    class="bg-white radius-card border border-slate-100 p-10 text-center text-body text-slate-400">
                                    Belum ada data claim garansi
                                </div>
                                <div v-for="(row, idx) in pagedClaimGaransiData" :key="'cg-mobile-' + (row.ID || idx)"
                                    class="stat-card mobile-record-card mobile-data-card motion-stagger-item"
                                    :style="getStaggerStyle(idx)">
                                    <div class="mobile-data-card__header">
                                        <span
                                            :class="['px-2.5 py-1 rounded-full text-overline font-bold uppercase', getStatusColor(row.STATUS)]">
                                            {{ row.STATUS || '-' }}
                                        </span>
                                        <span class="type-body-sm text-slate-400 font-bold uppercase">
                                            {{ row.LOKASI_KLAIM || '-' }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="mobile-data-card__title line-clamp-2">{{ row.NAMA_CUSTOMER || row.TIPE || '-' }}</p>
                                        <div class="mobile-data-card__meta mt-2">
                                            <span
                                                class="px-2.5 py-1 rounded-lg bg-secondary text-light text-overline font-bold uppercase">
                                                {{ row.GARANSI || '-' }}
                                            </span>
                                            <span
                                                class="px-2.5 py-1 rounded-lg bg-secondary text-light text-overline font-bold uppercase">
                                                {{ row.TIPE || '-' }}
                                            </span>
                                        </div>
                                        <p class="type-body-sm text-slate-400 mt-2 line-clamp-1">
                                            {{ row.KERUSAKAN || '-' }}
                                        </p>
                                    </div>
                                    <div class="mobile-data-card__summary">
                                        <div>
                                            <div class="type-body-sm text-slate-400 uppercase">Masuk</div>
                                            <div class="type-body font-bold text-slate-700">{{ formatShortDate(row.TANGGAL_MASUK) }}</div>
                                        </div>
                                        <div>
                                            <div class="type-body-sm text-slate-400 uppercase">Diambil</div>
                                            <div class="type-body font-bold text-slate-700">{{ formatShortDate(row.TANGGAL_DIAMBIL) }}</div>
                                        </div>
                                    </div>
                                    <div class="mobile-data-card__actions">
                                        <div class="type-body-sm text-slate-400 line-clamp-1">
                                            {{ row.NO_SERVICE || row.NO_TRANSAKSI || '-' }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button @click="openClaimGaransiModal('edit', row)"
                                                class="table-action-button table-action-compact" title="Edit"
                                                aria-label="Edit"><i class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                                            <button @click="deleteClaimGaransi(row.ID)"
                                                class="table-action-button table-action-compact table-action-danger"
                                                title="Hapus" aria-label="Hapus"><i
                                                    class="fa-solid fa-trash-can text-body-sm"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-center gap-2 py-2">
                                <button @click="claimPage--" :disabled="claimPage <= 1" aria-label="Halaman sebelumnya"
                                    class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-left text-body-sm"></i></button>
                                <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ claimPage }} / {{ claimTotalPages }}</span>
                                <button @click="claimPage++" :disabled="claimPage >= claimTotalPages"
                                    aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-right text-body-sm"></i></button>
                            </div>
                        </div>
                        <div class="hidden md:block section-card section-card-shell">
                            <div class="table-toolbar-shell">
                                <div class="table-toolbar-shell__left">
                                    <div class="relative">
                                        <i
                                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                                        <input id="claim-garansi-search-desktop" name="claim_garansi_search_desktop" v-model="claimGaransiSearch" type="text"
                                            autocomplete="off" aria-label="Cari claim garansi desktop"
                                            placeholder="Cari customer / tipe / IMEI..." class="form-input-search" />
                                    </div>
                                </div>
                                <div class="table-toolbar-shell__right">
                                    <div class="relative search-select-container">
                                        <button @click="toggleSearchSelect($event, 'filter_claim_status')"
                                            class="select-trigger-button toolbar-trigger-field">
                                            <i class="fa-solid fa-circle-dot text-body-sm text-slate-400"></i>
                                            <span class="truncate">{{ claimGaransiStatusFilter || 'Semua Status' }}</span>
                                            <i v-if="claimGaransiStatusFilter" @click.stop="claimGaransiStatusFilter = ''"
                                                class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-danger"></i>
                                            <i v-else class="fa-solid fa-chevron-down text-overline text-slate-400 ml-auto"></i>
                                        </button>
                                        <transition name="fade">
                                            <div v-if="searchSelectOpen === 'filter_claim_status'" :style="popoverStyle"
                                                class="search-select-popover">
                                                <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                                    <div @click="claimGaransiStatusFilter = ''; searchSelectOpen = null"
                                                        :class="['popover-option', !claimGaransiStatusFilter ? 'popover-option-active' : '']">
                                                        Semua Status</div>
                                                    <div v-for="opt in claimStatusOptions" :key="opt"
                                                        @click="claimGaransiStatusFilter = opt; searchSelectOpen = null"
                                                        :class="['popover-option', claimGaransiStatusFilter === opt ? 'popover-option-active' : '']">
                                                        {{ opt }} </div>
                                                </div>
                                            </div>
                                        </transition>
                                    </div>
                                    <div class="relative search-select-container">
                                        <button @click="toggleSearchSelect($event, 'filter_claim_garansi')"
                                            class="select-trigger-button toolbar-trigger-field">
                                            <i class="fa-solid fa-shield text-body-sm text-slate-400"></i>
                                            <span class="truncate">{{ claimGaransiGaransiFilter || 'Semua Garansi' }}</span>
                                            <i v-if="claimGaransiGaransiFilter" @click.stop="claimGaransiGaransiFilter = ''"
                                                class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-danger"></i>
                                            <i v-else class="fa-solid fa-chevron-down text-overline text-slate-400 ml-auto"></i>
                                        </button>
                                        <transition name="fade">
                                            <div v-if="searchSelectOpen === 'filter_claim_garansi'"
                                                :style="popoverStyle" class="search-select-popover">
                                                <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                                    <div @click="claimGaransiGaransiFilter = ''; searchSelectOpen = null"
                                                        :class="['popover-option', !claimGaransiGaransiFilter ? 'popover-option-active' : '']">
                                                        Semua Garansi</div>
                                                    <div v-for="opt in claimGaransiOptions" :key="opt"
                                                        @click="claimGaransiGaransiFilter = opt; searchSelectOpen = null"
                                                        :class="['popover-option', claimGaransiGaransiFilter === opt ? 'popover-option-active' : '']">
                                                        {{ opt }} </div>
                                                </div>
                                            </div>
                                        </transition>
                                    </div>
                                    <div class="toolbar-actions toolbar-actions--desktop-icon-only">
<button @click="openClaimGaransiModal('create')"
                                             class="primary-cta-button primary-cta-button--accent primary-cta-button--icon-only active:scale-95"
                                             aria-label="Tambah Claim Garansi"><i
                                                 class="fa-solid fa-plus"></i></button>
                                        <button @click="exportExcel"
                                            class="primary-cta-button primary-cta-button--success primary-cta-button--icon-only active:scale-95" aria-label="Export Excel"><i
                                                class="fa-solid fa-file-excel"></i></button>
                                        <button @click="exportPdf"
                                            class="primary-cta-button primary-cta-button--danger primary-cta-button--icon-only active:scale-95" aria-label="Export PDF"><i
                                                class="fa-solid fa-file-pdf"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[1500px] table-fixed text-body-sm text-left border-collapse">
                                    <thead>
                                        <tr class="table-header-row">
                                            <th class="table-header-cell table-header-index table-freeze-index">#</th>
                                            <th class="table-header-cell table-header-action table-freeze-action">Aksi</th>
                                            <th class="table-header-cell text-left w-[18%]">Nama Customer</th>
                                            <th class="table-header-cell text-left w-[120px]">No Service</th>
                                            <th class="table-header-cell text-left w-[130px]">No Transaksi</th>
                                            <th class="table-header-cell text-left w-[110px]">Tgl Masuk</th>
                                            <th class="table-header-cell text-left w-[110px]">Tgl Diambil</th>
                                            <th class="table-header-cell text-left w-[14%]">Tipe</th>
                                            <th class="table-header-cell text-left w-[130px]">IMEI</th>
                                            <th class="table-header-cell text-left w-[120px]">Lokasi Klaim</th>
                                            <th class="table-header-cell text-center w-[110px]">Status</th>
                                            <th class="table-header-cell text-left w-[120px]">Garansi</th>
                                            <th class="table-header-cell text-left w-[18%]">Kerusakan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(row, idx) in pagedClaimGaransiData" :key="row.ID || idx"
                                            class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                            <td class="px-4 py-3 text-center text-body-sm font-bold text-slate-400 tabular-nums table-freeze-index">{{ idx + 1 }}</td>
                                            <td class="px-4 py-3 text-left table-freeze-action">
                                                <div class="flex items-center gap-1.5">
                                                    <button @click="openClaimGaransiModal('edit', row)"
                                                        class="table-action-button table-action-compact" title="Edit"
                                                        aria-label="Edit"><i
                                                            class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                                                    <button @click="deleteClaimGaransi(row.ID)"
                                                        class="table-action-button table-action-compact table-action-danger"
                                                        title="Hapus" aria-label="Hapus"><i
                                                            class="fa-solid fa-trash-can text-body-sm"></i></button>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-left text-body font-semibold text-slate-800 break-words">{{ row.NAMA_CUSTOMER || '-' }}</td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600">{{ row.NO_SERVICE || '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600">{{ row.NO_TRANSAKSI || '-' }}</td>
                                            <td class="px-4 py-3 text-left type-body text-slate-500 whitespace-nowrap">{{ formatShortDate(row.TANGGAL_MASUK) }}</td>
                                            <td class="px-4 py-3 text-left type-body text-slate-500 whitespace-nowrap">{{ formatShortDate(row.TANGGAL_DIAMBIL) }}</td>
                                            <td class="px-4 py-3 text-left text-body text-slate-700 font-semibold break-words">{{ row.TIPE || '-' }}</td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600 break-words">{{ row.IMEI || '-' }}</td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600 break-words">{{ row.LOKASI_KLAIM || '-' }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <span :class="getStatusColor(row.STATUS)"
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap">{{ row.STATUS || '-' }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600">{{ row.GARANSI || '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600 break-words">{{ row.KERUSAKAN || '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div
                                class="table-pager-bar">
                                <div class="text-body-sm text-slate-400 font-medium">
                                    <template v-if="filteredClaimGaransiData.length > 0">{{ (claimPage - 1) * 15 + 1 }}-{{ Math.min(claimPage * 15, filteredClaimGaransiData.length) }} dari {{ filteredClaimGaransiData.length }} data</template>
                                    <template v-else>0 data</template>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button @click="claimPage--" :disabled="claimPage <= 1"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-left text-body-sm"></i></button>
                                    <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ claimPage }} / {{ claimTotalPages }}</span>
                                    <button @click="claimPage++" :disabled="claimPage >= claimTotalPages"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-right text-body-sm"></i></button>
                                </div>
                            </div>
                            <div v-if="claimGaransiData.length === 0"
                                class="flex flex-col items-center justify-center py-20 text-slate-400">
                                <i class="fa-solid fa-shield-heart text-4xl mb-4 opacity-20"></i>
                                <p class="text-body font-bold uppercase">Belum ada data claim garansi
                                </p>
                            </div>
                        </div>
                    </div>

    <!-- Claim Garansi Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="claimGaransiModalOpen"
                class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                <div @click="claimGaransiModalOpen = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop">
                </div>
                <div
                    class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface">
                    <div
                        class="modal-header-bar modal-header-bar-sticky radius-sheet-top z-[2010]">
                        <div class="modal-header-copy">
                            <div
                                class="modal-header-icon bg-danger text-light border border-danger">
                                <i class="fa-solid fa-shield-heart"></i>
                            </div>
                            <div>
                                <div class="type-heading-sm text-slate-900">{{ csModalType === 'create' ? 'Tambah' : 'Edit' }} Claim Garansi</div>
                                <div class="type-body-sm text-slate-400 uppercase mt-0.5">Customer
                                    Service</div>
                            </div>
                        </div>
                        <button @click="claimGaransiModalOpen = false" aria-label="Tutup modal"
                            class="icon-utility-button icon-utility-round"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="p-6 overflow-y-auto space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-section-card">
                                <div class="form-section-title">Info Customer</div>
                                <div class="form-section-copy">Data pelanggan, kontak, dan timeline layanan.</div>
                            </div>
                            <div class="col-span-2">
                                <label for="claim-nama-customer" class="type-body-sm font-bold text-slate-400 uppercase mb-2">Nama
                                    Customer</label>
                                <input id="claim-nama-customer" name="claim_nama_customer" v-model="claimGaransiForm['NAMA_CUSTOMER']" type="text" class="form-input" />
                            </div>
                            <div>
                                <label for="claim-no-service" class="type-body-sm font-bold text-slate-400 uppercase mb-2">No
                                    Service</label>
                                <input id="claim-no-service" name="claim_no_service" v-model="claimGaransiForm['NO_SERVICE']" type="text" class="form-input" />
                            </div>
                            <div>
                                <label for="claim-no-transaksi" class="type-body-sm font-bold text-slate-400 uppercase mb-2">No
                                    Transaksi</label>
                                <input id="claim-no-transaksi" name="claim_no_transaksi" v-model="claimGaransiForm['NO_TRANSAKSI']" type="text" class="form-input" />
                            </div>
                            <div class="relative search-select-container">
                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-2">Tanggal
                                    Masuk</label>
                                <button type="button" @click="openCalendar($event, 'form', '', 'claimGaransi1')"
                                    class="select-trigger-button-form toolbar-trigger-field-form">
                                    <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                    <span
                                        :class="claimGaransiForm['TANGGAL_MASUK'] ? 'text-slate-700 font-medium' : 'text-slate-400'">{{ claimGaransiForm['TANGGAL_MASUK'] ? formatFullDate(claimGaransiForm['TANGGAL_MASUK']) : 'Pilih Tanggal' }}</span>
                                </button>
                            </div>
                            <div class="relative search-select-container">
                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-2">Tanggal
                                    Estimasi</label>
                                <button type="button" @click="openCalendar($event, 'form', '', 'claimGaransi3')"
                                    class="select-trigger-button-form toolbar-trigger-field-form">
                                    <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                    <span
                                        :class="claimGaransiForm['TANGGAL_ESTIMASI'] ? 'text-slate-700 font-medium' : 'text-slate-400'">{{ claimGaransiForm['TANGGAL_ESTIMASI'] ? formatFullDate(claimGaransiForm['TANGGAL_ESTIMASI']) : 'Pilih Tanggal' }}</span>
                                </button>
                            </div>
                            <div class="relative search-select-container">
                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-2">Tanggal
                                    Diambil</label>
                                <button type="button" @click="openCalendar($event, 'form', '', 'claimGaransi2')"
                                    class="select-trigger-button-form toolbar-trigger-field-form">
                                    <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                    <span
                                        :class="claimGaransiForm['TANGGAL_DIAMBIL'] ? 'text-slate-700 font-medium' : 'text-slate-400'">{{ claimGaransiForm['TANGGAL_DIAMBIL'] ? formatFullDate(claimGaransiForm['TANGGAL_DIAMBIL']) : 'Pilih Tanggal' }}</span>
                                </button>
                            </div>
                            <div>
                                <label for="claim-wa-customer" class="type-body-sm font-bold text-slate-400 uppercase mb-2">WA
                                    Customer</label>
                                <input id="claim-wa-customer" name="claim_wa_customer" v-model="claimGaransiForm['WA_CUSTOMER']" type="text" placeholder="08xxx"
                                    class="form-input" />
                            </div>
                            <div>
                                <label for="claim-wa2-customer" class="type-body-sm font-bold text-slate-400 uppercase mb-2">WA
                                    2 Customer</label>
                                <input id="claim-wa2-customer" name="claim_wa2_customer" v-model="claimGaransiForm['WA2_CUSTOMER']" type="text"
                                    placeholder="08xxx (opsional)" class="form-input" />
                            </div>
                            <div class="form-section-card">
                                <div class="form-section-title">Info Unit & Service</div>
                                <div class="form-section-copy">Identitas unit, status klaim, dan detail kerusakan.</div>
                            </div>
                            <div class="relative search-select-container">
                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-2">Tipe
                                    Unit</label>
                                <button type="button" @click="toggleSearchSelect($event, 'claim_tipe')"
                                    :aria-expanded="searchSelectOpen === 'claim_tipe' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="claimGaransiForm['TIPE'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ claimGaransiForm['TIPE'] || 'Pilih Tipe Unit' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'claim_tipe'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari tipe claim garansi" placeholder="Cari tipe..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in sharedUnitTypeOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="claimGaransiForm['TIPE'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', claimGaransiForm['TIPE'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="sharedUnitTypeOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div>
                                <label for="claim-imei"
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">IMEI</label>
                                <input id="claim-imei" name="claim_imei" v-model="claimGaransiForm['IMEI']" type="text" class="form-input" />
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Seri</label>
                                <button type="button" @click="toggleSearchSelect($event, 'claim_seri')"
                                    :aria-expanded="searchSelectOpen === 'claim_seri' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="claimGaransiForm['SERI'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ claimGaransiForm['SERI'] || 'Pilih Seri' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'claim_seri'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari seri claim garansi" placeholder="Cari seri..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in claimSeriOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="claimGaransiForm['SERI'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', claimGaransiForm['SERI'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="claimSeriOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Model</label>
                                <button type="button" @click="toggleSearchSelect($event, 'claim_model')"
                                    :aria-expanded="searchSelectOpen === 'claim_model' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="claimGaransiForm['MODEL'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ claimGaransiForm['MODEL'] || 'Pilih Model' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'claim_model'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari model claim garansi" placeholder="Cari model..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in sharedUnitTypeOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="claimGaransiForm['MODEL'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', claimGaransiForm['MODEL'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="sharedUnitTypeOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div>
                                <label for="claim-hp-pinjaman" class="type-body-sm font-bold text-slate-400 uppercase mb-2">HP
                                    Pinjaman</label>
                                <input id="claim-hp-pinjaman" name="claim_hp_pinjaman" v-model="claimGaransiForm['HP_PINJAMAN']" type="text" class="form-input" />
                            </div>
                            <div>
                                <label for="claim-imei-pinjaman" class="type-body-sm font-bold text-slate-400 uppercase mb-2">IMEI
                                    Pinjaman</label>
                                <input id="claim-imei-pinjaman" name="claim_imei_pinjaman" v-model="claimGaransiForm['IMEI_PINJAMAN']" type="text" class="form-input" />
                            </div>
                            <div class="relative search-select-container">
                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-2">Lokasi
                                    Klaim</label>
                                <button type="button" @click="toggleSearchSelect($event, 'lokasi_klaim')"
                                    :aria-expanded="searchSelectOpen === 'lokasi_klaim' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="claimGaransiForm['LOKASI_KLAIM'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ claimGaransiForm['LOKASI_KLAIM'] || 'Pilih Lokasi' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'lokasi_klaim'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari lokasi claim garansi" placeholder="Cari lokasi..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in claimLokasiOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="claimGaransiForm['LOKASI_KLAIM'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', claimGaransiForm['LOKASI_KLAIM'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="claimLokasiOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-1.5">Status</label>
                                <button type="button" @click="toggleSearchSelect($event, 'claim_status')"
                                    :aria-expanded="searchSelectOpen === 'claim_status' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="claimGaransiForm['STATUS'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ claimGaransiForm['STATUS'] || 'Pilih Status' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'claim_status'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in claimStatusOptions" :key="opt"
                                                @click="claimGaransiForm['STATUS'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', claimGaransiForm['STATUS'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Garansi</label>
                                <button type="button" @click="toggleSearchSelect($event, 'claim_garansi')"
                                    :aria-expanded="searchSelectOpen === 'claim_garansi' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="claimGaransiForm['GARANSI'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ claimGaransiForm['GARANSI'] || 'Pilih Garansi' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'claim_garansi'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in claimGaransiOptions" :key="opt"
                                                @click="claimGaransiForm['GARANSI'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', claimGaransiForm['GARANSI'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="col-span-2">
                                <label for="claim-kerusakan"
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Kerusakan</label>
                                <textarea id="claim-kerusakan" name="claim_kerusakan" v-model="claimGaransiForm['KERUSAKAN']" rows="3"
                                    class="form-input resize-none"></textarea>
                            </div>
                            <div class="col-span-2">
                                <label for="claim-keterangan"
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Keterangan</label>
                                <textarea id="claim-keterangan" name="claim_keterangan" v-model="claimGaransiForm['KETERANGAN']" rows="2"
                                    placeholder="Catatan tambahan..." class="form-input resize-none"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer-bar modal-footer-actions">
                        <button @click="claimGaransiModalOpen = false" class="primary-cta-button primary-cta-button--neutral">Batal</button>
                        <button @click="saveClaimGaransi" :disabled="submitting" class="primary-cta-button">{{ submitting ? 'Menyimpan...' : 'Simpan' }}</button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
@endverbatim
