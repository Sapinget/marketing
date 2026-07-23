@verbatim
            <header
                class="dashboard-topbar h-12 md:h-16 bg-white border-b border-slate-200 flex items-center justify-between px-3 md:px-6 fixed top-0 right-0 z-50"
                :style="isMobileViewport ? { left: '0px' } : { left: isSidebarOpen ? '15rem' : '0px' }">
                <button @click="toggleSidebar" type="button"
                    class="w-10 h-10 text-slate-400 flex items-center justify-center hover:text-ppp-accent transition-colors"
                    aria-label="Toggle sidebar">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>

                <div class="flex items-center gap-2 md:gap-3 min-w-0">
                    <div class="active-team-chip active-team-chip--avatars-only" aria-label="Team aktif">
                        <div class="active-team-avatar-stack">
                            <div v-for="member in visibleActiveTeamUsers" :key="member.username || member.email || member.nama"
                                class="active-team-avatar-item">
                                <div class="active-team-avatar cursor-pointer"
                                    @click.stop="chatOpenConversation(member)" :title="'Chat dengan ' + (member?.nama || member?.username || 'User')">
                                    <img v-if="resolveAvatarUrl(member?.avatar_url)" :src="resolveAvatarUrl(member.avatar_url)"
                                        class="w-full h-full object-cover rounded-full" alt="Foto Team" />
                                    <span v-else>{{ ((member?.nama || member?.username || 'T')[0]) }}</span>
                                </div>
                                <span v-if="chatUnreadByUser?.[member.ID] > 0"
                                    class="absolute -top-1 -right-0.5 min-w-[14px] h-3.5 px-0.5 rounded-full bg-danger text-white text-[8px] font-bold flex items-center justify-center leading-none shadow-sm z-10">
                                    {{ chatUnreadByUser[member.ID] > 9 ? '9+' : chatUnreadByUser[member.ID] }}
                                </span>
                            </div>
                            <div v-if="hiddenActiveTeamUsersCount > 0" class="active-team-avatar-item">
                                <div class="active-team-avatar active-team-avatar--count"
                                    :title="hiddenActiveTeamUsersCount + ' user online lainnya'">
                                    +{{ hiddenActiveTeamUsersCount > 9 ? '9' : hiddenActiveTeamUsersCount }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Dropdown -->
                    <div class="relative" id="profile-menu-wrapper">
                    <button @click="profileMenuOpen = !profileMenuOpen"
                        class="flex items-center gap-2.5 px-2 py-1.5 transition-colors group" id="profile-menu-btn">
                        <div
                            class="w-8 h-8 rounded-full bg-ppp-accent text-white flex items-center justify-center text-body font-semibold uppercase flex-shrink-0 overflow-hidden">
                            <img v-if="currentUser?.avatar_url" :src="currentUser.avatar_url" class="w-full h-full object-cover" alt="Foto Profil" />
                            <span v-else>{{ ((currentUser?.nama || currentUser?.username || 'U')[0]) }}</span></div>
                        <div class="hidden sm:block text-left">
                            <div
                                class="text-body font-semibold text-slate-800 group-hover:text-ppp-accent leading-tight transition-colors">
                                {{ currentUser?.nama || currentUser?.username || 'User' }}</div>
                            <div class="type-micro text-slate-400 uppercase">{{ currentUser?.role || '-' }}</div>
                        </div>
                        <i class="fa-solid fa-chevron-down text-overline-xs text-slate-400 hidden sm:block transition-transform duration-200"
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
                            class="absolute right-0 top-[calc(100%+18px)] w-52 bg-white rounded-2xl border border-slate-200 z-[200] overflow-hidden"
                            id="profile-dropdown">
                            <!-- Menu Items -->
                            <div class="py-1.5">
                                <button @click="chatOpenPicker(); profileMenuOpen = false"
                                    class="w-full flex items-center justify-between gap-3 px-4 py-2.5 text-left hover:bg-slate-50 transition-colors group"
                                    id="btn-profile-chat">
                                    <div>
                                        <div class="text-body font-medium text-slate-700">Chat</div>
                                        <div class="text-overline text-slate-400">Lihat user online & pesan baru</div>
                                    </div>
                                    <span v-if="chatUnreadTotal > 0"
                                        class="min-w-[18px] h-[18px] px-1 rounded-full bg-danger text-white text-[9px] font-bold flex items-center justify-center leading-none shrink-0">
                                        {{ chatUnreadTotal > 99 ? '99+' : chatUnreadTotal }}
                                    </span>
                                </button>
                                <button @click="openProfileSetting"
                                    class="w-full px-4 py-2.5 text-left hover:bg-slate-50 transition-colors group"
                                    id="btn-profile-setting">
                                    <div>
                                        <div class="text-body font-medium text-slate-700">Profile Setting</div>
                                        <div class="text-overline text-slate-400">Ubah data & PIN akses</div>
                                    </div>
                                </button>
                            </div>

                            <!-- Divider + Logout -->
                            <div class="border-t border-slate-100 py-1.5">
                                <button @click="logout"
                                    class="w-full px-4 py-2.5 text-left text-danger hover:text-white hover:bg-danger transition-colors"
                                    id="btn-dropdown-logout">
                                    <div class="text-body font-medium">Logout</div>
                                </button>
                            </div>
                        </div>
                    </transition>
                    </div>
                </div>
            </header>
@endverbatim
