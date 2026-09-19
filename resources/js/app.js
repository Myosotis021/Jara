/**
 * JARA — Apple Design System UI Interactions
 *
 * ES Module Entry Point
 */

import { initAppleDialog } from './modules/dialog.js';
import { initTaskCreateModal, initTaskEditModal } from './modules/task-modals.js';
import { initAppleDateTimePickers } from './modules/datepicker.js';
import { initAppleCustomSelects } from './modules/select.js';
import { initAppleMemberSearch } from './modules/member-search.js';
import { initAppleResizableNavbar } from './modules/navbar.js';
import { initAppleCustomValidation } from './modules/validation.js';
import { initAppleTaskToggle } from './modules/task-toggle.js';

document.addEventListener('DOMContentLoaded', () => {
    initAppleDialog();
    initTaskCreateModal();
    initTaskEditModal();
    initAppleDateTimePickers();
    initAppleCustomSelects();
    initAppleMemberSearch();
    initAppleResizableNavbar();
    initAppleCustomValidation();
    initAppleTaskToggle();
});

document.addEventListener('turbo:load', () => {
    initAppleMemberSearch();
    initAppleResizableNavbar();
    initAppleCustomValidation();
    initAppleTaskToggle();
});
