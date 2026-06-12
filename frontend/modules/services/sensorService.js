import { api } from '../core/api.js';
import { SensorRequestDTO } from '../dto/SensorRequestDTO.js';
import { SensorPopulateRequestDTO } from '../dto/SensorPopulateRequestDTO.js';
import { SensorListResponseDTO } from '../dto/SensorListResponseDTO.js';
import { SensorResponseDTO } from '../dto/SensorResponseDTO.js';

export const sensorService = {
    getByReactor: async (reactorId) => {
        const response = await api.get(`/reactors/${reactorId}/sensors`);
        return (response.data ?? []).map(SensorListResponseDTO);
    },
    get: async (id) => {
        const response = await api.get(`/sensors/${id}`);
        return SensorResponseDTO(response.data ?? response);
    },
    create: (reactorId, formData) => {
        const dto = SensorRequestDTO(formData);
        return api.post('/sensors', { ...dto, reactorId });
    },
    update: (id, formData) => api.put(`/sensors/${id}`, SensorRequestDTO(formData)),
    delete: (id) => api.delete(`/sensors/${id}`),
    populate: (reactorId, reactorType) => {
        const dto = SensorPopulateRequestDTO({ reactorType });
        return api.post(`/reactors/${reactorId}/sensors/populate`, dto);
    },
};
