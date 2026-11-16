import { request } from './api.js';

const results = document.querySelector('[data-incidents-results]');
const form = document.querySelector('[data-incidents-form]');

function el(tag, className, text) {
    const node = document.createElement(tag);
    if (className) {
        node.className = className;
    }
    if (text !== undefined) {
        node.textContent = text;
    }
    return node;
}

const badgeClasses = {
    REPORTED: 'reported',
    UNDER_INVESTIGATION: 'investigation',
    PENALTY_ISSUED: 'penalty',
    NO_FURTHER_ACTION: 'closed',
};

export function buildRow(row) {
    const tr = document.createElement('tr');

    const lap = el('td', 'cell-lap');
    lap.append(el('span', 'lap-chip', `L${String(row.lap).padStart(2, '0')}`));

    const id = el('td', 'cell-id');
    id.append(el('strong', 'incident-id', row.code));

    const drivers = el('td', 'cell-drivers');
    row.drivers.forEach((driver, index) => {
        if (index > 0) {
            drivers.append(el('span', 'drivers__vs', 'vs'));
        }
        const tag = el('span', 'driver-tag', `Bolid ${driver.number} `);
        tag.append(el('small', '', `(${driver.code})`));
        drivers.append(tag);
    });

    const category = el('td', 'cell-category');
    category.append(el('strong', '', row.title), el('small', '', row.summary));

    const status = el('td', 'cell-status');
    status.append(el('span', `badge badge--${badgeClasses[row.status] ?? 'closed'}`, row.penaltyLabel ?? row.statusLabel));

    const action = el('td', 'cell-action');
    const link = el('a', 'button button--secondary button--small', 'Szczegóły ');
    link.href = row.url;
    link.append(el('span', 'visually-hidden', row.code));
    action.append(link);

    tr.append(lap, id, drivers, category, status, action);
    return tr;
}

function buildPager(meta, params) {
    const pages = Math.max(1, Math.ceil(meta.total / meta.perPage));
    if (pages <= 1) {
        return null;
    }

    const nav = el('nav', 'pagination');
    nav.setAttribute('aria-label', 'Paginacja');
    const add = (label, page, current = false) => {
        const link = el('a', `pagination__link${current ? ' pagination__link--current' : ''}`, label);
        const next = new URLSearchParams(params);
        next.set('page', String(page));
        link.href = `/incidents?${next}`;
        if (current) {
            link.setAttribute('aria-current', 'page');
        }
        nav.append(link);
    };

    if (meta.page > 1) {
        add('Poprzednia', meta.page - 1);
    }
    for (let i = 1; i <= pages; i += 1) {
        add(String(i), i, i === meta.page);
    }
    if (meta.page < pages) {
        add('Następna', meta.page + 1);
    }
    return nav;
}

function paramsFromForm() {
    const data = new FormData(form);
    const params = new URLSearchParams();
    for (const key of ['status', 'gp', 'q']) {
        const value = String(data.get(key) ?? '').trim();
        if (value !== '') {
            params.set(key, value);
        }
    }
    return params;
}

function showError(message) {
    const box = results.querySelector('[data-incidents-error]');
    box.textContent = message ?? '';
    box.hidden = !message;
}

async function load(params, push = true) {
    results.setAttribute('aria-busy', 'true');
    showError(null);

    try {
        const { data, meta } = await request('GET', `/api/incidents?${params}`);
        const body = results.querySelector('[data-incidents-body]');
        body.replaceChildren(...data.map(buildRow));

        results.querySelector('[data-incidents-empty]').hidden = data.length > 0;
        results.querySelector('[data-incidents-table]').hidden = data.length === 0;
        results.querySelector('[data-incidents-summary]').textContent =
            `Pokazano ${data.length} z ${meta.total} incydentów`;
        results.querySelector('[data-incidents-pager]').replaceChildren(
            ...[buildPager(meta, params)].filter(Boolean)
        );

        if (push) {
            history.replaceState(null, '', `/incidents?${params}`);
        }
    } catch (error) {
        showError(error.message);
    } finally {
        results.setAttribute('aria-busy', 'false');
    }
}

if (results && form) {
    const statusInput = form.querySelector('[data-incidents-status]');
    const chips = document.querySelector('[data-incidents-chips]');

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        load(paramsFromForm());
    });

    form.querySelector('select[name="gp"]')?.addEventListener('change', () => load(paramsFromForm()));

    chips?.addEventListener('click', (event) => {
        const chip = event.target.closest('a.chip');
        if (!chip) {
            return;
        }
        event.preventDefault();
        statusInput.value = new URL(chip.href).searchParams.get('status') ?? 'all';
        chips.querySelectorAll('a.chip').forEach((other) => {
            other.classList.toggle('chip--active', other === chip);
            if (other === chip) {
                other.setAttribute('aria-current', 'true');
            } else {
                other.removeAttribute('aria-current');
            }
        });
        load(paramsFromForm());
    });

    results.addEventListener('click', (event) => {
        const link = event.target.closest('.pagination a');
        if (!link) {
            return;
        }
        event.preventDefault();
        load(new URL(link.href).searchParams);
    });
}
