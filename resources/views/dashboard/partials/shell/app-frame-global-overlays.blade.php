@verbatim
    <teleport to="body">
        <transition name="fade">
            <div v-if="calendarOpen" class="calendar-popover-layer">
                <div class="calendar-popover-panel animate-fadeIn" :style="calendarAnchorStyle">
                    <div class="calendar-popover-value">
                        <div class="min-w-0">
                            <div class="calendar-popover-label">{{ calendarMode === 'filter' ? 'Range Tanggal' : 'Tanggal' }}</div>
                            <div class="calendar-popover-date truncate">{{ formatFullDate(calendarTargetDate) || 'Pilih tanggal' }}</div>
                        </div>
                        <button type="button" @click="calendarOpen = false" class="calendar-popover-close" aria-label="Tutup kalender">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="calendar-popover-body">
                        <div class="calendar-popover-monthbar">
                            <button @click="changeMonth(-1)" aria-label="Bulan sebelumnya"
                                class="icon-utility-button icon-utility-round"><i
                                    class="fa-solid fa-chevron-left text-body-sm"></i></button>
                            <div class="calendar-popover-month">{{ monthNames[currentDateView.getMonth()] }} {{ currentDateView.getFullYear() }}</div>
                            <button @click="changeMonth(1)" aria-label="Bulan berikutnya"
                                class="icon-utility-button icon-utility-round"><i
                                    class="fa-solid fa-chevron-right text-body-sm"></i></button>
                        </div>
                        <div class="calendar-popover-weekdays">
                            <div v-for="day in ['S', 'S', 'R', 'K', 'J', 'S', 'M']" :key="day"
                                class="calendar-popover-weekday">{{ day }}</div>
                        </div>
                        <div class="calendar-popover-grid">
                            <div v-for="n in calendarEmptyDays" :key="'empty-'+n" class="py-2"></div>
                            <div v-for="day in calendarDaysInMonth" :key="day" @click="selectDate(day)" @mouseenter="() => {
                                            const year = currentDateView.getFullYear();
                                            const month = currentDateView.getMonth();
                                            hoveredDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                                        }" @mouseleave="hoveredDate = ''"
                                :class="['calendar-day-button', (isStartDate(day) || isEndDate(day) || isSelectedDate(day)) ? 'calendar-day-active' : '', isInRange(day) ? 'calendar-day-range' : '', !isStartDate(day) && !isEndDate(day) && !isSelectedDate(day) && !isInRange(day) ? 'text-slate-600' : '', isToday(day) && !isStartDate(day) && !isEndDate(day) && !isSelectedDate(day) && !isInRange(day) ? 'text-light' : '']">
                                {{ day }}
                                <div v-if="isToday(day)"
                                    :class="['w-1 h-1 rounded-full absolute bottom-1.5', (isStartDate(day) || isEndDate(day) || isSelectedDate(day)) ? 'bg-white' : 'bg-amber']">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="calendar-popover-footer">
                        <button @click="resetCalendar"
                            class="calendar-footer-action text-slate-400 hover:text-danger">Reset</button>
                        <button @click="calendarOpen = false"
                            class="calendar-footer-action text-amber">Selesai</button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>

    <!-- Custom Confirmation Modal -->
    <teleport to="body">
        <transition name="fade">
            <div v-if="confirmModal.open" class="fixed inset-0 z-[3000] flex items-center justify-center p-4 overlay-motion-dialog">
                <div @click="confirmModal.open = false" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm overlay-backdrop">
                </div>
                <div
                    class="modal-width-compact radius-dialog modal-dialog-surface overlay-dialog-surface">
                    <div class="p-8 text-center">
                        <div
                            :class="['w-16 h-16 rounded-3xl flex items-center justify-center mx-auto mb-5', confirmModal.type === 'danger' ? 'bg-danger text-light' : 'bg-amber text-light']">
                            <i
                                :class="['fa-solid text-2xl', confirmModal.type === 'danger' ? 'fa-trash-can' : 'fa-circle-info']"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mb-2">{{ confirmModal.title }}</h3>
                        <p class="text-body text-slate-500 leading-relaxed">{{ confirmModal.message }}</p>
                    </div>
                    <div class="flex gap-3 p-4 border-t border-slate-100 bg-slate-50/80">
                        <button @click="confirmModal.open = false" class="primary-cta-button primary-cta-button--neutral flex-1">Batal</button>
                        <button @click="confirmModal.onConfirm(); confirmModal.open = false"
                            :class="['primary-cta-button flex-1', confirmModal.type === 'danger' ? 'primary-cta-button--danger' : 'primary-cta-button--info']">Ya,
                            Lanjutkan</button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
@endverbatim
