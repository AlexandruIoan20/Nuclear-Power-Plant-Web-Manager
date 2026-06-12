import { api } from '../core/api.js';

export const notificationService = {
    getNotifications: (category) => api.get('/notifications' + (category ? '?category=' + category : ''))
};
