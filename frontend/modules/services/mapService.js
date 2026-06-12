import { api } from '../core/api.js';
import { PlantMapResponseDTO } from '../dto/PlantMapResponseDTO.js';

export const mapService = {
    getMapData: async () => {
        const response = await api.get('/power-plants/map-data');
        return (response.data ?? []).map(PlantMapResponseDTO);
    },
};
