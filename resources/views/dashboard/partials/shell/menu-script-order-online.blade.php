@verbatim
                const orderanTotalPages = computed(() => Math.max(1, Math.ceil(filteredOrderanOnlineData.value.length / PAGE_SIZE)));

                const pagedOrderanOnlineData = computed(() => filteredOrderanOnlineData.value.slice((orderanPage.value - 1) * PAGE_SIZE, orderanPage.value * PAGE_SIZE));

                const orderanEcommerceOptions = computed(() => uniqueFrom(orderanOnlineData.value, 'ECOMMERCE', settings.value.Orderan_Online_Ecommerce || []));

                const orderanPengirimanOptions = computed(() => uniqueFrom(orderanOnlineData.value, 'PENGIRIMAN', settings.value.Orderan_Online_Pengiriman || []));

                const orderanStatusOptions = computed(() => uniqueFrom(orderanOnlineData.value, 'STATUS', settings.value.Orderan_Online_Status || []));

                const orderanHandleOptions = computed(() => uniqueMultiKeyFrom(
                    orderanOnlineData.value,
                    ['HANDLE'],
                    settings.value.Orderan_Online_Handle || []
                ));

                const orderanSummary = computed(() => {
                    const d = filteredOrderanOnlineData.value || [];
                    const st = _sCnt(d, r => r.STATUS);
                    const cair = _sSum(d, r => (r['NOMINAL CAIR'] != null ? r['NOMINAL CAIR'] : r.HARGA_ONLINE));
                    const done = Object.entries(st).filter(([k]) => ['SELESAI', 'DONE', 'CAIR', 'LUNAS'].includes(k.toUpperCase())).reduce((s, [, n]) => s + n, 0);
                    const kirim = _sCnt(d, r => r.PENGIRIMAN);
                    return { cards: limitSummaryCards([
                        { label: 'Total Orderan', value: formatNumber(d.length), unit: 'Order', icon: 'fa-cart-shopping', color: 'text-amber', unitColor: 'text-amber', subColor: 'text-amber', sub: 'Semua orderan' },
                        { label: 'Total Cair', value: formatCurrency(cair), icon: 'fa-money-bill-trend-up', color: 'text-success', subColor: 'text-success', sub: 'Nominal masuk' },
                        { label: 'Selesai', value: formatNumber(done), icon: 'fa-circle-check', color: 'text-slate-500', subColor: 'text-slate-600', sub: 'Order tuntas' },
                        { label: 'Ekspedisi', value: formatNumber(Object.keys(kirim).length), unit: 'Jasa', icon: 'fa-truck', color: 'text-amber', unitColor: 'text-amber', subColor: 'text-amber', sub: 'Pengiriman' },
                    ]) };
                });

                Object.assign(menuExports, {
                    orderanTotalPages,
                    pagedOrderanOnlineData,
                    orderanEcommerceOptions,
                    orderanPengirimanOptions,
                    orderanStatusOptions,
                    orderanHandleOptions,
                    orderanSummary,
                });
@endverbatim
