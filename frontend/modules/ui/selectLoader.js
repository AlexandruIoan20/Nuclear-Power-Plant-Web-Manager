export function loadSelect(selectId, options, selectedValue = null) { 
    const select = document.getElementById(selectId); 
    if(!select) return; 

    options.forEach(option => { 
        const o = document.createElement("option"); 
        o.value = option.value;
        o.textContent = option.label; 
        
        if(selectedValue && option.value === selectedValue) { 
            o.selected = true; 
        }

        select.appendChild(o);
    }); 
}