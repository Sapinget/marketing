@verbatim
                <div v-if="!isTeknisi" class="px-6 pb-1 pt-2.5 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-400">Customer Service</div>
                <div v-if="!isTeknisi" @click="switchTab('orderan_online')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'orderan_online' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-cart-shopping text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Order Online</span>
                    <span v-if="activeTab === 'orderan_online'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div v-if="!isTeknisi" @click="switchTab('unit_ditanya')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'unit_ditanya' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-circle-question text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Unit Ditanya</span>
                    <span v-if="activeTab === 'unit_ditanya'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div v-if="!isTeknisi" @click="switchTab('claim_garansi_asuransi')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'claim_garansi_asuransi' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-shield-heart text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Claim Garansi</span>
                    <span v-if="activeTab === 'claim_garansi_asuransi'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div v-if="!isTeknisi" @click="switchTab('keep_barang')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'keep_barang' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-box-archive text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Keep Barang</span>
                    <span v-if="activeTab === 'keep_barang'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
@endverbatim
