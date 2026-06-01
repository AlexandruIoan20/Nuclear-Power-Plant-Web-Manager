export function TechnicalDataRequestDTO({
    numberOfReactors,
    estimatedEfficiency,
    operationalRiskLevel,
    reactorConfigurations = []
}) {
    return {
        numberOfReactors: numberOfReactors ? parseInt(numberOfReactors) : null,
        estimatedEfficiency: estimatedEfficiency ? parseFloat(estimatedEfficiency) : null,
        operationalRiskLevel: operationalRiskLevel ? parseFloat(operationalRiskLevel) : null,
        reactorConfigurations: reactorConfigurations,
    };
}