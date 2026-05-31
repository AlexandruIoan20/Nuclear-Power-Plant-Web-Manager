import { API_BASE } from '../config/api.config.js'; 

async function request(method, endpoint, body = null) { 
    const options = { 
        method, 
        headers: { "Content-Type": "application/json" },
    };

    if(body) options.body = JSON.stringify(body); 

    const response = await fetch(`${API_BASE}${endpoint}`, options); 
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