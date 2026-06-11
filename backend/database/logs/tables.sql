CREATE TABLE logs (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    level VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    context JSONB,
    user_id UUID,
    plant_id UUID,
    reactor_id UUID,
    source VARCHAR(20) DEFAULT 'backend',
    request_uri VARCHAR(255),
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_logs_created_at ON logs (created_at DESC);
CREATE INDEX idx_logs_level ON logs (level);
CREATE INDEX idx_logs_user_id ON logs (user_id);
