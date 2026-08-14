@verbatim
<!-- Analisa Insight & Tren View -->
                    <div v-if="activeTab === 'analisa_insight'" class="space-y-6 animate-fadeIn pb-10">
                        <div class="section-card section-card-shell overflow-hidden">
                            <div class="table-toolbar-shell border-b border-slate-50">
                                <div class="table-toolbar-shell__left">
                                    <div class="flex items-center gap-3 px-6 py-4">
                                        <div class="w-11 h-11 rounded-2xl bg-amber text-light flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-microscope text-heading-sm"></i>
                                        </div>
                                        <div>
                                            <h2 class="type-title font-semibold text-slate-900">Insight & Tren</h2>
                                            <p class="type-body-sm text-slate-400">Analisa lintas konten, sales, dan customer service</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-toolbar-shell__right px-6 py-4">
                                    <button @click="openCalendar($event, 'filter', '', 'insight')" class="select-trigger-button-compact">
                                        <i class="fa-solid fa-calendar-days text-body-sm text-slate-400"></i>
                                        <template v-if="insightDateFilter.start">
                                            {{ formatShortDate(insightDateFilter.start) }}
                                            <span v-if="insightDateFilter.end"> - {{ formatShortDate(insightDateFilter.end) }}</span>
                                        </template>
                                        <template v-else>Semua Tanggal</template>
                                        <i v-if="insightDateFilter.start"
                                            @click.stop="insightDateFilter = { start: '', end: '' }"
                                            class="fa-solid fa-circle-xmark ml-auto text-slate-300 hover:text-danger"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 px-6 py-3 overflow-x-auto border-b border-slate-50">
                                <button @click="analisaInsightTab = 'konten'"
                                    :class="['toolbar-tab-button border-transparent', analisaInsightTab === 'konten' ? 'bg-ppp-accent text-light shadow-sm' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-50']">
                                    Konten
                                </button>
                                <button @click="analisaInsightTab = 'sales'"
                                    :class="['toolbar-tab-button border-transparent', analisaInsightTab === 'sales' ? 'bg-ppp-accent text-light shadow-sm' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-50']">
                                    Sales & CS
                                </button>
                            </div>
                        </div>

                        <template v-if="analisaInsightTab === 'konten'">
                            <div class="dashboard-summary-grid-compact grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                                <div class="dashboard-summary-card-compact stat-card">
                                    <p class="dashboard-summary-title">Konten Colab</p>
                                    <div class="flex items-baseline gap-2">
                                        <span class="dashboard-summary-value">{{ colabVsNonColabStats?.colab?.count || 0 }}</span>
                                        <span class="dashboard-summary-unit">konten</span>
                                    </div>
                                    <p class="text-body-sm font-bold mt-3 text-success">Avg {{ (colabVsNonColabStats?.colab?.avgViews || 0).toLocaleString('id-ID') }} views</p>
                                </div>
                                <div class="dashboard-summary-card-compact stat-card">
                                    <p class="dashboard-summary-title">Konten Non-Colab</p>
                                    <div class="flex items-baseline gap-2">
                                        <span class="dashboard-summary-value">{{ colabVsNonColabStats?.nonColab?.count || 0 }}</span>
                                        <span class="dashboard-summary-unit">konten</span>
                                    </div>
                                    <p class="text-body-sm font-bold mt-3 text-ppp-accent">Avg {{ (colabVsNonColabStats?.nonColab?.avgViews || 0).toLocaleString('id-ID') }} views</p>
                                </div>
                                <div class="dashboard-summary-card-compact stat-card">
                                    <p class="dashboard-summary-title">Total Konten Funnel</p>
                                    <div class="flex items-baseline gap-2">
                                        <span class="dashboard-summary-value">{{ masterPlanData.length }}</span>
                                        <span class="dashboard-summary-unit">item</span>
                                    </div>
                                    <p class="text-body-sm font-bold mt-3 text-slate-500">{{ contentFunnelStats.length }} tahap aktif</p>
                                </div>
                                <div class="dashboard-summary-card-compact stat-card">
                                    <p class="dashboard-summary-title">Bulan Tren</p>
                                    <div class="flex items-baseline gap-2">
                                        <span class="dashboard-summary-value">{{ monthlyTrendData.length }}</span>
                                        <span class="dashboard-summary-unit">bulan</span>
                                    </div>
                                    <p class="text-body-sm font-bold mt-3 text-slate-500">{{ monthlyTrendData.length ? monthlyTrendData.reduce((s,m)=>s+m.views,0).toLocaleString('id-ID') : 0 }} total views</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                                <section class="section-card section-card-shell overflow-hidden">
                                    <div class="px-6 py-4 border-b border-slate-50 flex items-center justify-between">
                                        <h3 class="type-title font-semibold text-slate-900">Colab vs Non-Colab</h3>
                                        <span class="type-body-sm text-slate-400">Perbandingan performa</span>
                                    </div>
                                    <div v-if="!colabVsNonColabStats || (!colabVsNonColabStats.colab.count && !colabVsNonColabStats.nonColab.count)"
                                        class="table-empty-state text-slate-400 text-body uppercase">Belum ada data analytics</div>
                                    <div v-else class="p-6 space-y-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="surface-panel-soft border border-success/20">
                                                <div class="flex items-center justify-between mb-3">
                                                    <span class="px-2.5 py-1 rounded-lg bg-success text-light text-overline font-bold uppercase">Colab</span>
                                                    <span class="type-body-sm text-slate-400">{{ colabVsNonColabStats.colab.count }} konten</span>
                                                </div>
                                                <div class="space-y-2">
                                                    <div class="flex items-center justify-between text-body"><span class="text-slate-500">Avg Views</span><span class="font-bold text-slate-800">{{ (colabVsNonColabStats.colab.avgViews || 0).toLocaleString('id-ID') }}</span></div>
                                                    <div class="flex items-center justify-between text-body"><span class="text-slate-500">Avg Likes</span><span class="font-bold text-slate-800">{{ (colabVsNonColabStats.colab.avgLikes || 0).toLocaleString('id-ID') }}</span></div>
                                                    <div class="flex items-center justify-between text-body"><span class="text-slate-500">Avg Comments</span><span class="font-bold text-slate-800">{{ (colabVsNonColabStats.colab.avgComments || 0).toLocaleString('id-ID') }}</span></div>
                                                    <div class="flex items-center justify-between text-body pt-2 border-t border-slate-100"><span class="text-slate-500">Engagement Score</span><span class="font-bold text-success">{{ (colabVsNonColabStats.colab.avgScore || 0).toLocaleString('id-ID') }}</span></div>
                                                </div>
                                            </div>
                                            <div class="surface-panel-soft">
                                                <div class="flex items-center justify-between mb-3">
                                                    <span class="px-2.5 py-1 rounded-lg bg-slate-400 text-light text-overline font-bold uppercase">Non-Colab</span>
                                                    <span class="type-body-sm text-slate-400">{{ colabVsNonColabStats.nonColab.count }} konten</span>
                                                </div>
                                                <div class="space-y-2">
                                                    <div class="flex items-center justify-between text-body"><span class="text-slate-500">Avg Views</span><span class="font-bold text-slate-800">{{ (colabVsNonColabStats.nonColab.avgViews || 0).toLocaleString('id-ID') }}</span></div>
                                                    <div class="flex items-center justify-between text-body"><span class="text-slate-500">Avg Likes</span><span class="font-bold text-slate-800">{{ (colabVsNonColabStats.nonColab.avgLikes || 0).toLocaleString('id-ID') }}</span></div>
                                                    <div class="flex items-center justify-between text-body"><span class="text-slate-500">Avg Comments</span><span class="font-bold text-slate-800">{{ (colabVsNonColabStats.nonColab.avgComments || 0).toLocaleString('id-ID') }}</span></div>
                                                    <div class="flex items-center justify-between text-body pt-2 border-t border-slate-100"><span class="text-slate-500">Engagement Score</span><span class="font-bold text-ppp-accent">{{ (colabVsNonColabStats.nonColab.avgScore || 0).toLocaleString('id-ID') }}</span></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="surface-panel-soft space-y-3">
                                            <div>
                                                <div class="flex items-center justify-between text-body-sm text-slate-500 mb-1"><span>Colab</span><span>{{ (colabVsNonColabStats.colab.avgViews || 0).toLocaleString('id-ID') }}</span></div>
                                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                                    <div class="bg-success h-2 rounded-full" :style="'width:' + (Math.max(colabVsNonColabStats.colab.avgViews, colabVsNonColabStats.nonColab.avgViews) > 0 ? Math.round(colabVsNonColabStats.colab.avgViews / Math.max(colabVsNonColabStats.colab.avgViews, colabVsNonColabStats.nonColab.avgViews) * 100) : 0) + '%'"></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="flex items-center justify-between text-body-sm text-slate-500 mb-1"><span>Non-Colab</span><span>{{ (colabVsNonColabStats.nonColab.avgViews || 0).toLocaleString('id-ID') }}</span></div>
                                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                                    <div class="bg-ppp-accent h-2 rounded-full" :style="'width:' + (Math.max(colabVsNonColabStats.colab.avgViews, colabVsNonColabStats.nonColab.avgViews) > 0 ? Math.round(colabVsNonColabStats.nonColab.avgViews / Math.max(colabVsNonColabStats.colab.avgViews, colabVsNonColabStats.nonColab.avgViews) * 100) : 0) + '%'"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <section class="section-card section-card-shell overflow-hidden">
                                    <div class="px-6 py-4 border-b border-slate-50 flex items-center justify-between">
                                        <h3 class="type-title font-semibold text-slate-900">Content Funnel</h3>
                                        <span class="type-body-sm text-slate-400">{{ masterPlanData.length }} total konten</span>
                                    </div>
                                    <div v-if="!masterPlanData.length" class="table-empty-state text-slate-400 text-body uppercase">Belum ada data master konten</div>
                                    <div v-else class="p-6 space-y-3">
                                        <div v-for="item in contentFunnelStats" :key="item.stage" class="surface-panel-soft">
                                            <div class="flex items-center justify-between gap-3 mb-2">
                                                <span class="text-body-sm font-bold text-slate-700 uppercase">{{ item.stage }}</span>
                                                <span class="text-body-sm font-bold text-slate-400">{{ item.count }} <span class="font-normal">({{ item.pct }}%)</span></span>
                                            </div>
                                            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                                <div class="h-2.5 rounded-full bg-ppp-accent transition-all duration-500" :style="'width:' + item.pct + '%'"></div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <section class="section-card section-card-shell overflow-hidden">
                                <div class="px-6 py-4 border-b border-slate-50 flex items-center justify-between">
                                    <h3 class="type-title font-semibold text-slate-900">Tren Bulanan Views</h3>
                                    <span class="type-body-sm text-slate-400">{{ monthlyTrendData.length }} bulan</span>
                                </div>
                                <div v-if="!monthlyTrendData.length" class="table-empty-state text-slate-400 text-body uppercase">Belum ada data analytics</div>
                                <div v-else class="p-6 space-y-5">
                                    <div class="surface-panel-soft">
                                        <div class="flex items-end gap-2 h-40">
                                            <div v-for="m in monthlyTrendData" :key="m.ym" class="flex-1 flex flex-col items-center gap-2 group min-w-0">
                                                <div class="relative w-full flex items-end justify-center h-28">
                                                    <div class="w-full max-w-[52px] rounded-t-xl bg-gradient-to-t from-ppp-accent to-amber transition-all duration-500 group-hover:opacity-80"
                                                        :style="'height:' + (monthlyTrendData.reduce((mx,x)=>Math.max(mx,x.views),1)>0 ? Math.max(8, Math.round(m.views/monthlyTrendData.reduce((mx,x)=>Math.max(mx,x.views),1)*112)) : 8) + 'px'"></div>
                                                    <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-900 text-light text-overline-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap transition-opacity">
                                                        {{ m.views.toLocaleString('id-ID') }} views
                                                    </div>
                                                </div>
                                                <span class="text-overline text-slate-400">{{ m.ym.substring(5) }}/{{ m.ym.substring(2,4) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="dashboard-summary-grid-compact grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <div class="dashboard-summary-card-compact stat-card">
                                            <p class="dashboard-summary-title">Total Views</p>
                                            <div class="flex items-baseline gap-2"><span class="dashboard-summary-value">{{ monthlyTrendData.reduce((s,m)=>s+m.views,0).toLocaleString('id-ID') }}</span></div>
                                        </div>
                                        <div class="dashboard-summary-card-compact stat-card">
                                            <p class="dashboard-summary-title">Total Konten</p>
                                            <div class="flex items-baseline gap-2"><span class="dashboard-summary-value">{{ monthlyTrendData.reduce((s,m)=>s+m.count,0) }}</span></div>
                                        </div>
                                        <div class="dashboard-summary-card-compact stat-card">
                                            <p class="dashboard-summary-title">Avg Views / Bulan</p>
                                            <div class="flex items-baseline gap-2"><span class="dashboard-summary-value">{{ monthlyTrendData.length ? Math.round(monthlyTrendData.reduce((s,m)=>s+m.views,0)/monthlyTrendData.length).toLocaleString('id-ID') : '-' }}</span></div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </template>

                        <template v-if="analisaInsightTab === 'sales'">
                            <div class="dashboard-summary-grid-compact grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                                <div class="dashboard-summary-card-compact stat-card">
                                    <p class="dashboard-summary-title">Produk Ditanya</p>
                                    <div class="flex items-baseline gap-2"><span class="dashboard-summary-value">{{ productDemandStats.length }}</span><span class="dashboard-summary-unit">item</span></div>
                                    <p class="text-body-sm font-bold mt-3 text-slate-500">Top demand unit</p>
                                </div>
                                <div class="dashboard-summary-card-compact stat-card">
                                    <p class="dashboard-summary-title">Platform Order</p>
                                    <div class="flex items-baseline gap-2"><span class="dashboard-summary-value">{{ orderOnlineSummary.length }}</span><span class="dashboard-summary-unit">platform</span></div>
                                    <p class="text-body-sm font-bold mt-3 text-slate-500">Ringkasan order online</p>
                                </div>
                                <div class="dashboard-summary-card-compact stat-card">
                                    <p class="dashboard-summary-title">Total Klaim</p>
                                    <div class="flex items-baseline gap-2"><span class="dashboard-summary-value">{{ claimGaransiStats.total || 0 }}</span><span class="dashboard-summary-unit">klaim</span></div>
                                    <p class="text-body-sm font-bold mt-3 text-slate-500">Garansi aktif</p>
                                </div>
                                <div class="dashboard-summary-card-compact stat-card">
                                    <p class="dashboard-summary-title">Avg Waktu Klaim</p>
                                    <div class="flex items-baseline gap-2"><span class="dashboard-summary-value">{{ claimGaransiStats.avgDays || 0 }}</span><span class="dashboard-summary-unit">hari</span></div>
                                    <p class="text-body-sm font-bold mt-3 text-slate-500">Penyelesaian rata-rata</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                                <section class="section-card section-card-shell overflow-hidden">
                                    <div class="px-6 py-4 border-b border-slate-50 flex items-center justify-between">
                                        <h3 class="type-title font-semibold text-slate-900">Produk Paling Banyak Ditanya</h3>
                                        <span class="type-body-sm text-slate-400">Top 20</span>
                                    </div>
                                    <div v-if="!productDemandStats.length" class="table-empty-state text-slate-400 text-body uppercase">Belum ada data unit ditanya</div>
                                    <div v-else class="p-6 space-y-3">
                                        <div v-for="(p, idx) in productDemandStats" :key="idx" class="surface-panel-soft">
                                            <div class="flex items-start justify-between gap-3 mb-2">
                                                <div class="flex items-start gap-3 min-w-0">
                                                    <span class="text-body-sm font-bold text-slate-300 w-5 shrink-0">{{ idx + 1 }}</span>
                                                    <div class="min-w-0">
                                                        <p class="text-body font-bold text-slate-700 uppercase leading-tight break-words">{{ p.brand }}</p>
                                                        <p class="text-body-sm text-slate-400 break-words">{{ p.seri }}</p>
                                                    </div>
                                                </div>
                                                <div class="text-right shrink-0">
                                                    <p class="text-body font-bold text-slate-800">{{ p.total }}</p>
                                                    <p class="text-overline text-slate-400 uppercase">ditanya</p>
                                                </div>
                                            </div>
                                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden mb-2">
                                                <div class="bg-ppp-accent h-2 rounded-full" :style="'width:' + Math.round(p.total / productDemandStats[0].total * 100) + '%'"></div>
                                            </div>
                                            <div class="flex justify-end gap-1 flex-wrap">
                                                <span v-if="p.available" class="px-2 py-1 bg-success text-light text-overline font-bold rounded-lg">{{ p.available }} Ada</span>
                                                <span v-if="p.notAvailable" class="px-2 py-1 bg-danger text-light text-overline font-bold rounded-lg">{{ p.notAvailable }} Tdk</span>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <section class="section-card section-card-shell overflow-hidden">
                                    <div class="px-6 py-4 border-b border-slate-50 flex items-center justify-between">
                                        <h3 class="type-title font-semibold text-slate-900">Ringkasan Order Online</h3>
                                        <span class="type-body-sm text-slate-400">{{ orderOnlineSummary.length }} platform</span>
                                    </div>
                                    <div v-if="!orderOnlineSummary.length" class="table-empty-state text-slate-400 text-body uppercase">Belum ada data order online</div>
                                    <div v-else class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div v-for="e in orderOnlineSummary" :key="e.platform" class="surface-panel-soft">
                                            <div class="flex items-center justify-between mb-3">
                                                <p class="text-body font-bold text-slate-800 uppercase">{{ e.platform }}</p>
                                                <span class="text-overline text-slate-400">Platform</span>
                                            </div>
                                            <div class="space-y-2">
                                                <div class="flex justify-between text-body"><span class="text-slate-500">Total Order</span><span class="font-bold text-slate-800">{{ e.total }}</span></div>
                                                <div class="flex justify-between text-body"><span class="text-slate-500">Nominal Cair</span><span class="font-bold text-slate-800">{{ formatCurrency(e.nominalCair) }}</span></div>
                                                <div class="flex justify-between text-body"><span class="text-slate-500">Harga Online</span><span class="font-bold text-slate-800">{{ formatCurrency(e.hargaOnline) }}</span></div>
                                                <div class="flex justify-between text-body pt-2 border-t border-slate-100"><span class="text-slate-500">Avg Admin %</span><span class="font-bold text-ppp-accent">{{ e.avgAdminPct }}%</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <section class="section-card section-card-shell overflow-hidden">
                                <div class="px-6 py-4 border-b border-slate-50 flex items-center justify-between">
                                    <h3 class="type-title font-semibold text-slate-900">Claim Garansi Tracker</h3>
                                    <span class="type-body-sm text-slate-400">{{ claimGaransiStats.total }} total klaim</span>
                                </div>
                                <div v-if="!claimGaransiStats.total" class="table-empty-state text-slate-400 text-body uppercase">Belum ada data claim garansi</div>
                                <div v-else class="p-6 space-y-5">
                                    <div class="dashboard-summary-grid-compact grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-3">
                                        <div v-for="(count, status) in claimGaransiStats.statusCount" :key="status" class="dashboard-summary-card-compact stat-card">
                                            <p class="dashboard-summary-title">{{ status }}</p>
                                            <div class="flex items-baseline gap-2"><span class="dashboard-summary-value">{{ count }}</span></div>
                                        </div>
                                    </div>
                                    <div v-if="claimGaransiStats.avgDays" class="surface-panel-soft flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-2xl bg-amber/15 text-amber flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-clock"></i>
                                        </div>
                                        <div>
                                            <p class="text-body font-bold text-slate-800">{{ claimGaransiStats.avgDays }} hari</p>
                                            <p class="text-body-sm text-slate-400">Rata-rata waktu penyelesaian klaim</p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                                        <div class="surface-panel-soft">
                                            <p class="text-body-sm font-bold text-slate-400 uppercase mb-3">Produk Terbanyak Klaim</p>
                                            <div class="space-y-2">
                                                <div v-for="(p, idx) in claimGaransiStats.topProduk.slice(0,5)" :key="idx" class="flex items-center gap-3">
                                                    <span class="text-body-sm font-bold text-slate-300 w-4">{{ idx + 1 }}</span>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center justify-between text-body-sm mb-1 gap-2">
                                                            <span class="font-bold text-slate-700 truncate">{{ p.label }}</span>
                                                            <span class="text-slate-400 shrink-0">{{ p.count }}</span>
                                                        </div>
                                                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                                            <div class="bg-danger h-2 rounded-full" :style="'width:' + Math.round(p.count / claimGaransiStats.topProduk[0].count * 100) + '%'"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="surface-panel-soft">
                                            <p class="text-body-sm font-bold text-slate-400 uppercase mb-3">Klaim per Lokasi</p>
                                            <div class="flex flex-wrap gap-2">
                                                <span v-for="l in claimGaransiStats.topLokasi" :key="l.lokasi" class="px-2.5 py-1.5 bg-slate-100 rounded-lg text-body-sm font-bold text-slate-700">{{ l.lokasi }} <span class="text-danger">{{ l.count }}</span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </template>
                    </div>

                    <!-- Meta: Story IG -->
@endverbatim
