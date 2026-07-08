@verbatim
<!-- Keep Barang / Retur View -->
                    <div v-if="activeTab === 'keep_barang' && !keepBarangLoaded"
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
                            </div>
                        </div>
                        <div class="section-card section-card-shell">
                            <div class="px-6 py-4 border-b border-slate-50 flex gap-6">
                                <div class="h-3 bg-slate-200 rounded-full w-20"></div>
                                <div class="h-3 bg-slate-200 rounded-full w-28"></div>
                                <div class="h-3 bg-slate-200 rounded-full w-24"></div>
                                <div class="h-3 bg-slate-200 rounded-full flex-1"></div>
                            </div>
                            <div class="divide-y divide-slate-50">
                                <div v-for="i in 8" :key="'sk-kb'+i" class="px-6 py-5 flex items-center gap-4">
                                    <div class="h-4 bg-slate-100 rounded-full w-44"></div>
                                    <div class="h-4 bg-slate-100 rounded-full w-20"></div>
                                    <div class="h-6 bg-slate-100 rounded-full w-14"></div>
                                    <div class="h-4 bg-slate-100 rounded-full flex-1"></div>
                                    <div class="flex gap-1">
                                        <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                                        <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="activeTab === 'keep_barang' && keepBarangLoaded" class="space-y-6 animate-fadeIn pb-10">
                        <section class="section-card section-card-body">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 md:gap-5">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center border border-indigo-100">
                                        <i class="fa-solid fa-box-archive text-lg"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-semibold text-slate-900">Keep Barang</h2>
                                        <p class="type-body text-slate-500">Kelola data keep barang & retur customer
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-3 mt-4">
                                <div class="relative flex-1">
                                    <i
                                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                                    <input v-model="keepBarangSearch" type="text"
                                        placeholder="Cari nama / HP / tipe / IMEI..." class="form-input-search" />
                                </div>
                                <div class="relative search-select-container">
                                    <button type="button" @click="toggleSearchSelect($event, 'keep_status_filter')"
                                        class="select-trigger-button sm:w-40">
                                        <span class="truncate">{{ keepBarangStatusFilter || 'Semua Status' }}</span>
                                        <i class="fa-solid fa-chevron-down text-[9px] text-slate-400"></i>
                                    </button>
                                    <div v-if="searchSelectOpen === 'keep_status_filter'" :style="popoverStyle"
                                        class="search-select-popover search-select-popover--compact max-h-60 overflow-y-auto">
                                        <div @click="keepBarangStatusFilter = ''; searchSelectOpen = null"
                                            class="popover-option">
                                            Semua Status</div>
                                        <div v-for="s in keepBarangUniqueStatus" :key="s"
                                            @click="keepBarangStatusFilter = s; searchSelectOpen = null"
                                            class="popover-option">
                                            {{ s }}</div>
                                    </div>
                                </div>
                                <div class="relative search-select-container">
                                    <button type="button" @click="toggleSearchSelect($event, 'keep_handle_filter')"
                                        class="select-trigger-button sm:w-44">
                                        <span class="truncate">{{ keepBarangHandleByFilter || 'Semua Handle By' }}</span>
                                        <i class="fa-solid fa-chevron-down text-[9px] text-slate-400"></i>
                                    </button>
                                    <div v-if="searchSelectOpen === 'keep_handle_filter'" :style="popoverStyle"
                                        class="search-select-popover search-select-popover--compact max-h-60 overflow-y-auto">
                                        <div @click="keepBarangHandleByFilter = ''; searchSelectOpen = null"
                                            class="popover-option">
                                            Semua Handle By</div>
                                        <div v-for="h in keepBarangUniqueHandleBy" :key="h"
                                            @click="keepBarangHandleByFilter = h; searchSelectOpen = null"
                                            class="popover-option">
                                            {{ h }}</div>
                                    </div>
                                </div>
                                <button
                                    @click="keepBarangSearch='';keepBarangStatusFilter='';keepBarangHandleByFilter=''"
                                    class="reset-filter-button" title="Reset"><i
                                        class="fa-solid fa-rotate-left text-[10px]"></i><span>Reset</span></button>
                            </div>
                            <div class="toolbar-actions mt-4">
                                <button @click="openKeepBarangModal('create')"
                                    class="primary-cta-button primary-cta-button--accent active:scale-95"><i
                                        class="fa-solid fa-plus mr-1.5"></i>Tambah</button>
                                <button @click="exportKeepBarangToExcel"
                                    class="secondary-cta-button secondary-cta-success active:scale-95"><i
                                        class="fa-solid fa-file-excel"></i><span
                                        class="ml-1">Excel</span></button>
                                <button @click="exportKeepBarangToPDF"
                                    class="secondary-cta-button secondary-cta-danger active:scale-95"><i
                                        class="fa-solid fa-file-pdf"></i><span
                                        class="ml-1">PDF</span></button>
                                <button @click="loadKeepBarangData"
                                    class="secondary-cta-button secondary-cta-neutral active:scale-95"><i
                                        class="fa-solid fa-rotate text-[10px]"></i> Muat Ulang</button>
                            </div>
                            <div class="chips-grid mt-4 pt-4 border-t border-slate-100">
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">Total:
                                    {{ keepBarangSummary.total }}</span>
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">PENDING:
                                    {{ keepBarangSummary.pending }}</span>
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">DONE:
                                    {{ keepBarangSummary.done }}</span>
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">CANCEL:
                                    {{ keepBarangSummary.cancel }}</span>
                            </div>
                        </section>
                        <div class="md:hidden space-y-3">
                            <div class="space-y-3">
                                <div v-if="filteredKeepBarangData.length === 0"
                                    class="bg-white radius-card border border-slate-100 p-10 text-center text-[11px] text-slate-400">
                                    Tidak ada data keep barang
                                </div>
                                <div v-for="(row, idx) in pagedKeepBarangData" :key="'kb-mobile-' + (row.ID || idx)"
                                    class="stat-card mobile-record-card mobile-data-card motion-stagger-item"
                                    :style="getStaggerStyle(idx)">
                                    <div class="mobile-data-card__header">
                                        <span
                                            :class="['px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider', keepBarangStatusClass(row.STATUS)]">
                                            {{ row.STATUS || '-' }}
                                        </span>
                                        <span class="type-meta text-slate-400 font-bold uppercase tracking-widest">
                                            {{ row.SISA_HARI_PENGAMBILAN || '-' }} hari
                                        </span>
                                    </div>
                                    <div>
                                        <p class="mobile-data-card__title line-clamp-2">{{ row.NAMA || row.TYPE_HP || '-' }}</p>
                                        <div class="mobile-data-card__meta mt-2">
                                            <span
                                                class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-[9px] font-bold uppercase">
                                                {{ row.HANDLE_BY || '-' }}
                                            </span>
                                            <span
                                                class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 text-[9px] font-bold uppercase">
                                                {{ row.TYPE_HP || '-' }}
                                            </span>
                                        </div>
                                        <p class="type-meta text-slate-400 mt-2 line-clamp-1">
                                            {{ row.NOMOR_HP || '-' }}
                                        </p>
                                    </div>
                                    <div class="mobile-data-card__summary">
                                        <div>
                                            <div class="type-meta text-slate-400 uppercase">Tgl Keep</div>
                                            <div class="type-body font-bold text-slate-700">{{ row.TANGGAL_KEEP || '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="type-meta text-slate-400 uppercase">DP</div>
                                            <div class="type-body font-bold text-ppp-accent">{{ row.DP_UANG_MUKA ? formatCurrency(row.DP_UANG_MUKA) : '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="mobile-data-card__actions">
                                        <div class="type-meta text-slate-400 line-clamp-1">
                                            {{ row.HANDLE_BY || row.KASIR_BY || '-' }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a v-if="row.NOMOR_HP" :href="'https://wa.me/62' + formatWaNumber(row.NOMOR_HP)"
                                                target="_blank" rel="noopener noreferrer" class="secondary-cta-button secondary-cta-link">WA 1</a>
                                            <a v-if="row.NOMOR_HP_2" :href="'https://wa.me/62' + formatWaNumber(row.NOMOR_HP_2)"
                                                target="_blank" rel="noopener noreferrer" class="secondary-cta-button secondary-cta-success">WA 2</a>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end gap-2">
                                        <div class="flex items-center gap-2">
                                            <button @click="openKeepBarangModal('edit', row)"
                                                class="table-action-button table-action-compact" title="Edit"
                                                aria-label="Edit"><i class="fa-solid fa-pen text-[10px]"></i></button>
                                            <button @click="deleteKeepBarang(row.ID)"
                                                class="table-action-button table-action-compact table-action-danger"
                                                title="Hapus" aria-label="Hapus"><i
                                                    class="fa-solid fa-trash text-[10px]"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-center gap-2 py-2">
                                <button @click="keepBarangPage--" :disabled="keepBarangPage <= 1"
                                    aria-label="Halaman sebelumnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-left text-[10px]"></i></button>
                                <span class="px-3 text-[10px] font-bold text-ppp-accent">{{ keepBarangPage }} / {{ keepBarangTotalPages }}</span>
                                <button @click="keepBarangPage++" :disabled="keepBarangPage >= keepBarangTotalPages"
                                    aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-right text-[10px]"></i></button>
                            </div>
                        </div>
                        <div class="hidden md:block section-card section-card-shell">
                            <div class="overflow-x-auto">
                                <table class="w-full text-[10.5px] text-left border-collapse min-w-[1100px]">
                                    <thead>
                                        <tr
                                            class="border-b border-slate-100 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                                            <th class="px-4 py-3">Aksi</th>
                                            <th class="px-4 py-3">Tgl Keep</th>
                                            <th class="px-4 py-3">Nama</th>
                                            <th class="px-4 py-3">No HP</th>
                                            <th class="px-4 py-3">Type HP</th>
                                            <th class="px-4 py-3">IMEI</th>
                                            <th class="px-4 py-3">DP</th>
                                            <th class="px-4 py-3">Harga Jual</th>
                                            <th class="px-4 py-3">Rencana Ambil</th>
                                            <th class="px-4 py-3">Sisa Hari</th>
                                            <th class="px-4 py-3">Tgl Expired</th>
                                            <th class="px-4 py-3">Handle By</th>
                                            <th class="px-4 py-3">Status</th>
                                            <th class="px-4 py-3">Follow Up</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="filteredKeepBarangData.length === 0">
                                            <td colspan="14" class="px-4 py-12 text-center text-[11px] text-slate-400">
                                                <i
                                                    class="fa-solid fa-box-archive text-2xl mb-3 block opacity-20"></i>Tidak
                                                ada data
                                            </td>
                                        </tr>
                                        <tr v-for="(row, idx) in pagedKeepBarangData" :key="row.ID || idx"
                                            class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-1.5">
                                                    <button @click="openKeepBarangModal('edit', row)"
                                                        class="table-action-button table-action-compact" title="Edit"
                                                        aria-label="Edit"><i
                                                            class="fa-solid fa-pen text-[10px]"></i></button>
                                                    <button @click="deleteKeepBarang(row.ID)"
                                                        class="table-action-button table-action-compact table-action-danger"
                                                        title="Hapus" aria-label="Hapus"><i
                                                            class="fa-solid fa-trash text-[10px]"></i></button>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 type-body text-slate-500">{{ row.TANGGAL_KEEP || '-' }}</td>
                                            <td class="px-4 py-3 text-[11px] font-semibold text-slate-800">{{ row.NAMA || '-' }}</td>
                                            <td class="px-4 py-3 text-[11px] text-slate-600">{{ row.NOMOR_HP || '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-[11px] text-slate-700 font-semibold">{{ row.TYPE_HP || '-' }}</td>
                                            <td class="px-4 py-3 text-[11px] text-slate-600">{{ row.IMEI_FULL || '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-[11px] text-slate-600">{{ row.DP_UANG_MUKA ? formatCurrency(row.DP_UANG_MUKA) : '-' }}</td>
                                            <td class="px-4 py-3 text-[11px] text-slate-600">{{ row.HARGA_JUAL ? formatCurrency(row.HARGA_JUAL) : '-' }}</td>
                                            <td class="px-4 py-3 type-body text-slate-500">{{ row.RENCANA_PENGAMBILAN || '-' }}</td>
                                            <td class="px-4 py-3 text-[11px]">
                                                <span :class="keepBarangSisaHariClass(row.SISA_HARI_PENGAMBILAN)">{{ row.SISA_HARI_PENGAMBILAN || '-' }}</span>
                                            </td>
                                            <td class="px-4 py-3 type-body text-slate-500">{{ row.TANGGAL_EXPIRED || '-' }}</td>
                                            <td class="px-4 py-3 text-[11px] text-slate-600">{{ row.HANDLE_BY || '-' }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <span :class="keepBarangStatusClass(row.STATUS)"
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap">{{ row.STATUS || '-' }}</span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex gap-1">
                                                    <a v-if="row.NOMOR_HP"
                                                        :href="'https://wa.me/62' + formatWaNumber(row.NOMOR_HP)"
                                                        target="_blank" rel="noopener noreferrer"
                                                        class="px-2 py-1 rounded-lg text-[10px] font-bold bg-emerald-500 text-white hover:bg-emerald-600 transition-all">WA
                                                        1</a>
                                                    <a v-if="row.NOMOR_HP_2"
                                                        :href="'https://wa.me/62' + formatWaNumber(row.NOMOR_HP_2)"
                                                        target="_blank" rel="noopener noreferrer"
                                                        class="px-2 py-1 rounded-lg text-[10px] font-bold bg-sky-500 text-white hover:bg-sky-600 transition-all">WA
                                                        2</a>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div
                                class="table-pager-bar">
                                <div class="text-[10px] text-slate-400 font-medium">
                                    <template v-if="filteredKeepBarangData.length > 0">{{ (keepBarangPage - 1) * 15 + 1 }}-{{ Math.min(keepBarangPage * 15, filteredKeepBarangData.length) }} dari {{ filteredKeepBarangData.length }} data</template>
                                    <template v-else>0 data</template>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button @click="keepBarangPage--" :disabled="keepBarangPage <= 1"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-left text-[10px]"></i></button>
                                    <span class="px-3 text-[10px] font-bold text-ppp-accent">{{ keepBarangPage }} / {{ keepBarangTotalPages }}</span>
                                    <button @click="keepBarangPage++" :disabled="keepBarangPage >= keepBarangTotalPages"
                                        class="icon-utility-button icon-utility-bordered"><i
                                            class="fa-solid fa-chevron-right text-[10px]"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
@endverbatim
