CREATE TABLE reactor_sensors ( 
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(), 
    reactor_id UUID NOT NULL, 
    sensor_code VARCHAR(30) NOT NULL, 
    sensor_type sensor_types NOT NULL, 
    description VARCHAR(255), 
    location_zone VARCHAR(100), 
    unit_of_measure VARCHAR(20), 
    measurement_field VARCHAR(40),
    normal_min DECIMAL(20, 4), 
    normal_max DECIMAL(20, 4), 
    alarm_low DECIMAL(20, 4), 
    alarm_high DECIMAL(20, 4), 
    alert_low DECIMAL(20, 4), 
    alert_high DECIMAL(20, 4), 
    scram_low DECIMAL(20, 4), 
    scram_high DECIMAL(20, 4), 
    status sensor_quality NOT NULL DEFAULT 'GOOD', 
    is_active BOOLEAN NOT NULL DEFAULT TRUE, 
    last_calibration TIMESTAMP, 
    calibration_due TIMESTAMP, 
    current_value DECIMAL(20, 4), 
    last_reading_at TIMESTAMP, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    CONSTRAINT fk_sensor_reactor 
        FOREIGN KEY (reactor_id) REFERENCES reactor(id)
        ON DELETE CASCADE, 
    CONSTRAINT uq_sensor_code_per_reactor 
        UNIQUE(reactor_id, sensor_code)
); 

CREATE TABLE measurements (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    reactor_id UUID NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    power_percent DECIMAL(6,3),
    neutron_flux DECIMAL(20,4),
    reactivity_pcm DECIMAL(10,4),
    reactor_period_sec DECIMAL(10,2),
    temp_fuel_center DECIMAL(8,2),
    temp_coolant_in DECIMAL(8,2),
    temp_coolant_out DECIMAL(8,2),
    temp_moderator DECIMAL(8,2),
    pressure DECIMAL(8,3),
    flow_rate_primary DECIMAL(12,2),
    flow_rate_secondary DECIMAL(12,2),
    steam_pressure DECIMAL(8,3),
    steam_flow_rate DECIMAL(12,2),
    feedwater_temp DECIMAL(8,2),
    radiation DECIMAL(15,4),
    activity_primary DECIMAL(15,4),
    dose_rate_control_room DECIMAL(10,4),
    dose_rate_reactor_bldg DECIMAL(10,4),
    airborne_activity DECIMAL(15,4),
    fuel_burnup_mwd_t DECIMAL(10,2),
    efficiency DECIMAL(6,4),
    wear_delta DECIMAL(8,6),
    level_reactor_vessel DECIMAL(8,2),
    CONSTRAINT fk_measurements_reactor
        FOREIGN KEY (reactor_id) REFERENCES reactor(id)
        ON DELETE CASCADE
);

CREATE TABLE measurements_hourly (
    reactor_id UUID NOT NULL,
    hour TIMESTAMP NOT NULL,
    samples_count INT NOT NULL,
    power_percent_avg DECIMAL(6,3),
    power_percent_min DECIMAL(6,3),
    power_percent_max DECIMAL(6,3),
    neutron_flux_avg DECIMAL(20,4),
    temp_fuel_center_avg DECIMAL(8,2),
    temp_coolant_in_avg DECIMAL(8,2),
    temp_coolant_out_avg DECIMAL(8,2),
    temp_moderator_avg DECIMAL(8,2),
    pressure_avg DECIMAL(8,3),
    flow_rate_primary_avg DECIMAL(12,2),
    radiation_avg DECIMAL(15,4),
    efficiency_avg DECIMAL(6,4),
    wear_delta_sum DECIMAL(12,6),
    PRIMARY KEY (reactor_id, hour),
    CONSTRAINT fk_hourly_reactor
        FOREIGN KEY (reactor_id) REFERENCES reactor(id)
        ON DELETE CASCADE
);

CREATE TABLE sensor_readings ( 
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(), 
    sensor_id UUID NOT NULL, 
    timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    value DECIMAL(20, 4) NOT NULL, 
    quality sensor_quality NOT NULL DEFAULT 'GOOD', 
    raw_value DECIMAL(20, 4),
    CONSTRAINT fk_reading_sensor
        FOREIGN KEY (sensor_id) REFERENCES reactor_sensors(id)
        ON DELETE CASCADE
); 

CREATE TABLE sensor_templates (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    reactor_type reactor_types NOT NULL,
    sensor_code VARCHAR(30) NOT NULL,
    sensor_type sensor_types NOT NULL,
    description VARCHAR(255) NOT NULL,
    location_zone VARCHAR(100),
    unit_of_measure VARCHAR(20),
    measurement_field VARCHAR(40),
    normal_min DECIMAL(20,4),
    normal_max DECIMAL(20,4),
    alarm_low DECIMAL(20,4),
    alarm_high DECIMAL(20,4),
    alert_low DECIMAL(20,4),
    alert_high DECIMAL(20,4),
    scram_low DECIMAL(20,4),
    scram_high DECIMAL(20,4),
    UNIQUE (reactor_type, sensor_code)
);