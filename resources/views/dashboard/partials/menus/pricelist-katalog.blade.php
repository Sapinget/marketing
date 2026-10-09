@if(!($catalogTemplateModalOnly ?? false))
@verbatim
<div v-if="activeTab === 'pricelist_katalog'" class="space-y-4 animate-fadeIn xl:h-[calc(100dvh-7rem)] xl:min-h-[620px] xl:overflow-hidden">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="type-title text-slate-900">Katalog Pricelist</h1>
            <p class="text-body-sm text-slate-400 mt-1">Sync pricelist Android dari spreadsheet, upload background, dan generate katalog Story / IG Feed.</p>
        </div>
        <button @click="syncPricelistProducts" :disabled="pricelistSyncing" class="primary-cta-button primary-cta-button--accent w-full sm:w-auto">
            <i class="fa-solid fa-rotate"></i>
            <span>{{ pricelistSyncing ? 'Sync...' : 'Sync Data' }}</span>
        </button>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.4fr)_minmax(340px,0.6fr)] gap-4 xl:h-[calc(100%-4.5rem)] min-h-0">
        <div class="section-card flex flex-col min-h-[420px] md:min-h-[520px] xl:min-h-0 overflow-hidden">
            <div class="px-4 md:px-6 py-4 border-b border-slate-100 space-y-3">
                <div>
                    <h3 class="type-label font-semibold text-slate-700">Data Pricelist</h3>
                    <p class="text-body-sm text-slate-400 mt-0.5">Hanya row dengan kolom URUT terisi yang masuk katalog.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button @click="pricelistSelectAll" class="toolbar-segment-button border-slate-200 text-slate-600 bg-white">Pilih semua</button>
                    <button @click="pricelistDeselectAll" class="toolbar-segment-button border-slate-200 text-slate-600 bg-white">Batal pilih</button>
                    <button @click="pricelistShowSelectedOnly = !pricelistShowSelectedOnly" :class="['toolbar-segment-button border-slate-200', pricelistShowSelectedOnly ? 'bg-ppp-accent text-white' : 'text-slate-600 bg-white']">{{ pricelistShowSelectedOnly ? 'Tampilkan semua' : 'Filter terpilih' }}</button>
                    <span class="text-body-sm text-slate-400">{{ pricelistSelectedIds.length }} dipilih</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-[minmax(0,1fr)_auto_auto] items-center gap-2 w-full">
                    <input v-model="pricelistSearch" class="form-input-compact" placeholder="Cari produk..." />
                    <div class="catalog-segment-group w-full sm:w-24 sm:justify-self-end">
                        <button @click="pricelistView = 'card'" :class="['catalog-segment-button', pricelistView === 'card' ? 'catalog-segment-button--active' : '']" title="Card view" aria-label="Card view">
                            <i class="fa-solid fa-grip"></i>
                        </button>
                        <button @click="pricelistView = 'table'" :class="['catalog-segment-button', pricelistView === 'table' ? 'catalog-segment-button--active' : '']" title="Table view" aria-label="Table view">
                            <i class="fa-solid fa-table-list"></i>
                        </button>
                    </div>
                    <div class="relative group search-select-container w-full sm:w-40 sm:justify-self-end">
                        <button type="button" @click="toggleSearchSelect($event, 'pricelist_sheet_filter')" :aria-expanded="searchSelectOpen === 'pricelist_sheet_filter' ? 'true' : 'false'" class="select-trigger-button select-trigger-button-compact toolbar-trigger-field-form">
                            <span class="truncate">{{ currentPricelistSheetFilterLabel }}</span>
                            <i class="fa-solid fa-chevron-down ml-auto text-[9px] text-slate-400"></i>
                        </button>
                        <transition name="fade">
                            <div v-if="searchSelectOpen === 'pricelist_sheet_filter'" :style="popoverStyle" class="search-select-popover">
                                <div class="p-2 border-b border-slate-100">
                                    <input v-model="searchSelectQuery" class="form-input-popover" placeholder="Cari brand..." />
                                </div>
                                <div class="max-h-56 overflow-y-auto custom-scrollbar p-1">
                                    <div @click="pricelistSheetFilter = 'all'; pricelistPage = 1; searchSelectOpen = null" :class="['popover-option', pricelistSheetFilter === 'all' ? 'popover-option-active' : '']">Semua</div>
                                    <div v-for="sheet in filteredPricelistBrandSheetOptions" :key="sheet" @click="pricelistSheetFilter = sheet; pricelistPage = 1; searchSelectOpen = null" :class="['popover-option', pricelistSheetFilter === sheet ? 'popover-option-active' : '']">{{ sheet }}</div>
                                    <div v-if="!filteredPricelistBrandSheetOptions.length" class="px-3 py-3 text-body-sm text-slate-400">Belum ada brand</div>
                                </div>
                            </div>
                        </transition>
                    </div>
                </div>
            </div>
            <div v-if="pricelistView === 'table'" class="flex-1 min-h-0 overflow-auto overscroll-x-contain">
                <table class="ppp-table w-full min-w-[720px]">
                    <thead>
                        <tr class="table-header-row">
                            <th class="table-header-cell table-header-index table-freeze-index">Pilih</th>
                            <th class="table-header-cell table-header-index table-freeze-index">#</th>
                            <th class="table-header-cell">Brand</th>
                            <th class="table-header-cell">Produk</th>
                            <th class="table-header-cell text-right">Harga Nasional</th>
                            <th class="table-header-cell text-right">Special Price</th>
                            <th class="table-header-cell text-center">Aktif</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="pagedPricelistProducts.length === 0">
                            <td colspan="7" class="text-center text-slate-400 py-10">Belum ada data pricelist</td>
                        </tr>
                        <tr v-for="row in pagedPricelistProducts" :key="row.ID">
                            <td class="px-4 py-3 text-center"><input type="checkbox" :checked="pricelistIsSelected(row)" @change="pricelistToggleSelected(row)" /></td>
                            <td class="px-4 py-3 text-center text-body-sm font-bold text-slate-400 tabular-nums table-freeze-index">{{ row.urut }}</td>
                            <td class="text-body-sm font-semibold text-slate-700">{{ row.source_sheet }}</td>
                            <td class="text-body-sm">
                                <div class="font-semibold text-slate-800">{{ row.nama_produk }}</div>
                                <div class="text-slate-400">{{ [row.ram, row.storage, row.warna].filter(Boolean).join(' · ') }}</div>
                            </td>
                            <td class="text-right text-body-sm text-slate-500 line-through">{{ formatPricelistPrice(row.harga_nasional) }}</td>
                            <td class="text-right text-body-sm font-bold text-red-600">{{ formatPricelistPrice(row.special_price) }}</td>
                            <td class="text-center">
                                <span :class="['px-2 py-0.5 rounded-full text-overline font-bold', row.is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400']">{{ row.is_active ? 'ON' : 'OFF' }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="flex-1 min-h-0 overflow-y-auto p-3 md:p-4">
                <div v-if="pricelistCardGroups.length === 0" class="text-center text-slate-400 py-10">Belum ada data pricelist</div>
                <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4 2xl:grid-cols-5 gap-2 md:gap-3 content-start">
                    <div v-for="group in pricelistCardGroups" :key="group.model" class="relative border border-slate-200 bg-white rounded-2xl p-2.5 md:p-3 transition-all hover:border-slate-300">
                        <div class="absolute top-2 right-2 z-10">
                            <span :class="['px-2 py-0.5 rounded-full text-overline font-bold', group.variants.some(row => row.is_active !== false) ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400']">
                                {{ group.variants.some(row => row.is_active !== false) ? 'ON' : 'OFF' }}
                            </span>
                        </div>
                        <div class="flex justify-center mb-1.5">
                            <div class="apple-product-thumb bg-slate-50 rounded-lg overflow-hidden flex items-center justify-center">
                                <img v-if="androidProductImage(group.variants[0])" :src="androidProductImage(group.variants[0])" class="apple-product-image" :alt="group.model" />
                                <i v-else class="fa-solid fa-mobile-screen-button text-slate-200 text-4xl"></i>
                            </div>
                        </div>
                        <p class="text-body-sm font-bold text-slate-700 text-center leading-tight mb-1.5">{{ group.model }}</p>
                        <p class="text-overline text-slate-400 text-center font-bold uppercase mb-2">{{ group.variants[0].source_sheet }}</p>
                        <div v-for="row in group.variants" :key="row.ID" class="rounded-lg mt-1 bg-slate-50/80 px-2 py-1.5 min-w-0">
                            <div class="flex items-center gap-1.5 min-w-0 cursor-pointer" @click="pricelistToggleSelected(row)">
                                <div class="flex-shrink-0 w-3 h-3 rounded-full border flex items-center justify-center" :class="pricelistIsSelected(row) ? 'bg-ppp-accent border-ppp-accent' : 'border-slate-300 bg-white'">
                                    <i v-if="pricelistIsSelected(row)" class="apple-check-icon fa-solid fa-check text-white"></i>
                                </div>
                                <div class="text-overline font-semibold text-slate-600 truncate min-w-0 flex-1">{{ pricelistCardVariantLabel(row) }}</div>
                            </div>
                            <div class="mt-1 grid grid-cols-1 gap-0.5 tabular-nums min-w-0 pl-4">
                                <span class="text-[9px] leading-none text-slate-400 line-through truncate">{{ formatPricelistPrice(row.harga_nasional) }}</span>
                                <span class="text-[10px] leading-tight font-bold text-red-600 truncate">{{ formatPricelistPrice(row.special_price) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="pricelistView === 'table'" class="table-pager-bar flex-shrink-0">
                <div class="text-body-sm text-slate-400 font-medium">{{ filteredPricelistProducts.length }} data</div>
                <div class="flex items-center gap-1">
                    <button @click="pricelistPage--" :disabled="pricelistPage <= 1" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-left text-body-sm"></i></button>
                    <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ pricelistPage }} / {{ pricelistTotalPages }}</span>
                    <button @click="pricelistPage++" :disabled="pricelistPage >= pricelistTotalPages" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-right text-body-sm"></i></button>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-3 min-h-0 xl:overflow-y-auto xl:pr-0.5">
            <div class="section-card p-4 catalog-control-stack">
                <div class="catalog-control-field">
                    <p class="text-overline text-slate-400 uppercase">Pengaturan Output</p>
                    <div class="catalog-segment-group">
                        <button @click="catalogOutputMode = 'list'" :class="['catalog-segment-button', catalogOutputMode === 'list' ? 'catalog-segment-button--active' : '']">List (Tabel)</button>
                        <button @click="catalogOutputMode = 'katalog'" :class="['catalog-segment-button', catalogOutputMode === 'katalog' ? 'catalog-segment-button--active' : '']">Katalog (Card)</button>
                    </div>
                </div>
                <div class="catalog-control-grid">
                    <div class="catalog-control-field">
                        <p class="catalog-control-label">Template</p>
                        <div class="relative group search-select-container min-w-0">
                            <button type="button" @click="toggleSearchSelect($event, 'catalog_template')" :aria-expanded="searchSelectOpen === 'catalog_template' ? 'true' : 'false'" class="select-trigger-button select-trigger-button-compact select-trigger-button-form toolbar-trigger-field-form w-full">
                                <span class="min-w-0 truncate">{{ currentCatalogTemplateLabel }}</span>
                                <i class="fa-solid fa-chevron-down ml-auto text-[9px] text-slate-400"></i>
                            </button>
                            <transition name="fade">
                                <div v-if="searchSelectOpen === 'catalog_template'" :style="popoverStyle" class="search-select-popover">
                                    <div class="p-2 border-b border-slate-100">
                                        <input v-model="searchSelectQuery" class="form-input-popover" placeholder="Cari template..." />
                                    </div>
                                    <div class="max-h-56 overflow-y-auto custom-scrollbar p-1">
                                        <div v-for="template in filteredCatalogTemplateOptions" :key="template.ID" @click="catalogSelectedTemplateId = template.ID; searchSelectOpen = null" :class="['popover-option', catalogSelectedTemplateId === template.ID ? 'popover-option-active' : '']">
                                            <div class="font-bold">{{ template.name }}</div>
                                            <div class="text-overline text-slate-400 uppercase">{{ template.format }} · {{ template.canvas_width }}x{{ template.canvas_height }}</div>
                                        </div>
                                        <div v-if="filteredCatalogTemplateOptions.length === 1 && !catalogTemplates.length" class="px-3 py-2 text-body-sm text-slate-400">Belum ada template upload</div>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>
                    <div class="catalog-control-field">
                        <p class="catalog-control-label">Brand</p>
                        <div class="relative group search-select-container min-w-0">
                            <button type="button" @click="toggleSearchSelect($event, 'catalog_sheet')" :aria-expanded="searchSelectOpen === 'catalog_sheet' ? 'true' : 'false'" class="select-trigger-button select-trigger-button-compact select-trigger-button-form toolbar-trigger-field-form w-full">
                                <span class="min-w-0 truncate">{{ currentCatalogSheetLabel }}</span>
                                <i class="fa-solid fa-chevron-down ml-auto text-[9px] text-slate-400"></i>
                            </button>
                            <transition name="fade">
                                <div v-if="searchSelectOpen === 'catalog_sheet'" :style="popoverStyle" class="search-select-popover">
                                    <div class="p-2 border-b border-slate-100">
                                        <input v-model="searchSelectQuery" class="form-input-popover" placeholder="Cari brand..." />
                                    </div>
                                    <div class="max-h-56 overflow-y-auto custom-scrollbar p-1">
                                        <div @click="catalogSelectedSheet = 'all'; searchSelectOpen = null" :class="['popover-option', catalogSelectedSheet === 'all' ? 'popover-option-active' : '']">Semua</div>
                                        <div v-for="sheet in filteredCatalogBrandSheetOptions" :key="sheet" @click="catalogSelectedSheet = sheet; searchSelectOpen = null" :class="['popover-option', catalogSelectedSheet === sheet ? 'popover-option-active' : '']">{{ sheet }}</div>
                                        <div v-if="!filteredCatalogBrandSheetOptions.length" class="px-3 py-3 text-body-sm text-slate-400">Belum ada brand</div>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>
                    <div class="catalog-control-field">
                        <p class="catalog-control-label">URUT awal</p>
                        <input v-model="catalogUrutStart" class="form-input-compact" placeholder="Awal" />
                    </div>
                    <div class="catalog-control-field">
                        <p class="catalog-control-label">URUT akhir</p>
                        <input v-model="catalogUrutEnd" class="form-input-compact" placeholder="Akhir" />
                    </div>
                </div>
                <div v-if="catalogOutputMode === 'katalog'" class="catalog-control-field">
                    <p class="catalog-control-label">Kolom per baris</p>
                    <div class="search-select-container">
                        <button @click="toggleSearchSelect($event, 'catalog-columns-per-row')" type="button" :aria-expanded="searchSelectOpen === 'catalog-columns-per-row'" class="select-trigger-button select-trigger-button-compact select-trigger-button-form toolbar-trigger-field-form w-full">
                            <span class="truncate">{{ catalogColumnsPerRowLabel }}</span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                        </button>
                        <div v-if="searchSelectOpen === 'catalog-columns-per-row'" class="search-select-popover" :style="popoverStyle">
                            <button v-for="col in catalogColumnOptions" :key="col" @click="catalogColumnsPerRow = col; searchSelectOpen = null" type="button" :class="['popover-option', catalogColumnsPerRow === col ? 'popover-option-active' : '']">
                                {{ col }} kolom
                            </button>
                        </div>
                    </div>
                </div>
                <div class="catalog-control-field">
                    <p class="catalog-control-label">Kolom harga</p>
                    <div class="catalog-segment-group">
                        <button v-for="opt in [{ key: 'special_price', label: 'Special Price' }, { key: 'harga_jual', label: 'Harga Jual' }]" :key="opt.key"
                            @click="catalogPriceKey = opt.key"
                            :class="['catalog-segment-button', catalogPriceKey === opt.key ? 'catalog-segment-button--active' : '']">
                            {{ opt.label }}
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <button @click="generateCatalogPreview" :disabled="catalogGenerating" class="primary-cta-button primary-cta-button--accent w-full">
                        <i :class="['fa-solid', catalogGenerating ? 'fa-spinner fa-spin' : 'fa-eye']"></i>
                        <span>{{ catalogGenerating ? 'Generate...' : 'Preview' }}</span>
                    </button>
                    <button @click="exportCatalogToPdf" :disabled="catalogGenerating" class="primary-cta-button primary-cta-button--info primary-cta-button--icon-only" aria-label="Export PDF">
                        <i :class="['fa-solid', catalogGenerating ? 'fa-spinner fa-spin' : 'fa-file-pdf']"></i>
                    </button>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2" v-if="catalogPreviewImages.length">
                <div v-for="(image, idx) in catalogPreviewImages" :key="idx" @click="openCatalogPreviewModal(idx)" class="block cursor-pointer border border-slate-100 rounded-xl overflow-hidden hover:border-ppp-accent/40 transition-colors">
                    <img :src="image" class="w-full h-auto" />
                </div>
            </div>
        </div>
    </div>

    <teleport to="body">
        <div v-if="catalogPreviewModalOpen" class="fixed inset-0 z-[9400] bg-slate-950/80 flex flex-col" @click="closeCatalogPreviewModal">
            <div class="shrink-0 px-3 md:px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3 bg-white/95 border-b border-slate-200" @click.stop>
                <div class="min-w-0">
                    <h3 class="type-label font-bold text-slate-800">Preview Katalog Pricelist</h3>
                    <p class="text-body-sm text-slate-500 truncate">Halaman {{ catalogPreviewModalIndex + 1 }} / {{ catalogPreviewImages.length }} · Zoom {{ Math.round(catalogPreviewZoom * 100) }}%</p>
                </div>
                <div class="flex items-center gap-1.5 flex-wrap">
                    <button @click="catalogPreviewNav(-1)" class="icon-utility-button icon-utility-bordered" aria-label="Previous preview" title="Previous preview"><i class="fa-solid fa-chevron-left"></i></button>
                    <button @click="catalogPreviewZoomBy(0.85)" class="icon-utility-button icon-utility-bordered" aria-label="Zoom out preview" title="Zoom out preview"><i class="fa-solid fa-magnifying-glass-minus"></i></button>
                    <button @click="catalogPreviewZoom = 1" class="icon-utility-button icon-utility-bordered" aria-label="Fit preview" title="Fit preview"><i class="fa-solid fa-expand"></i></button>
                    <button @click="catalogPreviewZoomBy(1.18)" class="icon-utility-button icon-utility-bordered" aria-label="Zoom in preview" title="Zoom in preview"><i class="fa-solid fa-magnifying-glass-plus"></i></button>
                    <button @click="catalogPreviewNav(1)" class="icon-utility-button icon-utility-bordered" aria-label="Next preview" title="Next preview"><i class="fa-solid fa-chevron-right"></i></button>
                    <button @click="downloadCatalogPreview(catalogPreviewImages[catalogPreviewModalIndex], catalogPreviewModalIndex)" class="icon-utility-button icon-utility-bordered" aria-label="Download preview" title="Download preview"><i class="fa-solid fa-download"></i></button>
                    <button @click="closeCatalogPreviewModal" class="icon-utility-button icon-utility-bordered" aria-label="Close preview" title="Close preview"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>
            <div class="flex-1 overflow-auto p-2 md:p-4" @click.stop>
                <div class="min-w-full min-h-full flex items-center justify-center">
                    <img :src="catalogPreviewImages[catalogPreviewModalIndex]" class="preview-zoom-image max-w-full max-h-[calc(100vh-7rem)] w-auto h-auto object-contain rounded-xl shadow-2xl bg-white transition-transform origin-center" :style="`transform:scale(${catalogPreviewZoom})`" />
                </div>
            </div>
        </div>
    </teleport>
    </div>
@endverbatim
@endif
@verbatim
    <div v-if="catalogTemplateModalOpen" class="fixed inset-0 z-[9000] bg-white">
        <div class="flex h-full w-full flex-col overflow-hidden">
            <div class="flex items-center justify-between px-5 py-5 border-b border-slate-100 shrink-0">
                <h3 class="type-label font-bold text-slate-800">{{ catalogTemplateModalType === 'edit' ? 'Edit Template' : 'Tambah Template' }}</h3>
                <button type="button" @click="closeCatalogTemplateModal" class="icon-utility-button"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="flex-1 min-h-0 overflow-y-auto p-4 md:p-5 flex flex-col gap-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="space-y-1.5">
                    <label class="type-micro text-slate-400 block">Nama template</label>
                    <input v-model="catalogTemplateForm.name" class="form-input-compact w-full" placeholder="Contoh: Pricelist Ramadan" />
                </div>
                <div class="space-y-1.5">
                    <label class="type-micro text-slate-400 block">Format template</label>
                    <div class="relative search-select-container w-full">
                        <button @click="toggleSearchSelect($event, 'catalog-template-format')" type="button" :aria-expanded="searchSelectOpen === 'catalog-template-format'" class="select-trigger-button-form toolbar-trigger-field-form w-full">
                            <span class="truncate">{{ catalogTemplateFormatLabel }}</span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                        </button>
                <div v-if="searchSelectOpen === 'catalog-template-format'" class="search-select-popover flex gap-1 p-1" :style="popoverStyle">
                    <button v-for="format in catalogTemplateFormatOptions" :key="format.key" @click="catalogTemplateForm.format = format.key; searchSelectOpen = null" type="button" :class="['popover-option flex-1 whitespace-nowrap', catalogTemplateForm.format === format.key ? 'popover-option-active' : '']">
                        {{ format.label }}
                    </button>
                </div>
            </div>
        </div>
    </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3 space-y-2">
                <label class="type-overline text-slate-400 uppercase block">Tipe Output</label>
                <div class="catalog-segment-group w-full">
                    <button type="button" @click="catalogTemplateForm.output_mode = 'list'" :class="['catalog-segment-button flex-1', catalogTemplateForm.output_mode === 'list' ? 'catalog-segment-button--active' : '']">List (Tabel)</button>
                    <button type="button" @click="catalogTemplateForm.output_mode = 'katalog'" :class="['catalog-segment-button flex-1', catalogTemplateForm.output_mode === 'katalog' ? 'catalog-segment-button--active' : '']">Katalog Produk</button>
                </div>
                <p class="text-body-sm text-slate-400">Tipe ini otomatis diterapkan ketika template dipilih pada Katalog Android.</p>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_minmax(340px,0.9fr)] gap-4 flex-1 min-h-0">
                <div class="space-y-3">
                    <div>
                        <div class="catalog-layout-drag relative mx-auto bg-slate-100 rounded-xl overflow-hidden border border-slate-200 cursor-move select-none"
                            :style="`height:min(58vh,640px);max-width:100%;aspect-ratio:${catalogTemplateForm.canvas_width || 1080}/${catalogTemplateForm.canvas_height || (catalogTemplateForm.format === 'feed' ? 1350 : 1920)}`"
                            @pointerdown="catalogLayoutDragStart($event, catalogTemplateForm)">
                            <img v-if="catalogTemplateForm.background_url" :src="catalogTemplateForm.background_url" class="absolute inset-0 w-full h-full object-cover pointer-events-none" />
                            <div v-else class="absolute inset-0 bg-gradient-to-br from-orange-400 to-amber-500 pointer-events-none"></div>
                            <div class="absolute left-1/2 top-0 bottom-0 border-l border-cyan-400 border-dashed pointer-events-none"></div>
                            <div class="absolute top-1/2 left-0 right-0 border-t border-cyan-400 border-dashed pointer-events-none"></div>
                            <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 px-2 py-0.5 rounded-full bg-slate-900/70 text-white text-[9px] font-bold uppercase pointer-events-none">CENTER</div>
                            <div v-if="catalogSelectedAssetsBounds(catalogTemplateForm)"
                                class="absolute border-2 border-dashed border-indigo-500 bg-indigo-500/10 rounded z-20 cursor-move touch-none select-none will-change-transform"
                                :style="catalogSelectedAssetsBounds(catalogTemplateForm)"
                                @pointerdown.stop="catalogSelectedAssetsDragStart($event, catalogTemplateForm)">
                                <button type="button" @click.stop="catalogAlignSelectedAssets('canvasCenter')"
                                    class="pointer-events-auto absolute -top-8 left-1/2 -translate-x-1/2 px-2.5 py-1 rounded bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-bold whitespace-nowrap shadow flex items-center gap-1 cursor-pointer">
                                    <i class="fa-solid fa-arrows-to-dot text-[9px]"></i>
                                    <span>Center Kanvas</span>
                                </button>
                            </div>
                            <div v-for="(asset, assetIndex) in catalogTemplateForm.layout_config.assets || []" :key="asset.id"
                                :class="[
                                    'absolute border touch-none select-none group/asset transition-shadow',
                                    catalogSelectedAssetIds.includes(asset.id)
                                        ? 'border-indigo-500 ring-2 ring-indigo-400/80 shadow-md bg-indigo-500/10'
                                        : 'border-dashed border-fuchsia-400'
                                ]"
                                :style="catalogAssetPreviewStyle(asset, catalogTemplateForm, assetIndex)"
                                @pointerdown.stop="catalogLayoutDragStart($event, catalogTemplateForm, 'asset', assetIndex)"
                                :title="`${asset.name} — geser posisi (Shift+klik untuk multi-select)`">
                                <img :src="asset.src" :alt="asset.name" draggable="false" class="w-full h-full object-contain pointer-events-none" @load="catalogOnAssetImageLoad($event, asset)" />
                                <div class="absolute -top-3 left-1/2 -translate-x-1/2 h-3.5 w-3.5 rounded-full border border-white bg-indigo-600 shadow cursor-grab flex items-center justify-center text-white text-[7px]" @pointerdown.stop="catalogAssetRotateStart($event, assetIndex)" title="Putar stiker (rotate)"><i class="fa-solid fa-rotate"></i></div>
                                <div class="absolute -bottom-1.5 -right-1.5 h-3.5 w-3.5 rounded-full border border-white bg-fuchsia-600 shadow cursor-nwse-resize" @pointerdown.stop="catalogAssetResizeStart($event, assetIndex)" title="Tarik untuk ubah ukuran"></div>
                            </div>
                            <div v-if="catalogTemplateForm.output_mode !== 'katalog' || !catalogTemplateEditorPreviewImage" class="absolute px-2 py-1 border-2 border-dashed border-cyan-400 rounded-md text-white font-extrabold uppercase text-center whitespace-nowrap cursor-move select-none group/title"
                                :style="catalogTitlePreviewStyle(catalogTemplateForm)"
                                @pointerdown.stop="catalogLayoutDragStart($event, catalogTemplateForm, 'title')"
                                @dblclick.stop="catalogPickColorSafe(() => catalogTemplateForm.layout_config.titleColor, v => catalogTemplateForm.layout_config.titleColor = v, $event)"
                                title="Geser judul · klik dua kali untuk warna (Drag judul brand)">
                                <span class="block text-left">{{ catalogEditorPreviewTitle.category }}</span>
                                <span class="block mt-1 text-left text-[0.62em] font-medium normal-case opacity-80">{{ catalogEditorPreviewTitle.condition }}</span>
                                <div class="absolute -bottom-1.5 -right-1.5 h-3.5 w-3.5 rounded-full border border-white bg-cyan-600 shadow cursor-ns-resize opacity-0 group-hover/title:opacity-100"
                                    @pointerdown.stop="catalogTitleResizeStart"
                                    title="Tarik untuk ubah ukuran font judul"></div>
                            </div>
                            <div v-if="catalogTemplateForm.output_mode !== 'katalog' || !catalogTemplateEditorPreviewImage">
                                <div v-for="(box, textBoxIndex) in catalogTemplateForm.layout_config.textBoxes || []" :key="box.id" class="absolute cursor-move select-none whitespace-nowrap" :style="catalogTextBoxPreviewStyle(box, catalogTemplateForm)" @pointerdown.stop="catalogLayoutDragStart($event, catalogTemplateForm, 'textbox', textBoxIndex)">
                                    {{ box.text }}
                                </div>
                            </div>
                            <div v-if="catalogTemplateForm.output_mode === 'katalog' && catalogTemplateEditorPreviewImage" class="absolute inset-0 overflow-hidden" :style="{ zIndex: 10 }">
                                <img :src="catalogTemplateEditorPreviewImage" class="h-full w-full object-contain pointer-events-none select-none" alt="Preview katalog produk" />
                            </div>
                            <div v-else-if="catalogTemplateForm.output_mode === 'katalog'" class="absolute grid gap-2 group/cards" :style="catalogCardPreviewStyle(catalogTemplateForm)">
                                <div v-for="row in catalogCardPreviewRows.slice(0, Number(catalogTemplateForm.layout_config.cardColumns || 4))" :key="row.nama_produk" class="overflow-hidden text-center">
                                    <div class="w-full flex items-center justify-center overflow-hidden shrink-0" :style="`height:${Math.max(36, Math.round(Number(catalogTemplateForm.layout_config.card_config?.imageHeight || 118) * (640 / (catalogTemplateForm.canvas_height || 1920)) * 0.8))}px;`">
                                        <img v-if="androidProductImage(row)" :src="androidProductImage(row)" draggable="false" class="mx-auto max-h-full max-w-full object-contain pointer-events-none select-none" />
                                        <i v-else class="fa-solid fa-mobile-screen text-slate-200 text-xl"></i>
                                    </div>
                                    <div :style="catalogCardPreviewTextStyle(catalogTemplateForm, 'model')" class="truncate mt-1 cursor-pointer font-bold" @click.stop="openColorPicker(() => catalogTemplateForm.layout_config.card_config.modelColor, v => catalogTemplateForm.layout_config.card_config.modelColor = v, $event)" title="Klik untuk ganti warna nama produk">{{ catalogProductType(row) }}</div>
                                    <div class="text-[0.62em] italic opacity-70 mt-0.5">Harga mulai</div>
                                    <div class="mt-1 grid grid-cols-[minmax(0,auto)_minmax(0,1fr)] items-center gap-2 px-[12%]">
                                        <span class="justify-self-start rounded-full border border-white/55 px-1.5 py-0.5 text-[0.58em] font-bold whitespace-nowrap">{{ [row.ram, row.storage].filter(Boolean).join('/') || '—' }}</span>
                                        <span class="min-w-0 text-right">
                                            <span v-if="Number(row.harga_nasional) > Number(row[catalogPriceKeyForSheet(row.source_sheet)])" class="block text-[0.58em] leading-none text-white/50 line-through">{{ formatCatalogPrice(row.harga_nasional) }}</span>
                                            <span :style="catalogCardPreviewTextStyle(catalogTemplateForm, 'price')" class="block cursor-pointer font-bold" @click.stop="openColorPicker(() => catalogTemplateForm.layout_config.card_config.priceColor, v => catalogTemplateForm.layout_config.card_config.priceColor = v, $event)" title="Klik untuk ganti warna harga">{{ formatCatalogPrice(row[catalogPriceKeyForSheet(row.source_sheet)]) }}</span>
                                        </span>
                                    </div>
                                    <span v-if="catalogTemplateForm.layout_config.card_config?.showHematBadge !== false && Number(row.harga_nasional) > Number(row[catalogPriceKeyForSheet(row.source_sheet)])" class="mx-auto mt-1 inline-block rounded-full bg-green-600 px-1.5 py-0.5 text-[0.55em] font-bold text-white">Hemat {{ formatCatalogPrice(Number(row.harga_nasional) - Number(row[catalogPriceKeyForSheet(row.source_sheet)])) }}</span>
                                </div>
                            </div>
                            <div v-else class="absolute border-2 border-dashed border-cyan-400 bg-slate-900/25 rounded-lg group/table"
                                 :style="catalogLayoutPreviewStyle(catalogTemplateForm)"
                                 @pointerdown.stop="catalogLayoutDragStart($event, catalogTemplateForm, 'table')">
                                 <div class="absolute -top-7 left-0 px-2 py-1 rounded bg-slate-900 text-white text-[10px] font-bold uppercase whitespace-nowrap cursor-move">Drag posisi tabel</div>
                                 <div class="absolute -bottom-2 -right-2 h-4 w-4 rounded-full border-2 border-white bg-cyan-600 shadow cursor-nwse-resize opacity-0 group-hover/table:opacity-100" @pointerdown.stop="catalogTableResizeStart" title="Ubah lebar tabel"></div>
                                <div class="grid grid-cols-[2fr_0.6fr_0.75fr_1fr_1fr] items-center rounded-t-md overflow-hidden" :style="catalogTableHeaderPreviewStyle(catalogTemplateForm)">
                                    <span class="pl-2 truncate cursor-pointer" @click.stop="openColorPicker(() => catalogTemplateForm.layout_config.headerTextColor, v => catalogTemplateForm.layout_config.headerTextColor = v, $event)" title="Klik untuk ganti warna teks header">TYPE</span>
                                    <span class="text-center truncate cursor-pointer" @click.stop="openColorPicker(() => catalogTemplateForm.layout_config.headerTextColor, v => catalogTemplateForm.layout_config.headerTextColor = v, $event)" title="Klik untuk ganti warna teks header">RAM</span>
                                    <span class="text-center truncate cursor-pointer" @click.stop="openColorPicker(() => catalogTemplateForm.layout_config.headerTextColor, v => catalogTemplateForm.layout_config.headerTextColor = v, $event)" title="Klik untuk ganti warna teks header">STORAGE</span>
                                    <span class="text-right pr-1 truncate cursor-pointer" @click.stop="openColorPicker(() => catalogTemplateForm.layout_config.headerTextColor, v => catalogTemplateForm.layout_config.headerTextColor = v, $event)" title="Klik untuk ganti warna teks header">NORMAL PRICE</span>
                                    <span class="text-right pr-2 truncate cursor-pointer" @click.stop="openColorPicker(() => catalogTemplateForm.layout_config.headerTextColor, v => catalogTemplateForm.layout_config.headerTextColor = v, $event)" title="Klik untuk ganti warna teks header">SPECIAL PRICE</span>
                                </div>
                                <div class="overflow-hidden" :style="catalogTableRowsPreviewStyle(catalogTemplateForm)">
                                    <div v-for="(row, index) in catalogPreviewRows" :key="index" class="grid grid-cols-[2fr_0.6fr_0.75fr_1fr_1fr] items-center min-h-0 cursor-pointer" :style="catalogTableRowPreviewStyle(catalogTemplateForm, index)" @click.self.stop="catalogPickColorSafe(() => catalogTemplateForm.layout_config[index % 2 === 0 ? 'rowEvenColor' : 'rowOddColor'], v => catalogTemplateForm.layout_config[index % 2 === 0 ? 'rowEvenColor' : 'rowOddColor'] = v, $event)" :title="`Klik area kosong untuk ganti warna row ${index % 2 === 0 ? 'genap' : 'ganjil'}`">
                                        <span class="pl-2 truncate cursor-pointer" @click.stop="openColorPicker(() => catalogTemplateForm.layout_config.textColor, v => catalogTemplateForm.layout_config.textColor = v, $event)" title="Klik untuk ganti warna teks produk">{{ catalogProductType(row) }}</span>
                                        <span class="text-center truncate cursor-pointer" @click.stop="openColorPicker(() => catalogTemplateForm.layout_config.textColor, v => catalogTemplateForm.layout_config.textColor = v, $event)" title="Klik untuk ganti warna teks produk">{{ row.ram || '—' }}</span>
                                        <span class="text-center truncate cursor-pointer" @click.stop="openColorPicker(() => catalogTemplateForm.layout_config.textColor, v => catalogTemplateForm.layout_config.textColor = v, $event)" title="Klik untuk ganti warna teks produk">{{ row.storage || '—' }}</span>
                                        <span class="text-right pr-1 truncate cursor-pointer" :style="catalogPreviewPriceStyle(catalogTemplateForm, 'normal')" @click.stop="openColorPicker(() => catalogTemplateForm.layout_config.normalPriceColor, v => catalogTemplateForm.layout_config.normalPriceColor = v, $event)" title="Klik untuk ganti warna harga normal">
                                            <span class="relative inline-block">
                                                {{ formatCatalogPrice(row.harga_nasional) }}
                                                <span class="absolute left-0 right-0 top-1/2 border-t pointer-events-none"
                                                    :style="catalogPreviewStrikeStyle(catalogTemplateForm, row.harga_nasional)"></span>
                                            </span>
                                        </span>
                                         <span class="text-right pr-2 truncate font-bold cursor-pointer" :style="catalogPreviewPriceStyle(catalogTemplateForm, 'special')" @click.stop="openColorPicker(() => catalogTemplateForm.layout_config.specialPriceColor, v => catalogTemplateForm.layout_config.specialPriceColor = v, $event)" title="Klik untuk ganti warna harga spesial">{{ formatCatalogPrice(row[catalogPriceKeyForSheet(row.source_sheet)]) }}</span>
                                </div>
                            </div>
                        </div>
                          <p class="text-body-sm text-slate-400 mt-2">Geser kotak tabel atau judul pada preview. Garis putus-putus menunjukkan center; posisi X akan snap ke center jika dekat.</p>
                      </div>
                </div>
                </div>

                <div class="space-y-4 h-full min-h-0 overflow-y-auto overscroll-contain pr-1 pb-6">
                    <div v-if="catalogTemplateForm.output_mode === 'katalog'" class="rounded-xl border border-slate-100 bg-slate-50/60 p-3 space-y-3">
                        <label class="type-overline text-slate-400 uppercase block">Tampilan Katalog Produk</label>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Kolom produk</label>
                                <input v-model.number="catalogTemplateForm.layout_config.cardColumns" min="1" max="5" class="form-input-compact w-full" />
                            </div>
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Tinggi gambar (px)</label>
                                <input v-model.number="catalogTemplateForm.layout_config.card_config.imageHeight" type="number" min="40" max="360" step="1" class="form-input-compact w-full" />
                            </div>
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Font model</label>
                                <input v-model.number="catalogTemplateForm.layout_config.card_config.modelFontSize" class="form-input-compact w-full" />
                            </div>
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Font harga</label>
                                <input v-model.number="catalogTemplateForm.layout_config.card_config.priceFontSize" class="form-input-compact w-full" />
                            </div>
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Warna model</label>
                                <input v-model="catalogTemplateForm.layout_config.card_config.modelColor" class="form-input-compact w-full" />
                            </div>
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Warna harga</label>
                                <input v-model="catalogTemplateForm.layout_config.card_config.priceColor" class="form-input-compact w-full" />
                            </div>
                        </div>
                    </div>
                    <div v-else class="rounded-xl border border-slate-100 bg-slate-50/60 p-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="type-overline text-slate-400 uppercase block">Warna Tabel</label>
                            <div class="flex items-center -space-x-1">
                                <span v-for="cf in catalogColorFields.slice(0, 5)" :key="cf.key"
                                    class="inline-block h-3.5 w-3.5 rounded-full border border-white shadow-xs"
                                    :style="'background:' + (catalogTemplateForm.layout_config[cf.key] || '#ffffff')">
                                </span>
                            </div>
                        </div>
                        <div class="relative search-select-container catalog-table-colors-select w-full">
                            <button type="button"
                                @click="toggleSearchSelect($event, 'catalog-table-colors')"
                                data-popover-match-trigger="true"
                                :aria-expanded="searchSelectOpen === 'catalog-table-colors' ? 'true' : 'false'"
                                class="select-trigger-button select-trigger-button-compact select-trigger-button-form toolbar-trigger-field-form w-full justify-between">
                                <span class="flex items-center gap-2 min-w-0">
                                    <span class="color-swatch-sm flex-shrink-0">
                                        <span class="checkerboard-bg absolute inset-0"></span>
                                        <span class="absolute inset-0" :style="'background:' + (catalogTemplateForm.layout_config.headerColor || '#ffffff')"></span>
                                    </span>
                                    <span class="text-body-sm font-semibold text-slate-700 truncate">Pilih & Atur Warna Tabel</span>
                                </span>
                                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 flex-shrink-0"></i>
                            </button>
                            <div v-if="searchSelectOpen === 'catalog-table-colors'" :style="popoverStyle" class="search-select-popover p-2 max-h-72 overflow-y-auto custom-scrollbar space-y-1 z-50">
                                <div v-for="colorField in catalogColorFields" :key="colorField.key"
                                    class="flex items-center justify-between gap-2 p-1.5 rounded-lg hover:bg-slate-50 transition-colors">
                                    <span class="text-[11px] font-semibold text-slate-700 truncate min-w-0 flex-1">{{ colorField.label }}</span>
                                    <input
                                        type="color"
                                        :value="catalogColorHex(catalogTemplateForm.layout_config[colorField.key])"
                                        class="sr-only"
                                        @input="catalogApplyColor(colorField.key, $event)" />
                                    <button type="button"
                                        class="select-trigger-button select-trigger-button-compact toolbar-trigger-field-form gap-1.5 py-1 px-2 cursor-pointer flex-shrink-0"
                                        @click="openColorPicker(() => catalogTemplateForm.layout_config[colorField.key], v => catalogTemplateForm.layout_config[colorField.key] = v, $event)">
                                        <span class="color-swatch-sm flex-shrink-0">
                                            <span class="checkerboard-bg absolute inset-0"></span>
                                            <span class="absolute inset-0" :style="'background:' + (catalogTemplateForm.layout_config[colorField.key] || '#ffffff')"></span>
                                        </span>
                                        <span class="font-mono text-[10px] text-slate-500 truncate">{{ catalogTemplateForm.layout_config[colorField.key] || '—' }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="type-overline text-slate-400 uppercase block">Asset / Sticker / Credit</label>
                            <span v-if="(catalogTemplateForm.layout_config.assets || []).length" class="type-micro text-slate-400">Shift+klik untuk multi-select</span>
                        </div>
                        <label class="primary-cta-button primary-cta-button--secondary w-full cursor-pointer"><i class="fa-solid fa-upload"></i><span>Tambah asset gambar</span><input type="file" accept="image/*" multiple class="hidden" @change="catalogAddAsset" /></label>
                        <div v-if="catalogSelectedAssetIds.length >= 2" class="rounded-lg border border-indigo-200 bg-indigo-50/80 p-2.5 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold text-indigo-900">{{ catalogSelectedAssetIds.length }} aset terpilih</span>
                                <button type="button" @click="catalogSelectedAssetIds = []" class="text-[10px] text-indigo-600 hover:underline font-semibold">Batal pilih</button>
                            </div>
                            <div class="grid grid-cols-3 gap-1">
                                <button type="button" @click="catalogAlignSelectedAssets('centerX')" class="secondary-cta-button text-[10px] py-1 px-1.5 justify-center font-bold" title="Ratakan tengah horizontal (sumbu X) kedua aset">
                                    <i class="fa-solid fa-arrows-left-right text-[9px] mr-1"></i> Center X
                                </button>
                                <button type="button" @click="catalogAlignSelectedAssets('centerY')" class="secondary-cta-button text-[10px] py-1 px-1.5 justify-center font-bold" title="Ratakan tengah vertikal (sumbu Y) kedua aset">
                                    <i class="fa-solid fa-arrows-up-down text-[9px] mr-1"></i> Center Y
                                </button>
                                <button type="button" @click="catalogAlignSelectedAssets('canvasCenter')" class="secondary-cta-button text-[10px] py-1 px-1.5 justify-center font-bold" title="Pusatkan grup aset ke tengah kanvas">
                                    <i class="fa-solid fa-bullseye text-[9px] mr-1"></i> Kanvas
                                </button>
                            </div>
                        </div>
                        <div v-if="!(catalogTemplateForm.layout_config.assets || []).length" class="text-body-sm text-slate-400">Pilih satu atau beberapa logo, sticker, atau credit badge sekaligus. Geser langsung asset pada preview untuk mengatur posisi.</div>
                        <div v-else class="space-y-1.5">
                            <div v-for="(asset, assetIndex) in catalogTemplateForm.layout_config.assets || []"
                                :key="asset.id"
                                draggable="true"
                                @dragstart="catalogAssetDragStart(assetIndex, $event)"
                                @dragover.prevent="catalogAssetDragOver(assetIndex, $event)"
                                @drop.prevent="catalogAssetDrop(assetIndex, $event)"
                                @dragend="catalogAssetDragEnd"
                                :class="[
                                    'flex items-center justify-between gap-2 rounded-lg border bg-white px-2.5 py-1.5 transition-all cursor-grab active:cursor-grabbing select-none',
                                    catalogSelectedAssetIds.includes(asset.id)
                                        ? 'border-indigo-500 ring-1 ring-indigo-400/50 bg-indigo-50/40'
                                        : (assetDragOverIndex === assetIndex ? 'border-ppp-accent ring-1 ring-ppp-accent/30 bg-ppp-accent/5' : 'border-slate-200/80 hover:border-slate-300')
                                ]">
                                <div class="flex items-center gap-2 min-w-0 flex-1">
                                    <button type="button" @click.stop="catalogToggleAssetSelection(asset, $event)" :class="['w-4 h-4 rounded flex items-center justify-center text-[9px] transition-colors flex-shrink-0', catalogSelectedAssetIds.includes(asset.id) ? 'bg-indigo-600 text-white' : 'border border-slate-300 text-transparent hover:text-slate-400 hover:border-slate-400']" title="Pilih aset">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                    <i class="fa-solid fa-grip-vertical text-slate-300 text-[10px] flex-shrink-0 cursor-grab"></i>
                                    <div class="w-6 h-6 rounded-md bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center flex-shrink-0 p-0.5">
                                        <img :src="asset.src" class="w-full h-full object-contain pointer-events-none" />
                                    </div>
                                    <div class="min-w-0 flex-1 flex items-center gap-1.5">
                                        <span class="text-[10px] font-mono text-slate-400 bg-slate-100 px-1 py-0.5 rounded leading-none flex-shrink-0">L{{ (catalogTemplateForm.layout_config.assets || []).length - assetIndex }}</span>
                                        <span class="text-body-sm text-slate-700 font-semibold truncate leading-tight">{{ asset.name }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    <span class="text-[10px] font-mono text-slate-400" title="Ukuran dan rotasi">{{ asset.width || 160 }}×{{ asset.height || 160 }}px · {{ asset.rotation || 0 }}°</span>
                                    <button type="button" @click.stop="catalogToggleFlip(asset, 'x')" :class="['w-6 h-6 flex items-center justify-center rounded transition-colors', asset.flipX ? 'bg-indigo-100 text-indigo-600' : 'text-slate-400 hover:text-indigo-600 hover:bg-indigo-50']" title="Mirror horizontal">
                                        <i class="fa-solid fa-left-right text-[10px]"></i>
                                    </button>
                                     <button type="button" @click.stop="catalogToggleFlip(asset, 'y')" :class="['w-6 h-6 flex items-center justify-center rounded transition-colors', asset.flipY ? 'bg-indigo-100 text-indigo-600' : 'text-slate-400 hover:text-indigo-600 hover:bg-indigo-50']" title="Mirror vertical">
                                         <i class="fa-solid fa-up-down text-[10px]"></i>
                                     </button>
                                     <button type="button" @click.stop="catalogCloneAsset(asset)" class="w-6 h-6 flex items-center justify-center rounded text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Clone asset">
                                         <i class="fa-solid fa-copy text-[11px]"></i>
                                     </button>
                                     <button type="button" @click.stop="catalogRemoveAsset(asset.id)" class="w-6 h-6 flex items-center justify-center rounded text-slate-400 hover:text-red-500 hover:bg-red-50 transition-colors" title="Hapus asset">
                                        <i class="fa-solid fa-trash-can text-[11px]"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="type-overline text-slate-400 uppercase block">Ukuran & Format Tabel</label>
                            <span class="type-micro text-slate-400">Posisi diatur via kanvas</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Lebar tabel (px)</label>
                                <input v-model.number="catalogTemplateForm.layout_config.width" class="form-input-compact w-full" placeholder="cth: 952" />
                            </div>
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Maks baris / halaman</label>
                                <input v-model.number="catalogTemplateForm.layout_config.maxItems" class="form-input-compact w-full" placeholder="0 = otomatis" />
                            </div>
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Tinggi header (px)</label>
                                <input v-model.number="catalogTemplateForm.layout_config.headerHeight" class="form-input-compact w-full" placeholder="cth: 34" />
                            </div>
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Tinggi baris (px)</label>
                                <input v-model.number="catalogTemplateForm.layout_config.rowHeight" class="form-input-compact w-full" placeholder="cth: 28" />
                            </div>
                            <div class="col-span-2">
                                <label class="type-micro text-slate-400 block mb-1">Radius sudut (px)</label>
                                <input v-model.number="catalogTemplateForm.layout_config.borderRadius" class="form-input-compact w-full" placeholder="cth: 10" />
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="type-overline text-slate-400 uppercase block">Gaya Judul Brand</label>
                            <span class="type-micro text-slate-400">Posisi diatur via kanvas</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Judul List (Tabel)</label>
                                <input v-model="catalogTemplateForm.layout_config.listTitle" class="form-input-compact w-full" placeholder="DAFTAR HARGA" />
                            </div>
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Judul Katalog Produk</label>
                                <input v-model="catalogTemplateForm.layout_config.katalogTitle" class="form-input-compact w-full" placeholder="KATALOG PRODUK" />
                            </div>
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Posisi judul X (px)</label>
                                <input v-model.number="catalogTemplateForm.layout_config.titleX" class="form-input-compact w-full" placeholder="cth: 540" />
                            </div>
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Posisi judul Y (px)</label>
                                <input v-model.number="catalogTemplateForm.layout_config.titleY" class="form-input-compact w-full" placeholder="cth: 213" />
                            </div>
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Ukuran font (px)</label>
                                <input v-model.number="catalogTemplateForm.layout_config.titleFontSize" class="form-input-compact w-full" placeholder="cth: 34" />
                             </div>
                             <div>
                                 <label class="type-micro text-slate-400 block mb-1">Jarak judul (px)</label>
                                 <input v-model.number="catalogTemplateForm.layout_config.titleGap" class="form-input-compact w-full" placeholder="cth: 12" />
                            </div>
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Stroke outline (px)</label>
                                <input v-model.number="catalogTemplateForm.layout_config.titleStrokeWidth" min="0" max="12" class="form-input-compact w-full" placeholder="0" />
                            </div>
                            <div class="col-span-2">
                                <label class="type-micro text-slate-400 block mb-1">Font family</label>
                                <div class="catalog-segment-group w-full">
                                    <button type="button" v-for="f in ['Inter, sans-serif', 'Poppins, sans-serif', 'Arial, sans-serif']" :key="f" @click="catalogTemplateForm.layout_config.fontFamily = f" :class="['catalog-segment-button flex-1 text-[10px]', (catalogTemplateForm.layout_config.fontFamily || 'Inter, sans-serif') === f ? 'catalog-segment-button--active' : '']">{{ f.split(',')[0] }}</button>
                                </div>
                            </div>
                            <div class="col-span-2">
                                <label class="type-micro text-slate-400 block mb-1">Warna judul</label>
                                <button type="button"
                                    class="select-trigger-button select-trigger-button-compact select-trigger-button-form toolbar-trigger-field-form w-full justify-start gap-2 cursor-pointer"
                                    @click="openColorPicker(() => catalogTemplateForm.layout_config.titleColor, v => catalogTemplateForm.layout_config.titleColor = v, $event)">
                                    <span class="color-swatch-sm">
                                        <span class="checkerboard-bg absolute inset-0"></span>
                                        <span class="absolute inset-0" :style="'background:' + (catalogTemplateForm.layout_config.titleColor || '#ffffff')"></span>
                                    </span>
                                    <span class="color-value-text flex-1 text-left font-mono text-slate-500 truncate">{{ catalogTemplateForm.layout_config.titleColor || '#ffffff' }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3 space-y-3">
                        <label class="type-overline text-slate-400 uppercase block">Font Tabel</label>
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Font header</label>
                                <input v-model.number="catalogTemplateForm.layout_config.headerFontSize" class="form-input-compact w-full" placeholder="cth: 13" />
                            </div>
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Font isi</label>
                                <input v-model.number="catalogTemplateForm.layout_config.bodyFontSize" class="form-input-compact w-full" placeholder="cth: 13" />
                            </div>
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Font harga</label>
                                <input v-model.number="catalogTemplateForm.layout_config.priceFontSize" class="form-input-compact w-full" placeholder="cth: 13" />
                            </div>
                        </div>
                </div>
            </div>
            </div>
            <div class="shrink-0 px-5 py-4 border-t border-slate-100 bg-white">
                <button @click="saveCatalogTemplate" class="primary-cta-button primary-cta-button--accent w-full">Simpan Template</button>
            </div>
        </div>
     </div>
     <div v-if="catalogCloseConfirmOpen" class="fixed inset-0 z-[9100] flex items-center justify-center bg-slate-900/40 p-4">
         <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-xl">
             <h3 class="type-label font-bold text-slate-800">Simpan perubahan?</h3>
             <p class="text-body-sm text-slate-500 mt-2">Perubahan template belum disimpan. Simpan sebelum menutup?</p>
             <div class="flex gap-2 mt-5">
                 <button @click="discardAndCloseCatalogTemplate" class="primary-cta-button primary-cta-button--neutral flex-1">Tutup tanpa simpan</button>
                 <button @click="saveAndCloseCatalogTemplate" class="primary-cta-button primary-cta-button--accent flex-1">Simpan</button>
             </div>
         </div>
      </div>
   </div>
@endverbatim

