/** Dostępny modal potwierdzenia oparty o natywny <dialog> (aria-modal, fokus w środku, Escape, powrót fokusu). */
export function confirmDialog(message, trigger, { confirmLabel = 'Potwierdź', cancelLabel = 'Anuluj' } = {}) {
    return new Promise((resolve) => {
        const dialog = document.createElement('dialog');
        dialog.className = 'card';
        dialog.setAttribute('aria-labelledby', 'confirm-title');
        dialog.setAttribute('aria-modal', 'true');

        const title = document.createElement('h2');
        title.id = 'confirm-title';
        title.className = 'card__title';
        title.textContent = message;

        const cancel = document.createElement('button');
        cancel.type = 'button';
        cancel.className = 'button button--secondary';
        cancel.textContent = cancelLabel;

        const ok = document.createElement('button');
        ok.type = 'button';
        ok.className = 'button';
        ok.textContent = confirmLabel;

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
            trigger?.focus();
            resolve(answer);
        });
        dialog.showModal();
        cancel.focus();
    });
}
