CREATE INDEX idx_measurements_reactor_ts ON measurements (reactor_id, timestamp DESC); 
CREATE INDEX idx_sensor_readings_sensor_ts ON sensor_readings(sensor_id, timestamp DESC); 
CREATE INDEX idx_measurements_ts ON measurements(timestamp); 
CREATE INDEX idx_measurements_hourly_ts ON measurements_hourly (hour DESC);
