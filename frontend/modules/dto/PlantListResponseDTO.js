export function PlantListResponseDTO({ id, name, country, latitude, longitude, status, createdBy, createdAt, updatedAt }) { 
    return {
        id, name, country,
        latitude: latitude != null ? parseFloat(latitude) : null,
        longitude: longitude != null ? parseFloat(longitude) : null,
        status, createdBy, createdAt, updatedAt
    };
}