import { ImportPlantRequestDTO } from './ImportPlantRequestDTO.js';

export function ImportPlantsRequestDTO({ plants }) {
    return {
        plants: Array.isArray(plants) ? plants.map(ImportPlantRequestDTO) : [],
    };
}
