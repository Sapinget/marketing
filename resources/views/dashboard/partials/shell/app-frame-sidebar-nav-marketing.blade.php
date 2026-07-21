@verbatim
                <!-- Marketing Accordion -->
                <div v-if="!isTeknisi" class="select-none">
                    <div @click="toggleMenuGroup('marketing')"
                        :class="['flex items-center justify-between px-5 py-3 cursor-pointer group transition-all duration-300', ['program_promo','sell_out','ads_log','budgeting'].includes(activeTab) ? 'nav-accordion-active' : 'nav-idle']">
                        <div class="flex items-center gap-3">
                            <i
                                class="fa-solid fa-bullseye text-body w-4 text-center transition-transform duration-300 group-hover:scale-110"></i>
                            <span class="type-body font-medium tracking-wide">Market</span>
                        </div>
                        <i
                            :class="['fa-solid fa-chevron-down text-body-sm transition-transform duration-300', marketingOpen ? 'rotate-180' : '']"></i>
                    </div>
                    <transition name="sidebar-accordion">
                        <div v-show="marketingOpen" class="sidebar-accordion-panel">
                            <div @click="switchTab('program_promo')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'program_promo' ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']">
                                <div v-if="activeTab === 'program_promo'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-bullhorn text-body w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-body-sm font-medium tracking-wide relative z-10">Program Promo</span>
                            </div>
                            <div @click="switchTab('sell_out')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'sell_out' ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']">
                                <div v-if="activeTab === 'sell_out'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-arrow-trend-up text-body w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-body-sm font-medium tracking-wide relative z-10">Sell Out
                                    Target</span>
                            </div>
                            <div @click="switchTab('ads_log')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'ads_log' ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']">
                                <div v-if="activeTab === 'ads_log'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-rectangle-ad text-body w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-body-sm font-medium tracking-wide relative z-10">Ads Log</span>
                            </div>
                            <div @click="switchTab('budgeting')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'budgeting' ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']">
                                <div v-if="activeTab === 'budgeting'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-wallet text-body w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-body-sm font-medium tracking-wide relative z-10">Budgeting</span>
                            </div>
                        </div>
                    </transition>
                </div>
@endverbatim
