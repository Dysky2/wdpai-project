/** Reguły pól i błędy pod polem. Serwer waliduje ponownie, to tylko szybka informacja zwrotna. */

export const rules = {
    required: (message) => (field) => (isEmpty(field) ? message : null),
    range: (min, max, message) => (field) => {
        if (isEmpty(field)) {
            return null;
        }
        const value = Number(field.value);
        return Number.isNaN(value) || value < min || value > max ? message : null;
    },
    length: (min, max, message) => (field) => {
        if (isEmpty(field)) {
            return null;
        }
        const length = field.value.trim().length;
        return length < min || length > max ? message : null;
    },
    file: (types, maxBytes, message) => (field) => {
        const file = field.files?.[0];
        if (!file) {
            return null;
        }
        return types.includes(file.type) && file.size <= maxBytes ? null : message;
    },
};

function isEmpty(field) {
    return field.value.trim() === '';
}

function errorElement(field) {
    const id = `${field.id}-error`;
    let element = document.getElementById(id);
    if (!element) {
        element = document.createElement('p');
        element.id = id;
        element.className = 'form-field__error';
        field.closest('.form-field')?.append(element);
    }
    return element;
}

/** Zapisuje token w aria-describedby bez gubienia istniejących (np. podpowiedzi). */
function toggleDescribedBy(field, id, on) {
    const tokens = new Set((field.getAttribute('aria-describedby') ?? '').split(/\s+/).filter(Boolean));
    if (on) {
        tokens.add(id);
    } else {
        tokens.delete(id);
    }
    if (tokens.size > 0) {
        field.setAttribute('aria-describedby', [...tokens].join(' '));
    } else {
        field.removeAttribute('aria-describedby');
    }
}

export function showError(field, message) {
    const wrapper = field.closest('.form-field');
    const element = errorElement(field);
    element.setAttribute('role', 'alert');
    element.textContent = message ?? '';
    element.hidden = !message;
    wrapper?.classList.toggle('form-field--invalid', Boolean(message));
    if (message) {
        field.setAttribute('aria-invalid', 'true');
    } else {
        field.removeAttribute('aria-invalid');
    }
    toggleDescribedBy(field, element.id, Boolean(message));
}

/** Zwraca pierwszy komunikat błędu albo null. */
export function validateField(field, fieldRules) {
    for (const rule of fieldRules) {
        const message = rule(field);
        if (message) {
            return message;
        }
    }
    return null;
}

/**
 * Podpina walidację do formularza: pola sprawdzane na `change`/`input` (po pierwszej próbie),
 * cały formularz na `submit`. Przy błędach blokuje wysyłkę i ustawia fokus na pierwszym polu.
 *
 * @param {HTMLFormElement} form
 * @param {Array<[HTMLElement, Function[]]>} schema
 */
export function attachValidation(form, schema) {
    form.noValidate = true;
    let submitted = false;

    const check = ([field, fieldRules]) => {
        const message = validateField(field, fieldRules);
        showError(field, message);
        return message === null;
    };

    for (const entry of schema) {
        const [field] = entry;
        const handler = () => {
            if (submitted) {
                check(entry);
            }
        };
        field.addEventListener('change', handler);
        field.addEventListener('input', handler);
    }

    form.addEventListener('submit', (event) => {
        submitted = true;
        const failed = schema.filter((entry) => !check(entry));
        if (failed.length > 0) {
            event.preventDefault();
            failed[0][0].focus();
        }
    });
}
