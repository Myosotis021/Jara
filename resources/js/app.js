/**
 * JARA — Apple Design System UI Interactions
 *
 * 1. Apple Abort / Confirmation Modal Popup Window
 * 2. Apple Custom Date & Time Picker (Calendar + Time Scrollers)
 * 3. Apple Custom Dropdown Selector (Priority, Role, User)
 */

document.addEventListener('DOMContentLoaded', () => {
    initAppleDialog();
    initTaskCreateModal();
    initTaskEditModal();
    initAppleDateTimePickers();
    initAppleCustomSelects();
    initAppleMemberSearch();
    initAppleResizableNavbar();
});

document.addEventListener('turbo:load', () => {
    initAppleMemberSearch();
    initAppleResizableNavbar();
});

/* ==========================================================================
   1. Apple Abort / Confirmation Modal Popup Window
   ========================================================================== */
function initAppleDialog() {
    // Create modal DOM once
    let backdrop = document.getElementById('apple-confirm-modal');
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.id = 'apple-confirm-modal';
        backdrop.className = 'apple-modal-backdrop';
        backdrop.setAttribute('role', 'dialog');
        backdrop.setAttribute('aria-modal', 'true');
        backdrop.setAttribute('aria-labelledby', 'apple-dialog-title');
        backdrop.innerHTML = `
            <div class="apple-modal-window">
                <div class="apple-modal-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v3m0 3.5h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </div>
                <h3 id="apple-dialog-title" class="apple-modal-title">Konfirmasi Tindakan</h3>
                <p id="apple-dialog-message" class="apple-modal-message">
                    Apakah Anda yakin ingin melanjutkan tindakan ini?
                </p>
                <div class="apple-modal-actions">
                    <button type="button" id="apple-dialog-cancel" class="apple-btn-secondary-compact">
                        Batal
                    </button>
                    <button type="button" id="apple-dialog-confirm" class="apple-btn-danger">
                        Hapus
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(backdrop);
    }

    const titleEl   = document.getElementById('apple-dialog-title');
    const messageEl = document.getElementById('apple-dialog-message');
    const cancelBtn = document.getElementById('apple-dialog-cancel');
    const confirmBtn = document.getElementById('apple-dialog-confirm');

    let currentCallback = null;

    function openModal({ title = 'Konfirmasi Tindakan', message = 'Lanjutkan tindakan ini?', confirmText = 'Hapus', onConfirm }) {
        titleEl.textContent   = title;
        messageEl.textContent = message;
        confirmBtn.textContent = confirmText;
        currentCallback = onConfirm;
        backdrop.classList.add('is-open');
        // Focus the cancel button (safer default for destructive actions)
        setTimeout(() => cancelBtn.focus(), 50);
    }

    function closeModal() {
        backdrop.classList.remove('is-open');
        currentCallback = null;
    }

    cancelBtn.addEventListener('click', closeModal);

    // Click outside window to dismiss
    backdrop.addEventListener('click', (e) => {
        if (e.target === backdrop) closeModal();
    });

    // Escape key to dismiss
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && backdrop.classList.contains('is-open')) {
            closeModal();
        }
    });

    confirmBtn.addEventListener('click', () => {
        if (typeof currentCallback === 'function') {
            const cb = currentCallback;
            closeModal();
            cb();
        }
    });

    // Intercept form submissions with data-confirm attribute
    document.addEventListener('submit', (e) => {
        const form = e.target;
        const confirmMsg = form.getAttribute('data-confirm');
        if (confirmMsg && !form.dataset.appleConfirmed) {
            e.preventDefault();
            openModal({
                title:       form.getAttribute('data-confirm-title') || 'Konfirmasi Tindakan',
                message:     confirmMsg,
                confirmText: form.getAttribute('data-confirm-btn')   || 'Hapus',
                onConfirm: () => {
                    form.dataset.appleConfirmed = 'true';
                    form.submit();
                }
            });
        }
    });

    // Expose globally if needed
    window.AppleDialog = { open: openModal, close: closeModal };
}

/* ==========================================================================
   1b. Task Create Modal Popup Window
   ========================================================================== */
function initTaskCreateModal() {
    const modal = document.getElementById('task-create-modal');
    if (!modal) return;

    function openModal() {
        modal.classList.add('is-open');
        const firstInput = modal.querySelector('#title');
        if (firstInput) setTimeout(() => firstInput.focus(), 100);
    }

    function closeModal() {
        modal.classList.remove('is-open');
        const stage = modal.querySelector('#task-modal-stage');
        if (stage) stage.classList.remove('has-calendar-open');
        const sidePopup = modal.querySelector('.apple-datetime-popup');
        if (sidePopup) sidePopup.classList.remove('is-open');
        const trigger = modal.querySelector('.apple-datetime-trigger');
        if (trigger) trigger.classList.remove('is-active');
    }

    document.querySelectorAll('.open-task-modal-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openModal();
        });
    });

    document.querySelectorAll('.close-task-modal-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal();
        });
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal || e.target.id === 'task-modal-stage') closeModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) {
            const stage = modal.querySelector('#task-modal-stage');
            if (stage && stage.classList.contains('has-calendar-open')) {
                const closeBtn = stage.querySelector('.apple-cal-close');
                if (closeBtn) closeBtn.click();
                else stage.classList.remove('has-calendar-open');
            } else {
                closeModal();
            }
        }
    });

    // Expose globally
    window.TaskCreateModal = { open: openModal, close: closeModal };
}

/* ==========================================================================
   1c. Task Edit Modal Popup Window
   ========================================================================== */
function initTaskEditModal() {
    const modal = document.getElementById('task-edit-modal');
    if (!modal) return;

    const form = document.getElementById('task-edit-modal-form');
    const taskIdInput = document.getElementById('edit_task_id');
    const titleInput = document.getElementById('edit_title');
    const descInput = document.getElementById('edit_description');
    const prioritySelect = document.getElementById('edit_priority');
    const dueDateInput = document.getElementById('edit_due_date');
    const stage = modal.querySelector('#task-edit-modal-stage');

    function openModal(data) {
        if (data.action && form) form.action = data.action;
        if (taskIdInput && data.id) taskIdInput.value = data.id;
        if (titleInput) titleInput.value = data.title || '';
        if (descInput) descInput.value = data.description || '';
        if (prioritySelect) {
            prioritySelect.value = data.priority || 'menyusul';
            prioritySelect.dispatchEvent(new Event('change', { bubbles: true }));
        }
        if (dueDateInput) {
            dueDateInput.value = data.dueDate || '';
            dueDateInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        modal.classList.add('is-open');
        if (titleInput) setTimeout(() => titleInput.focus(), 100);
    }

    function closeModal() {
        modal.classList.remove('is-open');
        if (stage) stage.classList.remove('has-calendar-open');
        const sidePopup = modal.querySelector('.apple-datetime-popup');
        if (sidePopup) sidePopup.classList.remove('is-open');
        const trigger = modal.querySelector('.apple-datetime-trigger');
        if (trigger) trigger.classList.remove('is-active');
    }

    document.querySelectorAll('.open-task-edit-modal-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            // Only intercept primary left clicks without modifier keys so standard opening in new tabs works
            if (e.button === 0 && !e.ctrlKey && !e.metaKey && !e.shiftKey && !e.altKey) {
                e.preventDefault();
                openModal({
                    id: btn.dataset.taskId,
                    title: btn.dataset.taskTitle,
                    description: btn.dataset.taskDescription,
                    priority: btn.dataset.taskPriority,
                    dueDate: btn.dataset.taskDueDate,
                    action: btn.dataset.taskAction,
                });
            }
        });
    });

    document.querySelectorAll('.close-task-edit-modal-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal();
        });
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal || e.target.id === 'task-edit-modal-stage') closeModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) {
            if (stage && stage.classList.contains('has-calendar-open')) {
                const closeBtn = stage.querySelector('.apple-cal-close');
                if (closeBtn) closeBtn.click();
                else stage.classList.remove('has-calendar-open');
            } else {
                closeModal();
            }
        }
    });

    // Expose globally
    window.TaskEditModal = { open: openModal, close: closeModal };
}

/* ==========================================================================
   2. Apple Custom Date & Time Picker Popup Window
   ========================================================================== */
function initAppleDateTimePickers() {
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

/* ==========================================================================
   3. Apple Custom Select Dropdown
   ========================================================================== */
function initAppleCustomSelects() {
    document.querySelectorAll('.apple-custom-select').forEach(select => {
        if (select.dataset.enhanced) return;
        select.dataset.enhanced = 'true';

        // Wrap select
        const wrapper = document.createElement('div');
        wrapper.className = 'apple-select-wrapper';
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);

        // Hide native select (still used for form value)
        Object.assign(select.style, {
            position:      'absolute',
            opacity:       '0',
            pointerEvents: 'none',
            width:         '1px',
            height:        '1px',
        });

        // Build trigger
        const trigger = document.createElement('div');
        trigger.className = 'apple-select-trigger';
        trigger.setAttribute('tabindex', '0');
        trigger.setAttribute('role', 'combobox');
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.innerHTML = `
            <span class="apple-select-label" style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></span>
            <svg width="14" height="14" fill="none" stroke="#7a7a7a" viewBox="0 0 24 24" style="flex-shrink:0;transition:transform 0.15s ease;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        `;
        wrapper.appendChild(trigger);

        const menu = document.createElement('div');
        menu.className = 'apple-select-menu';
        menu.setAttribute('role', 'listbox');
        wrapper.appendChild(menu);

        const labelEl  = trigger.querySelector('.apple-select-label');
        const arrowSvg = trigger.querySelector('svg');

        function renderOptions() {
            menu.innerHTML = '';
            Array.from(select.options).forEach(opt => {
                if (opt.value === '' && opt.disabled) return; // skip placeholder options
                const optEl = document.createElement('div');
                optEl.className = 'apple-select-option';
                optEl.setAttribute('role', 'option');
                optEl.setAttribute('aria-selected', opt.selected ? 'true' : 'false');
                if (opt.selected) optEl.classList.add('is-selected');

                optEl.innerHTML = `
                    <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${opt.textContent}</span>
                    ${opt.selected
                        ? `<svg width="16" height="16" fill="none" stroke="#0066cc" viewBox="0 0 24 24" style="flex-shrink:0;">
                               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                           </svg>`
                        : ''}
                `;

                optEl.addEventListener('click', () => {
                    select.value = opt.value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    updateTrigger();
                    closeMenu();
                });

                menu.appendChild(optEl);
            });
        }

        function updateTrigger() {
            const sel = select.options[select.selectedIndex];
            labelEl.textContent = sel ? sel.textContent : '— Pilih —';
            renderOptions();
        }

        function openMenu() {
            // Close any other open menus
            document.querySelectorAll('.apple-select-menu.is-open').forEach(m  => m.classList.remove('is-open'));
            document.querySelectorAll('.apple-select-trigger.is-active').forEach(t => t.classList.remove('is-active'));

            menu.classList.add('is-open');
            trigger.classList.add('is-active');
            trigger.setAttribute('aria-expanded', 'true');
            arrowSvg.style.transform = 'rotate(180deg)';
        }

        function closeMenu() {
            menu.classList.remove('is-open');
            trigger.classList.remove('is-active');
            trigger.setAttribute('aria-expanded', 'false');
            arrowSvg.style.transform = 'rotate(0deg)';
        }

        trigger.addEventListener('click', () => {
            menu.classList.contains('is-open') ? closeMenu() : openMenu();
        });

        trigger.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openMenu(); }
            if (e.key === 'Escape') closeMenu();
        });

        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) closeMenu();
        });

        select.addEventListener('change', updateTrigger);
        updateTrigger();
    });
}

/* ==========================================================================
   5. Apple Resizable Navbar (Scroll Detection & Mobile Sheet)
   ========================================================================== */
function initAppleResizableNavbar() {
    const navbar = document.getElementById('apple-resizable-navbar');
    const toggleBtn = document.getElementById('apple-mobile-menu-toggle');
    const sheet = document.getElementById('apple-mobile-nav-sheet');

    if (!navbar) return;

    // Scroll listener for expanding / contracting into floating pill
    let ticking = false;
    const onScroll = () => {
        if (!ticking) {
            window.requestAnimationFrame(() => {
                if (window.scrollY > 40) {
                    navbar.classList.add('is-scrolled');
                } else {
                    navbar.classList.remove('is-scrolled');
                }
                ticking = false;
            });
            ticking = true;
        }
    };

    window.removeEventListener('scroll', onScroll);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    // Mobile Hamburger Toggle
    if (toggleBtn && sheet) {
        const iconMenu = toggleBtn.querySelector('.icon-menu-hamburger');
        const iconClose = toggleBtn.querySelector('.icon-menu-close');

        const openMenu = () => {
            sheet.classList.add('is-open');
            toggleBtn.setAttribute('aria-expanded', 'true');
            if (iconMenu) iconMenu.style.display = 'none';
            if (iconClose) iconClose.style.display = 'block';
        };

        const closeMenu = () => {
            sheet.classList.remove('is-open');
            toggleBtn.setAttribute('aria-expanded', 'false');
            if (iconMenu) iconMenu.style.display = 'block';
            if (iconClose) iconClose.style.display = 'none';
        };

        // Remove old listeners to avoid stacking
        const newToggleBtn = toggleBtn.cloneNode(true);
        toggleBtn.parentNode.replaceChild(newToggleBtn, toggleBtn);

        newToggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            sheet.classList.contains('is-open') ? closeMenu() : openMenu();
        });

        document.addEventListener('click', (e) => {
            if (!navbar.contains(e.target)) {
                closeMenu();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeMenu();
            }
        });
    }
}

/* ==========================================================================
   4. Apple Searchable Member Combobox with Live Database Search
   ========================================================================== */
function initAppleMemberSearch() {
    const container = document.getElementById('apple-member-combobox');
    if (!container) return;

    const trigger     = container.querySelector('#apple-member-trigger');
    const labelEl     = container.querySelector('#apple-member-label');
    const menu        = container.querySelector('#apple-member-menu');
    const searchInput = container.querySelector('#apple-member-search-input');
    const clearBtn    = container.querySelector('#apple-member-clear-btn');
    const optionsList = container.querySelector('#apple-member-options-list');
    const hiddenUserId= container.querySelector('#invite_user_id');
    const hiddenEmail = container.querySelector('#invite_user_email');
    const submitBtn   = document.getElementById('invite-submit-btn');
    const searchUrl   = container.getAttribute('data-search-url');

    let selectedUserId = hiddenUserId ? hiddenUserId.value : '';

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function escapeRegex(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    // Capture initial available users from pre-rendered DOM
    const initialUsers = Array.from(optionsList.querySelectorAll('.apple-member-option')).map(opt => ({
        id: opt.dataset.id,
        name: opt.dataset.name,
        email: opt.dataset.email,
        initials: opt.querySelector('.avatar-initials')?.textContent?.trim() || (opt.dataset.name ? opt.dataset.name.substring(0, 2).toUpperCase() : 'U'),
    }));

    function renderOptions(users, query = '', isLoading = false) {
        optionsList.innerHTML = '';

        if (isLoading) {
            optionsList.innerHTML = `
                <div class="apple-member-loading">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0066cc" stroke-width="2.5" stroke-linecap="round" style="animation: spin 0.8s linear infinite;">
                        <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/>
                    </svg>
                    <span>Mencari pengguna di database…</span>
                </div>
            `;
            return;
        }

        if (!users || users.length === 0) {
            optionsList.innerHTML = `
                <div class="apple-member-empty">
                    ${query ? `Tidak ada pengguna dengan email "${escapeHtml(query)}" di database.` : 'Belum ada calon anggota lain yang tersedia.'}
                </div>
            `;
            return;
        }

        users.forEach(user => {
            const isSelected = String(user.id) === String(selectedUserId);
            const opt = document.createElement('div');
            opt.className = `apple-member-option ${isSelected ? 'is-selected' : ''}`;
            opt.dataset.id = user.id;
            opt.dataset.name = user.name;
            opt.dataset.email = user.email;

            // Highlight matching query in email
            let emailDisplay = escapeHtml(user.email);
            if (query) {
                const regex = new RegExp(`(${escapeRegex(query)})`, 'gi');
                emailDisplay = emailDisplay.replace(regex, '<mark style="background-color:rgba(0,102,204,0.15);color:#0066cc;padding:0 2px;border-radius:2px;font-weight:600;">$1</mark>');
            }

            opt.innerHTML = `
                <span class="avatar-initials" style="width:24px;height:24px;font-size:10px;flex-shrink:0;">
                    ${escapeHtml(user.initials || (user.name ? user.name.substring(0, 2).toUpperCase() : 'U'))}
                </span>
                <div style="overflow:hidden;flex:1;min-width:0;">
                    <div class="apple-member-opt-name" style="font-size:13px;font-weight:600;color:#1d1d1f;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        ${escapeHtml(user.name)}
                    </div>
                    <div class="apple-member-opt-email" style="font-size:11px;color:#7a7a7a;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        ${emailDisplay}
                    </div>
                </div>
                ${isSelected ? `
                    <svg width="15" height="15" fill="none" stroke="#0066cc" viewBox="0 0 24 24" style="flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                ` : ''}
            `;

            opt.addEventListener('click', () => {
                selectUser(user);
            });

            optionsList.appendChild(opt);
        });
    }

    function selectUser(user) {
        if (user) {
            selectedUserId = user.id;
            if (hiddenUserId) hiddenUserId.value = user.id;
            if (hiddenEmail) hiddenEmail.value = user.email;
            labelEl.textContent = `${user.name} (${user.email})`;
            labelEl.style.color = '#1d1d1f';
            labelEl.style.fontWeight = '500';
            if (submitBtn) submitBtn.disabled = false;
        } else {
            selectedUserId = '';
            if (hiddenUserId) hiddenUserId.value = '';
            if (hiddenEmail) hiddenEmail.value = '';
            labelEl.textContent = 'Cari nama atau email pengguna…';
            labelEl.style.color = '#7a7a7a';
            labelEl.style.fontWeight = '400';
            if (submitBtn) submitBtn.disabled = true;
        }
        closeMenu();
    }

    function openMenu() {
        document.querySelectorAll('.apple-member-menu.is-open').forEach(m => m.classList.remove('is-open'));
        document.querySelectorAll('.apple-member-trigger.is-active').forEach(t => t.classList.remove('is-active'));
        document.querySelectorAll('.apple-select-menu.is-open').forEach(m => m.classList.remove('is-open'));

        menu.classList.add('is-open');
        trigger.classList.add('is-active');
        trigger.setAttribute('aria-expanded', 'true');
        setTimeout(() => searchInput.focus(), 80);
    }

    function closeMenu() {
        menu.classList.remove('is-open');
        trigger.classList.remove('is-active');
        trigger.setAttribute('aria-expanded', 'false');
    }

    trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        menu.classList.contains('is-open') ? closeMenu() : openMenu();
    });

    trigger.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
            e.preventDefault();
            openMenu();
        }
    });

    // Debounced Live Search against database
    let debounceTimer = null;
    searchInput.addEventListener('input', () => {
        const query = searchInput.value.trim();
        clearBtn.style.display = query ? 'block' : 'none';

        // Instant local filter for instant feedback
        const localMatches = initialUsers.filter(u =>
            u.email.toLowerCase().includes(query.toLowerCase()) ||
            u.name.toLowerCase().includes(query.toLowerCase())
        );
        renderOptions(localMatches, query);

        // Fetch live database search
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(async () => {
            if (!searchUrl) return;
            try {
                const res = await fetch(`${searchUrl}?q=${encodeURIComponent(query)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });
                if (res.ok) {
                    const data = await res.json();
                    renderOptions(data.users || [], query);
                }
            } catch (err) {
                console.error('Error searching members in database:', err);
            }
        }, 180);
    });

    clearBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        searchInput.value = '';
        clearBtn.style.display = 'none';
        searchInput.focus();
        renderOptions(initialUsers, '');
    });

    // Handle Enter and Escape in search input
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMenu();
            trigger.focus();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            const firstOpt = optionsList.querySelector('.apple-member-option');
            if (firstOpt) {
                firstOpt.click();
            }
        }
    });

    // Outside click dismiss
    document.addEventListener('click', (e) => {
        if (!container.contains(e.target)) {
            closeMenu();
        }
    });

    // Initial binding for pre-rendered options
    optionsList.querySelectorAll('.apple-member-option').forEach(opt => {
        opt.addEventListener('click', () => {
            selectUser({
                id: opt.dataset.id,
                name: opt.dataset.name,
                email: opt.dataset.email,
            });
        });
    });
}
