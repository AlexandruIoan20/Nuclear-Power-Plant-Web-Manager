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
        seismicStability: seismicStability ? parseFloat(seismicStability) : null,
        floodRisk: floodRisk ? parseFloat(floodRisk) : null,
        groundwaterLevel: groundwaterLevel ? parseFloat(groundwaterLevel) : null,
        waterProximity: waterProximity ? parseFloat(waterProximity) : null,
        waterFlowRate: waterFlowRate ? parseFloat(waterFlowRate) : null,
        populationDensity: populationDensity ? parseFloat(populationDensity) : null,
        transportInfrastructureScore: transportInfrastructureScore ? parseFloat(transportInfrastructureScore) : null,
        geologicalRiskScore: geologicalRiskScore ? parseFloat(geologicalRiskScore) : null,
        country: country || null,
        latitude: latitude ? parseFloat(latitude) : null,
        longitude: longitude ? parseFloat(longitude) : null,
    };
}
