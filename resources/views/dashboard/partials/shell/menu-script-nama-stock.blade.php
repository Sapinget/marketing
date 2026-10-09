@verbatim
                // Nama Stock Helpers & Methods (already destructured in nama-stock-actions, reuse here)
                const namaStockFilterKategoriOptions = computed(() => filteredUniqueFrom(namaStockRows.value, 'KATEGORI'));

                const namaStockFilteredRows = computed(() => {
                    const q = (namaStockSearchQuery.value || '').toLowerCase().trim();
                    return namaStockRows.value.filter(row => {
                        const matchKategori = !namaStockKategoriFilter.value || String(row.KATEGORI || '').trim().toUpperCase() === String(namaStockKategoriFilter.value || '').trim().toUpperCase();
                        const matchBrand = !namaStockBrandFilter.value || String(row.BRAND || '').trim().toUpperCase() === String(namaStockBrandFilter.value || '').trim().toUpperCase();
                        const hay = `${row.KATEGORI || ''} ${row.BRAND || ''} ${row.SERI || ''}`.toLowerCase();
                        const matchQuery = !q || hay.includes(q);
                        return matchKategori && matchBrand && matchQuery;
                    });
                });

                const namaStockTotalPages = computed(() => Math.max(1, Math.ceil(namaStockFilteredRows.value.length / PAGE_SIZE)));

                const pagedNamaStockRows = computed(() => namaStockFilteredRows.value.slice((namaStockPage.value - 1) * PAGE_SIZE, namaStockPage.value * PAGE_SIZE));

                const namaStockKategoriOptions = computed(() => resolveNamaStockSettingOptions(
                    ['Nama_Stock_Kategori', 'Kategori_Produk', 'Kategori'],
                    filteredUniqueFrom(namaStockRows.value, 'KATEGORI')
                ));

                Object.assign(menuExports, {
                    namaStockFilterKategoriOptions,
                    namaStockFilteredRows,
                    namaStockTotalPages,
                    pagedNamaStockRows,
                    namaStockKategoriOptions,
                });
@endverbatim
