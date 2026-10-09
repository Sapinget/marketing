@verbatim
                const topContentTabs = computed(() => [
                    { key: 'all', label: 'Gabungan', count: topContentCombined.value.length },
                    ...topContentByPlatform.value.map(group => ({
                        key: group.platform,
                        label: platformDisplayName(group.platform),
                        count: group.rows.length
                    }))
                ]);

                const topContentActiveGroup = computed(() =>
                    topContentByPlatform.value.find(group => group.platform === topContentView.value) || null
                );

                const topContentActiveRows = computed(() =>
                    topContentView.value === 'all'
                        ? topContentCombined.value
                        : (topContentActiveGroup.value?.rows || [])
                );

                Object.assign(menuExports, {
                    topContentTabs,
                    topContentActiveGroup,
                    topContentActiveRows,
                });
@endverbatim
