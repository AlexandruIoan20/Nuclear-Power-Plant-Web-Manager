export function TechnicalDataRequestDTO({
    numberOfReactors,
    estimatedEfficiency,
    operationalRiskLevel,
    reactorConfigurations = []
}) {
    return {
        numberOfReactors: numberOfReactors !== undefined && numberOfReactors !== null ? Number(numberOfReactors) : null,
        estimatedEfficiency: estimatedEfficiency != null ? parseFloat(estimatedEfficiency) : null,
        operationalRiskLevel: operationalRiskLevel != null ? parseFloat(operationalRiskLevel) : null,
        reactorConfigurations: reactorConfigurations,
    };
}