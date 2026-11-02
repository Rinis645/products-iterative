document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
        event.preventDefault();
    }
});

document.addEventListener('submit', async (event) => {
    const form = event.target;

    if (
        !(form instanceof HTMLFormElement) ||
        !form.matches('[data-quantity-form]')
    ) {
        return;
    }

    event.preventDefault();

    const submitter = event.submitter;
    const amountInput = form.elements.namedItem('amount');
    const quantityElement = document.querySelector('#product-quantity');
    const messageElement = document.querySelector('#quantity-update-message');

    if (
        !(submitter instanceof HTMLButtonElement) ||
        !(amountInput instanceof HTMLInputElement) ||
        !(quantityElement instanceof HTMLElement) ||
        !(messageElement instanceof HTMLElement)
    ) {
        return;
    }

    const buttons = form.querySelectorAll('button[type="submit"]');
    const body = new FormData(form);

    body.set('direction', submitter.value);
    buttons.forEach((button) => {
        button.disabled = true;
    });
    messageElement.textContent = 'Daudzums tiek atjaunināts…';
    messageElement.setAttribute('role', 'status');
    messageElement.classList.remove('text-destructive');
    messageElement.classList.add('text-muted-foreground');

    try {
        const response = await fetch(form.action, {
            method: form.method,
            body,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
        const result = await response.json();

        if (!response.ok) {
            const validationError = Object.values(result.errors ?? {})
                .flat()
                .find((error) => typeof error === 'string');

            throw new Error(
                validationError ??
                    result.message ??
                    'Neizdevās atjaunināt daudzumu.',
            );
        }

        const quantity = Number(result.quantity);

        if (!Number.isInteger(quantity) || quantity < 0) {
            throw new Error('Serveris atgrieza nederīgu produkta daudzumu.');
        }

        quantityElement.textContent = String(quantity);
        messageElement.textContent = result.message;
        messageElement.classList.remove('text-destructive');
        messageElement.classList.add('text-muted-foreground');
        const maxQuantity = Number(form.dataset.maxQuantity);

        buttons.forEach((button) => {
            button.disabled =
                (button.value === 'increase' && quantity >= maxQuantity) ||
                (button.value === 'decrease' && quantity === 0);
        });
    } catch (error) {
        messageElement.textContent =
            error instanceof Error
                ? error.message
                : 'Neizdevās atjaunināt daudzumu.';
        messageElement.setAttribute('role', 'alert');
        messageElement.classList.remove('text-muted-foreground');
        messageElement.classList.add('text-destructive');

        buttons.forEach((button) => {
            button.disabled = false;
        });

        const currentQuantity = Number(quantityElement.textContent);

        buttons.forEach((button) => {
            button.disabled =
                (button.value === 'increase' &&
                    currentQuantity >= Number(form.dataset.maxQuantity)) ||
                (button.value === 'decrease' && currentQuantity === 0);
        });
    }
});
