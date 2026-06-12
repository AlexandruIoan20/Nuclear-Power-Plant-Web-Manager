import { api } from '../core/api.js';
import { UpdatePlantStatusRequestDTO } from '../dto/UpdatePlantStatusRequestDTO.js';
import { PendingApprovalsResponseDTO } from '../dto/PendingApprovalsResponseDTO.js';

export const adminApprovalService = {
    getPendingApprovals: async () => {
        const response = await api.get('/power-plants/pending-approvals');
        return (Array.isArray(response) ? response : response.data ?? []).map(PendingApprovalsResponseDTO);
    },

    updateStatus: (id, status) => api.patch(`/power-plants/${id}/admin-status`, UpdatePlantStatusRequestDTO({ status })),
};
