@verbatim
<!-- Bonus Report tab -->
                    <div v-if="activeTab === 'bonus_report'" class="space-y-4 animate-fadeIn">
                        <template v-if="!bonusConfigLoaded">
                        <div class="space-y-6 animate-pulse">
                        <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
                            <div v-for="i in 4" :key="'sk-br-st'+i"
                                class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                                <div class="w-10 h-10 rounded-2xl bg-slate-200 mb-4"></div>
                                <div class="h-3 bg-slate-100 rounded-full w-20 mb-2"></div>
                                <div class="h-5 bg-slate-200 rounded-full w-16"></div>
                            </div>
                        </div>
                        <div class="section-card section-card-body">
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-200"></div>
                                    <div class="space-y-2">
                                        <div class="h-5 bg-slate-200 rounded-full w-40"></div>
                                        <div class="h-3 bg-slate-100 rounded-full w-56"></div>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <div class="h-9 w-24 bg-slate-100 rounded-xl"></div>
                                    <div class="h-9 w-24 bg-slate-100 rounded-xl"></div>
                                </div>
                            </div>
                        </div>
                        <div class="section-card section-card-shell">
                            <div class="px-6 py-4 border-b border-slate-50 flex gap-6">
                                <div class="h-3 bg-slate-200 rounded-full w-32"></div>
                                <div class="h-3 bg-slate-200 rounded-full w-20"></div>
                                <div class="h-3 bg-slate-200 rounded-full w-16"></div>
                                <div class="h-3 bg-slate-200 rounded-full w-16"></div>
                                <div class="h-3 bg-slate-200 rounded-full flex-1"></div>
                            </div>
                            <div class="divide-y divide-slate-50">
                                <div v-for="i in 6" :key="'sk-br'+i" class="px-6 py-5 flex items-center gap-4">
                                    <div class="h-4 bg-slate-100 rounded-full w-48"></div>
                                    <div class="h-4 bg-slate-100 rounded-full w-20"></div>
                                    <div class="h-4 bg-slate-100 rounded-full w-16"></div>
                                    <div class="h-4 bg-slate-100 rounded-full w-16"></div>
                                    <div class="h-4 bg-slate-100 rounded-full flex-1"></div>
                                </div>
                            </div>
                        </div>
                        </div>
                        </template>
                        <template v-else>

                        <!-- Summary Cards (dipisah dari header, diletakkan di atas) -->
                        <div class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
                            <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                                <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-coins text-[120px]"></i></div>
                                <p class="dashboard-summary-title">Total Bonus</p>
                                <div class="flex items-baseline gap-2">
                                    <span class="dashboard-summary-value">{{ formatCurrency(bonusTotal.totalMoney) }}</span>
                                </div>
                                <p class="text-body-sm font-bold text-amber mt-3">{{ bonusTotal.count }} konten</p>
                            </div>
                            <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                                <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-eye text-[120px]"></i></div>
                                <p class="dashboard-summary-title">Views</p>
                                <div class="flex items-baseline gap-2">
                                    <span class="dashboard-summary-value">{{ formatNumber(bonusTotal.views) }}</span>
                                    <span class="dashboard-summary-unit">Tayangan</span>
                                </div>
                                <p class="text-body-sm font-bold text-amber mt-3">Dalam periode aktif</p>
                            </div>
                            <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                                <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-heart text-[120px]"></i></div>
                                <p class="dashboard-summary-title">Likes</p>
                                <div class="flex items-baseline gap-2">
                                    <span class="dashboard-summary-value">{{ formatNumber(bonusTotal.likes) }}</span>
                                    <span class="dashboard-summary-unit">Suka</span>
                                </div>
                                <p class="text-body-sm font-bold text-danger mt-3">Dalam periode aktif</p>
                            </div>
                            <div class="dashboard-summary-card-compact stat-card relative overflow-hidden group">
                                <div class="absolute -right-4 -bottom-4 opacity-5"><i class="fa-solid fa-comment text-[120px]"></i></div>
                                <p class="dashboard-summary-title">Komentar</p>
                                <div class="flex items-baseline gap-2">
                                    <span class="dashboard-summary-value">{{ formatNumber(bonusTotal.comments) }}</span>
                                    <span class="dashboard-summary-unit">Komen</span>
                                </div>
                                <p class="text-body-sm font-bold text-success mt-3">Dalam periode aktif</p>
                            </div>
                        </div>

                        <!-- Header & Stats -->
                        <section class="section-card section-card-body">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-amber text-light flex items-center justify-center border border-amber">
                                        <i class="fa-solid fa-coins text-heading-lg"></i>
                                    </div>
                                    <div>
                                        <h2 class="type-heading-sm font-bold text-slate-900">Bonus Report</h2>
                                        <p class="text-body-sm text-slate-400 uppercase mt-0.5">Kalkulasi
                                            bonus performa konten</p>
                                    </div>
                                </div>
                                <div class="mobile-toolbar-stack">
                                    <div class="compact-period-toolbar">
                                        <div class="compact-period-toolbar__label">
                                            <i class="fa-solid fa-calendar text-body-sm"></i>
                                            <span>Periode</span>
                                        </div>
                                        <div class="compact-period-toolbar__controls">
                                            <div class="relative search-select-container">
                                                <button type="button" @click="toggleSearchSelect($event, 'bonus_month')"
                                                    class="select-trigger-button select-trigger-button-compact">
                                                    <span>{{ monthOptionLabel(bonusMonth) }}</span>
                                                    <i class="fa-solid fa-chevron-down text-overline text-slate-400"></i>
                                                </button>
                                                <div v-if="searchSelectOpen === 'bonus_month'" :style="popoverStyle"
                                                    class="search-select-popover search-select-popover--compact max-h-72 overflow-y-auto">
                                                    <div v-for="(name, idx) in monthNames" :key="'bm'+idx"
                                                        @click="bonusMonth = idx + 1; searchSelectOpen = null"
                                                        class="popover-option">
                                                        {{ name }}</div>
                                                </div>
                                            </div>
                                            <div class="relative search-select-container">
                                                <button type="button" @click="toggleSearchSelect($event, 'bonus_year')"
                                                    class="select-trigger-button select-trigger-button-compact">
                                                    <span>{{ bonusYear }}</span>
                                                    <i class="fa-solid fa-chevron-down text-overline text-slate-400"></i>
                                                </button>
                                                <div v-if="searchSelectOpen === 'bonus_year'" :style="popoverStyle"
                                                    class="search-select-popover search-select-popover--compact max-h-60 overflow-y-auto">
                                                    <div v-for="y in availableYears" :key="'by'+y"
                                                        @click="bonusYear = y; searchSelectOpen = null"
                                                        class="popover-option">
                                                        {{ y }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="toolbar-actions">
                                            <button @click="exportBonusToExcel"
                                                class="primary-cta-button primary-cta-button--success active:scale-95">
                                                <i class="fa-solid fa-file-excel"></i> Excel
                                            </button>
                                            <button @click="exportBonusToPDF"
                                                class="primary-cta-button primary-cta-button--danger active:scale-95">
                                                <i class="fa-solid fa-file-pdf"></i> PDF
                                            </button>
                                            <button @click="showBonusSettings = !showBonusSettings"
                                                :class="['primary-cta-button active:scale-95', showBonusSettings ? 'bg-slate-900 text-white border-slate-900 hover:bg-black' : 'primary-cta-button--neutral']">
                                                <i class="fa-solid fa-sliders text-body-sm"></i> Matrix
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Bonus Matrix Config -->
                        <section v-if="showBonusSettings"
                            class="section-card section-card-body animate-fadeIn">
                            <h3
                                class="text-body font-bold text-slate-700 uppercase mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-sliders text-ppp-accent"></i> Konfigurasi Bonus Matrix
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                <!-- Non-Colab -->
                                <div>
                                    <p class="text-overline font-bold text-slate-400 uppercase mb-3"><i
                                            class="fa-solid fa-video text-amber mr-1"></i> Views Non-Colab</p>
                                    <div class="space-y-2">
                                        <div v-for="(tier, idx) in bonusConfig.reelsNonColab" :key="'nc'+idx"
                                            class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 flex items-center justify-between gap-3">
                                            <div>
                                                <p class="text-overline-xs text-slate-400 font-bold uppercase">Min Views</p>
                                                <label :for="`bonus-reels-non-colab-min-${idx}`" class="sr-only">Min views non colab {{ idx + 1 }}</label>
                                                <input :id="`bonus-reels-non-colab-min-${idx}`" :name="`bonus_reels_non_colab_min_${idx}`" type="number" v-model.number="tier.min"
                                                    class="w-20 text-body font-bold text-slate-700 bg-transparent outline-none" />
                                            </div>
                                            <div class="text-right">
                                                <p class="text-overline-xs text-slate-400 font-bold uppercase">Bonus (Rp)</p>
                                                <label :for="`bonus-reels-non-colab-amount-${idx}`" class="sr-only">Bonus non colab {{ idx + 1 }}</label>
                                                <input :id="`bonus-reels-non-colab-amount-${idx}`" :name="`bonus_reels_non_colab_amount_${idx}`" type="number" v-model.number="tier.amount"
                                                    class="w-24 text-body font-bold text-ppp-accent bg-transparent outline-none text-right" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Colab -->
                                <div>
                                    <p class="text-overline font-bold text-slate-400 uppercase mb-3"><i
                                            class="fa-solid fa-handshake text-slate-400 mr-1"></i> Views Colab</p>
                                    <div class="space-y-2">
                                        <div v-for="(tier, idx) in bonusConfig.reelsColab" :key="'c'+idx"
                                            class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 flex items-center justify-between gap-3">
                                            <div>
                                                <p class="text-overline-xs text-slate-400 font-bold uppercase">Min Views</p>
                                                <label :for="`bonus-reels-colab-min-${idx}`" class="sr-only">Min views colab {{ idx + 1 }}</label>
                                                <input :id="`bonus-reels-colab-min-${idx}`" :name="`bonus_reels_colab_min_${idx}`" type="number" v-model.number="tier.min"
                                                    class="w-20 text-body font-bold text-slate-700 bg-transparent outline-none" />
                                            </div>
                                            <div class="text-right">
                                                <p class="text-overline-xs text-slate-400 font-bold uppercase">Bonus (Rp)</p>
                                                <label :for="`bonus-reels-colab-amount-${idx}`" class="sr-only">Bonus colab {{ idx + 1 }}</label>
                                                <input :id="`bonus-reels-colab-amount-${idx}`" :name="`bonus_reels_colab_amount_${idx}`" type="number" v-model.number="tier.amount"
                                                    class="w-24 text-body font-bold text-success bg-transparent outline-none text-right" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Engagement -->
                                <div>
                                    <p class="text-overline font-bold text-slate-400 uppercase mb-3"><i
                                            class="fa-solid fa-medal text-amber mr-1"></i> Engagement Fixed</p>
                                    <div class="space-y-2">
                                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5">
                                            <p class="text-overline-xs text-slate-400 font-bold uppercase mb-1">Instagram
                                                Likes Min</p>
                                            <div class="flex justify-between items-center">
                                                <label for="bonus-instagram-like-unit" class="sr-only">Instagram likes min</label>
                                                <input id="bonus-instagram-like-unit" name="bonus_instagram_like_unit" type="number"
                                                    v-model.number="bonusConfig.engagement.instagram.likeUnit"
                                                    class="w-20 text-body font-bold text-slate-700 bg-transparent outline-none" />
                                                <div class="text-right">
                                                    <p class="text-overline-xs text-slate-400 font-bold uppercase">Bonus</p>
                                                    <label for="bonus-instagram-like-bonus" class="sr-only">Instagram likes bonus</label>
                                                    <input id="bonus-instagram-like-bonus" name="bonus_instagram_like_bonus" type="number"
                                                        v-model.number="bonusConfig.engagement.instagram.likeBonus"
                                                        class="w-24 text-body font-bold text-ppp-accent bg-transparent outline-none text-right" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5">
                                            <p class="text-overline-xs text-slate-400 font-bold uppercase mb-1">TikTok Likes
                                                Min</p>
                                            <div class="flex justify-between items-center">
                                                <label for="bonus-tiktok-like-unit" class="sr-only">TikTok likes min</label>
                                                <input id="bonus-tiktok-like-unit" name="bonus_tiktok_like_unit" type="number" v-model.number="bonusConfig.engagement.tiktok.likeUnit"
                                                    class="w-20 text-body font-bold text-slate-700 bg-transparent outline-none" />
                                                <div class="text-right">
                                                    <p class="text-overline-xs text-slate-400 font-bold uppercase">Bonus</p>
                                                    <label for="bonus-tiktok-like-bonus" class="sr-only">TikTok likes bonus</label>
                                                    <input id="bonus-tiktok-like-bonus" name="bonus_tiktok_like_bonus" type="number"
                                                        v-model.number="bonusConfig.engagement.tiktok.likeBonus"
                                                        class="w-24 text-body font-bold text-ppp-accent bg-transparent outline-none text-right" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5">
                                            <p class="text-overline-xs text-slate-400 font-bold uppercase mb-1">Comments Min
                                            </p>
                                            <div class="flex justify-between items-center">
                                                <label for="bonus-comment-unit" class="sr-only">Comments min</label>
                                                <input id="bonus-comment-unit" name="bonus_comment_unit" type="number"
                                                    v-model.number="bonusConfig.engagement.general.commentUnit"
                                                    class="w-20 text-body font-bold text-slate-700 bg-transparent outline-none" />
                                                <div class="text-right">
                                                    <p class="text-overline-xs text-slate-400 font-bold uppercase">Bonus</p>
                                                    <label for="bonus-comment-bonus" class="sr-only">Comments bonus</label>
                                                    <input id="bonus-comment-bonus" name="bonus_comment_bonus" type="number"
                                                        v-model.number="bonusConfig.engagement.general.commentBonus"
                                                        class="w-24 text-body font-bold text-ppp-accent bg-transparent outline-none text-right" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-end mt-5">
                                <button @click="saveBonusConfig"
                                    class="primary-cta-button primary-cta-button--accent hover:bg-amber active:scale-95">
                                    <i class="fa-solid fa-floppy-disk mr-1.5"></i> Simpan Matrix
                                </button>
                            </div>
                        </section>

                    <!-- Tabel Bonus -->
                    <div class="hidden md:block section-card section-card-shell">
                        <div class="overflow-x-auto">
                            <table class="w-full text-body-sm text-left border-collapse min-w-[900px]">
                                <thead class="bg-slate-50">
                                    <tr class="table-header-row">
                                        <th class="table-header-cell table-header-index table-freeze-index">
                                            #</th>
                                        <th class="table-header-cell table-header-action table-freeze-action">
                                            Aksi</th>
                                        <th class="table-header-cell">
                                            Judul Konten</th>
                                        <th class="table-header-cell">
                                            Platform</th>
                                        <th class="table-header-cell">
                                            Editor</th>
                                        <th class="table-header-cell text-center">
                                            Tipe</th>
                                        <th class="table-header-cell text-right">
                                            Views</th>
                                        <th class="table-header-cell text-right">
                                            Likes</th>
                                        <th class="table-header-cell text-right">
                                            Komen</th>
                                        <th class="table-header-cell text-right">
                                            Total Bonus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="pagedBonusRows.length === 0">
                                        <td colspan="10" class="px-5 py-16 text-center text-body text-slate-400">
                                            <i class="fa-solid fa-coins text-3xl mb-3 opacity-20 block"></i>
                                            Belum ada data bonus pada periode ini
                                        </td>
                                    </tr>
                                    <tr v-for="(row, idx) in pagedBonusRows" :key="row.id || idx"
                                        class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                        <td class="px-5 py-3.5 text-body-sm text-slate-400 table-freeze-index">{{ (bonusPage - 1) * 15 + idx + 1 }}</td>
                                        <td class="px-5 py-3.5 text-center table-freeze-action">
                                            <button v-if="row.masterPlan && row.masterPlan.ID"
                                                @click.stop="openEditModal(row.masterPlan)"
                                                class="table-action-button table-action-compact" title="Edit" aria-label="Edit">
                                                <i class="fa-solid fa-pen-to-square text-body-sm"></i>
                                            </button>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <div class="text-body font-semibold text-slate-800 leading-tight">{{ row.Judul || '-' }}</div>
                                            <div class="text-overline text-slate-400 mt-0.5">{{ formatShortDate(row.date) }}</div>
                                        </td>
                                        <td class="px-6 py-5 text-left">
                                            <div class="flex items-center gap-2">
                                                <i :class="getPlatformIcon(row.Platform) + ' text-body text-slate-400'"></i>
                                                <span class="text-body font-bold text-slate-700">{{ platformDisplayName(row.Platform) }}</span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3.5 text-left">
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-overline font-bold text-slate-600 flex-shrink-0 overflow-hidden">
                                                    <img v-if="resolveUserAvatarUrl(row.Editor)"
                                                        :src="resolveAvatarUrl(resolveUserAvatarUrl(row.Editor))"
                                                        class="w-full h-full object-cover" alt="Foto Editor"
                                                        @error="markMasterPlanEditorAvatarFailed(row.Editor)" />
                                                    <span v-else>{{ masterPersonInitials(row.Editor) }}</span>
                                                </div>
                                                <div
                                                    class="text-body text-slate-700 font-semibold truncate max-w-[80px]">
                                                    {{ personDisplayName(row.Editor) }}</div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3.5 text-center">
                                            <span
                                                :class="['inline-flex px-2 py-0.5 rounded-lg text-overline font-bold border uppercase', row.contentType === 'Ad' ? 'bg-amber text-light border-amber' : row.contentType === 'Colab' ? 'bg-secondary text-light border-slate-100' : 'bg-secondary text-light border-slate-100']">{{ row.contentType || 'Regular' }}</span>
                                        </td>
                                        <td class="px-5 py-3.5 text-right">
                                            <div class="text-body font-bold text-slate-700">{{ formatNumber(row.Views || 0) }}</div>
                                            <div v-if="row.viewBonus > 0" class="text-overline text-ppp-accent font-bold">
                                                +{{ formatCurrency(row.viewBonus) }}</div>
                                        </td>
                                        <td class="px-5 py-3.5 text-right">
                                            <div class="text-body font-bold text-slate-700">{{ formatNumber(row.Likes || 0) }}</div>
                                            <div v-if="row.likeBonus > 0" class="text-overline text-danger font-bold">+{{ formatCurrency(row.likeBonus) }}</div>
                                        </td>
                                        <td class="px-5 py-3.5 text-right">
                                            <div class="text-body font-bold text-slate-700">{{ formatNumber(row.Comments || 0) }}</div>
                                            <div v-if="row.commentBonus > 0"
                                                class="text-overline text-success font-bold">+{{ formatCurrency(row.commentBonus) }}</div>
                                        </td>
                                        <td class="px-5 py-3.5 text-right">
                                            <span class="text-heading-sm font-extrabold text-slate-900">{{ formatCurrency(row.calculatedBonus || 0) }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div
                            class="table-pager-bar">
                            <div class="text-body-sm text-slate-400 font-medium">
                                <template v-if="filteredBonusRows.length > 0">{{ (bonusPage - 1) * 15 + 1 }}-{{ Math.min(bonusPage * 15, filteredBonusRows.length) }} dari {{ filteredBonusRows.length }} data</template>
                                <template v-else>0 data</template>
                            </div>
                            <div class="flex items-center gap-1">
                                <button @click="bonusPage--" :disabled="bonusPage <= 1" aria-label="Halaman sebelumnya"
                                    class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-left text-body-sm"></i></button>
                                <span class="px-3 text-body-sm font-bold text-ppp-accent">{{ bonusPage }} / {{ bonusTotalPages }}</span>
                                <button @click="bonusPage++" :disabled="bonusPage >= bonusTotalPages"
                                    aria-label="Halaman berikutnya" class="icon-utility-button icon-utility-bordered"><i
                                        class="fa-solid fa-chevron-right text-body-sm"></i></button>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile cards -->
                    <div class="md:hidden space-y-3">
                        <div v-if="pagedBonusRows.length === 0"
                            class="bg-white radius-panel border border-slate-100 p-10 text-center text-body text-slate-400">
                            Belum ada data bonus</div>
                        <div v-for="(row, idx) in pagedBonusRows" :key="'mb'+idx"
                            class="stat-card mobile-record-card mobile-data-card motion-stagger-item"
                            :style="getStaggerStyle(idx)">
                            <div class="mobile-data-card__header">
                                <div>
                                    <p class="mobile-data-card__title">{{ row.Judul || '-' }}</p>
                                    <div class="mt-1.5 flex items-center gap-2">
                                        <div class="flex items-center gap-2">
                                            <i :class="getPlatformIcon(row.Platform) + ' text-body text-slate-400'"></i>
                                            <span class="text-body font-bold text-slate-700">{{ platformDisplayName(row.Platform) }}</span>
                                        </div>
                                        <span class="text-overline text-slate-300">|</span>
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-overline font-bold text-slate-600 flex-shrink-0 overflow-hidden">
                                                <img v-if="resolveUserAvatarUrl(row.Editor)"
                                                    :src="resolveAvatarUrl(resolveUserAvatarUrl(row.Editor))"
                                                    class="w-full h-full object-cover" alt="Foto Editor"
                                                    @error="markMasterPlanEditorAvatarFailed(row.Editor)" />
                                                <span v-else>{{ masterPersonInitials(row.Editor) }}</span>
                                            </div>
                                            <div class="text-body text-slate-700 font-semibold truncate max-w-[80px]">{{ personDisplayName(row.Editor) }}</div>
                                        </div>
                                        <span class="text-overline text-slate-300">|</span>
                                        <span class="text-overline text-slate-400">{{ formatShortDate(row.date) }}</span>
                                    </div>
                                </div>
                                <span
                                    :class="['inline-flex px-2 py-0.5 rounded-lg text-overline-xs font-bold border uppercase whitespace-nowrap', row.contentType === 'Ad' ? 'bg-amber text-light border-amber' : row.contentType === 'Colab' ? 'bg-secondary text-light border-slate-100' : 'bg-secondary text-light border-slate-100']">{{ row.contentType || 'Regular' }}</span>
                            </div>
                            <div class="mobile-data-card__summary">
                                <div>
                                    <div class="type-body-sm text-slate-400 uppercase">Views</div>
                                    <div class="type-body font-bold text-slate-700">{{ formatNumber(row.Views || 0) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="type-body-sm text-slate-400 uppercase">Bonus</div>
                                    <div class="type-body font-extrabold text-ppp-accent">{{ formatCurrency(row.calculatedBonus || 0) }}</div>
                                </div>
                            </div>
                            <div v-if="row.masterPlan && row.masterPlan.ID" class="mobile-data-card__actions">
                                <div class="type-body-sm text-slate-400">Aksi terkait</div>
                                <button @click.stop="openEditModal(row.masterPlan)"
                                    class="primary-cta-button primary-cta-button--link">
                                    <i class="fa-solid fa-pen-to-square text-overline"></i>
                                    Edit Master Plan
                                </button>
                            </div>
                        </div>
                    </div>
                        </template>

            </div>
@endverbatim
