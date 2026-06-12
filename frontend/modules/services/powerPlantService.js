import { api } from '../core/api.js'; 
import { PlantRequestDTO } from '../dto/PlantRequestDTO.js'; 
import { BasicDataRequestDTO } from '../dto/BasicDataRequestDTO.js'; 
import { GeologicalDataRequestDTO } from '../dto/GeologicalDataRequestDTO.js'; 
import { TechnicalDataRequestDTO } from '../dto/TechnicalDataRequestDTO.js'; 
import { UpdatePlantStatusRequestDTO } from '../dto/UpdatePlantStatusRequestDTO.js'; 
import { ImportPlantRequestDTO } from '../dto/ImportPlantRequestDTO.js'; 
import { ImportPlantsRequestDTO } from '../dto/ImportPlantsRequestDTO.js'; 
import { PlantDetailsResponseDTO } from '../dto/PlantDetailsResponseDTO.js'; 
import { BasicDataResponseDTO } from '../dto/BasicDataResponseDTO.js'; 
import { GeologicalDataResponseDTO } from '../dto/GeologicalDataResponseDTO.js'; 
import { TechnicalDataResponseDTO } from '../dto/TechnicalDataResponseDTO.js'; 
import { PlantListResponseDTO } from '../dto/PlantListResponseDTO.js'; 
import { PlantStatusListResponseDTO } from '../dto/PlantStatusListResponseDTO.js';

export const powerPlantService = { 
    createPlantDetails: (formData) => api.post("/power-plants", PlantRequestDTO(formData)), 
    updatePlantDetails: (formData, plantId) => api.put(`/power-plants/${plantId}/details`, PlantRequestDTO(formData)), 
    updateStatusAdmin: (data, plantId) => api.patch(`/power-plants/${plantId}/admin-status`, UpdatePlantStatusRequestDTO(data)),
    submitForReview: (plantId) => api.patch(`/power-plants/${plantId}/submit-review`),
    reopenDraft: (plantId) => api.patch(`/power-plants/${plantId}/reopen`),

    getAll: async () => {
        const response = await api.get("/power-plants");
        return (response.data ?? []).map(PlantListResponseDTO);
    },
    getMyPlants: async () => {
        const response = await api.get("/power-plants/my");
        return (response.data ?? []).map(PlantListResponseDTO);
    },
    getPlantDetails: async (plantId) => {
        const response = await api.get(`/power-plants/${plantId}/details`);
        return PlantDetailsResponseDTO(response.data ?? response);
    },
    getPlant: async (plantId) => {
        const response = await api.get(`/power-plants/${plantId}`);
        return response.data ?? response;
    },
    getPlantsByStatus: async (status) => {
        const response = await api.get(`/power-plants/filter?status=${status}`);
        return (response.data ?? []).map(PlantStatusListResponseDTO);
    },

    // Basics 
    getBasics: async (plantId) => {
        const response = await api.get(`/power-plants/${plantId}/basics`);
        return BasicDataResponseDTO(response.data ?? response);
    },
    createBasics: (formData, plantId) => api.post(`/power-plants/${plantId}/basics`, BasicDataRequestDTO(formData)), 
    updateBasics: (formData, plantId) => api.put(`/power-plants/${plantId}/basics`, BasicDataRequestDTO(formData)), 

    // Geological 
    getGeological: async (plantId) => {
        const response = await api.get(`/power-plants/${plantId}/geological`);
        return GeologicalDataResponseDTO(response.data ?? response);
    },
    createGeological: (formData, plantId) => api.post(`/power-plants/${plantId}/geological`, GeologicalDataRequestDTO(formData)), 
    updateGeological: (formData, plantId) => api.put(`/power-plants/${plantId}/geological`, GeologicalDataRequestDTO(formData)), 

    // Technical 
    getTechnical: async (plantId) => {
        const response = await api.get(`/power-plants/${plantId}/technical`);
        return TechnicalDataResponseDTO(response.data ?? response);
    },
    createTechnical: (formData, plantId) => api.post(`/power-plants/${plantId}/technical`, TechnicalDataRequestDTO(formData)), 
    updateTechnical: (formData, plantId) => api.put(`/power-plants/${plantId}/technical`, TechnicalDataRequestDTO(formData)), 

    // Import / Export 
    exportPlantJson: (plantId) => api.download(`/power-plants/${plantId}/export`),
    exportAllPlantsJson: () => api.download(`/power-plants/export`),
    exportPlantCsv: (plantId) => api.download(`/power-plants/${plantId}/export/csv`),
    exportAllPlantsCsv: () => api.download(`/power-plants/export/csv`),
    importPlant: (data) => api.post(`/power-plants/import`, ImportPlantRequestDTO(data)),
    importPlants: (data) => api.post(`/power-plants/import/batch`, ImportPlantsRequestDTO(data)),
};  