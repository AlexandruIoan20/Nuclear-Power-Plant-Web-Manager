import { api } from '../core/api.js';

export const notificationService = {
    getNotifications: () => api.get('/notifications')
};
