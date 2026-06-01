export function PlantRequestDTO({ name, country, latitude, longitude }) { 
    return { name, country, latitude: parseFloat(latitude), longitude: parseFloat(longitude) }; 
}