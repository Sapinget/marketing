@verbatim
<!-- Analisa Insight & Tren View -->
                    <div v-if="activeTab === 'analisa_insight'" class="space-y-6 animate-fadeIn pb-10">
                        <!-- Header -->
                        <section class="section-card section-card-body">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-gradient-to-br from-ppp-accent to-[#3D4FDB] flex items-center justify-center">
                                        <i class="fa-solid fa-microscope text-white text-[16px]"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-semibold text-slate-900">Insight & Tren</h2>
                                        <p class="type-body text-slate-500">Analisa lintas data konten & operasional</p>
                                    </div>
                                </div>
                                <!-- Filter tanggal + sub-tab toggle -->
                                <div class="mobile-toolbar-stack md:items-center">
                                    <button @click="openCalendar($event, 'filter', '', 'insight')"
                                        class="filter-trigger-button md:w-auto">
                                        <i class="fa-solid fa-calendar-days text-[10px] text-slate-400"></i>
                                        <template v-if="insightDateFilter.start">
                                            {{ formatShortDate(insightDateFilter.start) }}
                                            <span v-if="insightDateFilter.end"> - {{ formatShortDate(insightDateFilter.end) }}</span>
                                        </template>
                                        <template v-else>Semua Tanggal</template>
                                        <i v-if="insightDateFilter.start"
                                            @click.stop="insightDateFilter = { start: '', end: '' }"
                                            class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-red-500"></i>
                                    </button>
                                    <div class="segmented-control segmented-control--ios segmented-control--equal w-full md:w-auto" :data-index="analisaInsightTab === 'sales' ? 1 : 0">
                                        <button @click="analisaInsightTab = 'konten'"
                                            :class="['segmented-control__item flex-1 justify-center whitespace-nowrap', analisaInsightTab === 'konten' ? 'segmented-control__item--active' : '']">
                                            <i class="fa-solid fa-film mr-1.5"></i>Konten
                                        </button>
                                        <button @click="analisaInsightTab = 'sales'"
                                            :class="['segmented-control__item flex-1 justify-center whitespace-nowrap', analisaInsightTab === 'sales' ? 'segmented-control__item--active' : '']">
                                            <i class="fa-solid fa-store mr-1.5"></i>Sales & CS
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- SUB-TAB: KONTEN -->
                        <template v-if="analisaInsightTab === 'konten'">

                            <!-- A3: Colab vs Non-Colab -->
                            <section class="section-card section-card-body">
                                <h3 class="type-title font-bold text-slate-800 mb-4"><i
                                        class="fa-solid fa-handshake text-ppp-accent mr-2"></i>Colab vs Non-Colab</h3>
                                <div v-if="!colabVsNonColabStats || (!colabVsNonColabStats.colab.count && !colabVsNonColabStats.nonColab.count)"
                                    class="text-center py-8 text-[11px] text-slate-400">Belum ada data analytics</div>
                                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Colab card -->
                                    <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-4">
                                        <div class="flex items-center gap-2 mb-3">
                                            <span
                                                class="px-2 py-0.5 bg-emerald-500 text-white rounded-lg text-[9px] font-bold uppercase tracking-widest">Colab</span>
                                            <span class="type-body text-slate-500">{{ colabVsNonColabStats.colab.count }} konten</span>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="flex justify-between text-[11px]"><span
                                                    class="text-slate-500">Avg Views</span><span
                                                    class="font-bold text-slate-800">{{ (colabVsNonColabStats.colab.avgViews || 0).toLocaleString('id-ID') }}</span></div>
                                            <div class="flex justify-between text-[11px]"><span
                                                    class="text-slate-500">Avg Likes</span><span
                                                    class="font-bold text-slate-800">{{ (colabVsNonColabStats.colab.avgLikes || 0).toLocaleString('id-ID') }}</span></div>
                                            <div class="flex justify-between text-[11px]"><span
                                                    class="text-slate-500">Avg Comments</span><span
                                                    class="font-bold text-slate-800">{{ (colabVsNonColabStats.colab.avgComments || 0).toLocaleString('id-ID') }}</span></div>
                                            <div
                                                class="flex justify-between text-[11px] border-t border-emerald-200 pt-2 mt-2">
                                                <span class="text-slate-600 font-semibold">Engagement Score</span><span
                                                    class="font-bold text-emerald-600 text-[13px]">{{ (colabVsNonColabStats.colab.avgScore || 0).toLocaleString('id-ID') }}</span></div>
                                        </div>
                                    </div>
                                    <!-- Non-Colab card -->
                                    <div class="surface-panel-soft">
                                        <div class="flex items-center gap-2 mb-3">
                                            <span
                                                class="px-2 py-0.5 bg-slate-400 text-white rounded-lg text-[9px] font-bold uppercase tracking-widest">Non-Colab</span>
                                            <span class="type-body text-slate-500">{{ colabVsNonColabStats.nonColab.count }} konten</span>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="flex justify-between text-[11px]"><span
                                                    class="text-slate-500">Avg Views</span><span
                                                    class="font-bold text-slate-800">{{ (colabVsNonColabStats.nonColab.avgViews || 0).toLocaleString('id-ID') }}</span></div>
                                            <div class="flex justify-between text-[11px]"><span
                                                    class="text-slate-500">Avg Likes</span><span
                                                    class="font-bold text-slate-800">{{ (colabVsNonColabStats.nonColab.avgLikes || 0).toLocaleString('id-ID') }}</span></div>
                                            <div class="flex justify-between text-[11px]"><span
                                                    class="text-slate-500">Avg Comments</span><span
                                                    class="font-bold text-slate-800">{{ (colabVsNonColabStats.nonColab.avgComments || 0).toLocaleString('id-ID') }}</span></div>
                                            <div
                                                class="flex justify-between text-[11px] border-t border-slate-200 pt-2 mt-2">
                                                <span class="text-slate-600 font-semibold">Engagement Score</span><span
                                                    class="font-bold text-ppp-accent text-[13px]">{{ (colabVsNonColabStats.nonColab.avgScore || 0).toLocaleString('id-ID') }}</span></div>
                                        </div>
                                    </div>
                                    <!-- Relative bar comparison -->
                                    <div v-if="colabVsNonColabStats.colab.count || colabVsNonColabStats.nonColab.count"
                                        class="md:col-span-2 bg-slate-50 rounded-2xl p-4">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">
                                            Perbandingan Views</p>
                                        <div class="space-y-2">
                                            <div>
                                                <div class="flex justify-between text-[10px] text-slate-500 mb-1">
                                                    <span>Colab</span><span>{{ (colabVsNonColabStats.colab.avgViews || 0).toLocaleString('id-ID') }}</span></div>
                                                <div class="w-full bg-slate-200 rounded-full h-2">
                                                    <div class="bg-emerald-400 h-2 rounded-full"
                                                        :style="'width:' + (Math.max(colabVsNonColabStats.colab.avgViews, colabVsNonColabStats.nonColab.avgViews) > 0 ? Math.round(colabVsNonColabStats.colab.avgViews / Math.max(colabVsNonColabStats.colab.avgViews, colabVsNonColabStats.nonColab.avgViews) * 100) : 0) + '%'">
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="flex justify-between text-[10px] text-slate-500 mb-1">
                                                    <span>Non-Colab</span><span>{{ (colabVsNonColabStats.nonColab.avgViews || 0).toLocaleString('id-ID') }}</span></div>
                                                <div class="w-full bg-slate-200 rounded-full h-2">
                                                    <div class="bg-ppp-accent h-2 rounded-full"
                                                        :style="'width:' + (Math.max(colabVsNonColabStats.colab.avgViews, colabVsNonColabStats.nonColab.avgViews) > 0 ? Math.round(colabVsNonColabStats.nonColab.avgViews / Math.max(colabVsNonColabStats.colab.avgViews, colabVsNonColabStats.nonColab.avgViews) * 100) : 0) + '%'">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- A4: Content Funnel -->
                            <section class="section-card section-card-body">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="type-title font-bold text-slate-800"><i
                                            class="fa-solid fa-filter text-ppp-accent mr-2"></i>Content Funnel</h3>
                                    <span class="type-body text-slate-500">{{ masterPlanData.length }} total
                                        konten</span>
                                </div>
                                <div v-if="!masterPlanData.length" class="text-center py-8 text-[11px] text-slate-400">
                                    Belum ada data master konten</div>
                                <div v-else class="space-y-2">
                                    <div v-for="item in contentFunnelStats" :key="item.stage"
                                        class="flex items-center gap-3">
                                        <span
                                            class="w-20 text-[10px] font-bold text-slate-500 text-right uppercase tracking-wide shrink-0">{{ item.stage }}</span>
                                        <div class="flex-1 bg-slate-100 rounded-full h-3 overflow-hidden">
                                            <div class="h-3 rounded-full bg-gradient-to-r from-ppp-accent to-[#3D4FDB] transition-all duration-500"
                                                :style="'width:' + item.pct + '%'"></div>
                                        </div>
                                        <span class="w-20 text-[10px] font-bold text-slate-700 text-right shrink-0">{{ item.count }} <span class="text-slate-400 font-normal">({{ item.pct }}%)</span></span>
                                    </div>
                                </div>
                            </section>

                            <!-- A6: Monthly Trend -->
                            <section class="section-card section-card-body">
                                <h3 class="type-title font-bold text-slate-800 mb-4"><i
                                        class="fa-solid fa-chart-line text-ppp-accent mr-2"></i>Tren Bulanan (Views)
                                </h3>
                                <div v-if="!monthlyTrendData.length"
                                    class="text-center py-8 text-[11px] text-slate-400">Belum ada data analytics</div>
                                <div v-else>
                                    <div class="flex items-end gap-1.5 h-32 mb-2">
                                        <div v-for="m in monthlyTrendData" :key="m.ym"
                                            class="flex-1 flex flex-col items-center gap-1 group cursor-default">
                                            <div class="relative w-full flex items-end justify-center"
                                                style="height:96px">
                                                <div class="w-full rounded-t-lg bg-gradient-to-t from-ppp-accent to-[#3D4FDB] transition-all duration-500 group-hover:opacity-80 relative"
                                                    :style="'height:' + (monthlyTrendData.reduce((mx,x)=>Math.max(mx,x.views),1)>0 ? Math.max(4, Math.round(m.views/monthlyTrendData.reduce((mx,x)=>Math.max(mx,x.views),1)*96)) : 4) + 'px'">
                                                    <div
                                                        class="absolute -top-7 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[8px] px-1.5 py-0.5 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                                        {{ m.views.toLocaleString('id-ID') }}v | {{ m.count }}k
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="text-[8px] text-slate-400 font-medium">{{ m.ym.substring(5) }}/{{ m.ym.substring(2,4) }}</span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-3 mt-4">
                                        <div class="bg-slate-50 rounded-xl p-3 text-center">
                                            <p class="text-[10px] text-slate-400 mb-1">Total Views</p>
                                            <p class="type-title font-bold text-slate-800">{{ monthlyTrendData.reduce((s,m)=>s+m.views,0).toLocaleString('id-ID') }}
                                            </p>
                                        </div>
                                        <div class="bg-slate-50 rounded-xl p-3 text-center">
                                            <p class="text-[10px] text-slate-400 mb-1">Total Konten</p>
                                            <p class="type-title font-bold text-slate-800">{{ monthlyTrendData.reduce((s,m)=>s+m.count,0) }}</p>
                                        </div>
                                        <div class="bg-slate-50 rounded-xl p-3 text-center">
                                            <p class="text-[10px] text-slate-400 mb-1">Avg Views/Bln</p>
                                            <p class="type-title font-bold text-slate-800">{{ monthlyTrendData.length ? Math.round(monthlyTrendData.reduce((s,m)=>s+m.views,0)/monthlyTrendData.length).toLocaleString('id-ID') : '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </template>

                        <!-- SUB-TAB: SALES & CS -->
                        <template v-if="analisaInsightTab === 'sales'">

                            <!-- B2: Product Demand -->
                            <section class="section-card section-card-body">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="type-title font-bold text-slate-800"><i
                                            class="fa-solid fa-magnifying-glass-chart text-ppp-accent mr-2"></i>Produk
                                        Paling Banyak Ditanya</h3>
                                    <span class="type-body text-slate-500">Top 20</span>
                                </div>
                                <div v-if="!productDemandStats.length"
                                    class="text-center py-8 text-[11px] text-slate-400">Belum ada data unit ditanya
                                </div>
                                <div v-else class="space-y-2">
                                    <div
                                        class="grid grid-cols-12 text-[9px] font-bold text-slate-400 uppercase tracking-widest px-2 mb-1">
                                        <span class="col-span-1">#</span>
                                        <span class="col-span-5">Produk</span>
                                        <span class="col-span-3">Ditanya</span>
                                        <span class="col-span-3 text-right">Stok</span>
                                    </div>
                                    <div v-for="(p, idx) in productDemandStats" :key="idx"
                                        class="grid grid-cols-12 items-center bg-slate-50 hover:bg-slate-100 rounded-xl px-2 py-2 transition-all">
                                        <span class="col-span-1 text-[10px] font-bold text-slate-300">{{ idx+1 }}</span>
                                        <div class="col-span-5">
                                            <p class="text-[11px] font-bold text-slate-700 uppercase leading-tight">{{ p.brand }}</p>
                                            <p class="text-[9px] text-slate-400">{{ p.seri }}</p>
                                        </div>
                                        <div class="col-span-3">
                                            <div class="w-full bg-slate-200 rounded-full h-1.5 mb-0.5">
                                                <div class="bg-ppp-accent h-1.5 rounded-full"
                                                    :style="'width:' + Math.round(p.total/productDemandStats[0].total*100) + '%'">
                                                </div>
                                            </div>
                                            <span class="text-[10px] font-bold text-slate-700">{{ p.total }}</span>
                                        </div>
                                        <div class="col-span-3 flex justify-end gap-1 flex-wrap">
                                            <span v-if="p.available"
                                                class="px-1.5 py-0.5 bg-emerald-100 text-emerald-700 text-[8px] font-bold rounded">{{ p.available }} Ada</span>
                                            <span v-if="p.notAvailable"
                                                class="px-1.5 py-0.5 bg-rose-100 text-rose-600 text-[8px] font-bold rounded">{{ p.notAvailable }} Tdk</span>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- B1: Order Online Summary -->
                            <section class="section-card section-card-body">
                                <h3 class="type-title font-bold text-slate-800 mb-4"><i
                                        class="fa-solid fa-bag-shopping text-ppp-accent mr-2"></i>Ringkasan Order Online
                                </h3>
                                <div v-if="!orderOnlineSummary.length"
                                    class="text-center py-8 text-[11px] text-slate-400">Belum ada data order online
                                </div>
                                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <div v-for="e in orderOnlineSummary" :key="e.platform" class="surface-panel-soft">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">
                                            {{ e.platform }}</p>
                                        <div class="space-y-1.5">
                                            <div class="flex justify-between text-[11px]"><span
                                                    class="text-slate-500">Total Order</span><span
                                                    class="font-bold text-slate-800">{{ e.total }}</span></div>
                                            <div class="flex justify-between text-[11px]"><span
                                                    class="text-slate-500">Nominal Cair</span><span
                                                    class="font-bold text-slate-800">{{ formatCurrency(e.nominalCair) }}</span></div>
                                            <div class="flex justify-between text-[11px]"><span
                                                    class="text-slate-500">Harga Online</span><span
                                                    class="font-bold text-slate-800">{{ formatCurrency(e.hargaOnline) }}</span></div>
                                            <div
                                                class="flex justify-between text-[11px] border-t border-slate-200 pt-1.5 mt-1.5">
                                                <span class="text-slate-600 font-semibold">Avg Admin %</span><span
                                                    class="font-bold text-ppp-accent">{{ e.avgAdminPct }}%</span></div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- B3: Warranty Claim Tracker -->
                            <section class="section-card section-card-body">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="type-title font-bold text-slate-800"><i
                                            class="fa-solid fa-shield-halved text-ppp-accent mr-2"></i>Claim Garansi
                                        Tracker</h3>
                                    <span class="type-body text-slate-500">{{ claimGaransiStats.total }} total
                                        klaim</span>
                                </div>
                                <div v-if="!claimGaransiStats.total"
                                    class="text-center py-8 text-[11px] text-slate-400">Belum ada data claim garansi
                                </div>
                                <div v-else class="space-y-5">
                                    <!-- Status summary -->
                                    <div class="grid grid-cols-3 md:grid-cols-5 gap-2">
                                        <div v-for="(count, status) in claimGaransiStats.statusCount" :key="status"
                                            class="bg-slate-50 rounded-xl p-2 text-center">
                                            <p class="text-[11px] font-bold text-slate-800">{{ count }}</p>
                                            <p class="text-[8px] text-slate-400 uppercase leading-tight mt-0.5">{{ status }}</p>
                                        </div>
                                    </div>
                                    <!-- Avg resolution -->
                                    <div v-if="claimGaransiStats.avgDays"
                                        class="bg-amber-50 border border-amber-100 rounded-xl p-3 flex items-center gap-3">
                                        <i class="fa-solid fa-clock text-amber-500"></i>
                                        <div>
                                            <p class="text-[12px] font-bold text-slate-800">{{ claimGaransiStats.avgDays }} hari</p>
                                            <p class="text-[10px] text-slate-500">Rata-rata waktu penyelesaian klaim</p>
                                        </div>
                                    </div>
                                    <!-- Top produk -->
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">
                                            Produk Terbanyak Klaim</p>
                                        <div class="space-y-1.5">
                                            <div v-for="(p, idx) in claimGaransiStats.topProduk.slice(0,5)" :key="idx"
                                                class="flex items-center gap-2">
                                                <span class="text-[10px] font-bold text-slate-300 w-4">{{ idx+1 }}</span>
                                                <div
                                                    class="flex-1 bg-slate-100 rounded-full h-4 relative overflow-hidden">
                                                    <div class="h-4 bg-rose-400 rounded-full"
                                                        :style="'width:' + Math.round(p.count/claimGaransiStats.topProduk[0].count*100) + '%'">
                                                    </div>
                                                    <span
                                                        class="absolute inset-0 flex items-center px-2 text-[9px] font-bold text-white">{{ p.label }}</span>
                                                </div>
                                                <span class="text-[10px] font-bold text-slate-600 w-5 text-right">{{ p.count }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Lokasi -->
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">
                                            Klaim per Lokasi</p>
                                        <div class="flex flex-wrap gap-2">
                                            <span v-for="l in claimGaransiStats.topLokasi" :key="l.lokasi"
                                                class="px-2.5 py-1 bg-slate-100 rounded-lg text-[10px] font-bold text-slate-700">{{ l.lokasi }} <span class="text-ppp-accent">{{ l.count }}</span></span>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </template>
                    </div>

                    <!-- Meta: Story IG -->
@endverbatim
