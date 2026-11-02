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

const tagSearchRequests = new WeakMap();

function getSelectedTagNames(form) {
    return [...form.querySelectorAll('[data-tag-item]')].map((item) =>
        item.dataset.tagName.toLocaleLowerCase(),
    );
}

function closeTagSuggestions(form) {
    const suggestions = form.querySelector('[data-tag-suggestions]');
    const input = form.querySelector('[data-tag-input]');

    suggestions.replaceChildren();
    suggestions.classList.add('hidden');
    input.setAttribute('aria-expanded', 'false');
}

function addTag(form, { id, name }) {
    const selectedTags = form.querySelector('[data-selected-tags]');
    const normalizedName = name.trim();

    if (
        normalizedName.length === 0 ||
        getSelectedTagNames(form).includes(normalizedName.toLocaleLowerCase())
    ) {
        return false;
    }

    const item = document.createElement('li');
    const label = document.createElement('span');
    const removeButton = document.createElement('button');
    const valueInput = document.createElement('input');

    item.className =
        'flex items-center gap-2 rounded-full border px-3 py-1 text-sm';
    item.dataset.tagItem = '';
    item.dataset.tagName = normalizedName;
    label.textContent = normalizedName;
    removeButton.className = 'font-semibold';
    removeButton.type = 'button';
    removeButton.textContent = '×';
    removeButton.setAttribute('aria-label', `Dzēst birku ${normalizedName}`);
    removeButton.dataset.removeTag = '';
    valueInput.type = 'hidden';

    if (id !== undefined) {
        item.dataset.tagId = String(id);
        valueInput.name = 'tag_ids[]';
        valueInput.value = String(id);
    } else {
        valueInput.name = 'new_tags[]';
        valueInput.value = normalizedName;
    }

    item.append(label, valueInput, removeButton);
    selectedTags.append(item);

    return true;
}

function renderTagSuggestions(form, tags) {
    const suggestions = form.querySelector('[data-tag-suggestions]');
    const message = form.querySelector('[data-tag-message]');
    const input = form.querySelector('[data-tag-input]');
    const selectedNames = getSelectedTagNames(form);
    const availableTags = tags.filter(
        (tag) => !selectedNames.includes(tag.name.toLocaleLowerCase()),
    );

    suggestions.replaceChildren();
    suggestions.dataset.activeIndex = '0';

    availableTags.forEach((tag, index) => {
        const button = document.createElement('button');

        button.className = 'block w-full px-3 py-2 text-left hover:bg-muted';
        button.type = 'button';
        button.textContent = tag.name;
        button.dataset.tagId = String(tag.id);
        button.dataset.tagName = tag.name;
        button.dataset.tagSuggestion = '';
        button.role = 'option';
        button.setAttribute('aria-selected', String(index === 0));
        suggestions.append(button);
    });

    suggestions.classList.toggle('hidden', availableTags.length === 0);
    input.setAttribute('aria-expanded', String(availableTags.length > 0));
    message.textContent =
        availableTags.length > 0
            ? 'Izvēlieties birku vai nospiediet Enter, lai pievienotu jaunu.'
            : 'Jauna birka — nospiediet Enter, lai to pievienotu.';
    message.classList.remove('text-destructive');
    message.classList.add('text-muted-foreground');
}

document.addEventListener('input', async (event) => {
    const input = event.target;

    if (
        !(input instanceof HTMLInputElement) ||
        !input.matches('[data-tag-input]')
    ) {
        return;
    }

    const form = input.closest('[data-tag-form]');
    const query = input.value.trim();

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const requestId = (tagSearchRequests.get(form) ?? 0) + 1;
    tagSearchRequests.set(form, requestId);

    if (query.length === 0) {
        closeTagSuggestions(form);
        form.querySelector('[data-tag-message]').textContent = '';

        return;
    }

    try {
        const response = await fetch(
            `${input.dataset.searchUrl}?q=${encodeURIComponent(query)}`,
            {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            },
        );

        if (!response.ok) {
            throw new Error('Birkas pašlaik nevarēja ielādēt.');
        }

        const tags = await response.json();

        if (tagSearchRequests.get(form) === requestId) {
            renderTagSuggestions(form, tags);
        }
    } catch (error) {
        if (tagSearchRequests.get(form) !== requestId) {
            return;
        }

        closeTagSuggestions(form);
        const message = form.querySelector('[data-tag-message]');
        message.textContent =
            error instanceof Error
                ? error.message
                : 'Birkas pašlaik nevarēja ielādēt.';
        message.classList.remove('text-muted-foreground');
        message.classList.add('text-destructive');
    }
});

document.addEventListener('keydown', (event) => {
    const input = event.target;

    if (
        !(input instanceof HTMLInputElement) ||
        !input.matches('[data-tag-input]')
    ) {
        return;
    }

    const form = input.closest('[data-tag-form]');

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const suggestions = form.querySelector('[data-tag-suggestions]');
    const options = [...suggestions.querySelectorAll('[data-tag-suggestion]')];

    if (event.key === 'ArrowDown' && options.length > 0) {
        event.preventDefault();
        const activeIndex = Number(suggestions.dataset.activeIndex ?? 0);
        const nextIndex = (activeIndex + 1) % options.length;

        suggestions.dataset.activeIndex = String(nextIndex);
        options.forEach((option, index) => {
            option.setAttribute('aria-selected', String(index === nextIndex));
        });
    } else if (event.key === 'ArrowUp' && options.length > 0) {
        event.preventDefault();
        const activeIndex = Number(suggestions.dataset.activeIndex ?? 0);
        const nextIndex = (activeIndex - 1 + options.length) % options.length;

        suggestions.dataset.activeIndex = String(nextIndex);
        options.forEach((option, index) => {
            option.setAttribute('aria-selected', String(index === nextIndex));
        });
    } else if (event.key === 'Escape') {
        closeTagSuggestions(form);
    } else if (event.key === 'Enter') {
        event.preventDefault();
        const activeOption = options.find(
            (option) => option.getAttribute('aria-selected') === 'true',
        );
        const chosenTag = activeOption ?? options[0];
        const added = chosenTag
            ? addTag(form, {
                  id: chosenTag.dataset.tagId,
                  name: chosenTag.dataset.tagName,
              })
            : addTag(form, { name: input.value });
        const message = form.querySelector('[data-tag-message]');

        if (!added) {
            message.textContent = 'Šī birka jau ir izvēlēta.';

            return;
        }

        input.value = '';
        closeTagSuggestions(form);
        message.textContent = '';
    }
});

document.addEventListener('click', (event) => {
    const target = event.target;

    if (!(target instanceof Element)) {
        return;
    }

    const suggestionButton = target.closest('[data-tag-suggestion]');

    if (suggestionButton instanceof HTMLButtonElement) {
        const form = suggestionButton.closest('[data-tag-form]');
        const input = form?.querySelector('[data-tag-input]');

        if (
            form instanceof HTMLFormElement &&
            input instanceof HTMLInputElement
        ) {
            addTag(form, {
                id: suggestionButton.dataset.tagId,
                name: suggestionButton.dataset.tagName,
            });
            input.value = '';
            closeTagSuggestions(form);
            form.querySelector('[data-tag-message]').textContent = '';
        }

        return;
    }

    const removeButton = target.closest('[data-remove-tag]');

    if (removeButton instanceof HTMLButtonElement) {
        removeButton.closest('[data-tag-item]')?.remove();
    }
});
