@verbatim
                const calendarDayModalDate = ref("");

                const calendarDayModalItems = ref([]);

                const changeCalendarMonth = (delta) => {
                    const newDate = new Date(calendarActiveDate.value);
                    newDate.setMonth(newDate.getMonth() + delta);
                    calendarActiveDate.value = newDate;
                };

                const getCalendarEmptyDays = (date) => {
                    return new Date(date.getFullYear(), date.getMonth(), 1).getDay();
                };

                const isTodayCalendar = (day) => {
                    const today = new Date();
                    return today.getDate() === day && today.getMonth() === calendarActiveDate.value.getMonth() && today.getFullYear() === calendarActiveDate.value.getFullYear();
                };

                const openCalendarDayModal = (day) => {
                    const year = calendarActiveDate.value.getFullYear();
                    const month = calendarActiveDate.value.getMonth();
                    calendarDayModalDate.value = `${day} ${monthNames[month]} ${year}`;
                    calendarDayModalItems.value = getCalendarItems(day);
                    calendarDayModalOpen.value = true;
                };

                Object.assign(menuExports, {
                    calendarDayModalDate,
                    calendarDayModalItems,
                    changeCalendarMonth,
                    getCalendarEmptyDays,
                    isTodayCalendar,
                    openCalendarDayModal,
                });
@endverbatim
