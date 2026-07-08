import { exportAdsLogToPDF } from './ads-log.js';
import { exportBudgetToPDF } from './budget.js';
import { exportBudgetToExcel } from './budget-excel.js';
import { exportLpjkDetailToPDF } from './lpjk-detail.js';
import { exportPriceComparisonToPDF } from './price-comparison.js';

window.MarketingDashboardReportingExports = Object.freeze({
  exportAdsLogToPDF,
  exportBudgetToPDF,
  exportBudgetToExcel,
  exportLpjkDetailToPDF,
  exportPriceComparisonToPDF,
});
