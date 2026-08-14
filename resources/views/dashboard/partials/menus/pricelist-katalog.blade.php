@verbatim
<div v-if="activeTab === 'pricelist_katalog'" class="space-y-6 animate-fadeIn pb-10">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="type-title text-slate-900">Katalog Pricelist</h1>
            <p class="text-body-sm text-slate-400 mt-1">Sync pricelist Android dari spreadsheet, upload background, dan generate katalog Story / IG Feed.</p>
        </div>
        <button @click="syncPricelistProducts" :disabled="pricelistSyncing" class="primary-cta-button primary-cta-button--accent">
            <i class="fa-solid fa-rotate text-body-sm"></i>
            {{ pricelistSyncing ? 'Sync...' : 'Sync Spreadsheet' }}
        </button>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.2fr)_minmax(340px,0.8fr)] gap-4">
        <div class="section-card">
            <div class="px-6 py-4 border-b border-slate-100 space-y-3">
                <div>
                    <h3 class="type-label font-semibold text-slate-700">Data Pricelist</h3>
                    <p class="text-body-sm text-slate-400 mt-0.5">Hanya row dengan kolom URUT terisi yang masuk katalog.</p>
                </div>
                <div class="grid grid-cols-[minmax(220px,1fr)_10rem] items-center gap-2 w-full">
                    <input v-model="pricelistSearch" class="form-input-compact min-w-[220px]" placeholder="Cari produk..." />
                    <div class="relative group search-select-container justify-self-end w-40">
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
            <div class="overflow-x-auto">
                <table class="ppp-table w-full">
                    <thead>
                        <tr class="table-header-row">
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
                            <td colspan="6" class="text-center text-slate-400 py-10">Belum ada data pricelist</td>
                        </tr>
                        <tr v-for="row in pagedPricelistProducts" :key="row.ID">
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
            <div class="table-pager-bar">
                <div class="text-body-sm text-slate-400 font-medium">{{ filteredPricelistProducts.length }} data</div>
                <div class="flex items-center gap-1">
                    <button @click="pricelistPage--" :disabled="pricelistPage <= 1" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-left text-body-sm"></i></button>
                    <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ pricelistPage }} / {{ pricelistTotalPages }}</span>
                    <button @click="pricelistPage++" :disabled="pricelistPage >= pricelistTotalPages" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-right text-body-sm"></i></button>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-3 min-h-0 overflow-y-auto pr-0.5">
            <div class="section-card p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="type-label font-semibold text-slate-700">Template Background</h3>
                        <p class="text-body-sm text-slate-400 mt-0.5">Story 1080x1920 atau Feed 1080x1350</p>
                    </div>
                    <label class="icon-utility-button icon-utility-bordered cursor-pointer" title="Upload template baru">
                        <i class="fa-solid fa-plus"></i>
                        <input type="file" accept="image/*" class="hidden" @change="uploadNewCatalogTemplateBackground" />
                    </label>
                </div>
                <div class="space-y-2">
                    <div v-for="template in catalogTemplates" :key="template.ID" class="border border-slate-100 rounded-xl p-3 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-body-sm font-bold text-slate-800 truncate">{{ template.name }}</div>
                            <div class="text-overline text-slate-400 uppercase">{{ template.format }} · {{ template.canvas_width }}x{{ template.canvas_height }}</div>
                        </div>
                        <div class="flex items-center gap-1">
                            <label class="icon-utility-button icon-utility-bordered cursor-pointer">
                                <i class="fa-solid fa-image"></i>
                                <input type="file" accept="image/*" class="hidden" @change="uploadCatalogTemplateBackground($event, template)" />
                            </label>
                            <button @click="openCatalogTemplateModal('edit', template)" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-pen"></i></button>
                            <button @click="deleteCatalogTemplate(template)" class="icon-utility-button icon-utility-danger"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>

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
            </div>
            <div class="flex gap-2">
                <button @click="generateCatalogPreview" :disabled="catalogGenerating" class="primary-cta-button primary-cta-button--accent flex-1">
                    <i :class="['fa-solid', catalogGenerating ? 'fa-spinner fa-spin' : 'fa-eye']"></i>
                    <span>{{ catalogGenerating ? 'Generate...' : 'Preview' }}</span>
                </button>
                <button @click="exportCatalogToPdf" :disabled="catalogGenerating" class="primary-cta-button primary-cta-button--info flex-1">
                    <i :class="['fa-solid', catalogGenerating ? 'fa-spinner fa-spin' : 'fa-file-pdf']"></i>
                    <span>{{ catalogGenerating ? 'Exporting...' : 'Export PDF' }}</span>
                </button>
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
            <div class="shrink-0 px-4 py-3 flex items-center justify-between gap-3 bg-white/95 border-b border-slate-200" @click.stop>
                <div>
                    <h3 class="type-label font-bold text-slate-800">Preview Katalog Pricelist</h3>
                    <p class="text-body-sm text-slate-500">Halaman {{ catalogPreviewModalIndex + 1 }} / {{ catalogPreviewImages.length }} · Zoom {{ Math.round(catalogPreviewZoom * 100) }}%</p>
                </div>
                <div class="flex items-center gap-1.5">
                    <button @click="catalogPreviewNav(-1)" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-left"></i></button>
                    <button @click="catalogPreviewZoomBy(0.85)" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-magnifying-glass-minus"></i></button>
                    <button @click="catalogPreviewZoom = 1" class="toolbar-segment-button border-slate-200 text-slate-500 bg-white">100%</button>
                    <button @click="catalogPreviewZoomBy(1.18)" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-magnifying-glass-plus"></i></button>
                    <button @click="catalogPreviewNav(1)" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-right"></i></button>
                    <button @click="downloadCatalogPreview(catalogPreviewImages[catalogPreviewModalIndex], catalogPreviewModalIndex)" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-download"></i></button>
                    <button @click="closeCatalogPreviewModal" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>
            <div class="flex-1 overflow-auto p-6" @click.stop>
                <div class="min-w-full min-h-full flex items-start justify-center">
                    <img :src="catalogPreviewImages[catalogPreviewModalIndex]" class="preview-zoom-image rounded-xl shadow-2xl bg-white transition-transform origin-top" :style="`transform:scale(${catalogPreviewZoom})`" />
                </div>
            </div>
        </div>
    </teleport>

    <div v-if="catalogTemplateModalOpen" class="fixed inset-0 z-[9000] flex items-center justify-center p-4 bg-slate-900/40">
        <div class="flex flex-col bg-white rounded-2xl w-full max-w-6xl max-h-[88vh] overflow-hidden shadow-xl">
            <div class="flex items-center justify-between px-5 py-5 border-b border-slate-100 shrink-0">
                <h3 class="type-label font-bold text-slate-800">{{ catalogTemplateModalType === 'edit' ? 'Edit Template' : 'Tambah Template' }}</h3>
                <button @click="catalogTemplateModalOpen = false" class="icon-utility-button"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="flex-1 min-h-0 overflow-hidden p-5 flex flex-col gap-4">
            <input v-model="catalogTemplateForm.name" class="form-input-compact w-full" placeholder="Nama template" />
            <div class="search-select-container">
                <button @click="toggleSearchSelect($event, 'catalog-template-format')" type="button" :aria-expanded="searchSelectOpen === 'catalog-template-format'" class="select-trigger-button select-trigger-button-compact select-trigger-button-form w-full">
                    <span class="toolbar-trigger-field-form truncate">{{ catalogTemplateFormatLabel }}</span>
                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                </button>
                <div v-if="searchSelectOpen === 'catalog-template-format'" class="search-select-popover" :style="popoverStyle">
                    <button v-for="format in catalogTemplateFormatOptions" :key="format.key" @click="catalogTemplateForm.format = format.key; searchSelectOpen = null" type="button" :class="['popover-option', catalogTemplateForm.format === format.key ? 'popover-option-active' : '']">
                        {{ format.label }}
                    </button>
                </div>
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
                            <div class="absolute px-2 py-1 border-2 border-dashed border-cyan-400 rounded-md text-white font-extrabold uppercase text-center whitespace-nowrap cursor-move select-none"
                                :style="catalogTitlePreviewStyle(catalogTemplateForm)"
                                @pointerdown="catalogLayoutDragStart($event, catalogTemplateForm, 'title')">
                                Drag judul brand
                            </div>
                            <div class="absolute border-2 border-dashed border-cyan-400 bg-slate-900/25 rounded-lg pointer-events-none"
                                :style="catalogLayoutPreviewStyle(catalogTemplateForm)">
                                <div class="absolute -top-7 left-0 px-2 py-1 rounded bg-slate-900 text-white text-[10px] font-bold uppercase whitespace-nowrap">Drag posisi tabel</div>
                                <div class="grid grid-cols-[2fr_0.6fr_0.75fr_1fr_1fr] items-center rounded-t-md overflow-hidden" :style="catalogTableHeaderPreviewStyle(catalogTemplateForm)">
                                    <span class="pl-2 truncate">TYPE</span>
                                    <span class="text-center truncate">RAM</span>
                                    <span class="text-center truncate">STORAGE</span>
                                    <span class="text-right pr-1 truncate">NORMAL PRICE</span>
                                    <span class="text-right pr-2 truncate">SPECIAL PRICE</span>
                                </div>
                                <div class="overflow-hidden" :style="catalogTableRowsPreviewStyle(catalogTemplateForm)">
                                    <div v-for="(row, index) in catalogPreviewRows" :key="index" class="grid grid-cols-[2fr_0.6fr_0.75fr_1fr_1fr] items-center min-h-0" :style="catalogTableRowPreviewStyle(catalogTemplateForm, index)">
                                        <span class="pl-2 truncate">{{ catalogProductType(row) }}</span>
                                        <span class="text-center truncate">{{ row.ram || '—' }}</span>
                                        <span class="text-center truncate">{{ row.storage || '—' }}</span>
                                        <span class="text-right pr-1 truncate">
                                            <span class="relative inline-block">
                                                {{ formatCatalogPrice(row.harga_nasional) }}
                                                <span class="absolute left-0 right-0 top-1/2 border-t border-red-600 pointer-events-none"
                                                    :style="catalogPreviewStrikeStyle(catalogTemplateForm, row.harga_nasional)"></span>
                                            </span>
                                        </span>
                                        <span class="text-right pr-2 truncate font-bold">{{ formatCatalogPrice(row[catalogPriceKeyForSheet(row.source_sheet)]) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-body-sm text-slate-400 mt-2">Geser kotak tabel atau judul pada preview. Garis putus-putus menunjukkan center; posisi X akan snap ke center jika dekat.</p>
                    </div>
                </div>

                <div class="space-y-4 h-full min-h-0 overflow-y-auto overscroll-contain pr-1 pb-6">
                    <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3 space-y-3">
                        <label class="type-overline text-slate-400 uppercase block">Warna Tabel</label>
                        <div class="grid grid-cols-1 gap-1.5">
                            <div v-for="colorField in catalogColorFields" :key="colorField.key" class="flex items-center gap-2">
                                <span class="type-micro text-slate-400 w-24 flex-shrink-0">{{ colorField.label }}</span>
                                <input
                                    type="color"
                                    :value="catalogColorHex(catalogTemplateForm.layout_config[colorField.key])"
                                    class="sr-only"
                                    @input="catalogApplyColor(colorField.key, $event)" />
                                <button type="button"
                                    class="select-trigger-button select-trigger-button-compact select-trigger-button-form toolbar-trigger-field-form w-full justify-start gap-2 cursor-pointer"
                                    @click="openColorPicker(() => catalogTemplateForm.layout_config[colorField.key], v => catalogTemplateForm.layout_config[colorField.key] = v, $event)">
                                    <span class="color-swatch-sm relative flex-shrink-0 rounded-full overflow-hidden border border-black/10">
                                        <span class="checkerboard-bg absolute inset-0"></span>
                                        <span class="absolute inset-0" :style="'background:' + (catalogTemplateForm.layout_config[colorField.key] || '#ffffff')"></span>
                                    </span>
                                    <span class="color-value-text flex-1 text-left font-mono text-slate-500 truncate">{{ catalogTemplateForm.layout_config[colorField.key] || '—' }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3 space-y-3">
                        <label class="type-overline text-slate-400 uppercase block">Posisi & Ukuran Tabel</label>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Posisi X (px)</label>
                                <input v-model.number="catalogTemplateForm.layout_config.x" class="form-input-compact w-full" placeholder="cth: 64" />
                            </div>
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Posisi Y (px, dari atas)</label>
                                <input v-model.number="catalogTemplateForm.layout_config.y" class="form-input-compact w-full" placeholder="cth: 255" />
                            </div>
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Lebar tabel (px)</label>
                                <input v-model.number="catalogTemplateForm.layout_config.width" class="form-input-compact w-full" placeholder="cth: 952" />
                            </div>
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Max item per halaman</label>
                                <input v-model.number="catalogTemplateForm.layout_config.maxItems" class="form-input-compact w-full" placeholder="0 = auto" />
                            </div>
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Tinggi header (px)</label>
                                <input v-model.number="catalogTemplateForm.layout_config.headerHeight" class="form-input-compact w-full" placeholder="cth: 34" />
                            </div>
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Tinggi baris (px)</label>
                                <input v-model.number="catalogTemplateForm.layout_config.rowHeight" class="form-input-compact w-full" placeholder="cth: 28" />
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3 space-y-3">
                        <label class="type-overline text-slate-400 uppercase block">Posisi & Font Judul</label>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Posisi judul X (px)</label>
                                <input v-model.number="catalogTemplateForm.layout_config.titleX" class="form-input-compact w-full" placeholder="cth: 540" />
                            </div>
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Posisi judul Y (px)</label>
                                <input v-model.number="catalogTemplateForm.layout_config.titleY" class="form-input-compact w-full" placeholder="cth: 213" />
                            </div>
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Ukuran font</label>
                                <input v-model.number="catalogTemplateForm.layout_config.titleFontSize" class="form-input-compact w-full" placeholder="cth: 34" />
                            </div>
                            <div>
                                <label class="type-micro text-slate-400 block mb-1">Jarak fallback</label>
                                <input v-model.number="catalogTemplateForm.layout_config.titleGap" class="form-input-compact w-full" placeholder="cth: 42" />
                            </div>
                            <div class="col-span-2">
                                <label class="type-micro text-slate-400 block mb-1">Warna judul</label>
                                <button type="button"
                                    class="select-trigger-button select-trigger-button-compact select-trigger-button-form toolbar-trigger-field-form w-full justify-start gap-2 cursor-pointer"
                                    @click="openColorPicker(() => catalogTemplateForm.layout_config.titleColor, v => catalogTemplateForm.layout_config.titleColor = v, $event)">
                                    <span class="color-swatch-sm relative flex-shrink-0 rounded-full overflow-hidden border border-black/10">
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
            </div>
            <div class="shrink-0 px-5 py-4 border-t border-slate-100 bg-white">
                <button @click="saveCatalogTemplate" class="primary-cta-button primary-cta-button--accent w-full">Simpan Template</button>
            </div>
        </div>
    </div>
</div>
@endverbatim
