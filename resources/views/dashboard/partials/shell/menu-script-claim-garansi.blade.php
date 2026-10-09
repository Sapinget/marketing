@verbatim
                const claimTotalPages = computed(() => Math.max(1, Math.ceil(filteredClaimGaransiData.value.length / PAGE_SIZE)));

                const pagedClaimGaransiData = computed(() => filteredClaimGaransiData.value.slice((claimPage.value - 1) * PAGE_SIZE, claimPage.value * PAGE_SIZE));

                const claimSeriOptions = computed(() => mergeOptionValues(
                    settings.value.Seri_Produk || [],
                    uniqueMultiKeyFrom(claimGaransiData.value, ['SERI']),
                    uniqueMultiKeyFrom(unitDitanyaData.value, ['SERI']),
                    uniqueMultiKeyFrom(namaStockRows.value, ['SERI'])
                ));

                const claimStatusOptions = computed(() => uniqueFrom(
                    claimGaransiData.value,
                    'STATUS',
                    settings.value.Claim_Garansi_Status || settings.value.Status_Klaim || []
                ));

                const claimSummary = computed(() => {
                    const d = filteredClaimGaransiData.value || [];
                    const st = _sCnt(d, r => r.STATUS);
                    const done = Object.entries(st).filter(([k]) => ['SELESAI', 'CLAIM', 'DONE', 'PROCESED', 'PROCESSED'].includes(k.toUpperCase())).reduce((s, [, n]) => s + n, 0);
                    const pending = (st['NOT STARTED'] || 0) + (st['PENDING'] || 0);
                    const gar = _sCnt(d, r => r.GARANSI);
                    return { cards: limitSummaryCards([
                        { label: 'Total Klaim', value: formatNumber(d.length), unit: 'Unit', icon: 'fa-shield-halved', color: 'text-amber', unitColor: 'text-amber', subColor: 'text-amber', sub: 'Semua klaim' },
                        { label: 'Selesai', value: formatNumber(done), icon: 'fa-circle-check', color: 'text-success', subColor: 'text-success', sub: 'Klaim beres' },
                        { label: 'Pending', value: formatNumber(pending), icon: 'fa-hourglass-half', color: 'text-amber', subColor: 'text-amber', sub: 'Belum diproses' },
                        { label: 'Jenis Garansi', value: formatNumber(Object.keys(gar).length), unit: 'Tipe', icon: 'fa-file-shield', color: 'text-slate-500', unitColor: 'text-slate-400', subColor: 'text-slate-600', sub: 'Variasi garansi' },
                    ]) };
                });

                Object.assign(menuExports, {
                    claimTotalPages,
                    pagedClaimGaransiData,
                    claimSeriOptions,
                    claimStatusOptions,
                    claimSummary,
                });
@endverbatim
