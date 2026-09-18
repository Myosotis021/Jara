/**
 * Apple Confirmation / Abort Modal Dialog
 */
export function initAppleDialog() {
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
                    <button type="button" id="apple-dialog-confirm" class="apple-btn-danger" style="display:inline-flex;align-items:center;gap:6px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            <line x1="10" y1="11" x2="10" y2="17"></line>
                            <line x1="14" y1="11" x2="14" y2="17"></line>
                        </svg>
                        <span id="apple-dialog-confirm-text">Hapus</span>
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
        const confirmTextEl = document.getElementById('apple-dialog-confirm-text');
        if (confirmTextEl) {
            confirmTextEl.textContent = confirmText;
        } else {
            confirmBtn.textContent = confirmText;
        }
        currentCallback = onConfirm;
        backdrop.classList.add('is-open');
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
