<!-- hidden: Intelijen Pasar
@verbatim
                <div class="px-6 pb-1 pt-2.5 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-300">Intelijen Pasar</div>
                <div @click="switchTab('market_pasar')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'market_pasar' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-cart-shopping text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Pasar</span>
                    <span v-if="activeTab === 'market_pasar'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div @click="switchTab('market_intelijen_harga')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'market_intelijen_harga' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-tag text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Intelijen Harga</span>
                    <span v-if="activeTab === 'market_intelijen_harga'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div @click="switchTab('market_audit_harga')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'market_audit_harga' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-magnifying-glass-dollar text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Audit Harga</span>
                    <span v-if="activeTab === 'market_audit_harga'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div @click="switchTab('market_eksternal')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'market_eksternal' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-chart-simple text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Semua Kompetitor</span>
                    <span v-if="activeTab === 'market_eksternal'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div @click="switchTab('market_ext_goodponsel')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'market_ext_goodponsel' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-circle-dot text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Good Ponsel</span>
                    <span v-if="activeTab === 'market_ext_goodponsel'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div @click="switchTab('market_ext_devstore')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'market_ext_devstore' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-circle-dot text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Devstore</span>
                    <span v-if="activeTab === 'market_ext_devstore'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div @click="switchTab('market_ext_rumahgadget')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'market_ext_rumahgadget' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-circle-dot text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Rumah Gadget Bali</span>
                    <span v-if="activeTab === 'market_ext_rumahgadget'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
@endverbatim
-->
