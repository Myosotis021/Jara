/**
 * Apple Custom Date & Time Picker Popup Window
 */

export function initAppleDateTimePickers() {
    document.querySelectorAll('.apple-datetime-input').forEach(input => {
        if (input.dataset.enhanced) return;
        input.dataset.enhanced = 'true';

        // ------------------------------------------------------------------
        // Build wrapper + hide native input (still used for form submission)
        // ------------------------------------------------------------------
        const wrapper = document.createElement('div');
        wrapper.className = 'apple-datetime-picker-wrapper';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        Object.assign(input.style, {
            position:      'absolute',
            opacity:       '0',
            pointerEvents: 'none',
            width:         '1px',
            height:        '1px',
        });

        // ------------------------------------------------------------------
        // Trigger Button
        // ------------------------------------------------------------------
        const trigger = document.createElement('div');
        trigger.className = 'apple-datetime-trigger';
        trigger.setAttribute('tabindex', '0');
        trigger.setAttribute('role', 'button');
        trigger.setAttribute('aria-haspopup', 'dialog');
        trigger.innerHTML = `
            <div style="display:flex;align-items:center;gap:8px;overflow:hidden;">
                <svg width="16" height="16" fill="none" stroke="#7a7a7a" viewBox="0 0 24 24" style="flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="apple-datetime-display" style="color:#7a7a7a;font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    Pilih tanggal &amp; waktu…
                </span>
            </div>
            <div style="display:flex;align-items:center;gap:4px;flex-shrink:0;">
                <button type="button" class="apple-datetime-clear" style="display:none;background:none;border:none;color:#7a7a7a;cursor:pointer;padding:4px;font-size:16px;line-height:1;" title="Hapus">×</button>
                <svg width="14" height="14" fill="none" stroke="#7a7a7a" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        `;
        wrapper.appendChild(trigger);

        const displayEl = trigger.querySelector('.apple-datetime-display');
        const clearBtn  = trigger.querySelector('.apple-datetime-clear');

        // ------------------------------------------------------------------
        // Popup Window
        // ------------------------------------------------------------------
        const popup = document.createElement('div');
        popup.className = 'apple-datetime-popup';
        popup.setAttribute('role', 'dialog');
        popup.setAttribute('aria-label', 'Pilih Tanggal dan Waktu');
        popup.innerHTML = `
            <!-- Calendar Side Header -->
            <div class="apple-cal-side-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid #f0f0f0;">
                <span class="apple-chip" style="font-size:11px;padding:3px 10px;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;">
                    Atur Tenggat Waktu
                </span>
                <button type="button" class="apple-cal-close" style="background:none;border:none;color:#7a7a7a;font-size:18px;cursor:pointer;padding:2px 6px;line-height:1;border-radius:50%;transition:background-color 0.12s;" title="Tutup">✕</button>
            </div>

            <!-- Calendar Header -->
            <div class="apple-cal-header">
                <button type="button" class="apple-cal-nav-btn apple-cal-prev" aria-label="Bulan sebelumnya">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <span class="apple-cal-month-year" style="font-size:14px;font-weight:600;color:#1d1d1f;letter-spacing:-0.224px;"></span>
                <button type="button" class="apple-cal-nav-btn apple-cal-next" aria-label="Bulan berikutnya">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            <!-- Day-of-week labels -->
            <div class="apple-cal-grid" style="margin-bottom:4px;">
                <span class="apple-cal-day-header">Min</span>
                <span class="apple-cal-day-header">Sen</span>
                <span class="apple-cal-day-header">Sel</span>
                <span class="apple-cal-day-header">Rab</span>
                <span class="apple-cal-day-header">Kam</span>
                <span class="apple-cal-day-header">Jum</span>
                <span class="apple-cal-day-header">Sab</span>
            </div>

            <!-- Day cells -->
            <div class="apple-cal-grid apple-cal-days" style="margin-bottom:4px;"></div>

            <!-- Time Picker -->
            <div class="apple-time-picker-row">
                <span style="font-size:13px;font-weight:600;color:#1d1d1f;display:flex;align-items:center;gap:6px;">
                    <svg width="13" height="13" fill="none" stroke="#7a7a7a" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Waktu
                </span>
                <div style="display:flex;align-items:center;gap:4px;">
                    <div class="apple-custom-time-dropdown" style="position:relative;display:inline-block;">
                        <button type="button" class="apple-time-trigger apple-hour-trigger" style="height:32px;padding:0 8px;background-color:#fafafc;border:1px solid #e0e0e0;border-radius:8px;font-size:13px;font-weight:600;color:#1d1d1f;cursor:pointer;display:inline-flex;align-items:center;gap:3px;">
                            <span class="apple-hour-display">00</span>
                            <svg width="10" height="10" fill="none" stroke="#7a7a7a" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="apple-time-menu apple-hour-menu"></div>
                    </div>
                    <span style="font-weight:600;color:#1d1d1f;font-size:15px;">:</span>
                    <div class="apple-custom-time-dropdown" style="position:relative;display:inline-block;">
                        <button type="button" class="apple-time-trigger apple-minute-trigger" style="height:32px;padding:0 8px;background-color:#fafafc;border:1px solid #e0e0e0;border-radius:8px;font-size:13px;font-weight:600;color:#1d1d1f;cursor:pointer;display:inline-flex;align-items:center;gap:3px;">
                            <span class="apple-minute-display">00</span>
                            <svg width="10" height="10" fill="none" stroke="#7a7a7a" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="apple-time-menu apple-minute-menu"></div>
                    </div>
                </div>
            </div>

            <!-- Quick Presets -->
            <div class="apple-datetime-presets">
                <button type="button" class="apple-chip apple-preset-today"   style="font-size:12px;padding:4px 12px;">Hari ini 18:00</button>
                <button type="button" class="apple-chip apple-preset-tomorrow" style="font-size:12px;padding:4px 12px;">Besok 09:00</button>
                <button type="button" class="apple-preset-clear apple-text-link" style="font-size:12px;padding:4px 8px;">Kosongkan</button>
            </div>

            <!-- Action Buttons -->
            <div class="apple-datetime-actions">
                <button type="button" class="apple-cal-close apple-btn-secondary-compact" style="font-size:13px;padding:6px 16px;">Tutup</button>
                <button type="button" class="apple-cal-apply apple-btn-primary-compact"   style="font-size:13px;padding:6px 16px;">Terapkan</button>
            </div>
        `;

        // Check if this input is inside a modal or page stage with a side companion panel
        const modalHost  = input.closest('#task-create-modal, #task-edit-modal, .apple-modal-page-backdrop');
        const modalStage = modalHost ? modalHost.querySelector('.apple-modal-stage') : null;
        const sidePanel  = modalHost ? modalHost.querySelector('.apple-modal-calendar-panel') : null;

        if (sidePanel) {
            popup.classList.add('apple-datetime-popup-side');
            sidePanel.appendChild(popup);
        } else {
            wrapper.appendChild(popup);
        }

        // ------------------------------------------------------------------
        // DOM references within popup
        // ------------------------------------------------------------------
        const monthYearEl  = popup.querySelector('.apple-cal-month-year');
        const daysGrid     = popup.querySelector('.apple-cal-days');
        const prevBtn      = popup.querySelector('.apple-cal-prev');
        const nextBtn      = popup.querySelector('.apple-cal-next');
        const hourTrigger  = popup.querySelector('.apple-hour-trigger');
        const hourDisplay  = popup.querySelector('.apple-hour-display');
        const hourMenu     = popup.querySelector('.apple-hour-menu');
        const minTrigger   = popup.querySelector('.apple-minute-trigger');
        const minDisplay   = popup.querySelector('.apple-minute-display');
        const minMenu      = popup.querySelector('.apple-minute-menu');
        const applyBtn     = popup.querySelector('.apple-cal-apply');
        const closeBtn     = popup.querySelector('.apple-cal-close');

        // Apply guaranteed popup menu styles to time dropdowns
        const menuBaseStyle = {
            position: 'absolute',
            bottom: 'calc(100% + 6px)',
            left: '50%',
            transform: 'translateX(-50%)',
            width: '60px',
            maxHeight: '140px',
            overflowY: 'auto',
            backgroundColor: '#ffffff',
            border: '1px solid #e0e0e0',
            borderRadius: '10px',
            boxShadow: '0 10px 28px rgba(0, 0, 0, 0.16)',
            zIndex: '200',
            display: 'none',
            padding: '4px',
            boxSizing: 'border-box'
        };
        Object.assign(hourMenu.style, menuBaseStyle);
        Object.assign(minMenu.style, menuBaseStyle);

        let currentHour   = '00';
        let currentMinute = '00';

        function closeTimeMenus() {
            hourMenu.style.display = 'none';
            hourMenu.classList.remove('is-open');
            hourTrigger.classList.remove('is-active');
            minMenu.style.display = 'none';
            minMenu.classList.remove('is-open');
            minTrigger.classList.remove('is-active');
        }

        function renderTimeDropdowns() {
            // Render Hour options (00..23)
            hourMenu.innerHTML = '';
            for (let h = 0; h < 24; h++) {
                const val = String(h).padStart(2, '0');
                const opt = document.createElement('div');
                opt.className = `apple-time-option ${val === currentHour ? 'is-selected' : ''}`;
                opt.textContent = val;
                opt.style.padding = '6px 0';
                opt.style.textAlign = 'center';
                opt.style.fontSize = '13px';
                opt.style.fontWeight = val === currentHour ? '600' : '500';
                opt.style.cursor = 'pointer';
                opt.style.borderRadius = '6px';
                opt.style.backgroundColor = val === currentHour ? '#0066cc' : '';
                opt.style.color = val === currentHour ? '#ffffff' : '#1d1d1f';

                opt.addEventListener('click', (e) => {
                    e.stopPropagation();
                    currentHour = val;
                    hourDisplay.textContent = currentHour;
                    closeTimeMenus();
                    if (selectedDate) {
                        selectedDate.setHours(parseInt(currentHour, 10));
                    }
                    renderTimeDropdowns();
                });
                hourMenu.appendChild(opt);
            }

            // Render Minute options (00, 05, 10, ... 55)
            minMenu.innerHTML = '';
            ['00','05','10','15','20','25','30','35','40','45','50','55'].forEach(m => {
                const opt = document.createElement('div');
                opt.className = `apple-time-option ${m === currentMinute ? 'is-selected' : ''}`;
                opt.textContent = m;
                opt.style.padding = '6px 0';
                opt.style.textAlign = 'center';
                opt.style.fontSize = '13px';
                opt.style.fontWeight = m === currentMinute ? '600' : '500';
                opt.style.cursor = 'pointer';
                opt.style.borderRadius = '6px';
                opt.style.backgroundColor = m === currentMinute ? '#0066cc' : '';
                opt.style.color = m === currentMinute ? '#ffffff' : '#1d1d1f';

                opt.addEventListener('click', (e) => {
                    e.stopPropagation();
                    currentMinute = m;
                    minDisplay.textContent = currentMinute;
                    closeTimeMenus();
                    if (selectedDate) {
                        selectedDate.setMinutes(parseInt(currentMinute, 10));
                    }
                    renderTimeDropdowns();
                });
                minMenu.appendChild(opt);
            });
        }

        hourTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = hourMenu.style.display === 'block';
            closeTimeMenus();
            if (!isOpen) {
                hourMenu.style.display = 'block';
                hourMenu.classList.add('is-open');
                hourTrigger.classList.add('is-active');
                const sel = hourMenu.querySelector('.is-selected');
                if (sel) hourMenu.scrollTop = sel.offsetTop - 50;
            }
        });

        minTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = minMenu.style.display === 'block';
            closeTimeMenus();
            if (!isOpen) {
                minMenu.style.display = 'block';
                minMenu.classList.add('is-open');
                minTrigger.classList.add('is-active');
                const sel = minMenu.querySelector('.is-selected');
                if (sel) minMenu.scrollTop = sel.offsetTop - 50;
            }
        });

        const MONTHS = [
            'Januari','Februari','Maret','April','Mei','Juni',
            'Juli','Agustus','September','Oktober','November','Desember'
        ];

        function parseDateTimeString(val) {
            if (!val) return null;
            const match = String(val).match(/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})/);
            if (match) {
                return new Date(
                    parseInt(match[1], 10),
                    parseInt(match[2], 10) - 1,
                    parseInt(match[3], 10),
                    parseInt(match[4], 10),
                    parseInt(match[5], 10)
                );
            }
            const d = new Date(val);
            return isNaN(d.getTime()) ? null : d;
        }

        let selectedDate = parseDateTimeString(input.value);
        let viewDate     = selectedDate ? new Date(selectedDate) : new Date();

        // ------------------------------------------------------------------
        // Update trigger display + hidden input value
        // ------------------------------------------------------------------
        function updateDisplay() {
            if (selectedDate && !isNaN(selectedDate.getTime())) {
                const d  = selectedDate.getDate();
                const m  = MONTHS[selectedDate.getMonth()];
                const y  = selectedDate.getFullYear();
                const hh = String(selectedDate.getHours()).padStart(2, '0');
                const mm = String(selectedDate.getMinutes()).padStart(2, '0');

                displayEl.textContent = `${d} ${m} ${y}, ${hh}:${mm}`;
                displayEl.style.color  = '#1d1d1f';
                displayEl.style.fontWeight = '500';
                clearBtn.style.display = 'block';

                const isoY  = y;
                const isoM  = String(selectedDate.getMonth() + 1).padStart(2, '0');
                const isoD  = String(d).padStart(2, '0');
                input.value = `${isoY}-${isoM}-${isoD}T${hh}:${mm}`;
            } else {
                displayEl.textContent    = 'Pilih tanggal & waktu…';
                displayEl.style.color    = '#7a7a7a';
                displayEl.style.fontWeight = '400';
                clearBtn.style.display   = 'none';
                input.value = '';
            }
        }

        // ------------------------------------------------------------------
        // Render calendar grid
        // ------------------------------------------------------------------
        function renderCalendar() {
            monthYearEl.textContent = `${MONTHS[viewDate.getMonth()]} ${viewDate.getFullYear()}`;
            daysGrid.innerHTML = '';

            const firstDay  = new Date(viewDate.getFullYear(), viewDate.getMonth(), 1).getDay();
            const lastDay   = new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, 0).getDate();
            const today     = new Date();

            // Empty prefix cells
            for (let i = 0; i < firstDay; i++) {
                const blank = document.createElement('div');
                blank.className = 'apple-cal-cell is-disabled';
                daysGrid.appendChild(blank);
            }

            // Day cells
            for (let d = 1; d <= lastDay; d++) {
                const cell = document.createElement('div');
                cell.className = 'apple-cal-cell';
                cell.textContent = d;

                const isToday = (
                    today.getFullYear() === viewDate.getFullYear() &&
                    today.getMonth()    === viewDate.getMonth()    &&
                    today.getDate()     === d
                );
                if (isToday) cell.classList.add('is-today');

                if (
                    selectedDate &&
                    selectedDate.getFullYear() === viewDate.getFullYear() &&
                    selectedDate.getMonth()    === viewDate.getMonth()    &&
                    selectedDate.getDate()     === d
                ) {
                    cell.classList.add('is-selected');
                }

                cell.addEventListener('click', (e) => {
                    e.stopPropagation();
                    closeTimeMenus();
                    const h = parseInt(currentHour, 10) || 0;
                    const m = parseInt(currentMinute, 10) || 0;
                    selectedDate = new Date(viewDate.getFullYear(), viewDate.getMonth(), d, h, m);
                    renderCalendar();
                });

                daysGrid.appendChild(cell);
            }

            // Sync time selectors with selected date
            if (selectedDate) {
                currentHour = String(selectedDate.getHours()).padStart(2, '0');
                const nearestMin = Math.round(selectedDate.getMinutes() / 5) * 5;
                currentMinute = String(nearestMin === 60 ? 55 : nearestMin).padStart(2, '0');
            }
            hourDisplay.textContent = currentHour;
            minDisplay.textContent = currentMinute;
            renderTimeDropdowns();
        }

        // ------------------------------------------------------------------
        // Popup open / close
        // ------------------------------------------------------------------
        function openPopup() {
            // Close any other open pickers
            document.querySelectorAll('.apple-datetime-popup.is-open').forEach(p  => p.classList.remove('is-open'));
            document.querySelectorAll('.apple-datetime-trigger.is-active').forEach(t => t.classList.remove('is-active'));
            document.querySelectorAll('.apple-modal-stage.has-calendar-open').forEach(s => s.classList.remove('has-calendar-open'));

            if (modalStage && sidePanel) {
                modalStage.classList.add('has-calendar-open');
            }
            popup.classList.add('is-open');
            trigger.classList.add('is-active');
            trigger.setAttribute('aria-expanded', 'true');
            renderCalendar();
        }

        function closePopup() {
            closeTimeMenus();
            if (modalStage && sidePanel) {
                modalStage.classList.remove('has-calendar-open');
            }
            popup.classList.remove('is-open');
            trigger.classList.remove('is-active');
            trigger.setAttribute('aria-expanded', 'false');
        }

        trigger.addEventListener('click', (e) => {
            if (e.target.closest('.apple-datetime-clear')) return;
            popup.classList.contains('is-open') ? closePopup() : openPopup();
        });

        trigger.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openPopup(); }
        });

        // Navigation
        prevBtn.addEventListener('click', (e) => { e.stopPropagation(); closeTimeMenus(); viewDate.setMonth(viewDate.getMonth() - 1); renderCalendar(); });
        nextBtn.addEventListener('click', (e) => { e.stopPropagation(); closeTimeMenus(); viewDate.setMonth(viewDate.getMonth() + 1); renderCalendar(); });

        // Quick presets (updates selection without closing popup until Terapkan is pressed)
        popup.querySelector('.apple-preset-today').addEventListener('click', (e) => {
            e.stopPropagation();
            closeTimeMenus();
            const now = new Date();
            selectedDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 18, 0);
            viewDate = new Date(selectedDate);
            renderCalendar();
        });

        popup.querySelector('.apple-preset-tomorrow').addEventListener('click', (e) => {
            e.stopPropagation();
            closeTimeMenus();
            const now = new Date();
            now.setDate(now.getDate() + 1);
            selectedDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 9, 0);
            viewDate = new Date(selectedDate);
            renderCalendar();
        });

        popup.querySelector('.apple-preset-clear').addEventListener('click', (e) => {
            e.stopPropagation();
            closeTimeMenus();
            selectedDate = null;
            renderCalendar();
        });

        clearBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            closeTimeMenus();
            selectedDate = null;
            updateDisplay();
        });

        // Apply selection
        applyBtn.addEventListener('click', () => {
            if (selectedDate) {
                selectedDate.setHours(parseInt(currentHour, 10) || 0);
                selectedDate.setMinutes(parseInt(currentMinute, 10) || 0);
            }
            closeTimeMenus();
            updateDisplay();
            closePopup();
        });

        popup.querySelectorAll('.apple-cal-close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                closePopup();
            });
        });

        // Outside click closes popup
        document.addEventListener('click', (e) => {
            if (!document.body.contains(e.target)) return;
            if (!wrapper.contains(e.target) && !popup.contains(e.target)) {
                closePopup();
            }
        });

        // Escape key closes open picker
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && popup.classList.contains('is-open')) {
                closePopup();
            }
        });

        // Sync when value is programmatically changed on the input
        input.addEventListener('change', () => {
            selectedDate = parseDateTimeString(input.value);
            viewDate     = selectedDate ? new Date(selectedDate) : new Date();
            if (selectedDate) {
                currentHour = String(selectedDate.getHours()).padStart(2, '0');
                const nearestMin = Math.round(selectedDate.getMinutes() / 5) * 5;
                currentMinute = String(nearestMin === 60 ? 55 : nearestMin).padStart(2, '0');
            } else {
                currentHour = '00';
                currentMinute = '00';
            }
            hourDisplay.textContent = currentHour;
            minDisplay.textContent = currentMinute;
            renderTimeDropdowns();
            updateDisplay();
        });

        // Initial render
        updateDisplay();
    });
}
