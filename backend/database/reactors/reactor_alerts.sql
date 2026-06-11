CREATE TABLE reactor_alerts (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    reactor_id UUID NOT NULL,
    plant_id UUID NOT NULL,
    type VARCHAR(20) NOT NULL,
    severity VARCHAR(20) NOT NULL,
    sensor_type VARCHAR(50),
    value DECIMAL(12,4),
    threshold DECIMAL(12,4),
    message TEXT NOT NULL,
    is_read SMALLINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ra_reactor
        FOREIGN KEY (reactor_id) REFERENCES reactor(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_ra_plant
        FOREIGN KEY (plant_id) REFERENCES power_plants(id)
        ON DELETE CASCADE
);
