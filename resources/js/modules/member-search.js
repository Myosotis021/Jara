/**
 * Apple Searchable Member Combobox with Live Database Search
 */

export function initAppleMemberSearch() {
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
