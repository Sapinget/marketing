@verbatim
<!-- Order Online View -->
                    <div v-if="activeTab === 'orderan_online' && !tabDataLoaded['orderanOnline']"
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
                                <div v-for="i in 8" :key="'sk-oo'+i" class="px-6 py-5 flex items-center gap-4">
                                    <div class="h-4 bg-slate-100 rounded-full w-44"></div>
                                    <div class="h-4 bg-slate-100 rounded-full w-20"></div>
                                    <div class="h-4 bg-slate-100 rounded-full w-24"></div>
                                    <div class="h-4 bg-slate-100 rounded-full flex-1"></div>
                                    <div class="h-6 bg-slate-100 rounded-full w-14"></div>
                                    <div class="flex gap-1">
                                        <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                                        <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="activeTab === 'orderan_online' && tabDataLoaded['orderanOnline']"
                        class="space-y-4 animate-fadeIn">
                        <!-- Summary cards -->
                        <div class="space-y-3">
                            <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
                                <div v-for="c in orderanSummary.cards.slice(0, 5)" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
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
                                        <input id="order-online-search-mobile" name="order_online_search_mobile" v-model="orderanOnlineSearch" type="text" placeholder="Cari nama / ecommerce / no pesanan..." autocomplete="off" aria-label="Cari order online mobile" class="form-input-search" />
                                    </div>
                                </div>
                                <div class="table-toolbar-shell__right">
                                    <div class="relative group search-select-container">
                                        <button @click="openCalendar($event, 'filter', '', 'orderanOnline')" class="select-trigger-button-compact">
                                            <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                            <template v-if="orderanOnlineDateRange.start">{{ formatShortDate(orderanOnlineDateRange.start) }}<span v-if="orderanOnlineDateRange.end"> - {{ formatShortDate(orderanOnlineDateRange.end) }}</span></template>
                                            <template v-else>Semua Tanggal</template>
                                            <i v-if="orderanOnlineDateRange.start" @click.stop="orderanOnlineDateRange = { start: '', end: '' }" class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-danger"></i>
                                        </button>
                                    </div>
                                    <div class="toolbar-actions">
                                        <button @click="openOrderanOnlineModal('create')" class="primary-cta-button primary-cta-button--accent primary-cta-button--icon-only active:scale-95" aria-label="Tambah Order Online"><i class="fa-solid fa-plus"></i></button>
                                        <button @click="exportExcel" class="primary-cta-button primary-cta-button--success primary-cta-button--icon-only active:scale-95" aria-label="Export Excel"><i class="fa-solid fa-file-excel"></i></button>
                                        <button @click="exportPdf" class="primary-cta-button primary-cta-button--danger primary-cta-button--icon-only active:scale-95" aria-label="Export PDF"><i class="fa-solid fa-file-pdf"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div v-if="filteredOrderanOnlineData.length === 0"
                                    class="bg-white radius-card border border-slate-100 p-10 text-center text-body text-slate-400">
                                    Belum ada data order online
                                </div>
                                <div v-for="(row, idx) in pagedOrderanOnlineData" :key="'oo-mobile-' + (row.ID || idx)"
                                    class="stat-card mobile-record-card mobile-data-card motion-stagger-item"
                                    :style="getStaggerStyle(idx)">
                                    <div class="mobile-data-card__header">
                                        <span
                                            :class="['px-2.5 py-1 rounded-full text-overline font-bold uppercase', getStatusColor(row.STATUS)]">
                                            {{ row.STATUS || '-' }}
                                        </span>
                                        <span class="type-body-sm text-slate-400 font-bold uppercase">
                                            {{ row.TANGGAL ? formatShortDate(row.TANGGAL) : '-' }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="mobile-data-card__title line-clamp-2">{{ row.NAMA || row['TYPE UNIT'] || '-' }}</p>
                                        <div class="mobile-data-card__meta mt-2">
                                            <span
                                                class="px-2.5 py-1 rounded-lg bg-secondary text-light text-overline font-bold uppercase">
                                                {{ row.ECOMMERCE || '-' }}
                                            </span>
                                            <span
                                                class="px-2.5 py-1 rounded-lg bg-info text-light text-overline font-bold uppercase">
                                                {{ row.PENGIRIMAN || '-' }}
                                            </span>
                                        </div>
                                        <p class="type-body-sm text-slate-400 mt-2 line-clamp-1">
                                            {{ row['TYPE UNIT'] || row.TYPE_UNIT || '-' }}
                                        </p>
                                    </div>
                                    <div class="mobile-data-card__summary">
                                        <div>
                                            <div class="type-body-sm text-slate-400 uppercase">Harga</div>
                                            <div class="type-body font-bold text-slate-800">{{ formatNumber(row['HARGA ONLINE'] || row.HARGA_ONLINE || 0) }}</div>
                                        </div>
                                        <div>
                                            <div class="type-body-sm text-slate-400 uppercase">Cair</div>
                                            <div class="type-body font-bold text-success">{{ formatNumber(row['NOMINAL CAIR'] || row.NOMINAL_CAIR || 0) }}</div>
                                        </div>
                                    </div>
                                    <div class="mobile-data-card__actions">
                                        <div class="type-body-sm text-slate-400 line-clamp-1">
                                            {{ row['NO PESANAN'] || row.NO_PESANAN || row['NO RESI'] || row.NO_RESI || '-' }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button @click="openOrderanOnlineModal('edit', row)"
                                                class="table-action-button table-action-compact" title="Edit"
                                                aria-label="Edit"><i class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                                            <button @click="deleteOrderanOnline(row.ID)"
                                                class="table-action-button table-action-compact table-action-danger"
                                                title="Hapus" aria-label="Hapus"><i
                                                    class="fa-solid fa-trash-can text-body-sm"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-center gap-2 py-2">
                                <button @click="orderanPage--" :disabled="orderanPage <= 1"
                                    aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-left text-body-sm"></i></button>
                                <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ orderanPage }} / {{ orderanTotalPages }}</span>
                                <button @click="orderanPage++" :disabled="orderanPage >= orderanTotalPages"
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
                                        <input id="order-online-search-desktop" name="order_online_search_desktop" v-model="orderanOnlineSearch" type="text"
                                            autocomplete="off" aria-label="Cari order online desktop"
                                            placeholder="Cari nama / ecommerce / no pesanan..."
                                            class="form-input-search" />
                                    </div>
                                </div>
                                <div class="table-toolbar-shell__right">
                                    <div class="relative group search-select-container">
                                        <button @click="openCalendar($event, 'filter', '', 'orderanOnline')"
                                            class="select-trigger-button-compact">
                                            <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                            <template v-if="orderanOnlineDateRange.start">
                                                {{ formatShortDate(orderanOnlineDateRange.start) }}
                                                <span v-if="orderanOnlineDateRange.end"> - {{ formatShortDate(orderanOnlineDateRange.end) }}</span>
                                            </template>
                                            <template v-else>Semua Tanggal</template>
                                            <i v-if="orderanOnlineDateRange.start"
                                                @click.stop="orderanOnlineDateRange = { start: '', end: '' }"
                                                class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-danger"></i>
                                        </button>
                                    </div>
                                    <div class="toolbar-actions toolbar-actions--desktop-icon-only">
<button @click="openOrderanOnlineModal('create')"
                                             class="primary-cta-button primary-cta-button--accent primary-cta-button--icon-only active:scale-95"
                                             aria-label="Tambah Order Online">
                                             <i class="fa-solid fa-plus"></i>
                                         </button>
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
                                <table class="w-full min-w-[1960px] table-fixed text-body-sm text-left border-collapse">
                                    <thead>
                                        <tr class="table-header-row">
                                            <th class="table-header-cell table-header-index table-freeze-index">No</th>
                                            <th class="table-header-cell table-header-action table-freeze-action">Aksi</th>
                                            <th class="table-header-cell text-left w-[110px]">Tanggal</th>
                                            <th class="table-header-cell text-left w-[120px]">Ecommerce</th>
                                            <th class="table-header-cell text-left w-[110px]">Handle</th>
                                            <th class="table-header-cell text-left w-[15%]">Nama</th>
                                            <th class="table-header-cell text-left w-[120px]">No HP</th>
                                            <th class="table-header-cell text-left w-[120px]">Username</th>
                                            <th class="table-header-cell text-left w-[150px]">No Pesanan</th>
                                            <th class="table-header-cell text-left w-[120px]">Pengiriman</th>
                                            <th class="table-header-cell text-left w-[150px]">No Resi</th>
                                            <th class="table-header-cell text-left w-[14%]">Type Unit</th>
                                            <th class="table-header-cell text-left w-[140px]">IMEI/SN</th>
                                            <th class="table-header-cell text-right w-[120px]">Harga Online</th>
                                            <th class="table-header-cell text-right w-[120px]">Nominal Cair</th>
                                            <th class="table-header-cell text-right w-[90px]">Admin%</th>
                                            <th class="table-header-cell text-left w-[110px]">No Nota</th>
                                            <th class="table-header-cell text-center w-[110px]">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(row, idx) in pagedOrderanOnlineData" :key="row.ID || idx"
                                            class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                            <td class="px-4 py-3 type-body text-slate-500 table-freeze-index">{{ (orderanPage - 1) * 15 + idx + 1 }}</td>
                                            <td class="px-4 py-3 text-left table-freeze-action">
                                                <div class="flex items-center gap-1.5">
                                                    <button @click="openOrderanOnlineModal('edit', row)"
                                                        class="table-action-button table-action-compact" title="Edit"
                                                        aria-label="Edit"><i
                                                            class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                                                    <button @click="deleteOrderanOnline(row.ID)"
                                                        class="table-action-button table-action-compact table-action-danger"
                                                        title="Hapus" aria-label="Hapus"><i
                                                            class="fa-solid fa-trash-can text-body-sm"></i></button>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600 whitespace-nowrap">{{ row.TANGGAL || '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-left text-body font-semibold text-slate-800 break-words">{{ row.ECOMMERCE || '-' }}</td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600 break-words">{{ row.HANDLE || '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-left text-body text-slate-800 break-words">{{ row.NAMA || '-' }}</td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600">{{ row.HP || '-' }}</td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600 break-words">{{ row.USERNAME || '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600 break-words">{{ row['NO PESANAN'] || row.NO_PESANAN || '-' }}</td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600 break-words">{{ row.PENGIRIMAN || '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600 break-words">{{ row['NO RESI'] || row.NO_RESI || '-' }}</td>
                                            <td class="px-4 py-3 text-left text-body text-slate-800 font-semibold break-words">{{ row['TYPE UNIT'] || row.TYPE_UNIT || '-' }}</td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600 break-words">{{ row['IMEI/SN'] || row.IMEI_SN || '-' }}</td>
                                            <td class="px-4 py-3 text-body text-right font-semibold text-ppp-accent">
                                                {{ formatNumber(row['HARGA ONLINE'] || row.HARGA_ONLINE || 0) }}</td>
                                            <td class="px-4 py-3 text-body text-right font-semibold text-success">
                                                {{ formatNumber(row['NOMINAL CAIR'] || row.NOMINAL_CAIR || 0) }}</td>
                                            <td class="px-4 py-3 text-body text-right text-slate-600">{{ calcAdminPct(row) }}</td>
                                            <td class="px-4 py-3 text-left text-body text-slate-600">{{ row['NO NOTA'] || row.NO_NOTA || '-' }}</td>
                                            <td class="px-4 py-3 text-center"><span :class="getStatusColor(row.STATUS)"
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap">{{ row.STATUS || '-' }}</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div
                                class="table-pager-bar">
                                <div class="text-body-sm text-slate-400 font-medium">
                                    <template v-if="filteredOrderanOnlineData.length > 0">{{ (orderanPage - 1) * 15 + 1 }}-{{ Math.min(orderanPage * 15, filteredOrderanOnlineData.length) }} dari {{ filteredOrderanOnlineData.length }} data</template>
                                    <template v-else>0 data</template>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button @click="orderanPage--" :disabled="orderanPage <= 1"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-left text-body-sm"></i></button>
                                    <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ orderanPage }} / {{ orderanTotalPages }}</span>
                                    <button @click="orderanPage++" :disabled="orderanPage >= orderanTotalPages"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-right text-body-sm"></i></button>
                                </div>
                            </div>
                            <div v-if="orderanOnlineData.length === 0"
                                class="flex flex-col items-center justify-center py-20 text-slate-400">
                                <i class="fa-solid fa-cart-shopping text-4xl mb-4 opacity-20"></i>
                                <p class="text-body font-bold uppercase">Belum ada data order online
                                </p>
                            </div>
                        </div>
                    </div>

    <!-- Order Online Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="orderanOnlineModalOpen"
                class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                <div @click="orderanOnlineModalOpen = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop">
                </div>
                <div
                    class="mobile-sheet modal-width-detail radius-sheet modal-sheet-surface">
                    <div
                        class="modal-header-bar modal-header-bar-sticky radius-sheet-top z-[2010]">
                        <div class="modal-header-copy">
                            <div
                                class="modal-header-icon bg-info text-light border border-info">
                                <i class="fa-solid fa-cart-shopping"></i>
                            </div>
                            <div>
                                <div class="type-heading-sm text-slate-900">{{ csModalType === 'create' ? 'Tambah' : 'Edit' }} Order Online</div>
                                <div class="type-body-sm text-slate-400 uppercase mt-0.5">Customer
                                    Service</div>
                            </div>
                        </div>
                        <button @click="orderanOnlineModalOpen = false" aria-label="Tutup modal"
                            class="icon-utility-button icon-utility-round"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="p-6 overflow-y-auto space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Tanggal</label>
                                <button type="button" @click="openCalendar($event, 'form', '', 'orderanOnline1')"
                                    class="select-trigger-button-form toolbar-trigger-field-form">
                                    <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                    <span
                                        :class="orderanOnlineForm['TANGGAL'] ? 'text-slate-700 font-medium' : 'text-slate-400'">{{ orderanOnlineForm['TANGGAL'] ? formatFullDate(orderanOnlineForm['TANGGAL']) : 'Pilih Tanggal' }}</span>
                                </button>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Ecommerce</label>
                                <button type="button" @click="toggleSearchSelect($event, 'ecommerce')"
                                    :aria-expanded="searchSelectOpen === 'ecommerce' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="orderanOnlineForm['ECOMMERCE'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ orderanOnlineForm['ECOMMERCE'] || 'Pilih Ecommerce' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'ecommerce'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari ecommerce order online"
                                                placeholder="Cari ecommerce..." class="form-input-popover"
                                                @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in orderanEcommerceOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="orderanOnlineForm['ECOMMERCE'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', orderanOnlineForm['ECOMMERCE'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="orderanEcommerceOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Handle</label>
                                <button type="button" @click="toggleSearchSelect($event, 'orderan_handle')"
                                    :aria-expanded="searchSelectOpen === 'orderan_handle' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="orderanOnlineForm['HANDLE'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ orderanOnlineForm['HANDLE'] || 'Pilih Handle' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'orderan_handle'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari handle order online"
                                                placeholder="Cari handle..." class="form-input-popover"
                                                @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in orderanHandleOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="orderanOnlineForm['HANDLE'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', orderanOnlineForm['HANDLE'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="orderanHandleOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div>
                                <label for="order-online-nama" class="type-body-sm font-bold text-slate-400 uppercase mb-2">Nama
                                    Customer</label>
                                <input id="order-online-nama" name="order_online_nama" v-model="orderanOnlineForm['NAMA']" type="text" class="form-input" />
                            </div>
                            <div>
                                <label for="order-online-hp" class="type-body-sm font-bold text-slate-400 uppercase mb-2">No
                                    HP</label>
                                <input id="order-online-hp" name="order_online_hp" v-model="orderanOnlineForm['HP']" type="text" class="form-input" />
                            </div>
                            <div>
                                <label for="order-online-username"
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Username</label>
                                <input id="order-online-username" name="order_online_username" v-model="orderanOnlineForm['USERNAME']" type="text" class="form-input" />
                            </div>
                            <div>
                                <label for="order-online-no-pesanan" class="type-body-sm font-bold text-slate-400 uppercase mb-2">No
                                    Pesanan</label>
                                <input id="order-online-no-pesanan" name="order_online_no_pesanan" v-model="orderanOnlineForm['NO PESANAN']" type="text" class="form-input" />
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-2">Pengiriman</label>
                                <button type="button" @click="toggleSearchSelect($event, 'pengiriman')"
                                    :aria-expanded="searchSelectOpen === 'pengiriman' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="orderanOnlineForm['PENGIRIMAN'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ orderanOnlineForm['PENGIRIMAN'] || 'Pilih Pengiriman' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'pengiriman'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari pengiriman order online"
                                                placeholder="Cari pengiriman..." class="form-input-popover"
                                                @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in orderanPengirimanOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="orderanOnlineForm['PENGIRIMAN'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', orderanOnlineForm['PENGIRIMAN'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="orderanPengirimanOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div>
                                <label for="order-online-no-resi" class="type-body-sm font-bold text-slate-400 uppercase mb-2">No
                                    Resi</label>
                                <input id="order-online-no-resi" name="order_online_no_resi" v-model="orderanOnlineForm['NO RESI']" type="text" class="form-input" />
                            </div>
                            <div class="relative search-select-container">
                                <label class="type-body-sm font-bold text-slate-400 uppercase mb-2">Type
                                    Unit / Produk</label>
                                <button type="button" @click="toggleSearchSelect($event, 'orderan_type_unit')"
                                    :aria-expanded="searchSelectOpen === 'orderan_type_unit' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="orderanOnlineForm['TYPE UNIT'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ orderanOnlineForm['TYPE UNIT'] || 'Pilih Type Unit' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'orderan_type_unit'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                            <input v-model="searchSelectQuery" type="text" name="search_select_query"
                                                autocomplete="off" aria-label="Cari type unit order online"
                                                placeholder="Cari type unit..." class="form-input-popover"
                                                @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in sharedUnitTypeOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="orderanOnlineForm['TYPE UNIT'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', orderanOnlineForm['TYPE UNIT'] === opt ? 'popover-option-active' : '']">
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
                                <label for="order-online-imei-sn" class="type-body-sm font-bold text-slate-400 uppercase mb-2">IMEI
                                    / SN</label>
                                <input id="order-online-imei-sn" name="order_online_imei_sn" v-model="orderanOnlineForm['IMEI/SN']" type="text" class="form-input" />
                            </div>
                            <div>
                                <label for="order-online-no-nota" class="type-body-sm font-bold text-slate-400 uppercase mb-2">No
                                    Nota</label>
                                <input id="order-online-no-nota" name="order_online_no_nota" v-model="orderanOnlineForm['NO NOTA']" type="text" class="form-input" />
                            </div>
                            <div>
                                <label for="order-online-harga-online" class="type-body-sm font-bold text-slate-400 uppercase mb-2">Harga
                                    Online</label>
                                <input id="order-online-harga-online" name="order_online_harga_online" v-model.number="orderanOnlineForm['HARGA ONLINE']" type="number"
                                    class="form-input" />
                            </div>
                            <div>
                                <label for="order-online-nominal-cair" class="type-body-sm font-bold text-slate-400 uppercase mb-2">Nominal
                                    Cair</label>
                                <input id="order-online-nominal-cair" name="order_online_nominal_cair" v-model.number="orderanOnlineForm['NOMINAL CAIR']" type="number"
                                    class="form-input" />
                            </div>
                            <div>
                                <label for="order-online-admin-persentase" class="type-body-sm font-bold text-slate-400 uppercase mb-2">Admin
                                    %</label>
                                <input id="order-online-admin-persentase" name="order_online_admin_persentase" v-model="orderanOnlineForm['ADMIN %']" type="text" placeholder="2% / 3%"
                                    class="form-input" />
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-body-sm font-bold text-slate-400 uppercase mb-1.5">Status</label>
                                <button type="button" @click="toggleSearchSelect($event, 'orderan_status')"
                                    :aria-expanded="searchSelectOpen === 'orderan_status' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="orderanOnlineForm['STATUS'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ orderanOnlineForm['STATUS'] || 'Pilih Status' }}</span>
                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'orderan_status'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in orderanStatusOptions" :key="opt"
                                                @click="orderanOnlineForm['STATUS'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', orderanOnlineForm['STATUS'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer-bar modal-footer-actions">
                        <button @click="orderanOnlineModalOpen = false" class="primary-cta-button primary-cta-button--neutral">Batal</button>
                        <button @click="saveOrderanOnline" :disabled="submitting" class="primary-cta-button">{{ submitting ? 'Menyimpan...' : 'Simpan' }}</button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
@endverbatim
