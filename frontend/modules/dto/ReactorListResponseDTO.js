export function ReactorListResponseDTO(data) {
    return {
        id: data.id ?? null,
        reactorCode: data.reactorCode ?? null,
        reactorType: data.reactorType ?? null,
        coolingType: data.coolingType ?? null,
        operationalStatus: data.operationalStatus ?? null,
        thermalPowerMw: data.thermalPowerMw != null ? parseFloat(data.thermalPowerMw) : null,
        electricalPowerMw: data.electricalPowerMw != null ? parseFloat(data.electricalPowerMw) : null,
    };
}
