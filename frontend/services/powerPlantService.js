import { api } from '../core/api'; 
import { CreatePlantRequestDTO } from '../dto/CreatePlantRequestDTO'; 
import { UpdatePlantRequestDTO } from '../dto/UpdatePlantRequestDTO'; 

export const powerPlantService = { 
    create: (formData) => api.post("/power-plants", CreatePlantDTO(formData)), 
    update: (formData) => api.put(`/power-plants/${formData.id}`, UpdatePlantDTO(formData)), 
    getAll: () => api.get("/power-plants"), 
};  