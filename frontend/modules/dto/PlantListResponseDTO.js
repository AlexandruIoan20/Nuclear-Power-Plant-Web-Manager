export function PlantListResponseDTO({ id, name, country, latitude, longitude, status }) { 
    return { id, name, country, latitude: parseFloat(latitude), longitude: parseFloat(longitude), status }; 
}