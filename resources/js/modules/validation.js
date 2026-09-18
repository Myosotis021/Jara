/**
 * Apple Custom Field Validation Warning UI
 * Replaces native browser HTML5 validation balloons with a custom UI
 */

export function initAppleCustomValidation() {
    let activeTooltip = null;
    let activeInput = null;

    function getOrCreateTooltip() {
        let el = document.getElementById('apple-validation-tooltip');
        if (!el) {
            el = document.createElement('div');
            el.id = 'apple-validation-tooltip';
            el.className = 'apple-validation-tooltip';
            el.setAttribute('role', 'alert');
            el.innerHTML = `
                <div class="apple-validation-arrow"></div>
                <div class="apple-validation-content">
                    <span class="apple-validation-icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                        </svg>
                    </span>
                    <span class="apple-validation-msg" id="apple-validation-msg"></span>
                </div>
            `;
            document.body.appendChild(el);
        }
        return el;
    }

    function hideTooltip() {
        if (activeTooltip) {
            activeTooltip.classList.remove('is-visible');
        }
        if (activeInput) {
            activeInput.classList.remove('apple-input-invalid');
            activeInput = null;
        }
    }

    function getCustomMessage(input) {
        if (input.dataset && input.dataset.validationMessage) {
            return input.dataset.validationMessage;
        }
        const v = input.validity;
        if (!v) return input.validationMessage || 'Silakan isi bidang ini.';

        if (v.valueMissing) {
            if (input.type === 'checkbox') return 'Silakan centang kotak ini untuk melanjutkan.';
            if (input.type === 'radio') return 'Silakan pilih salah satu opsi.';
            if (input.type === 'file') return 'Silakan pilih berkas.';
            return 'Silakan isi bidang ini.';
        }
        if (v.typeMismatch) {
            if (input.type === 'email') return 'Silakan masukkan alamat email yang valid (contoh: nama@email.com).';
            if (input.type === 'url') return 'Silakan masukkan URL yang valid.';
        }
        if (v.tooShort) {
            return `Teks terlalu pendek (minimal ${input.minLength} karakter).`;
        }
        if (v.tooLong) {
            return `Teks terlalu panjang (maksimal ${input.maxLength} karakter).`;
        }
        if (v.rangeUnderflow) {
            return `Nilai minimal adalah ${input.min}.`;
        }
        if (v.rangeOverflow) {
            return `Nilai maksimal adalah ${input.max}.`;
        }
        if (v.patternMismatch) {
            return input.title || 'Format yang dimasukkan tidak sesuai.';
        }
        return input.validationMessage || 'Silakan isi bidang ini.';
    }

    function positionTooltip(input, tooltip) {
        const rect = input.getBoundingClientRect();
        const scrollX = window.scrollX || window.pageXOffset;
        const scrollY = window.scrollY || window.pageYOffset;

        let top = rect.bottom + scrollY + 8;
        let left = rect.left + scrollX + 16;

        const tooltipHeight = 44;
        if (rect.bottom + tooltipHeight + 16 > window.innerHeight && rect.top > tooltipHeight + 16) {
            top = rect.top + scrollY - tooltipHeight - 8;
            tooltip.classList.add('is-above');
        } else {
            tooltip.classList.remove('is-above');
        }

        const tooltipWidth = Math.min(360, window.innerWidth - 32);
        if (left + tooltipWidth > window.innerWidth - 16) {
            left = Math.max(16, window.innerWidth - tooltipWidth - 16);
        }

        tooltip.style.top = `${top}px`;
        tooltip.style.left = `${left}px`;
    }

    function showTooltip(input) {
        const tooltip = getOrCreateTooltip();
        const msgEl = document.getElementById('apple-validation-msg');
        const message = getCustomMessage(input);

        msgEl.textContent = message;
        activeInput = input;
        activeTooltip = tooltip;

        input.classList.add('apple-input-invalid');

        positionTooltip(input, tooltip);
        tooltip.classList.add('is-visible');

        input.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
    }

    // Capture phase invalid listener to suppress browser default popup
    window.addEventListener('invalid', (e) => {
        e.preventDefault();

        const input = e.target;
        if (!input) return;

        showTooltip(input);
        input.focus({ preventScroll: true });
    }, true);

    // Dismiss on user typing / changing
    document.addEventListener('input', (e) => {
        if (activeInput && (e.target === activeInput || activeInput.contains(e.target))) {
            hideTooltip();
        }
    });

    document.addEventListener('change', (e) => {
        if (activeInput && (e.target === activeInput || activeInput.contains(e.target))) {
            hideTooltip();
        }
    });

    // Dismiss on click outside
    document.addEventListener('click', (e) => {
        if (activeTooltip && !activeTooltip.contains(e.target) && e.target !== activeInput) {
            hideTooltip();
        }
    });

    // Dismiss on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && activeTooltip) {
            hideTooltip();
        }
    });

    window.addEventListener('resize', () => {
        if (activeTooltip && activeInput) {
            positionTooltip(activeInput, activeTooltip);
        }
    });

    window.addEventListener('scroll', () => {
        if (activeTooltip && activeInput) {
            positionTooltip(activeInput, activeTooltip);
        }
    }, true);
}
