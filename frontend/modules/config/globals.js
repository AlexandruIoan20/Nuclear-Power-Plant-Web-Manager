window.FRONTEND_BASE = 'http://localhost:5500'
window.BACKEND_BASE = 'http://localhost:8081'
window.API_BASE = 'http://localhost:8081/api'


window._csrfToken = null; 
window.getCsrfToken = async function () { 
    if(window._csrfToken) return window._csrfToken; 
    const response = await fetch(window.API_BASE + '/csrf-token', { credentials: 'include' });
    const data = await response.json(); 
    
    window._csrfToken = data.csrf_token; 
    return window._csrfToken; 
}

window._logger = {
    _send(level, message, context) {
        const payload = { level, message, context: context || null };
        try {
            navigator.sendBeacon(window.API_BASE + '/logs/frontend', JSON.stringify(payload));
        } catch {}
    },
    debug(msg, ctx) { console.debug('[DEBUG]', msg, ctx || ''); },
    info(msg, ctx) { console.info('[INFO]', msg, ctx || ''); },
    warning(msg, ctx) {
        console.warn('[WARNING]', msg, ctx || '');
        this._send('WARNING', msg, ctx);
    },
    error(msg, ctx) {
        console.error('[ERROR]', msg, ctx || '');
        this._send('ERROR', msg, ctx);
    },
    critical(msg, ctx) {
        console.error('[CRITICAL]', msg, ctx || '');
        this._send('CRITICAL', msg, ctx);
    }
};