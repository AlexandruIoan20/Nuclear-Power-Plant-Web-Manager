export function GeologicalDataResponseDTO(data) {
    return {
        id: data.id ?? null,
        powerPlantId: data.powerPlantId ?? null,
        country: data.country ?? null,
        latitude: data.latitude != null ? parseFloat(data.latitude) : null,
        longitude: data.longitude != null ? parseFloat(data.longitude) : null,
        soilType: data.soilType ?? null,
        waterSourceType: data.waterSourceType ?? null,
        seismicStability: data.seismicStability != null ? parseFloat(data.seismicStability) : null,
        floodRisk: data.floodRisk != null ? parseFloat(data.floodRisk) : null,
        groundwaterLevel: data.groundwaterLevel != null ? parseFloat(data.groundwaterLevel) : null,
        waterProximity: data.waterProximity != null ? parseFloat(data.waterProximity) : null,
        waterFlowRate: data.waterFlowRate != null ? parseFloat(data.waterFlowRate) : null,
        populationDensity: data.populationDensity != null ? parseFloat(data.populationDensity) : null,
        transportInfrastructureScore: data.transportInfrastructureScore != null ? parseFloat(data.transportInfrastructureScore) : null,
        geologicalRiskScore: data.geologicalRiskScore != null ? parseFloat(data.geologicalRiskScore) : null,
        createdAt: data.createdAt ?? null,
        updatedAt: data.updatedAt ?? null,
    };
}
