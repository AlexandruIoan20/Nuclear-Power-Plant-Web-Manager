export function PlantMapResponseDTO(data) {
    return {
        id: data.id ?? null,
        name: data.name ?? null,
        country: data.country ?? null,
        latitude: data.latitude != null ? parseFloat(data.latitude) : null,
        longitude: data.longitude != null ? parseFloat(data.longitude) : null,
        status: data.status ?? null,
        hasCoordinates: data.hasCoordinates === true,
        coordinatesLabel: data.coordinatesLabel ?? null,
        popupTitle: data.popupTitle ?? null,
        popupSubtitle: data.popupSubtitle ?? null,
        editUrl: data.editUrl ?? null,
    };
}
