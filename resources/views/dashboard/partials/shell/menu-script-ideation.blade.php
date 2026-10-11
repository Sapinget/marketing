@verbatim
                const ideationViewMode = ref("board");

                const ideationBoardMobileTab = ref('');

                watch(() => ideationDraftLabel.value, (label) => {
                    if (!ideationBoardMobileTab.value || !(label in kanbanBuckets.value)) {
                        ideationBoardMobileTab.value = label;
                    }
                    boardPages.value = createBoardPages();
                }, { immediate: true });

                watch(() => ideationViewMode.value, (mode) => {
                    if (mode === 'board') ideationBoardMobileTab.value = ideationDraftLabel.value;
                });

                watch(() => activeTab.value, (tab) => {
                    if (tab === 'ideation') ideationBoardMobileTab.value = ideationDraftLabel.value;
                });

                const getIdeaAgeLabel = (item) => {
                    const rawDate = String(item?.Tanggal_Rencana || '').trim();
                    if (!rawDate) return 'Umur -';
                    const source = new Date(rawDate);
                    if (Number.isNaN(source.getTime())) return 'Umur -';
                    const today = new Date();
                    source.setHours(0, 0, 0, 0);
                    today.setHours(0, 0, 0, 0);
                    const diffDays = Math.max(0, Math.floor((today.getTime() - source.getTime()) / 86400000));
                    return `Umur ${diffDays}h`;
                };

                const getIdeationTypeTone = (item) => {
                    const raw = String(item?.Format_Konten || item?.ContentType || item?.Status || "general").trim().toUpperCase();
                    const palettes = [
                        { chip: "bg-amber text-white", card: "border-amber" },
                        { chip: "bg-success text-white", card: "border-success" },
                        { chip: "bg-amber text-white", card: "border-amber" },
                        { chip: "bg-slate-500 text-white", card: "border-slate-500" },
                        { chip: "bg-danger text-white", card: "border-danger" }
                    ];
                    const fixedMap = { "AD": 0, "COLAB": 1, "PROMO": 2, "EDUKASI": 3, "STORY": 4 };
                    let idx = fixedMap[raw];
                    if (idx === undefined) idx = raw.length % palettes.length;
                    return palettes[idx];
                };

                const BOARD_COL_SIZE = 10;

                const pagedKanbanBuckets = computed(() => {
                    const b = kanbanBuckets.value;
                    const result = {};
                    for (const status of [ideationDraftLabel.value, ideationProgressLabel, ideationDoneLabel]) {
                        const items = b[status] || [];
                        const p = boardPages.value[status] || 1;
                        result[status] = items.slice((p - 1) * BOARD_COL_SIZE, p * BOARD_COL_SIZE);
                    }
                    return result;
                });

                Object.assign(menuExports, {
                    getIdeaAgeLabel,
                    getIdeationTypeTone,
                    pagedKanbanBuckets,
                
                    ideationViewMode,
                    ideationBoardMobileTab,
});
@endverbatim
