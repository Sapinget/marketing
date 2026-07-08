/**
 * bonus.js - Bonus Export Module
 *
 * Extracted from public/marketing-dashboard.html (Fase 2b - Pilot)
 * Handles PDF export for Bonus Report.
 *
 * @module bonus
 */

import { getPrintHTML } from './print-core.js';
import { openPrintWindow } from './print-browser.js';

function esc(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}

export function exportBonusToPDF(deps) {
  const {
    rows,
    monthNames,
    bonusMonth,
    bonusYear,
    periodStart,
    periodEnd,
    totals,
    formatCurrency,
    formatNumber,
    formatShortDate,
    showNotification,
    notifyError,
    jsonApi,
    getFriendlyErrorMessage,
    resolveAppUrl,
  } = deps;

  try {
    if (!rows.length) {
      showNotification('Tidak ada data bonus untuk diekspor');
      return;
    }

    const today = new Date().toLocaleDateString('id-ID', {
      day: '2-digit',
      month: 'long',
      year: 'numeric',
    });
    const periodLabel = `${monthNames[bonusMonth - 1]} ${bonusYear}`;
    const bodyRows = rows
      .map((row) => {
        const rowDate = row.date ? formatShortDate(row.date) : '-';
        const viewBonus =
          row.viewBonus > 0
            ? `<div class="small-text">+ ${formatCurrency(row.viewBonus).replace('Rp', '')}</div>`
            : '';
        const likeBonus =
          row.likeBonus > 0
            ? `<div class="small-text">+ ${formatCurrency(row.likeBonus).replace('Rp', '')}</div>`
            : '';
        const commentBonus =
          row.commentBonus > 0
            ? `<div class="small-text">+ ${formatCurrency(row.commentBonus).replace('Rp', '')}</div>`
            : '';

        return `<tr>
                    <td><div class="font-bold">${esc(row.Judul || '-')}</div><div class="small-text">${esc(row.Platform || '-')} | ${esc(rowDate)} | ${esc(row.Editor || '-')}</div></td>
                    <td class="text-center font-bold">${esc(row.contentType || 'REGULAR')}</td>
                    <td class="text-right"><div class="font-bold">${formatNumber(row.Views || 0)}</div>${viewBonus}</td>
                    <td class="text-right"><div class="font-bold">${formatNumber(row.Likes || 0)}</div>${likeBonus}</td>
                    <td class="text-right"><div class="font-bold">${formatNumber(row.Comments || 0)}</div>${commentBonus}</td>
                    <td class="text-right font-bold">${formatCurrency(row.calculatedBonus || 0).replace('Rp', '')}</td>
                </tr>`;
      })
      .join('');

    const extraHtml = `
                <table style="width:100%;border-collapse:collapse;margin:10px 0 14px;border-top:2px solid #eee;border-bottom:2px solid #eee;page-break-inside:avoid;">
                    <tr>
                        <td style="text-align:center;padding:12px 8px;border:none;background:none;width:25%"><div style="font-size:8pt;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.05em">Total Bonus</div><div style="font-size:14pt;font-weight:700;margin-top:4px">${formatCurrency(totals.totalMoney)}</div></td>
                        <td style="text-align:center;padding:12px 8px;border:none;background:none;width:25%"><div style="font-size:8pt;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.05em">Total Views</div><div style="font-size:14pt;font-weight:700;margin-top:4px">${formatNumber(totals.views)}</div></td>
                        <td style="text-align:center;padding:12px 8px;border:none;background:none;width:25%"><div style="font-size:8pt;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.05em">Total Likes</div><div style="font-size:14pt;font-weight:700;margin-top:4px">${formatNumber(totals.likes)}</div></td>
                        <td style="text-align:center;padding:12px 8px;border:none;background:none;width:25%"><div style="font-size:8pt;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.05em">Total Comments</div><div style="font-size:14pt;font-weight:700;margin-top:4px">${formatNumber(totals.comments)}</div></td>
                    </tr>
                </table>`;

    const extraStyles = `<style>
                @page { size: A4 landscape; margin: 12mm 14mm; }
                body { font-size: 10.25pt; line-height: 1.5; }
                h1 { font-size: 18pt; margin-bottom: 10px; }
                .report-meta { margin: 0 0 16px; text-align: center; color: var(--color-stone-500, #78716c); font-size: 9.25pt; line-height: 1.5; }
                table { font-size: 9.25pt; margin-top: 0; table-layout: fixed; }
                th, td { padding: 7px 8px; }
                th { text-align: center; }
                table th:nth-child(1), table td:nth-child(1) { width: 32%; }
                table th:nth-child(2), table td:nth-child(2) { width: 10%; }
                table th:nth-child(3), table td:nth-child(3) { width: 14%; }
                table th:nth-child(4), table td:nth-child(4) { width: 14%; }
                table th:nth-child(5), table td:nth-child(5) { width: 12%; }
                table th:nth-child(6), table td:nth-child(6) { width: 18%; }
                .small-text { font-size: 8pt; color: #64748b; font-weight: 600; margin-top: 3px; line-height: 1.35; }
                .font-bold { font-weight: 700; }
                .signature-section { margin-top: 24px; }
                .signature-line { height: 54px; }
            </style>`;

    const html = getPrintHTML({
      title: 'BONUS & PERFORMANCE PAYOUT REPORT',
      subtitle: 'MARKETING DIVISION',
      period: `Dicetak Pada: ${today} | Periode: ${periodLabel} (${periodStart} - ${periodEnd})`,
      totalLabel: 'Total Bonus Payout',
      totalValue: formatCurrency(totals.totalMoney),
      headers: [
        'Konten & Platform',
        'Tipe',
        'Views',
        'Likes',
        'Komen',
        'Total Bonus',
      ],
      rows: bodyRows,
      extraStyles,
      extraHtml,
      showSignature: true,
    });

    openPrintWindow(html, 'Bonus Report', {
      showNotification,
      notifyError,
      jsonApi,
      getFriendlyErrorMessage,
      resolveAppUrl,
    });
  } catch (err) {
    notifyError('Gagal export PDF', err, 'Dokumen PDF belum berhasil dibuat.');
  }
}
