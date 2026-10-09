@verbatim
                const formatOptions = computed(() => settings.value.Format_Konten || []);

                const talentOptions = computed(() => settings.value.Talent || []);

                const filteredFormatOptions = computed(() => {
                    const q = (searchSelectQuery.value || '').toLowerCase();
                    return (formatOptions.value || []).filter((opt) => String(opt || '').toLowerCase().includes(q));
                });

                 const filteredTalentOptions = computed(() => {
                     const q = (searchSelectQuery.value || '').toLowerCase();
                     return (talentOptions.value || []).filter((opt) => String(opt || '').toLowerCase().includes(q));
                 });

                 const filteredPlatformOptions = computed(() => {
                    const q = (searchSelectQuery.value || '').toLowerCase();
                    return (platformOptions.value || []).filter((opt) => String(opt || '').toLowerCase().includes(q));
                });

                const filteredColabOptions = computed(() => {
                    const opts = settings.value.Colab || [];
                    const q = (searchSelectQuery.value || '').toLowerCase();
                    return opts.filter(opt => String(opt || '').toLowerCase().includes(q));
                });

                const toggleColab = (opt) => {
                    if (!Array.isArray(masterForm.value.Colab)) masterForm.value.Colab = [];
                    const normalized = String(opt || '').trim();
                    if (!normalized) return;
                    const idx = masterForm.value.Colab.indexOf(normalized);
                    if (idx === -1) {
                        masterForm.value.Colab = [...masterForm.value.Colab, normalized];
                    } else {
                        const arr = [...masterForm.value.Colab];
                        arr.splice(idx, 1);
                        masterForm.value.Colab = arr;
                    }
                };

                const selectFormat = (opt) => {
                    masterForm.value.Format_Konten = opt;
                    searchSelectOpen.value = null;
                    clearPopoverTriggerState();
                };

                const selectStatus = (opt) => {
                    masterForm.value.Status = opt;
                    searchSelectOpen.value = null;
                    clearPopoverTriggerState();
                };

                const selectEditor = (opt) => {
                    masterForm.value.Editor = opt;
                    searchSelectOpen.value = null;
                    clearPopoverTriggerState();
                };

                const toggleTalent = (opt) => {
                    if (!Array.isArray(masterForm.value.Talent)) masterForm.value.Talent = [];
                    const normalized = String(opt || '').trim();
                    if (!normalized) return;
                    const idx = masterForm.value.Talent.indexOf(normalized);
                    if (idx === -1) {
                        masterForm.value.Talent = [...masterForm.value.Talent, normalized];
                    } else {
                        const nextTalent = [...masterForm.value.Talent];
                        nextTalent.splice(idx, 1);
                        masterForm.value.Talent = nextTalent;
                    }
                };

                const togglePlatform = (opt) => {
                    if (!Array.isArray(masterForm.value.Platforms)) {
                        masterForm.value.Platforms = [];
                    }
                    const normalizedOpt = String(opt || '').trim();
                    if (!normalizedOpt) return;

                    const idx = masterForm.value.Platforms.indexOf(normalizedOpt);
                    if (idx === -1) {
                        masterForm.value.Platforms = [...masterForm.value.Platforms, normalizedOpt];
                        // Ensure reactivity by replacing the object or ensuring the property exists
                        if (!masterForm.value.Distribution_Meta[normalizedOpt]) {
                            masterForm.value.Distribution_Meta = {
                                ...masterForm.value.Distribution_Meta,
                                [normalizedOpt]: { link: '', type: 'Regular', date: '' }
                            };
                        }
                    } else {
                        const newPlatforms = [...masterForm.value.Platforms];
                        newPlatforms.splice(idx, 1);
                        masterForm.value.Platforms = newPlatforms;
                    }
                };

                Object.assign(menuExports, {
                    formatOptions,
                    filteredFormatOptions,
                    filteredTalentOptions,
                    filteredPlatformOptions,
                    filteredColabOptions,
                    toggleColab,
                    selectFormat,
                    selectStatus,
                    selectEditor,
                    toggleTalent,
                    togglePlatform,
                });
@endverbatim
