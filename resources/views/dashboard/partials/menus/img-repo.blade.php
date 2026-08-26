{{-- Image Repository – Google Drive-style --}}
<div v-show="activeTab === 'img_repo'"
    class="img-repo-shell section-card flex flex-col md:flex-row overflow-hidden">

    {{-- ── Left Sidebar ──────────────────────────────────── --}}
    <aside class="img-repo-sidebar shrink-0 flex flex-col gap-1 min-h-0">

        {{-- New button --}}
        <div class="relative">
            <button @click="imgRepoNewMenuOpen = !imgRepoNewMenuOpen"
                class="img-repo-create-button">
                <i class="fa-solid fa-plus"></i>
                <span>Baru</span>
                <i class="img-repo-create-button__chevron fa-solid fa-caret-down"></i>
            </button>
            <div v-if="imgRepoNewMenuOpen"
                class="img-repo-create-menu">
                <label class="img-repo-create-menu__item cursor-pointer">
                    <i class="fa-solid fa-upload"></i>
                    <span>Upload File</span>
                    <input type="file" multiple accept="image/*" class="hidden" @change="imgRepoUploadFiles($event); imgRepoNewMenuOpen=false">
                </label>
                <button @click="imgRepoMkdirOpen=true; imgRepoMkdirName=''; imgRepoNewMenuOpen=false"
                    class="img-repo-create-menu__item">
                    <i class="fa-solid fa-folder-plus"></i>
                    <span>Folder Baru</span>
                </button>
            </div>
        </div>

        {{-- Quick folders --}}
        <div class="img-repo-sidebar-section">
            <p class="img-repo-sidebar-section__label">FOLDER UTAMA</p>
            <button @click="imgRepoBrowse('')"
                :class="['img-repo-sidebar-nav',
                    imgRepoPath === '' ? 'img-repo-sidebar-nav--active' : '']">
                <i class="fa-solid fa-hard-drive"></i>
                <span>Menu Utama</span>
            </button>
            <button @click="imgRepoBrowse('APPLE')"
                :class="['img-repo-sidebar-nav',
                    imgRepoPath === 'APPLE' || imgRepoPath.startsWith('APPLE/') ? 'img-repo-sidebar-nav--active' : '']">
                <i class="fa-brands fa-apple"></i>
                <span>Apple</span>
            </button>
            <button @click="imgRepoBrowse('ANDROID')"
                :class="['img-repo-sidebar-nav',
                    imgRepoPath === 'ANDROID' || imgRepoPath.startsWith('ANDROID/') ? 'img-repo-sidebar-nav--active' : '']">
                <i class="fa-brands fa-android"></i>
                <span>Android</span>
            </button>
        </div>
    </aside>

    {{-- ── Main Area ──────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0 min-h-0">

        {{-- Top bar --}}
        <div class="img-repo-topbar flex items-center gap-3 shrink-0">

            {{-- Breadcrumb --}}
            <nav class="img-repo-breadcrumb flex-1 min-w-0" aria-label="Breadcrumb folder">
                <template v-for="(crumb, idx) in imgRepoBreadcrumbs" :key="crumb.path">
                    <button v-if="idx < imgRepoBreadcrumbs.length - 1"
                        @click="imgRepoBrowse(crumb.path)"
                        class="img-repo-breadcrumb__button">
                        @{{ crumb.label }}
                    </button>
                    <i v-if="idx < imgRepoBreadcrumbs.length - 1"
                        class="img-repo-breadcrumb__separator fa-solid fa-chevron-right"
                        aria-hidden="true"></i>
                    <span v-if="idx === imgRepoBreadcrumbs.length - 1"
                        class="img-repo-breadcrumb__current">
                        @{{ crumb.label }}
                    </span>
                </template>
            </nav>

            {{-- Upload progress badge --}}
            <div v-if="imgRepoUploading"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-ppp-accent/10 text-ppp-accent text-body-sm font-medium shrink-0">
                <i class="fa-solid fa-spinner animate-spin text-xs"></i>
                <span>Uploading...</span>
            </div>

            {{-- View toggle --}}
            <div class="img-repo-toolbar-toggle shrink-0">
                <button @click="imgRepoViewMode='grid'"
                    :class="['img-repo-toggle-button', imgRepoViewMode==='grid' ? 'img-repo-toggle-button--active' : '']"
                    type="button"
                    aria-label="Grid view"
                    title="Grid view">
                    <i class="fa-solid fa-grip text-sm"></i>
                </button>
                <button @click="imgRepoViewMode='list'"
                    :class="['img-repo-toggle-button', imgRepoViewMode==='list' ? 'img-repo-toggle-button--active' : '']"
                    type="button"
                    aria-label="List view"
                    title="List view">
                    <i class="fa-solid fa-list text-sm"></i>
                </button>
            </div>

            {{-- Refresh --}}
            <button @click="imgRepoBrowse(imgRepoPath)" :disabled="imgRepoLoading"
                class="icon-toolbar-button rounded-lg text-slate-500 hover:bg-slate-100 transition-colors disabled:opacity-40 shrink-0">
                <i :class="['fa-solid fa-arrows-rotate text-sm', imgRepoLoading ? 'animate-spin' : '']"></i>
            </button>
        </div>

        {{-- Error --}}
        <div v-if="imgRepoError"
            class="mx-6 mt-4 flex items-center gap-2 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-body-sm">
            <i class="fa-solid fa-circle-exclamation"></i>
            @{{ imgRepoError }}
        </div>

        {{-- Content --}}
        <div class="img-repo-content flex-1 min-h-0 overflow-auto custom-scrollbar" @click.self="imgRepoSelected=null; imgRepoContextMenu=null">

            {{-- Loading --}}
            <div v-if="imgRepoLoading && !imgRepoItems.length"
                class="img-repo-grid img-repo-grid--loading">
                <div v-for="n in 16" :key="n"
                    class="aspect-video rounded-xl bg-slate-100 animate-pulse"></div>
            </div>

            {{-- Empty --}}
            <div v-if="!imgRepoLoading && !imgRepoError && imgRepoItems.length === 0"
                class="img-repo-empty-state flex flex-col items-center justify-center text-slate-400 select-none">
                <i class="fa-solid fa-folder-open text-5xl mb-4 opacity-30"></i>
                <p class="text-body font-medium">Folder kosong</p>
                <p class="text-body-sm mt-1">Upload file atau buat folder baru</p>
            </div>

            {{-- ─ GRID VIEW ─ --}}
            <template v-if="imgRepoViewMode === 'grid' && imgRepoItems.length">

                {{-- Folders section --}}
                <div v-if="imgRepoDirs.length">
                    <p class="text-overline text-slate-400 mb-2">FOLDER</p>
                    <div class="img-repo-grid img-repo-grid--folders">
                        <template v-for="item in imgRepoDirs" :key="'d-'+item.path">

                            {{-- Rename inline --}}
                            <div v-if="imgRepoRenameItem?.path === item.path"
                                class="img-repo-folder-card img-repo-folder-card--compact flex items-center border-2 border-ppp-accent bg-ppp-accent/5">
                                <i class="fa-solid fa-folder text-xl text-amber-400 shrink-0"></i>
                                <input v-model="imgRepoRenameName"
                                    @keydown.enter="imgRepoRenameSubmit"
                                    @keydown.escape="imgRepoCancelRename"
                                    class="form-input-compact flex-1"
                                    autofocus @click.stop>
                                <button @click.stop="imgRepoRenameSubmit" :disabled="imgRepoBusy"
                                    class="primary-cta-button primary-cta-button--accent shrink-0">OK</button>
                            </div>

                            {{-- Normal folder card --}}
                            <div v-else
                                @click.stop="imgRepoBrowse(item.path)"
                                @contextmenu.prevent="imgRepoOpenContext($event, item)"
                                class="img-repo-folder-card img-repo-folder-card--compact flex items-center cursor-pointer group select-none">
                                <i class="fa-solid fa-folder text-xl text-amber-400 shrink-0"></i>
                                <span class="flex-1 text-body-sm font-medium text-slate-700 truncate">@{{ item.name }}</span>
                                <button @click.stop="imgRepoOpenContext($event, item)"
                                    class="shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-slate-400 opacity-0 group-hover:opacity-100 hover:bg-slate-200 transition-all">
                                    <i class="fa-solid fa-ellipsis-vertical text-xs"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Files section --}}
                <div v-if="imgRepoFiles.length">
                    <p class="text-overline text-slate-400 mb-2">FILE</p>
                    <div class="img-repo-grid img-repo-grid--files">
                        <template v-for="item in imgRepoFiles" :key="'f-'+item.path">

                            {{-- Rename inline --}}
                            <div v-if="imgRepoRenameItem?.path === item.path"
                                class="img-repo-file-card border-2 border-ppp-accent bg-ppp-accent/5">
                                <div class="img-repo-file-card__media">
                                    <img :src="imgRepoThumbUrl(item.path)" class="img-repo-file-card__image">
                                </div>
                                <div class="img-repo-file-card__rename-body">
                                    <input v-model="imgRepoRenameName"
                                        @keydown.enter="imgRepoRenameSubmit"
                                        @keydown.escape="imgRepoCancelRename"
                                        class="form-input-compact w-full"
                                        autofocus @click.stop>
                                    <div class="img-repo-file-card__rename-actions">
                                        <button @click.stop="imgRepoRenameSubmit" :disabled="imgRepoBusy"
                                            class="primary-cta-button primary-cta-button--accent flex-1">Simpan</button>
                                        <button @click.stop="imgRepoCancelRename"
                                            class="primary-cta-button primary-cta-button--neutral flex-1">Batal</button>
                                    </div>
                                </div>
                            </div>

                            {{-- Normal file card --}}
                            <div v-else
                                @click.stop="imgRepoSelectItem(item)"
                                @contextmenu.prevent="imgRepoOpenContext($event, item)"
                                :class="['img-repo-file-card group',
                                    imgRepoSelected?.path === item.path
                                        ? 'img-repo-file-card--active'
                                        : '']">
                                <div class="img-repo-file-card__media">
                                    <img :src="imgRepoThumbUrl(item.path)"
                                        class="img-repo-file-card__image"
                                        loading="lazy"
                                        @@error="$event.target.parentElement.classList.add('flex','items-center','justify-center'); $event.target.style.display='none'">
                                    {{-- three-dots on hover --}}
                                    <button @click.stop="imgRepoOpenContext($event, item)"
                                        class="img-repo-file-card__menu-button">
                                        <i class="fa-solid fa-ellipsis-vertical text-xs"></i>
                                    </button>
                                </div>
                                <div class="img-repo-file-card__meta">
                                    <i class="fa-regular fa-image"></i>
                                    <span class="img-repo-file-card__name">@{{ item.name }}</span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            {{-- ─ LIST VIEW ─ --}}
            <template v-if="imgRepoViewMode === 'list' && imgRepoItems.length">
                <div class="overflow-x-auto">
                <table class="w-full text-body-sm min-w-[420px]">
                    <thead>
                        <tr class="table-header-row">
                            <th class="table-header-cell text-left">NAMA</th>
                            <th class="table-header-cell text-left w-24">TIPE</th>
                            <th class="table-header-cell text-right w-24">UKURAN</th>
                            <th class="table-header-cell w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="item in imgRepoItems" :key="'l-'+item.path">

                            {{-- Rename row --}}
                            <tr v-if="imgRepoRenameItem?.path === item.path"
                                class="bg-ppp-accent/5 border-b border-slate-100">
                                <td class="py-2 px-3" colspan="4">
                                    <div class="flex items-center gap-3">
                                        <i :class="['text-base w-5 text-center', item.type==='dir' ? 'fa-solid fa-folder text-amber-400' : 'fa-regular fa-image text-slate-400']"></i>
                                        <input v-model="imgRepoRenameName"
                                            @keydown.enter="imgRepoRenameSubmit"
                                            @keydown.escape="imgRepoCancelRename"
                                            class="form-input-compact flex-1"
                                            autofocus @click.stop>
                                        <button @click.stop="imgRepoRenameSubmit" :disabled="imgRepoBusy"
                                            class="primary-cta-button primary-cta-button--accent">Simpan</button>
                                        <button @click.stop="imgRepoCancelRename"
                                            class="primary-cta-button primary-cta-button--neutral">Batal</button>
                                    </div>
                                </td>
                            </tr>

                            {{-- Normal row --}}
                            <tr v-else
                                @click.stop="item.type === 'dir' ? imgRepoBrowse(item.path) : imgRepoSelectItem(item)"
                                @contextmenu.prevent="imgRepoOpenContext($event, item)"
                                :class="['border-b border-slate-100 cursor-pointer group transition-colors',
                                    imgRepoSelected?.path === item.path ? 'bg-ppp-accent/5' : 'hover:bg-slate-50']">
                                <td class="py-2 px-3">
                                    <div class="flex items-center gap-3">
                                        <i :class="['text-base w-5 text-center shrink-0', item.type==='dir' ? 'fa-solid fa-folder text-amber-400' : 'fa-regular fa-image text-slate-400']"></i>
                                        <span class="truncate max-w-xs text-slate-800">@{{ item.name }}</span>
                                    </div>
                                </td>
                                <td class="py-2 px-3 text-slate-500 uppercase text-xs">@{{ item.type === 'dir' ? 'Folder' : (item.ext || '-') }}</td>
                                <td class="py-2 px-3 text-slate-500 text-right">@{{ item.type === 'file' && item.size ? (item.size / 1024).toFixed(1) + ' KB' : '—' }}</td>
                                <td class="py-2 px-1">
                                    <button @click.stop="imgRepoOpenContext($event, item)"
                                        class="w-7 h-7 rounded-full flex items-center justify-center text-slate-400 opacity-0 group-hover:opacity-100 hover:bg-slate-200 transition-all">
                                        <i class="fa-solid fa-ellipsis-vertical text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                </div>
            </template>
        </div>
    </div>

    {{-- ── Detail Panel ──────────────────────────────────── --}}
    <div v-if="imgRepoSelected"
        class="img-repo-detail-backdrop"
        @click="imgRepoSelected=null"></div>
    <transition name="slide-right">
        <aside v-if="imgRepoSelected"
            class="img-repo-detail-panel shrink-0 flex flex-col min-h-0">

            {{-- Preview --}}
            <div class="img-repo-detail-header">
                <p class="img-repo-detail-title text-body font-semibold text-slate-800 truncate">@{{ imgRepoSelected.name }}</p>
                <button @click="imgRepoSelected=null" class="icon-toolbar-button text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-200 transition-colors ml-2 shrink-0">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <div class="flex-1 overflow-auto">
                <div v-if="imgRepoSelected.type === 'file'" class="p-4 border-b border-slate-200">
                    <div class="rounded-xl overflow-hidden bg-white border border-slate-200 aspect-square flex items-center justify-center">
                        <img :src="imgRepoThumbUrl(imgRepoSelected.path)"
                            class="max-w-full max-h-full object-contain p-2"
                            @@error="$event.target.style.display='none'">
                    </div>
                </div>

                <div v-if="imgRepoSelected.type === 'dir'" class="p-4 flex justify-center">
                    <i class="fa-solid fa-folder text-6xl text-amber-400"></i>
                </div>

                {{-- Meta --}}
                <div class="px-4 py-3 space-y-3">
                    <div>
                        <p class="text-overline text-slate-400 mb-1">DETAIL</p>
                        <div class="space-y-2">
                            <div class="flex justify-between text-body-sm">
                                <span class="text-slate-500">Tipe</span>
                                <span class="text-slate-800 font-medium">@{{ imgRepoSelected.type === 'dir' ? 'Folder' : imgRepoSelected.ext?.toUpperCase() }}</span>
                            </div>
                            <div v-if="imgRepoSelected.type === 'file'" class="flex justify-between text-body-sm">
                                <span class="text-slate-500">Ukuran</span>
                                <span class="text-slate-800 font-medium">@{{ imgRepoSelected.size ? (imgRepoSelected.size / 1024).toFixed(1) + ' KB' : '-' }}</span>
                            </div>
                            <div class="flex flex-col gap-0.5 text-body-sm">
                                <span class="text-slate-500">Path</span>
                                <span class="text-slate-600 break-all text-xs font-mono bg-slate-100 rounded px-2 py-1 mt-0.5">@{{ imgRepoSelected.path }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="img-repo-detail-actions">
                <button v-if="imgRepoSelected.type === 'dir'"
                    @click="imgRepoBrowse(imgRepoSelected.path)"
                    class="img-repo-detail-action img-repo-detail-action--primary">
                    <i class="fa-solid fa-folder-open"></i>
                    <span>Buka Folder</span>
                </button>
                <button @click="imgRepoStartRename(imgRepoSelected)"
                    class="img-repo-detail-action">
                    <i class="fa-solid fa-pencil"></i>
                    <span>Rename</span>
                </button>
                <button @click="imgRepoDelete(imgRepoSelected)"
                    class="img-repo-detail-action img-repo-detail-action--danger" aria-label="Hapus">
                    <i class="fa-solid fa-trash"></i>
                    <span>Hapus Item</span>
                </button>
            </div>
        </aside>
    </transition>

    {{-- ── Context Menu ──────────────────────────────────── --}}
    <div v-if="imgRepoContextMenu"
        :style="{ top: imgRepoContextMenu.y + 'px', left: imgRepoContextMenu.x + 'px' }"
        class="img-repo-context-menu fixed"
        @click.stop>
        <button v-if="imgRepoContextMenu.item.type === 'dir'"
            @click="imgRepoBrowse(imgRepoContextMenu.item.path); imgRepoContextMenu=null"
            class="img-repo-context-menu__item">
            <i class="fa-solid fa-folder-open"></i>
            <span>Buka</span>
        </button>
        <button @click="imgRepoStartRename(imgRepoContextMenu.item); imgRepoContextMenu=null"
            class="img-repo-context-menu__item">
            <i class="fa-solid fa-pencil"></i>
            <span>Rename</span>
        </button>
        <div class="img-repo-context-menu__separator"></div>
        <button @click="imgRepoDelete(imgRepoContextMenu.item); imgRepoContextMenu=null"
            class="img-repo-context-menu__item img-repo-context-menu__item--danger" aria-label="Hapus">
            <i class="fa-solid fa-trash"></i>
            <span>Hapus Item</span>
        </button>
    </div>
    {{-- Context menu backdrop --}}
    <div v-if="imgRepoContextMenu" class="fixed inset-0 z-40" @click="imgRepoContextMenu=null" @contextmenu.prevent="imgRepoContextMenu=null"></div>

    {{-- ── New Folder Modal ──────────────────────────────── --}}
    <teleport to="body">
        <transition name="fade">
            <div v-if="imgRepoMkdirOpen"
                class="fixed inset-0 z-[2500] flex items-end md:items-center justify-center md:p-6 overlay-motion-dialog">
                <div @click="imgRepoMkdirOpen=false"
                    class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm overlay-backdrop"></div>
                <div class="mobile-sheet modal-width-form relative w-full bg-white radius-sheet border border-slate-200 overflow-hidden animate-fadeIn flex flex-col">

                    {{-- Header --}}
                    <div class="modal-header-bar radius-sheet-top">
                        <div class="modal-header-copy">
                            <div class="modal-header-icon bg-slate-100 text-slate-500 border border-slate-200">
                                <i class="fa-solid fa-folder-plus"></i>
                            </div>
                            <div>
                                <div class="text-body font-semibold text-slate-800">Folder Baru</div>
                                <div class="text-body-sm text-slate-500">@{{ imgRepoPath ? 'Di dalam: ' + imgRepoPath : 'Di root' }}</div>
                            </div>
                        </div>
                        <button @click="imgRepoMkdirOpen=false"
                            class="icon-toolbar-button rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-5">
                        <label class="block text-body-sm font-medium text-slate-700 mb-1.5">Nama Folder</label>
                        <input v-model="imgRepoMkdirName"
                            @keydown.enter="imgRepoMkdir"
                            @keydown.escape="imgRepoMkdirOpen=false"
                            placeholder="Contoh: SAMSUNG, IPHONE 16, dll..."
                            class="form-input-compact w-full transition-all"
                            autofocus>
                        <p class="mt-2 text-body-sm text-slate-400">Gunakan huruf kapital sesuai konvensi folder yang ada.</p>
                    </div>

                    {{-- Footer --}}
                    <div class="modal-footer-bar">
                        <div class="modal-footer-actions">
                            <button @click="imgRepoMkdirOpen=false"
                                class="primary-cta-button primary-cta-button--neutral">Batal</button>
                            <button @click="imgRepoMkdir"
                                :disabled="!imgRepoMkdirName.trim() || imgRepoBusy"
                                class="primary-cta-button primary-cta-button--info active:scale-95 disabled:opacity-40">
                                <i v-if="imgRepoBusy" class="fa-solid fa-spinner animate-spin mr-1.5"></i>
                                Buat Folder
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </transition>
    </teleport>

</div>
