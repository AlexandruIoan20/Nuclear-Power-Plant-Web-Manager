import { API_BASE } from '../config/api.config.js'; 

let csrfToken = null; 

async function getCsrfToken() { 
    if(csrfToken) return csrfToken; 
    const response = await fetch(`${API_BASE}/csrf-token`, { credentials: 'include'}); 
    const data = await response.json(); 

    csrfToken = data.csrf_token; 
    return csrfToken; 
}

async function request(method, endpoint, body = null) { 
    const options = { 
        method, 
        headers: { "Content-Type": "application/json" },
    };

    if(!['GET', 'HEAD', 'OPTIONS'].includes(method)) { 
        options.headers['X-CSRF-TOKEN'] = await getCsrfToken(); 
    }

    if(body) options.body = JSON.stringify(body); 

    const response = await fetch(`${API_BASE}${endpoint}`, { ...options, credentials: 'include' }); 
    const data = await response.json(); 
    if(!response.ok) throw { 
        status: response.status,
        message: data.message
    }; 

    return data; 
}

export const api = { 
    get: (url) => request("GET", url), 
    post: (url, body) => request("POST", url, body), 
    put: (url, body) => request("PUT", url, body), 
    patch: (url, body) => request("PATCH", url, body), 
    delete: (url) => request("DELETE", url)
}; 