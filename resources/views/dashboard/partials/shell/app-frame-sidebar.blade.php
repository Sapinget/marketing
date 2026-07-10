@verbatim
        <aside
            :class="[
                'dashboard-sidebar-shell fixed inset-y-0 left-0 z-[80] bg-ppp-sidebar border-r border-slate-200 flex flex-col overflow-hidden transform-gpu',
                isMobileViewport
                    ? (isSidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-[calc(100%+20px)] shadow-none')
                    : 'translate-x-0 shadow-none',
            ]"
            :style="isMobileViewport ? { width: '15rem' } : { width: isSidebarOpen ? '15rem' : '0px' }"
            >
            <div :class="['dashboard-sidebar-content min-w-[15rem] flex h-full flex-col', !isMobileViewport && !isSidebarOpen ? 'opacity-0 pointer-events-none' : 'opacity-100']">
                <div class="px-4 h-16 flex items-center gap-3 border-b border-slate-200">
                    <div
                        class="w-9 h-9 bg-white rounded-xl flex items-center justify-center p-1.5 border border-blue-100">
                        <img src="/asset/images/logo.png"
                            class="w-full h-full object-contain" alt="Logo" />
                    </div>
                    <div>
                        <div class="text-body-sm font-medium text-ppp-nav-text tracking-widest uppercase">Pura Pura
                            Ponsel</div>
                        <div class="text-overline text-slate-400 uppercase">Marketing Dashboard</div>
                    </div>
                </div>

@endverbatim
                @include('dashboard.partials.shell.app-frame-sidebar-nav')
                @include('dashboard.partials.shell.app-frame-sidebar-footer')
@verbatim
            </div>
        </aside>
@endverbatim
