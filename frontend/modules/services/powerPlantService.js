import { api } from '../core/api.js'; 
import { PlantRequestDTO } from '../dto/PlantRequestDTO.js'; 
import { BasicDataRequestDTO } from '../dto/BasicDataRequestDTO.js'; 
import { GeologicalDataRequestDTO } from '../dto/GeologicalDataRequestDTO.js'; 
import { TechnicalDataRequestDTO } from '../dto/TechnicalDataRequestDTO.js'; 
import { UpdatePlantStatusRequestDTO } from '../dto/UpdatePlanStatusRequestDTO.js'; 

export const powerPlantService = { 
    createPlantDetails: (formData) => api.post("/power-plants", PlantRequestDTO(formData)), 
    updatePlantDetails: (formData, plantId) => api.put(`/power-plants/${plantId}/details`, PlantRequestDTO(formData)), 
    updateStatusAdmin: (data, plantId) => api.patch(`/power-plants/${plantId}/admin-status`, UpdatePlantStatusRequestDTO(data)),
    submitForReview: (plantId) => api.patch(`/power-plants/${plantId}/submit-review`),
    reopenDraft: (plantId) => api.patch(`/power-plants/${plantId}/reopen`),

    getAll: () => api.get("/power-plants"),
    getMyPlants: () => api.get("/power-plants/my"), 
    getPlantDetails: (plantId) => api.get(`/power-plants/${plantId}/details`), 
    getPlant: (plantId) => api.get(`/power-plants/${plantId}`), 
    getPlantsByStatus: (status) => api.get(`/power-plants/filter?status=${status}`), 

    // Basics 
    getBasics: (plantId) => api.get(`/power-plants/${plantId}/basics`),
    createBasics: (formData, plantId) => api.post(`/power-plants/${plantId}/basics`, BasicDataRequestDTO(formData)), 
    updateBasics: (formData, plantId) => api.put(`/power-plants/${plantId}/basics`, BasicDataRequestDTO(formData)), 

    // Geological 
    getGeological: (plantId) => api.get(`/power-plants/${plantId}/geological`), 
    createGeological: (formData, plantId) => api.post(`/power-plants/${plantId}/geological`, GeologicalDataRequestDTO(formData)), 
    updateGeological: (formData, plantId) => api.put(`/power-plants/${plantId}/geological`, GeologicalDataRequestDTO(formData)), 

    // Technical 
    getTechnical: (plantId) => api.get(`/power-plants/${plantId}/technical`), 
    createTechnical: (formData, plantId) => api.post(`/power-plants/${plantId}/technical`, TechnicalDataRequestDTO(formData)), 
    updateTechnical: (formData, plantId) => api.put(`/power-plants/${plantId}/technical`, TechnicalDataRequestDTO(formData)), 

    // Import / Export 
    exportPlantJson: (plantId) => api.download(`/power-plants/${plantId}/export`),
    exportAllPlantsJson: () => api.download(`/power-plants/export`),
    exportPlantCsv: (plantId) => api.download(`/power-plants/${plantId}/export/csv`),
    exportAllPlantsCsv: () => api.download(`/power-plants/export/csv`),
    importPlant: (data) => api.post(`/power-plants/import`, data),
    importPlants: (data) => api.post(`/power-plants/import/batch`, data),
};  