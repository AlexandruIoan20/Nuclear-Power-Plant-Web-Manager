CREATE TYPE power_plant_status AS ENUM (
    'DRAFT',
    'REVIEW',
    'APPROVED',
    'REJECTED'
);

CREATE TYPE soil_types AS ENUM (
    'BEDROCK', 'STIFF_CLAY', 'DENSE_SAND', 'GRAVEL', 'SHALE',        
    'LIMESTONE', 'SANDSTONE', 'SOFT_CLAY', 'LOOSE_SAND', 'SILT',         
    'LOAM', 'PEAT'          
);

CREATE TYPE water_source_types AS ENUM (
    'FRESH_WATER',
    'SALT_WATER',
    'BRACKISH_WATER'
);