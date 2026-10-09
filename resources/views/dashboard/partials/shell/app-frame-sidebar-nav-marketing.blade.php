@verbatim
                <div class="px-6 pb-1 pt-2.5 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-300">Market</div>
                <div @click="switchTab('program_promo')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'program_promo' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-bullhorn text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Program Promo</span>
                    <span v-if="activeTab === 'program_promo'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <a href="/promo-pamflet" @click="switchTab('promo_pamflet')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3', activeTab === 'promo_pamflet' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-image text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Promo Pamflet</span>
                    <span v-if="activeTab === 'promo_pamflet'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </a>
                <div @click="switchTab('sell_out')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'sell_out' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-arrow-trend-up text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Sell Out Target</span>
                    <span v-if="activeTab === 'sell_out'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div @click="switchTab('ads_log')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'ads_log' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-rectangle-ad text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Ads Log</span>
                    <span v-if="activeTab === 'ads_log'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div @click="switchTab('budgeting')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'budgeting' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-wallet text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Budgeting</span>
                    <span v-if="activeTab === 'budgeting'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
@endverbatim
