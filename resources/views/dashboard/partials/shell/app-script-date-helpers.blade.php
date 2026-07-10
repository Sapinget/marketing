@verbatim
                if (!window.MarketingDashboardRuntimeHelpers || typeof window.MarketingDashboardRuntimeHelpers.fmtLocalDate !== 'function') {
                    const fallbackFmtLocalDate = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
                    const fallbackTodayStr = () => fallbackFmtLocalDate(new Date());
                    const fallbackNormalizeDateKey = (value) => {
                        if (!value) return '';
                        if (value instanceof Date) {
                            return isNaN(value.getTime()) ? '' : fallbackFmtLocalDate(value);
                        }

                        const raw = String(value).trim();
                        const match = raw.match(/^(\d{4}-\d{2}-\d{2})/);
                        if (match) return match[1];

                        const parsed = new Date(raw);
                        return isNaN(parsed.getTime()) ? '' : fallbackFmtLocalDate(parsed);
                    };
                    const fallbackPickDateKey = (...values) => {
                        for (const value of values) {
                            const normalized = fallbackNormalizeDateKey(value);
                            if (normalized) return normalized;
                        }

                        return '';
                    };
                    const fallbackIsDateInRange = (value, start, end) => {
                        const normalized = fallbackNormalizeDateKey(value);
                        if (!normalized) return false;
                        if (start && normalized < start) return false;
                        if (end && normalized > end) return false;
                        return true;
                    };

                    window.MarketingDashboardRuntimeHelpers = {
                        fmtLocalDate: fallbackFmtLocalDate,
                        todayStr: fallbackTodayStr,
                        normalizeDateKey: fallbackNormalizeDateKey,
                        pickDateKey: fallbackPickDateKey,
                        isDateInRange: fallbackIsDateInRange,
                    };
                }

                const {
                    fmtLocalDate,
                    todayStr,
                    normalizeDateKey,
                    pickDateKey,
                    isDateInRange,
                } = window.MarketingDashboardRuntimeHelpers;
@endverbatim
