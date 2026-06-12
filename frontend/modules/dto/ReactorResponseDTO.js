export function ReactorResponseDTO(data) {
    return {
        id: data.id ?? null,
        powerPlantId: data.powerPlantId ?? null,
        reactorCode: data.reactorCode ?? null,
        reactorType: data.reactorType ?? null,
        coolingType: data.coolingType ?? null,
        operationalStatus: data.operationalStatus ?? null,
        thermalPowerMw: data.thermalPowerMw != null ? parseFloat(data.thermalPowerMw) : null,
        electricalPowerMw: data.electricalPowerMw != null ? parseFloat(data.electricalPowerMw) : null,
        fuelCycleDays: data.fuelCycleDays != null ? parseInt(data.fuelCycleDays) : null,
        currentCycleDay: data.currentCycleDay != null ? parseInt(data.currentCycleDay) : null,
        wearIndex: data.wearIndex != null ? parseFloat(data.wearIndex) : null,
        designLifetimeYr: data.designLifetimeYr != null ? parseInt(data.designLifetimeYr) : null,
        commissioningDate: data.commissioningDate ?? null,
        firstCriticality: data.firstCriticality ?? null,
        lastInspectionAt: data.lastInspectionAt ?? null,
        nextPlannedOutage: data.nextPlannedOutage ?? null,
        description: data.description ?? null,
        createdAt: data.createdAt ?? null,
    };
}
