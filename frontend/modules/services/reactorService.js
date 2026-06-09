import { api } from '../core/api.js';
import { ReactorDTO } from '../dto/ReactorDTO.js';

export const reactorService = {
    getReactorsByPlant: (plantId) => api.get(`/power-plants/${plantId}/reactors`),
    getReactor: (id) => api.get(`/reactors/${id}`),
    createReactor: (formData) => api.post('/reactors', ReactorDTO(formData)),
    updateReactor: (id, formData) => api.put(`/reactors/${id}`, ReactorDTO(formData)),
    deleteReactor: (id) => api.delete(`/reactors/${id}`),
};
