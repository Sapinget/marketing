@verbatim
<!-- Order Online View -->
                    <div v-if="activeTab === 'orderan_online' && !tabDataLoaded['orderanOnline']"
                        class="space-y-6 animate-fadeIn pb-10 animate-pulse">
                        <div class="section-card section-card-body">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-200"></div>
                                    <div class="space-y-2">
                                        <div class="h-5 bg-slate-200 rounded-full w-40"></div>
                                        <div class="h-3 bg-slate-100 rounded-full w-56"></div>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <div class="h-9 w-24 bg-slate-100 rounded-xl"></div>
                                    <div class="h-9 w-28 bg-slate-200 rounded-xl"></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="h-9 flex-1 bg-slate-100 rounded-xl"></div>
                                <div class="h-9 w-28 bg-slate-100 rounded-xl"></div>
                                <div class="h-9 w-28 bg-slate-100 rounded-xl"></div>
                            </div>
                        </div>
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
                        class="space-y-6 animate-fadeIn pb-10">
                        <!-- Summary cards -->
                        <div class="space-y-3">
                            <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                                <div v-for="c in orderanSummary.cards" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
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
                                        <i class="fa-solid fa-cart-shopping text-lg"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-semibold text-slate-900">Order Online</h2>
                                        <p class="type-body text-slate-500">Monitoring order marketplace dan pencairan
                                        </p>
                                    </div>
                                </div>
                                <div class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-3">
                                    <div class="relative flex-1 sm:w-56">
                                        <i
                                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                                        <input id="order-online-search" name="order_online_search" v-model="orderanOnlineSearch" type="text"
                                            autocomplete="off" aria-label="Cari order online"
                                            placeholder="Cari nama / ecommerce / no pesanan..."
                                            class="form-input-search" />
                                    </div>
                                    <div class="relative group search-select-container">
                                        <button @click="openCalendar($event, 'filter', '', 'orderanOnline')"
                                            class="date-trigger-button date-trigger-button-compact">
                                            <i class="fa-solid fa-calendar-days text-[10px] text-slate-400"></i>
                                            <template v-if="orderanOnlineDateRange.start">
                                                {{ formatShortDate(orderanOnlineDateRange.start) }}
                                                <span v-if="orderanOnlineDateRange.end"> - {{ formatShortDate(orderanOnlineDateRange.end) }}</span>
                                            </template>
                                            <template v-else>Semua Tanggal</template>
                                            <i v-if="orderanOnlineDateRange.start"
                                                @click.stop="orderanOnlineDateRange = { start: '', end: '' }"
                                                class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-red-500"></i>
                                        </button>
                                    </div>
                                    <div class="toolbar-actions">
                                        <button
                                            @click="orderanOnlineSearch='';orderanOnlineDateRange = getDefaultDateRange()"
                                            class="reset-filter-button" title="Reset">
                                            <i class="fa-solid fa-rotate-left text-[10px]"></i><span>Reset</span>
                                        </button>
                                        <button @click="openOrderanOnlineModal('create')"
                                            class="primary-cta-button primary-cta-button--accent active:scale-95">
                                            <i class="fa-solid fa-plus mr-2"></i>Tambah
                                        </button>
                                        <button @click="exportExcel"
                                            class="secondary-cta-button secondary-cta-success active:scale-95"><i
                                                class="fa-solid fa-file-excel"></i><span
                                                class="ml-1">Excel</span></button>
                                        <button @click="exportPdf"
                                            class="secondary-cta-button secondary-cta-danger active:scale-95"><i
                                                class="fa-solid fa-file-pdf"></i><span
                                                class="ml-1">PDF</span></button>
                                    </div>
                                </div>
                            </div>
                            <div v-if="orderanSummary.chips.length" class="chips-grid mt-4 pt-4 border-t border-slate-100">
                                <span v-for="ch in orderanSummary.chips" :key="ch.label" :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold', ch.cls]">{{ ch.label }} <span class="opacity-50">&middot;</span> {{ ch.n }}</span>
                            </div>
                        </section>
                        <div class="md:hidden space-y-3">
                            <div class="space-y-3">
                                <div v-if="filteredOrderanOnlineData.length === 0"
                                    class="bg-white radius-card border border-slate-100 p-10 text-center text-[11px] text-slate-400">
                                    Belum ada data order online
                                </div>
                                <div v-for="(row, idx) in pagedOrderanOnlineData" :key="'oo-mobile-' + (row.ID || idx)"
                                    class="stat-card mobile-record-card mobile-data-card motion-stagger-item"
                                    :style="getStaggerStyle(idx)">
                                    <div class="mobile-data-card__header">
                                        <span
                                            :class="['px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider', getStatusColor(row.STATUS)]">
                                            {{ row.STATUS || '-' }}
                                        </span>
                                        <span class="type-meta text-slate-400 font-bold uppercase tracking-widest">
                                            {{ row.TANGGAL ? formatShortDate(row.TANGGAL) : '-' }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="mobile-data-card__title line-clamp-2">{{ row.NAMA || row['TYPE UNIT'] || '-' }}</p>
                                        <div class="mobile-data-card__meta mt-2">
                                            <span
                                                class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-[9px] font-bold uppercase">
                                                {{ row.ECOMMERCE || '-' }}
                                            </span>
                                            <span
                                                class="px-2.5 py-1 rounded-lg bg-cyan-50 text-cyan-700 text-[9px] font-bold uppercase">
                                                {{ row.PENGIRIMAN || '-' }}
                                            </span>
                                        </div>
                                        <p class="type-meta text-slate-400 mt-2 line-clamp-1">
                                            {{ row['TYPE UNIT'] || row.TYPE_UNIT || '-' }}
                                        </p>
                                    </div>
                                    <div class="mobile-data-card__summary">
                                        <div>
                                            <div class="type-meta text-slate-400 uppercase">Harga</div>
                                            <div class="type-body font-bold text-slate-800">{{ formatNumber(row['HARGA ONLINE'] || row.HARGA_ONLINE || 0) }}</div>
                                        </div>
                                        <div>
                                            <div class="type-meta text-slate-400 uppercase">Cair</div>
                                            <div class="type-body font-bold text-emerald-600">{{ formatNumber(row['NOMINAL CAIR'] || row.NOMINAL_CAIR || 0) }}</div>
                                        </div>
                                    </div>
                                    <div class="mobile-data-card__actions">
                                        <div class="type-meta text-slate-400 line-clamp-1">
                                            {{ row['NO PESANAN'] || row.NO_PESANAN || row['NO RESI'] || row.NO_RESI || '-' }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button @click="openOrderanOnlineModal('edit', row)"
                                                class="table-action-button table-action-compact" title="Edit"
                                                aria-label="Edit"><i class="fa-solid fa-pen text-[10px]"></i></button>
                                            <button @click="deleteOrderanOnline(row.ID)"
                                                class="table-action-button table-action-compact table-action-danger"
                                                title="Hapus" aria-label="Hapus"><i
                                                    class="fa-solid fa-trash text-[10px]"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-center gap-2 py-2">
                                <button @click="orderanPage--" :disabled="orderanPage <= 1"
                                    aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-left text-[10px]"></i></button>
                                <span class="px-3 text-[10px] font-bold text-ppp-accent">{{ orderanPage }} / {{ orderanTotalPages }}</span>
                                <button @click="orderanPage++" :disabled="orderanPage >= orderanTotalPages"
                                    aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-right text-[10px]"></i></button>
                            </div>
                        </div>
                        <div class="hidden md:block section-card section-card-shell">
                            <div class="overflow-x-auto">
                                <table class="w-full text-[10.5px] text-left border-collapse min-w-[1400px]">
                                    <thead>
                                        <tr
                                            class="border-b border-slate-100 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                                            <th class="px-4 py-3">Aksi</th>
                                            <th class="px-4 py-3">No</th>
                                            <th class="px-4 py-3">Tanggal</th>
                                            <th class="px-4 py-3">Ecommerce</th>
                                            <th class="px-4 py-3">Handle</th>
                                            <th class="px-4 py-3">Nama</th>
                                            <th class="px-4 py-3">No HP</th>
                                            <th class="px-4 py-3">Username</th>
                                            <th class="px-4 py-3">No Pesanan</th>
                                            <th class="px-4 py-3">Pengiriman</th>
                                            <th class="px-4 py-3">No Resi</th>
                                            <th class="px-4 py-3">Type Unit</th>
                                            <th class="px-4 py-3">IMEI/SN</th>
                                            <th class="px-4 py-3 text-right">Harga Online</th>
                                            <th class="px-4 py-3 text-right">Nominal Cair</th>
                                            <th class="px-4 py-3 text-right">Admin%</th>
                                            <th class="px-4 py-3">No Nota</th>
                                            <th class="px-4 py-3 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(row, idx) in pagedOrderanOnlineData" :key="row.ID || idx"
                                            class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-1.5">
                                                    <button @click="openOrderanOnlineModal('edit', row)"
                                                        class="table-action-button table-action-compact" title="Edit"
                                                        aria-label="Edit"><i
                                                            class="fa-solid fa-pen text-[10px]"></i></button>
                                                    <button @click="deleteOrderanOnline(row.ID)"
                                                        class="table-action-button table-action-compact table-action-danger"
                                                        title="Hapus" aria-label="Hapus"><i
                                                            class="fa-solid fa-trash text-[10px]"></i></button>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 type-body text-slate-500">{{ (orderanPage - 1) * 15 + idx + 1 }}</td>
                                            <td class="px-4 py-3 text-[11px] text-slate-600">{{ row.TANGGAL || '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-[11px] font-semibold text-slate-800">{{ row.ECOMMERCE || '-' }}</td>
                                            <td class="px-4 py-3 text-[11px] text-slate-600">{{ row.HANDLE || '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-[11px] text-slate-800">{{ row.NAMA || '-' }}</td>
                                            <td class="px-4 py-3 text-[11px] text-slate-600">{{ row.HP || '-' }}</td>
                                            <td class="px-4 py-3 text-[11px] text-slate-600">{{ row.USERNAME || '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-[11px] text-slate-600">{{ row['NO PESANAN'] || row.NO_PESANAN || '-' }}</td>
                                            <td class="px-4 py-3 text-[11px] text-slate-600">{{ row.PENGIRIMAN || '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-[11px] text-slate-600">{{ row['NO RESI'] || row.NO_RESI || '-' }}</td>
                                            <td class="px-4 py-3 text-[11px] text-slate-800 font-semibold">{{ row['TYPE UNIT'] || row.TYPE_UNIT || '-' }}</td>
                                            <td class="px-4 py-3 text-[11px] text-slate-600">{{ row['IMEI/SN'] || row.IMEI_SN || '-' }}</td>
                                            <td class="px-4 py-3 text-[11px] text-right font-semibold text-ppp-accent">
                                                {{ formatNumber(row['HARGA ONLINE'] || row.HARGA_ONLINE || 0) }}</td>
                                            <td class="px-4 py-3 text-[11px] text-right font-semibold text-emerald-600">
                                                {{ formatNumber(row['NOMINAL CAIR'] || row.NOMINAL_CAIR || 0) }}</td>
                                            <td class="px-4 py-3 text-[11px] text-right text-slate-600">{{ calcAdminPct(row) }}</td>
                                            <td class="px-4 py-3 text-[11px] text-slate-600">{{ row['NO NOTA'] || row.NO_NOTA || '-' }}</td>
                                            <td class="px-4 py-3 text-center"><span :class="getStatusColor(row.STATUS)"
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap">{{ row.STATUS || '-' }}</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div
                                class="table-pager-bar">
                                <div class="text-[10px] text-slate-400 font-medium">
                                    <template v-if="filteredOrderanOnlineData.length > 0">{{ (orderanPage - 1) * 15 + 1 }}-{{ Math.min(orderanPage * 15, filteredOrderanOnlineData.length) }} dari {{ filteredOrderanOnlineData.length }} data</template>
                                    <template v-else>0 data</template>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button @click="orderanPage--" :disabled="orderanPage <= 1"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-left text-[10px]"></i></button>
                                    <span class="px-3 text-[10px] font-bold text-ppp-accent">{{ orderanPage }} / {{ orderanTotalPages }}</span>
                                    <button @click="orderanPage++" :disabled="orderanPage >= orderanTotalPages"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-right text-[10px]"></i></button>
                                </div>
                            </div>
                            <div v-if="orderanOnlineData.length === 0"
                                class="flex flex-col items-center justify-center py-20 text-slate-400">
                                <i class="fa-solid fa-cart-shopping text-4xl mb-4 opacity-20"></i>
                                <p class="text-[11px] font-bold uppercase tracking-widest">Belum ada data order online
                                </p>
                            </div>
                        </div>
                    </div>
@endverbatim
