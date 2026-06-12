import { api } from '../core/api.js';

export const adminApprovalService = {
    getPendingApprovals: () => api.get('/power-plants/pending-approvals'),

    updateStatus: (id, status) => api.patch(`/power-plants/${id}/admin-status`, { status })
};
