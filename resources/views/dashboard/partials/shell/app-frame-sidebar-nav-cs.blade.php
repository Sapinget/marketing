@verbatim
                <!-- Customer Service Accordion (non-Teknisi) -->
                <div v-if="!isTeknisi" class="select-none">
                    <div @click="toggleMenuGroup('cs')"
                        :class="['flex items-center justify-between px-5 py-3 cursor-pointer group transition-all duration-300', ['orderan_online','unit_ditanya','claim_garansi_asuransi','keep_barang'].includes(activeTab) ? 'text-ppp-accent' : 'text-slate-500 hover:bg-slate-50']">
                        <div class="flex items-center gap-3">
                            <i
                                class="fa-solid fa-headset text-[12px] w-4 text-center transition-transform duration-300 group-hover:scale-110"></i>
                            <span class="type-body font-medium tracking-wide">Customer Service</span>
                        </div>
                        <i
                            :class="['fa-solid fa-chevron-down text-[10px] transition-transform duration-300', csOpen ? 'rotate-180' : '']"></i>
                    </div>
                    <transition name="sidebar-accordion">
                        <div v-show="csOpen" class="sidebar-accordion-panel">
                            <div @click="switchTab('orderan_online')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'orderan_online' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'orderan_online'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-cart-shopping text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Order Online</span>
                            </div>
                            <div @click="switchTab('unit_ditanya')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'unit_ditanya' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'unit_ditanya'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-question-circle text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Unit Ditanya</span>
                            </div>
                            <div @click="switchTab('claim_garansi_asuransi')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'claim_garansi_asuransi' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'claim_garansi_asuransi'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-shield-heart text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Claim Garansi</span>
                            </div>
                            <div @click="switchTab('keep_barang')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'keep_barang' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'keep_barang'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-box-archive text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Keep Barang</span>
                            </div>
                        </div>
                    </transition>
                </div>
@endverbatim
