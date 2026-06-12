import { API_BASE } from '../config/api.config.js'; 
import { logger } from './logger.js';

let csrfToken = null; 

export async function getCsrfToken() { 
    if(csrfToken) return csrfToken; 
    const response = await fetch(`${API_BASE}/csrf-token`, { credentials: 'include'}); 
    const data = await response.json(); 

    csrfToken = data.csrf_token; 
    return csrfToken; 
}

async function parseResponseBody(response) {
    const contentType = response.headers.get('Content-Type') || '';
    if (response.status === 204) return null;
    if (contentType.includes('application/json')) {
        try {
            return await response.json();
        } catch {
            return null;
        }
    }
    return null;
}

async function request(method, endpoint, body = null) { 
    logger.info(`API ${method} ${endpoint}`, body ? { body } : undefined);

    const options = { 
        method, 
        headers: { "Content-Type": "application/json" },
    };

    if(!['GET', 'HEAD', 'OPTIONS'].includes(method)) { 
        options.headers['X-CSRF-TOKEN'] = await getCsrfToken(); 
    }

    if(body) options.body = JSON.stringify(body); 

    const response = await fetch(`${API_BASE}${endpoint}`, { ...options, credentials: 'include' }); 
    const data = await parseResponseBody(response);

    if(!response.ok) {
        const message = data?.message || response.statusText || 'Eroare necunoscuta';
        logger.error(`API ${method} ${endpoint} esuata`, { status: response.status, message });
        throw { 
            status: response.status,
            message
        }; 
    }

    logger.info(`API ${method} ${endpoint} reusit`);
    return data; 
}

export const api = { 
    get: (url) => request("GET", url), 
    post: (url, body) => request("POST", url, body), 
    put: (url, body) => request("PUT", url, body), 
    patch: (url, body) => request("PATCH", url, body), 
    delete: (url) => request("DELETE", url)
}; 