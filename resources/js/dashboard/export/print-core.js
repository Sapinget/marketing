/**
 * print-core.js - Core Print Helper Functions
 *
 * Extracted from public/marketing-dashboard.html (Fase 2b)
 * Source of truth for shared print/export utilities.
 *
 * All functions are side-effect-free pure computations that
 * accept inputs and return strings/promises - no direct DOM
 * or Vue ref dependencies.
 *
 * @module print-core
 */

// ----------------------------------------------
// Private helpers
// ----------------------------------------------

/** CSS constant: override @page margins for no-header exports */
const _printNoHeaderCss =
  '<style>@page{margin-top:0!important;margin-bottom:0!important}body{padding-top:10mm!important;padding-bottom:10mm!important}</style>';

// ----------------------------------------------
// Theme helpers
// ----------------------------------------------

/**
 * Read CSS custom properties from document.documentElement
 * and return them as an inline `:root { ... }` block.
 * Falls back silently to empty string if any property is missing.
 *
 * @returns {string} CSS `:root{...}` block
 */
export function getThemeVarsCSS() {
  const vars = [
    '--color-stone-50', '--color-stone-100', '--color-stone-200', '--color-stone-300',
    '--color-stone-400', '--color-stone-500', '--color-stone-600', '--color-stone-700',
    '--color-stone-800', '--color-stone-900', '--color-stone-950',
    '--blue-50', '--blue-100', '--blue-200', '--blue-300', '--blue-400',
    '--blue-500', '--blue-600', '--blue-700', '--blue-800', '--blue-900',
    '--accent-1', '--accent-2', '--accent-3', '--bg-1', '--bg-2', '--bg-3',
    '--surface-1', '--surface-2', '--border-soft',
  ];
  const style = getComputedStyle(document.documentElement);
  const css = vars
    .map((v) => {
      const val = style.getPropertyValue(v).trim();
      return val ? `${v}:${val};` : '';
    })
    .filter(Boolean)
    .join('');
  return `:root{${css}}`;
}

function getDashboardAssetUrl(assetPath) {
  if (typeof window === 'undefined' || !window.location?.origin) {
    return assetPath;
  }

  return new URL(assetPath, window.location.origin).toString();
}

// ----------------------------------------------
// Print base styles
// ----------------------------------------------

/**
 * Returns a `<link>` + `<style>` block containing FontAwesome CDN link
 * and all CSS required for print output (A4 portrait, table styling,
 * signature sections, chart containers, colour-adjust, etc.).
 *
 * @returns {string} HTML string (<link> + <style>)
 */
export function getPrintBaseStyles() {
  const themeVars = getThemeVarsCSS();
  const fontAwesomeHref = getDashboardAssetUrl('/vendor/dashboard/fontawesome/css/all.min.css');

  return [
    `<link href="${fontAwesomeHref}" rel="stylesheet">`,
    `<style>`,
    themeVars,
    `@page { size: A4 portrait; margin: 10mm 15mm 10mm 15mm; }`,
    `html, body, * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }`,
    `body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 10pt; line-height: 1.45; color: #1f2937; background: #fff; }`,
    `.print-org-header { margin-bottom: 8px; }`,
    `.print-org-brand { width: 76px; height: 76px; object-fit: contain; display: block; margin: 0 auto; }`,
    `.print-org-copy { text-align: center; }`,
    `.print-org-copy .org-name { font-family: 'Times New Roman', Times, serif; font-size: 14pt; font-weight: bold; letter-spacing: 0.5px; margin: 0 0 2px; }`,
    `.print-org-copy .org-addr { font-family: 'Times New Roman', Times, serif; font-size: 10.5pt; margin: 0; line-height: 1.35; white-space: nowrap; }`,
    `.org-divider { border-top: 2px solid #111; margin: 0 0 10px; }`,
    `h1 { font-family: 'Times New Roman', Times, serif; font-size: 18pt; margin: 0 0 14px; font-weight: bold; text-transform: uppercase; text-align: center; line-height: 1.2; }`,
    `table { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 9pt; page-break-inside: auto; }`,
    `thead tr { background: #0f172a; }`,
    `th { background: #0f172a; color: #ffffff; border: 1px solid #0f172a; padding: 7px 6px; font-weight: 700; font-size: 8.5pt; text-align: left; text-transform: uppercase; letter-spacing: 0.04em; line-height: 1.2; }`,
    `td { border-bottom: 1px solid #e2e8f0; border-left: none; border-right: none; border-top: none; padding: 6px; font-size: 9pt; vertical-align: top; line-height: 1.35; }`,
    `tbody tr:nth-child(even) td { background: #f8fafc; }`,
    `tr { page-break-inside: avoid; page-break-after: auto; }`,
    `tfoot { display: none; }`,
    `.text-right { text-align: right; }`,
    `.text-center { text-align: center; }`,
    `.font-bold { font-weight: bold; }`,
    `.total-section { margin-top: 12px; padding: 10px 12px; background: #0f172a; color: #fff; border: 2px solid #0f172a; display: flex; justify-content: space-between; page-break-inside: avoid; font-size: 9pt; }`,
    `.total-section .label { font-size: 9.5pt; font-weight: bold; text-transform: uppercase; color: #94a3b8; }`,
    `.total-section .value { font-size: 11pt; font-weight: bold; color: #ffffff; }`,
    `.category-header { background-color: #1e293b; color: #cbd5e1; font-weight: 700; font-size: 8.5pt; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #334155; padding: 6px; }`,
    `.merged-cell { vertical-align: middle; text-align: center; border-left: 2px solid #475569; padding: 6px; }`,
    `.preserve-text { white-space: pre-wrap; word-wrap: break-word; }`,
    `.wrapped-text { word-wrap: break-word; word-break: break-word; max-width: 150px; }`,
    `.print-chart { margin: 6px 0 8px; page-break-inside: avoid; max-height: 70mm; overflow: hidden; }`,
    `.print-chart img { width: 100%; max-width: 100%; height: auto; max-height: 70mm; object-fit: contain; display: block; border: 1px solid var(--color-stone-200, #e7e5e4); }`,
    `.signature-section { margin-top: 28px; page-break-inside: avoid; display: flex !important; justify-content: space-between !important; align-items: flex-start !important; flex-wrap: nowrap !important; gap: 24px; width: 100%; }`,
    `.signature-block { width: 45%; text-align: center; display: inline-block; vertical-align: top; }`,
    `.signature-line { border-bottom: 1px solid #333; margin-bottom: 6px; height: 48px; }`,
    `</style>`,
  ].join('\n');
}

// ----------------------------------------------
// Print organisation header
// ----------------------------------------------

/**
 * Returns HTML for the organisation header block used in all print documents.
 * Layout: flexbox with logo on left, company info centre, logo on right.
 * Uses flex:0 0 auto on logos + nowrap on text to prevent wrapping.
 *
 * @returns {string} HTML string
 */
export function getPrintOrgHeaderHTML() {
  const logoUrl = getDashboardAssetUrl('/asset/images/logo.png');
  const logo = [
    '<img class="print-org-brand"',
    `src="${logoUrl}"`,
    'alt="Pura Pura Ponsel Logo"',
    'width="62" height="62"',
    'style="flex:0 0 auto;width:62px;height:62px;object-fit:contain;margin:0;">',
  ].join(' ');

  return [
    '<div class="print-org-header" style="display:flex;align-items:center;justify-content:center;gap:18px;width:100%;margin:0 0 6px;">',
    logo,
    '<div class="print-org-copy" style="flex:0 1 auto;text-align:center;">',
    '<div class="org-name" style="white-space:nowrap;">PURA PURA PONSEL</div>',
    '<div class="org-addr">Jl. Subur No. 4, Pemecutan Klod, Denpasar Barat, Denpasar, Bali 80113</div>',
    '<div class="org-addr">Jl. Kroya No. 3, Kesiman, Denpasar Timur, Denpasar, Bali 80237</div>',
    '<div class="org-addr">Telephone : +6281237200400</div>',
    '</div>',
    logo,
    '</div>',
    '<div class="org-divider"></div>',
  ].join('\n');
}

// ----------------------------------------------
// Main print HTML builder
// ----------------------------------------------

/**
 * Build a complete, self-contained print HTML document.
 * Combines org header, title, meta, table, total section, and signature.
 *
 * @param {Object} options
 * @param {string}  options.title          - Report title (rendered as <h1>)
 * @param {string}  [options.subtitle]     - Optional subtitle
 * @param {string}  [options.period]       - Optional period string
 * @param {string}  [options.totalLabel]   - Label for the total bar (e.g. "Grand Total")
 * @param {string}  [options.totalValue]   - Value for the total bar
 * @param {string[]} options.headers       - Column header labels
 * @param {string}  options.rows           - Pre-rendered `<tr>...</tr>` rows HTML
 * @param {string}  [options.extraStyles]  - Extra <style> blocks injected before </head>
 * @param {string}  [options.extraHtml]    - Extra HTML injected before the table
 * @param {boolean} [options.showSignature=true] - Whether to include Prepared/Approved blocks
 * @returns {string} Complete HTML document as a string
 */
export function getPrintHTML({
  title,
  subtitle,
  period,
  totalLabel,
  totalValue,
  headers,
  rows,
  extraStyles = '',
  extraHtml = '',
  showSignature = true,
}) {
  const headerCells = headers.map((h) => `<th>${h}</th>`).join('');
  const hasTotal =
    String(totalLabel || '').trim() !== '' ||
    String(totalValue || '').trim() !== '';
  const totalHtml = hasTotal
    ? [
        '<div class="total-section">',
        `<span class="label">${totalLabel}:</span>`,
        `<span class="value">${totalValue}</span>`,
        '</div>',
      ].join('\n')
    : '';

  const signatureSection = showSignature
    ? [
        '<div class="signature-section" style="padding:0 20px;">',
        '<div class="signature-block">',
        '<div class="signature-line"></div>',
        '<div style="font-size:8pt;font-weight:bold;">Prepared By</div>',
        '<div style="font-size:7pt;color:#666;margin-top:2px;">Team Marketing</div>',
        '</div>',
        '<div class="signature-block">',
        '<div class="signature-line"></div>',
        '<div style="font-size:8pt;font-weight:bold;">Approved By</div>',
        '<div style="font-size:7pt;color:#666;margin-top:2px;">Manager Marketing</div>',
        '</div>',
        '</div>',
      ].join('\n')
    : '';

  const metaParts = [subtitle, period].filter(
    (v) => String(v || '').trim() !== '',
  );
  const metaHtml = metaParts.length
    ? `<div class="report-meta">${metaParts.join(' &bull; ')}</div>`
    : '';

  return [
    '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>',
    title,
    '</title>',
    getPrintBaseStyles(),
    '<style>',
    '.report-meta { margin: -6px 0 12px; text-align: center; color: var(--color-stone-500, #78716c); font-size: 8pt; }',
    '</style>',
    extraStyles,
    '</head><body>',
    getPrintOrgHeaderHTML(),
    `<h1>${title}</h1>`,
    metaHtml,
    extraHtml,
    '<table><thead><tr>',
    headerCells,
    '</tr></thead><tbody>',
    rows,
    '</tbody></table>',
    totalHtml,
    signatureSection,
    '</body></html>',
  ].join('\n');
}

// ----------------------------------------------
// Auto-print bootstrap script
// ----------------------------------------------

/**
 * Returns a <script> tag that waits for all assets (fonts, images) to load,
 * then triggers `window.print()`, and closes the window after printing.
 *
 * @returns {string} HTML script tag
 */
export function getPrintAutoBootstrapScript() {
  return `<script>
(()=>{
const waitForAssets=()=>{
const fontsReady=document.fonts&&document.fonts.ready
?document.fonts.ready.catch(()=>undefined)
:Promise.resolve();
const imgPromises=Array.from(document.images||[]).map(img=>{
if(img.complete)return Promise.resolve();
return new Promise(resolve=>{img.addEventListener('load',()=>resolve(),{once:true});img.addEventListener('error',()=>resolve(),{once:true});});
});
return Promise.all([fontsReady,Promise.all(imgPromises)]);
};
const triggerPrint=()=>{waitForAssets().finally(()=>{setTimeout(()=>{try{window.focus();window.print();}catch(e){}},150);});};
if(document.readyState==='complete'){triggerPrint();}else{window.addEventListener('load',triggerPrint,{once:true});}
if('onafterprint'in window){window.addEventListener('afterprint',()=>window.close(),{once:true});}
})();<\/script>`;
}

// ----------------------------------------------
// Standalone print document wrapper
// ----------------------------------------------

/**
 * Wrap a print HTML fragment into a standalone document with
 * optional auto-print script injection.
 *
 * @param {string}  html                    - HTML content (with or without <body>)
 * @param {object}  [options]
 * @param {boolean} [options.autoPrint=false] - Whether to inject auto-print script
 * @returns {string} Standalone HTML document string
 */
export function buildStandalonePrintHtml(html, { autoPrint = false } = {}) {
  const withPagePaddingCss = String(html).includes('</head>')
    ? String(html).replace('</head>', `${_printNoHeaderCss}</head>`)
    : `${html}${_printNoHeaderCss}`;

  if (!autoPrint) {
    return withPagePaddingCss;
  }

  const autoPrintScript = getPrintAutoBootstrapScript();
  return withPagePaddingCss.includes('</body>')
    ? withPagePaddingCss.replace('</body>', `${autoPrintScript}</body>`)
    : `${withPagePaddingCss}${autoPrintScript}`;
}

// ----------------------------------------------
// Print asset waiter
// ----------------------------------------------

/**
 * Returns a Promise that resolves when all fonts and images
 * inside the given print window's document have loaded.
 *
 * @param {Window} printWindow - The popup/target window
 * @returns {Promise<void>}
 */
export function waitForPrintAssets(printWindow) {
  const onLoadReady = new Promise((resolve) => {
    if (printWindow.document.readyState === 'complete') {
      resolve();
      return;
    }
    printWindow.addEventListener('load', () => resolve(), { once: true });
  });

  const fontsReady =
    printWindow.document.fonts && printWindow.document.fonts.ready
      ? printWindow.document.fonts.ready.catch(() => undefined)
      : Promise.resolve();

  const imagePromises = Array.from(
    printWindow.document.images || [],
  ).map((img) => {
    if (img.complete) return Promise.resolve();
    return new Promise((resolve) => {
      img.addEventListener('load', () => resolve(), { once: true });
      img.addEventListener('error', () => resolve(), { once: true });
    });
  });

  return Promise.all([onLoadReady, fontsReady, Promise.all(imagePromises)]);
}

// ----------------------------------------------
// Global exports for inline script compatibility
// ----------------------------------------------

if (typeof window !== 'undefined') {
  window.getThemeVarsCSS = getThemeVarsCSS;
  window.getPrintBaseStyles = getPrintBaseStyles;
  window.getPrintOrgHeaderHTML = getPrintOrgHeaderHTML;
  window.getPrintHTML = getPrintHTML;
  window._printNoHeaderCss = _printNoHeaderCss;
  window.getPrintAutoBootstrapScript = getPrintAutoBootstrapScript;
  window.buildStandalonePrintHtml = buildStandalonePrintHtml;
  window.waitForPrintAssets = waitForPrintAssets;
}
