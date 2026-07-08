function ensureXLSX() {
  if (window.XLSX) return Promise.resolve();
  return new Promise((resolve, reject) => {
    const s = document.createElement('script');
    s.src = 'https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js';
    s.onload = () => resolve();
    s.onerror = () => reject(new Error('Gagal memuat library Excel'));
    document.head.appendChild(s);
  });
}

export async function exportBudgetToExcel(deps) {
  const {
    config,
    calculations,
    showNotification,
    notifyError,
  } = deps;

  await ensureXLSX();
  try {
    const cfg = config;
    const calc = calculations;
    const safe = (value) => Number(value) || 0;

    const summaryData = [
      ['BUDGETING PLAN & APPROVAL'],
      ['PURA PURA PONSEL - MARKETING DIVISION'],
      [`Generated Date: ${new Date().toLocaleDateString('id-ID')}`],
      [],
      ['EXECUTIVE SUMMARY'],
      ['Metric', 'Value'],
      [
        'Total Rancangan Biaya (A)',
        calc.metaTotal + calc.googleTotal + (calc.mekariTopupTotal || 0) + ((calc.othersCalculated || []).reduce((a, b) => a + (b.total || 0), 0)),
      ],
      [
        'Total Saldo Digunakan (B)',
        Math.min(calc.metaTotal, safe(cfg.meta?.balance)) + Math.min(calc.googleTotal, safe(cfg.google?.balance)) + Math.min(calc.mekariBroadcastTotal || 0, safe(cfg.mekari?.broadcast?.balance)),
      ],
      ['TOTAL TOP-UP REQUIRED (A-B)', calc.totalTopup || 0],
      [],
      ['Note: Dokumen ini digunakan sebagai dasar pengajuan dana operasional.'],
    ];

    const detailHeaders = ['No', 'Kategori Utama', 'Sub-Kategori', 'Item Detail', 'Qty/Slot', 'Durasi (Hari/Minggu)', 'Cost Base (Rp)', 'Total Rancangan (Rp)', 'Saldo Dipotong (Rp)', 'Nominal Top-up (Rp)'];
    const detailRows = [];
    let no = 1;
    detailRows.push([no++, 'Platform Ads', 'Meta Ads', 'Iklan Facebook & Instagram', cfg.meta?.totalAds, cfg.meta?.days, cfg.meta?.costPerAd, calc.metaTotal, Math.min(calc.metaTotal, safe(cfg.meta?.balance)), calc.metaTopup]);
    detailRows.push([no++, 'Platform Ads', 'Google Ads', 'Google Search & Display', cfg.google?.totalAds, cfg.google?.days, cfg.google?.costPerAd, calc.googleTotal, Math.min(calc.googleTotal, safe(cfg.google?.balance)), calc.googleTopup]);
    detailRows.push([no++, 'Mekari Ecosystem', 'Visitor', `Target ${cfg.mekari?.visitor?.targetPerDay || 0} vis/day`, cfg.mekari?.visitor?.targetPerDay, cfg.mekari?.visitor?.days, '-', '-', '(Unit Visitor)', cfg.mekari?.visitor?.topupCost || 0]);
    detailRows.push([no++, 'Mekari Ecosystem', 'Broadcast', 'Broadcast Message Blasting', '-', `${cfg.mekari?.broadcast?.weeks || 0} Minggu`, cfg.mekari?.broadcast?.costPerWeek, calc.mekariBroadcastTotal, Math.min(calc.mekariBroadcastTotal || 0, safe(cfg.mekari?.broadcast?.balance)), calc.mekariBroadcastTopup]);
    (calc.colabBreakdown || []).forEach(c => {
      detailRows.push([no++, 'Collaboration', 'Partnership', c.name, c.slots, '-', c.packageCost, c.packageCost, '-', c.packageCost]);
    });
    (calc.othersCalculated || []).forEach(o => {
      detailRows.push([no++, 'Operational & Others', 'General', o.name, o.quantity, o.duration, o.costPerUnit, o.total, Math.min(o.total || 0, o.balance || 0), o.topup]);
    });

    const wb = window.XLSX.utils.book_new();
    const wsSummary = window.XLSX.utils.aoa_to_sheet(summaryData);
    wsSummary['!cols'] = [{ wch: 30 }, { wch: 20 }];
    window.XLSX.utils.book_append_sheet(wb, wsSummary, 'Summary & Approval');
    const wsDetail = window.XLSX.utils.aoa_to_sheet([detailHeaders, ...detailRows]);
    wsDetail['!cols'] = [{ wch: 5 }, { wch: 20 }, { wch: 20 }, { wch: 30 }, { wch: 10 }, { wch: 15 }, { wch: 15 }, { wch: 20 }, { wch: 20 }, { wch: 20 }];
    window.XLSX.utils.book_append_sheet(wb, wsDetail, 'Detailed Breakdown');

    const filename = `Budget_Plan_${new Date().toISOString().split('T')[0]}.xlsx`;
    window.XLSX.writeFile(wb, filename);
    showNotification('Budget plan berhasil diunduh');
  } catch (err) {
    notifyError('Gagal export Excel', err, 'File Excel belum berhasil dibuat.');
  }
}
