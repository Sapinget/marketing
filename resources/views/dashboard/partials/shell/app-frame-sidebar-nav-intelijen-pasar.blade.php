@verbatim
                <!-- Intelijen Pasar Accordion -->
                <div v-if="!isTeknisi" class="select-none">
                    <div @click="toggleMenuGroup('intelijenPasar')"
                        :class="['flex items-center justify-between px-5 py-3 cursor-pointer group transition-all duration-300', ['market_pasar','market_intelijen_harga','market_audit_harga','market_eksternal','market_ext_goodponsel','market_ext_devstore','market_ext_rumahgadget'].includes(activeTab) ? 'nav-accordion-active' : 'nav-idle']">
                        <div class="flex items-center gap-3">
                            <i
                                class="fa-solid fa-store text-body w-4 text-center transition-transform duration-300 group-hover:scale-110"></i>
                            <span class="type-body font-medium">Intelijen Pasar</span>
                        </div>
                        <i
                            :class="['fa-solid fa-chevron-down text-body-sm transition-transform duration-300', intelijenPasarOpen ? 'rotate-180' : '']"></i>
                    </div>
                    <transition name="sidebar-accordion">
                        <div v-show="intelijenPasarOpen" class="sidebar-accordion-panel">
                            <div @click="switchTab('market_pasar')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'market_pasar' ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']">
                                <div v-if="activeTab === 'market_pasar'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-cart-shopping text-body w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-body-sm font-medium relative z-10">Pasar</span>
                            </div>
                            <div @click="switchTab('market_intelijen_harga')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'market_intelijen_harga' ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']">
                                <div v-if="activeTab === 'market_intelijen_harga'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-tag text-body w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-body-sm font-medium relative z-10">Intelijen Harga</span>
                            </div>
                            <div @click="switchTab('market_audit_harga')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'market_audit_harga' ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']">
                                <div v-if="activeTab === 'market_audit_harga'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-magnifying-glass-dollar text-body w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-body-sm font-medium relative z-10">Audit Harga</span>
                            </div>
                            <div @click="marketExternalOpen = !marketExternalOpen"
                                :class="['flex items-center justify-between gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', marketExternalTabs.includes(activeTab) ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']"
                                :aria-expanded="marketExternalOpen ? 'true' : 'false'">
                                <div v-if="marketExternalTabs.includes(activeTab)"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <span class="flex items-center gap-3 min-w-0 relative z-10">
                                    <i
                                        class="fa-solid fa-globe text-body w-3.5 text-center transition-transform duration-300 group-hover:scale-110"></i>
                                    <span class="type-body-sm font-medium">Kompetitor</span>
                                </span>
                                <i
                                    :class="['fa-solid fa-chevron-down text-body-sm relative z-10 transition-transform duration-300', marketExternalOpen ? 'rotate-180' : '']"></i>
                            </div>
                            <transition name="sidebar-accordion">
                                <div v-show="marketExternalOpen" class="pl-14">
                                    <div class="border-l border-slate-200/80 pl-3">
                                        <div @click="switchTab('market_eksternal')"
                                            :class="['flex items-center gap-2 pr-5 py-2 cursor-pointer group transition-all duration-200', activeTab === 'market_eksternal' ? 'sidebar-sub-nav-item-active font-semibold' : 'nav-idle']">
                                            <i class="fa-solid fa-chart-simple text-[9px] w-3 text-center"></i>
                                            <span class="type-body-sm">Semua Kompetitor</span>
                                        </div>
                                        <div @click="switchTab('market_ext_goodponsel')"
                                            :class="['flex items-center gap-2 pr-5 py-2 cursor-pointer group transition-all duration-200', activeTab === 'market_ext_goodponsel' ? 'sidebar-sub-nav-item-active font-semibold' : 'nav-idle']">
                                            <i class="fa-solid fa-circle-dot text-[9px] w-3 text-center"></i>
                                            <span class="type-body-sm">Good Ponsel</span>
                                        </div>
                                        <div @click="switchTab('market_ext_devstore')"
                                            :class="['flex items-center gap-2 pr-5 py-2 cursor-pointer group transition-all duration-200', activeTab === 'market_ext_devstore' ? 'sidebar-sub-nav-item-active font-semibold' : 'nav-idle']">
                                            <i class="fa-solid fa-circle-dot text-[9px] w-3 text-center"></i>
                                            <span class="type-body-sm">Devstore</span>
                                        </div>
                                        <div @click="switchTab('market_ext_rumahgadget')"
                                            :class="['flex items-center gap-2 pr-5 py-2 cursor-pointer group transition-all duration-200', activeTab === 'market_ext_rumahgadget' ? 'sidebar-sub-nav-item-active font-semibold' : 'nav-idle']">
                                            <i class="fa-solid fa-circle-dot text-[9px] w-3 text-center"></i>
                                            <span class="type-body-sm">Rumah Gadget Bali</span>
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </transition>
                </div>
@endverbatim
