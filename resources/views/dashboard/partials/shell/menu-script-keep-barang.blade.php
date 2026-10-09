@verbatim
                const keepBarangModalType = ref('create');

                const openKeepBarangModal = (type, row = null) => {
                    keepBarangModalType.value = type;
                    keepBarangForm.value = row ? { ...row, TYPE_HP: normalizeKeepBarangTypeHpValue(row.TYPE_HP) } : { TANGGAL_KEEP: todayStr(), RENCANA_PENGAMBILAN: '', NAMA: '', NOMOR_HP: '', NOMOR_HP_2: '', TYPE_HP: '', IMEI_FULL: '', DP_UANG_MUKA: 0, HARGA_JUAL: 0, HANDLE_BY: '', KASIR_BY: '', TEAM_GUDANG: '', DEADLINE_TEAM_GUDANG: '', STATUS: keepBarangStatusOptions.value[0] || '' };
                    keepBarangModalOpen.value = true;
                    if (!namaStockLoaded.value) loadNamaStockData();
                };

                const keepBarangUniqueStatus = computed(() => [...new Set((keepBarangData.value || []).map(r => r.STATUS).filter(Boolean))].sort());

                const keepBarangUniqueHandleBy = computed(() => [...new Set((keepBarangData.value || []).map(r => r.HANDLE_BY).filter(Boolean))].sort());

                const keepBarangSummary = computed(() => {
                    const rows = keepBarangData.value || [];
                    return { total: rows.length, pending: rows.filter(r => r.STATUS === 'PENDING').length, done: rows.filter(r => r.STATUS === 'DONE').length, cancel: rows.filter(r => r.STATUS === 'CANCEL').length };
                });

                const keepBarangTotalPages = computed(() => Math.max(1, Math.ceil(filteredKeepBarangData.value.length / PAGE_SIZE)));

                const pagedKeepBarangData = computed(() => filteredKeepBarangData.value.slice((keepBarangPage.value - 1) * PAGE_SIZE, keepBarangPage.value * PAGE_SIZE));

                const keepBarangStatusClass = (status) => {
                    if (status === 'PENDING') return 'bg-amber text-light';
                    if (status === 'DONE') return 'bg-success text-light';
                    if (status === 'CANCEL') return 'bg-danger text-light';
                    return 'bg-secondary text-light';
                };

                const keepBarangSisaHariClass = (val) => {
                    if (!val || val === '-') return 'text-slate-400';
                    const n = parseInt(val);
                    if (isNaN(n)) return 'text-slate-500';
                    if (n <= 0) return 'text-danger font-bold';
                    if (n <= 3) return 'text-amber font-bold';
                    return 'text-slate-600';
                };

                const keepBarangTypeHpOptions = computed(() => {
                    const masterTypeOptions = namaStockRows.value
                        .map(row => buildStockNameLabel(row))
                        .filter(Boolean);
                    if (masterTypeOptions.length > 0) return mergeOptionValues(masterTypeOptions);
                    return mergeOptionValues(
                        uniqueMultiKeyFrom(keepBarangData.value, ['TYPE_HP'])
                    );
                });

                const keepBarangHandleByOptions = computed(() => resolveNamaStockSettingOptions(
                    ['Keep_Barang_Handle_By', 'Handle_By_Keep_Barang', 'Handle_By'],
                    mergeOptionValues(
                        uniqueMultiKeyFrom(keepBarangData.value, ['HANDLE_BY']),
                        uniqueMultiKeyFrom(orderanOnlineData.value, ['HANDLE'])
                    )
                ));

                const keepBarangKasirByOptions = computed(() => uniqueMultiKeyFrom(keepBarangData.value, ['KASIR_BY']));

                const keepBarangTeamGudangOptions = computed(() => uniqueMultiKeyFrom(keepBarangData.value, ['TEAM_GUDANG']));

                const keepBarangStatusOptions = computed(() => uniqueFrom(
                    keepBarangData.value,
                    'STATUS',
                    settings.value.Keep_Barang_Status || ['PENDING', 'DONE', 'CANCEL']
                ));

                Object.assign(menuExports, {
                    keepBarangModalType,
                    openKeepBarangModal,
                    keepBarangUniqueStatus,
                    keepBarangUniqueHandleBy,
                    keepBarangSummary,
                    keepBarangTotalPages,
                    pagedKeepBarangData,
                    keepBarangStatusClass,
                    keepBarangSisaHariClass,
                    keepBarangTypeHpOptions,
                    keepBarangHandleByOptions,
                    keepBarangKasirByOptions,
                    keepBarangTeamGudangOptions,
                    keepBarangStatusOptions,
                });
@endverbatim
