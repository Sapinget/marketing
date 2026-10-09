@verbatim
<div v-show="activeTab === 'tiktok_template'" class="space-y-4 animate-fadeIn">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="type-title text-slate-900">Template TikTok</h1>
            <p class="text-body-sm text-slate-400 mt-1">Upload export Batch Edit TikTok Seller Center atau template Update Massal Shopee, edit massal, download format yang sama untuk di-upload ulang.</p>
        </div>
        <div class="flex items-center gap-2">
            <label class="primary-cta-button primary-cta-button--secondary cursor-pointer" :class="{ 'opacity-60 pointer-events-none': ttLoading }">
                <i :class="['fa-solid', ttLoading ? 'fa-spinner fa-spin' : 'fa-file-arrow-up']"></i>
                <span>{{ ttFile ? 'Ganti File' : 'Upload File' }}</span>
                <input type="file" accept=".xlsx" class="hidden" @change="ttLoadFile" />
            </label>
            <button v-if="ttFile" @click="ttExport" :disabled="ttExporting || !ttRows.length"
                class="primary-cta-button primary-cta-button--accent disabled:opacity-60">
                <i :class="['fa-solid', ttExporting ? 'fa-spinner fa-spin' : 'fa-file-arrow-down']"></i>
                <span>Download XLSX</span>
            </button>
        </div>
    </div>

    <div v-if="ttError" class="section-card p-3 text-body-sm text-red-600 border border-red-100 bg-red-50 flex items-center gap-2">
        <i class="fa-solid fa-circle-exclamation"></i><span>{{ ttError }}</span>
    </div>

    <div v-if="ttFile && ttStockInfo" class="section-card p-3 text-body-sm border border-emerald-100 bg-emerald-50 text-emerald-700 space-y-2">
        <div class="flex items-center justify-between gap-2 flex-wrap">
            <div class="flex items-center gap-2"><i class="fa-solid fa-boxes-stacked"></i>
                <span>Dari db_analis: stok terisi <b>{{ ttStockInfo.filled }}</b> SKU, harga online terisi <b>{{ ttStockInfo.priced }}</b> SKU<template v-if="ttStockInfo.unmatched.length">, <b>{{ ttStockInfo.unmatched.length }}</b> SKU tidak cocok (nilai file tidak diubah)</template>.</span>
            </div>
            <button @click="ttDownloadAudit" class="table-action-button" title="Download audit CSV"><i class="fa-solid fa-file-arrow-down"></i></button>
        </div>
        <details class="text-overline text-emerald-800/80">
            <summary class="cursor-pointer">Audit stok &amp; harga per SKU ({{ ttStockInfo.audit.length }})</summary>
            <div class="mt-1 max-h-72 overflow-auto bg-white/70 rounded-lg">
                <table class="w-full text-left">
                    <thead class="sticky top-0 bg-emerald-100"><tr class="table-header-row">
                        <th class="table-header-cell">Produk</th><th class="table-header-cell">Variasi</th>
                        <th class="table-header-cell text-right">Stok file</th><th class="table-header-cell text-right">Stok DB</th>
                        <th class="table-header-cell text-right">Harga file</th><th class="table-header-cell text-right">Harga DB</th>
                        <th class="table-header-cell">Sumber / catatan</th>
                    </tr></thead>
                    <tbody>
                        <tr v-for="(e, i) in ttStockInfo.audit" :key="i" class="border-t border-emerald-100" :class="e.stockDb === null ? 'text-slate-400' : ''">
                            <td class="px-2 py-1">{{ e.product }}</td><td class="px-2 py-1">{{ e.variation }}</td>
                            <td class="px-2 py-1 text-right">{{ e.stockFile }}</td><td class="px-2 py-1 text-right font-semibold">{{ e.stockDb ?? '-' }}</td>
                            <td class="px-2 py-1 text-right">{{ ttFormatPrice(e.priceFile) }}</td>
                            <td class="px-2 py-1 text-right font-semibold" :class="e.priceDb && String(e.priceDb) !== String(e.priceFile) ? 'text-amber-600' : ''">{{ e.priceDb ? ttFormatPrice(e.priceDb) : '-' }}</td>
                            <td class="px-2 py-1">{{ [e.source, e.note].filter(Boolean).join(' · ') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </details>
    </div>

    <div v-if="!ttFile" class="section-card flex flex-col items-center justify-center text-center p-10 text-slate-400 space-y-3">
        <i class="fa-solid fa-file-excel text-4xl text-slate-200"></i>
        <div>
            <p class="font-semibold text-slate-600">Belum ada file</p>
            <p class="text-body-sm">Di Seller Center: Produk &rarr; Edit massal &rarr; Semua informasi &rarr; download template, lalu upload di sini (.xlsx, maks 15MB).</p>
        </div>
    </div>

    <template v-else>
        <datalist id="tt-list-status"><option v-for="option in ttStatusOptions" :key="option" :value="option"></option></datalist>
        <datalist v-for="(brands, category, i) in ttBrands" :key="category" :id="'tt-list-brand-' + i"><option v-for="brand in brands" :key="brand" :value="brand"></option></datalist>
        <datalist id="tt-list-columns"><option v-for="col in ttEditableColumns" :key="col.key" :value="col.label"></option></datalist>
        <div class="section-card p-4 space-y-4">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-body-sm font-bold text-slate-800 truncate"><i class="fa-solid fa-file-excel text-emerald-500 mr-1.5"></i>{{ ttFileName }}</div>
                    <div class="text-overline text-slate-400 mt-0.5">
                        {{ ttRows.length }} SKU · {{ ttChangedCount }} berubah ·
                        <span :class="ttIssueCount ? 'text-red-500 font-bold' : ''">{{ ttIssueCount }} sel bermasalah</span>
                        · kapasitas template {{ ttMaxRows }} baris
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <input v-model="ttSearch" type="search" placeholder="Cari nama / SKU / ID..." class="form-input-search w-full sm:w-64" />
                    <label class="inline-flex items-center gap-1.5 text-body-sm text-slate-500 cursor-pointer"><input type="checkbox" v-model="ttOnlyChanged" /> Berubah saja</label>
                    <label class="inline-flex items-center gap-1.5 text-body-sm text-slate-500 cursor-pointer"><input type="checkbox" v-model="ttShowAllColumns" /> Semua kolom</label>
                    <button @click="ttRevertAll" :disabled="!ttChangedCount" class="table-action-button disabled:opacity-40" title="Kembalikan semua perubahan"><i class="fa-solid fa-rotate-left"></i></button>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-3 space-y-2">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <div class="text-body-sm font-semibold text-slate-700">Edit massal
                        <span class="font-normal text-slate-400">&rarr; {{ ttSelectedCount ? ttSelectedCount + ' baris dipilih' : 'semua ' + ttFilteredRows.length + ' baris hasil filter' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="ttSelectFiltered" class="table-action-button text-body-sm px-2">Pilih semua hasil filter</button>
                        <button v-if="ttSelectedCount" @click="ttClearSelection" class="table-action-button text-body-sm px-2">Batal pilih</button>
                        <button v-if="ttSelectedCount" @click="ttRemoveSelected" class="table-action-button table-action-danger text-body-sm px-2"><i class="fa-solid fa-trash-can mr-1"></i>Hapus dari output</button>
                    </div>
                </div>
                <div class="grid grid-cols-1 xl:grid-cols-[auto_14rem_1fr_auto] gap-2 items-center">
                    <div class="flex flex-wrap gap-1">
                        <button v-for="mode in ttBulkModes" :key="mode.value" @click="ttBulk.mode = mode.value"
                            :class="['table-action-button text-body-sm px-2', ttBulk.mode === mode.value ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : '']">{{ mode.label }}</button>
                    </div>
                    <input v-model="ttBulk.columnLabel" type="text" list="tt-list-columns" autocomplete="off" class="form-input" placeholder="Pilih kolom..." aria-label="Kolom target" />
                    <div class="flex gap-2">
                        <input v-if="ttBulk.mode === 'set'" v-model="ttBulk.value" type="text" class="form-input w-full" placeholder="Nilai baru (kosong = hapus isi)" />
                        <template v-else-if="ttBulk.mode === 'replace'">
                            <input v-model="ttBulk.find" type="text" class="form-input w-full" placeholder="Cari" />
                            <input v-model="ttBulk.replace" type="text" class="form-input w-full" placeholder="Ganti dengan" />
                        </template>
                        <input v-else v-model="ttBulk.amount" type="text" inputmode="decimal" class="form-input w-full" :placeholder="ttBulk.mode === 'percent' ? 'Contoh: 5 atau -3 (%)' : 'Contoh: 50000 atau -25000'" />
                    </div>
                    <button @click="ttApplyBulk" class="primary-cta-button primary-cta-button--accent"><i class="fa-solid fa-wand-magic-sparkles"></i><span>Terapkan</span></button>
                </div>
            </div>
        </div>

        <div class="section-card p-0 overflow-hidden">
            <div class="overflow-auto max-h-[calc(100dvh-26rem)] min-h-[320px]">
                <table class="w-full text-body-sm border-separate border-spacing-0">
                    <thead class="sticky top-0 z-10 bg-white">
                        <tr class="table-header-row">
                            <th class="table-header-cell w-8"><input type="checkbox" :checked="ttAllPageSelected" @change="ttTogglePage" aria-label="Pilih halaman" /></th>
                            <th v-for="col in ttVisibleColumns" :key="col.key" class="table-header-cell whitespace-nowrap" :title="col.hint">
                                {{ col.label }}<span v-if="col.requirement === 'Wajib'" class="text-red-400"> *</span><i v-if="col.readonly" class="fa-solid fa-lock ml-1 text-slate-300"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!ttPagedRows.length"><td :colspan="ttVisibleColumns.length + 1" class="p-8 text-center text-slate-400">Tidak ada baris.</td></tr>
                        <tr v-for="row in ttPagedRows" :key="row.id" :class="ttSelected[row.id] ? 'bg-sky-50/60' : 'hover:bg-slate-50'">
                            <td class="px-3 py-1.5 border-b border-slate-100"><input type="checkbox" :checked="!!ttSelected[row.id]" @change="ttToggleRow(row)" /></td>
                            <td v-for="col in ttVisibleColumns" :key="col.key" class="px-2 py-1 border-b border-slate-100 align-top">
                                <div v-if="col.readonly" class="px-1 text-slate-500 max-w-[16rem] truncate" :title="row.cells[col.index]">{{ row.cells[col.index] }}</div>
                                <input v-else-if="ttListId(row, col)" v-model="row.cells[col.index]" type="text" :list="ttListId(row, col)" autocomplete="off"
                                    :title="ttIssueFor(row, col) || row.cells[col.index]"
                                    :class="['form-input min-w-[9rem]', ttIssueFor(row, col) ? 'ring-1 ring-red-400 bg-red-50' : (ttIsChanged(row, col.index) ? 'ring-1 ring-amber-300 bg-amber-50' : '')]" />
                                <input v-else v-model="row.cells[col.index]" type="text" :title="ttIssueFor(row, col) || row.cells[col.index]"
                                    :inputmode="col.key === 'price' || col.key === 'parcel_weight' ? 'numeric' : 'text'"
                                    :class="['form-input', col.key === 'product_name' ? 'min-w-[22rem]' : 'min-w-[7rem]',
                                        ttIssueFor(row, col) ? 'ring-1 ring-red-400 bg-red-50' : (ttIsChanged(row, col.index) ? 'ring-1 ring-amber-300 bg-amber-50' : '')]" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex items-center justify-between gap-2 p-3 border-t border-slate-100 text-body-sm text-slate-500">
                <span>{{ ttFilteredRows.length }} baris · halaman {{ Math.min(ttPage, ttPageCount) }}/{{ ttPageCount }}</span>
                <div class="flex items-center gap-1.5">
                    <button @click="ttPage = Math.max(1, ttPage - 1)" :disabled="ttPage <= 1" class="table-action-button disabled:opacity-40" aria-label="Sebelumnya"><i class="fa-solid fa-chevron-left"></i></button>
                    <button @click="ttPage = Math.min(ttPageCount, ttPage + 1)" :disabled="ttPage >= ttPageCount" class="table-action-button disabled:opacity-40" aria-label="Berikutnya"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>
        </div>
    </template>
</div>
@endverbatim
