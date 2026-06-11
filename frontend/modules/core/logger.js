import { API_BASE } from '../config/api.config.js';

const LEVELS = {
    DEBUG: { priority: 0, label: 'DEBUG', color: '#888', emoji: '⚪' },
    INFO: { priority: 1, label: 'INFO', color: '#2196F3', emoji: '🔵' },
    WARNING: { priority: 2, label: 'WARNING', color: '#FF9800', emoji: '🟡' },
    ERROR: { priority: 3, label: 'ERROR', color: '#f44336', emoji: '🔴' },
    CRITICAL: { priority: 4, label: 'CRITICAL', color: '#9C27B0', emoji: '🟣' },
};

let userId = null;
let sessionChecked = false;

async function ensureUserId() {
    if (sessionChecked) return;
    sessionChecked = true;
    try {
        const resp = await fetch(`${API_BASE}/user/status`, { credentials: 'include' });
        const data = await resp.json();
        if (data.status === 'success') {
            userId = data.data.id;
        }
    } catch {
    }
}

function getTimestamp() {
    return new Date().toISOString().replace('T', ' ').slice(0, 19);
}

function sendToBackend(level, message, context) {
    const payload = {
        level: level,
        message: message,
        context: context || null,
        user_id: userId,
    };
    try {
        navigator.sendBeacon(`${API_BASE}/logs/frontend`, JSON.stringify(payload));
    } catch {
    }
}

function log(level, message, context) {
    const cfg = LEVELS[level];
    if (!cfg) return;

    const timestamp = getTimestamp();
    const prefix = `${cfg.emoji} [${cfg.label}] [${timestamp}]`;

    const consoleArgs = [
        `%c${prefix}%c ${message}`,
        `color: ${cfg.color}; font-weight: bold;`,
        'color: inherit;',
    ];

    if (context !== undefined && context !== null) {
        consoleArgs.push(context);
    }

    switch (level) {
        case 'DEBUG':
            console.debug(...consoleArgs);
            break;
        case 'INFO':
            console.info(...consoleArgs);
            break;
        case 'WARNING':
            console.warn(...consoleArgs);
            break;
        case 'ERROR':
        case 'CRITICAL':
            console.error(...consoleArgs);
            break;
    }

    if (cfg.priority >= 2) {
        sendToBackend(level, message, context);
    }
}

ensureUserId();

export const logger = {
    debug: (message, context) => log('DEBUG', message, context),
    info: (message, context) => log('INFO', message, context),
    warning: (message, context) => log('WARNING', message, context),
    error: (message, context) => log('ERROR', message, context),
    critical: (message, context) => log('CRITICAL', message, context),
};
