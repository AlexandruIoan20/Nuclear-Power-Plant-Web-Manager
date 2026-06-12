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

export function renderStatusBadge(status) {
    if (!status) return '<span class="tag">—</span>';
    const s = status.toUpperCase();
    let extraClass = '';
    if (s === 'REVIEW') extraClass = 'warn';
    else if (s === 'REJECTED') extraClass = 'danger';
    return `<span class="tag${extraClass ? ' ' + extraClass : ''}">${escapeHtml(status)}</span>`;
}
