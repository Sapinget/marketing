@verbatim
                // ── TikTok Batch Edit Template ───────────────────────────────────────────
                const TT_DEFAULT_COLUMNS = ['product_name', 'product_status', 'variation_value', 'brand', 'price', 'seller_sku', 'quantity', 'parcel_weight', 'main_image',
                    'et_title_product_name', 'et_title_variation_name', 'et_title_parent_sku', 'et_title_variation_sku', 'et_title_variation_price', 'et_title_variation_stock'];
                const TT_PAGE_SIZE = 50;

                const ttFile        = ref(null);
                const ttFileName    = ref('');
                const ttColumns     = ref([]);
                const ttRows        = ref([]);
                const ttBrands      = ref({});
                const ttStatusOptions = ref([]);
                const ttMaxRows     = ref(0);
                const ttLoading     = ref(false);
                const ttExporting   = ref(false);
                const ttError       = ref('');
                const ttStockInfo   = ref(null);
                const ttFields      = ref({ name: 'product_name', variation: 'variation_value', stock: 'quantity', price: 'price' });
                const ttSearch      = ref('');
                const ttOnlyChanged = ref(false);
                const ttShowAllColumns = ref(false);
                const ttPage        = ref(1);
                const ttSelected    = ref({});
                const ttBulk        = ref({ mode: 'set', columnLabel: 'Harga Ritel (Mata Uang Lokal)', value: '', find: '', replace: '', amount: '' });
                const ttBulkModes   = [
                    { value: 'set', label: 'Isi nilai' },
                    { value: 'replace', label: 'Cari & ganti' },
                    { value: 'percent', label: 'Naik/turun %' },
                    { value: 'add', label: 'Tambah/kurang' },
                ];

                const ttKeyIndex = computed(() => {
                    const map = {};
                    ttColumns.value.forEach(col => { map[col.key] = col.index; });
                    return map;
                });
                const ttVisibleColumns = computed(() => ttShowAllColumns.value
                    ? ttColumns.value
                    : ttColumns.value.filter(col => TT_DEFAULT_COLUMNS.includes(col.key)));
                const ttEditableColumns = computed(() => ttColumns.value.filter(col => !col.readonly));
                const ttCell = (row, key) => row.cells[ttKeyIndex.value[key]] ?? '';
                const ttIsChanged = (row, colIndex) => row.cells[colIndex] !== row.orig[colIndex];
                const ttRowChanged = (row) => row.cells.some((value, i) => value !== row.orig[i]);

                const ttFilteredRows = computed(() => {
                    const q = ttSearch.value.trim().toLowerCase();
                    return ttRows.value.filter(row => {
                        if (ttOnlyChanged.value && !ttRowChanged(row)) return false;
                        if (!q) return true;
                        return ['product_name', 'variation_value', 'seller_sku', 'product_id', 'sku_id', 'et_title_product_name', 'et_title_variation_name', 'et_title_variation_sku', 'et_title_product_id', 'et_title_variation_id']
                            .some(key => String(ttCell(row, key)).toLowerCase().includes(q));
                    });
                });
                const ttPageCount = computed(() => Math.max(1, Math.ceil(ttFilteredRows.value.length / TT_PAGE_SIZE)));
                const ttPagedRows = computed(() => {
                    const page = Math.min(ttPage.value, ttPageCount.value);
                    return ttFilteredRows.value.slice((page - 1) * TT_PAGE_SIZE, page * TT_PAGE_SIZE);
                });
                const ttChangedCount = computed(() => ttRows.value.filter(ttRowChanged).length);
                const ttSelectedCount = computed(() => ttRows.value.filter(row => ttSelected.value[row.id]).length);
                const ttBulkTargets = computed(() => {
                    const selected = ttRows.value.filter(row => ttSelected.value[row.id]);
                    return selected.length ? selected : ttFilteredRows.value;
                });
                const ttAllPageSelected = computed(() => ttPagedRows.value.length > 0 && ttPagedRows.value.every(row => ttSelected.value[row.id]));

                watch([ttSearch, ttOnlyChanged], () => { ttPage.value = 1; });

                const ttCsrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
                const ttErrorMessage = async (res) => {
                    try {
                        const json = await res.json();
                        return json.message || ('HTTP ' + res.status);
                    } catch (e) {
                        return 'HTTP ' + res.status;
                    }
                };

                const ttOptionsFor = (row, col) => {
                    if (col.key === 'product_status') return ttStatusOptions.value;
                    if (col.key === 'brand') return ttBrands.value[ttCell(row, 'category')] || null;
                    return null;
                };
                const ttListId = (row, col) => {
                    if (col.key === 'product_status') return 'tt-list-status';
                    if (col.key === 'brand') {
                        const i = Object.keys(ttBrands.value).indexOf(ttCell(row, 'category'));
                        return i >= 0 ? 'tt-list-brand-' + i : '';
                    }
                    return '';
                };

                // Validation mirrors the template's own dataValidation rules.
                const ttCellIssue = (row, col) => {
                    const value = String(row.cells[col.index] ?? '').trim();
                    if (col.key === 'product_name' && (value.length < 25 || value.length > 255)) return 'Nama produk harus 25–255 karakter';
                    if (col.key === 'price' && (value === '' || !/^\d+(\.\d+)?$/.test(value) || Number(value) <= 0)) return 'Harga harus angka > 0, tanpa simbol mata uang';
                    if (col.key === 'parcel_weight' && (value === '' || !/^\d+(\.\d+)?$/.test(value) || Number(value) <= 0)) return 'Berat paket harus angka > 0';
                    if (col.key === 'product_status' && !ttStatusOptions.value.includes(value)) return 'Pilih Aktif(1) atau Dinonaktifkan(2)';
                    if (col.key === 'brand' && value !== '' && ttBrands.value[ttCell(row, 'category')] && !ttBrands.value[ttCell(row, 'category')].includes(value)) return 'Merek tidak ada di daftar kategori ini';
                    if (col.key === 'product_description' && value === '') return 'Deskripsi wajib diisi';
                    if (col.key === 'main_image' && value === '') return 'Gambar utama wajib diisi';
                    return '';
                };
                const ttIssues = computed(() => {
                    const issues = [];
                    ttRows.value.forEach(row => {
                        ttColumns.value.forEach(col => {
                            if (col.readonly) return;
                            const message = ttCellIssue(row, col);
                            if (message) issues.push({ rowId: row.id, key: col.key, label: col.label, message });
                        });
                    });
                    return issues;
                });
                const ttIssueCount = computed(() => ttIssues.value.length);
                const ttRowIssueKeys = computed(() => {
                    const map = {};
                    ttIssues.value.forEach(issue => { (map[issue.rowId] ||= {})[issue.key] = issue.message; });
                    return map;
                });
                const ttIssueFor = (row, col) => ttRowIssueKeys.value[row.id]?.[col.key] || '';

                async function ttLoadFile(event) {
                    const file = event?.target?.files?.[0];
                    if (!file) return;
                    ttLoading.value = true;
                    ttError.value = '';
                    try {
                        const fd = new FormData();
                        fd.append('file', file);
                        const res = await fetch(window.MarketingDashboardRuntimeHelpers.resolveAppUrl('/api/tiktok-template/parse'), {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': ttCsrf(), 'Accept': 'application/json' },
                            body: fd,
                        });
                        if (!res.ok) throw new Error(await ttErrorMessage(res));
                        const json = await res.json();
                        ttFile.value = file;
                        ttFileName.value = file.name;
                        ttColumns.value = json.columns || [];
                        ttBrands.value = json.brands || {};
                        ttStatusOptions.value = json.statusOptions || [];
                        ttMaxRows.value = json.maxRows || 0;
                        ttFields.value = json.fields || ttFields.value;
                        ttBulk.value.columnLabel = json.platform === 'shopee' ? 'Stok' : 'Harga Ritel (Mata Uang Lokal)';
                        ttRows.value = (json.rows || []).map((cells, i) => ({ id: i, cells: [...cells], orig: [...cells] }));
                        ttSelected.value = {};
                        ttSearch.value = '';
                        ttOnlyChanged.value = false;
                        ttPage.value = 1;
                        ttApplyStock(json.stock);
                    } catch (e) {
                        ttError.value = e.message || 'Gagal membaca file';
                    } finally {
                        ttLoading.value = false;
                        if (event?.target) event.target.value = '';
                    }
                }

                // Fills stock + online price from db_analis; rows without a match keep the file's values.
                // Every row lands in the audit list so the numbers can be checked against the database.
                function ttApplyStock(stock) {
                    ttStockInfo.value = null;
                    const qtyIndex = ttKeyIndex.value[ttFields.value.stock];
                    const priceIndex = ttKeyIndex.value[ttFields.value.price];
                    if (!Array.isArray(stock) || qtyIndex === undefined) return;
                    let filled = 0;
                    let priced = 0;
                    const unmatched = [];
                    const audit = [];
                    ttRows.value.forEach((row, i) => {
                        const hit = stock[i] || {};
                        const entry = {
                            product: String(ttCell(row, ttFields.value.name)).replace(/^\[[^\]]*\]\s*/, '').replace(/\s+GARANSI RESMI.*$/i, ''),
                            variation: ttCell(row, ttFields.value.variation),
                            stockFile: row.orig[qtyIndex],
                            stockDb: null,
                            priceFile: priceIndex === undefined ? '' : row.orig[priceIndex],
                            priceDb: null,
                            source: '',
                            note: hit.reason || '',
                        };
                        if (hit.stock !== null && hit.stock !== undefined) {
                            row.cells[qtyIndex] = String(hit.stock);
                            entry.stockDb = hit.stock;
                            filled++;
                            if (priceIndex !== undefined && hit.price) {
                                row.cells[priceIndex] = String(hit.price);
                                entry.priceDb = hit.price;
                                entry.source = hit.price_source === 'stok' ? 'unit ada stok' : 'unit terbaru (stok kosong)';
                                if ((hit.prices || []).length > 1) entry.note = 'harga unit beda-beda: ' + hit.prices.join(' / ');
                                priced++;
                            } else {
                                entry.note = 'harga online DB kosong, harga file dipertahankan';
                            }
                        } else {
                            unmatched.push(entry.variation + ' · ' + entry.product);
                        }
                        audit.push(entry);
                    });
                    ttStockInfo.value = { filled, priced, unmatched, audit };
                }

                function ttDownloadAudit() {
                    const info = ttStockInfo.value;
                    if (!info) return;
                    const head = ['Produk', 'Variasi', 'Stok file', 'Stok DB', 'Harga file', 'Harga DB', 'Sumber harga', 'Catatan'];
                    const esc = (v) => '"' + String(v ?? '').replace(/"/g, '""') + '"';
                    const lines = [head.map(esc).join(',')].concat(info.audit.map(e =>
                        [e.product, e.variation, e.stockFile, e.stockDb, e.priceFile, e.priceDb, e.source, e.note].map(esc).join(',')));
                    const blob = new Blob(['\ufeff' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8' });
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = ttFileName.value.replace(/\.xlsx$/i, '') + '_audit.csv';
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    setTimeout(() => URL.revokeObjectURL(link.href), 10000);
                }

                function ttResetFile() {
                    ttFile.value = null;
                    ttFileName.value = '';
                    ttColumns.value = [];
                    ttRows.value = [];
                    ttSelected.value = {};
                    ttStockInfo.value = null;
                    ttError.value = '';
                }

                function ttRevertAll() {
                    if (!ttChangedCount.value) return;
                    if (!confirm('Kembalikan semua perubahan ke data file asli?')) return;
                    ttRows.value.forEach(row => { row.cells = [...row.orig]; });
                }

                function ttToggleRow(row) {
                    ttSelected.value = { ...ttSelected.value, [row.id]: !ttSelected.value[row.id] };
                }
                function ttTogglePage() {
                    const next = { ...ttSelected.value };
                    const value = !ttAllPageSelected.value;
                    ttPagedRows.value.forEach(row => { next[row.id] = value; });
                    ttSelected.value = next;
                }
                function ttSelectFiltered() {
                    const next = { ...ttSelected.value };
                    ttFilteredRows.value.forEach(row => { next[row.id] = true; });
                    ttSelected.value = next;
                }
                function ttClearSelection() { ttSelected.value = {}; }

                function ttRemoveSelected() {
                    const ids = ttRows.value.filter(row => ttSelected.value[row.id]).map(row => row.id);
                    if (!ids.length) return;
                    if (!confirm(ids.length + ' baris dihapus dari output? (file asli tidak berubah, hanya hasil download)')) return;
                    ttRows.value = ttRows.value.filter(row => !ids.includes(row.id));
                    ttSelected.value = {};
                }

                function ttApplyBulk() {
                    const bulk = ttBulk.value;
                    const column = ttEditableColumns.value.find(col => col.label === bulk.columnLabel.trim());
                    const targets = ttBulkTargets.value;
                    if (!column) { alert('Pilih kolom target dari daftar.'); return; }
                    const index = column.index;
                    if (!targets.length) return;

                    if (bulk.mode === 'set') {
                        targets.forEach(row => { row.cells[index] = bulk.value; });
                    } else if (bulk.mode === 'replace') {
                        if (bulk.find === '') return;
                        targets.forEach(row => { row.cells[index] = String(row.cells[index]).split(bulk.find).join(bulk.replace); });
                    } else if (bulk.mode === 'percent' || bulk.mode === 'add') {
                        const amount = Number(String(bulk.amount).replace(',', '.'));
                        if (!Number.isFinite(amount)) return;
                        targets.forEach(row => {
                            const current = Number(row.cells[index]);
                            if (!Number.isFinite(current) || row.cells[index] === '') return;
                            const next = bulk.mode === 'percent' ? current * (1 + amount / 100) : current + amount;
                            row.cells[index] = String(Math.max(0, Math.round(next)));
                        });
                    }
                }

                async function ttExport() {
                    if (!ttFile.value || !ttRows.value.length) return;
                    const blocking = ttIssues.value.filter(issue => issue.key === 'product_name' || issue.key === 'price');
                    if (blocking.length) {
                        alert('Ada ' + blocking.length + ' sel Nama/Harga yang tidak valid. Perbaiki dulu (sel merah).');
                        return;
                    }
                    if (ttIssueCount.value && !confirm(ttIssueCount.value + ' sel masih bermasalah (ditandai merah). Tetap download?')) return;

                    ttExporting.value = true;
                    ttError.value = '';
                    try {
                        const fd = new FormData();
                        fd.append('file', ttFile.value);
                        fd.append('rows', JSON.stringify(ttRows.value.map(row => row.cells)));
                        const res = await fetch(window.MarketingDashboardRuntimeHelpers.resolveAppUrl('/api/tiktok-template/export'), {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': ttCsrf(), 'Accept': 'application/json' },
                            body: fd,
                        });
                        if (!res.ok) throw new Error(await ttErrorMessage(res));
                        const blob = await res.blob();
                        const link = document.createElement('a');
                        link.href = URL.createObjectURL(blob);
                        link.download = ttFileName.value.replace(/\.xlsx$/i, '') + '_edited.xlsx';
                        document.body.appendChild(link);
                        link.click();
                        link.remove();
                        setTimeout(() => URL.revokeObjectURL(link.href), 10000);
                    } catch (e) {
                        ttError.value = e.message || 'Gagal membuat file';
                    } finally {
                        ttExporting.value = false;
                    }
                }

                const ttFormatPrice = (value) => {
                    const n = Number(value);
                    return Number.isFinite(n) && value !== '' ? n.toLocaleString('id-ID') : value;
                };

                Object.assign(menuExports, {
                    ttFile,
                    ttFileName,
                    ttColumns,
                    ttRows,
                    ttBrands,
                    ttStatusOptions,
                    ttMaxRows,
                    ttLoading,
                    ttExporting,
                    ttError,
                    ttStockInfo,
                    ttDownloadAudit,
                    ttSearch,
                    ttOnlyChanged,
                    ttShowAllColumns,
                    ttPage,
                    ttSelected,
                    ttBulk,
                    ttBulkModes,
                    ttListId,
                    ttVisibleColumns,
                    ttEditableColumns,
                    ttCell,
                    ttIsChanged,
                    ttRowChanged,
                    ttFilteredRows,
                    ttPageCount,
                    ttPagedRows,
                    ttChangedCount,
                    ttSelectedCount,
                    ttBulkTargets,
                    ttAllPageSelected,
                    ttOptionsFor,
                    ttCellIssue,
                    ttIssueCount,
                    ttIssueFor,
                    ttLoadFile,
                    ttResetFile,
                    ttRevertAll,
                    ttToggleRow,
                    ttTogglePage,
                    ttSelectFiltered,
                    ttClearSelection,
                    ttRemoveSelected,
                    ttApplyBulk,
                    ttExport,
                    ttFormatPrice,
                });
@endverbatim
