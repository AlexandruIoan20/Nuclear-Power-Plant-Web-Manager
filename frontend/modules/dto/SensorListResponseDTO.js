export function SensorListResponseDTO(data) {
    return {
        id: data.id ?? null,
        reactorId: data.reactorId ?? null,
        sensorCode: data.sensorCode ?? null,
        sensorType: data.sensorType ?? null,
        status: data.status ?? null,
        currentValue: data.currentValue != null ? parseFloat(data.currentValue) : null,
        unitOfMeasure: data.unitOfMeasure ?? null,
        description: data.description ?? null,
        isActive: data.isActive === true,
        lastReadingAt: data.lastReadingAt ?? null,
    };
}
