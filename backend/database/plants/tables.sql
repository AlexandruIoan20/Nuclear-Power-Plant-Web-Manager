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

CREATE TABLE feasibility_reports (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(), 
    power_plant_id UUID NOT NULL, 
    deficiencies JSONB, 
    status power_plant_status NOT NULL, 
    nsvi_score DECIMAL(5, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    CONSTRAINT fk_report_powerplant
        FOREIGN KEY (power_plant_id) REFERENCES power_plants(id)
        ON DELETE CASCADE 
); 

CREATE TABLE basic_data (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    power_plant_id UUID NOT NULL,
    capacity_mw DECIMAL,
    construction_duration_years INT,
    description TEXT,
    CONSTRAINT fk_basicdata_powerplant
        FOREIGN KEY (power_plant_id) REFERENCES power_plants(id)
        ON DELETE CASCADE,
    CONSTRAINT unique_power_plant_basic UNIQUE (power_plant_id)
);

CREATE TABLE geological_data (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    power_plant_id UUID NOT NULL,
    soil_type soil_types,
    water_source_type water_source_types,
    seismic_stability DECIMAL,
    flood_risk DECIMAL,
    groundwater_level DECIMAL,
    water_proximity DECIMAL,
    water_flow_rate DECIMAL,
    population_density DECIMAL,
    transport_infrastructure_score DECIMAL,
    geological_risk_score DECIMAL,
    CONSTRAINT fk_geological_powerplant
        FOREIGN KEY (power_plant_id) REFERENCES power_plants(id)
        ON DELETE CASCADE,
    CONSTRAINT unique_power_plant_geological UNIQUE (power_plant_id)
);

CREATE TABLE technical_data (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    power_plant_id UUID NOT NULL,
    number_of_reactors INT,
    estimated_efficiency DECIMAL,
    operational_risk_level DECIMAL,
    safety_systems JSONB,
    CONSTRAINT fk_technical_powerplant
        FOREIGN KEY (power_plant_id) REFERENCES power_plants(id)
        ON DELETE CASCADE,
    CONSTRAINT unique_power_plant_technical UNIQUE (power_plant_id)
);