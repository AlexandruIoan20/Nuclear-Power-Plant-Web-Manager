export function showSuccess(element, text) { 
    element.innerHTML = `<span class = "inline-status">${text}</span>`
}

export function showError(element, text) {
    element.innerHTML = `<span style="color: var(--danger); font-size: 0.9rem;">${text}</span>`;
}
  
export function clearStatus(element) { element.innerHTML = ""; }