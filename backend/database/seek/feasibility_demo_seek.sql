WITH new_plant AS (
    INSERT INTO power_plants (id, name, country, latitude, longitude, status)
    VALUES (
        gen_random_uuid(),
        'Centrala Nucleara Valea Jiului',
        'Romania',
        45.365000,
        23.365000,
        'DRAFT'
    )
    RETURNING id
),
new_basic AS (
    INSERT INTO basic_data (id, power_plant_id, capacity_mw, construction_duration_years, description)
    SELECT
        gen_random_uuid(),
        id,
        1400.0,
        5,
        'Centrala nucleara amplasata in bazinul Vaii Jiului, pe strat de granit, langa Raul Jiului.'
    FROM new_plant
),
new_geological AS (
    INSERT INTO geological_data (
        id, power_plant_id, soil_type, water_source_type,
        seismic_stability, flood_risk, groundwater_level,
        water_proximity, water_flow_rate, population_density,
        transport_infrastructure_score, geological_risk_score
    )
    SELECT
        gen_random_uuid(),
        id,
        'BEDROCK',
        'FRESH_WATER',
        7.5,
        3.0,
        15.0,
        1.5,
        200.0,
        80.0,
        8.0,
        1.5
    FROM new_plant
),
new_technical AS (
    INSERT INTO technical_data (id, power_plant_id, number_of_reactors, estimated_efficiency, operational_risk_level, safety_systems)
    SELECT
        gen_random_uuid(),
        id,
        2,
        36.0,
        0.2,
        '["Emergency Core Cooling System (ECCS)", "Reactor Protection System (RPS)", "Containment Spray System", "Diesel Generators (4x100%)", "Spent Fuel Pool Cooling System"]'::jsonb
    FROM new_plant
    RETURNING id
)
INSERT INTO reactor_plant_data (technical_data_id, reactor_schema_id, number_of_reactors)
SELECT
    new_technical.id,
    rs.id,
    2
FROM new_technical, reactor_schema rs
WHERE rs.reactor_type = 'PWR' AND rs.cooling_type = 'NATURAL_DRAFT_WET';
