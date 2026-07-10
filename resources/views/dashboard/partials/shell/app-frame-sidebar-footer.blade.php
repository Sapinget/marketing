@verbatim
            <div class="mt-auto p-4 border-t border-slate-200">
                <div class="flex items-center gap-3 mb-4">
                    <div
                        class="w-9 h-9 rounded-full bg-ppp-accent text-white flex items-center justify-center text-[11px] font-semibold uppercase">
                        {{ (currentUser?.username || 'U')[0] }}</div>
                    <div class="min-w-0">
                        <div class="type-body font-medium text-slate-800 truncate">{{ currentUser?.username || 'User' }}
                        </div>
                        <div class="type-micro text-slate-400 uppercase tracking-widest">{{ currentUser?.role || '-' }}
                        </div>
                    </div>
                </div>
                <button @click="logout" class="secondary-cta-button secondary-cta-danger w-full">Logout</button>
            </div>
@endverbatim
