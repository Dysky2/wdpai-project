import { t } from './i18n.js';
import { attachValidation, rules } from './validator.js';

const MAX_BYTES = 5 * 1024 * 1024;
const DESCRIPTION_MAX = 500;

export function initReportForm() {
    const form = document.querySelector('form[action="/incidents"]');
    if (!form) {
        return;
    }

    const grandPrix = form.querySelector('#grand_prix_id');
    const lap = form.querySelector('#lap_number');
    const accused = form.querySelector('#accused_driver_id');
    const article = form.querySelector('#infringement_article_id');
    const description = form.querySelector('#description');
    const evidence = form.querySelector('#evidence');

    const maxLap = () => Number(grandPrix.selectedOptions[0]?.dataset.totalLaps ?? lap.max ?? 0);

    // Okrążenie ≤ liczba okrążeń wybranego Grand Prix.
    const lapRule = (field) => {
        if (field.value.trim() === '') {
            return null;
        }
        const value = Number(field.value);
        const max = maxLap();
        return Number.isNaN(value) || value < 1 || (max > 0 && value > max)
            ? t('js.report.lap_range', { max })
            : null;
    };

    const syncLapLimit = () => {
        const max = maxLap();
        lap.max = String(max);
        const hint = form.querySelector('#lap-hint');
        if (hint) {
            hint.textContent = `maks. ${max}`;
        }
    };
    grandPrix.addEventListener('change', syncLapLimit);

    const counter = document.createElement('p');
    counter.className = 'form-field__hint';
    counter.setAttribute('aria-live', 'polite');
    counter.id = 'description-counter';
    description.closest('.form-field').append(counter);
    const updateCounter = () => {
        counter.textContent = `${description.value.length} / ${DESCRIPTION_MAX}`;
    };
    description.addEventListener('input', updateCounter);
    updateCounter();

    initPreview(evidence);

    attachValidation(form, [
        [grandPrix, [rules.required(t('js.report.grand_prix_required'))]],
        [lap, [rules.required(t('js.report.lap_required')), lapRule]],
        [accused, [rules.required(t('js.report.accused_required'))]],
        [article, [rules.required(t('js.report.article_required'))]],
        [description, [
            rules.required(t('js.report.description_required')),
            rules.length(20, DESCRIPTION_MAX, t('js.report.description_length', { min: 20, max: DESCRIPTION_MAX })),
        ]],
        [evidence, [rules.file(['image/jpeg', 'image/png'], MAX_BYTES, t('js.report.file_type'))]],
    ]);
}

/** Podgląd wybranego obrazu; URL tworzony przez createObjectURL i zwalniany przez revokeObjectURL. */
function initPreview(input) {
    const dropzone = input.closest('.form-field')?.querySelector('.dropzone');
    if (!dropzone) {
        return;
    }

    const preview = document.createElement('img');
    preview.className = 'dropzone__preview';
    preview.alt = t('js.report.preview_alt');
    preview.hidden = true;
    preview.style.maxWidth = '100%';
    preview.style.maxHeight = '200px';
    dropzone.append(preview);

    let objectUrl = null;
    const release = () => {
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }
    };

    input.addEventListener('change', () => {
        release();
        const file = input.files?.[0];
        if (file && file.type.startsWith('image/')) {
            objectUrl = URL.createObjectURL(file);
            preview.src = objectUrl;
            preview.hidden = false;
        } else {
            preview.removeAttribute('src');
            preview.hidden = true;
        }
    });

    window.addEventListener('pagehide', release);
}
