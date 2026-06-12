export function SensorRequestDTO(data) {
    const dto = {};

    if (data.sensorCode !== undefined) dto.sensorCode = data.sensorCode;
    if (data.sensorType !== undefined) dto.sensorType = data.sensorType;
    if (data.description !== undefined) dto.description = data.description || null;
    if (data.locationZone !== undefined) dto.locationZone = data.locationZone || null;
    if (data.unitOfMeasure !== undefined) dto.unitOfMeasure = data.unitOfMeasure || null;
    if (data.measurementField !== undefined) dto.measurementField = data.measurementField || null;

    ['normalMin', 'normalMax', 'alarmLow', 'alarmHigh',
     'alertLow', 'alertHigh', 'scramLow', 'scramHigh'].forEach(f => {
        const v = data[f];
        dto[f] = (v !== undefined && v !== '' && v !== null) ? parseFloat(v) : null;
    });

    if (data.status !== undefined) dto.status = data.status;
    if (data.isActive !== undefined) dto.isActive = data.isActive === true || data.isActive === '1';

    if (data.lastCalibration !== undefined) dto.lastCalibration = data.lastCalibration || null;
    if (data.calibrationDue !== undefined) dto.calibrationDue = data.calibrationDue || null;

    return dto;
}
