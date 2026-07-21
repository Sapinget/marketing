@verbatim
            <!-- Ads Log tab -->
            <div v-if="activeTab === 'ads_log' && !tabDataLoaded['ads']"
                class="space-y-6 animate-fadeIn pb-10 animate-pulse">
                <div class="section-card section-card-shell">
                    <div class="px-6 py-4 border-b border-slate-50 flex gap-6">
                        <div class="h-3 bg-slate-200 rounded-full w-24"></div>
                        <div class="h-3 bg-slate-200 rounded-full w-20"></div>
                        <div class="h-3 bg-slate-200 rounded-full w-20"></div>
                        <div class="h-3 bg-slate-200 rounded-full w-24"></div>
                        <div class="h-3 bg-slate-200 rounded-full flex-1"></div>
                    </div>
                    <div class="divide-y divide-slate-50">
                        <div v-for="i in 8" :key="'sk-al'+i" class="px-6 py-5 flex items-center gap-4">
                            <div class="h-4 bg-slate-100 rounded-full w-40"></div>
                            <div class="h-4 bg-slate-100 rounded-full w-20"></div>
                            <div class="h-4 bg-slate-100 rounded-full w-20"></div>
                            <div class="h-4 bg-slate-100 rounded-full w-24"></div>
                            <div class="h-4 bg-slate-100 rounded-full flex-1"></div>
                            <div class="flex gap-1">
                                <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                                <div class="w-8 h-8 bg-slate-100 rounded-lg"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="activeTab === 'ads_log' && tabDataLoaded['ads']" class="space-y-6 animate-fadeIn pb-10">

                <!-- Summary Cards (di atas judul, konsisten) -->
                <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-rectangle-ad text-[120px]"></i></div>
                        <p class="dashboard-summary-title">Total Iklan</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ filteredAdsData.length }}</span>
                            <span class="dashboard-summary-unit">Iklan</span>
                        </div>
                        <p class="text-body-sm font-bold text-amber mt-3">Dalam periode aktif</p>
                    </div>
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-money-bill-wave text-[120px]"></i></div>
                        <p class="dashboard-summary-title">Total Spent</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ formatCurrency(filteredAdsData.reduce((s, r) => s + (Number(r.Biaya)||0), 0)) }}</span>
                        </div>
                        <p class="text-body-sm font-bold text-amber mt-3">Total biaya iklan</p>
                    </div>
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-bullhorn text-[120px]"></i></div>
                        <p class="dashboard-summary-title">Total Reach</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ filteredAdsData.reduce((s, r) => s + (Number(r.Jangkauan)||0), 0).toLocaleString('id') }}</span>
                        </div>
                        <p class="text-body-sm font-bold text-success mt-3">Jangkauan total</p>
                    </div>
                    <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-star text-[120px]"></i></div>
                        <p class="dashboard-summary-title">Avg Score</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ filteredAdsData.length ? Math.round(filteredAdsData.reduce((s, r) => s + (Number(r.Rata_Komentar)||0), 0) / filteredAdsData.length) : 0 }}</span>
                        </div>
                        <p class="text-body-sm font-bold text-slate-600 mt-3">Rata-rata komentar</p>
                    </div>
                </div>



                <div class="md:hidden space-y-3">
                    <div class="space-y-3">
                        <div v-if="pagedAdsData.length === 0"
                            class="bg-white radius-card border border-slate-100 p-10 text-center text-body text-slate-400">
                            Belum ada data iklan
                        </div>
                        <div v-for="(row, idx) in pagedAdsData" :key="'ads-mobile-' + (row.ID || idx)"
                            class="stat-card mobile-record-card mobile-data-card motion-stagger-item"
                            :style="getStaggerStyle(idx)">
                            <div class="mobile-data-card__header">
                                <span
                                    class="px-2.5 py-1 rounded-full text-overline font-bold uppercase tracking-wider bg-amber text-light">
                                    {{ row.Kategori || '-' }}
                                </span>
                                <span class="type-body-sm text-slate-400 font-bold uppercase tracking-widest">
                                    {{ row.Tanggal ? formatShortDate(row.Tanggal) : '-' }}
                                </span>
                            </div>
                            <div>
                                <p class="mobile-data-card__title line-clamp-2">{{ row.Nama || '-' }}</p>
                                <div class="mobile-data-card__meta mt-2">
                                    <div class="flex items-center gap-2">
                                        <i :class="getPlatformIcon(row.Platform || 'Ads') + ' text-body text-slate-400'"></i>
                                        <span class="text-body font-bold text-slate-700">{{ platformDisplayName(row.Platform || 'Ads') }}</span>
                                    </div>
                                    <span
                                        class="px-2.5 py-1 rounded-lg bg-amber text-light text-overline font-bold uppercase">
                                        {{ row.ID_Ads || 'Tanpa ID' }}
                                    </span>
                                </div>
                            </div>
                            <div class="mobile-data-card__summary">
                                <div>
                                    <div class="type-body-sm text-slate-400 uppercase">Spend</div>
                                    <div class="type-body font-bold text-amber">{{ formatCurrency(row.Biaya||0) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="type-body-sm text-slate-400 uppercase">Reach</div>
                                    <div class="type-body font-bold text-slate-700">{{ (Number(row.Jangkauan)||0).toLocaleString('id') }}</div>
                                </div>
                            </div>
                            <div class="mobile-data-card__actions">
                                <div class="type-body-sm font-bold"
                                    :class="(Number(row.Rata_Komentar)||0) >= 70 ? 'text-success' : (Number(row.Rata_Komentar)||0) >= 40 ? 'text-amber' : 'text-slate-400'">
                                    Score {{ Number(row.Rata_Komentar)||0 }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="openAdsModal('edit', row)"
                                        class="table-action-button table-action-compact" title="Edit"
                                        aria-label="Edit"><i class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                                    <button @click="deleteAdsRow(row.ID)"
                                        class="table-action-button table-action-compact table-action-danger"
                                        title="Hapus" aria-label="Hapus"><i
                                            class="fa-solid fa-trash-can text-body-sm"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center gap-2 py-2">
                        <button @click="adsPage = 1" :disabled="adsPage <= 1" aria-label="Halaman pertama"
                            class="icon-utility-button icon-utility-bordered"><i
                                class="fa-solid fa-angles-left text-body-sm"></i></button>
                        <button @click="adsPage--" :disabled="adsPage <= 1" aria-label="Halaman sebelumnya"
                            class="icon-utility-button icon-utility-bordered"><i
                                class="fa-solid fa-chevron-left text-body-sm"></i></button>
                        <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ adsPage }} / {{ adsTotalPages }}</span>
                        <button @click="adsPage++" :disabled="adsPage >= adsTotalPages" aria-label="Halaman berikutnya"
                            class="icon-utility-button icon-utility-bordered"><i
                                class="fa-solid fa-chevron-right text-body-sm"></i></button>
                        <button @click="adsPage = adsTotalPages" :disabled="adsPage >= adsTotalPages"
                            aria-label="Halaman terakhir" class="icon-utility-button icon-utility-bordered"><i
                                class="fa-solid fa-angles-right text-body-sm"></i></button>
                    </div>
                </div>
                <div class="hidden md:block section-card section-card-shell">
                    <div class="table-toolbar-shell">
                        <div class="table-toolbar-shell__left">
                            <div class="relative">
                                <i
                                    class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-body-sm"></i>
                                <input id="ads-log-search" name="ads_log_search" v-model="adsSearch" type="text" placeholder="Cari iklan..."
                                    class="form-input-search" />
                            </div>
                        </div>
                        <div class="table-toolbar-shell__right">
                            <div class="relative">
                                <button @click="openCalendar($event, 'filter', '', 'ads_log')"
                                    class="select-trigger-button-compact">
                                    <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                    <template v-if="adsDateFilter.start">{{ formatShortDate(adsDateFilter.start) }}
                                        - {{ adsDateFilter.end ? formatShortDate(adsDateFilter.end) : '...' }}</template>
                                    <template v-else>Semua Tanggal</template>
                                    <i v-if="adsDateFilter.start" @click.stop="adsDateFilter = {start:'',end:''}"
                                        class="fa-solid fa-xmark text-overline text-slate-400 hover:text-danger ml-1"></i>
                                </button>
                            </div>
                            <div class="toolbar-actions">
                                <button @click="openAdsModal('create')"
                                    class="primary-cta-button primary-cta-button--accent active:scale-95"><i
                                        class="fa-solid fa-plus text-overline"></i> Tambah</button>
                                <button @click="exportAdsLogToPDF"
                                    class="primary-cta-button primary-cta-button--danger active:scale-95"><i
                                        class="fa-solid fa-file-pdf text-[9px]"></i><span
                                        class="ml-1">PDF</span></button>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-body-sm text-left border-collapse min-w-[900px]">
                            <thead>
                                <tr class="table-header-row">
                                    <th class="table-header-cell table-header-index table-freeze-index">#</th>
                                    <th class="table-header-cell table-header-action table-freeze-action">Aksi</th>
                                    <th class="table-header-cell">Nama Iklan</th>
                                    <th class="table-header-cell text-center w-28">ID Ads</th>
                                    <th class="table-header-cell text-center w-24">Reach</th>
                                    <th class="table-header-cell text-center w-20">Score</th>
                                    <th class="table-header-cell text-center w-24">Tanggal</th>
                                    <th class="table-header-cell text-center w-28">Biaya</th>
                                    <th class="table-header-cell text-center w-28">Sisa Saldo</th>
                                    <th class="table-header-cell text-center w-36">Kategori</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="pagedAdsData.length === 0">
                                    <td colspan="10" class="px-4 py-12 text-center text-body text-slate-400">
                                        Belum ada data iklan</td>
                                </tr>
                                <tr v-for="(row, idx) in pagedAdsData" :key="row.ID"
                                    class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 text-center text-body text-slate-400 table-freeze-index">{{ (adsPage - 1) * 20 + idx + 1 }}</td>
                                    <td class="px-4 py-3 table-freeze-action">
                                        <div class="flex items-center gap-1.5">
                                            <button @click="openAdsModal('edit', row)"
                                                class="table-action-button table-action-compact" title="Edit"
                                                aria-label="Edit"><i class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                                            <button @click="deleteAdsRow(row.ID)"
                                                class="table-action-button table-action-compact table-action-danger"
                                                title="Hapus" aria-label="Hapus"><i
                                                    class="fa-solid fa-trash-can text-body-sm"></i></button>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-slate-800 uppercase text-body-sm">{{ row.Nama }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <p v-if="row.ID_Ads" class="text-body-sm text-slate-500 font-mono">{{ row.ID_Ads }}</p>
                                        <span v-else class="text-body-sm text-slate-300 italic">-</span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-body font-bold text-slate-600">{{ (Number(row.Jangkauan)||0).toLocaleString('id') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-heading-sm font-bold"
                                            :class="(Number(row.Rata_Komentar)||0) >= 70 ? 'text-success' : (Number(row.Rata_Komentar)||0) >= 40 ? 'text-amber' : 'text-slate-400'">{{ Number(row.Rata_Komentar)||0 }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-body text-slate-500">{{ formatShortDate(row.Tanggal) }}</td>
                                    <td class="px-4 py-3 text-center text-body font-bold text-amber">{{ formatCurrency(row.Biaya||0) }}</td>
                                    <td class="px-4 py-3 text-center text-body font-bold text-success">{{ formatCurrency(row.Sisa_Saldo||0) }}</td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <span v-if="row.Kategori"
                                            class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-amber text-light text-[9px] font-bold uppercase whitespace-nowrap">{{ row.Kategori }}</span>
                                        <span v-else class="text-body-sm text-slate-300 italic">-</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-pager-bar">
                        <span class="text-body-sm font-bold text-slate-400">{{ filteredAdsData.length }}
                            iklan</span>
                        <div class="flex items-center gap-1">
                            <button @click="adsPage = 1" :disabled="adsPage <= 1"
                                class="icon-utility-button icon-utility-bordered"><i
                                    class="fa-solid fa-angles-left text-body-sm"></i></button>
                            <button @click="adsPage--" :disabled="adsPage <= 1" aria-label="Halaman sebelumnya"
                                class="icon-utility-button icon-utility-bordered"><i
                                    class="fa-solid fa-chevron-left text-body-sm"></i></button>
                            <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ adsPage }} / {{ adsTotalPages }}</span>
                            <button @click="adsPage++" :disabled="adsPage >= adsTotalPages"
                                aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered"><i
                                    class="fa-solid fa-chevron-right text-body-sm"></i></button>
                            <button @click="adsPage = adsTotalPages" :disabled="adsPage >= adsTotalPages"
                                class="icon-utility-button icon-utility-bordered"><i
                                    class="fa-solid fa-angles-right text-body-sm"></i></button>
                        </div>
                    </div>
                </div>
            </div>
@endverbatim
