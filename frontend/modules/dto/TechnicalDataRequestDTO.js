export function TechnicalDataRequestDTO({
    numberOfReactors,
    estimatedEfficiency,
    operationalRiskLevel,
    reactorConfigurations = []
}) {
    return {
        numberOfReactors: numberOfReactors !== undefined && numberOfReactors !== null ? Number(numberOfReactors) : null,
        estimatedEfficiency: estimatedEfficiency ? parseFloat(estimatedEfficiency) : null,
        operationalRiskLevel: operationalRiskLevel ? parseFloat(operationalRiskLevel) : null,
        reactorConfigurations: reactorConfigurations,
    };
}