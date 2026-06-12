export function BasicDataResponseDTO(data) {
    return {
        id: data.id ?? null,
        powerPlantId: data.powerPlantId ?? null,
        capacity: data.capacity != null ? parseFloat(data.capacity) : null,
        constructionDurationYears: data.constructionDurationYears != null ? parseInt(data.constructionDurationYears) : null,
        description: data.description ?? null,
        createdAt: data.createdAt ?? null,
        updatedAt: data.updatedAt ?? null,
    };
}
