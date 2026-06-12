export function GetPlantDTO(data) {
    return {
        id: data.details?.id ?? null,
        name: data.details?.name ?? null,
        country: data.geological?.country ?? null,
        latitude: data.geological?.latitude ?? null,
        longitude: data.geological?.longitude ?? null,
        status: data.details?.status ?? null,

        basicId: data.basic?.id ?? null,
        capacity: data.basic?.capacity ?? null,
        constructionDurationYears: data.basic?.constructionDurationYears ?? null,
        description: data.basic?.description ?? null,

        geologicalId: data.geological?.id ?? null,
        soilType: data.geological?.soilType ?? null,
        waterSourceType: data.geological?.waterSourceType ?? null,
        seismicStability: data.geological?.seismicStability ?? null,
        floodRisk: data.geological?.floodRisk ?? null,
        groundwaterLevel: data.geological?.groundwaterLevel ?? null,
        waterProximity: data.geological?.waterProximity ?? null,
        waterFlowRate: data.geological?.waterFlowRate ?? null,
        populationDensity: data.geological?.populationDensity ?? null,
        transportInfrastructureScore: data.geological?.transportInfrastructureScore ?? null,
        geologicalRiskScore: data.geological?.geologicalRiskScore ?? null,

        technicalId: data.technical?.id ?? null,
        numberOfReactors: data.technical?.numberOfReactors ?? null,
        estimatedEfficiency: data.technical?.estimatedEfficiency ?? null,
        operationalRiskLevel: data.technical?.operationalRiskLevel ?? null,
        safetySystems: data.technical?.safetySystems ?? [],
        reactorConfigurations: data.technical?.reactorConfigurations ?? [],
    };
}
