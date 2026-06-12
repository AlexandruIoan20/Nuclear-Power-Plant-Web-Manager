import { API_BASE } from '../config/api.config.js';
import { getCsrfToken } from '../core/api.js';

const seenIds = new Set();
let pollTimer = null;
let toastContainer = null;

const SEVERITY_LABELS = {
  SCRAM: { label: 'SCRAM', cls: 'scram' },
  ALARM: { label: 'ALARMĂ', cls: 'alarm' },
  ALERT: { label: 'Alertă', cls: 'alert' },
  WARNING: { label: 'Atenționare', cls: 'warning' },
  INFO: { label: 'Info', cls: 'info' },
  PLANT_STATUS_CHANGE: { label: 'Status Centrală', cls: 'info' },
  PLANT_APPROVED: { label: 'Centrală Aprobată', cls: 'info' },
  PLANT_REJECTED: { label: 'Centrală Respinsă', cls: 'alarm' },
};

function ensureContainer() {
  if (toastContainer) return toastContainer;
  toastContainer = document.createElement('div');
  toastContainer.id = 'alert-toast-container';
  toastContainer.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;max-width:400px;';
  document.body.appendChild(toastContainer);
  return toastContainer;
}

function showToast(alert) {
  const info = SEVERITY_LABELS[alert.type] || { label: alert.type, cls: 'info' };
  const container = ensureContainer();

  const toast = document.createElement('div');
  toast.className = `alert-toast ${info.cls}`;
  toast.style.cssText = `
    background:var(--surface,#1e1e1e);border-left:4px solid var(--danger,#dc3545);
    padding:14px 16px;border-radius:4px;box-shadow:0 4px 20px rgba(0,0,0,0.5);
    display:flex;flex-direction:column;gap:6px;animation:slideIn 0.25s ease-out;
    cursor:pointer;transition:opacity 0.2s;
  `;
  toast.innerHTML = `
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <strong style="font-size:0.85rem;color:var(--text,#fff);">${info.label}</strong>
      <button class="toast-close" style="background:none;border:none;color:var(--text-muted,#888);cursor:pointer;font-size:1.1rem;padding:0 2px;">&times;</button>
    </div>
    <div style="font-size:0.78rem;color:#ccc;line-height:1.4;">${alert.message}</div>
  `;

  toast.querySelector('.toast-close').addEventListener('click', (e) => {
    e.stopPropagation();
    dismiss(toast);
  });

  toast.addEventListener('click', () => {
    window.location.href = '/pages/notifications.html';
  });

  container.appendChild(toast);

  setTimeout(() => dismiss(toast), 8000);
}

function dismiss(toast) {
  toast.style.opacity = '0';
  setTimeout(() => toast.remove(), 200);
}

async function poll() {
  try {
    const resp = await fetch(`${API_BASE}/alerts/unread`, { credentials: 'include' });
    if (!resp.ok) return;
    const result = await resp.json();
    if (result.status !== 'success' || !Array.isArray(result.data)) return;

    for (const alert of result.data) {
      if (!seenIds.has(alert.id)) {
        seenIds.add(alert.id);
        if (!['PLANT_STATUS_CHANGE','PLANT_APPROVED','PLANT_REJECTED','DISMISSED_APPROVAL'].includes(alert.type)) {
          showToast(alert);
        }
      }
    }
  } catch {
  }
}

export function startAlertPolling() {
  if (pollTimer) return;
  poll();
  pollTimer = setInterval(poll, 30000);
}

export function stopAlertPolling() {
  if (pollTimer) {
    clearInterval(pollTimer);
    pollTimer = null;
  }
}

const style = document.createElement('style');
style.textContent = `
  @keyframes slideIn { from { transform:translateX(100%);opacity:0; } to { transform:translateX(0);opacity:1; } }
  .alert-toast.scram { border-left-color:#dc3545; }
  .alert-toast.alarm { border-left-color:#ffc107; }
  .alert-toast.alert { border-left-color:#fd7e14; }
  .alert-toast.warning { border-left-color:#ffc107; }
  .alert-toast.info { border-left-color:#0d6efb; }
`;
document.head.appendChild(style);

document.addEventListener('DOMContentLoaded', startAlertPolling);
