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