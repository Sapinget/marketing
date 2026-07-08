import { exportBonusToPDF } from './bonus.js';
import { exportBonusToExcel } from './bonus-excel.js';
import { exportPromoToPDF } from './promo.js';
import { exportSellOutToPDF } from './sell-out.js';
import { exportSellOutToExcel } from './sell-out-excel.js';

window.MarketingDashboardSalesExports = Object.freeze({
  exportBonusToPDF,
  exportBonusToExcel,
  exportPromoToPDF,
  exportSellOutToPDF,
  exportSellOutToExcel,
});
