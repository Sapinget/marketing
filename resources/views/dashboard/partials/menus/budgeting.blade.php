@verbatim
<!-- Budgeting tab -->
            <div v-if="activeTab === 'budgeting' && !budgetConfigLoaded"
                class="space-y-4 animate-fadeIn animate-pulse">
                <div class="section-card section-card-body">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-slate-200"></div>
                        <div class="space-y-2">
                            <div class="h-5 bg-slate-200 rounded-full w-40"></div>
                            <div class="h-3 bg-slate-100 rounded-full w-56"></div>
                        </div>
                    </div>
                    <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
                        <div v-for="i in 4" :key="'sk-bg-st'+i"
                            class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
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
            <div v-if="activeTab === 'budgeting' && budgetConfigLoaded" class="space-y-4 animate-fadeIn">
                <section class="section-card section-card-body">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 md:gap-5">
                        <div class="modal-header-copy">
                            <div class="w-12 h-12 rounded-2xl bg-amber text-light flex items-center justify-center border border-amber">
                                <i class="fa-solid fa-wallet text-heading-lg"></i>
                            </div>
                            <div>
                                <h2 class="type-heading-sm font-bold text-slate-900">Rancangan Anggaran</h2>
                                <p class="text-body-sm text-slate-400 uppercase mt-0.5">Estimasi
                                    Kebutuhan Topup Per Platform</p>
                            </div>
                        </div>
                        <div class="toolbar-actions">
                            <button @click="exportBudgetToExcel"
                                class="primary-cta-button primary-cta-button--success primary-cta-button--icon-only active:scale-95" aria-label="Export Excel"><i
                                    class="fa-solid fa-file-excel text-[9px]"></i></button>
                            <button @click="exportBudgetToPDF"
                                class="primary-cta-button primary-cta-button--danger primary-cta-button--icon-only active:scale-95" aria-label="Export PDF"><i
                                    class="fa-solid fa-file-pdf text-[9px]"></i></button>
                            <button @click="showBudgetSettings = !showBudgetSettings"
                                class="primary-cta-button primary-cta-button--neutral primary-cta-button--icon-only active:scale-95"
                                aria-label="Atur Budget"><i
                                    class="fa-solid fa-sliders text-[9px]"></i></button>
                        </div>
                    </div>
                </section>

                <!-- Date filter -->
                <div class="bg-white radius-panel border border-slate-100 p-4">
                    <div class="compact-period-toolbar">
                        <label class="compact-period-toolbar__label font-bold uppercase">Periode</label>
                        <div class="compact-period-toolbar__controls">
                            <button type="button" @click="openCalendar($event, 'filter', '', 'budgeting')"
                                class="select-trigger-button-compact w-full">
                                <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                <template v-if="budgetDateFilter.start">
                                    {{ formatShortDate(budgetDateFilter.start) }}
                                    <span v-if="budgetDateFilter.end"> - {{ formatShortDate(budgetDateFilter.end) }}</span>
                                </template>
                                <template v-else>Pilih Periode</template>
                                <i v-if="budgetDateFilter.start"
                                    @click.stop="budgetDateFilter = { start: '', end: '' }"
                                    class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-danger"></i>
                            </button>
                            <button @click="budgetDateFilter = { start: '', end: '' }"
                                class="icon-utility-button icon-utility-bordered" title="Reset" aria-label="Reset"><i
                                    class="fa-solid fa-rotate-left text-body-sm"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Total topup banner -->
                <div
                    class="bg-ppp-accent text-white font-bold text-heading-sm px-5 py-3.5 rounded-2xl flex justify-between items-center">
                    <span>Total Rencana Topup</span>
                    <span>{{ formatCurrency(budgetCalculations.totalTopup) }}</span>
                </div>

                <!-- Settings panel -->
                <div v-if="showBudgetSettings" class="bg-white radius-panel border border-slate-100 p-5 space-y-6">
                    <!-- Meta -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 pb-4 border-b border-slate-100">
                        <div class="col-span-full text-body-sm font-bold text-slate-500 uppercase mb-1">
                            Konfigurasi Meta</div>
                        <div><label for="budget-meta-cost-per-ad" class="block text-body-sm text-slate-400 mb-1">Biaya / Iklan</label><input
                                id="budget-meta-cost-per-ad" name="budget_meta_cost_per_ad" type="number" v-model.number="budgetConfig.meta.costPerAd"
                                class="w-full text-body font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div><label for="budget-meta-total-ads" class="block text-body-sm text-slate-400 mb-1">Total Iklan</label><input
                                id="budget-meta-total-ads" name="budget_meta_total_ads" type="number" v-model.number="budgetConfig.meta.totalAds"
                                class="w-full text-body font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div><label for="budget-meta-days" class="block text-body-sm text-slate-400 mb-1">Durasi (Hari)</label><input
                                id="budget-meta-days" name="budget_meta_days" type="number" v-model.number="budgetConfig.meta.days"
                                class="w-full text-body font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div><label for="budget-meta-balance" class="block text-body-sm text-slate-400 mb-1">Sisa Saldo</label><input id="budget-meta-balance" name="budget_meta_balance" type="number"
                                v-model.number="budgetConfig.meta.balance"
                                class="w-full text-body font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                    </div>
                    <!-- Google -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 pb-4 border-b border-slate-100">
                        <div class="col-span-full text-body-sm font-bold text-slate-500 uppercase mb-1">
                            Konfigurasi Google</div>
                        <div><label for="budget-google-cost-per-ad" class="block text-body-sm text-slate-400 mb-1">Biaya / Ads</label><input
                                id="budget-google-cost-per-ad" name="budget_google_cost_per_ad" type="number" v-model.number="budgetConfig.google.costPerAd"
                                class="w-full text-body font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div><label for="budget-google-total-ads" class="block text-body-sm text-slate-400 mb-1">Total Ads</label><input id="budget-google-total-ads" name="budget_google_total_ads" type="number"
                                v-model.number="budgetConfig.google.totalAds"
                                class="w-full text-body font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div><label for="budget-google-days" class="block text-body-sm text-slate-400 mb-1">Durasi (Hari)</label><input
                                id="budget-google-days" name="budget_google_days" type="number" v-model.number="budgetConfig.google.days"
                                class="w-full text-body font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div><label for="budget-google-balance" class="block text-body-sm text-slate-400 mb-1">Sisa Saldo</label><input id="budget-google-balance" name="budget_google_balance" type="number"
                                v-model.number="budgetConfig.google.balance"
                                class="w-full text-body font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                    </div>
                    <!-- Mekari -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 pb-4 border-b border-slate-100">
                        <div class="col-span-full text-body-sm font-bold text-slate-500 uppercase mb-1">
                            Konfigurasi Mekari</div>
                        <div><label for="budget-mekari-visitor-target" class="block text-body-sm text-slate-400 mb-1">Visitor Target /
                                Hari</label><input id="budget-mekari-visitor-target" name="budget_mekari_visitor_target" type="number"
                                v-model.number="budgetConfig.mekari.visitor.targetPerDay"
                                class="w-full text-body font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div><label for="budget-mekari-visitor-days" class="block text-body-sm text-slate-400 mb-1">Durasi Visitor
                                (Hari)</label><input id="budget-mekari-visitor-days" name="budget_mekari_visitor_days" type="number" v-model.number="budgetConfig.mekari.visitor.days"
                                class="w-full text-body font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div><label for="budget-mekari-visitor-balance" class="block text-body-sm text-slate-400 mb-1">Saldo Visitor
                                (Unit)</label><input id="budget-mekari-visitor-balance" name="budget_mekari_visitor_balance" type="number" v-model.number="budgetConfig.mekari.visitor.balance"
                                class="w-full text-body font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div><label for="budget-mekari-visitor-topup-cost" class="block text-body-sm text-slate-400 mb-1">Biaya Topup Visitor
                                (Manual)</label><input id="budget-mekari-visitor-topup-cost" name="budget_mekari_visitor_topup_cost" type="number"
                                v-model.number="budgetConfig.mekari.visitor.topupCost"
                                class="w-full text-body font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div class="col-span-full border-t border-dashed border-slate-200 my-1"></div>
                        <div><label for="budget-mekari-broadcast-cost-per-week" class="block text-body-sm text-slate-400 mb-1">Biaya Broadcast /
                                Minggu</label><input id="budget-mekari-broadcast-cost-per-week" name="budget_mekari_broadcast_cost_per_week" type="number"
                                v-model.number="budgetConfig.mekari.broadcast.costPerWeek"
                                class="w-full text-body font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div><label for="budget-mekari-broadcast-weeks" class="block text-body-sm text-slate-400 mb-1">Durasi (Minggu)</label><input
                                id="budget-mekari-broadcast-weeks" name="budget_mekari_broadcast_weeks" type="number" v-model.number="budgetConfig.mekari.broadcast.weeks"
                                class="w-full text-body font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div><label for="budget-mekari-broadcast-special-price" class="block text-body-sm text-slate-400 mb-1">Special Price
                                Addon</label><input id="budget-mekari-broadcast-special-price" name="budget_mekari_broadcast_special_price" type="number"
                                v-model.number="budgetConfig.mekari.broadcast.specialPrice"
                                class="w-full text-body font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                        <div><label for="budget-mekari-broadcast-balance" class="block text-body-sm text-slate-400 mb-1">Saldo Broadcast
                                (Rp)</label><input id="budget-mekari-broadcast-balance" name="budget_mekari_broadcast_balance" type="number" v-model.number="budgetConfig.mekari.broadcast.balance"
                                class="w-full text-body font-bold p-2 rounded-xl border border-slate-200 bg-slate-50 outline-none focus:border-ppp-accent" />
                        </div>
                    </div>
                    <!-- Colab Partners -->
                    <div class="pb-4 border-b border-slate-100">
                        <div class="flex justify-between items-center mb-3">
                            <div class="text-body-sm font-bold text-slate-500 uppercase">Partner Colab</div>
<button type="button"
                                 @click="budgetConfig.colabPartners.push({name: '', packageCost: 0, slots: 0})"
                                 class="primary-cta-button primary-cta-button--neutral primary-cta-button--icon-only" aria-label="Tambah Partner Colab"><i
                                     class="fa-solid fa-plus"></i></button>
                        </div>
                        <div v-for="(partner, idx) in budgetConfig.colabPartners" :key="'cp'+idx"
                            class="grid grid-cols-1 md:grid-cols-4 gap-2 mb-2 bg-slate-50 p-3 rounded-xl border border-slate-100 relative">
                            <div class="md:col-span-2"><label class="block text-overline text-slate-400 mb-1">Nama
                                    Partner</label>
                                <div class="relative search-select-container">
                                    <button type="button" @click="toggleSearchSelect($event, 'budget_partner_'+idx)"
                                        class="w-full text-body font-bold bg-white border border-slate-200 rounded-lg p-2 outline-none hover:border-ppp-accent transition-all flex items-center justify-between gap-2">
                                        <span class="truncate"
                                            :class="partner.name ? 'text-slate-700' : 'text-slate-400'">{{ partner.name || 'Pilih Partner' }}</span>
                                        <i class="fa-solid fa-chevron-down text-overline text-slate-400"></i>
                                    </button>
                                    <div v-if="searchSelectOpen === 'budget_partner_'+idx" :style="popoverStyle"
                                        class="search-select-popover search-select-popover--compact max-h-60 overflow-y-auto">
                                        <div v-for="opt in (settings.Colab || [])" :key="opt"
                                            @click="partner.name = opt; searchSelectOpen = null" class="popover-option">
                                            {{ opt }}</div>
                                    </div>
                                </div>
                            </div>
                            <div><label :for="`budget-colab-package-cost-${idx}`" class="block text-overline text-slate-400 mb-1">Biaya Paket</label><input
                                    :id="`budget-colab-package-cost-${idx}`" :name="`budget_colab_package_cost_${idx}`" type="number" v-model.number="partner.packageCost"
                                    class="w-full text-body bg-white border border-slate-200 rounded-lg p-2 outline-none" />
                            </div>
                            <div><label :for="`budget-colab-slots-${idx}`" class="block text-overline text-slate-400 mb-1">Slot Video</label><input
                                    :id="`budget-colab-slots-${idx}`" :name="`budget_colab_slots_${idx}`" type="number" v-model.number="partner.slots"
                                    class="w-full text-body bg-white border border-slate-200 rounded-lg p-2 outline-none" />
                            </div>
                            <button @click="budgetConfig.colabPartners.splice(idx, 1)"
                                class="absolute -top-2 -right-2 bg-danger text-white h-5 w-5 rounded-full text-body-sm flex items-center justify-center"><i
                                    class="fa-solid fa-xmark"></i></button>
                        </div>
                        <div v-if="!budgetConfig.colabPartners || budgetConfig.colabPartners.length === 0"
                            class="text-center py-4 text-body-sm text-slate-400 italic bg-slate-50 rounded-xl border border-dashed border-slate-200">
                            Belum ada partner colab.</div>
                    </div>
                    <!-- Others -->
                    <div class="pb-4">
                        <div class="flex justify-between items-center mb-3">
                            <div class="text-body-sm font-bold text-slate-500 uppercase">Platform Lainnya</div>
<button type="button"
                                 @click="budgetConfig.others.push({name: '', costPerUnit: 0, quantity: 1, duration: 1, balance: 0})"
                                 class="primary-cta-button primary-cta-button--neutral primary-cta-button--icon-only" aria-label="Tambah Platform Lainnya"><i
                                     class="fa-solid fa-plus"></i></button>
                        </div>
                        <div v-for="(item, idx) in budgetConfig.others" :key="'oth'+idx"
                            class="grid grid-cols-2 md:grid-cols-6 gap-2 mb-2 bg-slate-50 p-3 rounded-xl border border-slate-100 relative">
                            <div class="col-span-2"><label :for="`budget-other-name-${idx}`" class="block text-overline text-slate-400 mb-1">Nama
                                    Platform</label><input :id="`budget-other-name-${idx}`" :name="`budget_other_name_${idx}`" type="text" v-model="item.name"
                                    class="w-full text-body font-bold bg-white border border-slate-200 rounded-lg p-2 outline-none" />
                            </div>
                            <div><label :for="`budget-other-cost-per-unit-${idx}`" class="block text-overline text-slate-400 mb-1">Biaya Satuan</label><input
                                    :id="`budget-other-cost-per-unit-${idx}`" :name="`budget_other_cost_per_unit_${idx}`" type="number" v-model.number="item.costPerUnit"
                                    class="w-full text-body bg-white border border-slate-200 rounded-lg p-2 outline-none" />
                            </div>
                            <div><label :for="`budget-other-quantity-${idx}`" class="block text-overline text-slate-400 mb-1">Qty</label><input :id="`budget-other-quantity-${idx}`" :name="`budget_other_quantity_${idx}`" type="number"
                                    v-model.number="item.quantity"
                                    class="w-full text-body bg-white border border-slate-200 rounded-lg p-2 outline-none" />
                            </div>
                            <div><label :for="`budget-other-duration-${idx}`" class="block text-overline text-slate-400 mb-1">Durasi</label><input :id="`budget-other-duration-${idx}`" :name="`budget_other_duration_${idx}`" type="number"
                                    v-model.number="item.duration"
                                    class="w-full text-body bg-white border border-slate-200 rounded-lg p-2 outline-none" />
                            </div>
                            <div><label :for="`budget-other-balance-${idx}`" class="block text-overline text-slate-400 mb-1">Saldo</label><input :id="`budget-other-balance-${idx}`" :name="`budget_other_balance_${idx}`" type="number"
                                    v-model.number="item.balance"
                                    class="w-full text-body bg-white border border-slate-200 rounded-lg p-2 outline-none" />
                            </div>
                            <button @click="budgetConfig.others.splice(idx, 1)"
                                class="absolute -top-2 -right-2 bg-danger text-white h-5 w-5 rounded-full text-body-sm flex items-center justify-center"><i
                                    class="fa-solid fa-xmark"></i></button>
                        </div>
                        <div v-if="budgetConfig.others.length === 0"
                            class="text-center py-4 text-body-sm text-slate-400 italic bg-slate-50 rounded-xl border border-dashed border-slate-200">
                            Belum ada platform tambahan.</div>
                    </div>
                    <div class="flex justify-end pt-2 border-t border-slate-100">
                        <button @click="saveBudgetServer" :disabled="submitting"
                            class="primary-cta-button primary-cta-button--accent hover:bg-amber disabled:opacity-50">
                            <i v-if="!submitting" class="fa-solid fa-floppy-disk"></i>
                            <i v-else class="fa-solid fa-circle-notch fa-spin"></i>
                            {{ submitting ? 'Menyimpan...' : 'Simpan Konfigurasi' }}
                        </button>
                    </div>
                </div>

                <!-- Summary cards (compact) -->
                <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
                    <div v-for="c in budgetSummary.cards.slice(0, 5)" :key="c.label" class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-5"><i :class="[c.iconPrefix || 'fa-solid', c.icon, 'text-[120px]']"></i></div>
                        <p class="dashboard-summary-title">{{ c.label }}</p>
                        <div class="flex items-baseline gap-2">
                            <span class="dashboard-summary-value">{{ c.value }}</span>
                            <span v-if="c.unit" class="dashboard-summary-unit">{{ c.unit }}</span>
                        </div>
                        <p :class="['text-body-sm font-bold mt-3', c.subColor]">{{ c.sub }}</p>
                    </div>
                </div>
            </div>




















    <!-- class="filter-trigger-button toolbar-trigger-field" -->
@endverbatim
