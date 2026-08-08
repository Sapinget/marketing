@verbatim
                const chatOpen = ref(false);
                const chatShowPicker = ref(false);
                const chatTargetUser = ref(null);
                const chatMessages = ref([]);
                const chatSending = ref(false);
                const chatInput = ref('');
                const chatUnreadTotal = ref(0);
                const chatUnreadByUser = ref({});
                const chatPrevUnreadByUser = ref({});
                const chatLoading = ref(false);
                const chatPollTimer = ref(null);
                const chatUnreadPollTimer = ref(null);
                const chatContainer = ref(null);
                const chatHasMore = ref(false);
                const chatSessionConfirmed = ref(false);
                const chatLastInboundMessageId = ref(null);
                const chatTyping = ref(false);
                const chatPeerTyping = ref(false);
                const chatTypingTimer = ref(null);
                const chatTypingPollTimer = ref(null);

                const chatOnlineUsers = ref([]);
                const chatOnlineLoading = ref(false);
                const chatRecentContacts = ref([]);
                const chatRecentLoading = ref(false);

                const chatApi = window.MarketingDashboardRuntimeHelpers?.jsonApi || window.MarketingDashboardRuntimeHelpers?.chatFetch || (async (url, opts = {}) => { throw new Error('jsonApi not ready'); });

                const chatLoadOnlineUsers = async () => {
                    if (!chatReady.value) return;
                    try {
                        chatOnlineLoading.value = true;
                        const result = await chatApi('/api/chat/users');
                        chatOnlineUsers.value = Array.isArray(result?.data) ? result.data : [];
                    } catch (error) {
                    } finally {
                        chatOnlineLoading.value = false;
                    }
                };

                const chatLoadRecentContacts = async () => {
                    if (!chatReady.value) return;
                    try {
                        chatRecentLoading.value = true;
                        const result = await chatApi('/api/chat/recent');
                        chatRecentContacts.value = Array.isArray(result?.data) ? result.data : [];
                    } catch (error) {
                    } finally {
                        chatRecentLoading.value = false;
                    }
                };

                const chatOpenConversation = async (user) => {
                    if (!user?.ID || !chatReady.value) return;
                    chatTargetUser.value = user;
                    chatMessages.value = [];
                    chatLoading.value = true;
                    chatOpen.value = true;
                    chatShowPicker.value = false;
                    if (window.innerWidth < 768) profileMenuOpen.value = false;
                    try {
                        const result = await chatApi(`/api/chat/messages/${user.ID}`);
                        chatMessages.value = Array.isArray(result?.data) ? result.data : [];
                    } catch (error) {
                        showNotification('Gagal memuat pesan.', 'error');
                    } finally {
                        chatLoading.value = false;
                        nextTick(() => chatScrollToBottom());
                    }
                };

                const chatScrollToBottom = () => {
                    nextTick(() => {
                        const el = chatContainer.value;
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                };

                const chatNotifyTyping = async () => {
                    if (!chatTargetUser.value?.ID || !chatReady.value) return;
                    try {
                        await chatApi(`/api/chat/typing/${chatTargetUser.value.ID}`, { method: 'POST' });
                    } catch (error) {
                    }
                };

                const chatPollTyping = async () => {
                    if (!chatTargetUser.value?.ID || !chatReady.value || !chatOpen.value) return;
                    try {
                        const result = await chatApi(`/api/chat/typing/${chatTargetUser.value.ID}`);
                        chatPeerTyping.value = result?.typing === true;
                    } catch (error) {
                    }
                };

                const chatScheduleTyping = () => {
                    if (!chatTargetUser.value?.ID || !chatReady.value || !String(chatInput.value || '').trim()) return;
                    if (!chatTyping.value) {
                        chatTyping.value = true;
                        chatNotifyTyping();
                    }
                    if (chatTypingTimer.value) {
                        window.clearTimeout(chatTypingTimer.value);
                    }
                    chatTypingTimer.value = window.setTimeout(() => {
                        chatTyping.value = false;
                    }, 2000);
                };

                const chatSendMessage = async () => {
                    const msg = String(chatInput.value || '').trim();
                    if (!msg || !chatTargetUser.value?.ID || chatSending.value || !chatReady.value) return;
                    chatSending.value = true;
                    try {
                        const result = await chatApi('/api/chat/messages', {
                            method: 'POST',
                            body: JSON.stringify({ receiver_id: chatTargetUser.value.ID, message: msg }),
                        });
                        if (result?.data) {
                            chatMessages.value.push(result.data);
                            chatInput.value = '';
                            chatTyping.value = false;
                            chatScrollToBottom();
                            chatLoadRecentContacts();
                        }
                    } catch (error) {
                        showNotification(error?.message || 'Gagal kirim pesan.', 'error');
                    } finally {
                        chatSending.value = false;
                    }
                };

                const chatPollMessages = async () => {
                    if (!chatTargetUser.value?.ID || !chatOpen.value) return;
                    try {
                        const result = await chatApi(`/api/chat/messages/${chatTargetUser.value.ID}`);
                        const fresh = Array.isArray(result?.data) ? result.data : [];
                        const hasChanged = chatMessagesSignature(fresh) !== chatMessagesSignature(chatMessages.value);
                        if (hasChanged) {
                            chatCheckNewInbound(fresh);
                            chatMessages.value = fresh;
                            chatScrollToBottom();
                        }
                    } catch (error) {
                    }
                };

                const chatPollUnread = async () => {
                    if (!chatReady.value) return;
                    try {
                        const result = await chatApi('/api/chat/unread');
                        const prev = { ...(chatPrevUnreadByUser.value || {}) };
                        const next = result?.by_user || {};
                        chatUnreadTotal.value = result?.total || 0;
                        chatUnreadByUser.value = next;
                        chatPrevUnreadByUser.value = { ...next };

                        for (const [userId, count] of Object.entries(next)) {
                            const prevCount = Number(prev[userId] || 0);
                            const newCount = Number(count || 0);
                            if (newCount > prevCount && newCount > 0) {
                                const targetId = Number(userId);
                                const isCurrentlyOpen = chatOpen.value && Number(chatTargetUser.value?.ID || 0) === targetId;
                                if (!isCurrentlyOpen) {
                                    showNotification(`Pesan baru dari ${chatResolveUserName(targetId)}`, 'success');
                                    chatShowBrowserNotification(targetId);
                                }
                            }
                        }
                    } catch (error) {
                        if (error?.status === 401) {
                            chatStopUnreadPolling();
                            clearSessionState(error?.message || 'Sesi login berakhir. Silakan login kembali.', 'warning');
                        }
                    }
                };

                const chatResolveUserName = (userId) => {
                    const targetId = Number(userId || 0);
                    if (!targetId) return 'user';
                    const pools = [
                        ...(Array.isArray(chatOnlineUsers.value) ? chatOnlineUsers.value : []),
                        ...(Array.isArray(chatRecentContacts.value) ? chatRecentContacts.value.map((item) => item.user) : []),
                        ...(chatTargetUser.value ? [chatTargetUser.value] : []),
                    ];
                    const match = pools.find((user) => Number(user?.ID || 0) === targetId);
                    return match?.nama || match?.username || 'user';
                };

                const chatCheckNewInbound = (fresh) => {
                    if (!fresh?.length) return;
                    const prevLen = chatMessages.value.length;
                    const newMsgs = fresh.slice(prevLen);
                    for (const msg of newMsgs) {
                        if (!msg.is_mine && (!chatLastInboundMessageId.value || msg.id !== chatLastInboundMessageId.value)) {
                            chatLastInboundMessageId.value = msg.id;
                        }
                    }
                };

                const chatMessagesSignature = (messages) =>
                    (Array.isArray(messages) ? messages : [])
                        .map((message) => [message?.id, message?.message, message?.read_at || '', message?.created_at || ''].join(':'))
                        .join('|');

                const chatStartPolling = () => {
                    chatStopPolling();
                    chatPollTimer.value = window.setInterval(chatPollMessages, 3000);
                    chatTypingPollTimer.value = window.setInterval(chatPollTyping, 2000);
                    chatUnreadPollTimer.value = window.setInterval(chatPollUnread, 10000);
                    chatPollUnread();
                    chatPollTyping();
                };

                const chatStopPolling = () => {
                    if (chatPollTimer.value) {
                        window.clearInterval(chatPollTimer.value);
                        chatPollTimer.value = null;
                    }
                    if (chatTypingPollTimer.value) {
                        window.clearInterval(chatTypingPollTimer.value);
                        chatTypingPollTimer.value = null;
                    }
                    if (chatTypingTimer.value) {
                        window.clearTimeout(chatTypingTimer.value);
                        chatTypingTimer.value = null;
                    }
                    chatPeerTyping.value = false;
                    chatTyping.value = false;
                    chatStopUnreadPolling();
                };

                const chatClose = () => {
                    chatOpen.value = false;
                    chatShowPicker.value = false;
                    chatTargetUser.value = null;
                    chatMessages.value = [];
                    chatStopPolling();
                };

                const chatOpenPicker = async () => {
                    if (!chatReady.value) return;
                    chatShowPicker.value = true;
                    chatOpen.value = false;
                    chatTargetUser.value = null;
                    await Promise.all([
                        chatLoadOnlineUsers(),
                        chatLoadRecentContacts(),
                    ]);
                };

                const chatHandleKeydown = (e) => {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        chatSendMessage();
                    }
                };

                const chatMarkAllRead = async () => {
                    if (!chatTargetUser.value?.ID || !chatReady.value) return;
                    try {
                        await chatApi(`/api/chat/messages/${chatTargetUser.value.ID}`);
                        const nextUnread = { ...(chatUnreadByUser.value || {}) };
                        delete nextUnread[chatTargetUser.value.ID];
                        chatUnreadByUser.value = nextUnread;
                        chatUnreadTotal.value = Math.max(0, Object.values(nextUnread).reduce((sum, value) => sum + Number(value || 0), 0));
                    } catch (error) {
                    }
                };

                watch(chatOpen, (open) => {
                    if (open && chatTargetUser.value?.ID) {
                        chatStartPolling();
                    } else {
                        chatStopPolling();
                    }
                });

                watch(chatTargetUser, () => {
                    if (chatTargetUser.value?.ID) {
                        chatMarkAllRead();
                    }
                });

                const chatTimeLabel = (iso) => {
                    if (!iso) return '';
                    const d = new Date(iso);
                    if (isNaN(d.getTime())) return '';
                    return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                };

                const chatMessageStatus = (message) => {
                    if (message?.read_at) return 'read';
                    if (chatTargetUser.value?.is_online === true) return 'delivered';
                    return 'sent_offline';
                };

                const chatMessageStatusIconClass = (message) => {
                    const status = chatMessageStatus(message);
                    if (status === 'read') return 'chat-check-status chat-check-status--double chat-check-status--read';
                    if (status === 'delivered') return 'chat-check-status chat-check-status--double chat-check-status--delivered';
                    return 'chat-check-status chat-check-status--offline';
                };

                const chatContactUsers = computed(() => {
                    return (Array.isArray(chatOnlineUsers.value) ? chatOnlineUsers.value : []).map((user) => ({
                        ...user,
                        unread_count: Number(chatUnreadByUser.value?.[user.ID] || 0),
                    }));
                });

                const chatUsersWithUnread = computed(() => chatContactUsers.value.filter((user) => Number(user?.unread_count || 0) > 0));
                const chatOnlineContacts = computed(() => chatContactUsers.value.filter((user) => user?.is_online === true));
                const chatOfflineContacts = computed(() => chatContactUsers.value.filter((user) => user?.is_online !== true));

                // Load online users after auth ready
                const chatReady = computed(() => Boolean(chatSessionConfirmed.value && currentUser.value?.ID));

                const chatCanUseBrowserNotification = () => typeof window !== 'undefined' && 'Notification' in window;

                const chatEnsureBrowserNotificationPermission = async () => {
                    if (!chatCanUseBrowserNotification()) return 'denied';
                    if (Notification.permission === 'granted') return 'granted';
                    if (Notification.permission === 'denied') return 'denied';
                    try {
                        return await Notification.requestPermission();
                    } catch (error) {
                        return 'denied';
                    }
                };

                const chatShowBrowserNotification = async (userId) => {
                    if (!document.hidden) return;
                    const permission = await chatEnsureBrowserNotificationPermission();
                    if (permission !== 'granted') return;
                    const name = chatResolveUserName(userId);
                    try {
                        const notice = new Notification(`Pesan baru dari ${name}`, {
                            body: 'Buka dashboard untuk lihat pesan baru.',
                            tag: `chat-message-${userId}`,
                        });
                        notice.onclick = () => {
                            window.focus();
                            const target = chatContactUsers.value.find((user) => Number(user?.ID || 0) === Number(userId || 0));
                            if (target) {
                                chatOpenConversation(target);
                            } else {
                                chatOpenPicker();
                            }
                            notice.close();
                        };
                    } catch (error) {
                    }
                };

                const chatStartUnreadPolling = () => {
                    if (chatUnreadPollTimer.value) return;
                    chatPollUnread();
                    chatUnreadPollTimer.value = window.setInterval(chatPollUnread, 10000);
                };

                const chatStopUnreadPolling = () => {
                    if (chatUnreadPollTimer.value) {
                        window.clearInterval(chatUnreadPollTimer.value);
                        chatUnreadPollTimer.value = null;
                    }
                };

                watch(chatReady, (ready) => {
                    if (!ready) {
                        chatClose();
                        chatRecentContacts.value = [];
                        return;
                    }
                    chatLoadOnlineUsers();
                    chatLoadRecentContacts();
                    chatStartUnreadPolling();
                }, { immediate: true });
@endverbatim
