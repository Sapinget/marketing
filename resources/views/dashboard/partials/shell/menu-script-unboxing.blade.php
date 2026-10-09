@verbatim
                const unboxingTotalPages = computed(() => Math.max(1, Math.ceil(filteredUnboxingData.value.length / PAGE_SIZE)));

                const pagedUnboxingData = computed(() => filteredUnboxingData.value.slice((unboxingPage.value - 1) * PAGE_SIZE, unboxingPage.value * PAGE_SIZE));

                const unboxingStatusOptions = computed(() => mergeOptionValues(
                    settings.value.Unboxing_Status || [],
                    settings.value.Status_Unboxing || [],
                    settings.value.Status || []
                ));

                const unboxingSummary = computed(() => {
                    const d = filteredUnboxingData.value || [];
                    const st = _sCnt(d, r => r.Status);
                    const done = Object.entries(st).filter(([k]) => ['DONE', 'SELESAI', 'PUBLISHED', 'UPLOAD', 'UPLOADED'].includes(k.toUpperCase())).reduce((s, [, n]) => s + n, 0);
                    const editors = _sCnt(d, r => r.Editor);
                    return { cards: limitSummaryCards([
                        { label: 'Total Unboxing', value: formatNumber(d.length), unit: 'Video', icon: 'fa-box-open', color: 'text-amber', unitColor: 'text-amber', subColor: 'text-amber', sub: 'Semua unboxing' },
                        { label: 'Selesai', value: formatNumber(done), icon: 'fa-circle-check', color: 'text-success', subColor: 'text-success', sub: 'Sudah upload' },
                        { label: 'Proses', value: formatNumber(d.length - done), icon: 'fa-spinner', color: 'text-amber', subColor: 'text-amber', sub: 'Belum selesai' },
                        { label: 'Editor', value: formatNumber(Object.keys(editors).length), unit: 'Orang', icon: 'fa-user-pen', color: 'text-slate-500', unitColor: 'text-slate-400', subColor: 'text-slate-600', sub: 'Terlibat' },
                    ]) };
                });

                Object.assign(menuExports, {
                    unboxingTotalPages,
                    pagedUnboxingData,
                    unboxingStatusOptions,
                    unboxingSummary,
                });
@endverbatim
