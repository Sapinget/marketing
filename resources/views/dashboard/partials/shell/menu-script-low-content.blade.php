@verbatim
                const lowContentTabs = computed(() => [
                    { key: 'all', label: 'Gabungan', count: lowContentCombined.value.length },
                    ...lowContentByPlatform.value.map(group => ({
                        key: group.platform,
                        label: platformDisplayName(group.platform),
                        count: group.rows.length
                    }))
                ]);

                const lowContentActiveGroup = computed(() =>
                    lowContentByPlatform.value.find(group => group.platform === lowContentView.value) || null
                );

                const lowContentActiveRows = computed(() =>
                    lowContentView.value === 'all'
                        ? lowContentCombined.value
                        : (lowContentActiveGroup.value?.rows || [])
                );

                Object.assign(menuExports, {
                    lowContentTabs,
                    lowContentActiveGroup,
                    lowContentActiveRows,
                });
@endverbatim
