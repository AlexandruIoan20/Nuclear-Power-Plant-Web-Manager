export function saveHeaderState(ids) { 
    const current = getHeaderState(); 
    localStorage.setItem("headerState", JSON.stringify({ ...current, ...ids })); 
}

export function getHeaderState() { 
    try { 
        return JSON.parse(localStorage.getItem("headerState")) ?? {}; 
    } catch { 
        return {}; 
    }
}

export function clearHeaderState() { 
    localStorage.removeItem("headerState"); 
}