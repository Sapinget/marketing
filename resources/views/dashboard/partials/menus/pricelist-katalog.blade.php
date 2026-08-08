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
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h3 class="type-label font-semibold text-slate-700">Data Pricelist</h3>
                    <p class="text-body-sm text-slate-400 mt-0.5">Hanya row dengan kolom URUT terisi yang masuk katalog.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="pricelistSheetFilter = 'all'" :class="['px-3 py-2 rounded-xl text-body-sm font-bold border', pricelistSheetFilter === 'all' ? 'bg-ppp-accent text-white border-ppp-accent' : 'bg-white text-slate-500 border-slate-200']">Semua</button>
                    <input v-model="pricelistSearch" class="form-input-compact min-w-[220px]" placeholder="Cari produk..." />
                </div>
            </div>
            <div class="px-6 py-3 border-b border-slate-50 flex flex-wrap gap-2">
                <button v-for="sheet in pricelistBrandSheets" :key="sheet" @click="pricelistSheetFilter = sheet" :class="['px-3 py-1.5 rounded-xl text-body-sm font-bold border', pricelistSheetFilter === sheet ? 'bg-ppp-accent text-white border-ppp-accent' : 'bg-white text-slate-500 border-slate-200']">{{ sheet }}</button>
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

            <div class="section-card p-4 space-y-2">
                <p class="text-overline text-slate-400 uppercase">Format Output</p>
                <div class="flex gap-1.5">
                    <button @click="catalogOutputMode = 'list'" :class="['px-3 py-1.5 rounded-xl text-body-sm font-bold border flex-1', catalogOutputMode === 'list' ? 'bg-ppp-accent text-white border-ppp-accent' : 'bg-white text-slate-500 border-slate-200']">List (Tabel)</button>
                    <button @click="catalogOutputMode = 'katalog'" :class="['px-3 py-1.5 rounded-xl text-body-sm font-bold border flex-1', catalogOutputMode === 'katalog' ? 'bg-ppp-accent text-white border-ppp-accent' : 'bg-white text-slate-500 border-slate-200']">Katalog (Card)</button>
                </div>
            </div>
            <div class="section-card p-4 space-y-2">
                <p class="text-overline text-slate-400 uppercase">Template</p>
                <div class="flex flex-wrap gap-1.5">
                    <button @click="catalogSelectedTemplateId = 'a4_auto'"
                        :class="['px-3 py-1.5 rounded-xl text-body-sm font-bold border', catalogSelectedTemplateId === 'a4_auto' ? 'bg-ppp-accent text-white border-ppp-accent' : 'bg-white text-slate-500 border-slate-200']">
                        A4 Auto
                    </button>
                    <button v-for="template in catalogTemplates" :key="template.ID" @click="catalogSelectedTemplateId = template.ID"
                        :class="['px-3 py-1.5 rounded-xl text-body-sm font-bold border', catalogSelectedTemplateId === template.ID ? 'bg-ppp-accent text-white border-ppp-accent' : 'bg-white text-slate-500 border-slate-200']">
                        {{ template.name }}
                    </button>
                    <span v-if="!catalogTemplates.length" class="text-body-sm text-slate-400">Belum ada template</span>
                </div>
            </div>
            <div v-if="catalogOutputMode === 'katalog'" class="section-card p-4 space-y-2">
                <p class="text-overline text-slate-400 uppercase">Kolom per baris</p>
                <div class="flex flex-wrap gap-1.5">
                    <button v-for="col in [2,3,4,5]" :key="col" @click="catalogColumnsPerRow = col"
                        :class="['px-3 py-1.5 rounded-xl text-body-sm font-bold border', catalogColumnsPerRow === col ? 'bg-ppp-accent text-white border-ppp-accent' : 'bg-white text-slate-500 border-slate-200']">
                        {{ col }}
                    </button>
                </div>
            </div>
            <div class="section-card p-4 space-y-3">
                <div>
                    <p class="text-overline text-slate-400 uppercase">Layout</p>
                    <p class="text-body-sm text-slate-400 mt-0.5">Cukup atur bagian utama. Detail lanjutan bisa dibuka kalau hasil preview belum pas.</p>
                </div>
                <div v-if="catalogSelectedTemplate" class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="type-micro text-slate-400 block mb-1">Area atas</label>
                        <input v-model.number="catalogSelectedTemplate.layout_config.y" class="form-input-compact w-full" placeholder="255" />
                    </div>
                    <div>
                        <label class="type-micro text-slate-400 block mb-1">Max item</label>
                        <input v-model.number="catalogSelectedTemplate.layout_config.maxItems" class="form-input-compact w-full" placeholder="auto" />
                    </div>
                    <div>
                        <label class="type-micro text-slate-400 block mb-1">Padding samping</label>
                        <input v-model.number="catalogSelectedTemplate.layout_config.x" class="form-input-compact w-full" placeholder="64" />
                    </div>
                    <div>
                        <label class="type-micro text-slate-400 block mb-1">Lebar area</label>
                        <input v-model.number="catalogSelectedTemplate.layout_config.width" class="form-input-compact w-full" placeholder="952" />
                    </div>
                </div>
                <details v-if="catalogSelectedTemplate" class="rounded-xl border border-slate-100 bg-slate-50/60">
                    <summary class="cursor-pointer px-3 py-2 text-body-sm font-bold text-slate-600 flex items-center justify-between">Pengaturan teks & judul <i class="fa-solid fa-chevron-down text-slate-300"></i></summary>
                    <div class="grid grid-cols-2 gap-2 p-3 pt-1">
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Font judul</label>
                            <input v-model.number="catalogSelectedTemplate.layout_config.titleFontSize" class="form-input-compact w-full" placeholder="34" />
                        </div>
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Y judul</label>
                            <input v-model.number="catalogSelectedTemplate.layout_config.titleY" class="form-input-compact w-full" placeholder="213" />
                        </div>
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">X judul</label>
                            <input v-model.number="catalogSelectedTemplate.layout_config.titleX" class="form-input-compact w-full" placeholder="540" />
                        </div>
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Jarak judul</label>
                            <input v-model.number="catalogSelectedTemplate.layout_config.titleGap" class="form-input-compact w-full" placeholder="42" />
                        </div>
                    </div>
                </details>
                <details v-if="catalogOutputMode === 'katalog'" class="rounded-xl border border-slate-100 bg-slate-50/60">
                    <summary class="cursor-pointer px-3 py-2 text-body-sm font-bold text-slate-600 flex items-center justify-between">Detail layout card <i class="fa-solid fa-chevron-down text-slate-300"></i></summary>
                    <div class="grid grid-cols-2 gap-2 p-3 pt-1">
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Tinggi gambar</label>
                            <input v-model.number="catalogCardCfg.imageHeight" class="form-input-compact w-full" placeholder="100" />
                        </div>
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Font produk</label>
                            <input v-model.number="catalogCardCfg.modelFontSize" class="form-input-compact w-full" placeholder="13" />
                        </div>
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Font harga</label>
                            <input v-model.number="catalogCardCfg.priceFontSize" class="form-input-compact w-full" placeholder="13" />
                        </div>
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Font SRP</label>
                            <input v-model.number="catalogCardCfg.srpFontSize" class="form-input-compact w-full" placeholder="11" />
                        </div>
                    </div>
                </details>
                <details v-if="catalogOutputMode === 'list' && catalogSelectedTemplate" class="rounded-xl border border-slate-100 bg-slate-50/60" open>
                    <summary class="cursor-pointer px-3 py-2 text-body-sm font-bold text-slate-600 flex items-center justify-between">Layout tabel <i class="fa-solid fa-chevron-down text-slate-300"></i></summary>
                    <div class="grid grid-cols-2 gap-2 p-3 pt-1">
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Tinggi header</label>
                            <input v-model.number="catalogSelectedTemplate.layout_config.headerHeight" class="form-input-compact w-full" placeholder="34" />
                        </div>
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Tinggi row</label>
                            <input v-model.number="catalogSelectedTemplate.layout_config.rowHeight" class="form-input-compact w-full" placeholder="28" />
                        </div>
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Font header</label>
                            <input v-model.number="catalogSelectedTemplate.layout_config.headerFontSize" class="form-input-compact w-full" placeholder="13" />
                        </div>
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Font isi</label>
                            <input v-model.number="catalogSelectedTemplate.layout_config.bodyFontSize" class="form-input-compact w-full" placeholder="13" />
                        </div>
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Font harga</label>
                            <input v-model.number="catalogSelectedTemplate.layout_config.priceFontSize" class="form-input-compact w-full" placeholder="13" />
                        </div>
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Radius header</label>
                            <input v-model.number="catalogSelectedTemplate.layout_config.borderRadius" class="form-input-compact w-full" placeholder="10" />
                        </div>
                    </div>
                </details>
                <details v-if="catalogSelectedTemplate" class="rounded-xl border border-slate-100 bg-slate-50/60">
                    <summary class="cursor-pointer px-3 py-2 text-body-sm font-bold text-slate-600 flex items-center justify-between">Warna <i class="fa-solid fa-chevron-down text-slate-300"></i></summary>
                    <div class="grid grid-cols-1 gap-1.5 p-3 pt-1">
                        <div v-for="colorField in catalogColorFields" :key="colorField.key" class="flex items-center gap-2">
                            <span class="type-micro text-slate-400 w-24 flex-shrink-0">{{ colorField.label }}</span>
                            <button type="button"
                                class="flex-1 flex items-center gap-2 px-2.5 rounded-lg border border-slate-200 bg-white hover:border-slate-300 transition-colors cursor-pointer"
                                style="height:30px"
                                @click="openColorPicker(() => catalogSelectedTemplate.layout_config[colorField.key], v => catalogSelectedTemplate.layout_config[colorField.key] = v, $event)">
                                <span class="relative flex-shrink-0 rounded-full overflow-hidden border border-black/10" style="width:18px;height:18px">
                                    <span class="absolute inset-0" style="background:repeating-conic-gradient(#e2e8f0 0% 25%,white 0% 50%) 0 0/6px 6px"></span>
                                    <span class="absolute inset-0" :style="'background:' + (catalogSelectedTemplate.layout_config[colorField.key] || '#ffffff')"></span>
                                </span>
                                <span class="flex-1 text-left font-mono text-slate-500 truncate" style="font-size:10px">{{ catalogSelectedTemplate.layout_config[colorField.key] || '—' }}</span>
                            </button>
                        </div>
                    </div>
                </details>
            </div>
            <div class="section-card p-4 space-y-3">
                <div class="space-y-1">
                    <p class="text-overline text-slate-400 uppercase">Brand</p>
                    <div class="flex flex-wrap gap-1.5">
                        <button @click="catalogSelectedSheet = 'all'" :class="['px-3 py-1.5 rounded-xl text-body-sm font-bold border', catalogSelectedSheet === 'all' ? 'bg-ppp-accent text-white border-ppp-accent' : 'bg-white text-slate-500 border-slate-200']">Semua</button>
                        <button v-for="sheet in pricelistBrandSheets" :key="sheet" @click="catalogSelectedSheet = sheet"
                            :class="['px-3 py-1.5 rounded-xl text-body-sm font-bold border', catalogSelectedSheet === sheet ? 'bg-ppp-accent text-white border-ppp-accent' : 'bg-white text-slate-500 border-slate-200']">
                            {{ sheet }}
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input v-model="catalogUrutStart" class="form-input-compact" placeholder="URUT awal" />
                    <input v-model="catalogUrutEnd" class="form-input-compact" placeholder="URUT akhir" />
                </div>
                <div>
                    <p class="type-micro text-slate-400 mb-1">Kolom harga</p>
                    <div class="flex gap-1.5">
                        <button v-for="opt in [{ key: 'special_price', label: 'Special Price' }, { key: 'harga_jual', label: 'Harga Jual' }]" :key="opt.key"
                            @click="catalogPriceKey = opt.key"
                            :class="['px-3 py-1.5 rounded-xl text-body-sm font-bold border flex-1', catalogPriceKey === opt.key ? 'bg-ppp-accent text-white border-ppp-accent' : 'bg-white text-slate-500 border-slate-200']">
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
                    <button @click="catalogPreviewZoom = 1" class="px-3 py-2 rounded-xl text-body-sm font-bold border border-slate-200 text-slate-500 bg-white">100%</button>
                    <button @click="catalogPreviewZoomBy(1.18)" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-magnifying-glass-plus"></i></button>
                    <button @click="catalogPreviewNav(1)" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-right"></i></button>
                    <button @click="downloadCatalogPreview(catalogPreviewImages[catalogPreviewModalIndex], catalogPreviewModalIndex)" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-download"></i></button>
                    <button @click="closeCatalogPreviewModal" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>
            <div class="flex-1 overflow-auto p-6" @click.stop>
                <div class="min-w-full min-h-full flex items-start justify-center">
                    <img :src="catalogPreviewImages[catalogPreviewModalIndex]" class="rounded-xl shadow-2xl bg-white transition-transform origin-top" :style="`width:auto;height:auto;max-width:none;transform:scale(${catalogPreviewZoom})`" />
                </div>
            </div>
        </div>
    </teleport>

    <div v-if="catalogTemplateModalOpen" class="fixed inset-0 z-[9000] flex items-center justify-center p-4 bg-slate-900/40">
        <div class="flex flex-col bg-white rounded-2xl w-full max-w-3xl max-h-[88vh] overflow-hidden shadow-xl">
            <div class="flex items-center justify-between px-5 py-5 border-b border-slate-100 shrink-0">
                <h3 class="type-label font-bold text-slate-800">{{ catalogTemplateModalType === 'edit' ? 'Edit Template' : 'Tambah Template' }}</h3>
                <button @click="catalogTemplateModalOpen = false" class="icon-utility-button"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="overflow-y-auto p-5 space-y-4">
            <input v-model="catalogTemplateForm.name" class="form-input-compact w-full" placeholder="Nama template" />
            <div class="grid grid-cols-3 gap-2">
                <button @click="catalogTemplateForm.format = 'story'" :class="['px-3 py-2 rounded-xl text-body-sm font-bold border', catalogTemplateForm.format === 'story' ? 'bg-ppp-accent text-white border-ppp-accent' : 'bg-white text-slate-500 border-slate-200']">Story 1080x1920</button>
                <button @click="catalogTemplateForm.format = 'feed'" :class="['px-3 py-2 rounded-xl text-body-sm font-bold border', catalogTemplateForm.format === 'feed' ? 'bg-ppp-accent text-white border-ppp-accent' : 'bg-white text-slate-500 border-slate-200']">Feed 1080x1350</button>
                <button @click="catalogTemplateForm.format = 'a4'" :class="['px-3 py-2 rounded-xl text-body-sm font-bold border', catalogTemplateForm.format === 'a4' ? 'bg-ppp-accent text-white border-ppp-accent' : 'bg-white text-slate-500 border-slate-200']">A4 1240x1754</button>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="type-overline text-slate-400 uppercase mb-1 block">Posisi & Ukuran Tabel</label>
                    <div class="catalog-layout-drag relative w-full aspect-[4/5] bg-slate-100 rounded-xl overflow-hidden border border-slate-200 mb-3 cursor-move select-none"
                        @pointerdown="catalogLayoutDragStart($event, catalogTemplateForm)">
                        <img v-if="catalogTemplateForm.background_url" :src="catalogTemplateForm.background_url" class="absolute inset-0 w-full h-full object-cover pointer-events-none" />
                        <div v-else class="absolute inset-0 bg-gradient-to-br from-orange-400 to-amber-500 pointer-events-none"></div>
                        <div class="absolute left-1/2 top-0 bottom-0 border-l border-white/70 border-dashed pointer-events-none"></div>
                        <div class="absolute top-1/2 left-0 right-0 border-t border-white/70 border-dashed pointer-events-none"></div>
                        <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 px-2 py-0.5 rounded-full bg-slate-900/70 text-white text-[9px] font-bold uppercase pointer-events-none">CENTER</div>
                        <div class="absolute px-3 py-1 rounded-full border-2 border-dashed border-white bg-slate-900/60 text-white text-[11px] font-bold uppercase text-center cursor-move"
                            :style="catalogTitlePreviewStyle(catalogTemplateForm)"
                            @pointerdown="catalogLayoutDragStart($event, catalogTemplateForm, 'title')">
                            Drag judul brand
                        </div>
                        <div class="absolute border-2 border-dashed border-white bg-slate-900/25 shadow-lg rounded-lg pointer-events-none"
                            :style="catalogLayoutPreviewStyle(catalogTemplateForm)">
                            <div class="absolute -top-7 left-0 px-2 py-1 rounded bg-slate-900 text-white text-[10px] font-bold uppercase whitespace-nowrap">Drag posisi tabel</div>
                            <div class="h-4 rounded-t-md bg-slate-700/90"></div>
                            <div class="space-y-1 p-2">
                                <div v-for="i in 5" :key="i" class="h-2 rounded" :class="i % 2 ? 'bg-white/70' : 'bg-white/25'"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="type-overline text-slate-400 uppercase mb-1 block">Warna Tabel</label>
                        <div class="grid grid-cols-1 gap-1.5">
                            <div v-for="colorField in catalogColorFields" :key="colorField.key" class="flex items-center gap-2">
                                <span class="type-micro text-slate-400 w-24 flex-shrink-0">{{ colorField.label }}</span>
                                <button type="button"
                                    class="flex-1 flex items-center gap-2 px-2.5 rounded-lg border border-slate-200 bg-white hover:border-slate-300 transition-colors cursor-pointer"
                                    style="height:30px"
                                    @click="openColorPicker(() => catalogTemplateForm.layout_config[colorField.key], v => catalogTemplateForm.layout_config[colorField.key] = v, $event)">
                                    <span class="relative flex-shrink-0 rounded-full overflow-hidden border border-black/10" style="width:18px;height:18px">
                                        <span class="absolute inset-0" style="background:repeating-conic-gradient(#e2e8f0 0% 25%,white 0% 50%) 0 0/6px 6px"></span>
                                        <span class="absolute inset-0" :style="'background:' + (catalogTemplateForm.layout_config[colorField.key] || '#ffffff')"></span>
                                    </span>
                                    <span class="flex-1 text-left font-mono text-slate-500 truncate" style="font-size:10px">{{ catalogTemplateForm.layout_config[colorField.key] || '—' }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <p class="text-body-sm text-slate-400 mb-2">Geser kotak tabel pada preview untuk mengubah X/Y. Garis putus-putus menunjukkan center; posisi X akan snap ke center jika dekat.</p>
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
                            <label class="type-micro text-slate-400 block mb-1">Max item per halaman (0 = auto)</label>
                            <input v-model.number="catalogTemplateForm.layout_config.maxItems" class="form-input-compact w-full" placeholder="cth: 0" />
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

                <div>
                    <label class="type-overline text-slate-400 uppercase mb-1 block">Posisi & Font Judul</label>
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
                            <label class="type-micro text-slate-400 block mb-1">Jarak dari header (fallback)</label>
                            <input v-model.number="catalogTemplateForm.layout_config.titleGap" class="form-input-compact w-full" placeholder="cth: 42" />
                        </div>
                        <div class="col-span-2">
                            <label class="type-micro text-slate-400 block mb-1">Warna judul</label>
                            <button type="button"
                                class="w-full flex items-center gap-2 px-2.5 rounded-lg border border-slate-200 bg-white hover:border-slate-300 transition-colors cursor-pointer"
                                style="height:30px"
                                @click="openColorPicker(() => catalogTemplateForm.layout_config.titleColor, v => catalogTemplateForm.layout_config.titleColor = v, $event)">
                                <span class="relative flex-shrink-0 rounded-full overflow-hidden border border-black/10" style="width:18px;height:18px">
                                    <span class="absolute inset-0" style="background:repeating-conic-gradient(#e2e8f0 0% 25%,white 0% 50%) 0 0/6px 6px"></span>
                                    <span class="absolute inset-0" :style="'background:' + (catalogTemplateForm.layout_config.titleColor || '#ffffff')"></span>
                                </span>
                                <span class="flex-1 text-left font-mono text-slate-500 truncate" style="font-size:10px">{{ catalogTemplateForm.layout_config.titleColor || '#ffffff' }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="type-overline text-slate-400 uppercase mb-1 block">Font Tabel</label>
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
            <button @click="saveCatalogTemplate" class="primary-cta-button primary-cta-button--accent w-full">Simpan Template</button>
            </div>
        </div>
    </div>
</div>
@endverbatim
