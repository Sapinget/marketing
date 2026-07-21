@verbatim
<div v-if="activeTab === 'calendar'" class="space-y-6 animate-fadeIn pb-10">
    <section class="section-card section-card-body">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 md:gap-5">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-amber text-light flex items-center justify-center border border-amber">
                    <i class="fa-solid fa-calendar-days text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Content Calendar</h2>
                    <p class="type-body text-slate-500">Jadwal publikasi konten bulanan</p>
                </div>
            </div>
        </div>
    </section>

    <div class="flex justify-center">
        <div class="inline-flex items-center gap-1 bg-slate-50 border border-slate-100 rounded-2xl p-1">
            <button @click="changeCalendarMonth(-1)" aria-label="Bulan sebelumnya"
                class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:bg-white hover:text-ppp-accent transition-all active:scale-95">
                <i class="fa-solid fa-chevron-left text-body"></i></button>
            <div class="px-4 min-w-[150px] text-center text-heading-sm font-bold text-slate-700 tracking-wide">
                {{ monthNames[calendarActiveDate.getMonth()] }} {{ calendarActiveDate.getFullYear() }}</div>
            <button @click="changeCalendarMonth(1)" aria-label="Bulan berikutnya"
                class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:bg-white hover:text-ppp-accent transition-all active:scale-95">
                <i class="fa-solid fa-chevron-right text-body"></i></button>
        </div>
    </div>

    <div class="bg-white radius-dialog border border-slate-100 p-4 md:p-8">
        <div class="md:hidden space-y-2">
            <template v-for="day in getCalendarDaysInMonth(calendarActiveDate)" :key="'mlist-'+day">
                <div @click="getCalendarItems(day).length > 0 && openCalendarDayModal(day)"
                    :class="['flex items-center gap-3 p-2.5 rounded-xl border transition-all', isTodayCalendar(day) ? 'bg-ppp-accent/5 border-ppp-accent/30' : 'bg-white border-slate-100', getCalendarItems(day).length > 0 ? 'cursor-pointer active:scale-[0.99]' : 'opacity-55']">
                    <div
                        :class="['flex flex-col items-center justify-center w-11 h-11 rounded-xl shrink-0', isTodayCalendar(day) ? 'bg-ppp-accent text-white' : 'bg-secondary text-light']">
                        <span class="text-heading-sm font-bold leading-none">{{ day }}</span>
                        <span class="text-overline-xs font-bold uppercase tracking-wider opacity-70">{{ ['Min','Sen','Sel','Rab','Kam','Jum','Sab'][new Date(calendarActiveDate.getFullYear(), calendarActiveDate.getMonth(), day).getDay()] }}</span>
                    </div>
                    <div class="flex-1 min-w-0 flex flex-wrap items-center gap-1.5">
                        <span v-if="getCalendarItems(day).filter(i => i.TYPE === 'content').length > 0"
                            class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-amber text-light text-overline font-bold"><i
                                class="fa-solid fa-photo-film text-overline-xs"></i> Konten {{ getCalendarItems(day).filter(i => i.TYPE === 'content').length }}</span>
                        <span v-if="getCalendarItems(day).filter(i => i.TYPE === 'story').length > 0"
                            class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-danger text-light text-overline font-bold"><i
                                class="fa-solid fa-clapperboard text-overline-xs"></i> Story {{ getCalendarItems(day).filter(i => i.TYPE === 'story').length }}</span>
                        <span v-if="getCalendarItems(day).filter(i => i.TYPE === 'event').length > 0"
                            class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-amber text-light text-overline font-bold"><i
                                class="fa-solid fa-star text-overline-xs"></i> Event {{ getCalendarItems(day).filter(i => i.TYPE === 'event').length }}</span>
                        <span v-if="getCalendarItems(day).length === 0"
                            class="text-body-sm text-slate-300 italic">Tidak ada jadwal</span>
                    </div>
                    <i v-if="getCalendarItems(day).length > 0"
                        class="fa-solid fa-chevron-right text-slate-300 text-body-sm"></i>
                </div>
            </template>
        </div>

        <div class="hidden md:block">
            <div class="grid grid-cols-7 mb-4">
                <div v-for="day in ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']" :key="day"
                    class="text-center text-body-sm font-bold text-slate-400 uppercase tracking-widest py-2">
                    {{ day }}</div>
            </div>
            <div class="grid grid-cols-7 gap-4">
                <div v-for="empty in getCalendarEmptyDays(calendarActiveDate)" :key="'empty-'+empty"
                    class="min-h-[140px] bg-slate-50/50 rounded-2xl border border-dashed border-slate-100">
                </div>
                <div v-for="day in getCalendarDaysInMonth(calendarActiveDate)" :key="day"
                    @click="openCalendarDayModal(day)"
                    :class="['min-h-[140px] rounded-2xl border p-3 transition-all group relative cursor-pointer', isTodayCalendar(day) ? 'bg-ppp-accent/5 border-ppp-accent/20' : 'bg-white border-slate-100 hover:border-ppp-accent/30 ']">
                    <div class="flex items-center justify-between mb-2">
                        <span
                            :class="['text-body font-bold', isTodayCalendar(day) ? 'text-ppp-accent' : 'text-slate-400 group-hover:text-slate-600']">{{ day }}</span>
                        <div v-if="getCalendarItems(day).length > 0"
                            class="w-1.5 h-1.5 rounded-full bg-ppp-accent"></div>
                    </div>
                    <div class="space-y-1.5">
                        <div v-for="item in getCalendarItems(day).filter(i => i.TYPE === 'content')"
                            :key="item.ID" @click.stop="openEditModal(item)"
                            class="px-2 py-1 rounded-lg bg-slate-50 border border-slate-100 flex items-center gap-2 cursor-pointer hover:bg-slate-50 hover:border-slate-100 transition-all group/item">
                            <i
                                :class="getPlatformIcon(item.Platform || (item.Platforms || '').split(',')[0]) + ' text-body text-slate-400'"></i>
                            <div
                                class="text-body font-bold text-slate-700 truncate">
                                {{ item.Judul }}</div>
                        </div>

                        <div v-for="story in getCalendarItems(day).filter(i => i.TYPE === 'story')"
                            :key="'story-'+story.ID" @click.stop="openEditStoryModal(story)"
                            class="px-2 py-1 rounded-lg bg-danger border border-danger flex items-center gap-1.5 cursor-pointer hover:bg-danger hover:border-danger transition-all group/story">
                            <i
                                class="fa-solid fa-clapperboard text-overline-xs text-danger group-hover/story:text-white"></i>
                            <div
                                class="text-overline-xs font-bold text-danger truncate group-hover/story:text-white">
                                {{ story.Jam ? `${story.Jam} | ` : '' }}{{ story.Story_Schedule || story.Story }}
                            </div>
                        </div>

                        <div v-for="event in getCalendarItems(day).filter(i => i.TYPE === 'event')"
                            :key="'event-'+event.ID"
                            class="px-2 py-1 rounded-lg border border-amber bg-amber flex items-center gap-1.5 text-light">
                            <i class="fa-solid fa-star text-overline-xs text-amber"></i>
                            <div
                                class="text-overline-xs font-black truncate uppercase tracking-tighter text-amber">
                                {{ event.Nama_Event }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endverbatim
