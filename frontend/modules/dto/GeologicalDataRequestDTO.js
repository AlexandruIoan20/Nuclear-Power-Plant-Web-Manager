export function GeologicalDataRequestDTO({
    soilType,
    waterSourceType,
    seismicStability,
    floodRisk,
    groundwaterLevel,
    waterProximity,
    waterFlowRate,
    populationDensity,
    transportInfrastructureScore,
    geologicalRiskScore,
    country,
    latitude,
    longitude
}) {
    return {
        soilType: soilType || null,
        waterSourceType: waterSourceType || null,
        seismicStability: seismicStability != null ? parseFloat(seismicStability) : null,
        floodRisk: floodRisk != null ? parseFloat(floodRisk) : null,
        groundwaterLevel: groundwaterLevel != null ? parseFloat(groundwaterLevel) : null,
        waterProximity: waterProximity != null ? parseFloat(waterProximity) : null,
        waterFlowRate: waterFlowRate != null ? parseFloat(waterFlowRate) : null,
        populationDensity: populationDensity != null ? parseFloat(populationDensity) : null,
        transportInfrastructureScore: transportInfrastructureScore != null ? parseFloat(transportInfrastructureScore) : null,
        geologicalRiskScore: geologicalRiskScore != null ? parseFloat(geologicalRiskScore) : null,
        country: country || null,
        latitude: latitude != null ? parseFloat(latitude) : null,
        longitude: longitude != null ? parseFloat(longitude) : null,
    };
}
