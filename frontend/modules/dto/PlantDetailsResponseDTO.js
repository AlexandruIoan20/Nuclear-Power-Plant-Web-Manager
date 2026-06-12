export function PlantDetailsResponseDTO(data) {
    return {
        id: data.id ?? null,
        name: data.name ?? null,
        createdBy: data.createdBy ?? null,
        createdAt: data.createdAt ?? null,
        updatedAt: data.updatedAt ?? null,
    };
}
