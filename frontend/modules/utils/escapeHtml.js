export function escapeHtml(value) {
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
