@verbatim
        <aside
            :class="[
                'dashboard-sidebar-shell fixed inset-y-0 left-0 z-[80] lg:z-40 bg-white border-r border-slate-100 flex flex-col overflow-hidden w-60',
                'shadow-none',
            ]"
            :style="{ transform: sidebarOpen || (!isMobileViewport && !sidebarCollapsed) ? 'translateX(0)' : 'translateX(-100%)' }"
            >
            <div class="dashboard-sidebar-content min-w-[15rem] flex h-full flex-col relative">
                <div class="dashboard-sidebar-brand relative px-4 h-16 flex items-center gap-3 border-b border-slate-100 flex-shrink-0 bg-white">
                    <div
                        class="dashboard-sidebar-logo w-9 h-9 rounded-xl bg-white overflow-hidden flex items-center justify-center shrink-0 shadow-sm ring-1 ring-slate-100">
                        <img src="/asset/images/logo.png"
                            class="h-full w-full object-contain p-1" alt="Pura Pura Ponsel" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-[10px] lg:text-[11px] font-black tracking-wide text-slate-900 leading-tight">PURA PURA PONSEL</div>
                        <div class="text-[7px] lg:text-[8px] text-slate-400 uppercase tracking-[0.18em]">MARKETING DASHBOARD</div>
                    </div>
                    <button class="w-11 h-11 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-50 hover:text-slate-600 transition-all lg:hidden" @click="sidebarOpen = false">
                        <i class="fa-solid fa-xmark text-[14px]"></i>
                    </button>
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
