@verbatim
                <div class="px-6 pb-1 pt-2.5 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-300">Complain Traker</div>
                <div @click="switchTab('input_claim')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'input_claim' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-file-pen text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Input Claim</span>
                    <span v-if="activeTab === 'input_claim'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div @click="switchTab('garansi_cermati')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'garansi_cermati' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-shield-halved text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Garansi Cermati</span>
                    <span v-if="activeTab === 'garansi_cermati'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div @click="switchTab('garansi_resmi')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'garansi_resmi' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-screwdriver-wrench text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Garansi Resmi</span>
                    <span v-if="activeTab === 'garansi_resmi'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
@endverbatim
