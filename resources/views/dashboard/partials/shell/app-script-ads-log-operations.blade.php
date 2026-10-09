@verbatim

                const adsSaldoPlatform = computed(() => {
                    const bc = budgetConfig.value;
                    const p = (adsForm.value.Platform || '').toLowerCase();
                    if (p === 'meta') return bc.meta?.balance || 0;
                    if (p === 'google') return bc.google?.balance || 0;
                    if (p === 'mekari') return (bc.mekari?.visitor?.balance || 0) + (bc.mekari?.broadcast?.balance || 0);
                    const other = (bc.others || []).find(o => (o.name || '').toLowerCase() === p);
                    return other?.balance || 0;
                });

                const adsPlatformColor = (platform) => {
                    const p = (platform || '').toLowerCase();
                    if (p === 'meta') return 'bg-amber text-light';
                    if (p === 'google') return 'bg-danger text-light';
                    if (p === 'mekari') return 'bg-info text-light';
                    return 'bg-secondary text-light';
                };

@endverbatim
