@verbatim
                <!-- Customer Service Accordion (non-Teknisi) -->
                <div v-if="!isTeknisi" class="select-none">
                    <div @click="toggleMenuGroup('cs')"
                        :class="['flex items-center justify-between px-5 py-3 cursor-pointer group transition-all duration-300', ['orderan_online','unit_ditanya','claim_garansi_asuransi','keep_barang'].includes(activeTab) ? 'nav-accordion-active' : 'nav-idle']">
                        <div class="flex items-center gap-3">
                            <i
                                class="fa-solid fa-headset text-body w-4 text-center transition-transform duration-300 group-hover:scale-110"></i>
                            <span class="type-body font-medium">Customer Service</span>
                        </div>
                        <i
                            :class="['fa-solid fa-chevron-down text-body-sm transition-transform duration-300', csOpen ? 'rotate-180' : '']"></i>
                    </div>
                    <transition name="sidebar-accordion">
                        <div v-show="csOpen" class="sidebar-accordion-panel">
                            <div @click="switchTab('orderan_online')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'orderan_online' ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']">
                                <div v-if="activeTab === 'orderan_online'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-cart-shopping text-body w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-body-sm font-medium relative z-10">Order Online</span>
                            </div>
                            <div @click="switchTab('unit_ditanya')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'unit_ditanya' ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']">
                                <div v-if="activeTab === 'unit_ditanya'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-circle-question text-body w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-body-sm font-medium relative z-10">Unit Ditanya</span>
                            </div>
                            <div @click="switchTab('claim_garansi_asuransi')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'claim_garansi_asuransi' ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']">
                                <div v-if="activeTab === 'claim_garansi_asuransi'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-shield-heart text-body w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-body-sm font-medium relative z-10">Claim Garansi</span>
                            </div>
                            <div @click="switchTab('keep_barang')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'keep_barang' ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']">
                                <div v-if="activeTab === 'keep_barang'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-box-archive text-body w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-body-sm font-medium relative z-10">Keep Barang</span>
                            </div>
                        </div>
                    </transition>
                </div>
@endverbatim
