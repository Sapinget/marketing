@verbatim
                const {
                    filteredLpjkData,
                    lpjkTotalPages,
                    pagedLpjkData,
                    lpjkDetailGrouped,
                    lpjkDetailTotal,
                    openLpjkModal,
                    saveLpjk: saveLpjkOperation,
                    deleteLpjk,
                    openLpjkDetail,
                    closeLpjkDetail,
                    saveLpjkDetail,
                    deleteLpjkDetail,
                } = window.MarketingDashboardRuntimeHelpers.createLpjkOperations({
                    computed,
                    watch,
                    lpjkData,
                    lpjkSearch,
                    lpjkPage,
                    lpjkDetailData,
                    lpjkModalType,
                    lpjkForm,
                    lpjkModalOpen,
                    lpjkDetailItem,
                    lpjkDetailModalOpen,
                    activeLpjkRow,
                    ensureRunApi,
                    todayStr,
                    submitting,
                    showNotification,
                    handleError,
                    PAGE_SIZE,
                });
                const saveLpjk = () => {
                    return saveLpjkOperation();
                };
@endverbatim
