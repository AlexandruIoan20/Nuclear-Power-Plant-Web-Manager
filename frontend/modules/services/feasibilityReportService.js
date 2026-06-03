import { api } from '../core/api.js'; 

export const feasibilityReportService = { 
    createReport: (plantId) => api.post(`/power-plants/${plantId}/feasibility`), 
    getReport: (plantId) => api.get(`/power-plants/${plantId}/feasibility`)
}