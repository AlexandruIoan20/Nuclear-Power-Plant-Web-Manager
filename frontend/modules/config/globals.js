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

// Logger inline pentru pagini fara module
window._logger = {
    _ts: () => new Date().toISOString().replace('T', ' ').slice(0, 19),
    debug: (msg, ctx) => console.debug(`⚪ [DEBUG] [${window._logger._ts()}] ${msg}`, ctx ?? ''),
    info: (msg, ctx) => console.info(`🔵 [INFO] [${window._logger._ts()}] ${msg}`, ctx ?? ''),
    warning: function (msg, ctx) {
        console.warn(`🟡 [WARNING] [${this._ts()}] ${msg}`, ctx ?? '');
        try { navigator.sendBeacon(window.API_BASE + '/logs/frontend', JSON.stringify({ level: 'WARNING', message: msg, context: ctx || null })); } catch {}
    },
    error: function (msg, ctx) {
        console.error(`🔴 [ERROR] [${this._ts()}] ${msg}`, ctx ?? '');
        try { navigator.sendBeacon(window.API_BASE + '/logs/frontend', JSON.stringify({ level: 'ERROR', message: msg, context: ctx || null })); } catch {}
    },
    critical: function (msg, ctx) {
        console.error(`🟣 [CRITICAL] [${this._ts()}] ${msg}`, ctx ?? '');
        try { navigator.sendBeacon(window.API_BASE + '/logs/frontend', JSON.stringify({ level: 'CRITICAL', message: msg, context: ctx || null })); } catch {}
    }
};

window._logger.info('Logger initializat');