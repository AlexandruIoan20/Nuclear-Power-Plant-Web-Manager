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