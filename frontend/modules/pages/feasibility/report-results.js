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
        const report = await feasibilityReportService.getReport(plantId); 

        logger.info({ report }); 
        if(!report.reportId) { 
            hideLoading(); 
            alert("A apărut o problemă la căutarea raportului"); 
            return; 
        }

        hideLoading();

        const btn = document.getElementById('btn-back-to-plant');
        if (btn) btn.href = `/pages/power-plants/finish.html?id=${plantId}`;

        populateFeasibilityReport(report); 
    } catch(error) {    
        hideLoading(); 
        logger.error(error.message);
        const shell = document.querySelector('.page-shell');
        if (shell) {
            shell.innerHTML = `
                <div style="text-align:center;padding:60px 20px;">
                    <h2 style="color:var(--red);margin-bottom:16px;">Eroare la încărcarea raportului</h2>
                    <p style="color:var(--muted);margin-bottom:24px;">${error.message || 'A apărut o eroare necunoscută.'}</p>
                    <a href="/pages/power-plants/finish.html?id=${plantId}" class="button" style="display:inline-block;">Înapoi la centrală</a>
                </div>
            `;
        }
    }
}); 