export function exportActiveTabPdf(deps) {
  const {
    activeTab,
    formatNumber,
    formatShortDate,
    showNotification,
    notifyError,
    jsonApi,
    getFriendlyErrorMessage,
    resolveAppUrl,
    exportUnitDitanyaToPDF,
    exportClaimGaransiToPDF,
    exportKeepBarangToPDF,
    exportAnalyticsToPDF,
    filteredMasterPlanData,
    filteredDistributionData,
    filteredAnalyticsData,
    filteredUnboxingData,
    filteredOrderanOnlineData,
    filteredUnitDitanyaData,
    filteredClaimGaransiData,
    topContentCombined,
    lowContentCombined,
    exportGenericTabularPdf,
  } = deps;

  if (activeTab === 'unit_ditanya') {
    exportUnitDitanyaToPDF();
    return;
  }

  if (activeTab === 'claim_garansi_asuransi') {
    exportClaimGaransiToPDF();
    return;
  }

  if (activeTab === 'keep_barang') {
    exportKeepBarangToPDF();
    return;
  }

  if (activeTab === 'analytics') {
    exportAnalyticsToPDF();
    return;
  }

  const filteredDataMap = {
    master: filteredMasterPlanData,
    ideation: filteredMasterPlanData,
    distribution: filteredDistributionData,
    analytics: filteredAnalyticsData,
    unboxing: filteredUnboxingData,
    orderan_online: filteredOrderanOnlineData,
    unit_ditanya: filteredUnitDitanyaData,
    claim_garansi_asuransi: filteredClaimGaransiData,
    top_content_platform: topContentCombined.map((row) => ({
      Platform: row.platform,
      Judul: row.title,
      Editor: row.editor,
      Views: formatNumber(row.views),
      Tanggal: formatShortDate(row.date),
    })),
    low_content_platform: lowContentCombined.map((row) => ({
      Platform: row.platform,
      Judul: row.title,
      Editor: row.editor,
      Views: formatNumber(row.views),
      Tanggal: formatShortDate(row.date),
    })),
  };

  const tabTitles = {
    master: 'Master Plan Konten',
    ideation: 'Ideation & Konten',
    distribution: 'Distribution',
    analytics: 'Analytics',
    unboxing: 'Unboxing',
    orderan_online: 'Orderan Online',
    unit_ditanya: 'Unit Ditanya',
    claim_garansi_asuransi: 'Claim Garansi & Asuransi',
    top_content_platform: 'Top Konten Per Platform',
    low_content_platform: 'Konten Terlemah Per Platform',
  };

  const rows = filteredDataMap[activeTab] || [];
  const title = tabTitles[activeTab] || activeTab;

  return exportGenericTabularPdf({
    rows,
    title,
    showNotification,
    notifyError,
    jsonApi,
    getFriendlyErrorMessage,
    resolveAppUrl,
  });
}
