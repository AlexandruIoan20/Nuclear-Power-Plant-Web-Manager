import { getQueryParam } from '../../utils/urlHelper.js';
import { getHeaderState } from './formHeaderState.js'

const currentPage = window.location.pathname.split("/").pop().replace(".html", "");

function getSteps() {  
    const plantId = getQueryParam("id");
    const { basicsId, geologicalId, technicalId } = getHeaderState();

    return [
        {
            label: "Detalii Centrală",
            icon: "⚡",
            page: "create",
            hrefCreate: `/pages/power-plants/create.html`,
            hrefEdit: `/pages/power-plants/create.html?id=${plantId}`,
            exists: !!plantId,
            alwaysAccessible: true,
        },
        {
            label: "Informații Generale",
            icon: "📋",
            page: "basics",
            hrefCreate: `/pages/power-plants/basics.html?id=${plantId}`,
            hrefEdit: `/pages/power-plants/basics.html?id=${plantId}&basicsId=${basicsId}`,
            exists: !!basicsId,
            alwaysAccessible: false,
        },
        {
            label: "Date Geologice",
            icon: "🌍",
            page: "geological",
            hrefCreate: `/pages/power-plants/geological.html?id=${plantId}`,
            hrefEdit: `/pages/power-plants/geological.html?id=${plantId}&geologicalId=${geologicalId}`,
            exists: !!geologicalId,
            alwaysAccessible: false,
        },
        {
            label: "Specificații Tehnice",
            icon: "⚙️",
            page: "technical",
            hrefCreate: `/pages/power-plants/technical.html?id=${plantId}`,
            hrefEdit: `/pages/power-plants/technical.html?id=${plantId}&technicalId=${technicalId}`,
            exists: !!technicalId,
            alwaysAccessible: false,
        },
    ];
}

export function renderHeader() {
    const header = document.getElementById("form-header");
    if (!header) return;

    const steps = getSteps();

    header.innerHTML = `
        <nav class="form-nav">
            <div class="form-nav__brand">
                <span>⚡</span>
                <span>Configurare Centrală</span>
            </div>
            <button class="form-nav__toggle" id="nav-toggle" aria-label="Deschide meniul">☰</button>
            <div class="form-nav__links" id="nav-links">
                ${steps.map(step => buildButton(step, !!getQueryParam("id"))).join("")}
            </div>
        </nav>
    `;

    document.getElementById("nav-toggle").addEventListener("click", () => {
        const links = document.getElementById("nav-links");
        links.classList.toggle("form-nav__links--open");
    });
}

function buildButton(step, plantExists) {
    const isCurrent = currentPage === step.page;
    const isAccessible = step.alwaysAccessible || plantExists;

    if (isCurrent) {
        return `
            <span class="nav-btn nav-btn--current">
                <span class="nav-btn__icon">${step.icon}</span>
                <span class="nav-btn__label">${step.label}</span>
                <span class="nav-btn__badge nav-btn__badge--active">Activ</span>
            </span>
        `;
    }

    if (!isAccessible) {
        return `
            <span class="nav-btn nav-btn--disabled" title="Completează mai întâi detaliile centralei">
                <span class="nav-btn__icon">${step.icon}</span>
                <span class="nav-btn__label">${step.label}</span>
                <span class="nav-btn__badge nav-btn__badge--locked">🔒</span>
            </span>
        `;
    }

    if (step.exists) {
        return `
            <a href="${step.hrefEdit}" class="nav-btn nav-btn--done">
                <span class="nav-btn__icon">${step.icon}</span>
                <span class="nav-btn__label">${step.label}</span>
                <span class="nav-btn__badge nav-btn__badge--done">Completat</span>
            </a>
        `;
    }

    return `
        <a href="${step.hrefCreate}" class="nav-btn nav-btn--empty">
            <span class="nav-btn__icon">${step.icon}</span>
            <span class="nav-btn__label">${step.label}</span>
            <span class="nav-btn__badge nav-btn__badge--empty">Necompletat</span>
        </a>
    `;
}

document.addEventListener("DOMContentLoaded", renderHeader);