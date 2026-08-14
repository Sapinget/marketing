{{-- Image Repository – Google Drive-style --}}
<div v-show="activeTab === 'img_repo'"
    class="flex bg-white overflow-hidden"
    style="margin: -0.75rem -0.75rem 0; min-height: calc(100dvh - 64px);">

    {{-- ── Left Sidebar ──────────────────────────────────── --}}
    <aside class="w-56 shrink-0 flex flex-col gap-1 pt-4 pb-6 border-r border-slate-200 bg-white">

        {{-- New button --}}
        <div class="px-3 mb-2">
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button @click="imgRepoNewMenuOpen = !imgRepoNewMenuOpen"
                    class="flex items-center gap-3 px-4 py-3 rounded-2xl shadow-md bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition-all w-full font-medium text-body-sm">
                    <i class="fa-solid fa-plus text-slate-500"></i>
                    <span>Baru</span>
                    <i class="fa-solid fa-caret-down text-slate-400 text-xs ml-auto"></i>
                </button>
                <div v-if="imgRepoNewMenuOpen"
                    class="absolute left-0 top-full mt-1 w-44 bg-white border border-slate-200 rounded-xl shadow-lg z-30 py-1 overflow-hidden">
                    <label class="flex items-center gap-3 px-4 py-2.5 text-body-sm text-slate-700 hover:bg-slate-50 cursor-pointer transition-colors">
                        <i class="fa-solid fa-upload text-slate-400 w-4 text-center"></i>
                        <span>Upload File</span>
                        <input type="file" multiple accept="image/*" class="hidden" @change="imgRepoUploadFiles($event); imgRepoNewMenuOpen=false">
                    </label>
                    <button @click="imgRepoMkdirOpen=true; imgRepoMkdirName=''; imgRepoNewMenuOpen=false"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-body-sm text-slate-700 hover:bg-slate-50 transition-colors">
                        <i class="fa-solid fa-folder-plus text-slate-400 w-4 text-center"></i>
                        <span>Folder Baru</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Nav items --}}
        <button @click="imgRepoBrowse('')"
            :class="['flex items-center gap-3 mx-2 px-4 py-2 rounded-full text-body-sm font-medium transition-colors',
                imgRepoPath === '' ? 'bg-blue-100 text-blue-700' : 'text-slate-700 hover:bg-slate-100']">
            <i class="fa-solid fa-hard-drive w-4 text-center"></i>
            <span>Repo Gambar</span>
        </button>

        {{-- Quick folders --}}
        <div class="mt-2 px-3">
            <p class="text-overline text-slate-400 px-1 mb-1">FOLDER UTAMA</p>
            <button @click="imgRepoBrowse('APPLE')"
                :class="['w-full flex items-center gap-2 px-3 py-1.5 rounded-lg text-body-sm transition-colors text-left',
                    imgRepoPath === 'APPLE' || imgRepoPath.startsWith('APPLE/') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-slate-600 hover:bg-slate-50']">
                <i class="fa-brands fa-apple text-xs w-4 text-center"></i>
                APPLE
            </button>
            <button @click="imgRepoBrowse('ANDROID')"
                :class="['w-full flex items-center gap-2 px-3 py-1.5 rounded-lg text-body-sm transition-colors text-left',
                    imgRepoPath === 'ANDROID' || imgRepoPath.startsWith('ANDROID/') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-slate-600 hover:bg-slate-50']">
                <i class="fa-brands fa-android text-xs w-4 text-center"></i>
                ANDROID
            </button>
        </div>

        <div class="mt-auto px-5 pt-4 border-t border-slate-100">
            <p class="text-overline text-slate-400">resources/img</p>
        </div>
    </aside>

    {{-- ── Main Area ──────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Top bar --}}
        <div class="flex items-center gap-3 px-6 py-3 border-b border-slate-200 shrink-0">

            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-0.5 flex-1 min-w-0 text-body-sm">
                <template v-for="(crumb, idx) in imgRepoBreadcrumbs" :key="crumb.path">
                    <button v-if="idx < imgRepoBreadcrumbs.length - 1"
                        @click="imgRepoBrowse(crumb.path)"
                        class="px-2 py-1 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors shrink-0 font-medium">
                        @{{ crumb.label }}
                    </button>
                    <span v-if="idx < imgRepoBreadcrumbs.length - 1" class="text-slate-300 text-sm shrink-0">/</span>
                    <span v-if="idx === imgRepoBreadcrumbs.length - 1"
                        class="px-2 py-1 rounded-lg text-slate-800 font-semibold shrink-0 truncate" style="max-width:200px">
                        @{{ crumb.label }}
                    </span>
                </template>
            </nav>

            {{-- Upload progress badge --}}
            <div v-if="imgRepoUploading"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-blue-50 text-blue-600 text-body-sm font-medium shrink-0">
                <i class="fa-solid fa-spinner animate-spin text-xs"></i>
                <span>Uploading...</span>
            </div>

            {{-- View toggle --}}
            <div class="flex items-center rounded-lg border border-slate-200 overflow-hidden shrink-0">
                <button @click="imgRepoViewMode='grid'"
                    :class="['px-2.5 py-1.5 transition-colors', imgRepoViewMode==='grid' ? 'bg-blue-50 text-blue-600' : 'text-slate-500 hover:bg-slate-50']">
                    <i class="fa-solid fa-grip text-sm"></i>
                </button>
                <button @click="imgRepoViewMode='list'"
                    :class="['px-2.5 py-1.5 transition-colors border-l border-slate-200', imgRepoViewMode==='list' ? 'bg-blue-50 text-blue-600' : 'text-slate-500 hover:bg-slate-50']">
                    <i class="fa-solid fa-list text-sm"></i>
                </button>
            </div>

            {{-- Refresh --}}
            <button @click="imgRepoBrowse(imgRepoPath)" :disabled="imgRepoLoading"
                class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors disabled:opacity-40 shrink-0">
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
        <div class="flex-1 overflow-auto px-6 py-4" @click.self="imgRepoSelected=null; imgRepoContextMenu=null">

            {{-- Loading --}}
            <div v-if="imgRepoLoading && !imgRepoItems.length"
                style="display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:0.5rem; margin-top:0.5rem">
                <div v-for="n in 16" :key="n"
                    class="aspect-video rounded-xl bg-slate-100 animate-pulse"></div>
            </div>

            {{-- Empty --}}
            <div v-if="!imgRepoLoading && !imgRepoError && imgRepoItems.length === 0"
                class="flex flex-col items-center justify-center py-32 text-slate-400 select-none">
                <i class="fa-solid fa-folder-open text-5xl mb-4 opacity-30"></i>
                <p class="text-body font-medium">Folder kosong</p>
                <p class="text-body-sm mt-1">Upload file atau buat folder baru</p>
            </div>

            {{-- ─ GRID VIEW ─ --}}
            <template v-if="imgRepoViewMode === 'grid' && imgRepoItems.length">

                {{-- Folders section --}}
                <div v-if="imgRepoDirs.length">
                    <p class="text-overline text-slate-400 mb-2">FOLDER</p>
                    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:0.5rem; margin-bottom:1.5rem">
                        <template v-for="item in imgRepoDirs" :key="'d-'+item.path">

                            {{-- Rename inline --}}
                            <div v-if="imgRepoRenameItem?.path === item.path"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl border-2 border-blue-400 bg-blue-50">
                                <i class="fa-solid fa-folder text-xl text-amber-400 shrink-0"></i>
                                <input v-model="imgRepoRenameName"
                                    @keydown.enter="imgRepoRenameSubmit"
                                    @keydown.escape="imgRepoCancelRename"
                                    class="flex-1 text-body-sm border border-blue-300 rounded-lg px-2 py-1 outline-none bg-white"
                                    autofocus @click.stop>
                                <button @click.stop="imgRepoRenameSubmit" :disabled="imgRepoBusy"
                                    class="shrink-0 px-2 py-1 text-xs bg-blue-600 text-white rounded-lg disabled:opacity-50">OK</button>
                            </div>

                            {{-- Normal folder card --}}
                            <div v-else
                                @click.stop="imgRepoBrowse(item.path)"
                                @contextmenu.prevent="imgRepoOpenContext($event, item)"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer group transition-all select-none border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300">
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
                    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:0.5rem">
                        <template v-for="item in imgRepoFiles" :key="'f-'+item.path">

                            {{-- Rename inline --}}
                            <div v-if="imgRepoRenameItem?.path === item.path"
                                class="flex flex-col rounded-xl border-2 border-blue-400 bg-blue-50 overflow-hidden">
                                <div class="aspect-[4/3] bg-slate-100 overflow-hidden">
                                    <img :src="imgRepoThumbUrl(item.path)" class="w-full h-full object-contain">
                                </div>
                                <div class="flex flex-col gap-1 p-2">
                                    <input v-model="imgRepoRenameName"
                                        @keydown.enter="imgRepoRenameSubmit"
                                        @keydown.escape="imgRepoCancelRename"
                                        class="text-xs border border-blue-300 rounded px-1.5 py-0.5 outline-none bg-white"
                                        autofocus @click.stop>
                                    <div class="flex gap-1">
                                        <button @click.stop="imgRepoRenameSubmit" :disabled="imgRepoBusy"
                                            class="flex-1 px-2 py-0.5 text-xs bg-blue-600 text-white rounded disabled:opacity-50">Simpan</button>
                                        <button @click.stop="imgRepoCancelRename"
                                            class="flex-1 px-2 py-0.5 text-xs bg-slate-200 text-slate-700 rounded">Batal</button>
                                    </div>
                                </div>
                            </div>

                            {{-- Normal file card --}}
                            <div v-else
                                @click.stop="imgRepoSelectItem(item)"
                                @contextmenu.prevent="imgRepoOpenContext($event, item)"
                                :class="['flex flex-col rounded-xl border cursor-pointer group transition-all select-none overflow-hidden',
                                    imgRepoSelected?.path === item.path
                                        ? 'border-blue-300 bg-blue-50'
                                        : 'border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300']">
                                <div class="aspect-[4/3] bg-slate-100 overflow-hidden relative">
                                    <img :src="imgRepoThumbUrl(item.path)"
                                        class="w-full h-full object-contain"
                                        loading="lazy"
                                        @@error="$event.target.parentElement.classList.add('flex','items-center','justify-center'); $event.target.style.display='none'">
                                    {{-- three-dots on hover --}}
                                    <button @click.stop="imgRepoOpenContext($event, item)"
                                        class="absolute top-1.5 right-1.5 w-7 h-7 rounded-full flex items-center justify-center bg-white/90 shadow-sm text-slate-500 opacity-0 group-hover:opacity-100 hover:bg-white transition-all">
                                        <i class="fa-solid fa-ellipsis-vertical text-xs"></i>
                                    </button>
                                </div>
                                <div class="px-2 py-1.5 flex items-center gap-1">
                                    <i class="fa-regular fa-image text-slate-300 text-xs shrink-0"></i>
                                    <span class="text-xs text-slate-700 truncate">@{{ item.name }}</span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            {{-- ─ LIST VIEW ─ --}}
            <template v-if="imgRepoViewMode === 'list' && imgRepoItems.length">
                <table class="w-full text-body-sm">
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
                                class="bg-blue-50 border-b border-slate-100">
                                <td class="py-2 px-3" colspan="4">
                                    <div class="flex items-center gap-3">
                                        <i :class="['text-base w-5 text-center', item.type==='dir' ? 'fa-solid fa-folder text-amber-400' : 'fa-regular fa-image text-slate-400']"></i>
                                        <input v-model="imgRepoRenameName"
                                            @keydown.enter="imgRepoRenameSubmit"
                                            @keydown.escape="imgRepoCancelRename"
                                            class="flex-1 border border-blue-300 rounded-lg px-2 py-0.5 text-body-sm outline-none bg-white"
                                            autofocus @click.stop>
                                        <button @click.stop="imgRepoRenameSubmit" :disabled="imgRepoBusy"
                                            class="px-3 py-0.5 text-xs bg-blue-600 text-white rounded-lg disabled:opacity-50">Simpan</button>
                                        <button @click.stop="imgRepoCancelRename"
                                            class="px-3 py-0.5 text-xs bg-slate-200 rounded-lg">Batal</button>
                                    </div>
                                </td>
                            </tr>

                            {{-- Normal row --}}
                            <tr v-else
                                @click.stop="item.type === 'dir' ? imgRepoBrowse(item.path) : imgRepoSelectItem(item)"
                                @contextmenu.prevent="imgRepoOpenContext($event, item)"
                                :class="['border-b border-slate-100 cursor-pointer group transition-colors',
                                    imgRepoSelected?.path === item.path ? 'bg-blue-50' : 'hover:bg-slate-50']">
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
            </template>
        </div>
    </div>

    {{-- ── Detail Panel ──────────────────────────────────── --}}
    <transition name="slide-right">
        <aside v-if="imgRepoSelected"
            class="w-72 shrink-0 flex flex-col border-l border-slate-200 bg-slate-50">

            {{-- Preview --}}
            <div class="flex items-center justify-between px-4 pt-4 pb-3 border-b border-slate-200">
                <p class="text-body font-semibold text-slate-800 truncate" style="max-width:180px">@{{ imgRepoSelected.name }}</p>
                <button @click="imgRepoSelected=null" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-200 transition-colors ml-2 shrink-0">
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
            <div class="px-4 py-3 border-t border-slate-200 flex flex-col gap-2">
                <button v-if="imgRepoSelected.type === 'dir'"
                    @click="imgRepoBrowse(imgRepoSelected.path)"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-body-sm font-medium bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                    <i class="fa-solid fa-folder-open"></i>
                    <span>Buka Folder</span>
                </button>
                <button @click="imgRepoStartRename(imgRepoSelected)"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-body-sm font-medium bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition-colors">
                    <i class="fa-solid fa-pencil text-slate-400"></i>
                    <span>Rename</span>
                </button>
                <button @click="imgRepoDelete(imgRepoSelected)"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-body-sm font-medium bg-white border border-red-200 text-red-600 hover:bg-red-50 transition-colors" aria-label="Hapus">
                    <i class="fa-solid fa-trash text-red-400"></i>
                </button>
            </div>
        </aside>
    </transition>

    {{-- ── Context Menu ──────────────────────────────────── --}}
    <div v-if="imgRepoContextMenu"
        :style="{ top: imgRepoContextMenu.y + 'px', left: imgRepoContextMenu.x + 'px' }"
        class="fixed z-50 bg-white border border-slate-200 rounded-xl shadow-xl py-1.5 w-44 overflow-hidden"
        @click.stop>
        <button v-if="imgRepoContextMenu.item.type === 'dir'"
            @click="imgRepoBrowse(imgRepoContextMenu.item.path); imgRepoContextMenu=null"
            class="w-full flex items-center gap-3 px-4 py-2 text-body-sm text-slate-700 hover:bg-slate-50 transition-colors">
            <i class="fa-solid fa-folder-open text-slate-400 w-4 text-center"></i>
            <span>Buka</span>
        </button>
        <button @click="imgRepoStartRename(imgRepoContextMenu.item); imgRepoContextMenu=null"
            class="w-full flex items-center gap-3 px-4 py-2 text-body-sm text-slate-700 hover:bg-slate-50 transition-colors">
            <i class="fa-solid fa-pencil text-slate-400 w-4 text-center"></i>
            <span>Rename</span>
        </button>
        <div class="my-1 border-t border-slate-100"></div>
        <button @click="imgRepoDelete(imgRepoContextMenu.item); imgRepoContextMenu=null"
            class="w-full flex items-center gap-3 px-4 py-2 text-body-sm text-red-600 hover:bg-red-50 transition-colors" aria-label="Hapus">
            <i class="fa-solid fa-trash w-4 text-center"></i>
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
                            class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
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
                            class="w-full border border-slate-300 rounded-xl px-4 py-3 text-body outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
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
