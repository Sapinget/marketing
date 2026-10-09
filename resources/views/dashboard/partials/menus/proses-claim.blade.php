@verbatim
<!-- Proses Claim View -->
                    <div v-if="activeTab === 'proses_claim' && !tabDataLoaded['serviceClaims']"
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
                                <div v-for="i in 8" :key="'sk-pc'+i" class="px-6 py-5 flex items-center gap-4">
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
                    <div v-if="activeTab === 'proses_claim' && tabDataLoaded['serviceClaims']" class="space-y-4 animate-fadeIn">
                        <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                            <div v-for="c in serviceClaimSummary.cards.slice(0, 5)" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                                <div class="absolute -right-4 -bottom-4 opacity-5"><i :class="['fa-solid', c.icon, 'text-[120px]']"></i></div>
                                <p class="dashboard-summary-title">{{ c.label }}</p>
                                <div class="flex items-baseline gap-2"><span class="dashboard-summary-value">{{ c.value }}</span><span v-if="c.unit" class="dashboard-summary-unit">{{ c.unit }}</span></div>
                                <p :class="['text-body-sm font-bold mt-3', c.subColor]">{{ c.sub }}</p>
                            </div>
                        </div>

                        <div class="section-card section-card-shell">
                            <div class="table-toolbar-shell">
                                <div class="table-toolbar-shell__left">
                                    <div class="relative">
                                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                                        <input id="service-claim-search-desktop" name="service_claim_search_desktop" v-model="serviceClaimSearch" type="text" autocomplete="off" aria-label="Cari proses claim" placeholder="Cari customer / no service / tipe..." class="form-input-search" />
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[1900px] table-fixed text-body-sm text-left border-collapse">
                                    <thead>
                                        <tr class="table-header-row">
                                            <th class="table-header-cell table-header-index table-freeze-index">No</th>
                                            <th class="table-header-cell table-header-action table-freeze-action">Aksi</th>
                                            <th class="table-header-cell text-left w-[130px]">No Service</th>
                                            <th class="table-header-cell text-left w-[120px]">No Transaksi</th>
                                            <th class="table-header-cell text-left w-[110px]">Tgl Masuk</th>
                                            <th class="table-header-cell text-left w-[16%]">Customer</th>
                                            <th class="table-header-cell text-left w-[130px]">WA</th>
                                            <th class="table-header-cell text-left w-[14%]">Tipe</th>
                                            <th class="table-header-cell text-left w-[130px]">IMEI/SN</th>
                                            <th class="table-header-cell text-left w-[120px]">Garansi</th>
                                            <th class="table-header-cell text-left w-[120px]">Lokasi Klaim</th>
                                            <th class="table-header-cell text-left w-[110px]">Estimasi</th>
                                            <th class="table-header-cell text-left w-[110px]">Diambil</th>
                                            <th class="table-header-cell text-center w-[110px]">Status</th>
                                            <th class="table-header-cell text-left w-[18%]">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(row, idx) in pagedServiceClaimData" :key="row.ID || idx" class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                            <td class="px-4 py-3 type-body text-slate-500 table-freeze-index">{{ (serviceClaimPage - 1) * 15 + idx + 1 }}</td>
                                            <td class="px-4 py-3 text-left table-freeze-action">
                                                <button @click="openServiceClaimModal(row)" class="table-action-button table-action-compact" title="Edit / Lengkapi" aria-label="Edit / Lengkapi"><i class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                                            </td>
                                            <td class="px-4 py-3 text-body font-semibold text-slate-800 whitespace-nowrap">{{ row.NO_SERVICE || '-' }}</td>
                                            <td class="px-4 py-3 text-body text-slate-600 break-words">{{ row.NO_TRANSAKSI || '-' }}</td>
                                            <td class="px-4 py-3 text-body text-slate-600 whitespace-nowrap">{{ formatShortDate(row.TANGGAL_MASUK) }}</td>
                                            <td class="px-4 py-3 text-body text-slate-800 whitespace-nowrap">{{ row.NAMA_CUSTOMER || '-' }}</td>
                                            <td class="px-4 py-3 text-body text-slate-600 whitespace-nowrap">{{ row.WA_CUSTOMER || '-' }}</td>
                                            <td class="px-4 py-3 text-body font-semibold text-slate-800 break-words">{{ row.TIPE || '-' }}</td>
                                            <td class="px-4 py-3 text-body text-slate-600 break-words">{{ row.IMEI_SN || '-' }}</td>
                                            <td class="px-4 py-3 text-body text-slate-600 break-words">{{ row.GARANSI || '-' }}</td>
                                            <td class="px-4 py-3 text-body text-slate-600 break-words">{{ row.LOKASI_KLAIM || '-' }}</td>
                                            <td class="px-4 py-3 text-body text-slate-600 whitespace-nowrap">{{ formatShortDate(row.TANGGAL_ESTIMASI) }}</td>
                                            <td class="px-4 py-3 text-body text-slate-600 whitespace-nowrap">{{ formatShortDate(row.TANGGAL_DIAMBIL) }}</td>
                                            <td class="px-4 py-3 text-center"><span :class="getStatusColor(row.STATUS)" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap">{{ row.STATUS || '-' }}</span></td>
                                            <td class="px-4 py-3 text-body text-slate-600 break-words">{{ row.KETERANGAN || row.KERUSAKAN || '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="table-pager-bar">
                                <div class="text-body-sm text-slate-400 font-medium">
                                    <template v-if="filteredServiceClaimData.length > 0">{{ (serviceClaimPage - 1) * 15 + 1 }}-{{ Math.min(serviceClaimPage * 15, filteredServiceClaimData.length) }} dari {{ filteredServiceClaimData.length }} data</template>
                                    <template v-else>0 data</template>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button @click="serviceClaimPage--" :disabled="serviceClaimPage <= 1" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-left text-body-sm"></i></button>
                                    <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ serviceClaimPage }} / {{ serviceClaimTotalPages }}</span>
                                    <button @click="serviceClaimPage++" :disabled="serviceClaimPage >= serviceClaimTotalPages" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-right text-body-sm"></i></button>
                                </div>
                            </div>
                            <div v-if="serviceClaimsData.length === 0" class="flex flex-col items-center justify-center py-20 text-slate-400">
                                <i class="fa-solid fa-file-shield text-4xl mb-4 opacity-20"></i>
                                <p class="text-body font-bold uppercase">Belum ada data proses claim</p>
                            </div>
                        </div>
                    </div>

                    <!-- Proses Claim Modal (Desain konsisten dengan Modal Claim Garansi) -->
                    <teleport to="body">
                        <transition name="fade">
                            <div v-if="serviceClaimModalOpen"
                                class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                                <div @click="serviceClaimModalOpen = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop">
                                </div>
                                <div class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface">
                                    <div class="modal-header-bar modal-header-bar-sticky radius-sheet-top z-[2010]">
                                        <div class="modal-header-copy">
                                            <div class="modal-header-icon bg-danger text-light border border-danger">
                                                <i class="fa-solid fa-shield-heart"></i>
                                            </div>
                                            <div>
                                                <div class="type-heading-sm text-slate-900">Lengkapi Proses Claim</div>
                                                <div class="type-body-sm text-slate-400 uppercase mt-0.5">Customer Service</div>
                                            </div>
                                        </div>
                                        <button @click="serviceClaimModalOpen = false" aria-label="Tutup modal" class="icon-utility-button icon-utility-round">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                    <div class="p-6 overflow-y-auto space-y-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="form-section-card">
                                                <div class="form-section-title">Info Layanan & Transaksi</div>
                                                <div class="form-section-copy">Nomor referensi, tanggal estimasi, dan pengambilan.</div>
                                            </div>
                                            <div>
                                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-2">No Service (Service DB)</label>
                                                <input :value="serviceClaimForm['NO_SERVICE']" type="text" class="form-input bg-slate-50 cursor-not-allowed" disabled />
                                            </div>
                                            <div>
                                                <label for="service-claim-no-transaksi" class="type-body-sm font-bold text-slate-400 uppercase mb-2">No Transaksi</label>
                                                <input id="service-claim-no-transaksi" name="service_claim_no_transaksi" v-model="serviceClaimForm['NO_TRANSAKSI']" type="text" class="form-input" placeholder="Masukkan no transaksi" />
                                            </div>
                                            <div>
                                                <label for="service-claim-imei-sn" class="type-body-sm font-bold text-slate-400 uppercase mb-2">IMEI / SN</label>
                                                <input id="service-claim-imei-sn" name="service_claim_imei_sn" v-model="serviceClaimForm['IMEI_SN']" type="text" class="form-input" placeholder="Masukkan IMEI/SN" />
                                            </div>
                                            <div class="relative search-select-container">
                                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-2">Tanggal Estimasi</label>
                                                <button type="button" @click="openCalendar($event, 'form', '', 'serviceClaimEstimasi')" class="select-trigger-button-form toolbar-trigger-field-form">
                                                    <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                                    <span :class="serviceClaimForm['TANGGAL_ESTIMASI'] ? 'text-slate-700 font-medium' : 'text-slate-400'">
                                                        {{ serviceClaimForm['TANGGAL_ESTIMASI'] ? formatFullDate(serviceClaimForm['TANGGAL_ESTIMASI']) : 'Pilih Tanggal Estimasi' }}
                                                    </span>
                                                </button>
                                            </div>
                                            <div class="relative search-select-container">
                                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-2">Tanggal Diambil</label>
                                                <button type="button" @click="openCalendar($event, 'form', '', 'serviceClaimDiambil')" class="select-trigger-button-form toolbar-trigger-field-form">
                                                    <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                                    <span :class="serviceClaimForm['TANGGAL_DIAMBIL'] ? 'text-slate-700 font-medium' : 'text-slate-400'">
                                                        {{ serviceClaimForm['TANGGAL_DIAMBIL'] ? formatFullDate(serviceClaimForm['TANGGAL_DIAMBIL']) : 'Pilih Tanggal Diambil' }}
                                                    </span>
                                                </button>
                                            </div>

                                            <div class="form-section-card">
                                                <div class="form-section-title">Detail Klaim & Garansi</div>
                                                <div class="form-section-copy">Pilih lokasi klaim, opsi jenis garansi, dan catatan tambahan.</div>
                                            </div>

                                            <div class="relative search-select-container">
                                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-2">Lokasi Klaim</label>
                                                <button type="button" @click="toggleSearchSelect($event, 'service_claim_lokasi')" :aria-expanded="searchSelectOpen === 'service_claim_lokasi' ? 'true' : 'false'" class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                                    <span :class="serviceClaimForm['LOKASI_KLAIM'] ? 'text-slate-800 font-medium' : 'text-slate-400'">
                                                        {{ serviceClaimForm['LOKASI_KLAIM'] || 'Pilih Lokasi' }}
                                                    </span>
                                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                                </button>
                                                <transition name="fade">
                                                    <div v-if="searchSelectOpen === 'service_claim_lokasi'" :style="popoverStyle" class="search-select-popover">
                                                        <div class="relative mb-2">
                                                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                                            <input v-model="searchSelectQuery" type="text" autocomplete="off" placeholder="Cari lokasi..." class="form-input-popover" @click.stop />
                                                        </div>
                                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                                            <div v-for="opt in claimLokasiOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))" :key="opt" @click="serviceClaimForm['LOKASI_KLAIM'] = opt; searchSelectOpen = null" :class="['popover-option', serviceClaimForm['LOKASI_KLAIM'] === opt ? 'popover-option-active' : '']">
                                                                {{ opt }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </transition>
                                            </div>

                                            <div class="relative search-select-container">
                                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-2">Garansi</label>
                                                <button type="button" @click="toggleSearchSelect($event, 'service_claim_garansi')" :aria-expanded="searchSelectOpen === 'service_claim_garansi' ? 'true' : 'false'" class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                                    <span :class="serviceClaimForm['GARANSI'] ? 'text-slate-800 font-medium' : 'text-slate-400'">
                                                        {{ serviceClaimForm['GARANSI'] || 'Pilih Garansi' }}
                                                    </span>
                                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                                </button>
                                                <transition name="fade">
                                                    <div v-if="searchSelectOpen === 'service_claim_garansi'" :style="popoverStyle" class="search-select-popover">
                                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                                            <div v-for="opt in claimGaransiOptions" :key="opt" @click="serviceClaimForm['GARANSI'] = opt; searchSelectOpen = null" :class="['popover-option', serviceClaimForm['GARANSI'] === opt ? 'popover-option-active' : '']">
                                                                {{ opt }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </transition>
                                            </div>

                                            <div class="col-span-2">
                                                <label for="service-claim-keterangan" class="type-body-sm font-bold text-slate-400 uppercase mb-2">Keterangan Tambahan</label>
                                                <textarea id="service-claim-keterangan" name="service_claim_keterangan" v-model="serviceClaimForm['KETERANGAN']" rows="3" placeholder="Catatan kelengkapan claim..." class="form-input resize-none"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer-bar modal-footer-actions">
                                        <button @click="serviceClaimModalOpen = false" class="primary-cta-button primary-cta-button--neutral">Batal</button>
                                        <button @click="saveServiceClaim" :disabled="submitting" class="primary-cta-button">{{ submitting ? 'Menyimpan...' : 'Simpan' }}</button>
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </teleport>
@endverbatim
