@verbatim
            <!-- Mini Chat Panel -->
            <div v-if="chatOpen && chatTargetUser"
                class="chat-backdrop glass-backdrop fixed inset-0 z-[299] transition-opacity"
                @click="chatClose" aria-hidden="true"></div>
            <div v-if="chatOpen && chatTargetUser"
                class="fixed inset-0 z-[300] md:inset-auto md:bottom-4 md:right-4 md:w-80 md:h-[440px] md:rounded-2xl bg-white border-t md:border border-slate-200 flex flex-col overflow-hidden transition-all duration-200"
                :class="isMobileViewport ? 'top-16 rounded-none' : ''">
                <!-- Header -->
                <div class="flex items-center gap-2 px-3 py-2.5 border-b border-slate-100 bg-white shrink-0">
                    <div
                        class="w-7 h-7 rounded-full bg-ppp-accent text-white flex items-center justify-center text-overline-xs font-bold uppercase shrink-0 overflow-hidden">
                        <img v-if="chatTargetUser.avatar_url" :src="chatTargetUser.avatar_url" class="w-full h-full object-cover" />
                        <span v-else>{{ ((chatTargetUser?.nama || chatTargetUser?.username || '?')[0]) }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-body-sm font-semibold text-slate-800 truncate">{{ chatTargetUser.nama || chatTargetUser.username }}</div>
                        <div v-if="chatPeerTyping" class="text-overline-xs text-slate-500 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber inline-block animate-pulse"></span>
                            <span>mengetik...</span>
                        </div>
                        <div v-else-if="chatTargetUser.is_online" class="text-overline-xs text-success flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-success inline-block"></span>
                            <span>Online</span>
                        </div>
                        <div v-else class="text-overline-xs text-slate-400 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300 inline-block"></span>
                            <span>Offline</span>
                        </div>
                    </div>
                    <button @click="chatClose" class="w-7 h-7 flex items-center justify-center text-slate-300 hover:text-slate-600 transition-colors">
                        <i class="fa-solid fa-xmark text-body-sm"></i>
                    </button>
                </div>
                <!-- Messages -->
                <div ref="chatContainer" class="flex-1 overflow-y-auto px-3 py-2 space-y-2 scroll-smooth bg-slate-50/50"
                    style="scroll-behavior:smooth;overscroll-behavior:contain;">
                    <div v-if="chatLoading" class="flex items-center justify-center py-8 text-slate-300">
                        <i class="fa-solid fa-spinner fa-spin text-body"></i>
                    </div>
                    <template v-if="!chatLoading && chatMessages.length === 0">
                        <div class="flex flex-col items-center justify-center py-8 text-slate-300">
                            <i class="fa-regular fa-comment-dots text-title mb-2"></i>
                            <div class="text-body-sm">Belum ada pesan</div>
                            <div class="text-overline-xs text-slate-300 mt-1">Kirim pesan pertama</div>
                        </div>
                    </template>
                    <template v-for="(msg, idx) in chatMessages" :key="msg.id || idx">
                        <div :class="['flex', msg.is_mine ? 'justify-end' : 'justify-start']">
                            <div :class="msg.is_mine ? 'chat-bubble chat-bubble--mine' : 'chat-bubble chat-bubble--other'">
                                <div class="chat-bubble-message">{{ msg.message }}</div>
                                <div class="chat-bubble-meta">
                                    <span>{{ chatTimeLabel(msg.created_at) }}</span>
                                    <span v-if="msg.is_mine" :class="chatMessageStatusIconClass(msg)" aria-hidden="true">
                                        <i class="fa-solid fa-check"></i>
                                        <i v-if="chatMessageStatus(msg) !== 'sent_offline'" class="fa-solid fa-check"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div v-if="chatPeerTyping" class="flex justify-start">
                        <div class="bg-white border border-slate-200 text-slate-500 rounded-2xl rounded-bl-sm px-3 py-2 text-body-sm">
                            mengetik...
                        </div>
                    </div>
                </div>
                <!-- Input -->
                <div class="shrink-0 border-t border-slate-100 bg-white px-3 py-2.5">
                    <div class="flex items-center gap-2">
                        <input v-model="chatInput" @input="chatScheduleTyping" @keydown="chatHandleKeydown" type="text" maxlength="1000"
                            class="flex-1 min-w-0 rounded-full border border-slate-200 bg-slate-50 px-3.5 py-2 text-body-sm text-slate-700 placeholder:text-slate-300 focus:outline-none focus:border-ppp-accent/40 focus:ring-1 focus:ring-ppp-accent/20 transition-colors"
                            placeholder="Ketik pesan..." />
                        <button @click="chatSendMessage" :disabled="chatSending || !chatInput.trim()"
                            class="w-9 h-9 rounded-full bg-ppp-accent text-white flex items-center justify-center shrink-0 hover:opacity-90 transition-opacity disabled:opacity-30">
                            <i :class="['fa-solid text-body-sm', chatSending ? 'fa-spinner fa-spin' : 'fa-paper-plane']"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Floating Chat Button when no active conversation -->
            <div v-if="currentUser && !(chatOpen && chatTargetUser)"
                class="fixed bottom-4 right-4 z-[200] flex flex-col items-end gap-2">
                <!-- Online users mini strip -->
                <div v-if="chatUnreadTotal > 0"
                    class="flex items-center gap-1 rounded-full px-2.5 py-1.5">
                    <div v-for="(member, idx) in chatUsersWithUnread.slice(0, 4)" :key="member.ID"
                        @click="chatOpenConversation(member)"
                        class="relative w-8 h-8 rounded-full -ml-1.5 first:ml-0 text-ppp-accent flex items-center justify-center text-overline-xs font-bold uppercase overflow-visible cursor-pointer hover:z-10 transition-all shrink-0"
                        :style="{ zIndex: 10 - idx }" :title="member.nama || member.username">
                        <div class="w-full h-full rounded-full overflow-hidden">
                            <img v-if="member.avatar_url" :src="member.avatar_url" class="w-full h-full object-cover" />
                            <span v-else>{{ ((member?.nama || member?.username || '?')[0]) }}</span>
                        </div>
                        <span v-if="member.unread_count > 0"
                            class="absolute -top-1 -right-1 min-w-[16px] h-4 px-1 rounded-full bg-danger text-white text-[9px] font-bold flex items-center justify-center leading-none z-10">
                            {{ member.unread_count > 9 ? '9+' : member.unread_count }}
                        </span>
                    </div>
                    <div v-if="chatUsersWithUnread.length > 4"
                        class="text-overline-xs text-slate-400 pl-1.5 pr-1 font-medium">+{{ chatUsersWithUnread.length - 4 }}</div>
                </div>
                <button @click="chatOpenPicker"
                    class="w-12 h-12 rounded-full bg-ppp-accent text-white flex items-center justify-center hover:opacity-90 transition-opacity relative">
                    <i class="fa-regular fa-comment-dots text-body"></i>
                    <span v-if="chatUnreadTotal > 0"
                        class="absolute -top-1 -right-1 bg-danger text-white text-overline-xs font-bold w-5 h-5 rounded-full flex items-center justify-center">
                        {{ chatUnreadTotal > 99 ? '99+' : chatUnreadTotal }}
                    </span>
                </button>
            </div>

            <!-- User picker modal when clicking the FAB with no target -->
            <transition name="fade">
                <div v-if="chatShowPicker" class="fixed inset-0 z-[300] glass-backdrop flex items-start justify-center pt-16 md:pt-24"
                    @click.self="chatShowPicker = false">
                    <div class="bg-white rounded-2xl border border-slate-200 w-[90vw] max-w-sm max-h-[70vh] flex flex-col overflow-hidden">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 shrink-0">
                            <div class="text-body font-semibold text-slate-800">Pilih User</div>
                            <button @click="chatShowPicker = false" class="w-7 h-7 flex items-center justify-center text-slate-300 hover:text-slate-600">
                                <i class="fa-solid fa-xmark text-body-sm"></i>
                            </button>
                        </div>
                        <div class="flex-1 overflow-y-auto p-2 space-y-0.5">
                            <div v-if="chatOnlineLoading && chatRecentLoading" class="flex items-center justify-center py-6 text-slate-300">
                                <i class="fa-solid fa-spinner fa-spin text-body"></i>
                            </div>

                            <!-- Recent Contacts -->
                            <template v-if="chatRecentContacts.length > 0">
                                <div class="px-3 pt-1.5 pb-0.5 text-overline font-bold text-slate-400 uppercase">Terakhir Chat</div>
                                <button v-for="item in chatRecentContacts" :key="'recent-' + item.user.ID"
                                    @click="chatOpenConversation(item.user)"
                                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 transition-colors text-left">
                                    <div
                                        class="relative w-9 h-9 rounded-full bg-ppp-accent text-white flex items-center justify-center text-body-sm font-bold uppercase shrink-0 overflow-hidden">
                                        <img v-if="item.user.avatar_url" :src="item.user.avatar_url" class="w-full h-full object-cover" />
                                        <span v-else>{{ ((item.user?.nama || item.user?.username || '?')[0]) }}</span>
                                        <span v-if="item.unread_count > 0"
                                            class="absolute -top-1 -right-1 min-w-[16px] h-4 px-1 rounded-full bg-danger text-white text-[9px] font-bold flex items-center justify-center leading-none">
                                            {{ item.unread_count > 9 ? '9+' : item.unread_count }}
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="text-body-sm font-medium text-slate-700 truncate">{{ item.user.nama || item.user.username }}</div>
                                            <div class="text-overline-xs text-slate-400 shrink-0">{{ chatTimeLabel(item.last_message_at) }}</div>
                                        </div>
                                        <div class="text-overline-xs text-slate-400 truncate">{{ item.last_message }}</div>
                                    </div>
                                </button>
                            </template>

                            <!-- Online Users -->
                            <template v-if="chatOnlineContacts.length > 0">
                                <div class="px-3 pt-2 pb-0.5 text-overline font-bold text-slate-400 uppercase">Online Sekarang</div>
                                <button v-for="member in chatOnlineContacts" :key="'online-' + member.ID"
                                    @click="chatOpenConversation(member)"
                                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 transition-colors text-left">
                                    <div
                                        class="relative w-9 h-9 rounded-full bg-ppp-accent text-white flex items-center justify-center text-body-sm font-bold uppercase shrink-0 overflow-hidden">
                                        <img v-if="member.avatar_url" :src="member.avatar_url" class="w-full h-full object-cover" />
                                        <span v-else>{{ ((member?.nama || member?.username || '?')[0]) }}</span>
                                        <span v-if="member.unread_count > 0"
                                            class="absolute -top-1 -right-1 min-w-[16px] h-4 px-1 rounded-full bg-danger text-white text-[9px] font-bold flex items-center justify-center leading-none">
                                            {{ member.unread_count > 9 ? '9+' : member.unread_count }}
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-body-sm font-medium text-slate-700 truncate">{{ member.nama || member.username }}</div>
                                        <div class="flex items-center gap-1.5 text-overline-xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-success inline-block shrink-0"></span>
                                            <span class="text-slate-400">Online</span>
                                        </div>
                                    </div>
                                </button>
                            </template>

                            <!-- Offline Users -->
                            <template v-if="chatOfflineContacts.length > 0">
                                <div class="px-3 pt-2 pb-0.5 text-overline font-bold text-slate-400 uppercase">Offline</div>
                                <button v-for="member in chatOfflineContacts" :key="'offline-' + member.ID"
                                    @click="chatOpenConversation(member)"
                                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 transition-colors text-left">
                                    <div
                                        class="relative w-9 h-9 rounded-full bg-slate-400 text-white flex items-center justify-center text-body-sm font-bold uppercase shrink-0 overflow-hidden">
                                        <img v-if="member.avatar_url" :src="member.avatar_url" class="w-full h-full object-cover" />
                                        <span v-else>{{ ((member?.nama || member?.username || '?')[0]) }}</span>
                                        <span v-if="member.unread_count > 0"
                                            class="absolute -top-1 -right-1 min-w-[16px] h-4 px-1 rounded-full bg-danger text-white text-[9px] font-bold flex items-center justify-center leading-none">
                                            {{ member.unread_count > 9 ? '9+' : member.unread_count }}
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-body-sm font-medium text-slate-700 truncate">{{ member.nama || member.username }}</div>
                                        <div class="flex items-center gap-1.5 text-overline-xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300 inline-block shrink-0"></span>
                                            <span class="text-slate-400">Offline</span>
                                        </div>
                                    </div>
                                </button>
                            </template>

                            <template v-if="!chatOnlineLoading && !chatRecentLoading && chatRecentContacts.length === 0 && chatOnlineContacts.length === 0 && chatOfflineContacts.length === 0">
                                <div class="text-center py-6 text-slate-300">
                                    <i class="fa-regular fa-user text-title mb-2"></i>
                                    <div class="text-body-sm">Belum ada percakapan</div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </transition>
@endverbatim
