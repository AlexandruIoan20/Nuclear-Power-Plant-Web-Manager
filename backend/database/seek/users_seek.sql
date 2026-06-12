INSERT INTO users (username, first_name, last_name, email, password_hash, role)
VALUES (
    'admin',
    'Admin',
    'System',
    'admin@nuclear.ro',
    '$2y$12$VM.GOX.C1lh7xqrptnOslumQqcQha8w1MHRwXokv22QSqJHbreXS2',
    'ADMIN'
)
ON CONFLICT (email) DO UPDATE SET
    username = EXCLUDED.username,
    first_name = EXCLUDED.first_name,
    last_name = EXCLUDED.last_name,
    password_hash = EXCLUDED.password_hash,
    role = EXCLUDED.role;