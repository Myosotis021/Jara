/**
 * Apple Custom Select Dropdown
 */

export function initAppleCustomSelects() {
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
