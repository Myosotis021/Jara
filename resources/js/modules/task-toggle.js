/**
 * Apple Optimistic Task Status Toggle
 * Enables zero-reload, instantaneous completion feedback
 */

export function initAppleTaskToggle() {
    document.addEventListener('submit', async (e) => {
        const form = e.target;
        if (!form || !form.classList.contains('task-toggle-form')) return;

        // Prevent full page reload
        e.preventDefault();

        const card = form.closest('.enterprise-task-card');
        const btn = form.querySelector('.task-toggle-btn');
        const titleEl = card ? card.querySelector('.task-title-text') : null;
        if (!card || !btn) return;

        const isCurrentlyCompleted = card.classList.contains('is-completed');
        const nextState = !isCurrentlyCompleted;

        function applyState(completed) {
            if (completed) {
                card.classList.add('is-completed');
                btn.style.borderColor = '#0066cc';
                btn.style.backgroundColor = '#0066cc';
                btn.style.color = '#ffffff';
                btn.title = 'Tandai belum selesai';
                btn.innerHTML = `
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                `;
                if (titleEl) {
                    titleEl.style.textDecoration = 'line-through';
                    titleEl.style.color = '#7a7a7a';
                }
            } else {
                card.classList.remove('is-completed');
                btn.style.borderColor = '#d2d2d7';
                btn.style.backgroundColor = '#ffffff';
                btn.style.color = 'transparent';
                btn.title = 'Tandai selesai';
                btn.innerHTML = '';
                if (titleEl) {
                    titleEl.style.textDecoration = 'none';
                    titleEl.style.color = '#1d1d1f';
                }
            }
        }

        // Apply instant optimistic state
        applyState(nextState);

        // Perform asynchronous background request
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(form)
            });

            if (!res.ok) throw new Error('Toggle request failed');

            const data = await res.json();
            if (data.success && data.stats) {
                const kpiTotal = document.getElementById('kpi-total-tasks');
                const kpiCompleted = document.getElementById('kpi-completed-tasks');
                const kpiPending = document.getElementById('kpi-pending-tasks');
                const kpiPercent = document.getElementById('kpi-progress-percentage');
                const kpiTotalSub = document.getElementById('kpi-total-tasks-sub');

                if (kpiTotal) kpiTotal.textContent = data.stats.total;
                if (kpiCompleted) kpiCompleted.textContent = data.stats.completed;
                if (kpiPending) kpiPending.textContent = Math.max(0, data.stats.total - data.stats.completed);
                if (kpiPercent) kpiPercent.textContent = `${data.stats.progress_percentage}%`;
                if (kpiTotalSub) kpiTotalSub.textContent = data.stats.total;
            }
        } catch (err) {
            // Revert state if network fails
            applyState(isCurrentlyCompleted);
        }
    });
}
