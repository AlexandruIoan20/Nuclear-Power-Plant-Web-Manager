export function PlantStatusListResponseDTO(data) {
    return {
        id: data.id ?? null,
        name: data.name ?? null,
        status: data.status ?? null,
        createdBy: data.createdBy ?? null,
        createdAt: data.createdAt ?? null,
        updatedAt: data.updatedAt ?? null,
    };
}
