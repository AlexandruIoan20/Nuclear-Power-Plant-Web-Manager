import { api } from '../core/api.js'; 
import { FeasibilityReportResponseDTO } from '../dto/FeasibilityReportResponseDTO.js';

export const feasibilityReportService = { 
    createReport: (plantId) => api.post(`/power-plants/${plantId}/feasibility`), 
    getReport: async (plantId) => {
        const response = await api.get(`/power-plants/${plantId}/feasibility`);
        return FeasibilityReportResponseDTO(response.data ?? response);
    },
}