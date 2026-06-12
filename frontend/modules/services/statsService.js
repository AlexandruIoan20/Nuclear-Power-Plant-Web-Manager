import { api } from '../core/api.js';

export const statsService = {
    getAll: () => api.get('/stats'),
    getMeasurements: (reactorId = null, hours = 24) => {
        const params = new URLSearchParams();
        if (reactorId) params.set('reactorId', reactorId);
        params.set('hours', hours.toString());
        return api.get('/stats/measurements?' + params.toString());
    },
};
