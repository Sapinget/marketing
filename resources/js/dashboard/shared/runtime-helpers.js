import {
    formatNumber, formatCurrency, formatWaNumber, calcAdminPct,
    formatShortDate, formatFullDate, formatMonthLabel, getStatusColor,
} from './formatters.js';
import {
    inferNotificationType, getFriendlyErrorMessage, createNotificationHelpers,
} from './notifications.js';
import { resolveAppUrl, jsonApi } from './url.js';
import { ensureRunApi, setRunnerFactory } from './run-api.js';
import { createAdminUserSettingsState, createAdminUserSettingsActions } from '../menu/admin-users.js';
import { createCustomerServiceCrud, createCustomerServiceState } from '../menu/customer-service.js';
import { createSettingsHelpers } from '../menu/settings.js';
import {
    generateTempId,
    normalizeNamaStockPayload,
    normalizeNamaStockKeyStrict,
    createNamaStockState,
    createNamaStockActions,
} from '../menu/nama-stock.js';
import { createLpjkOperations } from '../menu/lpjk.js';
import {
    fmtLocalDate,
    todayStr,
    normalizeDateKey,
    pickDateKey,
    isDateInRange,
} from './date-utils.js';

window.MarketingDashboardRuntimeHelpers = {
    fmtLocalDate,
    todayStr,
    normalizeDateKey,
    pickDateKey,
    isDateInRange,
    inferNotificationType,
    getFriendlyErrorMessage,
    createNotificationHelpers,
    createAdminUserSettingsState,
    createAdminUserSettingsActions,
    createCustomerServiceState,
    createCustomerServiceCrud,
    createSettingsHelpers,
    generateTempId,
    normalizeNamaStockPayload,
    normalizeNamaStockKeyStrict,
    createNamaStockState,
    createNamaStockActions,
    formatNumber,
    formatCurrency,
    formatWaNumber,
    calcAdminPct,
    formatShortDate,
    formatFullDate,
    formatMonthLabel,
    getStatusColor,
    resolveAppUrl,
    jsonApi,
    ensureRunApi,
    setRunnerFactory,
    createLpjkOperations,
};
