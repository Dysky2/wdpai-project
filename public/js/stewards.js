import { ApiError, request } from './api.js';

const buttons = [...document.querySelectorAll('[data-incident-action]')];
const errorBox = document.querySelector('[data-incident-error]');
const banner = document.querySelector('[data-status-banner]');

const badgeClasses = {
    REPORTED: 'reported',
    UNDER_INVESTIGATION: 'investigation',
    PENALTY_ISSUED: 'penalty',
    NO_FURTHER_ACTION: 'closed',
};

function showError(message) {
    if (!errorBox) {
        return;
    }
    errorBox.textContent = message ?? '';
    errorBox.hidden = !message;
}

/** Natywny <dialog>: fokus w środku, Escape zamyka, fokus wraca do przycisku. */
function confirmDialog(message, trigger) {
    return new Promise((resolve) => {
        const dialog = document.createElement('dialog');
        dialog.className = 'card';
        dialog.setAttribute('aria-labelledby', 'confirm-title');

        const title = document.createElement('h2');
        title.id = 'confirm-title';
        title.className = 'card__title';
        title.textContent = message;

        const cancel = document.createElement('button');
        cancel.type = 'button';
        cancel.className = 'button button--secondary';
        cancel.textContent = 'Anuluj';

        const ok = document.createElement('button');
        ok.type = 'button';
        ok.className = 'button';
        ok.textContent = 'Potwierdź';

        const actions = document.createElement('div');
        actions.className = 'actions';
        actions.append(cancel, ok);
        dialog.append(title, actions);
        document.body.append(dialog);

        let answer = false;
        cancel.addEventListener('click', () => dialog.close());
        ok.addEventListener('click', () => {
            answer = true;
            dialog.close();
        });
        dialog.addEventListener('close', () => {
            dialog.remove();
            trigger.focus();
            resolve(answer);
        });
        dialog.showModal();
        cancel.focus();
    });
}

function applyStatus(data) {
    if (banner) {
        const badge = document.createElement('span');
        badge.className = `badge badge--${badgeClasses[data.status] ?? 'closed'}`;
        badge.textContent = data.statusLabel;
        banner.replaceChildren(badge);
        banner.className = `status-banner status-banner--${data.status === 'UNDER_INVESTIGATION' ? 'investigation' : 'default'}`;
    }

    for (const button of buttons) {
        const action = button.dataset.incidentAction;
        button.disabled = !(
            (action === 'investigate' && data.status === 'REPORTED') ||
            (action === 'dismiss' && data.status === 'UNDER_INVESTIGATION')
        );
        button.dataset.wasDisabled = String(button.disabled);
    }

    const investigating = data.status === 'UNDER_INVESTIGATION';
    const link = document.querySelector('[data-adjudicate-link]');
    const disabled = document.querySelector('[data-adjudicate-disabled]');
    if (link && disabled) {
        link.hidden = !investigating;
        disabled.hidden = investigating;
    }
}

async function run(button) {
    const action = button.dataset.incidentAction;
    const id = Number(button.dataset.incidentId);

    if (action === 'dismiss' && !(await confirmDialog('Umorzyć sprawę bez dalszych działań?', button))) {
        return;
    }

    const label = button.textContent;
    showError(null);
    buttons.forEach((other) => { other.disabled = true; });
    button.setAttribute('aria-busy', 'true');
    button.textContent = 'Zapisywanie…';

    try {
        const { data } = await request('POST', `/api/incidents/${id}/${action}`);
        button.textContent = label;
        button.removeAttribute('aria-busy');
        applyStatus(data);
    } catch (error) {
        button.textContent = label;
        button.removeAttribute('aria-busy');
        showError(error instanceof ApiError ? error.message : 'Nieoczekiwany błąd.');
        // Przywraca przyciski zgodnie ze stanem widocznym na stronie (brak zmiany po błędzie).
        buttons.forEach((other) => { other.disabled = other.dataset.wasDisabled === 'true'; });
    }
}

for (const button of buttons) {
    button.dataset.wasDisabled = String(button.disabled);
    button.addEventListener('click', () => run(button));
}
