@verbatim
            <header
                class="h-12 md:h-16 bg-white border-b border-slate-200 flex items-center justify-between px-3 md:px-6 sticky top-0 z-50">
                <button @click="toggleSidebar" type="button"
                    class="w-10 h-10 text-slate-400 flex items-center justify-center hover:text-ppp-accent transition-colors"
                    aria-label="Toggle sidebar">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>

                <!-- Profile Dropdown -->
                <div class="relative" id="profile-menu-wrapper">
                    <button @click="profileMenuOpen = !profileMenuOpen"
                        class="flex items-center gap-2.5 px-2 py-1.5 transition-colors group" id="profile-menu-btn">
                        <div
                            class="w-8 h-8 rounded-full bg-ppp-accent text-white flex items-center justify-center text-[11px] font-semibold uppercase flex-shrink-0">
                            {{ ((currentUser?.nama || currentUser?.username || 'U')[0]) }}</div>
                        <div class="hidden sm:block text-left">
                            <div
                                class="text-[11px] font-semibold text-slate-800 group-hover:text-ppp-accent leading-tight transition-colors">
                                {{ currentUser?.nama || currentUser?.username || 'User' }}</div>
                            <div class="text-[9px] text-slate-400 uppercase tracking-widest leading-tight mt-0.5">{{ currentUser?.role || '-' }}</div>
                        </div>
                        <i class="fa-solid fa-chevron-down text-[8px] text-slate-400 hidden sm:block transition-transform duration-200"
                            :class="profileMenuOpen ? 'rotate-180' : ''"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <transition enter-active-class="transition duration-150 ease-out"
                        enter-from-class="opacity-0 scale-95 -translate-y-1"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                        leave-active-class="transition duration-100 ease-in"
                        leave-from-class="opacity-100 scale-100 translate-y-0"
                        leave-to-class="opacity-0 scale-95 -translate-y-1">
                        <div v-if="profileMenuOpen"
                            class="absolute right-0 top-[calc(100%+18px)] w-52 bg-white rounded-2xl border border-slate-200 shadow-xl shadow-slate-200/60 z-[200] overflow-hidden"
                            id="profile-dropdown">
                            <!-- Menu Items -->
                            <div class="py-1.5">
                                <button @click="openProfileSetting"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 text-left hover:bg-slate-50 transition-colors group"
                                    id="btn-profile-setting">
                                    <div
                                        class="w-7 h-7 rounded-xl bg-blue-50 text-ppp-accent flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-user text-[10px]"></i>
                                    </div>
                                    <div>
                                        <div class="text-[11px] font-medium text-slate-700">Profile Setting</div>
                                        <div class="text-[9px] text-slate-400">Ubah data & PIN akses</div>
                                    </div>
                                </button>
                            </div>

                            <!-- Divider + Logout -->
                            <div class="border-t border-slate-100 py-1.5">
                                <button @click="logout"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 text-left hover:bg-red-50 transition-colors group"
                                    id="btn-dropdown-logout">
                                    <div
                                        class="w-7 h-7 rounded-xl bg-red-50 text-red-500 flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-right-from-bracket text-[10px]"></i>
                                    </div>
                                    <div class="text-[11px] font-medium text-red-500">Logout</div>
                                </button>
                            </div>
                        </div>
                    </transition>
                </div>
            </header>
@endverbatim
