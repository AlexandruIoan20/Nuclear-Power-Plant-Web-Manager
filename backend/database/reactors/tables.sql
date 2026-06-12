CREATE TABLE reactor (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(), 
    power_plant_id UUID NOT NULL, 
    reactor_code VARCHAR(100) NOT NULL, 
    reactor_type reactor_types NOT NULL, 
    cooling_type cooling_types NOT NULL, 
    operational_status reactor_operational_status NOT NULL DEFAULT 'SHUTDOWN', 
    thermal_power_mw DECIMAL(10,2),
    electrical_power_mw  DECIMAL(10,2), 
    fuel_cycle_days INT DEFAULT 365,
    current_cycle_day INT DEFAULT 0,
    wear_index DECIMAL(5,4) DEFAULT 0.0000 CHECK (wear_index BETWEEN 0 AND 1),
    design_lifetime_yr INT DEFAULT 40,
    commissioning_date DATE,
    first_criticality DATE,
    last_inspection_at TIMESTAMP,
    next_planned_outage TIMESTAMP,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reactor_powerplant
        FOREIGN KEY (power_plant_id) REFERENCES power_plants(id)
        ON DELETE CASCADE
);

CREATE TABLE reactor_schema (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(), 
    reactor_type reactor_types NOT NULL, 
    cooling_type cooling_types
); 

CREATE TABLE reactor_plant_data (
    technical_data_id UUID NOT NULL,
    reactor_schema_id UUID NOT NULL,
    number_of_reactors INT NOT NULL, 
    PRIMARY KEY (technical_data_id, reactor_schema_id), 
    CONSTRAINT fk_config_technical 
        FOREIGN KEY (technical_data_id) REFERENCES technical_data(id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_config_schema 
        FOREIGN KEY (reactor_schema_id) REFERENCES reactor_schema(id) 
        ON DELETE RESTRICT 
);

CREATE TABLE control_rods ( 
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(), 
    reactor_id UUID NOT NULL, 
    rod_group VARCHAR(10) NOT NULL, 
    rod_number INT NOT NULL, 
    material VARCHAR(50) DEFAULT 'Ag-In-Cd', 
    position_mm DECIMAL(8, 2), 
    position_percent DECIMAL(5, 2),
    is_inserted BOOLEAN NOT NULL DEFAULT TRUE, 
    status VARCHAR(30) DEFAULT 'OPERATIONAL', 
    last_inspection TIMESTAMP, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    CONSTRAINT fk_rod_reactor
        FOREIGN KEY (reactor_id) REFERENCES reactor(id)
        ON DELETE CASCADE, 
    CONSTRAINT uq_rod_group_number
        UNIQUE (reactor_id, rod_group, rod_number)
);