import { ensureXLSX } from './xlsx-loader.js';

export async function exportBonusToExcel(deps) {
  const {
    rows,
    formatNumber,
    formatCurrency,
    formatShortDate,
    showNotification,
    notifyError,
  } = deps;

  await ensureXLSX();
  try {
    if (!rows.length) {
      showNotification('Tidak ada data untuk diekspor');
      return;
    }

    const headers = ['No', 'Judul Konten', 'Platform', 'Editor', 'Views', 'Likes', 'Comments', 'Bonus (Rp)', 'View Bonus (Rp)', 'Like Bonus (Rp)', 'Comment Bonus (Rp)', 'Tipe Konten', 'Tanggal Publish'];
    const data = rows.map((r, i) => [
      i + 1, r.Judul || '-', r.Platform || '-', r.Editor || '-',
      formatNumber(r.Views || 0), formatNumber(r.Likes || 0), formatNumber(r.Comments || 0),
      formatCurrency(r.calculatedBonus || 0), formatCurrency(r.viewBonus || 0),
      formatCurrency(r.likeBonus || 0), formatCurrency(r.commentBonus || 0),
      r.contentType || '-', r.date ? formatShortDate(r.date) : '-',
    ]);

    const ws = window.XLSX.utils.aoa_to_sheet([headers, ...data]);
    ws['!cols'] = [
      { wch: 5 }, { wch: 30 }, { wch: 15 }, { wch: 14 },
      { wch: 12 }, { wch: 12 }, { wch: 12 }, { wch: 15 },
      { wch: 15 }, { wch: 15 }, { wch: 18 }, { wch: 12 }, { wch: 15 },
    ];
    const range = window.XLSX.utils.decode_range(ws['!ref']);
    for (let C = range.s.c; C <= range.e.c; C++) {
      const addr = window.XLSX.utils.encode_cell({ r: 0, c: C });
      if (!ws[addr]) ws[addr] = {};
      ws[addr].s = {
        font: { bold: true },
        fill: { fgColor: { rgb: 'FF4F46E5' } },
        alignment: { horizontal: 'center' },
      };
    }
    const wb = window.XLSX.utils.book_new();
    window.XLSX.utils.book_append_sheet(wb, ws, 'Bonus Report');
    const today = new Date().toISOString().split('T')[0];
    window.XLSX.writeFile(wb, `Bonus_Report_${today}.xlsx`);
    showNotification('File Excel berhasil diunduh');
  } catch (err) {
    notifyError('Gagal export Excel', err, 'File Excel belum berhasil dibuat.');
  }
}
