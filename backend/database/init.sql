-- EXTENSIE pentru UUID
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

CREATE TYPE user_roles AS ENUM (
    'ADMIN',
    'ENGINEER',
    'OPERATOR'
);

CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    username VARCHAR(30) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role user_roles NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TYPE reactor_types AS ENUM (
    'PWR',
    'BWR',
    'PHWR',
    'FBR'
);

CREATE TYPE power_plant_status AS ENUM (
    'DRAFT',
    'REVIEW',
    'APPROVED',
    'REJECTED'
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
    reactor_type reactor_types NOT NULL, 
    status VARCHAR(50),
    commissioning_date DATE,
    CONSTRAINT fk_reactor_powerplant
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

CREATE TYPE soil_types AS ENUM (
    'BEDROCK',      
    'STIFF_CLAY',   
    'DENSE_SAND',   
    'GRAVEL',       
    'SHALE',        
    'LIMESTONE',    
    'SANDSTONE',    
    'SOFT_CLAY',    
    'LOOSE_SAND',  
    'SILT',         
    'LOAM',         
    'PEAT'          
);

-- Corectat virgula de la final
CREATE TYPE water_source_types AS ENUM (
    'FRESH_WATER',
    'SALT_WATER',
    'BRACKISH_WATER'
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
        ON DELETE CASCADE
);

CREATE TYPE cooling_types AS ENUM (
    'ONCE_THROUGH_FRESH',     
    'ONCE_THROUGH_SALT',       
    'NATURAL_DRAFT_WET',       
    'MECHANICAL_DRAFT_WET',    
    'DRY_COOLING',            
    'HYBRID',                  
    'COOLING_POND'             
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
    PRIMARY KEY (technical_data_id, reactor_schema_id), 
    CONSTRAINT fk_config_technical 
        FOREIGN KEY (technical_data_id) REFERENCES technical_data(id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_config_schema 
        FOREIGN KEY (reactor_schema_id) REFERENCES reactor_schema(id) 
        ON DELETE RESTRICT 
);