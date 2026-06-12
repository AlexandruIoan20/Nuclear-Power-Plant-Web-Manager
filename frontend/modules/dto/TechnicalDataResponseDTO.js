export function TechnicalDataResponseDTO(data) {
    return {
        id: data.id ?? null,
        powerPlantId: data.powerPlantId ?? null,
        numberOfReactors: data.numberOfReactors != null ? parseInt(data.numberOfReactors) : null,
        estimatedEfficiency: data.estimatedEfficiency != null ? parseFloat(data.estimatedEfficiency) : null,
        operationalRiskLevel: data.operationalRiskLevel != null ? parseFloat(data.operationalRiskLevel) : null,
        safetySystems: Array.isArray(data.safetySystems) ? data.safetySystems : [],
        reactorConfigs: Array.isArray(data.reactorConfigs) ? data.reactorConfigs : [],
        createdAt: data.createdAt ?? null,
        updatedAt: data.updatedAt ?? null,
    };
}
