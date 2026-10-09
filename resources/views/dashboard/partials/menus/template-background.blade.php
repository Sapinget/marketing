@verbatim
<div v-if="activeTab === 'template_background'" class="space-y-4 animate-fadeIn xl:h-[calc(100dvh-7rem)] xl:min-h-[620px] xl:overflow-hidden">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="type-title text-slate-900">Template Background</h1>
            <p class="text-body-sm text-slate-400 mt-1">Kelola template background canvas (Story / Feed / A4) untuk generator katalog.</p>
        </div>
<label class="primary-cta-button primary-cta-button--accent primary-cta-button--icon-only w-full sm:w-auto cursor-pointer" aria-label="Upload Template Baru" title="Upload Template Baru">
             <i class="fa-solid fa-plus"></i>
             <input type="file" accept="image/*" class="hidden" @change="uploadNewCatalogTemplateBackground" />
         </label>
    </div>

    <div class="section-card flex flex-col xl:h-[calc(100%-4.5rem)] min-h-[500px] overflow-hidden p-4 md:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div>
                <h3 class="type-label font-semibold text-slate-700">Daftar Template</h3>
                <p class="text-body-sm text-slate-400 mt-0.5">{{ catalogTemplates.length }} template terpasang</p>
            </div>
<button @click="openCatalogTemplateModal('create')" class="primary-cta-button primary-cta-button--secondary primary-cta-button--icon-only" aria-label="Buat Template Manual">
                         <i class="fa-solid fa-sliders"></i>
                     </button>
        </div>

        <div v-if="!catalogTemplates.length" class="flex-1 flex flex-col items-center justify-center text-center p-8 text-slate-400 space-y-3">
            <i class="fa-solid fa-image text-4xl text-slate-200"></i>
            <div>
                <p class="font-semibold text-slate-600">Belum ada template background</p>
                <p class="text-body-sm text-slate-400">Upload gambar background Story 1080x1920 atau Feed 1080x1350 untuk memulai.</p>
            </div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 overflow-y-auto min-h-0 pr-1">
            <div v-for="template in catalogTemplates" :key="template.ID" class="border border-slate-200 rounded-2xl p-4 flex flex-col justify-between gap-4 bg-white hover:border-ppp-accent/40 transition-colors">
                <div class="space-y-3">
                    <div class="relative w-full aspect-[9/16] max-h-56 bg-slate-900 rounded-xl overflow-hidden border border-slate-200 cursor-pointer shadow-sm group/thumb" @click="openCatalogTemplateModal('edit', template)">
                        <img v-if="template.thumbnail_url" :src="template.thumbnail_url" class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover/thumb:scale-105 pointer-events-none" />
                        <template v-else>
                            <img v-if="template.background_url" :src="template.background_url" class="absolute inset-0 w-full h-full object-cover pointer-events-none" />
                            <div v-else class="absolute inset-0 bg-gradient-to-br from-orange-400 to-amber-500"></div>
                            <div v-for="(asset, assetIndex) in template.layout_config?.assets || []" :key="asset.id" class="absolute pointer-events-none" :style="catalogAssetPreviewStyle(asset, template, assetIndex)">
                                <img :src="asset.src" :alt="asset.name" class="w-full h-full object-contain" />
                            </div>
                            <div class="absolute text-center font-extrabold uppercase whitespace-nowrap pointer-events-none" :style="catalogTemplateThumbnailTitleStyle(template)">
                                {{ template.output_mode === 'katalog' ? (template.layout_config?.katalogTitle || 'KATALOG PRODUK') : (template.layout_config?.listTitle || 'DAFTAR HARGA') }}
                            </div>
                            <div v-for="box in template.layout_config?.textBoxes || []" :key="box.id" class="absolute whitespace-nowrap pointer-events-none" :style="catalogTemplateThumbnailTextBoxStyle(box, template)">
                                {{ box.text }}
                            </div>
                            <div v-if="template.output_mode === 'katalog'" class="absolute grid gap-1 pointer-events-none" :style="catalogCardPreviewStyle(template)">
                                <div v-for="row in catalogPreviewRows.slice(0, Number(template.layout_config?.cardColumns || 3))" :key="row.nama_produk" class="rounded bg-slate-900/75 p-1 text-center overflow-hidden">
                                    <div class="truncate font-bold" :style="catalogTemplateThumbnailCardTextStyle(template, 'model')">{{ catalogProductType(row) }}</div>
                                    <div class="truncate font-bold" :style="catalogTemplateThumbnailCardTextStyle(template, 'price')">{{ formatCatalogPrice(row.special_price) }}</div>
                                </div>
                            </div>
                            <div v-else class="absolute overflow-hidden pointer-events-none" :style="catalogLayoutPreviewStyle(template)">
                                <div class="grid grid-cols-[2fr_0.6fr_0.75fr_1fr_1fr] items-center" :style="catalogTemplateThumbnailTableHeaderStyle(template)">
                                    <span class="pl-1 truncate">TYPE</span><span class="text-center">RAM</span><span class="text-center">ROM</span><span class="text-right">NORMAL</span><span class="text-right pr-1">SPECIAL</span>
                                </div>
                                <div :style="catalogTableRowsPreviewStyle(template)">
                                    <div v-for="(row, index) in catalogPreviewRows.slice(0, 5)" :key="index" class="grid grid-cols-[2fr_0.6fr_0.75fr_1fr_1fr] items-center" :style="catalogTemplateThumbnailTableRowStyle(template, index)">
                                        <span class="pl-1 truncate">{{ catalogProductType(row) }}</span><span class="text-center truncate">{{ row.ram }}</span><span class="text-center truncate">{{ row.storage }}</span><span class="text-right truncate">{{ formatCatalogPrice(row.harga_nasional) }}</span><span class="text-right pr-1 truncate font-bold">{{ formatCatalogPrice(row.special_price) }}</span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div>
                        <div class="text-body-sm font-bold text-slate-800 truncate">{{ template.name }}</div>
                        <div class="text-overline text-slate-400 uppercase mt-0.5">{{ template.format }} · {{ template.canvas_width }}x{{ template.canvas_height }} px</div>
                        <span :class="['inline-flex mt-2 px-2 py-0.5 rounded-full text-overline font-bold', template.output_mode === 'katalog' ? 'bg-violet-50 text-violet-600' : 'bg-sky-50 text-sky-600']">{{ template.output_mode === 'katalog' ? 'Katalog Produk' : 'List Tabel' }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 pt-2 border-t border-slate-100 justify-end">
                    <label class="table-action-button table-action-compact cursor-pointer" title="Ganti Gambar" aria-label="Ganti Gambar">
                        <i class="fa-solid fa-image text-body-sm"></i>
                        <input type="file" accept="image/*" class="hidden" @change="uploadCatalogTemplateBackground($event, template)" />
                    </label>
                    <button @click="openCatalogTemplateModal('edit', template)" class="table-action-button table-action-compact" title="Edit" aria-label="Edit"><i class="fa-solid fa-pen-to-square text-body-sm"></i></button>
                    <button @click="deleteCatalogTemplate(template)" class="table-action-button table-action-compact table-action-danger" title="Hapus" aria-label="Hapus"><i class="fa-solid fa-trash-can text-body-sm"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>
@endverbatim
@include('dashboard.partials.menus.pricelist-katalog', ['catalogTemplateModalOnly' => true])
