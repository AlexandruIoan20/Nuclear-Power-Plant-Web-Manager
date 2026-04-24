-- EXTENSIE pentru UUID
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

CREATE TYPE reactor_types AS ENUM (
    'PWR',
    'BWR',
    'PHWR',
    'FBR'
);

CREATE TYPE power_plant_status AS ENUM (
    'OPERATIONAL',
    'MAINTENANCE',
    'DECOMMISSIONED'
);

CREATE TABLE power_plants (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    country VARCHAR(100) NOT NULL,
    latitude DECIMAL(9,6),
    longitude DECIMAL(9,6),
    status power_plant_status NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by UUID,
    CONSTRAINT fk_powerplant_user
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE SET NULL
);

CREATE TABLE reactor (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    power_plant_id UUID NOT NULL,
    reactor_code VARCHAR(100) NOT NULL,
    status VARCHAR(50),
    commissioning_date DATE,
    CONSTRAINT fk_reactor_powerplant
        FOREIGN KEY (power_plant_id) REFERENCES power_plant(id)
        ON DELETE CASCADE
);

CREATE TABLE basic_data (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    power_plant_id UUID NOT NULL,
    reactor_type reactor_types NOT NULL,
    capacity_mw DECIMAL,
    construction_duration_years INT,
    description TEXT,
    CONSTRAINT fk_basicdata_powerplant
        FOREIGN KEY (power_plant_id) REFERENCES power_plant(id)
        ON DELETE CASCADE
);

CREATE TABLE measurements (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    reactor_id UUID NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    temperature DECIMAL,
    pressure DECIMAL,
    radiation DECIMAL,
    efficiency DECIMAL,
    CONSTRAINT fk_measurements_reactor
        FOREIGN KEY (reactor_id) REFERENCES reactor(id)
        ON DELETE CASCADE
);

CREATE TABLE geological_data (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    power_plant_id UUID NOT NULL,
    soil_type VARCHAR(100),
    seismic_stability DECIMAL,
    flood_risk DECIMAL,
    groundwater_level DECIMAL,
    water_proximity DECIMAL,
    geological_risk_score DECIMAL,
    CONSTRAINT fk_geological_powerplant
        FOREIGN KEY (power_plant_id) REFERENCES power_plant(id)
        ON DELETE CASCADE
);

CREATE TABLE technical_data (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    power_plant_id UUID NOT NULL,
    number_of_reactors INT,
    cooling_type VARCHAR(100),
    estimated_efficiency DECIMAL,
    operational_risk_level DECIMAL,
    safety_systems JSONB,
    CONSTRAINT fk_technical_powerplant
        FOREIGN KEY (power_plant_id) REFERENCES power_plant(id)
        ON DELETE CASCADE
);