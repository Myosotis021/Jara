/**
 * Apple Task Create & Edit Modals
 */

export function initTaskCreateModal() {
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

export function initTaskEditModal() {
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
