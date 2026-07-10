@verbatim
        <aside
            :class="['fixed inset-y-0 left-0 z-[80] w-60 bg-ppp-sidebar border-r border-slate-200 flex flex-col transform-gpu will-change-transform transition-[transform,box-shadow] duration-400', isSidebarOpen ? 'translate-x-0 shadow-2xl md:shadow-none' : '-translate-x-[calc(100%+20px)] shadow-none']"
            style="transition-timing-function:cubic-bezier(0.22, 1, 0.36, 1);">
            <div class="px-4 h-16 flex items-center gap-3 border-b border-slate-200">
                <div
                    class="w-9 h-9 bg-white rounded-xl flex items-center justify-center p-1.5 border border-blue-100">
                    <img src="/asset/images/logo.png"
                        class="w-full h-full object-contain" alt="Logo" />
                </div>
                <div>
                    <div class="text-[10px] font-medium text-ppp-nav-text tracking-widest uppercase">Pura Pura
                        Ponsel</div>
                    <div class="text-[9px] text-slate-400 uppercase">Marketing Dashboard</div>
                </div>
            </div>

@endverbatim
            @include('dashboard.partials.shell.app-frame-sidebar-nav')
            @include('dashboard.partials.shell.app-frame-sidebar-footer')
@verbatim
        </aside>
@endverbatim
