@verbatim
                const unitDitanyaTotalPages = computed(() => Math.max(1, Math.ceil(filteredUnitDitanyaData.value.length / PAGE_SIZE)));

                const pagedUnitDitanyaData = computed(() => filteredUnitDitanyaData.value.slice((unitDitanyaPage.value - 1) * PAGE_SIZE, unitDitanyaPage.value * PAGE_SIZE));

                const unitBrandOptions = computed(() => getBrandOptions(unitDitanyaForm.value['KATEGORI']));

                const nsSeriOptions = computed(() => getSeriOptions(unitDitanyaForm.value['KATEGORI'], unitDitanyaForm.value['BRAND']));

                const unitAvailableOptions = computed(() => uniqueFrom(unitDitanyaData.value, 'AVAILABLE', []));

                const unitDitanyaSummary = computed(() => {
                    const d = filteredUnitDitanyaData.value || [];
                    const ditanya = _sSum(d, r => r.DITANYA);
                    const avail = d.filter(r => { const v = String(r.AVAILABLE || '').toUpperCase(); return v === 'YA' || v === 'ADA' || v === 'AVAILABLE' || v === '1' || v === 'TRUE' || Number(r.AVAILABLE) > 0; }).length;
                    const brand = _sCnt(d, r => r.BRAND);
                    const topBrand = Object.entries(brand).sort((a, b) => b[1] - a[1])[0] || null;
                    return { cards: limitSummaryCards([
                        { label: 'Total Unit', value: formatNumber(d.length), unit: 'Tipe', icon: 'fa-mobile-screen', color: 'text-amber', unitColor: 'text-amber', subColor: 'text-amber', sub: 'Data unit' },
                        { label: 'Total Ditanya', value: formatNumber(ditanya), icon: 'fa-comments', color: 'text-amber', subColor: 'text-amber', sub: 'Akumulasi' },
                        { label: 'Available', value: formatNumber(avail), icon: 'fa-circle-check', color: 'text-success', subColor: 'text-success', sub: 'Ready stock' },
                        { label: 'Brand', value: formatNumber(Object.keys(brand).length), unit: 'Merek', icon: 'fa-tags', color: 'text-slate-500', unitColor: 'text-slate-400', subColor: 'text-slate-600', sub: 'Variasi brand' },
                        { label: 'Top Brand', value: topBrand ? String(topBrand[0]).toUpperCase() : '-', icon: 'fa-mobile-screen', color: 'text-amber', sub: formatNumber(topBrand ? topBrand[1] : 0) + ' unit', subColor: 'text-amber' },
                    ]) };
                });

                Object.assign(menuExports, {
                    unitDitanyaTotalPages,
                    pagedUnitDitanyaData,
                    unitBrandOptions,
                    nsSeriOptions,
                    unitAvailableOptions,
                    unitDitanyaSummary,
                });
@endverbatim
