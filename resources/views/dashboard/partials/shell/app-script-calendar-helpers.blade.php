@verbatim
                // Calendar Logic
                const getFilterRef = (ctx) => {
                    if (ctx === 'orderanOnline' || activeTab.value === 'orderan_online') return orderanOnlineDateRange;
                    if (ctx === 'unitDitanya' || activeTab.value === 'unit_ditanya') return unitDitanyaDateRange;
                    if (ctx === 'bonus') return bonusFilter;
                    if (ctx === 'hargaKompetitor') return hargaKompetitorDateFilter;
                    if (ctx === 'ads_log') return adsDateFilter;
                    if (ctx === 'budgeting') return budgetDateFilter;
                    if (ctx === 'insight' || activeTab.value === 'analisa_insight') return insightDateFilter;
                    if (ctx === 'metaStory' || activeTab.value === 'meta_story') return metaStoryDateFilter;
                    if (ctx === 'metaFeed' || activeTab.value === 'meta_feed') return metaFeedDateFilter;
                    if (activeTab.value === 'master' || activeTab.value === 'ideation') return masterFilterRange;
                    return commonDateFilter;
                };

                const openCalendar = (event, mode, plat = "", formContext = 'master') => {
                    calendarMode.value = mode;
                    currentPlatForDate.value = plat;
                    calendarFormContext.value = formContext;
                    let initialDate = new Date();

                    if (mode === "form") {
                        if (formContext === 'master') {
                            initialDate = new Date(masterForm.value.Tanggal_Rencana);
                        } else if (formContext === 'story') {
                            initialDate = storyForm.value.Tanggal ? new Date(storyForm.value.Tanggal) : new Date();
                        } else if (formContext === 'orderanOnline1') {
                            initialDate = orderanOnlineForm.value['TANGGAL'] ? new Date(orderanOnlineForm.value['TANGGAL']) : new Date();
                        } else if (formContext === 'unitDitanya1') {
                            initialDate = unitDitanyaForm.value['TANGGAL'] ? new Date(unitDitanyaForm.value['TANGGAL']) : new Date();
                        } else if (formContext === 'claimGaransi1') {
                            initialDate = claimGaransiForm.value['TANGGAL_MASUK'] ? new Date(claimGaransiForm.value['TANGGAL_MASUK']) : new Date();
                        } else if (formContext === 'claimGaransi2') {
                            initialDate = claimGaransiForm.value['TANGGAL_DIAMBIL'] ? new Date(claimGaransiForm.value['TANGGAL_DIAMBIL']) : new Date();
                        } else if (formContext === 'claimGaransi3') {
                            initialDate = claimGaransiForm.value['TANGGAL_ESTIMASI'] ? new Date(claimGaransiForm.value['TANGGAL_ESTIMASI']) : new Date();
                        } else if (formContext === 'distribution') {
                            initialDate = distributionForm.value.Tanggal_Publish ? new Date(distributionForm.value.Tanggal_Publish) : new Date();
                        } else if (formContext === 'promoDate1') {
                            initialDate = promoTempDate.value.start ? new Date(promoTempDate.value.start) : new Date();
                        } else if (formContext === 'promoDate2') {
                            initialDate = promoTempDate.value.end ? new Date(promoTempDate.value.end) : new Date();
                        } else if (formContext === 'sotDate1') {
                            initialDate = sellOutForm.value.Periode_Start ? new Date(sellOutForm.value.Periode_Start) : new Date();
                        } else if (formContext === 'sotDate2') {
                            initialDate = sellOutForm.value.Periode_End ? new Date(sellOutForm.value.Periode_End) : new Date();
                        } else if (formContext === 'hargaKompetitorCek') {
                            initialDate = hargaKompetitorForm.value.Tanggal_Cek ? new Date(hargaKompetitorForm.value.Tanggal_Cek) : new Date();
                        } else if (formContext === 'lpjkTanggal') {
                            initialDate = lpjkForm.value.Tanggal ? new Date(lpjkForm.value.Tanggal) : new Date();
                        } else if (formContext === 'adsTanggal') {
                            initialDate = adsForm.value.Tanggal ? new Date(adsForm.value.Tanggal) : new Date();
                        } else if (formContext === 'keepBarangTanggalKeep') {
                            initialDate = keepBarangForm.value['TANGGAL_KEEP'] ? new Date(keepBarangForm.value['TANGGAL_KEEP']) : new Date();
                        } else if (formContext === 'keepBarangRencanaAmbil') {
                            initialDate = keepBarangForm.value['RENCANA_PENGAMBILAN'] ? new Date(keepBarangForm.value['RENCANA_PENGAMBILAN']) : new Date();
                        } else if (formContext === 'keepBarangDeadlineGudang') {
                            initialDate = keepBarangForm.value['DEADLINE_TEAM_GUDANG'] ? new Date(keepBarangForm.value['DEADLINE_TEAM_GUDANG']) : new Date();
                        } else if (formContext === 'unboxingUploadDate') {
                            initialDate = unboxingForm.value.Upload_Date ? new Date(unboxingForm.value.Upload_Date) : new Date();
                        }
                    } else if (mode === "published" && plat && masterForm.value.Distribution_Meta[plat]) {
                        initialDate = masterForm.value.Distribution_Meta[plat].date ? new Date(masterForm.value.Distribution_Meta[plat].date) : new Date();
                    } else if (mode === 'filter') {
                        const targetFilter = getFilterRef(formContext);
                        initialDate = targetFilter.value.start ? new Date(targetFilter.value.start) : new Date();
                    }

                    currentDateView.value = isNaN(initialDate.getTime()) ? new Date() : initialDate;
                    calendarOpen.value = true;
                };

                const resetCalendar = () => {
                    const ctx = calendarFormContext.value;
                    if (calendarMode.value === 'filter') {
                        getFilterRef(ctx).value = { start: '', end: '' };
                    } else if (calendarMode.value === 'form') {
                        if (ctx === 'master') masterForm.value.Tanggal_Rencana = '';
                        else if (ctx === 'story') storyForm.value.Tanggal = '';
                        else if (ctx === 'orderanOnline1') orderanOnlineForm.value['TANGGAL'] = '';
                        else if (ctx === 'unitDitanya1') unitDitanyaForm.value['TANGGAL'] = '';
                        else if (ctx === 'claimGaransi1') claimGaransiForm.value['TANGGAL_MASUK'] = '';
                        else if (ctx === 'claimGaransi2') claimGaransiForm.value['TANGGAL_DIAMBIL'] = '';
                        else if (ctx === 'claimGaransi3') claimGaransiForm.value['TANGGAL_ESTIMASI'] = '';
                        else if (ctx === 'distribution') distributionForm.value.Tanggal_Publish = '';
                        else if (ctx === 'analytics') analyticsForm.value.Tanggal_Publish = '';
                        else if (ctx === 'promoDate1') { promoTempDate.value.start = ''; syncPromoPerideText(); }
                        else if (ctx === 'promoDate2') { promoTempDate.value.end = ''; syncPromoPerideText(); }
                        else if (ctx === 'sotDate1') sellOutForm.value.Periode_Start = '';
                        else if (ctx === 'sotDate2') sellOutForm.value.Periode_End = '';
                        else if (ctx === 'hargaKompetitorCek') hargaKompetitorForm.value.Tanggal_Cek = '';
                        else if (ctx === 'lpjkTanggal') lpjkForm.value.Tanggal = '';
                        else if (ctx === 'adsTanggal') adsForm.value.Tanggal = '';
                        else if (ctx === 'keepBarangTanggalKeep') keepBarangForm.value['TANGGAL_KEEP'] = '';
                        else if (ctx === 'keepBarangRencanaAmbil') keepBarangForm.value['RENCANA_PENGAMBILAN'] = '';
                        else if (ctx === 'keepBarangDeadlineGudang') keepBarangForm.value['DEADLINE_TEAM_GUDANG'] = '';
                        else if (ctx === 'unboxingUploadDate') unboxingForm.value.Upload_Date = '';
                    } else if (calendarMode.value === 'published') {
                        if (currentPlatForDate.value && masterForm.value.Distribution_Meta[currentPlatForDate.value]) {
                            masterForm.value.Distribution_Meta[currentPlatForDate.value].date = '';
                        }
                    }
                    calendarOpen.value = false;
                };

                const getPlatformIcon = (plat) => {
                    const map = {
                        'Instagram': 'fa-brands fa-instagram',
                        'TikTok': 'fa-brands fa-tiktok',
                        'YouTube': 'fa-brands fa-youtube',
                        'Facebook': 'fa-brands fa-facebook',
                        'X': 'fa-brands fa-x-twitter',
                        'Threads': 'fa-brands fa-threads'
                    };
                    return map[plat] || 'fa-solid fa-link';
                };

                const hasAnyLink = (item) => {
                    if (!item.Distribution_Meta || typeof item.Distribution_Meta !== 'object') return false;
                    return Object.values(item.Distribution_Meta).some(d => d && typeof d === 'object' && d.link && typeof d.link === 'string');
                };
                const hasAnyMasterLink = (item) => hasAnyLink(item) || !!String(item?.Link_Drive || '').trim();

                const formatFullDate = window.MarketingDashboardRuntimeHelpers?.formatFullDate || ((d) => {
                    if (!d) return '';
                    const date = new Date(d);
                    if (isNaN(date.getTime())) return String(d);
                    return `${date.getDate().toString().padStart(2, '0')}/${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getFullYear()}`;
                });

                const calendarTargetDate = computed(() => {
                    if (calendarMode.value === "form") {
                        const ctx = calendarFormContext.value;
                        if (ctx === 'master') return masterForm.value.Tanggal_Rencana;
                        if (ctx === 'story') return storyForm.value.Tanggal;
                        if (ctx === 'orderanOnline1') return orderanOnlineForm.value['TANGGAL'];
                        if (ctx === 'unitDitanya1') return unitDitanyaForm.value['TANGGAL'];
                        if (ctx === 'claimGaransi1') return claimGaransiForm.value['TANGGAL_MASUK'];
                        if (ctx === 'claimGaransi2') return claimGaransiForm.value['TANGGAL_DIAMBIL'];
                        if (ctx === 'claimGaransi3') return claimGaransiForm.value['TANGGAL_ESTIMASI'];
                        if (ctx === 'distribution') return distributionForm.value.Tanggal_Publish;
                        if (ctx === 'analytics') return analyticsForm.value.Tanggal_Publish;
                        if (ctx === 'promoDate1') return promoTempDate.value.start;
                        if (ctx === 'promoDate2') return promoTempDate.value.end;
                        if (ctx === 'sotDate1') return sellOutForm.value.Periode_Start;
                        if (ctx === 'sotDate2') return sellOutForm.value.Periode_End;
                        if (ctx === 'hargaKompetitorCek') return hargaKompetitorForm.value.Tanggal_Cek;
                        if (ctx === 'lpjkTanggal') return lpjkForm.value.Tanggal;
                        if (ctx === 'adsTanggal') return adsForm.value.Tanggal;
                        if (ctx === 'keepBarangTanggalKeep') return keepBarangForm.value['TANGGAL_KEEP'];
                        if (ctx === 'keepBarangRencanaAmbil') return keepBarangForm.value['RENCANA_PENGAMBILAN'];
                        if (ctx === 'keepBarangDeadlineGudang') return keepBarangForm.value['DEADLINE_TEAM_GUDANG'];
                        if (ctx === 'unboxingUploadDate') return unboxingForm.value.Upload_Date;
                        return '';
                    }
                    if (calendarMode.value === "published" && currentPlatForDate.value) return masterForm.value.Distribution_Meta[currentPlatForDate.value].date;
                    return getFilterRef(calendarFormContext.value).value.start;
                });

                const calendarDaysInMonth = computed(() => {
                    const year = currentDateView.value.getFullYear();
                    const month = currentDateView.value.getMonth();
                    return new Date(year, month + 1, 0).getDate();
                });

                const calendarEmptyDays = computed(() => {
                    const year = currentDateView.value.getFullYear();
                    const month = currentDateView.value.getMonth();
                    let firstDay = new Date(year, month, 1).getDay();
                    return firstDay === 0 ? 6 : firstDay - 1;
                });

                const selectDate = (day) => {
                    const year = currentDateView.value.getFullYear();
                    const month = currentDateView.value.getMonth();
                    const dateStr = `${year}-${String(month + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;

                    if (calendarMode.value === "form") {
                        if (calendarFormContext.value === 'master') {
                            masterForm.value.Tanggal_Rencana = dateStr;
                        } else if (calendarFormContext.value === 'story') {
                            storyForm.value.Tanggal = dateStr;
                        } else if (calendarFormContext.value === 'orderanOnline1') {
                            orderanOnlineForm.value['TANGGAL'] = dateStr;
                        } else if (calendarFormContext.value === 'unitDitanya1') {
                            unitDitanyaForm.value['TANGGAL'] = dateStr;
                        } else if (calendarFormContext.value === 'claimGaransi1') {
                            claimGaransiForm.value['TANGGAL_MASUK'] = dateStr;
                        } else if (calendarFormContext.value === 'claimGaransi2') {
                            claimGaransiForm.value['TANGGAL_DIAMBIL'] = dateStr;
                        } else if (calendarFormContext.value === 'claimGaransi3') {
                            claimGaransiForm.value['TANGGAL_ESTIMASI'] = dateStr;
                        } else if (calendarFormContext.value === 'distribution') {
                            distributionForm.value.Tanggal_Publish = dateStr;
                        } else if (calendarFormContext.value === 'analytics') {
                            analyticsForm.value.Tanggal_Publish = dateStr;
                        } else if (calendarFormContext.value === 'promoDate1') {
                            promoTempDate.value.start = dateStr;
                            promoPeriodePreset.value = 'custom';
                            syncPromoPerideText();
                        } else if (calendarFormContext.value === 'promoDate2') {
                            promoTempDate.value.end = dateStr;
                            promoPeriodePreset.value = 'custom';
                            syncPromoPerideText();
                        } else if (calendarFormContext.value === 'sotDate1') {
                            sellOutForm.value.Periode_Start = dateStr;
                        } else if (calendarFormContext.value === 'sotDate2') {
                            sellOutForm.value.Periode_End = dateStr;
                        } else if (calendarFormContext.value === 'hargaKompetitorCek') {
                            hargaKompetitorForm.value.Tanggal_Cek = dateStr;
                        } else if (calendarFormContext.value === 'lpjkTanggal') {
                            lpjkForm.value.Tanggal = dateStr;
                        } else if (calendarFormContext.value === 'adsTanggal') {
                            adsForm.value.Tanggal = dateStr;
                        } else if (calendarFormContext.value === 'keepBarangTanggalKeep') {
                            keepBarangForm.value['TANGGAL_KEEP'] = dateStr;
                        } else if (calendarFormContext.value === 'keepBarangRencanaAmbil') {
                            keepBarangForm.value['RENCANA_PENGAMBILAN'] = dateStr;
                        } else if (calendarFormContext.value === 'keepBarangDeadlineGudang') {
                            keepBarangForm.value['DEADLINE_TEAM_GUDANG'] = dateStr;
                        } else if (calendarFormContext.value === 'unboxingUploadDate') {
                            unboxingForm.value.Upload_Date = dateStr;
                        }
                        calendarOpen.value = false;
                    } else if (calendarMode.value === "published") {
                        if (currentPlatForDate.value && masterForm.value.Distribution_Meta[currentPlatForDate.value]) {
                            masterForm.value.Distribution_Meta[currentPlatForDate.value].date = dateStr;
                        }
                    } else {
                        const targetFilter = getFilterRef(calendarFormContext.value);

                        if (!targetFilter.value.start || (targetFilter.value.start && targetFilter.value.end)) {
                            targetFilter.value.start = dateStr;
                            targetFilter.value.end = "";
                        } else {
                            if (dateStr < targetFilter.value.start) {
                                targetFilter.value.end = targetFilter.value.start;
                                targetFilter.value.start = dateStr;
                            } else {
                                targetFilter.value.end = dateStr;
                            }
                            hoveredDate.value = "";
                            calendarOpen.value = false;
                        }
                        if (activeTab.value === 'master' || activeTab.value === 'ideation') saveFilterRange();
                    }
                };

                const isSelectedDate = (day) => {
                    const year = currentDateView.value.getFullYear();
                    const month = currentDateView.value.getMonth();
                    const dateStr = `${year}-${String(month + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;

                    if (calendarMode.value === "form") {
                        if (calendarFormContext.value === 'master') {
                            return masterForm.value.Tanggal_Rencana === dateStr;
                        } else if (calendarFormContext.value === 'story') {
                            return storyForm.value.Tanggal === dateStr;
                        } else if (calendarFormContext.value === 'orderanOnline1') {
                            return orderanOnlineForm.value['TANGGAL'] === dateStr;
                        } else if (calendarFormContext.value === 'unitDitanya1') {
                            return unitDitanyaForm.value['TANGGAL'] === dateStr;
                        } else if (calendarFormContext.value === 'claimGaransi1') {
                            return claimGaransiForm.value['TANGGAL_MASUK'] === dateStr;
                        } else if (calendarFormContext.value === 'claimGaransi2') {
                            return claimGaransiForm.value['TANGGAL_DIAMBIL'] === dateStr;
                        } else if (calendarFormContext.value === 'claimGaransi3') {
                            return claimGaransiForm.value['TANGGAL_ESTIMASI'] === dateStr;
                        } else if (calendarFormContext.value === 'distribution') {
                            return distributionForm.value.Tanggal_Publish === dateStr;
                        } else if (calendarFormContext.value === 'analytics') {
                            return analyticsForm.value.Tanggal_Publish === dateStr;
                        } else if (calendarFormContext.value === 'promoDate1') {
                            return promoTempDate.value.start === dateStr;
                        } else if (calendarFormContext.value === 'promoDate2') {
                            return promoTempDate.value.end === dateStr;
                        } else if (calendarFormContext.value === 'sotDate1') {
                            return sellOutForm.value.Periode_Start === dateStr;
                        } else if (calendarFormContext.value === 'sotDate2') {
                            return sellOutForm.value.Periode_End === dateStr;
                        } else if (calendarFormContext.value === 'hargaKompetitorCek') {
                            return hargaKompetitorForm.value.Tanggal_Cek === dateStr;
                        } else if (calendarFormContext.value === 'lpjkTanggal') {
                            return lpjkForm.value.Tanggal === dateStr;
                        } else if (calendarFormContext.value === 'adsTanggal') {
                            return adsForm.value.Tanggal === dateStr;
                        } else if (calendarFormContext.value === 'keepBarangTanggalKeep') {
                            return keepBarangForm.value['TANGGAL_KEEP'] === dateStr;
                        } else if (calendarFormContext.value === 'keepBarangRencanaAmbil') {
                            return keepBarangForm.value['RENCANA_PENGAMBILAN'] === dateStr;
                        } else if (calendarFormContext.value === 'keepBarangDeadlineGudang') {
                            return keepBarangForm.value['DEADLINE_TEAM_GUDANG'] === dateStr;
                        } else if (calendarFormContext.value === 'unboxingUploadDate') {
                            return unboxingForm.value.Upload_Date === dateStr;
                        }
                        return false;
                    } else {
                        const { start, end } = getFilterRef(calendarFormContext.value).value;
                        return start === dateStr || end === dateStr;
                    }
                };

                const isInRange = (day) => {
                    if (calendarMode.value !== 'filter') return false;
                    const { start, end } = getFilterRef(calendarFormContext.value).value;
                    const year = currentDateView.value.getFullYear();
                    const month = currentDateView.value.getMonth();
                    const dateStr = `${year}-${String(month + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;

                    if (start && end) {
                        return dateStr > start && dateStr < end;
                    }
                    if (start && hoveredDate.value) {
                        const s = start < hoveredDate.value ? start : hoveredDate.value;
                        const e = start < hoveredDate.value ? hoveredDate.value : start;
                        return dateStr > s && dateStr < e;
                    }
                    return false;
                };

                const isToday = (day) => {
                    const today = new Date();
                    return today.getDate() === day && today.getMonth() === currentDateView.value.getMonth() && today.getFullYear() === currentDateView.value.getFullYear();
                };

                const isStartDate = (day) => {
                    const year = currentDateView.value.getFullYear();
                    const month = currentDateView.value.getMonth();
                    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                    if (calendarMode.value === 'filter') {
                        return dateStr === getFilterRef(calendarFormContext.value).value.start;
                    }
                    return dateStr === calendarTargetDate.value;
                };

                const isEndDate = (day) => {
                    if (calendarMode.value !== 'filter') return false;
                    const year = currentDateView.value.getFullYear();
                    const month = currentDateView.value.getMonth();
                    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                    return dateStr === getFilterRef(calendarFormContext.value).value.end;
                };

                const changeMonth = (delta) => {
                    const newDate = new Date(currentDateView.value);
                    newDate.setMonth(newDate.getMonth() + delta);
                    currentDateView.value = newDate;
                };

                const changeCalendarMonth = (delta) => {
                    const newDate = new Date(calendarActiveDate.value);
                    newDate.setMonth(newDate.getMonth() + delta);
                    calendarActiveDate.value = newDate;
                };

                const getCalendarDaysInMonth = (date) => {
                    return new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
                };

                const getCalendarEmptyDays = (date) => {
                    return new Date(date.getFullYear(), date.getMonth(), 1).getDay();
                };

                const isTodayCalendar = (day) => {
                    const today = new Date();
                    return today.getDate() === day && today.getMonth() === calendarActiveDate.value.getMonth() && today.getFullYear() === calendarActiveDate.value.getFullYear();
                };

                const getCalendarItems = (day) => {
                    const targetDate = fmtLocalDate(new Date(calendarActiveDate.value.getFullYear(), calendarActiveDate.value.getMonth(), day));

                    const items = masterPlanData.value.filter(item => {
                        const itemDate = item.Tanggal_Rencana ? new Date(item.Tanggal_Rencana).toISOString().split('T')[0] : null;
                        return itemDate === targetDate;
                    }).map(i => ({ ...i, TYPE: 'content' }));

                    const stories = storyData.value.filter(story => {
                        if (!story.Tanggal) return false;
                        const storyDate = new Date(story.Tanggal).toISOString().split('T')[0];
                        return storyDate === targetDate;
                    }).map(s => ({ ...s, TYPE: 'story' }));

                    const events = calendarEventsData.value.filter(ev => {
                        if (!ev.Tanggal) return false;
                        const evDate = new Date(ev.Tanggal).toISOString().split('T')[0];
                        return evDate === targetDate;
                    }).map(e => ({ ...e, TYPE: 'event' }));

                    return [...items, ...stories, ...events];
                };

                // getCalendarDaysInMonth returns a NUMBER (day count), so iterate manually.
                const calendarMonthIsEmpty = computed(() => {
                    const days = getCalendarDaysInMonth(calendarActiveDate.value);
                    for (let d = 1; d <= days; d += 1) {
                        if (getCalendarItems(d).length > 0) return false;
                    }
                    return true;
                });

                const openCalendarDayModal = (day) => {
                    const year = calendarActiveDate.value.getFullYear();
                    const month = calendarActiveDate.value.getMonth();
                    calendarDayModalDate.value = `${day} ${monthNames[month]} ${year}`;
                    calendarDayModalItems.value = getCalendarItems(day);
                    calendarDayModalOpen.value = true;
                };
@endverbatim
