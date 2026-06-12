import { api } from '../core/api.js';
import { ReactorRequestDTO } from '../dto/ReactorRequestDTO.js';
import { ReactorListResponseDTO } from '../dto/ReactorListResponseDTO.js';
import { ReactorResponseDTO } from '../dto/ReactorResponseDTO.js';

export const reactorService = {
    getReactorsByPlant: async (plantId) => {
        const response = await api.get(`/power-plants/${plantId}/reactors`);
        return (response.data ?? []).map(ReactorListResponseDTO);
    },
    getReactor: async (id) => {
        const response = await api.get(`/reactors/${id}`);
        return ReactorResponseDTO(response.data ?? response);
    },
    createReactor: (formData) => api.post('/reactors', ReactorRequestDTO(formData)),
    updateReactor: (id, formData) => api.put(`/reactors/${id}`, ReactorRequestDTO(formData)),
    deleteReactor: (id) => api.delete(`/reactors/${id}`),
};
