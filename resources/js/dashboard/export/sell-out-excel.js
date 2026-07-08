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

export async function exportSellOutToExcel(deps) {
  const {
    rows,
    getSellOutProgress,
    formatNumber,
    formatCurrency,
    sellOutMonth,
    sellOutVendorFilter,
    showNotification,
    notifyError,
  } = deps;

  await ensureXLSX();
  try {
    if (!rows.length) {
      showNotification('Tidak ada data untuk diekspor');
      return;
    }

    const mapped = rows
      .map((r, i) => ({ r, p: getSellOutProgress(r) }))
      .filter(({ p }) => p.status !== 'TIDAK DIPAKAI')
      .map(({ r, p }, i) => ({
        'No': i + 1,
        'Brand': r.Brand || '',
        'Vendor': r.Vendor || '',
        'Nama Produk': r.Nama_Produk || '',
        'Terjual': r.Realisasi_Unit || 0,
        'Target': r.Target_Unit || 0,
        'Bonus/Unit': r.Bonus_Nominal || 0,
        'Total Bonus': p.bonusTotal || 0,
        'Status': p.status || '',
        'Periode': `${r.Periode_Start || '-'} - ${r.Periode_End || '-'}`,
      }));
    mapped.sort((a, b) => String(a.Brand).localeCompare(String(b.Brand)) || String(a.Vendor).localeCompare(String(b.Vendor)));

    const brandSummary = {};
    mapped.forEach(r => {
      const key = String(r.Brand || 'LAINNYA');
      if (!brandSummary[key]) brandSummary[key] = { Brand: key, Terjual: 0, Target: 0, TotalBonus: 0, Count: 0 };
      brandSummary[key].Terjual += Number(r['Terjual'] || 0);
      brandSummary[key].Target += Number(r['Target'] || 0);
      brandSummary[key].TotalBonus += Number(r['Total Bonus'] || 0);
      brandSummary[key].Count += 1;
    });

    const wb = window.XLSX.utils.book_new();
    window.XLSX.utils.book_append_sheet(wb, window.XLSX.utils.json_to_sheet(mapped), 'Target Vendor');
    window.XLSX.utils.book_append_sheet(wb, window.XLSX.utils.json_to_sheet(Object.values(brandSummary)), 'By Brand');
    window.XLSX.utils.book_append_sheet(
      wb,
      window.XLSX.utils.aoa_to_sheet([
        ['Filter Bulan', sellOutMonth || 'All'],
        ['Filter Vendor', sellOutVendorFilter || 'Semua'],
        ['Total Bonus', mapped.reduce((s, r) => s + Number(r['Total Bonus'] || 0), 0)],
      ]),
      'Summary',
    );

    const today = new Date().toISOString().split('T')[0];
    window.XLSX.writeFile(wb, `SellOut_Target_${sellOutMonth || 'All'}_${today}.xlsx`);
    showNotification(`${mapped.length} data berhasil diunduh`);
  } catch (err) {
    notifyError('Gagal export', err, 'File belum berhasil dibuat.');
  }
}
