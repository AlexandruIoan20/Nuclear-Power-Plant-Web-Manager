import { api } from '../core/api.js'; 
import { CreatePlantRequestDTO } from '../dto/CreatePlantRequestDTO.js'; 
import { UpdatePlantRequestDTO } from '../dto/UpdatePlantRequestDTO.js'; 

export const powerPlantService = { 
    create: (formData) => api.post("/power-plants", CreatePlantRequestDTO(formData)), 
    update: (formData) => api.put(`/power-plants/${formData.id}`, UpdatePlantRequestDTO(formData)), 
    getAll: () => api.get("/power-plants"), 
};  