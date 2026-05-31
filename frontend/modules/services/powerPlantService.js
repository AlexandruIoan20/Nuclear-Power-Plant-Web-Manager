import { api } from '../core/api.js'; 
import { PlantRequestDTO } from '../dto/PlantRequestDTO.js'; 
import { BasicDataRequestDTO } from '../dto/BasicDataRequestDTO.js'; 
import { GeologicalDataRequestDTO } from '../dto/GeologicalDataRequestDTO.js'; 
import { TechnicalDataRequestDTO } from '../dto/TechnicalDataRequestDTO.js'; 

export const powerPlantService = { 
    create: (formData) => api.post("/power-plants", PlantRequestDTO(formData)), 
    update: (formData) => api.put(`/power-plants/${formData.id}`, PlantRequestDTO(formData)), 
    getAll: () => api.get("/power-plants"), 

    // Basics 
    getBasics: (plantId) => api.get(`/power-plants/${plantId}/basics`),
    createBasics: (formData, plantId) => api.post(`/power-plants/${plantId}/basics`, BasicDataRequestDTO(formData)), 
    updateBasics: (formData, plantId) => api.put(`/power-plants/${plantId}/basics`, BasicDataRequestDTO(formData)), 

    // Geological 
    getGeological: (plantId) => api.get(`power-plants/${plantId}/geological`), 
    createGeological: (formData, plantId) => api.post(`/power-plants/${plantId}/geological`, GeologicalDataRequestDTO(formData)), 
    updateGeological: (formData, plantId) => api.put(`/power-plants/${plantId}/geological`, GeologicalDataRequestDTO(formData)), 

    // Technical 
    getTechnical: (plantId) => api.get(`/power-plants/${plantId}/technical`), 
    createTechnical: (formData, plantId) => api.post(`/power-plants/${plantId}/technical`, TechnicalDataRequestDTO(formData)), 
    updateTechnical: (formData, plantId) => api.put(`/power-plants/${plantId}/technical`, TechnicalDataRequestDTO(formData)) 
};  