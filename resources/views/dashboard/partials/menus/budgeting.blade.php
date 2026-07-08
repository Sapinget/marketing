@verbatim
<!-- Budgeting tab -->
            <div v-if="activeTab === 'budgeting' && !budgetConfigLoaded"
                class="space-y-6 animate-fadeIn pb-10 animate-pulse">
                <div class="section-card section-card-body">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-slate-200"></div>
                        <div class="space-y-2">
                            <div class="h-5 bg-slate-200 rounded-full w-40"></div>
                            <div class="h-3 bg-slate-100 rounded-full w-56"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div v-for="i in 4" :key="'sk-bg-st'+i"
                            class="bg-slate-50 radius-card p-4 border border-slate-100">
                            <div class="h-3 bg-slate-200 rounded-full w-20 mb-2"></div>
                            <div class="h-6 bg-slate-200 rounded-full w-28"></div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="i in 4" :key="'sk-bg-c'+i" class="bg-white radius-panel border border-slate-100 p-5">
                        <div class="h-4 bg-slate-200 rounded-full w-32 mb-4"></div>
                        <div class="space-y-3">
                            <div v-for="j in 3" :key="j" class="h-10 bg-slate-100 rounded-xl"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="activeTab === 'budgeting' && budgetConfigLoaded" class="space-y-6 animate-fadeIn pb-10">
                <section class="section-card section-card-body">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 md:gap-5">
                        <div class="modal-header-copy">
                            <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center">
                                <i class="fa-solid fa-wallet text-[16px]"></i>
                            </div>
                            <div>
                                <h2 class="type-title font-bold text-slate-900">Rancangan Anggaran</h2>
                                <p class="text-[10px] text-slate-400 uppercase tracking-widest mt-0.5">Estimasi
                                    Kebutuhan Topup Per Platform</p>
                            </div>
                        </div>
                        <div class="toolbar-actions">
                            <button @click="exportBudgetToExcel"
                                class="secondary-cta-button secondary-cta-success active:scale-95"><i
                                    class="fa-solid fa-file-excel text-[9px]"></i> Excel</button>
                            <button @click="exportBudgetToPDF"
                                class="secondary-cta-button secondary-cta-danger active:scale-95"><i
                                    class="fa-solid fa-file-pdf text-[9px]"></i> PDF</button>
                            <button @click="showBudgetSettings = !showBudgetSettings"
                                class="secondary-cta-button secondary-cta-neutral active:scale-95"><i
                                    class="fa-solid fa-sliders text-[9px]"></i> Atur</button>
                        </div>
                    </div>
                </section>

                <!-- Date filter -->
                <div class="bg-white radius-panel border border-slate-100 p-4">
                    <div class="flex flex-col sm:flex-row sm:items-end gap-3">
                        <div class="flex-1 w-full">
                            <label class="type-meta font-bold text-slate-400 uppercase mb-1">Tanggal
                                Awal</label>
                            <button type="button" @click="openCalendar($event, 'filter', '', 'budgeting')"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-[11px] text-left outline-none hover:border-ppp-accent transition-all flex items-center gap-2">
                                <i class="fa-solid fa-calendar-days text-[10px] text-slate-400"></i>
                                <span :class="budgetDateFilter.start ? 'text-slate-700 font-medium' : 'text-slate-400'">
                                    {{ budgetDateFilter.start ? formatShortDate(budgetDateFilter.start) : 'Pilih tanggal awal' }}
                                </span>
                            </button>
                        </div>
                        <div class="flex-1 w-full">
                            <label class="type-meta font-bold text-slate-400 uppercase mb-1">Tanggal
                                Akhir</label>
                            <button type="button" @click="openCalendar($event, 'filter', '', 'budgeting')"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-[11px] text-left outline-none hover:border-ppp-accent transition-all flex items-center gap-2">
                                <i class="fa-solid fa-calendar-days text-[10px] text-slate-400"></i>
                                <span :class="budgetDateFilter.end ? 'text-slate-700 font-medium' : 'text-slate-400'">
                                    {{ budgetDateFilter.end ? formatShortDate(budgetDateFilter.end) : 'Pilih tanggal akhir' }}
                                </span>
                            </button>
                        </div>
                        <button @click="budgetDateFilter = { start: '', end: '' }"
                            class="reset-filter-button" title="Reset"><i
                                class="fa-solid fa-rotate-left text-[10px]"></i><span>Reset</span></button>
                    </div>
                </div>

                <!-- Total topup banner -->
                <div
                    class="bg-ppp-accent text-white font-bold text-[14px] px-5 py-3.5 rounded-2xl flex justify-between items-center">
                    <span>Total Rencana Topup</span>
                    <span>{{ formatCurrency(budgetCalculations.totalTopup) }}</span>
                </div>

                <!-- Settings panel -->
                <div v-if="showBudgetSettings" class="bg-white radius-panel border border-slate-100 p-5 space-y-6">
                    <!-- Meta -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 pb-4 border-b border-slate-100">
                        <div class="col-span-full text-[10px] font-bold text-slate-500 uppercase mb-1">
                            Konfigurasi Meta</div>
                        <div><label class="block text-[10px] text-slate-400 mb-1">Biaya / Iklan</label><input
                                type="number" v-model.number="budgetConfig.meta.costPerAd"
                                class="w-full text-[11px] font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div><label class="block text-[10px] text-slate-400 mb-1">Total Iklan</label><input
                                type="number" v-model.number="budgetConfig.meta.totalAds"
                                class="w-full text-[11px] font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div><label class="block text-[10px] text-slate-400 mb-1">Durasi (Hari)</label><input
                                type="number" v-model.number="budgetConfig.meta.days"
                                class="w-full text-[11px] font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div><label class="block text-[10px] text-slate-400 mb-1">Sisa Saldo</label><input type="number"
                                v-model.number="budgetConfig.meta.balance"
                                class="w-full text-[11px] font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                    </div>
                    <!-- Google -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 pb-4 border-b border-slate-100">
                        <div class="col-span-full text-[10px] font-bold text-slate-500 uppercase mb-1">
                            Konfigurasi Google</div>
                        <div><label class="block text-[10px] text-slate-400 mb-1">Biaya / Ads</label><input
                                type="number" v-model.number="budgetConfig.google.costPerAd"
                                class="w-full text-[11px] font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div><label class="block text-[10px] text-slate-400 mb-1">Total Ads</label><input type="number"
                                v-model.number="budgetConfig.google.totalAds"
                                class="w-full text-[11px] font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div><label class="block text-[10px] text-slate-400 mb-1">Durasi (Hari)</label><input
                                type="number" v-model.number="budgetConfig.google.days"
                                class="w-full text-[11px] font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div><label class="block text-[10px] text-slate-400 mb-1">Sisa Saldo</label><input type="number"
                                v-model.number="budgetConfig.google.balance"
                                class="w-full text-[11px] font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                    </div>
                    <!-- Mekari -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 pb-4 border-b border-slate-100">
                        <div class="col-span-full text-[10px] font-bold text-slate-500 uppercase mb-1">
                            Konfigurasi Mekari</div>
                        <div><label class="block text-[10px] text-slate-400 mb-1">Visitor Target /
                                Hari</label><input type="number"
                                v-model.number="budgetConfig.mekari.visitor.targetPerDay"
                                class="w-full text-[11px] font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div><label class="block text-[10px] text-slate-400 mb-1">Durasi Visitor
                                (Hari)</label><input type="number" v-model.number="budgetConfig.mekari.visitor.days"
                                class="w-full text-[11px] font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div><label class="block text-[10px] text-slate-400 mb-1">Saldo Visitor
                                (Unit)</label><input type="number" v-model.number="budgetConfig.mekari.visitor.balance"
                                class="w-full text-[11px] font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div><label class="block text-[10px] text-slate-400 mb-1">Biaya Topup Visitor
                                (Manual)</label><input type="number"
                                v-model.number="budgetConfig.mekari.visitor.topupCost"
                                class="w-full text-[11px] font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div class="col-span-full border-t border-dashed border-slate-200 my-1"></div>
                        <div><label class="block text-[10px] text-slate-400 mb-1">Biaya Broadcast /
                                Minggu</label><input type="number"
                                v-model.number="budgetConfig.mekari.broadcast.costPerWeek"
                                class="w-full text-[11px] font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div><label class="block text-[10px] text-slate-400 mb-1">Durasi (Minggu)</label><input
                                type="number" v-model.number="budgetConfig.mekari.broadcast.weeks"
                                class="w-full text-[11px] font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div><label class="block text-[10px] text-slate-400 mb-1">Special Price
                                Addon</label><input type="number"
                                v-model.number="budgetConfig.mekari.broadcast.specialPrice"
                                class="w-full text-[11px] font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div><label class="block text-[10px] text-slate-400 mb-1">Saldo Broadcast
                                (Rp)</label><input type="number" v-model.number="budgetConfig.mekari.broadcast.balance"
                                class="w-full text-[11px] font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                    </div>
                    <!-- Colab Partners -->
                    <div class="pb-4 border-b border-slate-100">
                        <div class="flex justify-between items-center mb-3">
                            <div class="text-[10px] font-bold text-slate-500 uppercase">Partner Colab</div>
                            <button type="button"
                                @click="budgetConfig.colabPartners.push({name: '', packageCost: 0, slots: 0})"
                                class="text-[10px] bg-slate-100 px-2 py-1 rounded-lg hover:bg-slate-200 transition text-slate-600"><i
                                    class="fa-solid fa-plus mr-1"></i>Tambah</button>
                        </div>
                        <div v-for="(partner, idx) in budgetConfig.colabPartners" :key="'cp'+idx"
                            class="grid grid-cols-1 md:grid-cols-4 gap-2 mb-2 bg-slate-50 p-3 rounded-xl border border-slate-100 relative">
                            <div class="md:col-span-2"><label class="block text-[9px] text-slate-400 mb-1">Nama
                                    Partner</label>
                                <div class="relative search-select-container">
                                    <button type="button" @click="toggleSearchSelect($event, 'budget_partner_'+idx)"
                                        class="w-full text-[11px] font-bold bg-white border border-slate-200 rounded-lg p-2 outline-none hover:border-ppp-accent transition-all flex items-center justify-between gap-2">
                                        <span class="truncate"
                                            :class="partner.name ? 'text-slate-700' : 'text-slate-400'">{{ partner.name || 'Pilih Partner' }}</span>
                                        <i class="fa-solid fa-chevron-down text-[9px] text-slate-400"></i>
                                    </button>
                                    <div v-if="searchSelectOpen === 'budget_partner_'+idx" :style="popoverStyle"
                                        class="search-select-popover search-select-popover--compact max-h-60 overflow-y-auto">
                                        <div v-for="opt in (settings.Colab || [])" :key="opt"
                                            @click="partner.name = opt; searchSelectOpen = null" class="popover-option">
                                            {{ opt }}</div>
                                    </div>
                                </div>
                            </div>
                            <div><label class="block text-[9px] text-slate-400 mb-1">Biaya Paket</label><input
                                    type="number" v-model.number="partner.packageCost"
                                    class="w-full text-[11px] bg-white border border-slate-200 rounded-lg p-2 outline-none" />
                            </div>
                            <div><label class="block text-[9px] text-slate-400 mb-1">Slot Video</label><input
                                    type="number" v-model.number="partner.slots"
                                    class="w-full text-[11px] bg-white border border-slate-200 rounded-lg p-2 outline-none" />
                            </div>
                            <button @click="budgetConfig.colabPartners.splice(idx, 1)"
                                class="absolute -top-2 -right-2 bg-rose-500 text-white h-5 w-5 rounded-full text-[10px] flex items-center justify-center"><i
                                    class="fa-solid fa-times"></i></button>
                        </div>
                        <div v-if="!budgetConfig.colabPartners || budgetConfig.colabPartners.length === 0"
                            class="text-center py-4 text-[10px] text-slate-400 italic bg-slate-50 rounded-xl border border-dashed border-slate-200">
                            Belum ada partner colab.</div>
                    </div>
                    <!-- Others -->
                    <div class="pb-4">
                        <div class="flex justify-between items-center mb-3">
                            <div class="text-[10px] font-bold text-slate-500 uppercase">Platform Lainnya</div>
                            <button type="button"
                                @click="budgetConfig.others.push({name: '', costPerUnit: 0, quantity: 1, duration: 1, balance: 0})"
                                class="text-[10px] bg-slate-100 px-2 py-1 rounded-lg hover:bg-slate-200 transition text-slate-600"><i
                                    class="fa-solid fa-plus mr-1"></i>Tambah</button>
                        </div>
                        <div v-for="(item, idx) in budgetConfig.others" :key="'oth'+idx"
                            class="grid grid-cols-2 md:grid-cols-6 gap-2 mb-2 bg-slate-50 p-3 rounded-xl border border-slate-100 relative">
                            <div class="col-span-2"><label class="block text-[9px] text-slate-400 mb-1">Nama
                                    Platform</label><input type="text" v-model="item.name"
                                    class="w-full text-[11px] font-bold bg-white border border-slate-200 rounded-lg p-2 outline-none" />
                            </div>
                            <div><label class="block text-[9px] text-slate-400 mb-1">Biaya Satuan</label><input
                                    type="number" v-model.number="item.costPerUnit"
                                    class="w-full text-[11px] bg-white border border-slate-200 rounded-lg p-2 outline-none" />
                            </div>
                            <div><label class="block text-[9px] text-slate-400 mb-1">Qty</label><input type="number"
                                    v-model.number="item.quantity"
                                    class="w-full text-[11px] bg-white border border-slate-200 rounded-lg p-2 outline-none" />
                            </div>
                            <div><label class="block text-[9px] text-slate-400 mb-1">Durasi</label><input type="number"
                                    v-model.number="item.duration"
                                    class="w-full text-[11px] bg-white border border-slate-200 rounded-lg p-2 outline-none" />
                            </div>
                            <div><label class="block text-[9px] text-slate-400 mb-1">Saldo</label><input type="number"
                                    v-model.number="item.balance"
                                    class="w-full text-[11px] bg-white border border-slate-200 rounded-lg p-2 outline-none" />
                            </div>
                            <button @click="budgetConfig.others.splice(idx, 1)"
                                class="absolute -top-2 -right-2 bg-rose-500 text-white h-5 w-5 rounded-full text-[10px] flex items-center justify-center"><i
                                    class="fa-solid fa-times"></i></button>
                        </div>
                        <div v-if="budgetConfig.others.length === 0"
                            class="text-center py-4 text-[10px] text-slate-400 italic bg-slate-50 rounded-xl border border-dashed border-slate-200">
                            Belum ada platform tambahan.</div>
                    </div>
                    <div class="flex justify-end pt-2 border-t border-slate-100">
                        <button @click="saveBudgetServer" :disabled="submitting"
                            class="px-5 py-2.5 bg-ppp-accent text-white rounded-xl text-[11px] font-bold flex items-center gap-2 hover:bg-ppp-accent-dark transition shadow-sm disabled:opacity-50">
                            <i v-if="!submitting" class="fa-solid fa-floppy-disk"></i>
                            <i v-else class="fa-solid fa-circle-notch fa-spin"></i>
                            {{ submitting ? 'Menyimpan...' : 'Simpan Konfigurasi' }}
                        </button>
                    </div>
                </div>

                <!-- Summary cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Meta Ads -->
                    <div class="dashboard-summary-card stat-card">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="icon-utility-button icon-utility-bordered !w-10 !h-10 text-slate-600">
                                <i class="fa-brands fa-meta text-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900">Meta Ads</h3>
                                <p class="text-[10px] text-slate-400 uppercase tracking-wider">Facebook &amp;
                                    Instagram</p>
                            </div>
                        </div>
                        <div class="space-y-2 text-[11px] text-slate-600">
                            <div class="flex justify-between border-b border-slate-50 pb-2"><span>Biaya /
                                    Iklan</span><span class="font-bold">{{ formatCurrency(budgetConfig.meta.costPerAd) }}</span></div>
                            <div class="flex justify-between border-b border-slate-50 pb-2"><span>Total
                                    Iklan</span><span class="font-bold">{{ budgetConfig.meta.totalAds }}
                                    Slot</span></div>
                            <div class="flex justify-between border-b border-slate-50 pb-2">
                                <span>Durasi</span><span class="font-bold">{{ budgetConfig.meta.days }}
                                    Hari</span>
                            </div>
                            <div class="flex justify-between bg-slate-50 p-2 rounded-lg"><span>Total
                                    Anggaran</span><span class="font-bold">{{ formatCurrency(budgetCalculations.metaTotal) }}</span></div>
                            <div class="flex justify-between text-slate-400"><span>Sisa Saldo</span><span>- {{ formatCurrency(budgetConfig.meta.balance) }}</span></div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between items-center">
                            <span class="text-[11px] font-bold text-slate-600">Topup Rencana</span>
                            <span class="type-title font-bold text-slate-900">{{ formatCurrency(budgetCalculations.metaTopup) }}</span>
                        </div>
                    </div>
                    <!-- Google Ads -->
                    <div class="dashboard-summary-card stat-card">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="icon-utility-button icon-utility-bordered !w-10 !h-10 text-slate-600">
                                <i class="fa-brands fa-google text-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900">Google Ads</h3>
                                <p class="text-[10px] text-slate-400 uppercase tracking-wider">Search &amp;
                                    Display</p>
                            </div>
                        </div>
                        <div class="space-y-2 text-[11px] text-slate-600">
                            <div class="flex justify-between border-b border-slate-50 pb-2"><span>Biaya /
                                    Ads</span><span class="font-bold">{{ formatCurrency(budgetConfig.google.costPerAd) }}</span></div>
                            <div class="flex justify-between border-b border-slate-50 pb-2"><span>Total
                                    Ads</span><span class="font-bold">{{ budgetConfig.google.totalAds }}
                                    Slot</span></div>
                            <div class="flex justify-between border-b border-slate-50 pb-2">
                                <span>Durasi</span><span class="font-bold">{{ budgetConfig.google.days }}
                                    Hari</span>
                            </div>
                            <div class="flex justify-between bg-slate-50 p-2 rounded-lg"><span>Total
                                    Anggaran</span><span class="font-bold">{{ formatCurrency(budgetCalculations.googleTotal) }}</span></div>
                            <div class="flex justify-between text-slate-400"><span>Sisa Saldo</span><span>- {{ formatCurrency(budgetConfig.google.balance) }}</span></div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between items-center">
                            <span class="text-[11px] font-bold text-slate-600">Topup Rencana</span>
                            <span class="type-title font-bold text-slate-900">{{ formatCurrency(budgetCalculations.googleTopup) }}</span>
                        </div>
                    </div>
                    <!-- Mekari -->
                    <div class="dashboard-summary-card stat-card">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="icon-utility-button icon-utility-bordered !w-10 !h-10 text-slate-600">
                                <i class="fa-solid fa-bullhorn text-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900">Mekari Ecosystem</h3>
                                <p class="text-[10px] text-slate-400 uppercase tracking-wider">Visitor &amp;
                                    Broadcast</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <p class="text-[10px] font-bold text-slate-500 uppercase mb-2">Mekari Visitor</p>
                            <div class="bg-slate-50 p-2 rounded-lg space-y-1 text-[11px] mb-2">
                                <div class="flex justify-between"><span>Target</span><b>{{ budgetConfig.mekari.visitor.targetPerDay }} visit/hari x {{ budgetConfig.mekari.visitor.days }}</b></div>
                                <div class="flex justify-between"><span>Total Visitor</span><b>{{ formatNumber(budgetCalculations.mekariVisitorTotal) }}</b></div>
                                <div class="flex justify-between text-slate-400"><span>Sisa Saldo</span><b>-{{ formatNumber(budgetConfig.mekari.visitor.balance) }}</b></div>
                                <div class="flex justify-between font-bold border-t border-slate-200 pt-1 mt-1">
                                    <span>Need Topup</span><span>~{{ formatNumber(budgetCalculations.mekariVisitorNeeded) }} Unit</span>
                                </div>
                            </div>
                            <div class="text-right font-bold text-slate-900">{{ formatCurrency(budgetConfig.mekari.visitor.topupCost) }}</div>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase mb-2">Mekari Broadcast</p>
                            <div class="bg-slate-50 p-2 rounded-lg space-y-1 text-[11px] mb-2">
                                <div class="flex justify-between"><span>Paket</span><b>{{ formatNumber(budgetConfig.mekari.broadcast.costPerWeek/1000) }}rb/mg x
                                        {{ budgetConfig.mekari.broadcast.weeks }}mg</b></div>
                                <div class="flex justify-between"><span>Special Price</span><b>+ {{ formatCurrency(budgetConfig.mekari.broadcast.specialPrice) }}</b></div>
                                <div class="flex justify-between"><span>Total Anggaran</span><b>{{ formatCurrency(budgetCalculations.mekariBroadcastTotal) }}</b></div>
                                <div class="flex justify-between text-slate-400"><span>Sisa Saldo</span><b>-{{ formatCurrency(budgetConfig.mekari.broadcast.balance) }}</b></div>
                            </div>
                            <div class="text-right font-bold text-slate-900">{{ formatCurrency(budgetCalculations.mekariBroadcastTopup) }}</div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between items-center">
                            <span class="text-[11px] font-bold text-slate-600">Total Topup Mekari</span>
                            <span class="type-title font-bold text-slate-900">{{ formatCurrency(budgetCalculations.mekariTopupTotal) }}</span>
                        </div>
                    </div>
                    <!-- Paid Colab -->
                    <div class="dashboard-summary-card stat-card">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="icon-utility-button icon-utility-bordered !w-10 !h-10 text-slate-600">
                                <i class="fa-solid fa-handshake text-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900">Paid Collaboration</h3>
                                <p class="text-[10px] text-slate-400 uppercase tracking-wider">Track by Partner
                                </p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div v-for="(colab, idx) in budgetCalculations.colabBreakdown" :key="'cb'+idx"
                                class="bg-slate-50 p-2 rounded-xl">
                                <div class="flex justify-between text-[11px] font-bold text-slate-700 mb-1">
                                    <span>{{ colab.name }}</span>
                                    <span :class="colab.remaining < 0 ? 'text-rose-500' : ''">{{ colab.used }} /
                                        {{ colab.slots }}</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-1.5 mb-1">
                                    <div class="bg-ppp-accent h-1.5 rounded-full transition-all"
                                        :style="'width:' + Math.min((colab.used / (colab.slots || 1)) * 100, 100) + '%'">
                                    </div>
                                </div>
                                <div class="flex justify-between text-[9px] text-slate-500">
                                    <span>Sisa Slot: <strong class="text-slate-700">{{ Math.max(0, colab.remaining) }}</strong></span>
                                    <span>Cost: {{ formatCurrency(colab.packageCost) }}</span>
                                </div>
                            </div>
                            <div v-if="budgetCalculations.colabBreakdown.length === 0"
                                class="text-center text-[10px] text-slate-400 italic py-4">Belum ada partner
                                colab. Tambahkan di pengaturan.</div>
                            <button @click="showColabListModal = true"
                                class="w-full text-[11px] font-bold text-slate-500 hover:text-ppp-accent flex items-center justify-center gap-2 py-2 rounded-xl hover:bg-slate-50 transition"><i
                                    class="fa-solid fa-list-ul"></i> Lihat Detail Riwayat</button>
                        </div>
                    </div>
                    <!-- Others -->
                    <div v-for="(item, idx) in budgetCalculations.othersCalculated" :key="'oth'+idx"
                        class="dashboard-summary-card stat-card">
                        <div class="flex items-center gap-3 mb-4">
                            <div
                                class="icon-utility-button icon-utility-bordered !w-10 !h-10 text-slate-600 bg-slate-100">
                                <i class="fa-solid fa-layer-group text-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900">{{ item.name || 'Platform Lain' }}</h3>
                                <p class="text-[10px] text-slate-400 uppercase tracking-wider">Additional
                                    Channel</p>
                            </div>
                        </div>
                        <div class="space-y-2 text-[11px] text-slate-600">
                            <div class="flex justify-between border-b border-slate-50 pb-2"><span>Biaya
                                    Satuan</span><span class="font-bold">{{ formatCurrency(item.costPerUnit) }}</span></div>
                            <div class="flex justify-between border-b border-slate-50 pb-2"><span>Jumlah
                                    (Qty)</span><span class="font-bold">{{ item.quantity }}</span></div>
                            <div class="flex justify-between border-b border-slate-50 pb-2">
                                <span>Durasi</span><span class="font-bold">{{ item.duration }}</span>
                            </div>
                            <div class="flex justify-between bg-slate-50 p-2 rounded-lg"><span>Total
                                    Anggaran</span><span class="font-bold">{{ formatCurrency(item.total) }}</span></div>
                            <div class="flex justify-between text-slate-400"><span>Sisa Saldo</span><span>- {{ formatCurrency(item.balance) }}</span></div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between items-center">
                            <span class="text-[11px] font-bold text-slate-600">Topup Rencana</span>
                            <span class="type-title font-bold text-slate-900">{{ formatCurrency(item.topup) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            </main>
        </div>

    <!-- Harga Kompetitor Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="hargaKompetitorModalOpen"
                class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                <div @click="hargaKompetitorModalOpen = false"
                    class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop"></div>
                <div
                    class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface">
                    <div class="modal-header-bar radius-sheet-top">
                        <div class="modal-header-copy">
                            <div
                                class="modal-header-icon bg-blue-50 text-blue-500">
                                <i class="fa-solid fa-calculator"></i>
                            </div>
                            <div>
                                <div class="type-title font-bold text-slate-800">{{ hargaKompetitorModalType === 'create' ? 'Tambah Data Harga' : 'Edit Data Harga' }}</div>
                                <div class="type-meta text-slate-400">Analisa harga dan kompetitor</div>
                            </div>
                        </div>
                        <button @click="hargaKompetitorModalOpen = false"
                            class="icon-utility-button icon-utility-danger"><i
                                class="fa-solid fa-xmark text-sm"></i></button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-6 space-y-4">
                        <div>
                            <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">Nama
                                Produk</label>
                            <input v-model="hargaKompetitorForm.Nama_Produk" type="text" class="form-input-compact"
                                placeholder="Contoh: Samsung S24 Ultra 256GB" />
                        </div>
                        <div>
                            <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">Tanggal
                                Cek</label>
                            <button @click="openCalendar($event, 'form', '', 'hargaKompetitorCek')"
                                class="filter-trigger-button toolbar-trigger-field">
                                <i class="fa-solid fa-calendar-days text-[10px] text-slate-400"></i>
                                <span
                                    :class="hargaKompetitorForm.Tanggal_Cek ? 'text-slate-700 font-medium' : 'text-slate-400'">{{ hargaKompetitorForm.Tanggal_Cek || 'Pilih tanggal' }}</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">Harga
                                    Distributor 1</label>
                                <input v-model.number="hargaKompetitorForm.Harga_Distributor_1" type="number"
                                    class="form-input-compact" />
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">Harga
                                    Distributor 2</label>
                                <input v-model.number="hargaKompetitorForm.Harga_Distributor_2" type="number"
                                    class="form-input-compact" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">Harga
                                    Kompetitor</label>
                                <input v-model.number="hargaKompetitorForm.Harga_Kompetitor" type="number"
                                    class="form-input-compact" />
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">Harga
                                    Rencana Jual</label>
                                <input v-model.number="hargaKompetitorForm.Harga_Rencana_Jual" type="number"
                                    class="form-input-compact" />
                            </div>
                        </div>
                        <div>
                            <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">Margin
                                Profit</label>
                            <input v-model.number="hargaKompetitorForm.Margin_Profit" type="number"
                                class="form-input-compact" />
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl text-[11px] text-slate-600">
                            <span class="font-bold">Selisih (Rencana Jual - Kompetitor): </span>
                            <span
                                :class="(hargaKompetitorForm.Harga_Rencana_Jual - hargaKompetitorForm.Harga_Kompetitor) >= 0 ? 'text-emerald-600 font-bold' : 'text-rose-500 font-bold'">
                                {{ formatCurrency((hargaKompetitorForm.Harga_Rencana_Jual || 0) - (hargaKompetitorForm.Harga_Kompetitor || 0)) }}
                            </span>
                        </div>
                    </div>
                    <div class="modal-footer-bar modal-footer-actions">
                        <button @click="hargaKompetitorModalOpen = false" class="modal-secondary-button">Batal</button>
                        <button @click="saveHargaKompetitor" :disabled="submitting" class="modal-primary-button">
                            <i v-if="submitting" class="fa-solid fa-circle-notch fa-spin"></i>
                            {{ submitting ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>

    <!-- Ads Log Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="adsModalOpen"
                class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                <div @click="adsModalOpen = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop"></div>
                <div
                    class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface">
                    <div class="modal-header-bar radius-sheet-top">
                        <div class="modal-header-copy">
                            <div class="modal-header-icon bg-blue-500 text-white">
                                <i class="fa-solid fa-rectangle-ad text-[14px]"></i>
                            </div>
                            <div>
                                <div class="type-title font-bold text-slate-800">{{ adsModalType === 'create' ? 'Tambah Iklan' : 'Edit Iklan' }}</div>
                                <div class="type-meta text-slate-400 uppercase tracking-widest">Ads Performance
                                    Log</div>
                            </div>
                        </div>
                        <button @click="adsModalOpen = false" class="icon-utility-button icon-utility-danger"><i
                                class="fa-solid fa-xmark text-sm"></i></button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="col-span-2">
                                <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">Tanggal <span class="text-red-500">*</span></label>
                                <button @click="openCalendar($event, 'form', '', 'adsTanggal')"
                                    class="filter-trigger-button toolbar-trigger-field">
                                    <i class="fa-solid fa-calendar-days text-[10px] text-slate-400"></i>
                                    <span :class="adsForm.Tanggal ? 'text-slate-700 font-medium' : 'text-slate-400'">{{ adsForm.Tanggal || 'Pilih tanggal' }}</span>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">Nama Iklan /
                                Campaign <span class="text-red-500">*</span></label>
                            <input v-model="adsForm.Nama" type="text" class="form-input-compact"
                                placeholder="Contoh: Promo Lebaran Reel" />
                        </div>
                        <div>
                            <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">ID Ads
                                (Optional)</label>
                            <input v-model="adsForm.ID_Ads" type="text"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-[11px] font-mono outline-none focus:border-ppp-accent"
                                placeholder="ID dari Ads Manager" />
                        </div>
                        <div>
                            <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">Platform</label>
                            <div class="relative search-select-container">
                                <button type="button" @click="toggleSearchSelect($event, 'ads_platform')"
                                    class="select-trigger-button toolbar-trigger-field">
                                    <span class="truncate"
                                        :class="adsForm.Platform ? 'text-slate-700' : 'text-slate-400'">{{ adsForm.Platform || 'Pilih Platform' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[9px] text-slate-400"></i>
                                </button>
                                <div v-if="searchSelectOpen === 'ads_platform'" :style="popoverStyle"
                                    class="search-select-popover search-select-popover--compact max-h-60 overflow-y-auto">
                                    <div v-for="platform in adsPlatformOptions" :key="platform"
                                        @click="adsForm.Platform = platform; searchSelectOpen = null"
                                        class="popover-option">
                                        {{ platform }}</div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">Kategori</label>
                            <div class="relative search-select-container">
                                <button type="button" @click="toggleSearchSelect($event, 'ads_kategori')"
                                    class="select-trigger-button toolbar-trigger-field">
                                    <span class="truncate"
                                        :class="adsForm.Kategori ? 'text-slate-700' : 'text-slate-400'">{{ adsForm.Kategori || 'Pilih Kategori' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[9px] text-slate-400"></i>
                                </button>
                                <div v-if="searchSelectOpen === 'ads_kategori'" :style="popoverStyle"
                                    class="search-select-popover search-select-popover--compact max-h-60 overflow-y-auto">
                                    <div v-for="k in adsKategoriOptions" :key="k"
                                        @click="adsForm.Kategori = k; searchSelectOpen = null" class="popover-option">
                                        {{ k }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="border-t border-slate-100 pt-4">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">
                                Engagement Metrics</p>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">Jangkauan
                                        (Reach)</label>
                                    <input v-model.number="adsForm.Jangkauan" type="number" min="0"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-[11px] text-right outline-none focus:border-ppp-accent" />
                                </div>
                                <div>
                                    <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">Suka
                                        / Like</label>
                                    <input v-model.number="adsForm.Suka" type="number" min="0"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-[11px] text-right outline-none focus:border-ppp-accent" />
                                </div>
                                <div>
                                    <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">Komentar</label>
                                    <input v-model.number="adsForm.Komentar" type="number" min="0"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-[11px] text-right outline-none focus:border-ppp-accent" />
                                </div>
                                <div>
                                    <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">Share</label>
                                    <input v-model.number="adsForm.Share" type="number" min="0"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-[11px] text-right outline-none focus:border-ppp-accent" />
                                </div>
                            </div>
                            <div class="mt-3 bg-slate-50 rounded-xl px-4 py-3 flex items-center justify-between">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Score (Auto)</span>
                                <span class="text-[20px] font-bold"
                                    :class="adsComputedScore >= 70 ? 'text-emerald-600' : adsComputedScore >= 40 ? 'text-amber-500' : 'text-slate-400'">{{ adsComputedScore }}</span>
                            </div>
                        </div>
                        <div class="border-t border-slate-100 pt-4">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Keuangan
                            </p>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">Biaya
                                        Iklan (Spent)</label>
                                    <input v-model.number="adsForm.Biaya" type="number" min="0"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-[11px] text-right outline-none focus:border-ppp-accent" />
                                </div>
                                <div>
                                    <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">Sisa
                                        Saldo Platform</label>
                                    <input v-model.number="adsForm.Sisa_Saldo" type="number" min="0"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-[11px] text-right outline-none focus:border-ppp-accent" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer-bar modal-footer-actions">
                        <button @click="adsModalOpen = false" class="modal-secondary-button">Batal</button>
                        <button @click="saveAdsRow" :disabled="submitting"
                            class="px-5 py-2.5 rounded-xl bg-blue-500 text-white text-[11px] font-bold hover:bg-blue-600 transition-all disabled:opacity-60">
                            {{ submitting ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>

    <!-- Laporan Event Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="lpjkModalOpen"
                class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                <div @click="lpjkModalOpen = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop"></div>
                <div
                    class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface">
                    <div class="modal-header-bar radius-sheet-top">
                        <div class="modal-header-copy">
                            <div
                                class="modal-header-icon bg-violet-50 text-violet-500">
                                <i class="fa-solid fa-calendar-check"></i>
                            </div>
                            <div>
                                <div class="type-title font-bold text-slate-800">{{ lpjkModalType === 'create' ? 'Tambah Event' : 'Edit Event' }}</div>
                                <div class="type-meta text-slate-400">Laporan kegiatan & anggaran</div>
                            </div>
                        </div>
                        <button @click="lpjkModalOpen = false" class="icon-utility-button icon-utility-danger"><i
                                class="fa-solid fa-xmark text-sm"></i></button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-6 space-y-4">
                        <div>
                            <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">Nama
                                Event</label>
                            <input v-model="lpjkForm.Nama_Event" type="text" class="form-input-compact"
                                placeholder="Contoh: Open Table Mall Hartono" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">Tanggal</label>
                                <button @click="openCalendar($event, 'form', '', 'lpjkTanggal')"
                                    class="filter-trigger-button toolbar-trigger-field">
                                    <i class="fa-solid fa-calendar-days text-[10px] text-slate-400"></i>
                                    <span :class="lpjkForm.Tanggal ? 'text-slate-700 font-medium' : 'text-slate-400'">{{ lpjkForm.Tanggal || 'Pilih tanggal' }}</span>
                                </button>
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">Status</label>
                                <div class="relative search-select-container">
                                    <button type="button" @click="toggleSearchSelect($event, 'lpjk_status')"
                                        class="select-trigger-button toolbar-trigger-field">
                                        <span class="truncate">{{ lpjkForm.Status || 'Pilih Status' }}</span>
                                        <i class="fa-solid fa-chevron-down text-[9px] text-slate-400"></i>
                                    </button>
                                    <div v-if="searchSelectOpen === 'lpjk_status'" :style="popoverStyle"
                                        class="search-select-popover search-select-popover--compact max-h-60 overflow-y-auto">
                                        <div v-for="s in lpjkStatusOptions" :key="s"
                                            @click="lpjkForm.Status = s; searchSelectOpen = null"
                                            class="popover-option">
                                            {{ s }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">Budget
                                    Rencana</label>
                                <input v-model.number="lpjkForm.Budget_Rencana" type="number"
                                    class="form-input-compact" />
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">Realisasi
                                    Biaya</label>
                                <input v-model.number="lpjkForm.Realisasi_Biaya" type="number"
                                    class="form-input-compact" />
                            </div>
                        </div>
                        <div>
                            <label class="type-meta font-bold text-slate-400 uppercase mb-1.5">Keterangan</label>
                            <textarea v-model="lpjkForm.Keterangan" rows="2" class="form-input-compact resize-none"
                                placeholder="Catatan tambahan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer-bar modal-footer-actions">
                        <button @click="lpjkModalOpen = false" class="modal-secondary-button">Batal</button>
                        <button @click="saveLpjk" :disabled="submitting" class="modal-primary-button">
                            <i v-if="submitting" class="fa-solid fa-circle-notch fa-spin"></i>
                            {{ submitting ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>

    <!-- LPJK Detail Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="lpjkDetailModalOpen"
                class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-dialog">
                <div @click="closeLpjkDetail" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop"></div>
                <div
                    class="modal-width-wide radius-dialog modal-dialog-surface overlay-dialog-surface flex flex-col max-h-[92vh]">
                    <div
                        class="modal-header-bar radius-sheet-top shrink-0">
                        <div class="modal-header-copy">
                            <div
                                class="modal-header-icon bg-violet-500 text-white shadow-lg">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                            </div>
                            <div>
                                <div class="type-title font-bold text-slate-800">Detail Keuangan: {{ activeLpjkRow && activeLpjkRow.Nama_Event }}</div>
                                <div class="type-meta text-slate-400">Input Pengeluaran &amp; Cetak LPJK</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="exportLpjkDetailToPDF" class="secondary-cta-button secondary-cta-danger"><i
                                    class="fa-solid fa-file-pdf"></i> PDF</button>
                            <button @click="closeLpjkDetail" class="icon-utility-button icon-utility-danger"><i
                                    class="fa-solid fa-xmark text-sm"></i></button>
                        </div>
                    </div>
                    <div class="flex-1 overflow-y-auto p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                            <!-- Add item form -->
                            <div class="space-y-4">
                                <div class="text-[10px] font-bold text-slate-500 uppercase mb-2">Tambah Pengeluaran
                                </div>
                                <div>
                                    <label class="type-meta font-bold text-slate-400 uppercase mb-1">Kategori</label>
                                    <div class="relative search-select-container">
                                        <button type="button"
                                            @click="toggleSearchSelect($event, 'lpjk_expense_category')"
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-[11px] outline-none hover:border-ppp-accent transition-all flex items-center justify-between gap-2">
                                            <span class="truncate">{{ lpjkDetailItem.Kategori || 'Pilih Kategori' }}</span>
                                            <i class="fa-solid fa-chevron-down text-[9px] text-slate-400"></i>
                                        </button>
                                        <div v-if="searchSelectOpen === 'lpjk_expense_category'" :style="popoverStyle"
                                            class="search-select-popover search-select-popover--compact max-h-60 overflow-y-auto">
                                            <div v-for="cat in lpjkExpenseCategories" :key="cat"
                                                @click="lpjkDetailItem.Kategori = cat; searchSelectOpen = null"
                                                class="popover-option">
                                                {{ cat }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="type-meta font-bold text-slate-400 uppercase mb-1">Nama
                                        Pengeluaran</label>
                                    <input v-model="lpjkDetailItem.Nama_Pengeluaran" type="text"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-[11px] outline-none focus:border-ppp-accent"
                                        placeholder="Contoh: Print Undangan" />
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="type-meta font-bold text-slate-400 uppercase mb-1">Harga
                                            Satuan</label>
                                        <input v-model.number="lpjkDetailItem.Satuan" type="number"
                                            @input="lpjkDetailItem.Total = (lpjkDetailItem.Satuan||0)*(lpjkDetailItem.Jumlah||0)"
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-[11px] outline-none focus:border-ppp-accent" />
                                    </div>
                                    <div>
                                        <label class="type-meta font-bold text-slate-400 uppercase mb-1">Jumlah
                                            (Qty)</label>
                                        <input v-model.number="lpjkDetailItem.Jumlah" type="number"
                                            @input="lpjkDetailItem.Total = (lpjkDetailItem.Satuan||0)*(lpjkDetailItem.Jumlah||0)"
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-[11px] outline-none focus:border-ppp-accent" />
                                    </div>
                                </div>
                                <div>
                                    <label class="type-meta font-bold text-slate-400 uppercase mb-1">Bukti /
                                        No. Nota</label>
                                    <input v-model="lpjkDetailItem.Bukti" type="text"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-[11px] outline-none focus:border-ppp-accent"
                                        placeholder="Nota No 01" />
                                </div>
                                <div
                                    class="bg-slate-50 p-3 rounded-xl flex justify-between items-center border border-slate-100">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase">Subtotal</span>
                                    <span class="font-bold text-slate-800">{{ formatCurrency(lpjkDetailItem.Total || 0) }}</span>
                                </div>
                                <button @click="saveLpjkDetail"
                                    :disabled="submitting || !lpjkDetailItem.Nama_Pengeluaran"
                                    class="modal-primary-button w-full active:scale-95 disabled:opacity-50">
                                    <i v-if="submitting" class="fa-solid fa-circle-notch fa-spin"></i>
                                    {{ submitting ? 'Menyimpan...' : 'Tambahkan Item' }}
                                </button>
                            </div>
                            <!-- Print area -->
                            <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl p-4 md:p-8 text-slate-900 overflow-hidden">
                                <div class="text-center mb-8">
                                    <h3 class="dashboard-summary-unit leading-snug">LAPORAN KEGIATAN
                                        PENGGUNAAN<br />DANA {{ activeLpjkRow && activeLpjkRow.Nama_Event }}</h3>
                                </div>
                                <div class="space-y-4 text-sm">
                                    <div class="flex items-start gap-3">
                                        <span class="font-bold">A.</span>
                                        <span class="font-bold">Pengeluaran</span>
                                    </div>
                                    <template v-for="(items, category, catIdx) in lpjkDetailGrouped" :key="category">
                                        <div class="ml-6 space-y-3">
                                            <div class="flex items-start gap-2">
                                                <span class="font-bold">{{ catIdx + 1 }}.</span>
                                                <span class="font-bold">Seksi {{ category }}</span>
                                            </div>
                                            <div class="overflow-x-auto">
                                            <table class="w-full border-collapse text-[10.5px] min-w-[460px]">
                                                <thead>
                                                    <tr>
                                                        <th
                                                            class="border-b-2 border-slate-800 px-2 py-2 w-[5%] text-left">
                                                            NO</th>
                                                        <th
                                                            class="border-b-2 border-slate-800 px-2 py-2 w-[35%] text-left">
                                                            NAMA PENGELUARAN</th>
                                                        <th
                                                            class="border-b-2 border-slate-800 px-2 py-2 w-[15%] text-right">
                                                            SATUAN</th>
                                                        <th
                                                            class="border-b-2 border-slate-800 px-2 py-2 w-[8%] text-center">
                                                            QTY</th>
                                                        <th
                                                            class="border-b-2 border-slate-800 px-2 py-2 w-[17%] text-right">
                                                            TOTAL BIAYA</th>
                                                        <th
                                                            class="border-b-2 border-slate-800 px-2 py-2 w-[15%] text-center">
                                                            BUKTI</th>
                                                        <th
                                                            class="border-b-2 border-slate-800 px-2 py-2 w-[5%] text-center text-slate-400">
                                                            #</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(item, i) in items" :key="item.ID">
                                                        <td class="border-b border-slate-200 px-2 py-2 text-center">
                                                            {{ i + 1 }}</td>
                                                        <td
                                                            class="border-b border-slate-200 px-2 py-2 uppercase text-[10px]">
                                                            {{ item.Nama_Pengeluaran }}</td>
                                                        <td class="border-b border-slate-200 px-2 py-2 text-right">
                                                            {{ formatCurrency(item.Satuan) }}</td>
                                                        <td class="border-b border-slate-200 px-2 py-2 text-center">
                                                            {{ item.Jumlah }}</td>
                                                        <td
                                                            class="border-b border-slate-200 px-2 py-2 text-right font-bold">
                                                            {{ formatCurrency(item.Total) }}</td>
                                                        <td
                                                            class="border-b border-slate-200 px-2 py-2 text-center italic text-[10px]">
                                                            {{ item.Bukti }}</td>
                                                        <td class="border-b border-slate-200 px-1 py-1 text-center">
                                                            <button @click="deleteLpjkDetail(item.ID)"
                                                                class="text-slate-300 hover:text-rose-500 transition px-2 py-1"><i
                                                                    class="fa-solid fa-trash text-[10px]"></i></button>
                                                        </td>
                                                    </tr>
                                                    <tr class="font-bold bg-slate-50">
                                                        <td colspan="4"
                                                            class="border-b border-slate-200 px-2 py-2 text-center uppercase text-[10px]">
                                                            JUMLAH</td>
                                                        <td class="border-b border-slate-200 px-2 py-2 text-right">
                                                            {{ formatCurrency(items.reduce((s,d) => s + (Number(d.Total)||0), 0)) }}</td>
                                                        <td colspan="2" class="border-b border-slate-200 px-2 py-2">
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            </div>
                                        </div>
                                    </template>
                                    <div v-if="Object.keys(lpjkDetailGrouped).length === 0"
                                        class="ml-6 text-[11px] text-slate-400 italic py-4">Belum ada pengeluaran.
                                        Tambahkan dari form di sebelah kiri.</div>
                                    <div
                                        class="mt-6 pt-4 border-t-2 border-double border-slate-800 flex justify-between items-center font-bold">
                                        <span class="uppercase text-sm">TOTAL KESELURUHAN PENGELUARAN</span>
                                        <span class="text-[14px] underline decoration-double underline-offset-4">{{ formatCurrency(lpjkDetailTotal) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>

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
                        <div class="type-title font-bold text-slate-800">Riwayat Konten Colab</div>
                        <button @click="showColabListModal = false" class="icon-utility-button icon-utility-danger"
                            aria-label="Tutup"><i class="fa-solid fa-xmark text-sm"></i></button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-6">
                        <div v-if="budgetCalculations.colabList.length === 0"
                            class="text-center py-8 text-[11px] text-slate-400">Belum ada konten colab terdaftar.
                        </div>
                        <div v-for="(item, idx) in budgetCalculations.colabList" :key="idx"
                            class="flex items-center justify-between py-2.5 border-b border-slate-50 last:border-0">
                            <div>
                                <p class="text-[11px] font-bold text-slate-800">{{ item.Judul }}</p>
                                <p class="text-[9px] text-slate-400 mt-0.5">{{ item.Tanggal_Rencana }}</p>
                            </div>
                            <span
                                class="px-2.5 py-1 bg-ppp-accent/10 text-ppp-accent text-[9px] font-bold rounded-lg">{{ item.colabPartner }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>

    <!-- Sell Out Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="sellOutModalOpen"
                class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                <div @click="sellOutModalOpen = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop">
                </div>
                <div
                    class="mobile-sheet modal-width-detail radius-sheet modal-sheet-surface">
                    <div
                        class="modal-header-bar modal-header-bar-sticky radius-sheet-top z-[2010]">
                        <div class="flex items-center gap-3">
                            <div
                                class="modal-header-icon bg-emerald-50 text-emerald-500 border border-emerald-100">
                                <i class="fa-solid fa-arrow-trend-up"></i>
                            </div>
                            <div>
                                <div class="type-title text-slate-900">{{ sellOutModalType === 'create' ? 'Tambah Target' : 'Edit Target' }}</div>
                                <div class="type-meta text-slate-400 uppercase tracking-widest mt-0.5">Sell Out
                                    Target</div>
                            </div>
                        </div>
                        <button @click="sellOutModalOpen = false" aria-label="Tutup modal"
                            class="icon-utility-button icon-utility-round"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="p-6 overflow-y-auto flex-1 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Vendor -->
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Vendor</label>
                                <div @click="toggleSearchSelect($event, 'sotVendor')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="sellOutForm.Vendor ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ sellOutForm.Vendor || 'Pilih / Ketik Vendor' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </div>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'sotVendor'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text" placeholder="Cari vendor..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in sellOutVendorOptions.filter(o => !searchSelectQuery || String(o || '').toLowerCase().includes(String(searchSelectQuery || '').toLowerCase()))"
                                                :key="opt" @click="sellOutForm.Vendor = opt; searchSelectOpen = null"
                                                :class="['popover-option', sellOutForm.Vendor === opt ? 'popover-option-active' : '']">
                                                {{ opt }}</div>
                                        </div>
                                    </div>
                                </transition>
                                <input v-model="sellOutForm.Vendor" type="text" placeholder="atau ketik manual..."
                                    class="w-full bg-transparent border-0 px-4 pt-1 pb-0 text-[10px] text-slate-400 outline-none" />
                            </div>
                            <!-- Kategori -->
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Kategori</label>
                                <div @click="toggleSearchSelect($event, 'sotKategori')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="sellOutForm.Kategori ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ sellOutForm.Kategori || 'Pilih Kategori' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </div>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'sotKategori'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text" placeholder="Cari..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unitKategoriOptions.filter(o => !searchSelectQuery || String(o || '').toLowerCase().includes(String(searchSelectQuery || '').toLowerCase()))"
                                                :key="opt"
                                                @click="sellOutForm.Kategori = opt; sellOutForm.Brand = ''; sellOutForm.Seri = ''; buildSellOutProductName(); searchSelectOpen = null"
                                                :class="['popover-option', sellOutForm.Kategori === opt ? 'popover-option-active' : '']">
                                                {{ opt }}</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <!-- Brand -->
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Brand</label>
                                <div @click="toggleSearchSelect($event, 'sotBrand')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="sellOutForm.Brand ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ sellOutForm.Brand || 'Pilih Brand' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </div>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'sotBrand'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text" placeholder="Cari..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in getBrandOptions(sellOutForm.Kategori).filter(o => !searchSelectQuery || String(o || '').toLowerCase().includes(String(searchSelectQuery || '').toLowerCase()))"
                                                :key="opt"
                                                @click="sellOutForm.Brand = opt; sellOutForm.Seri = ''; buildSellOutProductName(); searchSelectOpen = null"
                                                :class="['popover-option', sellOutForm.Brand === opt ? 'popover-option-active' : '']">
                                                {{ opt }}</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <!-- Seri -->
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Seri</label>
                                <div @click="toggleSearchSelect($event, 'sotSeri')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span :class="sellOutForm.Seri ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ sellOutForm.Seri || 'Pilih / Ketik Seri' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </div>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'sotSeri'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text" placeholder="Cari / Ketik..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in getSeriOptions(sellOutForm.Kategori, sellOutForm.Brand).filter(o => !searchSelectQuery || String(o || '').toLowerCase().includes(String(searchSelectQuery || '').toLowerCase()))"
                                                :key="opt"
                                                @click="sellOutForm.Seri = opt; buildSellOutProductName(); searchSelectOpen = null"
                                                :class="['popover-option', sellOutForm.Seri === opt ? 'popover-option-active' : '']">
                                                {{ opt }}</div>
                                        </div>
                                    </div>
                                </transition>
                                <input v-model="sellOutForm.Seri" @input="buildSellOutProductName" type="text"
                                    placeholder="tambah data di menu Nama Stock"
                                    class="w-full bg-transparent border-0 px-4 pt-1 pb-0 text-[10px] text-slate-400 outline-none" />
                            </div>
                            <!-- RAM -->
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">RAM</label>
                                <div @click="toggleSearchSelect($event, 'sotRAM')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span :class="sellOutForm.RAM ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ sellOutForm.RAM || 'Pilih RAM' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </div>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'sotRAM'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text" placeholder="Cari..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unitRAMOptions.filter(o => !searchSelectQuery || String(o || '').toLowerCase().includes(String(searchSelectQuery || '').toLowerCase()))"
                                                :key="opt"
                                                @click="sellOutForm.RAM = opt; buildSellOutProductName(); searchSelectOpen = null"
                                                :class="['popover-option', sellOutForm.RAM === opt ? 'popover-option-active' : '']">
                                                {{ opt }}</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <!-- Internal -->
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Internal</label>
                                <div @click="toggleSearchSelect($event, 'sotInternal')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="sellOutForm.Internal ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ sellOutForm.Internal || 'Pilih Internal' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </div>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'sotInternal'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text" placeholder="Cari..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unitInternalOptions.filter(o => !searchSelectQuery || String(o || '').toLowerCase().includes(String(searchSelectQuery || '').toLowerCase()))"
                                                :key="opt"
                                                @click="sellOutForm.Internal = opt; buildSellOutProductName(); searchSelectOpen = null"
                                                :class="['popover-option', sellOutForm.Internal === opt ? 'popover-option-active' : '']">
                                                {{ opt }}</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <!-- Size -->
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Ukuran</label>
                                <div @click="toggleSearchSelect($event, 'sotSize')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span :class="sellOutForm.Size ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ sellOutForm.Size || 'Pilih Ukuran' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </div>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'sotSize'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text" placeholder="Cari..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unitSizeOptions.filter(o => !searchSelectQuery || String(o || '').toLowerCase().includes(String(searchSelectQuery || '').toLowerCase()))"
                                                :key="opt"
                                                @click="sellOutForm.Size = opt; buildSellOutProductName(); searchSelectOpen = null"
                                                :class="['popover-option', sellOutForm.Size === opt ? 'popover-option-active' : '']">
                                                {{ opt }}</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <!-- Kondisi -->
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Kondisi</label>
                                <div @click="toggleSearchSelect($event, 'sotKondisi')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="sellOutForm.Kondisi ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ sellOutForm.Kondisi || 'Pilih Kondisi' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </div>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'sotKondisi'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text" placeholder="Cari..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unitKondisiOptions.filter(o => !searchSelectQuery || String(o || '').toLowerCase().includes(String(searchSelectQuery || '').toLowerCase()))"
                                                :key="opt"
                                                @click="sellOutForm.Kondisi = opt; buildSellOutProductName(); searchSelectOpen = null"
                                                :class="['popover-option', sellOutForm.Kondisi === opt ? 'popover-option-active' : '']">
                                                {{ opt }}</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <!-- Nama Produk (auto) -->
                            <div class="col-span-2">
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Nama
                                    Produk <span class="text-ppp-accent">(auto)</span></label>
                                <input v-model="sellOutForm.Nama_Produk" type="text" disabled
                                    class="w-full bg-slate-100 border border-slate-200 rounded-2xl px-4 py-3 text-[12px] font-bold text-slate-500 outline-none cursor-not-allowed"
                                    placeholder="Terisi otomatis dari field di atas..." />
                            </div>
                            <!-- Target Unit -->
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Target
                                    Unit</label>
                                <input v-model.number="sellOutForm.Target_Unit" type="number" min="0"
                                    class="form-input text-right" />
                            </div>
                            <!-- Bonus per Unit -->
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Bonus
                                    / Unit (Rp)</label>
                                <input v-model.number="sellOutForm.Bonus_Nominal" type="number" min="0"
                                    class="form-input text-right" />
                            </div>
                            <!-- Realisasi -->
                            <div>
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Realisasi
                                    (unit terjual)</label>
                                <input v-model.number="sellOutForm.Realisasi_Unit" type="number" min="0"
                                    class="form-input text-right" />
                            </div>
                            <!-- Preview bonus -->
                            <div
                                class="bg-emerald-50 border border-emerald-100 rounded-2xl px-4 py-3 flex items-center justify-between">
                                <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">Est.
                                    Bonus</span>
                                <span class="text-[14px] font-bold text-emerald-700">{{ formatCurrency((sellOutForm.Realisasi_Unit || 0) >= (sellOutForm.Target_Unit || 0) && (sellOutForm.Target_Unit || 0) > 0 ? (sellOutForm.Realisasi_Unit || 0) * (sellOutForm.Bonus_Nominal || 0) : 0) }}</span>
                            </div>
                            <!-- Periode Start -->
                            <div class="relative search-select-container">
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Periode
                                    Mulai</label>
                                <div @click="openCalendar($event, 'form', '', 'sotDate1')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="sellOutForm.Periode_Start ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ sellOutForm.Periode_Start ? formatFullDate(sellOutForm.Periode_Start) : 'Pilih Tanggal' }}</span>
                                    <i class="fa-solid fa-calendar-day text-[10px] text-slate-300"></i>
                                </div>
                            </div>
                            <!-- Periode End -->
                            <div class="relative search-select-container">
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Periode
                                    Selesai</label>
                                <div @click="openCalendar($event, 'form', '', 'sotDate2')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="sellOutForm.Periode_End ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ sellOutForm.Periode_End ? formatFullDate(sellOutForm.Periode_End) : 'Pilih Tanggal' }}</span>
                                    <i class="fa-solid fa-calendar-day text-[10px] text-slate-300"></i>
                                </div>
                            </div>
                            <!-- Catatan -->
                            <div class="col-span-2">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Catatan</label>
                                <textarea v-model="sellOutForm.Catatan" rows="2" placeholder="Catatan tambahan..."
                                    class="form-input resize-none"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer-bar modal-footer-actions">
                        <button @click="sellOutModalOpen = false" class="modal-secondary-button">Batal</button>
                        <button @click="saveSellOut" :disabled="submitting"
                            class="modal-primary-button modal-primary-button--success">{{ submitting ? 'Menyimpan...' : 'Simpan' }}</button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>

    <!-- Master Plan Modal -->
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
                                :class="['modal-header-icon text-white', modalType === 'create' ? 'bg-emerald-500' : 'bg-ppp-accent']">
                            <i
                                :class="['fa-solid text-[12px]', modalType === 'create' ? 'fa-plus' : 'fa-pen-to-square']"></i>
                        </div>
                        <div>
                            <div class="type-title text-slate-900">{{ modalType === 'create' ? 'Tambah Plan Baru' : 'Edit Plan Konten' }}</div>
                            <div class="type-meta text-slate-400 uppercase tracking-widest mt-0.5">Marketing Module
                            </div>
                        </div>
                    </div>
                    <button @click="modalOpen = false" aria-label="Tutup modal"
                        class="icon-utility-button icon-utility-round">
                        <i class="fa-solid fa-xmark text-[12px]"></i>
                    </button>
                </div>

                <!-- Scrollable Body -->
                <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
                    <div class="space-y-6">
                        <section class="space-y-5">
                            <div class="form-section-card">
                                <div class="form-section-title">Ringkasan Konten</div>
                                <div class="form-section-copy">Info inti plan, owner, status, dan jadwal kerja.</div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <!-- 1. Judul -->
                                <div class="md:col-span-2">
                                    <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Judul
                                        Konten <span class="text-red-500">*</span></label>
                                    <input v-model="masterForm.Judul" type="text" placeholder="Contoh: Review iPhone 15 Pro"
                                        class="form-input" />
                                </div>

                                <!-- 2. Link Folder Drive -->
                                <div class="md:col-span-2">
                                    <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Link
                                        Folder Drive (Materi/Raw)</label>
                                    <div class="relative">
                                        <i
                                            class="fa-brands fa-google-drive absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-[12px]"></i>
                                        <input v-model="masterForm.Link_Drive" type="text"
                                            placeholder="https://drive.google.com/..." class="form-input pl-10" />
                                    </div>
                                </div>

                                <!-- 3. Format & Status Side-by-side -->
                                <div class="relative search-select-container">
                                    <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Format
                                        Konten <span class="text-red-500">*</span></label>
                                    <div @click="toggleSearchSelect($event, 'format')"
                                        class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                        <span
                                            :class="masterForm.Format_Konten ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ masterForm.Format_Konten || 'Pilih Format' }}</span>
                                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                    </div>
                                    <transition name="fade">
                                        <div v-if="searchSelectOpen === 'format'" :style="popoverStyle"
                                            class="search-select-popover">
                                            <div class="relative mb-2">
                                                <i
                                                    class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                                <input v-model="searchSelectQuery" type="text" placeholder="Cari format..."
                                                    class="form-input-popover" @click.stop />
                                            </div>
                                            <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                                <div v-for="opt in filteredFormatOptions" :key="opt" @click="selectFormat(opt)"
                                                    :class="['popover-option', masterForm.Format_Konten === opt ? 'popover-option-active' : '']">
                                                    {{ opt }} </div>
                                                <div v-if="filteredFormatOptions.length === 0"
                                                    class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                    Tidak ditemukan</div>
                                            </div>
                                        </div>
                                    </transition>
                                </div>

                                <div class="relative search-select-container">
                                    <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Status
                                        <span class="text-red-500">*</span></label>
                                    <div @click="toggleSearchSelect($event, 'status')"
                                        class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                        <span class="text-slate-800 font-medium">{{ masterForm.Status }}</span>
                                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                    </div>
                                    <transition name="fade">
                                        <div v-if="searchSelectOpen === 'status'" :style="popoverStyle"
                                            class="search-select-popover">
                                            <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                                <div v-for="opt in statusOptions" :key="opt" @click="selectStatus(opt)"
                                                    :class="['popover-option', masterForm.Status === opt ? 'popover-option-active' : '']">
                                                    {{ opt }} </div>
                                            </div>
                                        </div>
                                    </transition>
                                </div>

                                <!-- 6. Editor, Talent & Tanggal Rencana -->
                                <div class="relative search-select-container">
                                    <label
                                        class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Editor <span class="text-red-500">*</span></label>
                                    <div @click="toggleSearchSelect($event, 'editor')"
                                        class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                        <span :class="masterForm.Editor ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ masterForm.Editor || 'Pilih Editor' }}</span>
                                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                    </div>
                                    <transition name="fade">
                                        <div v-if="searchSelectOpen === 'editor'" :style="popoverStyle"
                                            class="search-select-popover">
                                            <div class="relative mb-2">
                                                <i
                                                    class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                                <input v-model="searchSelectQuery" type="text" placeholder="Cari editor..."
                                                    class="form-input-popover" @click.stop />
                                            </div>
                                            <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                                <div v-for="opt in filteredEditorOptions" :key="opt"
                                                    @click="masterForm.Editor = opt; searchSelectOpen = null"
                                                    :class="['popover-option', masterForm.Editor === opt ? 'popover-option-active' : '']">
                                                    {{ opt }} </div>
                                                <div v-if="filteredEditorOptions.length === 0"
                                                    class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                    Tidak ditemukan</div>
                                            </div>
                                        </div>
                                    </transition>
                                </div>

                                <div class="relative search-select-container">
                                    <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Tanggal
                                        Rencana</label>
                                    <div @click="openCalendar($event, 'form', '', 'master')"
                                        class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                        <span
                                            :class="masterForm.Tanggal_Rencana ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ masterForm.Tanggal_Rencana ? formatFullDate(masterForm.Tanggal_Rencana) : 'Pilih Tanggal' }}</span>
                                        <i class="fa-solid fa-calendar-day text-[10px] text-slate-300"></i>
                                    </div>
                                </div>

                                <div class="relative md:col-span-2 search-select-container">
                                    <label
                                        class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Talent</label>
                                    <div @click="toggleSearchSelect($event, 'talent')"
                                        class="select-trigger-button select-trigger-button-form min-h-[48px]">
                                        <div class="flex flex-wrap gap-1">
                                            <span v-if="!masterForm.Talent.length" class="text-slate-400">Pilih / cari
                                                talent...</span>
                                            <div v-for="talent in masterForm.Talent" :key="talent" class="multi-select-chip">
                                                {{ talent }}
                                                <i @click.stop="toggleTalent(talent)"
                                                    class="fa-solid fa-xmark hover:text-red-500 cursor-pointer"></i>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-300 flex-shrink-0"></i>
                                    </div>
                                    <transition name="fade">
                                        <div v-if="searchSelectOpen === 'talent'" :style="popoverStyle"
                                            class="search-select-popover">
                                            <div class="relative mb-2">
                                                <i
                                                    class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                                <input v-model="searchSelectQuery" type="text" placeholder="Cari talent..."
                                                    class="form-input-popover" @click.stop />
                                            </div>
                                            <div class="max-h-48 overflow-y-auto custom-scrollbar space-y-1 p-1">
                                                <div v-for="opt in filteredTalentOptions" :key="opt" @click="toggleTalent(opt)"
                                                    :class="['popover-option-check', masterForm.Talent.includes(opt) ? 'popover-option-active' : '']">
                                                    <span>{{ opt }}</span>
                                                    <i v-if="masterForm.Talent.includes(opt)"
                                                        class="fa-solid fa-check text-[10px]"></i>
                                                    <div v-else
                                                        class="w-4 h-4 border-2 border-slate-200 rounded group-hover:border-ppp-accent transition-colors">
                                                    </div>
                                                </div>
                                                <div v-if="filteredTalentOptions.length === 0"
                                                    class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                    Tidak ditemukan</div>
                                            </div>
                                        </div>
                                    </transition>
                                </div>
                            </div>
                        </section>

                        <section class="space-y-5">
                            <div class="form-section-card">
                                <div class="form-section-title">Distribusi & Asset</div>
                                <div class="form-section-copy">Platform tayang, link kerja, skrip, caption, dan metadata
                                    publish.</div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <!-- 4. Platforms -->
                                <div class="relative md:col-span-2 search-select-container">
                                    <label
                                        class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Platforms</label>
                                    <div @click="toggleSearchSelect($event, 'platforms')"
                                        class="select-trigger-button select-trigger-button-form min-h-[48px]">
                                        <div class="flex flex-wrap gap-1">
                                            <span v-if="!masterForm.Platforms.length" class="text-slate-400">Pilih
                                                Platform</span>
                                            <div v-for="p in masterForm.Platforms" :key="p" class="multi-select-chip">
                                                {{ p }}
                                                <i @click.stop="togglePlatform(p)"
                                                    class="fa-solid fa-xmark hover:text-red-500"></i>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-300 flex-shrink-0"></i>
                                    </div>
                                    <transition name="fade">
                                        <div v-if="searchSelectOpen === 'platforms'" :style="popoverStyle"
                                            class="search-select-popover">
                                            <div class="relative mb-2">
                                                <i
                                                    class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                                <input v-model="searchSelectQuery" type="text" placeholder="Cari platform..."
                                                    class="form-input-popover" @click.stop />
                                            </div>
                                            <div class="max-h-48 overflow-y-auto custom-scrollbar space-y-1 p-1">
                                                <div v-for="opt in filteredPlatformOptions" :key="opt"
                                                    @click="togglePlatform(opt)"
                                                    :class="['popover-option-check', masterForm.Platforms.includes(opt) ? 'popover-option-active' : '']">
                                                    <span>{{ opt }}</span>
                                                    <i v-if="masterForm.Platforms.includes(opt)"
                                                        class="fa-solid fa-check text-[10px]"></i>
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
                                        class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Colab</label>
                                    <div @click="toggleSearchSelect($event, 'colab')"
                                        class="select-trigger-button select-trigger-button-form min-h-[48px]">
                                        <div class="flex flex-wrap gap-1">
                                            <span v-if="!masterForm.Colab.length" class="text-slate-400">Pilih / cari
                                                colab...</span>
                                            <div v-for="c in masterForm.Colab" :key="c" class="multi-select-chip">
                                                {{ c }}
                                                <i @click.stop="toggleColab(c)"
                                                    class="fa-solid fa-xmark hover:text-red-500 cursor-pointer"></i>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-300 flex-shrink-0"></i>
                                    </div>
                                    <transition name="fade">
                                        <div v-if="searchSelectOpen === 'colab'" :style="popoverStyle"
                                            class="search-select-popover">
                                            <div class="relative mb-2">
                                                <i
                                                    class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                                <input v-model="searchSelectQuery" type="text" placeholder="Cari colab..."
                                                    class="form-input-popover" @click.stop />
                                            </div>
                                            <div class="max-h-48 overflow-y-auto custom-scrollbar space-y-1 p-1">
                                                <div v-for="opt in filteredColabOptions" :key="opt" @click="toggleColab(opt)"
                                                    :class="['popover-option-check', masterForm.Colab.includes(opt) ? 'popover-option-active' : '']">
                                                    <span>{{ opt }}</span>
                                                    <i v-if="masterForm.Colab.includes(opt)"
                                                        class="fa-solid fa-check text-[10px]"></i>
                                                    <div v-else
                                                        class="w-4 h-4 border-2 border-slate-200 rounded group-hover:border-ppp-accent transition-colors">
                                                    </div>
                                                </div>
                                                <div v-if="filteredColabOptions.length === 0"
                                                    class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                    Tidak ditemukan</div>
                                            </div>
                                        </div>
                                    </transition>
                                </div>

                                <!-- 7. Distribution Meta (show when status SCHEDULE or PUBLISHED) -->
                                <template
                                    v-if="masterForm.Status && (masterForm.Status.toUpperCase() === 'SCHEDULE' || masterForm.Status.toUpperCase() === 'PUBLISHED' || masterForm.Status.toUpperCase() === 'DONE')">
                                    <div class="md:col-span-2">
                                        <label
                                            class="block text-[10px] font-bold text-blue-500 uppercase tracking-widest mb-3">Distribution
                                            Details</label>
                                        <div class="space-y-3">
                                            <div v-for="plat in masterForm.Platforms" :key="plat" class="surface-panel-soft">
                                                <div class="flex items-center gap-2 mb-3">
                                                    <i :class="[getPlatformIcon(plat), 'text-slate-400 text-[12px]']"></i>
                                                    <span class="text-[11px] font-bold text-slate-700">{{ plat }}</span>
                                                </div>
                                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                    <div class="sm:col-span-1">
                                                        <label
                                                            class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Link
                                                            Post</label>
                                                        <input v-model="masterForm.Distribution_Meta[plat].link" type="text"
                                                            placeholder="https://..." class="form-input-compact-white" />
                                                    </div>
                                                    <div>
                                                        <label
                                                            class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Type</label>
                                                        <div @click="toggleSearchSelect($event, 'distType_'+plat)"
                                                            class="select-trigger-button select-trigger-button-compact relative search-select-container">
                                                            <span
                                                                :class="masterForm.Distribution_Meta[plat].type ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ masterForm.Distribution_Meta[plat].type || 'Pilih Type' }}</span>
                                                            <i class="fa-solid fa-chevron-down text-[9px] text-slate-300"></i>
                                                            <transition name="fade">
                                                                <div v-if="searchSelectOpen === 'distType_'+plat"
                                                                    :style="popoverStyle"
                                                                    class="bg-white border border-slate-100 rounded-xl overflow-hidden p-1 animate-fadeIn shadow-2xl">
                                                                    <div v-for="t in ['Regular','Colab','Ad']" :key="t"
                                                                        @click.stop="masterForm.Distribution_Meta[plat].type = t; searchSelectOpen = null"
                                                                        :class="['px-3 py-2 text-[11px] rounded-lg cursor-pointer transition-all', masterForm.Distribution_Meta[plat].type === t ? 'popover-option-active' : '']">
                                                                        {{ t }} </div>
                                                                </div>
                                                            </transition>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label
                                                            class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Tanggal
                                                            Publish</label>
                                                        <button type="button" @click="openCalendar($event, 'published', plat)"
                                                            class="date-trigger-button date-trigger-button-compact">
                                                            <i class="fa-solid fa-calendar-days text-[10px] text-slate-400"></i>
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
                                        <label
                                            class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Skrip</label>
                                        <div
                                            class="flex rounded-xl overflow-hidden border border-slate-100 text-[10px] font-bold">
                                            <button type="button" @click="masterForm.Skrip = ''"
                                                :class="['px-3 py-1.5 transition-all', masterForm.Skrip !== 'Tidak' ? 'bg-ppp-accent text-white' : 'bg-slate-50 text-slate-400 hover:bg-slate-100']">Ya</button>
                                            <button type="button" @click="masterForm.Skrip = 'Tidak'"
                                                :class="['px-3 py-1.5 transition-all', masterForm.Skrip === 'Tidak' ? 'bg-slate-200 text-slate-600' : 'bg-slate-50 text-slate-400 hover:bg-slate-100']">Tidak</button>
                                        </div>
                                    </div>
                                    <transition name="fade">
                                        <textarea v-if="masterForm.Skrip !== 'Tidak'" v-model="masterForm.Skrip" rows="4"
                                            placeholder="Isi skrip konten..."
                                            class="form-input resize-none custom-scrollbar"></textarea>
                                    </transition>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label
                                            class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Caption</label>
                                        <div
                                            class="flex rounded-xl overflow-hidden border border-slate-100 text-[10px] font-bold">
                                            <button type="button" @click="masterForm.Caption = ''"
                                                :class="['px-3 py-1.5 transition-all', masterForm.Caption !== 'Tidak' ? 'bg-ppp-accent text-white' : 'bg-slate-50 text-slate-400 hover:bg-slate-100']">Ya</button>
                                            <button type="button" @click="masterForm.Caption = 'Tidak'"
                                                :class="['px-3 py-1.5 transition-all', masterForm.Caption === 'Tidak' ? 'bg-slate-200 text-slate-600' : 'bg-slate-50 text-slate-400 hover:bg-slate-100']">Tidak</button>
                                        </div>
                                    </div>
                                    <transition name="fade">
                                        <textarea v-if="masterForm.Caption !== 'Tidak'" v-model="masterForm.Caption" rows="4"
                                            placeholder="Caption untuk posting..."
                                            class="form-input resize-none custom-scrollbar"></textarea>
                                    </transition>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <!-- Sticky Footer -->
                <div class="modal-footer-bar modal-footer-actions">
                    <button @click="modalOpen = false" class="modal-secondary-button flex-1">Batal</button>
                    <button @click="saveMasterPlan" :disabled="submitting" class="modal-primary-button flex-1">
                        <i v-if="submitting" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                        <i v-else class="fa-solid fa-floppy-disk text-xs"></i>
                        {{ submitting ? 'Menyimpan...' : (modalType === 'create' ? 'Simpan' : 'Update') }}
                    </button>
                </div>
            </div>
        </div>
    </transition>

    <teleport to="body">
        <transition name="fade">
            <div v-if="calendarOpen" class="fixed inset-0 z-[5000] flex items-center justify-center p-4 overlay-motion-dialog">
                <div @click="calendarOpen = false" class="absolute inset-0 bg-slate-900/20 backdrop-blur-[2px] overlay-backdrop">
                </div>
                <div
                    class="w-full modal-width-compact bg-white radius-dialog border border-slate-200 overflow-hidden animate-fadeIn z-[5001] overlay-dialog-surface">
                    <div class="p-5 bg-blue-600 text-white">
                        <div class="text-[10px] uppercase tracking-widest opacity-80 mb-1">{{ calendarMode === 'filter' ? 'Pilih Range Tanggal' : 'Pilih Tanggal' }}</div>
                        <div class="text-xl font-bold">{{ formatFullDate(calendarTargetDate) }}</div>
                    </div>
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-4 px-2">
                            <button @click="changeMonth(-1)" aria-label="Bulan sebelumnya"
                                class="icon-utility-button icon-utility-round"><i
                                    class="fa-solid fa-chevron-left text-[10px]"></i></button>
                            <div class="text-[11px] font-bold text-slate-800 uppercase tracking-widest">{{ monthNames[currentDateView.getMonth()] }} {{ currentDateView.getFullYear() }}</div>
                            <button @click="changeMonth(1)" aria-label="Bulan berikutnya"
                                class="icon-utility-button icon-utility-round"><i
                                    class="fa-solid fa-chevron-right text-[10px]"></i></button>
                        </div>
                        <div class="grid grid-cols-7 gap-1 text-center mb-2">
                            <div v-for="day in ['S', 'S', 'R', 'K', 'J', 'S', 'M']" :key="day"
                                class="text-[9px] font-bold text-slate-300 py-1">{{ day }}</div>
                        </div>
                        <div class="grid grid-cols-7 gap-1 text-center">
                            <div v-for="n in calendarEmptyDays" :key="'empty-'+n" class="py-2"></div>
                            <div v-for="day in calendarDaysInMonth" :key="day" @click="selectDate(day)" @mouseenter="() => {
                                            const year = currentDateView.getFullYear();
                                            const month = currentDateView.getMonth();
                                            hoveredDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                                        }" @mouseleave="hoveredDate = ''"
                                :class="['calendar-day-button', isStartDate(day) ? 'bg-emerald-500 text-white ring-2 ring-emerald-300' : (isEndDate(day) ? 'bg-orange-500 text-white ring-2 ring-orange-300' : ''), !isStartDate(day) && isSelectedDate(day) ? 'bg-blue-600 text-white' : '', isInRange(day) ? 'bg-blue-50 text-blue-600' : '', !isStartDate(day) && !isEndDate(day) && !isSelectedDate(day) && !isInRange(day) ? 'text-slate-600' : '', isToday(day) && !isStartDate(day) && !isEndDate(day) && !isSelectedDate(day) && !isInRange(day) ? 'text-blue-600' : '']">
                                {{ day }}
                                <div v-if="isToday(day)"
                                    :class="['w-1 h-1 rounded-full absolute bottom-1.5', (isStartDate(day) || isEndDate(day) || isSelectedDate(day)) ? 'bg-white' : 'bg-blue-600']">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 pt-0 flex items-center justify-between gap-2">
                        <button @click="resetCalendar"
                            class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest hover:text-rose-500 transition-colors">Reset</button>
                        <button @click="calendarOpen = false"
                            class="px-4 py-2 text-[10px] font-bold text-blue-600 uppercase tracking-widest">Selesai</button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>

    <!-- Custom Confirmation Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="confirmModal.open" class="fixed inset-0 z-[3000] flex items-center justify-center p-4 overlay-motion-dialog">
                <div @click="confirmModal.open = false" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm overlay-backdrop">
                </div>
                <div
                    class="modal-width-compact radius-dialog modal-dialog-surface overlay-dialog-surface">
                    <div class="p-8 text-center">
                        <div
                            :class="['w-16 h-16 rounded-3xl flex items-center justify-center mx-auto mb-5', confirmModal.type === 'danger' ? 'bg-rose-50 text-rose-500' : 'bg-blue-50 text-blue-500']">
                            <i
                                :class="['fa-solid text-2xl', confirmModal.type === 'danger' ? 'fa-trash-can' : 'fa-circle-info']"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mb-2">{{ confirmModal.title }}</h3>
                        <p class="text-[12px] text-slate-500 leading-relaxed">{{ confirmModal.message }}</p>
                    </div>
                    <div class="flex gap-3 p-4 border-t border-slate-100 bg-slate-50/80">
                        <button @click="confirmModal.open = false" class="modal-secondary-button flex-1">Batal</button>
                        <button @click="confirmModal.onConfirm(); confirmModal.open = false"
                            :class="['modal-primary-button flex-1', confirmModal.type === 'danger' ? 'modal-primary-button--danger' : 'modal-primary-button--info']">Ya,
                            Lanjutkan</button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>

    <!-- Nama Stock Form Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="showNamaStockFormModal"
                class="fixed inset-0 z-[2500] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                <div @click="closeNamaStockFormModal" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm overlay-backdrop">
                </div>
                <div
                    class="mobile-sheet modal-width-form relative w-full bg-white radius-sheet border border-slate-200 overflow-hidden animate-fadeIn flex flex-col max-h-[90dvh]">
                    <div
                        class="modal-header-bar modal-header-bar-sticky radius-sheet-top z-[2010]">
                        <div class="modal-header-copy">
                            <div
                                class="modal-header-icon bg-slate-100 text-slate-600 border border-slate-200">
                                <i class="fa-solid fa-boxes-stacked text-[13px]"></i>
                            </div>
                            <div>
                                <div class="type-title text-slate-900">{{ namaStockFormMode === 'create' ? 'Tambah Nama Stock' : 'Edit Nama Stock' }}</div>
                                <div class="type-meta text-slate-400 uppercase tracking-widest mt-0.5">Master Stock</div>
                            </div>
                        </div>
                        <button @click="closeNamaStockFormModal" class="icon-utility-button icon-utility-round">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>
                    <form @submit.prevent="submitNamaStockForm" class="flex flex-1 flex-col min-h-0">
                        <div class="flex-1 overflow-y-auto p-6 space-y-4">
                        <div class="space-y-1 relative search-select-container">
                            <label
                                class="type-meta font-semibold text-slate-500 uppercase tracking-wide">Kategori</label>
                            <div @click="toggleSearchSelect($event, 'nama_stock_kategori')"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white cursor-pointer flex items-center justify-between hover:bg-slate-50 transition-all">
                                <span
                                    :class="namaStockForm.KATEGORI ? 'text-slate-700 font-semibold' : 'text-slate-400'">{{ namaStockForm.KATEGORI || 'Pilih Kategori' }}</span>
                                <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                            </div>
                            <transition name="fade">
                                <div v-if="searchSelectOpen === 'nama_stock_kategori'" :style="popoverStyle"
                                    class="search-select-popover">
                                    <div class="relative mb-2">
                                        <i
                                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                        <input v-model="searchSelectQuery" type="text" placeholder="Cari kategori..."
                                            class="form-input-popover" @click.stop />
                                    </div>
                                    <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                        <div v-if="namaStockKategoriOptions.length === 0"
                                            class="px-3 py-2 text-[11px] text-slate-400 italic">Belum ada opsi kategori
                                            di setting</div>
                                        <div v-for="opt in namaStockKategoriOptions.filter(o => !searchSelectQuery || String(o || '').toLowerCase().includes(String(searchSelectQuery || '').toLowerCase()))"
                                            :key="`ns-kat-${opt}`"
                                            @click="namaStockForm.KATEGORI = opt; namaStockForm.BRAND = ''; namaStockForm.SERI = ''; searchSelectOpen = null"
                                            :class="['popover-option', namaStockForm.KATEGORI === opt ? 'popover-option-active' : '']">
                                            {{ opt }}
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </div>
                        <div class="space-y-1 relative search-select-container">
                            <label
                                class="text-[10px] font-semibold text-slate-500 uppercase tracking-wide">Brand</label>
                            <div @click="toggleSearchSelect($event, 'nama_stock_brand')"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white cursor-pointer flex items-center justify-between hover:bg-slate-50 transition-all">
                                <span
                                    :class="namaStockForm.BRAND ? 'text-slate-700 font-semibold' : 'text-slate-400'">{{ namaStockForm.BRAND || 'Pilih Brand' }}</span>
                                <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                            </div>
                            <transition name="fade">
                                <div v-if="searchSelectOpen === 'nama_stock_brand'" :style="popoverStyle"
                                    class="search-select-popover">
                                    <div class="relative mb-2">
                                        <i
                                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                        <input v-model="searchSelectQuery" type="text" placeholder="Cari brand..."
                                            class="form-input-popover" @click.stop />
                                    </div>
                                    <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                        <div v-if="namaStockBrandOptions.length === 0"
                                            class="px-3 py-2 text-[11px] text-slate-400 italic">Pilih kategori dulu atau
                                            lengkapi setting brand</div>
                                        <div v-for="opt in namaStockBrandOptions.filter(o => !searchSelectQuery || String(o || '').toLowerCase().includes(String(searchSelectQuery || '').toLowerCase()))"
                                            :key="`ns-brand-${opt}`"
                                            @click="namaStockForm.BRAND = opt; namaStockForm.SERI = ''; searchSelectOpen = null"
                                            :class="['popover-option', namaStockForm.BRAND === opt ? 'popover-option-active' : '']">
                                            {{ opt }}
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-semibold text-slate-500 uppercase tracking-wide">Seri</label>
                            <input v-model.trim="namaStockForm.SERI" type="text" placeholder="Ketik seri"
                                class="form-input-compact-white" />
                        </div>
                        </div>
                        <div class="modal-footer-bar modal-footer-actions">
                            <button type="button" @click="closeNamaStockFormModal"
                                class="modal-secondary-button">Batal</button>
                            <button type="submit"
                                class="modal-primary-button">{{ namaStockFormMode === 'create' ? 'Tambah' : 'Simpan' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </teleport>

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
                                :class="['modal-header-icon text-white', storyModalType === 'create' ? 'bg-emerald-500' : 'bg-rose-500']">
                                <i
                                    :class="['fa-solid text-[12px]', storyModalType === 'create' ? 'fa-plus' : 'fa-pen-to-square']"></i>
                            </div>
                            <div>
                                <div class="type-title text-slate-900">{{ storyModalType === 'create' ? 'Tambah Jadwal Story' : 'Edit Jadwal Story' }}</div>
                            </div>
                        </div>
                        <button @click="storyModalOpen = false" aria-label="Tutup modal"
                            class="icon-utility-button icon-utility-round">
                            <i class="fa-solid fa-xmark text-[12px]"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-5 flex-1 overflow-y-auto custom-scrollbar">
                        <div class="bg-slate-50 border border-slate-100 p-4 rounded-2xl">
                            <label
                                class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3 text-center">Kelompok
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
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Tanggal</label>
                                <div @click="openCalendar($event, 'form', '', 'story')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="storyForm.Tanggal ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ storyForm.Tanggal ? formatFullDate(storyForm.Tanggal) : 'Pilih Tanggal' }}</span>
                                    <i class="fa-solid fa-calendar-day text-[10px] text-slate-300"></i>
                                </div>
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Waktu
                                    Tayang (Jam) <span class="text-red-500">*</span></label>
                                <input v-model="storyForm.Jam" type="time" class="form-input" />
                            </div>
                        </div>
                        <div>
                            <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Story
                                (Konten) <span class="text-red-500">*</span></label>
                            <input v-model="storyForm.Story" type="text" placeholder="Ketik ide konten..."
                                class="form-input uppercase" />
                        </div>
                        <div>
                            <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Internal
                                Note (Opsional)</label>
                            <textarea v-model="storyForm.Catatan" rows="3" placeholder="Catatan singkat..."
                                class="form-input custom-scrollbar"></textarea>
                        </div>
                        <div>
                            <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Link
                                Reference (Opsional)</label>
                            <input v-model="storyForm.Link" type="url" placeholder="https://..." class="form-input" />
                        </div>
                        <div class="relative search-select-container">
                            <label
                                class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Status</label>
                            <div @click="toggleSearchSelect($event, 'storyStatus')"
                                class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                <span :class="storyForm.Status ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ storyForm.Status || 'Pilih Status' }}</span>
                                <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                            </div>
                            <transition name="fade">
                                <div v-if="searchSelectOpen === 'storyStatus'" :style="popoverStyle"
                                    class="search-select-popover">
                                    <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                        <div v-for="opt in (statusOptions.length ? statusOptions : ['DRAFT','TERJADWAL','PUBLISH'])"
                                            :key="opt" @click="storyForm.Status = opt; searchSelectOpen = null"
                                            :class="['popover-option', storyForm.Status === opt ? 'bg-rose-500 text-white font-semibold' : 'text-slate-600 hover:bg-slate-50']">
                                            {{ opt }}</div>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>

                    <div class="modal-footer-bar modal-footer-actions">
                        <button @click="storyModalOpen = false" class="modal-secondary-button">Batal</button>
                        <button @click="saveStory" :disabled="submitting"
                            class="modal-primary-button modal-primary-button--danger">
                            <i v-if="submitting" class="fa-solid fa-spinner fa-spin"></i>
                            Simpan Story
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>

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
                                class="modal-header-icon bg-cyan-50 text-cyan-500 border border-cyan-100">
                                <i class="fa-solid fa-cart-shopping"></i>
                            </div>
                            <div>
                                <div class="type-title text-slate-900">{{ csModalType === 'create' ? 'Tambah' : 'Edit' }} Order Online</div>
                                <div class="type-meta text-slate-400 uppercase tracking-widest mt-0.5">Customer
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
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Tanggal</label>
                                <div @click="openCalendar($event, 'form', '', 'orderanOnline1')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="orderanOnlineForm['TANGGAL'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ orderanOnlineForm['TANGGAL'] ? formatFullDate(orderanOnlineForm['TANGGAL']) : 'Pilih Tanggal' }}</span>
                                    <i class="fa-solid fa-calendar-day text-[10px] text-slate-300"></i>
                                </div>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Ecommerce</label>
                                <button type="button" @click="toggleSearchSelect($event, 'ecommerce')"
                                    :aria-expanded="searchSelectOpen === 'ecommerce' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="orderanOnlineForm['ECOMMERCE'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ orderanOnlineForm['ECOMMERCE'] || 'Pilih Ecommerce' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'ecommerce'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text"
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
                                                class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Handle</label>
                                <button type="button" @click="toggleSearchSelect($event, 'orderan_handle')"
                                    :aria-expanded="searchSelectOpen === 'orderan_handle' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="orderanOnlineForm['HANDLE'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ orderanOnlineForm['HANDLE'] || 'Pilih Handle' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'orderan_handle'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text"
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
                                                class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Nama
                                    Customer</label>
                                <input v-model="orderanOnlineForm['NAMA']" type="text" class="form-input" />
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">No
                                    HP</label>
                                <input v-model="orderanOnlineForm['HP']" type="text" class="form-input" />
                            </div>
                            <div>
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Username</label>
                                <input v-model="orderanOnlineForm['USERNAME']" type="text" class="form-input" />
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">No
                                    Pesanan</label>
                                <input v-model="orderanOnlineForm['NO PESANAN']" type="text" class="form-input" />
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Pengiriman</label>
                                <button type="button" @click="toggleSearchSelect($event, 'pengiriman')"
                                    :aria-expanded="searchSelectOpen === 'pengiriman' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="orderanOnlineForm['PENGIRIMAN'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ orderanOnlineForm['PENGIRIMAN'] || 'Pilih Pengiriman' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'pengiriman'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text"
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
                                                class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">No
                                    Resi</label>
                                <input v-model="orderanOnlineForm['NO RESI']" type="text" class="form-input" />
                            </div>
                            <div class="relative search-select-container">
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Type
                                    Unit / Produk</label>
                                <button type="button" @click="toggleSearchSelect($event, 'orderan_type_unit')"
                                    :aria-expanded="searchSelectOpen === 'orderan_type_unit' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="orderanOnlineForm['TYPE UNIT'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ orderanOnlineForm['TYPE UNIT'] || 'Pilih Type Unit' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'orderan_type_unit'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text"
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
                                                class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">IMEI
                                    / SN</label>
                                <input v-model="orderanOnlineForm['IMEI/SN']" type="text" class="form-input" />
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">No
                                    Nota</label>
                                <input v-model="orderanOnlineForm['NO NOTA']" type="text" class="form-input" />
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Harga
                                    Online</label>
                                <input v-model.number="orderanOnlineForm['HARGA ONLINE']" type="number"
                                    class="form-input" />
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Nominal
                                    Cair</label>
                                <input v-model.number="orderanOnlineForm['NOMINAL CAIR']" type="number"
                                    class="form-input" />
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Admin
                                    %</label>
                                <input v-model="orderanOnlineForm['ADMIN %']" type="text" placeholder="2% / 3%"
                                    class="form-input" />
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Status</label>
                                <button type="button" @click="toggleSearchSelect($event, 'orderan_status')"
                                    :aria-expanded="searchSelectOpen === 'orderan_status' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="orderanOnlineForm['STATUS'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ orderanOnlineForm['STATUS'] || 'Pilih Status' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
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
                        <button @click="orderanOnlineModalOpen = false" class="modal-secondary-button">Batal</button>
                        <button @click="saveOrderanOnline" :disabled="submitting" class="modal-primary-button">{{ submitting ? 'Menyimpan...' : 'Simpan' }}</button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>

    <!-- Unit Ditanya Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="unitDitanyaModalOpen"
                class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                <div @click="unitDitanyaModalOpen = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop">
                </div>
                <div
                    class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface">
                    <div
                        class="modal-header-bar modal-header-bar-sticky radius-sheet-top z-[2010]">
                        <div class="modal-header-copy">
                            <div
                                class="modal-header-icon bg-amber-50 text-amber-500 border border-amber-100">
                                <i class="fa-solid fa-circle-question"></i>
                            </div>
                            <div>
                                <div class="type-title text-slate-900">{{ csModalType === 'create' ? 'Tambah' : 'Edit' }} Unit Ditanya</div>
                                <div class="type-meta text-slate-400 uppercase tracking-widest mt-0.5">Customer
                                    Service</div>
                            </div>
                        </div>
                        <button @click="unitDitanyaModalOpen = false" aria-label="Tutup modal"
                            class="icon-utility-button icon-utility-round"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="p-6 overflow-y-auto space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Tanggal</label>
                                <div @click="openCalendar($event, 'form', '', 'unitDitanya1')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="unitDitanyaForm['TANGGAL'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ unitDitanyaForm['TANGGAL'] ? formatFullDate(unitDitanyaForm['TANGGAL']) : 'Pilih Tanggal' }}</span>
                                    <i class="fa-solid fa-calendar-day text-[10px] text-slate-300"></i>
                                </div>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Kategori</label>
                                <button type="button" @click="toggleSearchSelect($event, 'unit_kategori')"
                                    :aria-expanded="searchSelectOpen === 'unit_kategori' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="unitDitanyaForm['KATEGORI'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ unitDitanyaForm['KATEGORI'] || 'Pilih Kategori' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'unit_kategori'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text"
                                                placeholder="Cari kategori..." class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unitKategoriOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="unitDitanyaForm['KATEGORI'] = opt; unitDitanyaForm['BRAND'] = ''; unitDitanyaForm['SERI'] = ''; searchSelectOpen = null"
                                                :class="['popover-option', unitDitanyaForm['KATEGORI'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="unitKategoriOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Brand</label>
                                <button type="button" @click="toggleSearchSelect($event, 'unit_brand')"
                                    :aria-expanded="searchSelectOpen === 'unit_brand' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="unitDitanyaForm['BRAND'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ unitDitanyaForm['BRAND'] || 'Pilih Brand' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'unit_brand'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text" placeholder="Cari brand..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unitBrandOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="unitDitanyaForm['BRAND'] = opt; unitDitanyaForm['SERI'] = ''; searchSelectOpen = null"
                                                :class="['popover-option', unitDitanyaForm['BRAND'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="unitBrandOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Seri</label>
                                <button type="button" @click="toggleSearchSelect($event, 'unit_seri')"
                                    :aria-expanded="searchSelectOpen === 'unit_seri' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="unitDitanyaForm['SERI'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ unitDitanyaForm['SERI'] || 'Pilih Seri' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'unit_seri'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text" placeholder="Cari seri..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-if="nsSeriOptions.length === 0"
                                                class="px-3 py-2 text-[11px] text-slate-400 italic">Pilih Kategori &
                                                Brand dulu</div>
                                            <div v-for="opt in nsSeriOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="unitDitanyaForm['SERI'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', unitDitanyaForm['SERI'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="nsSeriOptions.length > 0 && nsSeriOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">RAM</label>
                                <button type="button" @click="toggleSearchSelect($event, 'unit_ram')"
                                    :aria-expanded="searchSelectOpen === 'unit_ram' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="unitDitanyaForm['RAM'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ unitDitanyaForm['RAM'] || 'Pilih RAM' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'unit_ram'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text" placeholder="Cari RAM..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unitRAMOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="unitDitanyaForm['RAM'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', unitDitanyaForm['RAM'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="unitRAMOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Internal</label>
                                <button type="button" @click="toggleSearchSelect($event, 'unit_internal')"
                                    :aria-expanded="searchSelectOpen === 'unit_internal' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="unitDitanyaForm['INTERNAL'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ unitDitanyaForm['INTERNAL'] || 'Pilih Internal' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'unit_internal'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text"
                                                placeholder="Cari internal..." class="form-input-popover"
                                                @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unitInternalOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="unitDitanyaForm['INTERNAL'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', unitDitanyaForm['INTERNAL'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="unitInternalOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Size</label>
                                <button type="button" @click="toggleSearchSelect($event, 'unit_size')"
                                    :aria-expanded="searchSelectOpen === 'unit_size' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="unitDitanyaForm['SIZE'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ unitDitanyaForm['SIZE'] || 'Pilih Size' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'unit_size'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text"
                                                placeholder="Cari size..." class="form-input-popover"
                                                @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unitSizeOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="unitDitanyaForm['SIZE'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', unitDitanyaForm['SIZE'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="unitSizeOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div>
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Warna</label>
                                <input v-model="unitDitanyaForm['WARNA']" type="text" class="form-input" />
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Kondisi</label>
                                <button type="button" @click="toggleSearchSelect($event, 'kondisi')"
                                    :aria-expanded="searchSelectOpen === 'kondisi' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="unitDitanyaForm['KONDISI'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ unitDitanyaForm['KONDISI'] || 'Pilih Kondisi' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'kondisi'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unitKondisiOptions" :key="opt"
                                                @click="unitDitanyaForm['KONDISI'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', unitDitanyaForm['KONDISI'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Tipe</label>
                                <button type="button" @click="toggleSearchSelect($event, 'unit_tipe')"
                                    :aria-expanded="searchSelectOpen === 'unit_tipe' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="unitDitanyaForm['TIPE'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ unitDitanyaForm['TIPE'] || 'Pilih Tipe' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'unit_tipe'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text" placeholder="Cari tipe..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in sharedUnitTypeOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="unitDitanyaForm['TIPE'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', unitDitanyaForm['TIPE'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="sharedUnitTypeOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Jumlah
                                    Ditanya</label>
                                <input v-model.number="unitDitanyaForm['DITANYA']" type="number" min="1"
                                    class="form-input" />
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Available</label>
                                <button type="button" @click="toggleSearchSelect($event, 'available')"
                                    :aria-expanded="searchSelectOpen === 'available' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="unitDitanyaForm['AVAILABLE'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ unitDitanyaForm['AVAILABLE'] || 'Pilih Available' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'available'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unitAvailableOptions" :key="opt"
                                                @click="unitDitanyaForm['AVAILABLE'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', unitDitanyaForm['AVAILABLE'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer-bar modal-footer-actions">
                        <button @click="unitDitanyaModalOpen = false" class="modal-secondary-button">Batal</button>
                        <button @click="saveUnitDitanya" :disabled="submitting" class="modal-primary-button">{{ submitting ? 'Menyimpan...' : 'Simpan' }}</button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>

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
                                class="modal-header-icon bg-rose-50 text-rose-500 border border-rose-100">
                                <i class="fa-solid fa-shield-heart"></i>
                            </div>
                            <div>
                                <div class="type-title text-slate-900">{{ csModalType === 'create' ? 'Tambah' : 'Edit' }} Claim Garansi</div>
                                <div class="type-meta text-slate-400 uppercase tracking-widest mt-0.5">Customer
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
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Nama
                                    Customer</label>
                                <input v-model="claimGaransiForm['NAMA_CUSTOMER']" type="text" class="form-input" />
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">No
                                    Service</label>
                                <input v-model="claimGaransiForm['NO_SERVICE']" type="text" class="form-input" />
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">No
                                    Transaksi</label>
                                <input v-model="claimGaransiForm['NO_TRANSAKSI']" type="text" class="form-input" />
                            </div>
                            <div class="relative search-select-container">
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Tanggal
                                    Masuk</label>
                                <div @click="openCalendar($event, 'form', '', 'claimGaransi1')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="claimGaransiForm['TANGGAL_MASUK'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ claimGaransiForm['TANGGAL_MASUK'] ? formatFullDate(claimGaransiForm['TANGGAL_MASUK']) : 'Pilih Tanggal' }}</span>
                                    <i class="fa-solid fa-calendar-day text-[10px] text-slate-300"></i>
                                </div>
                            </div>
                            <div class="relative search-select-container">
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Tanggal
                                    Estimasi</label>
                                <div @click="openCalendar($event, 'form', '', 'claimGaransi3')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="claimGaransiForm['TANGGAL_ESTIMASI'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ claimGaransiForm['TANGGAL_ESTIMASI'] ? formatFullDate(claimGaransiForm['TANGGAL_ESTIMASI']) : 'Pilih Tanggal' }}</span>
                                    <i class="fa-solid fa-calendar-day text-[10px] text-slate-300"></i>
                                </div>
                            </div>
                            <div class="relative search-select-container">
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Tanggal
                                    Diambil</label>
                                <div @click="openCalendar($event, 'form', '', 'claimGaransi2')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="claimGaransiForm['TANGGAL_DIAMBIL'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ claimGaransiForm['TANGGAL_DIAMBIL'] ? formatFullDate(claimGaransiForm['TANGGAL_DIAMBIL']) : 'Pilih Tanggal' }}</span>
                                    <i class="fa-solid fa-calendar-day text-[10px] text-slate-300"></i>
                                </div>
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">WA
                                    Customer</label>
                                <input v-model="claimGaransiForm['WA_CUSTOMER']" type="text" placeholder="08xxx"
                                    class="form-input" />
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">WA
                                    2 Customer</label>
                                <input v-model="claimGaransiForm['WA2_CUSTOMER']" type="text"
                                    placeholder="08xxx (opsional)" class="form-input" />
                            </div>
                            <div class="form-section-card">
                                <div class="form-section-title">Info Unit & Service</div>
                                <div class="form-section-copy">Identitas unit, status klaim, dan detail kerusakan.</div>
                            </div>
                            <div class="relative search-select-container">
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Tipe
                                    Unit</label>
                                <button type="button" @click="toggleSearchSelect($event, 'claim_tipe')"
                                    :aria-expanded="searchSelectOpen === 'claim_tipe' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="claimGaransiForm['TIPE'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ claimGaransiForm['TIPE'] || 'Pilih Tipe Unit' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'claim_tipe'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text" placeholder="Cari tipe..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in sharedUnitTypeOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="claimGaransiForm['TIPE'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', claimGaransiForm['TIPE'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="sharedUnitTypeOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div>
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">IMEI</label>
                                <input v-model="claimGaransiForm['IMEI']" type="text" class="form-input" />
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Seri</label>
                                <button type="button" @click="toggleSearchSelect($event, 'claim_seri')"
                                    :aria-expanded="searchSelectOpen === 'claim_seri' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="claimGaransiForm['SERI'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ claimGaransiForm['SERI'] || 'Pilih Seri' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'claim_seri'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text" placeholder="Cari seri..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in claimSeriOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="claimGaransiForm['SERI'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', claimGaransiForm['SERI'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="claimSeriOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Model</label>
                                <button type="button" @click="toggleSearchSelect($event, 'claim_model')"
                                    :aria-expanded="searchSelectOpen === 'claim_model' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="claimGaransiForm['MODEL'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ claimGaransiForm['MODEL'] || 'Pilih Model' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'claim_model'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text" placeholder="Cari model..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in sharedUnitTypeOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="claimGaransiForm['MODEL'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', claimGaransiForm['MODEL'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="sharedUnitTypeOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">HP
                                    Pinjaman</label>
                                <input v-model="claimGaransiForm['HP_PINJAMAN']" type="text" class="form-input" />
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">IMEI
                                    Pinjaman</label>
                                <input v-model="claimGaransiForm['IMEI_PINJAMAN']" type="text" class="form-input" />
                            </div>
                            <div class="relative search-select-container">
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Lokasi
                                    Klaim</label>
                                <button type="button" @click="toggleSearchSelect($event, 'lokasi_klaim')"
                                    :aria-expanded="searchSelectOpen === 'lokasi_klaim' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="claimGaransiForm['LOKASI_KLAIM'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ claimGaransiForm['LOKASI_KLAIM'] || 'Pilih Lokasi' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'lokasi_klaim'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text" placeholder="Cari lokasi..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in claimLokasiOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="claimGaransiForm['LOKASI_KLAIM'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', claimGaransiForm['LOKASI_KLAIM'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="claimLokasiOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Status</label>
                                <button type="button" @click="toggleSearchSelect($event, 'claim_status')"
                                    :aria-expanded="searchSelectOpen === 'claim_status' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="claimGaransiForm['STATUS'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ claimGaransiForm['STATUS'] || 'Pilih Status' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
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
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Garansi</label>
                                <button type="button" @click="toggleSearchSelect($event, 'claim_garansi')"
                                    :aria-expanded="searchSelectOpen === 'claim_garansi' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="claimGaransiForm['GARANSI'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ claimGaransiForm['GARANSI'] || 'Pilih Garansi' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
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
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Kerusakan</label>
                                <textarea v-model="claimGaransiForm['KERUSAKAN']" rows="3"
                                    class="form-input resize-none"></textarea>
                            </div>
                            <div class="col-span-2">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Keterangan</label>
                                <textarea v-model="claimGaransiForm['KETERANGAN']" rows="2"
                                    placeholder="Catatan tambahan..." class="form-input resize-none"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer-bar modal-footer-actions">
                        <button @click="claimGaransiModalOpen = false" class="modal-secondary-button">Batal</button>
                        <button @click="saveClaimGaransi" :disabled="submitting" class="modal-primary-button">{{ submitting ? 'Menyimpan...' : 'Simpan' }}</button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>

    <!-- Keep Barang Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="keepBarangModalOpen"
                class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                <div @click="keepBarangModalOpen = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop">
                </div>
                <div
                    class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface">
                    <div
                        class="modal-header-bar modal-header-bar-sticky radius-sheet-top z-[2010]">
                        <div class="modal-header-copy">
                            <div
                                class="modal-header-icon bg-indigo-50 text-indigo-500 border border-indigo-100">
                                <i class="fa-solid fa-box-archive"></i>
                            </div>
                            <div>
                                <div class="type-title text-slate-900">{{ keepBarangModalType === 'create' ? 'Tambah' : 'Edit' }} Barang Ditahan</div>
                                <div class="type-meta text-slate-400 uppercase tracking-widest mt-0.5">Customer
                                    Service</div>
                            </div>
                        </div>
                        <button @click="keepBarangModalOpen = false" aria-label="Tutup modal"
                            class="icon-utility-button icon-utility-round"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="p-6 overflow-y-auto space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Tanggal
                                    Keep</label>
                                <button type="button" @click="openCalendar($event, 'form', '', 'keepBarangTanggalKeep')"
                                    class="date-trigger-button toolbar-trigger-field">
                                    <i class="fa-solid fa-calendar-days text-[10px] text-slate-400"></i>
                                    <span
                                        :class="keepBarangForm['TANGGAL_KEEP'] ? 'text-slate-700 font-medium' : 'text-slate-400'">
                                        {{ keepBarangForm['TANGGAL_KEEP'] ? formatShortDate(keepBarangForm['TANGGAL_KEEP']) : 'Pilih tanggal keep' }}
                                    </span>
                                </button>
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Rencana
                                    Pengambilan</label>
                                <button type="button"
                                    @click="openCalendar($event, 'form', '', 'keepBarangRencanaAmbil')"
                                    class="date-trigger-button toolbar-trigger-field">
                                    <i class="fa-solid fa-calendar-days text-[10px] text-slate-400"></i>
                                    <span
                                        :class="keepBarangForm['RENCANA_PENGAMBILAN'] ? 'text-slate-700 font-medium' : 'text-slate-400'">
                                        {{ keepBarangForm['RENCANA_PENGAMBILAN'] ? formatShortDate(keepBarangForm['RENCANA_PENGAMBILAN']) : 'Pilih rencana pengambilan' }}
                                    </span>
                                </button>
                            </div>
                            <div class="col-span-2">
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Nama
                                    Customer</label>
                                <input v-model="keepBarangForm['NAMA']" type="text" class="form-input" />
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">No
                                    HP</label>
                                <input v-model="keepBarangForm['NOMOR_HP']" type="text" placeholder="08xxx"
                                    class="form-input" />
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">No
                                    HP 2</label>
                                <input v-model="keepBarangForm['NOMOR_HP_2']" type="text" placeholder="08xxx (opsional)"
                                    class="form-input" />
                            </div>
                            <div class="relative search-select-container">
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Type
                                    HP</label>
                                <button type="button" @click="toggleSearchSelect($event, 'keep_type_hp')"
                                    :aria-expanded="searchSelectOpen === 'keep_type_hp' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="keepBarangForm['TYPE_HP'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ keepBarangForm['TYPE_HP'] || 'Pilih Type HP' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'keep_type_hp'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text" placeholder="Cari type HP..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in keepBarangTypeHpOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="keepBarangForm['TYPE_HP'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', keepBarangForm['TYPE_HP'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="keepBarangTypeHpOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                Belum ada opsi Type HP
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div>
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">IMEI</label>
                                <input v-model="keepBarangForm['IMEI_FULL']" type="text" class="form-input" />
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">DP
                                    (Uang Muka)</label>
                                <input v-model.number="keepBarangForm['DP_UANG_MUKA']" type="number" min="0"
                                    class="form-input" />
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Harga
                                    Jual</label>
                                <input v-model.number="keepBarangForm['HARGA_JUAL']" type="number" min="0"
                                    class="form-input" />
                            </div>
                            <div class="relative search-select-container">
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Handle
                                    By</label>
                                <button type="button" @click="toggleSearchSelect($event, 'keep_handle_by')"
                                    :aria-expanded="searchSelectOpen === 'keep_handle_by' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="keepBarangForm['HANDLE_BY'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ keepBarangForm['HANDLE_BY'] || 'Pilih Handle By' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'keep_handle_by'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text"
                                                placeholder="Cari handle by..." class="form-input-popover"
                                                @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in keepBarangHandleByOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="keepBarangForm['HANDLE_BY'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', keepBarangForm['HANDLE_BY'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="keepBarangHandleByOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Kasir
                                    By</label>
                                <button type="button" @click="toggleSearchSelect($event, 'keep_kasir_by')"
                                    :aria-expanded="searchSelectOpen === 'keep_kasir_by' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="keepBarangForm['KASIR_BY'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ keepBarangForm['KASIR_BY'] || 'Pilih Kasir By' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'keep_kasir_by'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text"
                                                placeholder="Cari kasir by..." class="form-input-popover"
                                                @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in keepBarangKasirByOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="keepBarangForm['KASIR_BY'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', keepBarangForm['KASIR_BY'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="keepBarangKasirByOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Team
                                    Gudang</label>
                                <button type="button" @click="toggleSearchSelect($event, 'keep_team_gudang')"
                                    :aria-expanded="searchSelectOpen === 'keep_team_gudang' ? 'true' : 'false'"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="keepBarangForm['TEAM_GUDANG'] ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ keepBarangForm['TEAM_GUDANG'] || 'Pilih Team Gudang' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </button>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'keep_team_gudang'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text"
                                                placeholder="Cari team gudang..." class="form-input-popover"
                                                @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in keepBarangTeamGudangOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase()))"
                                                :key="opt"
                                                @click="keepBarangForm['TEAM_GUDANG'] = opt; searchSelectOpen = null"
                                                :class="['popover-option', keepBarangForm['TEAM_GUDANG'] === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                            <div v-if="keepBarangTeamGudangOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"
                                                class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                Tidak ditemukan
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div>
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Deadline
                                    Gudang</label>
                                <button type="button"
                                    @click="openCalendar($event, 'form', '', 'keepBarangDeadlineGudang')"
                                    class="date-trigger-button toolbar-trigger-field">
                                    <i class="fa-solid fa-calendar-days text-[10px] text-slate-400"></i>
                                    <span
                                        :class="keepBarangForm['DEADLINE_TEAM_GUDANG'] ? 'text-slate-700 font-medium' : 'text-slate-400'">
                                        {{ keepBarangForm['DEADLINE_TEAM_GUDANG'] ? formatShortDate(keepBarangForm['DEADLINE_TEAM_GUDANG']) : 'Pilih deadline gudang' }}
                                    </span>
                                </button>
                            </div>
                            <div class="col-span-2">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Status</label>
                                <div class="relative search-select-container">
                                    <button type="button" @click="toggleSearchSelect($event, 'keep_form_status')"
                                        class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                        <span class="truncate">{{ keepBarangForm['STATUS'] || 'Pilih Status' }}</span>
                                        <i class="fa-solid fa-chevron-down text-[9px] text-slate-400"></i>
                                    </button>
                                    <div v-if="searchSelectOpen === 'keep_form_status'" :style="popoverStyle"
                                        class="search-select-popover search-select-popover--compact max-h-60 overflow-y-auto">
                                        <div v-for="status in keepBarangStatusOptions" :key="status"
                                            @click="keepBarangForm['STATUS'] = status; searchSelectOpen = null"
                                            class="popover-option">
                                            {{ status }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer-bar modal-footer-actions">
                        <button @click="keepBarangModalOpen = false" class="modal-secondary-button">Batal</button>
                        <button @click="saveKeepBarang" :disabled="submitting" class="modal-primary-button">{{ submitting ? 'Menyimpan...' : 'Simpan' }}</button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>

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
                                class="modal-header-icon bg-orange-50 text-orange-500 border border-orange-100">
                                <i class="fa-solid fa-bullhorn"></i>
                            </div>
                            <div>
                                <div class="type-title text-slate-900">{{ promoModalType === 'create' ? 'Tambah Program' : 'Edit Program' }}</div>
                                <div class="type-meta text-slate-400 uppercase tracking-widest mt-0.5">Program &
                                    Promo</div>
                            </div>
                        </div>
                        <button @click="promoModalOpen = false" aria-label="Tutup modal"
                            class="icon-utility-button icon-utility-round"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="p-6 overflow-y-auto flex-1 space-y-4">
                        <!-- Kategori -->
                        <div>
                            <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Kategori
                                Promo</label>
                            <div class="relative search-select-container">
                                <div @click="toggleSearchSelect($event, 'promoKategori')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="promoForm.Kategori ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ promoForm.Kategori || 'Pilih Kategori' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
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
                            <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Nama
                                Program <span class="text-red-500">*</span></label>
                            <input v-model="promoForm.Program" type="text" placeholder="Promo Cashback..."
                                class="form-input" />
                        </div>
                        <!-- Varian -->
                        <div>
                            <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Varian
                                / Unit</label>
                            <input v-model="promoForm.Warna" type="text" placeholder="Semua Tipe / Galaxy S25..."
                                class="form-input" />
                        </div>
                        <!-- Harga -->
                        <div>
                            <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Nominal
                                Potongan (Rp)</label>
                            <input v-model.number="promoForm.Harga" type="number" min="0"
                                class="form-input text-right" />
                        </div>
                        <!-- Periode -->
                        <div class="surface-panel-soft space-y-3">
                            <label
                                class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Periode
                                Berlaku</label>
                            <div class="grid grid-cols-2 gap-2">
                                <!-- Preset dropdown -->
                                <div class="relative search-select-container">
                                    <div @click="toggleSearchSelect($event, 'promoPeriode')"
                                        class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                        <span
                                            :class="promoPeriodePreset ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ promoPeriodePreset === 'stock' ? 'Selama Stok Ada' : promoPeriodePreset === 'custom' ? 'Tanggal Custom' : 'Pilih Preset' }}</span>
                                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
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
                                    <div @click="openCalendar($event, 'form', '', 'promoDate1')"
                                        class="select-trigger-button select-trigger-button-form-tight">
                                        <i class="fa-solid fa-calendar-day text-[10px] text-slate-300"></i>
                                        <span
                                            :class="promoTempDate.start ? 'text-slate-700 font-medium' : 'text-slate-400'">{{ promoTempDate.start ? formatShortDate(promoTempDate.start) : 'Mulai' }}</span>
                                    </div>
                                    <div @click="openCalendar($event, 'form', '', 'promoDate2')"
                                        class="select-trigger-button select-trigger-button-form-tight">
                                        <i class="fa-solid fa-calendar-day text-[10px] text-slate-300"></i>
                                        <span
                                            :class="promoTempDate.end ? 'text-slate-700 font-medium' : 'text-slate-400'">{{ promoTempDate.end ? formatShortDate(promoTempDate.end) : 'Selesai' }}</span>
                                    </div>
                                </div>
                            </div>
                            <input v-model="promoForm.Periode" type="text"
                                placeholder="Ketik periode manual atau pilih preset di atas..."
                                class="form-input bg-white font-medium" />
                        </div>
                        <!-- Rules -->
                        <div>
                            <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">S&K
                                / Rules</label>
                            <textarea v-model="promoForm.Rules" rows="3" placeholder="Syarat dan ketentuan berlaku..."
                                class="form-input resize-none"></textarea>
                        </div>
                        <!-- Benefit -->
                        <div>
                            <label
                                class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Benefit</label>
                            <textarea v-model="promoForm.Benefit" rows="3" placeholder="Keuntungan yang didapat..."
                                class="form-input resize-none"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer-bar modal-footer-actions">
                        <button @click="promoModalOpen = false" class="modal-secondary-button">Batal</button>
                        <button @click="savePromo" :disabled="submitting" class="modal-primary-button">{{ submitting ? 'Menyimpan...' : 'Simpan' }}</button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>

    <!-- Unboxing Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="unboxingModalOpen"
                class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                <div @click="unboxingModalOpen = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop">
                </div>
                <div
                    class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface z-[2001]">
                    <div
                        class="modal-header-bar modal-header-bar-sticky radius-sheet-top z-[2010]">
                        <div class="modal-header-copy">
                            <div
                                :class="['modal-header-icon text-white', unboxingModalType === 'create' ? 'bg-amber-500' : 'bg-ppp-accent']">
                                <i
                                    :class="['fa-solid text-[12px]', unboxingModalType === 'create' ? 'fa-plus' : 'fa-pen-to-square']"></i>
                            </div>
                            <div>
                                <div class="type-title text-slate-900">{{ unboxingModalType === 'create' ? 'Tambah Unboxing' : 'Edit Unboxing' }}</div>
                                <div class="type-meta text-slate-400 uppercase tracking-widest mt-0.5">Konten Module
                                </div>
                            </div>
                        </div>
                        <button @click="unboxingModalOpen = false" aria-label="Tutup modal"
                            class="icon-utility-button icon-utility-round"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="md:col-span-2">
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Judul
                                    Unboxing <span class="text-red-500">*</span></label>
                                <input v-model="unboxingForm.Nama" type="text"
                                    placeholder="Contoh: Unboxing Samsung S24 Ultra" class="form-input" />
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Editor</label>
                                <div @click="toggleSearchSelect($event, 'unboxingEditor')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="unboxingForm.Editor ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ unboxingForm.Editor || 'Pilih Editor' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </div>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'unboxingEditor'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="relative mb-2">
                                            <i
                                                class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                                            <input v-model="searchSelectQuery" type="text" placeholder="Cari editor..."
                                                class="form-input-popover" @click.stop />
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in filteredEditorOptions" :key="opt"
                                                @click="unboxingForm.Editor = opt; searchSelectOpen = null"
                                                :class="['popover-option', unboxingForm.Editor === opt ? 'popover-option-active' : '']">
                                                {{ opt }}</div>
                                            <div v-if="filteredEditorOptions.length === 0"
                                                class="px-3 py-4 text-center text-[10px] text-slate-400 uppercase tracking-widest">
                                                Tidak ditemukan</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Status</label>
                                <div @click="toggleSearchSelect($event, 'unboxingStatus')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="unboxingForm.Status ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ unboxingForm.Status || 'Pilih Status' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </div>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'unboxingStatus'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in unboxingStatusOptions" :key="opt"
                                                @click="unboxingForm.Status = opt; searchSelectOpen = null"
                                                :class="['popover-option', unboxingForm.Status === opt ? 'popover-option-active' : '']">
                                                {{ opt }} </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Tanggal
                                    Upload</label>
                                <button type="button" @click="openCalendar($event, 'form', '', 'unboxingUploadDate')"
                                    class="date-trigger-button toolbar-trigger-field">
                                    <i class="fa-solid fa-calendar-days text-[10px] text-slate-400"></i>
                                    <span
                                        :class="unboxingForm.Upload_Date ? 'text-slate-700 font-medium' : 'text-slate-400'">
                                        {{ unboxingForm.Upload_Date ? formatShortDate(unboxingForm.Upload_Date) : 'Pilih tanggal upload' }}
                                    </span>
                                </button>
                            </div>
                            <div class="md:col-span-2">
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Link
                                    Video</label>
                                <input v-model="unboxingForm.Link" type="text" placeholder="https://..."
                                    class="form-input" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer-bar modal-footer-actions">
                        <button @click="unboxingModalOpen = false" class="modal-secondary-button">Batal</button>
                        <button @click="saveUnboxing" :disabled="submitting" class="modal-primary-button">{{ submitting ? 'Menyimpan...' : 'Simpan' }}</button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>

    <!-- Distribution Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="distModalOpen"
                class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                <div @click="distModalOpen = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop"></div>
                <div
                    class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface z-[2001]">
                    <div
                        class="modal-header-bar modal-header-bar-sticky radius-sheet-top z-[2010]">
                        <div class="modal-header-copy">
                            <div class="modal-header-icon text-white bg-ppp-accent">
                                <i class="fa-solid fa-share-nodes text-[12px]"></i>
                            </div>
                            <div>
                                <div class="type-title text-slate-900">{{ distributionForm.ID ? 'Edit Distribusi' : 'Tambah Distribusi' }}</div>
                                <div class="type-meta text-slate-400 uppercase tracking-widest mt-0.5">Konten Module
                                </div>
                            </div>
                        </div>
                        <button @click="distModalOpen = false" aria-label="Tutup modal"
                            class="icon-utility-button icon-utility-round"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="md:col-span-2">
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Judul
                                    <span class="text-red-500">*</span></label>
                                <input v-model="distributionForm.Judul" type="text" placeholder="Judul konten"
                                    class="form-input" />
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Platform</label>
                                <div @click="toggleSearchSelect($event, 'distPlatform')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="distributionForm.Platform ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ distributionForm.Platform || 'Pilih Platform' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </div>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'distPlatform'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in (platformOptions)" :key="opt"
                                                @click="distributionForm.Platform = opt; searchSelectOpen = null"
                                                :class="['popover-option', distributionForm.Platform === opt ? 'popover-option-active' : '']">
                                                {{ opt }}</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div>
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Tanggal
                                    Publish</label>
                                <button type="button" @click="openCalendar($event, 'form', '', 'distribution')"
                                    class="date-trigger-button toolbar-trigger-field">
                                    <i class="fa-solid fa-calendar-days text-[10px] text-slate-400"></i>
                                    <span
                                        :class="distributionForm.Tanggal_Publish ? 'text-slate-700 font-medium' : 'text-slate-400'">
                                        {{ distributionForm.Tanggal_Publish ? formatShortDate(distributionForm.Tanggal_Publish) : 'Pilih tanggal publish' }}
                                    </span>
                                </button>
                            </div>
                            <div class="md:col-span-2">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Link</label>
                                <input v-model="distributionForm.Link" type="text" placeholder="https://..."
                                    class="form-input" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer-bar modal-footer-actions">
                        <button @click="distModalOpen = false" class="modal-secondary-button">Batal</button>
                        <button @click="saveDistribution" :disabled="submitting" class="modal-primary-button">{{ submitting ? 'Menyimpan...' : 'Simpan' }}</button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>

    <!-- Analytics Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="analyticsModalOpen"
                class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                <div @click="analyticsModalOpen = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop">
                </div>
                <div
                    class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface z-[2001]">
                    <div
                        class="modal-header-bar modal-header-bar-sticky radius-sheet-top z-[2010]">
                        <div class="modal-header-copy">
                            <div class="modal-header-icon text-white bg-ppp-accent">
                                <i class="fa-solid fa-chart-bar text-[12px]"></i>
                            </div>
                            <div>
                                <div class="type-title text-slate-900">{{ analyticsForm.ID ? 'Edit Analitik' : 'Tambah Analitik' }}</div>
                                <div class="type-meta text-slate-400 uppercase tracking-widest mt-0.5">Konten Module
                                </div>
                            </div>
                        </div>
                        <button @click="analyticsModalOpen = false" aria-label="Tutup modal"
                            class="icon-utility-button icon-utility-round"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="md:col-span-2">
                                <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Judul
                                    <span class="text-red-500">*</span></label>
                                <input v-model="analyticsForm.Judul" type="text" placeholder="Judul konten"
                                    class="form-input" />
                            </div>
                            <div class="relative search-select-container">
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Platform</label>
                                <div @click="toggleSearchSelect($event, 'analyticsPlatform')"
                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                    <span
                                        :class="analyticsForm.Platform ? 'text-slate-800 font-medium' : 'text-slate-400'">{{ analyticsForm.Platform || 'Pilih Platform' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300"></i>
                                </div>
                                <transition name="fade">
                                    <div v-if="searchSelectOpen === 'analyticsPlatform'" :style="popoverStyle"
                                        class="search-select-popover">
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <div v-for="opt in (platformOptions)" :key="opt"
                                                @click="analyticsForm.Platform = opt; searchSelectOpen = null"
                                                :class="['popover-option', analyticsForm.Platform === opt ? 'popover-option-active' : '']">
                                                {{ opt }}</div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                            <div>
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Views</label>
                                <input v-model.number="analyticsForm.Views" type="number" min="0" class="form-input" />
                            </div>
                            <div>
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Likes</label>
                                <input v-model.number="analyticsForm.Likes" type="number" min="0" class="form-input" />
                            </div>
                            <div>
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Comments</label>
                                <input v-model.number="analyticsForm.Comments" type="number" min="0"
                                    class="form-input" />
                            </div>
                            <div>
                                <label
                                    class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-2">Shares</label>
                                <input v-model.number="analyticsForm.Shares" type="number" min="0" class="form-input" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer-bar modal-footer-actions">
                        <button @click="analyticsModalOpen = false" class="modal-secondary-button">Batal</button>
                        <button @click="saveAnalytics" :disabled="submitting" class="modal-primary-button">{{ submitting ? 'Menyimpan...' : 'Simpan' }}</button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>

    <!-- Calendar Day Detail Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="calendarDayModalOpen" class="fixed inset-0 z-[5000] flex items-center justify-center p-4 overlay-motion-dialog">
                <div @click="calendarDayModalOpen = false" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm overlay-backdrop">
                </div>
                <div
                    class="modal-width-form radius-dialog modal-dialog-surface-scroll overlay-dialog-surface">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-white">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Jadwal & Event</h3>
                            <p class="text-[10px] text-slate-400 font-medium mt-0.5 uppercase tracking-widest">{{ calendarDayModalDate }}</p>
                        </div>
                        <button @click="calendarDayModalOpen = false" aria-label="Tutup modal"
                            class="icon-utility-button icon-utility-round">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>
                    <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-4 bg-slate-50/30">
                        <div v-if="calendarDayModalItems.length === 0"
                            class="flex flex-col items-center justify-center py-10 text-slate-300">
                            <i class="fa-solid fa-calendar-xmark text-3xl mb-3 opacity-20"></i>
                            <p class="text-[11px] font-bold uppercase tracking-widest">Tidak ada jadwal</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="item in calendarDayModalItems" :key="item.ID || item.Nama_Event"
                                :class="['p-3 rounded-xl border transition-all', item.TYPE === 'event' ? 'bg-slate-50 border-slate-100' : 'bg-white border-slate-100 hover:shadow-md']">

                                <template v-if="item.TYPE === 'content'">
                                    <div class="flex items-start justify-between gap-2 mb-1.5">
                                        <div class="flex items-center gap-2">
                                            <i
                                                :class="[getPlatformIcon(item.Platform || (item.Platforms || '').split(',')[0]), 'text-xs text-blue-500']"></i>
                                            <span
                                                class="text-[10px] font-bold text-blue-500 uppercase tracking-widest">Konten</span>
                                        </div>
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-blue-50 text-blue-500 uppercase">{{ item.Status }}</span>
                                    </div>
                                    <h4 class="text-[12px] font-bold text-slate-900 leading-[1.2] mb-1 uppercase">{{ item.Judul }}</h4>
                                    <div class="flex items-center gap-2.5 flex-wrap">
                                        <div class="flex items-center gap-1.5">
                                            <i class="fa-solid fa-user text-[9px] text-slate-300"></i>
                                            <span class="text-[10px] font-medium text-slate-500">{{ item.Editor || '-' }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <i class="fa-solid fa-clapperboard text-[9px] text-slate-300"></i>
                                            <span class="text-[10px] font-medium text-slate-500">{{ item.Format_Konten || '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-2.5 pt-2 border-t border-slate-50 flex items-center justify-end">
                                        <button @click="openEditModal(item); calendarDayModalOpen = false"
                                            class="text-[10px] font-bold text-ppp-accent hover:underline">Edit
                                            Detail <i class="fa-solid fa-arrow-right ml-1"></i></button>
                                    </div>
                                </template>

                                <template v-else-if="item.TYPE === 'story'">
                                    <div class="flex items-start justify-between gap-2 mb-1.5">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-clapperboard text-xs text-rose-500"></i>
                                            <span
                                                class="text-[10px] font-bold text-rose-500 uppercase tracking-widest">Story</span>
                                        </div>
                                        <span v-if="item.Status"
                                            class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-rose-50 text-rose-500 uppercase">{{ item.Status }}</span>
                                    </div>
                                    <h4 class="text-[12px] font-bold text-slate-900 leading-[1.2] mb-1 uppercase">{{ item.Story_Schedule || item.Story }}</h4>
                                    <div class="flex items-center gap-2.5 flex-wrap">
                                        <div class="flex items-center gap-1.5">
                                            <i class="fa-solid fa-clock text-[9px] text-slate-300"></i>
                                            <span class="text-[10px] font-medium text-slate-500">{{ item.Jam || '-' }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <i class="fa-solid fa-note-sticky text-[9px] text-slate-300"></i>
                                            <span class="text-[10px] font-medium text-slate-500">{{ item.Catatan || '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-2.5 pt-2 border-t border-slate-50 flex items-center justify-end">
                                        <button @click="openEditStoryModal(item); calendarDayModalOpen = false"
                                            class="text-[10px] font-bold text-rose-500 hover:underline">Edit
                                            Story <i class="fa-solid fa-arrow-right ml-1"></i></button>
                                    </div>
                                </template>

                                <template v-else>
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-white shadow-sm bg-amber-500">
                                            <i class="fa-solid fa-star text-[10px]"></i>
                                        </div>
                                        <div>
                                            <span
                                                class="text-[9px] font-black uppercase tracking-widest text-amber-500">Hari Raya</span>
                                            <h4 class="text-[12px] font-bold text-slate-900 leading-[1.2] uppercase">{{ item.Nama_Event }}</h4>
                                        </div>
                                    </div>
                                </template>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
    </div>
@endverbatim
