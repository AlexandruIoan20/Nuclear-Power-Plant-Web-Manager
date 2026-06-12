import { feasibilityReportService } from '../../services/feasibilityReportService.js';  
import { getQueryParam } from '../../utils/urlHelper.js'; 
import { logger } from '../../core/logger.js';
import { populateFeasibilityReport } from '../../ui/feasibility/feasibilityReportRenderer.js';

const plantId = getQueryParam("id"); 

function showLoading(message) {
    const shell = document.querySelector('.page-shell');
    if (!shell) return;
    let loader = document.getElementById('loading-indicator');
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'loading-indicator';
        loader.style.cssText = 'text-align:center;padding:40px;color:var(--muted);font-size:1.1rem;';
        shell.prepend(loader);
    }
    loader.textContent = message;
}

function hideLoading() {
    const loader = document.getElementById('loading-indicator');
    if (loader) loader.remove();
}

document.addEventListener("DOMContentLoaded", async () => { 
    if(!plantId) { 
        alert("Centrala nu a fost găsită."); 
        return; 
    }

    showLoading("Se încarcă raportul..."); 

    try { 
        const response = await feasibilityReportService.getReport(plantId); 
        
        logger.info({ response }); 
        if(!response.success) { 
            hideLoading(); 
            alert("A apărut o problemă la căutarea raportului"); 
            return; 
        }

        hideLoading();

        const btn = document.getElementById('btn-back-to-plant');
        if (btn) btn.href = `/pages/power-plants/finish.html?id=${plantId}`;

        populateFeasibilityReport(response.data); 
    } catch(error) {    
        hideLoading(); 
        logger.error(error.message); 
        alert("A apărut o eroare la căutarea raportului"); 
    }
}); 