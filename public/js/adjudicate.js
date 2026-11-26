import { t } from './i18n.js';
import { confirmDialog } from './modal.js';
import { attachValidation, rules } from './validator.js';

export function initAdjudicateForm() {
    const form = document.querySelector('form[action^="/stewards/adjudicate/"]');
    if (!form) {
        return;
    }

    const article = form.querySelector('#article_id');
    const points = form.querySelector('#points');
    const reason = form.querySelector('#reason');
    const projected = form.querySelector('[data-projected-points]');
    const insertButton = form.querySelector('[data-insert-precedent]');
    const current = Number(points.dataset.currentPoints ?? 0);
    // Limity uzasadnienia pochodzą z atrybutów minlength/maxlength (serwer: PenaltyService::REASON_MIN/MAX).
    const reasonMin = Math.max(1, reason.minLength);
    const reasonMax = reason.maxLength > 0 ? reason.maxLength : Number.MAX_SAFE_INTEGER;

    const clampPoints = () => Math.min(3, Math.max(0, Number.parseInt(points.value, 10) || 0));

    const updateProjected = () => {
        if (projected) {
            projected.textContent = String(current + clampPoints());
        }
    };

    initStepper(points, clampPoints, updateProjected);
    points.addEventListener('input', updateProjected);
    points.addEventListener('change', updateProjected);

    // Preset sankcji i punktów z wybranego artykułu (można je potem zmienić).
    const applyPreset = () => {
        const option = article.selectedOptions[0];
        if (!option) {
            return;
        }
        const sanction = option.dataset.sanction;
        const radio = sanction ? form.querySelector(`input[name="sanction"][value="${CSS.escape(sanction)}"]`) : null;
        if (radio) {
            radio.checked = true;
        }
        points.value = option.dataset.points ?? points.value;
        updateProjected();
        if (insertButton) {
            insertButton.hidden = !option.dataset.wording;
        }
    };
    article.addEventListener('change', applyPreset);
    if (insertButton) {
        insertButton.hidden = !article.selectedOptions[0]?.dataset.wording;
        insertButton.addEventListener('click', () => {
            const wording = article.selectedOptions[0]?.dataset.wording ?? '';
            if (!wording) {
                return;
            }
            reason.value = reason.value.trim() === '' ? wording : `${reason.value.trim()}\n\n${wording}`;
            reason.dispatchEvent(new Event('input', { bubbles: true }));
            reason.focus();
        });
    }

    const counter = document.createElement('p');
    counter.className = 'form-field__hint';
    counter.setAttribute('aria-live', 'polite');
    reason.closest('.form-field').append(counter);
    const updateCounter = () => {
        counter.textContent = `${reason.value.length} / ${reasonMax}`;
    };
    reason.addEventListener('input', updateCounter);
    updateCounter();

    attachValidation(form, [
        [article, [rules.required(t('js.adjudicate.article_required'))]],
        [points, [rules.required('Podaj punkty karne.'), rules.range(0, 3, 'Punkty karne: od 0 do 3.')]],
        [reason, [
            rules.required(t('js.adjudicate.reason_required')),
            rules.length(reasonMin, reasonMax, t('js.adjudicate.reason_length', { min: reasonMin, max: reasonMax })),
        ]],
    ]);

    // Potwierdzenie w modalu przed zapisem kary; po zatwierdzeniu formularz jest wysyłany zwykłym submitem.
    let confirmed = false;
    form.addEventListener('submit', async (event) => {
        if (confirmed || event.defaultPrevented) {
            return;
        }
        event.preventDefault();
        const submitter = event.submitter ?? form.querySelector('button[type="submit"]');
        const ok = await confirmDialog(t('js.adjudicate.confirm'), submitter, {
            confirmLabel: t('js.adjudicate.confirm_label'),
        });
        if (ok) {
            confirmed = true;
            form.requestSubmit(submitter);
        }
    });
}

/** Przyciski − / + obok pola punktów (0–3); bez skryptu pole liczbowe działa normalnie. */
function initStepper(input, clamp, onChange) {
    const wrap = document.createElement('div');
    wrap.className = 'points-stepper';
    wrap.setAttribute('role', 'group');
    wrap.setAttribute('aria-label', t('js.adjudicate.points_group'));

    const make = (label, text, delta) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'button button--secondary';
        button.textContent = text;
        button.setAttribute('aria-label', label);
        button.addEventListener('click', () => {
            input.value = String(Math.min(3, Math.max(0, clamp() + delta)));
            input.dispatchEvent(new Event('input', { bubbles: true }));
            onChange();
        });
        return button;
    };

    wrap.append(make(t('js.adjudicate.points_less'), '−', -1), make(t('js.adjudicate.points_more'), '+', 1));
    input.after(wrap);
}
