@verbatim
        <aside
            :class="[
                'dashboard-sidebar-shell fixed inset-y-0 left-0 z-[80] bg-white border-r border-slate-100 flex flex-col overflow-hidden transform-gpu w-60',
                isMobileViewport
                    ? (isSidebarOpen ? 'translate-x-0 shadow-none' : '-translate-x-[calc(100%+20px)] shadow-none')
                    : (isSidebarOpen ? 'translate-x-0 shadow-none' : '-translate-x-full shadow-none'),
            ]"
            >
            <div :class="['dashboard-sidebar-content min-w-[15rem] flex h-full flex-col relative', !isMobileViewport && !isSidebarOpen ? 'opacity-0 pointer-events-none' : 'opacity-100']">
                <div class="dashboard-sidebar-brand px-4 h-16 flex items-center gap-3 border-b border-slate-100">
                    <div
                        class="dashboard-sidebar-logo w-9 h-9 bg-white rounded-xl flex items-center justify-center p-1.5 ring-1 ring-slate-100">
                        <img src="/asset/images/logo.png"
                            class="w-full h-full object-contain" alt="Logo" />
                    </div>
                    <div class="min-w-0 leading-none">
                        <div class="text-[11px] font-black tracking-wide text-slate-900 truncate">PURA PURA PONSEL</div>
                        <div class="text-[8px] text-slate-400 uppercase tracking-[0.18em] truncate">MARKETING DASHBOARD</div>
                    </div>
                </div>

@endverbatim
                @include('dashboard.partials.shell.app-frame-sidebar-nav')
@verbatim
                <div class="relative mt-auto border-t border-slate-100 px-4 py-3 text-center text-[8px] text-slate-400 overflow-hidden">
@endverbatim
                    @include('dashboard.partials.shell.waves', ['fill1' => 'rgba(255,165,0,.08)', 'fill2' => 'rgba(255,165,0,.14)', 'height1' => 'h-16', 'height2' => 'h-12'])
@verbatim
                    <div class="relative z-10">© Pura Pura Ponsel</div>
                </div>
            </div>
        </aside>
@endverbatim
