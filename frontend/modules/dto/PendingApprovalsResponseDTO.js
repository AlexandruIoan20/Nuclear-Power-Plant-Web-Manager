export function PendingApprovalsResponseDTO(data) {
    return {
        id: data.id ?? null,
        name: data.name ?? null,
        country: data.country ?? null,
        latitude: data.latitude != null ? parseFloat(data.latitude) : null,
        longitude: data.longitude != null ? parseFloat(data.longitude) : null,
        status: data.status ?? null,
        createdBy: data.createdBy ?? null,
        createdAt: data.createdAt ?? null,
        updatedAt: data.updatedAt ?? null,
    };
}
