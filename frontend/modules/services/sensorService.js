import { api } from '../core/api.js';

export const sensorService = {
    getByReactor: (reactorId) => api.get(`/reactors/${reactorId}/sensors`),
    get: (id) => api.get(`/sensors/${id}`),
    update: (id, formData) => api.put(`/sensors/${id}`, formData),
    delete: (id) => api.delete(`/sensors/${id}`),
    populate: (reactorId, reactorType) => api.post(`/reactors/${reactorId}/sensors/populate`, { reactorType }),
};
