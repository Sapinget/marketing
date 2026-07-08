/**
 * lpjk-detail.js - LPJK Detail Export Module
 *
 * Extracted from public/marketing-dashboard.html (Fase 2b - Pilot)
 * Handles PDF export for LPJK detail.
 *
 * @module lpjk-detail
 */

import { getPrintHTML } from './print-core.js';
import { openPrintWindow } from './print-browser.js';

function esc(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}

export function exportLpjkDetailToPDF(deps) {
  const {
    lpjk,
    details,
    grouped,
    formatCurrency,
    showNotification,
    notifyError,
    jsonApi,
    getFriendlyErrorMessage,
    resolveAppUrl,
  } = deps;

  try {
    if (!lpjk) return;
    if (!details.length) {
      showNotification('Tidak ada data untuk dicetak');
      return;
    }

    const today = new Date().toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    });
    const totalBudget = Number(lpjk.Budget_Rencana || 0);
    const totalRealisasi = details.reduce(
      (sum, detail) => sum + (Number(detail.Total) || 0),
      0,
    );
    const selisih = totalBudget - totalRealisasi;
    const selisihColor =
      selisih >= 0 ? 'var(--color-stone-700, #44403c)' : '#dc2626';
    const selisihLabel = `${formatCurrency(Math.abs(selisih))} ${
      selisih >= 0 ? '(Sisa)' : '(Over Budget)'
    }`;

    let rowsHtml = '';
    let globalIdx = 0;

    Object.keys(grouped).forEach((category) => {
      rowsHtml += `<tr><td colspan="5" class="category-header">${esc(category)}</td></tr>`;

      grouped[category].forEach((detail) => {
        globalIdx++;
        rowsHtml += `<tr>
                    <td>${globalIdx}</td>
                    <td>${esc(detail.Nama_Pengeluaran || '-')}</td>
                    <td class="text-right">${formatCurrency(detail.Satuan || 0)}</td>
                    <td class="text-right">${detail.Jumlah || 0}</td>
                    <td class="text-right font-bold">${formatCurrency(detail.Total || 0)}</td>
                </tr>`;
      });
    });

    rowsHtml += `<tr style="background:var(--color-stone-50,#fafaf9);font-weight:bold;">
            <td colspan="4" class="text-right" style="font-size:9pt;text-transform:uppercase;letter-spacing:.04em;">Total Realisasi</td>
            <td class="text-right" style="font-size:9pt;">${formatCurrency(totalRealisasi)}</td>
        </tr>`;

    const extraHtml = `
            <table style="width:100%;border-collapse:collapse;margin-bottom:14px;border:1px solid #e7e5e4;background:#fafaf9;page-break-inside:avoid;">
                <tr>
                    <td style="padding:10px 12px;border:none;border-right:1px solid #e7e5e4;width:25%;">
                        <div style="font-size:7pt;font-weight:bold;color:#666;text-transform:uppercase;letter-spacing:.04em;">Nama Event</div>
                        <div style="font-size:9pt;font-weight:bold;margin-top:3px;">${esc(lpjk.Nama_Event || '-')}</div>
                    </td>
                    <td style="padding:10px 12px;border:none;border-right:1px solid #e7e5e4;width:25%;">
                        <div style="font-size:7pt;font-weight:bold;color:#666;text-transform:uppercase;letter-spacing:.04em;">Tanggal</div>
                        <div style="font-size:9pt;font-weight:bold;margin-top:3px;">${esc(lpjk.Tanggal || '-')}</div>
                    </td>
                    <td style="padding:10px 12px;border:none;border-right:1px solid #e7e5e4;width:25%;">
                        <div style="font-size:7pt;font-weight:bold;color:#666;text-transform:uppercase;letter-spacing:.04em;">Budget Rencana</div>
                        <div style="font-size:9pt;font-weight:bold;margin-top:3px;">${formatCurrency(totalBudget)}</div>
                    </td>
                    <td style="padding:10px 12px;border:none;width:25%;">
                        <div style="font-size:7pt;font-weight:bold;color:#666;text-transform:uppercase;letter-spacing:.04em;">Selisih</div>
                        <div style="font-size:9pt;font-weight:bold;margin-top:3px;color:${selisihColor};">${selisihLabel}</div>
                    </td>
                </tr>
            </table>`;

    const html = getPrintHTML({
      title: 'LAPORAN PERTANGGUNGJAWABAN KEUANGAN',
      subtitle: 'MARKETING DIVISION',
      period: `Dicetak Pada: ${today}`,
      totalLabel: 'Total Realisasi',
      totalValue: formatCurrency(totalRealisasi),
      headers: ['#', 'Nama Pengeluaran', 'Satuan (Rp)', 'Qty', 'Total (Rp)'],
      rows: rowsHtml,
      extraHtml,
      showSignature: true,
    });

    openPrintWindow(html, `LPJK - ${lpjk.Nama_Event}`, {
      showNotification,
      notifyError,
      jsonApi,
      getFriendlyErrorMessage,
      resolveAppUrl,
    });
  } catch (err) {
    notifyError('Gagal export', err, 'File belum berhasil dibuat.');
  }
}
