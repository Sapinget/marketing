@verbatim
                const inputClaimRows = ref([]);
                const garansiCermatiRows = ref([]);
                const garansiResmiRows = ref([]);
                const inputClaimLoaded = ref(false);
                const garansiCermatiLoaded = ref(false);
                const garansiResmiLoaded = ref(false);
                const inputClaimSearch = ref('');
                const garansiCermatiSearch = ref('');
                const garansiResmiSearch = ref('');

                const filterClaimRows = (rows, query) => {
                    const term = String(query || '').trim().toLowerCase();
                    if (!term) return rows;
                    return rows.filter(row => Object.values(row || {}).some(value => String(value ?? '').toLowerCase().includes(term)));
                };
                const hasFiveOrMoreEmptyGaransiResmiValues = row => ['Nama', 'Hp', 'Type unit', 'IMEI', 'Kerusakan', 'Status perbaikan', 'Keterangan']
                    .filter(field => ['-', ''].includes(String(row?.[field] ?? '').trim())).length >= 5;

                const filteredInputClaimRows = computed(() => filterClaimRows(inputClaimRows.value, inputClaimSearch.value));
                const filteredGaransiCermatiRows = computed(() => filterClaimRows(garansiCermatiRows.value, garansiCermatiSearch.value));
                const filteredGaransiResmiRows = computed(() => filterClaimRows(garansiResmiRows.value.filter(row => !hasFiveOrMoreEmptyGaransiResmiValues(row)), garansiResmiSearch.value));
                const inputClaimPage = ref(1);
                const garansiCermatiPage = ref(1);
                const garansiResmiPage = ref(1);
                const inputClaimTotalPages = computed(() => Math.max(1, Math.ceil(filteredInputClaimRows.value.length / PAGE_SIZE)));
                const garansiCermatiTotalPages = computed(() => Math.max(1, Math.ceil(filteredGaransiCermatiRows.value.length / PAGE_SIZE)));
                const garansiResmiTotalPages = computed(() => Math.max(1, Math.ceil(filteredGaransiResmiRows.value.length / PAGE_SIZE)));
                const pagedInputClaimRows = computed(() => filteredInputClaimRows.value.slice((inputClaimPage.value - 1) * PAGE_SIZE, inputClaimPage.value * PAGE_SIZE));
                const pagedGaransiCermatiRows = computed(() => filteredGaransiCermatiRows.value.slice((garansiCermatiPage.value - 1) * PAGE_SIZE, garansiCermatiPage.value * PAGE_SIZE));
                const pagedGaransiResmiRows = computed(() => filteredGaransiResmiRows.value.slice((garansiResmiPage.value - 1) * PAGE_SIZE, garansiResmiPage.value * PAGE_SIZE));
                watch(() => inputClaimSearch.value, () => { inputClaimPage.value = 1; });
                watch(() => garansiCermatiSearch.value, () => { garansiCermatiPage.value = 1; });
                watch(() => garansiResmiSearch.value, () => { garansiResmiPage.value = 1; });

                const loadInputClaimData = () => ensureRunApi()
                    .withSuccessHandler(data => {
                        inputClaimRows.value = Array.isArray(data) ? data : [];
                        inputClaimLoaded.value = true;
                    })
                    .withFailureHandler(() => {
                        inputClaimLoaded.value = true;
                    })
                    .getInputClaimData();

                const loadGaransiCermatiData = () => ensureRunApi()
                    .withSuccessHandler(data => {
                        garansiCermatiRows.value = Array.isArray(data) ? data : [];
                        garansiCermatiLoaded.value = true;
                    })
                    .withFailureHandler(() => {
                        garansiCermatiLoaded.value = true;
                    })
                    .getGaransiCermatiData();

                const loadGaransiResmiData = () => ensureRunApi()
                    .withSuccessHandler(data => {
                        garansiResmiRows.value = Array.isArray(data) ? data : [];
                        garansiResmiLoaded.value = true;
                    })
                    .withFailureHandler(() => {
                        garansiResmiLoaded.value = true;
                    })
                    .getGaransiResmiData();

                const _downloadCsv = (filename, headers, fields, rows) => {
                    if (!rows.length) { showNotification('Tidak ada data untuk diekspor'); return; }
                    const escape = v => '"' + String(v ?? '').replace(/"/g, '""') + '"';
                    const csv = [headers.map(escape).join(','), ...rows.map(r => fields.map(f => escape(r[f])).join(','))].join('\r\n');
                    const a = document.createElement('a');
                    a.href = URL.createObjectURL(new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8;' }));
                    a.download = filename;
                    a.click();
                    showNotification('File CSV berhasil diunduh');
                };

                const exportGaransiCermatiToExcel = () => _downloadCsv(
                    'garansi-cermati.csv',
                    ['Tanggal Masuk', 'Type Unit', 'Nama Customer', 'No Telp', 'Kendala Unit', 'Proses Klaim', 'Status Perbaikan'],
                    ['Tanggal masuk', 'Type unit', 'Nama customer', 'Nomor hp', 'Kendala unit', 'Proses claim', 'Status perbaikan'],
                    filteredGaransiCermatiRows.value
                );

                const exportGaransiResmiToExcel = () => _downloadCsv(
                    'garansi-resmi.csv',
                    ['Nama', 'HP', 'Type Unit', 'IMEI', 'Kerusakan', 'Status Perbaikan', 'Keterangan'],
                    ['Nama', 'Hp', 'Type unit', 'IMEI', 'Kerusakan', 'Status perbaikan', 'Keterangan'],
                    filteredGaransiResmiRows.value
                );

                const exportInputClaimToExcel = () => _downloadCsv(
                    'input-claim.csv',
                    ['Toko', 'Tanggal Masuk', 'Nama Customer', 'Nomor HP', 'No Invoice', 'Keterangan', 'Keterangan Complain', 'Status'],
                    ['Toko', 'Tanggal masuk', 'Nama Customer', 'Nomor Hp customer', 'No Invoice', 'Keterangan', 'Keterangan complain', 'Status'],
                    filteredInputClaimRows.value
                );

                const claimSheetSyncing = ref(false);
                const syncClaimSheets = async () => {
                    if (claimSheetSyncing.value) return;
                    claimSheetSyncing.value = true;
                    showNotification('Menyinkronkan data dari Google Sheets...');
                    try {
                        const res = await jsonApi('/api/google-sheet-claim/sync', { method: 'POST' });
                        if (!res.ok) throw new Error(res.message || 'Gagal');
                        inputClaimRows.value = [];
                        garansiCermatiRows.value = [];
                        garansiResmiRows.value = [];
                        inputClaimLoaded.value = false;
                        garansiCermatiLoaded.value = false;
                        garansiResmiLoaded.value = false;
                        const tab = activeTab.value;
                        if (tab === 'input_claim') loadInputClaimData();
                        else if (tab === 'garansi_cermati') loadGaransiCermatiData();
                        else if (tab === 'garansi_resmi') loadGaransiResmiData();
                        showNotification('Sinkronisasi berhasil. Data diperbarui.');
                    } catch (err) {
                        notifyError('Sinkronisasi gagal', err, 'Tidak dapat terhubung ke Google Sheets.');
                    } finally {
                        claimSheetSyncing.value = false;
                    }
                };
@endverbatim
