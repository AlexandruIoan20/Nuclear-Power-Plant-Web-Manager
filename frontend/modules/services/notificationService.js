import { api } from '../core/api.js';
import { NotificationListResponseDTO } from '../dto/NotificationListResponseDTO.js';

export const notificationService = {
    getNotifications: async (category) => {
        const response = await api.get(`/notifications?category=${category}`);
        return (response.data ?? []).map(NotificationListResponseDTO);
    },
};
