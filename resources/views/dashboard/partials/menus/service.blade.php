@verbatim
<!-- Service View -->
                    <div v-if="activeTab === 'service' && !tabDataLoaded['service']"
                        class="space-y-4 animate-fadeIn animate-pulse">
                        <div class="section-card section-card-shell">
                            <div class="px-6 py-4 border-b border-slate-50 flex gap-6">
                                <div class="h-3 bg-slate-200 rounded-full w-24"></div>
                                <div class="h-3 bg-slate-200 rounded-full w-32"></div>
                                <div class="h-3 bg-slate-200 rounded-full w-28"></div>
                                <div class="h-3 bg-slate-200 rounded-full flex-1"></div>
                            </div>
                            <div class="divide-y divide-slate-50">
                                <div v-for="i in 8" :key="'sk-service'+i" class="px-6 py-5 flex items-center gap-4">
                                    <div class="h-4 bg-slate-100 rounded-full w-36"></div>
                                    <div class="h-4 bg-slate-100 rounded-full w-44"></div>
                                    <div class="h-4 bg-slate-100 rounded-full w-28"></div>
                                    <div class="h-4 bg-slate-100 rounded-full flex-1"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="activeTab === 'service' && tabDataLoaded['service']" class="space-y-4 animate-fadeIn">
                        <div class="space-y-3">
                            <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                                <div v-for="c in serviceSummary.cards.slice(0, 5)" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
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

                        <div class="section-card section-card-shell">
                            <div class="table-toolbar-shell">
                                <div class="table-toolbar-shell__left">
                                    <div class="relative">
                                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                                        <input id="service-search" name="service_search" v-model="serviceSearch" type="text" autocomplete="off" aria-label="Cari service" placeholder="Cari customer / no service / tipe..." class="form-input-search" />
                                    </div>
                                </div>
                                <div class="table-toolbar-shell__right">
                                    <div class="relative group search-select-container">
                                        <button @click="openCalendar($event, 'filter', '', 'service')" class="select-trigger-button-compact">
                                            <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                            <template v-if="serviceDateRange.start">{{ formatShortDate(serviceDateRange.start) }}<span v-if="serviceDateRange.end"> - {{ formatShortDate(serviceDateRange.end) }}</span></template>
                                            <template v-else>Semua Tanggal</template>
                                            <i v-if="serviceDateRange.start" @click.stop="serviceDateRange = { start: '', end: '' }" class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-danger"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[1400px] table-fixed text-body-sm text-left border-collapse">
                                    <thead>
                                        <tr class="table-header-row">
                                            <th class="table-header-cell table-header-index table-freeze-index">No</th>
                                            <th class="table-header-cell table-header-action table-freeze-action">Aksi</th>
                                            <th class="table-header-cell text-left w-[130px]">No Service</th>
                                            <th class="table-header-cell text-left w-[110px]">Tanggal</th>
                                            <th class="table-header-cell text-left w-[16%]">Customer</th>
                                            <th class="table-header-cell text-left w-[130px]">No HP</th>
                                            <th class="table-header-cell text-left w-[16%]">Type Unit</th>
                                            <th class="table-header-cell text-left w-[130px]">IMEI/SN</th>
                                            <th class="table-header-cell text-left w-[14%]">Kerusakan</th>
                                            <th class="table-header-cell text-center w-[120px]">Status</th>
                                            <th class="table-header-cell text-left w-[16%]">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(row, idx) in pagedServiceData" :key="row.ID || idx" class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                            <td class="px-4 py-3 type-body text-slate-500 table-freeze-index">{{ (servicePage - 1) * 15 + idx + 1 }}</td>
                                            <td class="px-4 py-3 text-left table-freeze-action">
                                                <button @click="transferServiceClaim(row)" class="table-action-button table-action-compact" title="Transfer ke Proses Claim" aria-label="Transfer ke Proses Claim">
                                                    <i class="fa-solid fa-right-to-bracket text-body-sm"></i>
                                                </button>
                                            </td>
                                            <td class="px-4 py-3 text-left text-body font-semibold text-slate-800 break-words">{{ row.NO_SERVICE || row.no_service || '-' }}</td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600 whitespace-nowrap">{{ (row.TANGGAL || row.tanggal || row.TANGGAL_MASUK || row.tanggal_masuk) ? formatShortDate(row.TANGGAL || row.tanggal || row.TANGGAL_MASUK || row.tanggal_masuk) : '-' }}</td>
                                            <td class="px-4 py-3 text-left text-body text-slate-800 whitespace-nowrap">{{ row.NAMA_CUSTOMER || row.nama_customer || row.NAMA || row.nama || '-' }}</td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600 break-words">{{ row.WA_CUSTOMER || row.wa_customer || row.HP || row.hp || '-' }}</td>
                                            <td class="px-4 py-3 text-left text-body font-semibold text-slate-800 break-words">{{ row.TYPE_UNIT || row.type_unit || row.TIPE || row.tipe || row.MODEL || row.model || '-' }}</td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600 break-words">{{ row.IMEI_SN || row.imei_sn || row.IMEI || row.imei || '-' }}</td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600 break-words">{{ row.KERUSAKAN || row.kerusakan || '-' }}</td>
                                            <td class="px-4 py-3 text-center"><span :class="getStatusColor(row.STATUS)" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap">{{ row.STATUS || row.status || '-' }}</span></td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600 break-words">{{ row.KETERANGAN || row.keterangan || '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="table-pager-bar">
                                <div class="text-body-sm text-slate-400 font-medium">
                                    <template v-if="filteredServiceData.length > 0">{{ (servicePage - 1) * 15 + 1 }}-{{ Math.min(servicePage * 15, filteredServiceData.length) }} dari {{ filteredServiceData.length }} data</template>
                                    <template v-else>0 data</template>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button @click="servicePage--" :disabled="servicePage <= 1" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-left text-body-sm"></i></button>
                                    <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ servicePage }} / {{ serviceTotalPages }}</span>
                                    <button @click="servicePage++" :disabled="servicePage >= serviceTotalPages" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-right text-body-sm"></i></button>
                                </div>
                            </div>
                            <div v-if="serviceData.length === 0" class="flex flex-col items-center justify-center py-20 text-slate-400">
                                <i class="fa-solid fa-screwdriver-wrench text-4xl mb-4 opacity-20"></i>
                                <p class="text-body font-bold uppercase">Belum ada data service</p>
                            </div>
                        </div>
                    </div>

                    <teleport to="body">
                        <transition name="fade">
                            <div v-if="serviceModalOpen" class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                                <div @click="serviceModalOpen = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop"></div>
                                <div class="relative bg-white w-full md:max-w-3xl max-h-[90vh] overflow-y-auto radius-card shadow-2xl">
                                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                                        <div class="type-heading-sm text-slate-900">{{ csModalType === 'create' ? 'Tambah' : 'Edit' }} Service</div>
                                        <button @click="serviceModalOpen = false" aria-label="Tutup modal" class="icon-utility-button"><i class="fa-solid fa-xmark"></i></button>
                                    </div>
                                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="relative search-select-container"><label class="type-body-sm font-bold text-slate-400 uppercase mb-2 block">Tanggal</label><button type="button" @click="openCalendar($event, 'form', '', 'serviceTanggal')" class="select-trigger-button-form toolbar-trigger-field-form"><i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i><span :class="serviceForm['TANGGAL'] ? 'text-slate-700 font-medium' : 'text-slate-400'">{{ serviceForm['TANGGAL'] ? formatFullDate(serviceForm['TANGGAL']) : 'Pilih tanggal' }}</span></button></div>
                                        <div><label class="type-body-sm font-bold text-slate-400 uppercase mb-2 block">No Service</label><input v-model="serviceForm['NO_SERVICE']" type="text" class="form-input" /></div>
                                        <div><label class="type-body-sm font-bold text-slate-400 uppercase mb-2 block">Customer</label><input v-model="serviceForm['NAMA_CUSTOMER']" type="text" class="form-input" /></div>
                                        <div><label class="type-body-sm font-bold text-slate-400 uppercase mb-2 block">No HP</label><input v-model="serviceForm['WA_CUSTOMER']" type="text" class="form-input" /></div>
                                        <div><label class="type-body-sm font-bold text-slate-400 uppercase mb-2 block">Type Unit</label><input v-model="serviceForm['TYPE_UNIT']" type="text" class="form-input" /></div>
                                        <div><label class="type-body-sm font-bold text-slate-400 uppercase mb-2 block">IMEI/SN</label><input v-model="serviceForm['IMEI_SN']" type="text" class="form-input" /></div>
                                        <div><label class="type-body-sm font-bold text-slate-400 uppercase mb-2 block">Status</label><input v-model="serviceForm['STATUS']" type="text" class="form-input" /></div>
                                        <div><label class="type-body-sm font-bold text-slate-400 uppercase mb-2 block">Total</label><input v-model.number="serviceForm['TOTAL']" type="number" min="0" class="form-input" /></div>
                                        <div class="md:col-span-2"><label class="type-body-sm font-bold text-slate-400 uppercase mb-2 block">Kerusakan</label><textarea v-model="serviceForm['KERUSAKAN']" rows="3" class="form-input"></textarea></div>
                                        <div class="md:col-span-2"><label class="type-body-sm font-bold text-slate-400 uppercase mb-2 block">Keterangan</label><textarea v-model="serviceForm['KETERANGAN']" rows="3" class="form-input"></textarea></div>
                                    </div>
                                    <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2">
                                        <button @click="serviceModalOpen = false" class="primary-cta-button primary-cta-button--neutral">Batal</button>
                                        <button @click="saveService" :disabled="submitting" class="primary-cta-button">{{ submitting ? 'Menyimpan...' : 'Simpan' }}</button>
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </teleport>
@endverbatim
