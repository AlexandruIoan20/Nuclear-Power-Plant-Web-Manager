function escapeHtml(value) {
    return String(value).replace(/[&<>"]|'/g, (character) => {
        const escapeMap = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        };
        return escapeMap[character] || character;
    });
}

export function showSuccess(element, text) {
    element.innerHTML = `<span class="inline-status">${escapeHtml(text)}</span>`;
}

export function showError(element, text) {
    element.innerHTML = `<span style="color: var(--danger); font-size: 0.9rem;">${escapeHtml(text)}</span>`;
}

export function clearStatus(element) { element.innerHTML = ""; }
