@verbatim
<div v-show="activeTab === 'apple_katalog'" class="space-y-4 md:space-y-6 animate-fadeIn pb-10 xl:pb-0 xl:h-[calc(100dvh-7rem)] xl:min-h-[620px] xl:overflow-hidden">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="type-title text-slate-900">Katalog Apple</h1>
            <p class="text-body-sm text-slate-400 mt-1">Pilih model Apple, atur template, lalu generate katalog siap publish.</p>
        </div>
        <button @click="syncAppleProducts" :disabled="appleSyncing" class="primary-cta-button primary-cta-button--accent w-full sm:w-auto">
            <i :class="['fa-solid fa-rotate', appleSyncing && 'fa-spin']"></i>
            <span>{{ appleSyncing ? 'Sync...' : 'Sync Data' }}</span>
        </button>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.4fr)_minmax(340px,0.6fr)] gap-4 xl:h-[calc(100%-4.5rem)] min-h-0">

        <!-- LEFT: products panel -->
        <div class="flex flex-col gap-3 min-h-0 min-w-0">

            <!-- header: search + toggle + sync + categories -->
            <div class="section-card p-4 catalog-control-stack">
                <div class="catalog-control-field">
                    <p class="text-overline text-slate-400 uppercase">Data Produk</p>
                    <div class="grid grid-cols-1 sm:grid-cols-[minmax(0,1fr)_auto] gap-2">
                        <input v-model="appleSearch" class="form-input-compact" placeholder="Cari model..." />
                        <div class="flex items-center justify-end gap-2">
                            <div class="catalog-segment-group w-24 flex-shrink-0">
                                <button @click="appleView = 'card'" :class="['catalog-segment-button', appleView === 'card' ? 'catalog-segment-button--active' : '']" title="Card view" aria-label="Card view">
                                    <i class="fa-solid fa-grip"></i>
                                </button>
                                <button @click="appleView = 'table'" :class="['catalog-segment-button', appleView === 'table' ? 'catalog-segment-button--active' : '']" title="Table view" aria-label="Table view">
                                    <i class="fa-solid fa-table-list"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="catalog-control-grid">
                    <div class="catalog-control-field">
                        <p class="catalog-control-label">Kategori</p>
                        <div class="relative group search-select-container min-w-0">
                            <button type="button" @click="toggleSearchSelect($event, 'apple_category')" :aria-expanded="searchSelectOpen === 'apple_category' ? 'true' : 'false'" class="select-trigger-button select-trigger-button-compact select-trigger-button-form toolbar-trigger-field-form w-full">
                                <span class="min-w-0 truncate">{{ appleCategory || 'Pilih kategori' }}</span>
                                <i class="fa-solid fa-chevron-down ml-auto text-[9px] text-slate-400"></i>
                            </button>
                            <transition name="fade">
                                <div v-if="searchSelectOpen === 'apple_category'" :style="popoverStyle" class="search-select-popover">
                                    <div class="max-h-56 overflow-y-auto custom-scrollbar p-1">
                                        <div v-for="sheet in appleSheets" :key="sheet" @click="appleCategory = sheet; searchSelectOpen = null" :class="['popover-option', appleCategory === sheet ? 'popover-option-active' : '']">{{ sheet }}</div>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>
                    <div class="catalog-control-field">
                        <p class="catalog-control-label">Harga utama</p>
                        <div class="relative group search-select-container min-w-0">
                            <button type="button" @click="toggleSearchSelect($event, 'apple_price')" :aria-expanded="searchSelectOpen === 'apple_price' ? 'true' : 'false'" class="select-trigger-button select-trigger-button-compact select-trigger-button-form toolbar-trigger-field-form w-full">
                                <span class="min-w-0 truncate">{{ (applePriceOptions.find(opt => opt.key === applePriceKey) || {}).label || 'Pilih harga utama' }}</span>
                                <i class="fa-solid fa-chevron-down ml-auto text-[9px] text-slate-400"></i>
                            </button>
                            <transition name="fade">
                                <div v-if="searchSelectOpen === 'apple_price'" :style="popoverStyle" class="search-select-popover">
                                    <div class="max-h-56 overflow-y-auto custom-scrollbar p-1">
                                        <div v-for="opt in applePriceOptions" :key="opt.key" @click="applePriceKey = opt.key; searchSelectOpen = null" :class="['popover-option', applePriceKey === opt.key ? 'popover-option-active' : '']">{{ opt.label }}</div>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <button @click="appleSelectAll" class="primary-cta-button primary-cta-button--accent w-full whitespace-nowrap">Select All</button>
                    <button @click="appleResetSelection" class="primary-cta-button primary-cta-button--danger w-full whitespace-nowrap">Reset</button>
                </div>
            </div>

            <!-- CARD VIEW -->
            <div v-if="appleView === 'card'" class="section-card flex-1 min-h-[420px] xl:min-h-0 overflow-y-auto p-3 md:p-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-5 gap-2 md:gap-3 content-start">
                <div v-for="group in appleGroupedModels" :key="group.model"
                    class="relative border rounded-2xl p-2.5 md:p-3 transition-all hover:shadow-sm"
                    :class="appleSelectedModels.includes(group.model)
                        ? 'border-ppp-accent bg-ppp-accent/5 shadow-sm'
                        : 'border-slate-200 bg-white hover:border-slate-300'">
                    <!-- order number / toggle-all — click header area -->
                    <div class="absolute top-2 right-2 z-10 cursor-pointer" @click.stop="appleToggleModel(group.model)">
                        <div v-if="appleSelectedModels.includes(group.model)"
                            class="apple-selection-badge w-5 h-5 rounded-full bg-ppp-accent flex items-center justify-center text-white font-bold">
                            {{ appleSelectedModels.indexOf(group.model) + 1 }}
                        </div>
                        <div v-else class="w-5 h-5 rounded-full border-2 border-slate-300"></div>
                    </div>
                    <!-- product image (click to toggle all) -->
                    <div class="flex justify-center mb-1.5 cursor-pointer" @click="appleToggleModel(group.model)">
                        <div class="apple-product-thumb bg-slate-50 rounded-lg overflow-hidden flex items-center justify-center">
                            <img v-if="appleGetImage(appleCategory, group.modelKey, appleModelColorIdx(appleCategory, group.modelKey))"
                                :src="appleGetImage(appleCategory, group.modelKey, appleModelColorIdx(appleCategory, group.modelKey))"
                                class="apple-product-image" :alt="group.model" />
                            <i v-else class="apple-empty-logo fa-brands fa-apple text-slate-200"></i>
                        </div>
                    </div>
                    <!-- model name (click to toggle all) -->
                    <p class="text-body-sm font-bold text-slate-700 text-center leading-tight mb-1.5 cursor-pointer" @click="appleToggleModel(group.model)">{{ group.model }}</p>
                    <!-- per-variant storage rows — each clickable -->
                    <div v-for="variant in group.variants" :key="appleVariantKey(variant)"
                        @click.stop="appleToggleVariant(group.model, appleVariantKey(variant))"
                        class="flex items-center gap-1.5 px-1.5 py-0.5 rounded-lg cursor-pointer transition-colors mt-0.5"
                        :class="appleSelectedModels.includes(group.model) && (appleSelectedVariants[group.model] || []).includes(appleVariantKey(variant))
                            ? 'bg-ppp-accent/10'
                            : 'hover:bg-slate-50 opacity-50'">
                        <div class="flex-shrink-0 w-3 h-3 rounded-full border flex items-center justify-center transition-colors"
                            :class="appleSelectedModels.includes(group.model) && (appleSelectedVariants[group.model] || []).includes(appleVariantKey(variant))
                                ? 'bg-ppp-accent border-ppp-accent'
                                : 'border-slate-300 bg-white'">
                            <i v-if="appleSelectedModels.includes(group.model) && (appleSelectedVariants[group.model] || []).includes(appleVariantKey(variant))"
                                class="apple-check-icon fa-solid fa-check text-white"></i>
                        </div>
                        <span class="text-overline font-semibold text-slate-600 truncate flex-1">{{ appleVariantLabel(group.model, variant) }}</span>
                        <span class="text-overline font-bold text-slate-800 whitespace-nowrap tabular-nums">
                            {{ appleResolvePrice(variant) ? formatApplePrice(appleResolvePrice(variant)) : '—' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- TABLE VIEW -->
            <div v-else-if="appleView === 'table'" class="section-card flex-1 min-h-[420px] xl:min-h-0 min-w-0 overflow-hidden flex flex-col">
                <div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-slate-100 bg-white">
                    <div class="min-w-0">
                        <p class="text-overline text-slate-400 uppercase">Table View</p>
                        <p class="text-body-sm text-slate-500 truncate">Tipe harga katalog: {{ (applePriceOptions.find(opt => opt.key === applePriceKey) || {}).label || 'Harga utama' }}</p>
                    </div>
                    <div class="text-body-sm font-bold text-ppp-accent whitespace-nowrap">{{ appleTableRows.length }} data</div>
                </div>
                <div class="apple-table-scroll flex-1 min-h-0 min-w-0 max-w-full">
                    <table class="apple-catalog-table text-body-sm">
                        <thead>
                            <tr class="table-header-row">
                                <th class="table-header-cell apple-model-cell text-left whitespace-nowrap">Model</th>
                                <th class="table-header-cell apple-storage-cell text-left whitespace-nowrap">Storage</th>
                                <template v-for="column in appleTablePriceColumns" :key="column.key">
                                    <th class="table-header-cell apple-price-cell text-right whitespace-nowrap">{{ column.label }}</th>
                                </template>
                                <th class="table-header-cell apple-select-cell text-center sticky right-0 bg-slate-100 z-[75]">Pilih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in appleTableRows" :key="row.model + row.storage"
                                @click="appleToggleVariant(row.model, appleVariantKey(row))"
                                :class="['cursor-pointer border-b border-slate-50 hover:bg-slate-50', appleIsVariantSelected(row.model, appleVariantKey(row)) ? 'bg-ppp-accent/5' : '']">
                                <td class="apple-model-cell px-3 py-2 font-semibold text-slate-700 whitespace-nowrap">{{ row.model }}</td>
                                <td class="apple-storage-cell px-3 py-2 text-slate-500 whitespace-nowrap">{{ row.storage }}</td>
                                <template v-for="column in appleTablePriceColumns" :key="column.key">
                                    <td class="apple-price-cell px-3 py-2 text-right text-slate-600 whitespace-nowrap">
                                        {{ formatApplePrice(appleTablePriceValue(row, column)) }}
                                    </td>
                                </template>
                                <td class="apple-select-cell px-3 py-2 text-center sticky right-0 bg-white">
                                    <div v-if="appleIsVariantSelected(row.model, appleVariantKey(row))"
                                        class="apple-selection-dot apple-selection-dot--active">
                                        {{ appleSelectedModelOrder(row.model) }}
                                    </div>
                                    <div v-else class="apple-selection-dot"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- RIGHT: config panel -->
        <div class="apple-config-panel flex flex-col gap-3 min-h-0 xl:overflow-hidden">

            <div class="section-card p-4 catalog-control-stack flex-shrink-0">
                <div class="catalog-control-field">
                    <p class="text-overline text-slate-400 uppercase">Pengaturan Output</p>
                    <div class="relative group search-select-container min-w-0">
                        <button type="button" @click="toggleSearchSelect($event, 'apple_template')" :aria-expanded="searchSelectOpen === 'apple_template' ? 'true' : 'false'" class="select-trigger-button select-trigger-button-compact select-trigger-button-form toolbar-trigger-field-form w-full">
                            <span class="min-w-0 truncate">{{ (catalogTemplates.find(template => template.ID === appleSelectedTemplateId) || {}).name || (catalogTemplates.find(template => template.ID === appleSelectedTemplateId) || {}).format || 'Pilih template' }}</span>
                            <i class="fa-solid fa-chevron-down ml-auto text-[9px] text-slate-400"></i>
                        </button>
                        <transition name="fade">
                            <div v-if="searchSelectOpen === 'apple_template'" :style="popoverStyle" class="search-select-popover">
                                <div class="p-2 border-b border-slate-100">
                                    <input v-model="searchSelectQuery" class="form-input-popover" placeholder="Cari template..." />
                                </div>
                                <div class="max-h-56 overflow-y-auto custom-scrollbar p-1">
                                    <div v-for="template in catalogTemplates.filter(template => !searchSelectQuery || String(template.name || template.format || '').toLowerCase().includes(searchSelectQuery.toLowerCase()))" :key="template.ID" @click="appleSelectedTemplateId = template.ID; searchSelectOpen = null" :class="['popover-option', appleSelectedTemplateId === template.ID ? 'popover-option-active' : '']">
                                        <div class="font-bold">{{ template.name || template.format }}</div>
                                        <div class="text-overline text-slate-400 uppercase">{{ template.format }} · {{ template.canvas_width }}x{{ template.canvas_height }}</div>
                                    </div>
                                    <div v-if="!catalogTemplates.length" class="px-3 py-2 text-body-sm text-slate-400">Belum ada template upload</div>
                                </div>
                            </div>
                        </transition>
                    </div>
                </div>
                <div class="catalog-control-field">
                    <p class="catalog-control-label">Kolom per baris</p>
                    <div class="search-select-container">
                        <button @click="toggleSearchSelect($event, 'apple-columns-per-row')" type="button" :aria-expanded="searchSelectOpen === 'apple-columns-per-row'" class="select-trigger-button select-trigger-button-compact select-trigger-button-form toolbar-trigger-field-form w-full">
                            <span class="truncate">{{ appleColumnsPerRow }} kolom</span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                        </button>
                        <div v-if="searchSelectOpen === 'apple-columns-per-row'" class="search-select-popover" :style="popoverStyle">
                            <button v-for="col in [3,4,5,6,7,8]" :key="col" @click="appleColumnsPerRow = col; searchSelectOpen = null" type="button" :class="['popover-option', appleColumnsPerRow === col ? 'popover-option-active' : '']">
                                {{ col }} kolom
                            </button>
                        </div>
                    </div>
                </div>
                <button @click="generateAppleCatalog"
                    :disabled="appleGenerating || !appleSelectedModels.length || !appleSelectedTemplateId"
                    class="primary-cta-button primary-cta-button--info w-full">
                    <i :class="['fa-solid', appleGenerating ? 'fa-spinner fa-spin' : 'fa-wand-magic-sparkles']"></i>
                    <span>{{ appleGenerating ? 'Generating...' : 'Generate Katalog' }}</span>
                </button>
            </div>

            <div class="section-card apple-layout-card flex-1 min-h-0 overflow-hidden flex flex-col">
                <div class="apple-layout-card__header flex-shrink-0 p-4 pb-3">
                    <p class="text-overline text-slate-400 uppercase">Layout</p>
                    <p class="text-body-sm text-slate-400 mt-0.5">Cukup atur bagian utama. Detail lanjutan bisa dibuka kalau hasil preview belum pas.</p>
                </div>
                <div class="apple-layout-card__body flex-1 min-h-0 overflow-y-auto custom-scrollbar px-4 pb-4 space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <div>
                        <label class="type-micro text-slate-400 block mb-1">Area atas</label>
                        <input v-model.number="appleCfg.topOffset" class="form-input-compact w-full" placeholder="150" />
                    </div>
                    <div>
                        <label class="type-micro text-slate-400 block mb-1">Area bawah</label>
                        <input v-model.number="appleCfg.bottomSafe" class="form-input-compact w-full" placeholder="130" />
                    </div>
                    <div>
                        <label class="type-micro text-slate-400 block mb-1">Padding samping</label>
                        <input v-model.number="appleCfg.padding" class="form-input-compact w-full" placeholder="100" />
                    </div>
                    <div>
                        <label class="type-micro text-slate-400 block mb-1">Jarak item</label>
                        <input v-model.number="appleCfg.gap" class="form-input-compact w-full" placeholder="50" />
                    </div>
                </div>
                <details class="rounded-xl border border-slate-100 bg-white overflow-hidden">
                    <summary class="cursor-pointer toolbar-trigger-field-form control-height-md px-3 text-body-sm font-bold text-slate-600 flex items-center justify-between">Pengaturan teks & judul <i class="fa-solid fa-chevron-down text-slate-300"></i></summary>
                    <div class="grid grid-cols-2 gap-2 p-3 pt-1">
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Font judul</label>
                            <input v-model.number="appleCfg.titleFontSize" class="form-input-compact w-full" placeholder="32" />
                        </div>
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Y judul</label>
                            <input v-model.number="appleCfg.titleY" class="form-input-compact w-full" placeholder="auto" />
                        </div>
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Font model</label>
                            <input v-model.number="appleCfg.modelFontSize" class="form-input-compact w-full" placeholder="13" />
                        </div>
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Font harga</label>
                            <input v-model.number="appleCfg.priceFontSize" class="form-input-compact w-full" placeholder="13" />
                        </div>
                    </div>
                </details>
                <details v-if="applePriceKey !== 'second_table'" class="rounded-xl border border-slate-100 bg-white overflow-hidden">
                    <summary class="cursor-pointer toolbar-trigger-field-form control-height-md px-3 text-body-sm font-bold text-slate-600 flex items-center justify-between">Detail layout card <i class="fa-solid fa-chevron-down text-slate-300"></i></summary>
                    <div class="grid grid-cols-2 gap-2 p-3 pt-1">
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Tinggi gambar</label>
                            <input v-model.number="appleCfg.imageHeight" class="form-input-compact w-full" placeholder="100" />
                        </div>
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Baris/halaman</label>
                            <input v-model.number="appleCfg.rowsPerPage" class="form-input-compact w-full" placeholder="auto" min="1" />
                        </div>
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Font SRP</label>
                            <input v-model.number="appleCfg.srpFontSize" class="form-input-compact w-full" placeholder="11" />
                        </div>
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Jarak row</label>
                            <input v-model.number="appleCfg.rowGap" class="form-input-compact w-full" placeholder="4" />
                        </div>
                    </div>
                </details>
                <details v-if="applePriceKey === 'second_table'" class="rounded-xl border border-slate-100 bg-white overflow-hidden" open>
                    <summary class="cursor-pointer toolbar-trigger-field-form control-height-md px-3 text-body-sm font-bold text-slate-600 flex items-center justify-between">Layout tabel second <i class="fa-solid fa-chevron-down text-slate-300"></i></summary>
                    <div class="grid grid-cols-2 gap-2 p-3 pt-1">
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Tinggi header</label>
                            <input v-model.number="appleCfg.headerHeight" class="form-input-compact w-full" placeholder="40" min="1" />
                        </div>
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Tinggi row</label>
                            <input v-model.number="appleCfg.rowHeight" class="form-input-compact w-full" placeholder="34" min="1" />
                        </div>
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Font header</label>
                            <input v-model.number="appleCfg.tableHeaderFontSize" class="form-input-compact w-full" placeholder="14" min="1" />
                        </div>
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Font isi</label>
                            <input v-model.number="appleCfg.tableBodyFontSize" class="form-input-compact w-full" placeholder="14" min="1" />
                        </div>
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Seri %</label>
                            <input v-model.number="appleCfg.tableSeriWidth" class="form-input-compact w-full" placeholder="34" min="1" />
                        </div>
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Storage %</label>
                            <input v-model.number="appleCfg.tableStorageWidth" class="form-input-compact w-full" placeholder="18" min="1" />
                        </div>
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Beacukai %</label>
                            <input v-model.number="appleCfg.tableBeacukaiWidth" class="form-input-compact w-full" placeholder="24" min="1" />
                        </div>
                        <div>
                            <label class="type-micro text-slate-400 block mb-1">Radius header</label>
                            <input v-model.number="appleCfg.tableHeaderRadius" class="form-input-compact w-full" placeholder="12" min="0" />
                        </div>
                    </div>
                </details>
                <details class="rounded-xl border border-slate-100 bg-white overflow-hidden">
                    <summary class="cursor-pointer toolbar-trigger-field-form control-height-md px-3 text-body-sm font-bold text-slate-600 flex items-center justify-between">Warna <i class="fa-solid fa-chevron-down text-slate-300"></i></summary>
                    <div class="grid grid-cols-1 gap-1.5 p-3 pt-1">
                        <template v-for="(colorCfg, idx) in [
                            { label: 'Nama produk', key: 'modelColor', ph: '#ffffff' },
                            { label: 'Label/subjudul', key: 'labelColor', ph: 'rgba(255,255,255,0.6)' },
                            { label: 'Badge/header text', key: 'badgeTextColor', ph: '#ffffff' },
                            { label: 'Harga', key: 'priceColor', ph: '#ffffff' },
                            { label: 'SRP coret', key: 'srpColor', ph: 'rgba(255,255,255,0.5)' },
                            { label: 'Header tabel', key: 'tableHeaderBg', ph: 'rgba(255,255,255,0.16)' },
                            { label: 'Row ganjil', key: 'tableRowOddBg', ph: 'rgba(255,255,255,0.08)' },
                            { label: 'Row genap', key: 'tableRowEvenBg', ph: 'rgba(255,255,255,0.16)' },
                            { label: 'Garis tabel', key: 'tableBorderColor', ph: 'rgba(255,255,255,0.35)' },
                            { label: 'Garis seri', key: 'tableGroupLineColor', ph: 'rgba(255,255,255,0.5)' },
                        ]" :key="idx">
                            <div class="grid grid-cols-[7rem_minmax(0,1fr)] items-center gap-2">
                                <span class="type-micro text-slate-400 min-w-0 truncate">{{ colorCfg.label }}</span>
                                <button type="button"
                                    class="select-trigger-button select-trigger-button-compact select-trigger-button-form toolbar-trigger-field-form w-full min-w-0 justify-start gap-2 cursor-pointer"
                                    @click="openColorPicker(() => appleCfg[colorCfg.key], v => appleCfg[colorCfg.key] = v, $event)">
                                    <span class="color-swatch-sm">
                                        <span class="checkerboard-bg absolute inset-0"></span>
                                        <span class="absolute inset-0" :style="'background:' + (appleCfg[colorCfg.key] || colorCfg.ph)"></span>
                                    </span>
                                    <span class="color-value-text flex-1 text-left font-mono text-slate-500 truncate">{{ appleCfg[colorCfg.key] || colorCfg.ph }}</span>
                                </button>
                            </div>
                        </template>
                    </div>
                </details>
                <label for="appleRowSepToggle" class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-white px-3 py-2 cursor-pointer">
                    <span class="type-micro text-slate-400">Tampilkan separator antar baris</span>
                    <input id="appleRowSepToggle" type="checkbox" v-model="appleCfg.rowSep" class="sr-only" />
                    <span :class="['relative inline-flex h-5 w-9 flex-shrink-0 items-center rounded-full border transition-colors duration-300 ease-out', appleCfg.rowSep ? 'border-ppp-accent bg-ppp-accent' : 'border-slate-200 bg-slate-100']">
                        <span :class="['absolute left-0.5 top-1/2 h-4 w-4 -translate-y-1/2 rounded-full bg-white shadow-sm transition-transform duration-300 ease-out will-change-transform', appleCfg.rowSep ? 'translate-x-4' : 'translate-x-0']"></span>
                    </span>
                </label>
            </div>

            <!-- Selection summary -->
            <div v-if="appleSelectedModels.length" class="px-3 py-2 bg-ppp-accent/5 rounded-xl border border-ppp-accent/20">
                <p class="text-body-sm text-ppp-accent font-bold">{{ appleSelectedModels.length }} model dari {{ appleCategory }}</p>
                <p class="text-overline text-slate-400 mt-0.5">{{ appleSelectedModels.slice(0,3).join(', ') }}{{ appleSelectedModels.length > 3 ? ` +${appleSelectedModels.length - 3} lagi` : '' }}</p>
            </div>
            <!-- Preview images -->
            <div v-if="applePreviewImages.length" class="section-card p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="type-label font-semibold text-slate-700">Preview ({{ applePreviewImages.length }} halaman)</h3>
                    <button @click="applePreviewImages = []" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="space-y-3">
                    <div v-for="(img, idx) in applePreviewImages" :key="idx"
                        @click="openApplePreviewModal(idx)"
                        class="block cursor-pointer border border-slate-100 rounded-xl overflow-hidden hover:border-ppp-accent/40 transition-colors">
                        <div class="apple-preview-frame overflow-hidden bg-slate-100 flex justify-center">
                            <img :src="img" class="apple-preview-img" />
                        </div>
                        <div class="px-3 py-2 flex items-center gap-2 bg-slate-50 border-t border-slate-100">
                            <i class="fa-solid fa-eye text-slate-400 text-body-sm"></i>
                            <span class="text-body-sm text-slate-500">Halaman {{ idx + 1 }} — klik untuk preview besar</span>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>

    <teleport to="body">
        <div v-if="applePreviewModalOpen" class="fixed inset-0 z-[9400] bg-slate-950/80 flex flex-col" @click="closeApplePreviewModal">
            <div class="shrink-0 px-3 md:px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3 bg-white/95 border-b border-slate-200" @click.stop>
                <div class="min-w-0">
                    <h3 class="type-label font-bold text-slate-800">Preview Katalog Apple</h3>
                    <p class="text-body-sm text-slate-500 truncate">Halaman {{ applePreviewModalIndex + 1 }} / {{ applePreviewImages.length }} · Zoom {{ Math.round(applePreviewZoom * 100) }}%</p>
                </div>
                <div class="flex items-center gap-1.5 flex-wrap">
                    <button @click="applePreviewNav(-1)" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-left"></i></button>
                    <button @click="applePreviewZoomBy(0.85)" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-magnifying-glass-minus"></i></button>
                    <button @click="applePreviewZoom = 1" class="toolbar-segment-button border-slate-200 text-slate-500 bg-white">100%</button>
                    <button @click="applePreviewZoomBy(1.18)" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-magnifying-glass-plus"></i></button>
                    <button @click="applePreviewNav(1)" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-chevron-right"></i></button>
                    <button @click="downloadApplePreview(applePreviewImages[applePreviewModalIndex], applePreviewModalIndex)" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-download"></i></button>
                    <button @click="closeApplePreviewModal" class="icon-utility-button icon-utility-bordered"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>
            <div class="flex-1 overflow-auto p-2 md:p-6" @click.stop>
                <div class="min-w-full min-h-full flex items-start md:items-start justify-center">
                    <img :src="applePreviewImages[applePreviewModalIndex]"
                        class="preview-zoom-image apple-preview-modal-image rounded-xl shadow-2xl bg-white transition-transform origin-top"
                        :style="`transform:scale(${applePreviewZoom})`" />
                </div>
            </div>
        </div>
    </teleport>

    <!-- ── Apple Color Picker ──────────────────────────────────── -->
    <teleport to="body">
        <template v-if="applePickerOpen">
            <!-- backdrop -->
            <div class="floating-backdrop-layer fixed inset-0" @click="applePickerClose"></div>
            <!-- picker panel -->
            <div class="color-picker-panel fixed bg-white rounded-xl overflow-hidden"
                :style="`left:${applePickerPos.x}px;top:${applePickerPos.y}px`"
                @click.stop>

                <!-- Saturation / Brightness gradient -->
                <div class="color-picker-gradient relative select-none overflow-hidden"
                    :style="`background:${applePickerHueColor}`"
                    @mousedown="applePickerGradDrag">
                    <div class="absolute inset-0 bg-gradient-to-r from-white to-transparent"></div>
                    <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black"></div>
                    <div class="color-picker-handle color-picker-handle--strong absolute w-3 h-3 rounded-full pointer-events-none"
                        :style="`left:${applePickerS*100}%;top:${(1-applePickerV)*100}%`"></div>
                </div>

                <div class="p-2.5 space-y-2">
                    <!-- Preview + sliders -->
                    <div class="flex items-center gap-2">
                        <div class="color-swatch-md flex-shrink-0 rounded-lg border border-slate-200 relative overflow-hidden">
                            <div class="checkerboard-bg absolute inset-0"></div>
                            <div class="absolute inset-0" :style="`background:${applePickerColorStr}`"></div>
                        </div>
                        <div class="flex-1 space-y-1.5">
                            <!-- Hue -->
                            <div class="color-picker-slider color-picker-hue-track relative select-none rounded-full overflow-hidden"
                                @mousedown="(e) => applePickerSliderDrag(e, v => applePickerH = Math.round(v * 360))">
                                <div class="color-picker-handle absolute top-1/2 w-3 h-3 rounded-full pointer-events-none"
                                    :style="`left:${applePickerH/360*100}%;background:${applePickerHueColor}`"></div>
                            </div>
                            <!-- Alpha -->
                            <div class="color-picker-slider relative select-none rounded-full overflow-hidden"
                                @mousedown="(e) => applePickerSliderDrag(e, v => applePickerA = parseFloat(v.toFixed(2)))">
                                <div class="checkerboard-bg absolute inset-0"></div>
                                <div class="absolute inset-0" :style="`background:linear-gradient(to right,transparent,${applePickerHueColor})`"></div>
                                <div class="color-picker-handle absolute top-1/2 w-3 h-3 rounded-full pointer-events-none"
                                    :style="`left:${applePickerA*100}%;background:${applePickerColorStr}`"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Hex + opacity inputs -->
                    <div class="flex gap-1.5">
                        <div class="flex items-center border border-slate-200 rounded-md overflow-hidden flex-1">
                            <span class="color-picker-input-prefix pl-1.5 pr-0.5 text-slate-400 font-mono select-none">#</span>
                            <input :value="applePickerHexStr"
                                @input="applePickerSetHex($event.target.value)"
                                maxlength="6" placeholder="FFFFFF"
                                class="color-picker-input flex-1 min-w-0 py-1 pr-1.5 font-mono text-slate-700 border-0 outline-none bg-transparent uppercase" />
                        </div>
                        <div class="color-picker-percent-field flex items-center border border-slate-200 rounded-md overflow-hidden">
                            <input type="number" min="0" max="100"
                                :value="Math.round(applePickerA * 100)"
                                @input="applePickerA = Math.max(0, Math.min(1, +$event.target.value / 100))"
                                class="color-picker-input flex-1 min-w-0 py-1 pl-1.5 font-mono text-slate-700 border-0 outline-none bg-transparent"
                                placeholder="100" />
                            <span class="color-picker-percent pr-1.5 text-slate-400 select-none">%</span>
                        </div>
                    </div>

                    <!-- Saved colors -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="color-picker-saved-label text-overline text-slate-400 uppercase">Saved</span>
                            <button type="button" @click="applePickerAddSaved"
                                class="color-picker-saved-label text-overline text-ppp-accent font-semibold hover:opacity-70 transition-opacity">+ Add</button>
                        </div>
                        <div class="flex flex-wrap gap-1">
                            <button v-for="c in appleSavedColors" :key="c"
                                type="button"
                                @click="applePickerLoadSaved(c)"
                                :class="['w-5 h-5 rounded-full transition-transform hover:scale-110 relative overflow-hidden', applePickerColorStr === c ? 'color-picker-saved-swatch--active' : 'color-picker-saved-swatch']">
                                <span class="checkerboard-bg absolute inset-0"></span>
                                <span class="absolute inset-0" :style="`background:${c}`"></span>
                            </button>
                        </div>
                        <p v-if="!appleSavedColors.length" class="color-picker-saved-label text-overline text-slate-300 mt-0.5">Belum ada</p>
                    </div>
                </div>
            </div>
        </template>
    </teleport>
</div>
@endverbatim
