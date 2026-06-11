CREATE EXTENSION IF NOT EXISTS "pgcrypto";

\i '/docker-entrypoint-initdb.d/users/types.sql'
\i '/docker-entrypoint-initdb.d/plants/types.sql'
\i '/docker-entrypoint-initdb.d/reactors/types.sql'
\i '/docker-entrypoint-initdb.d/sensors/types.sql'

\i '/docker-entrypoint-initdb.d/users/tables.sql'
\i '/docker-entrypoint-initdb.d/plants/tables.sql'
\i '/docker-entrypoint-initdb.d/reactors/tables.sql'
\i '/docker-entrypoint-initdb.d/reactors/reactor_alerts.sql'
\i '/docker-entrypoint-initdb.d/sensors/tables.sql'
\i '/docker-entrypoint-initdb.d/alerts/tables.sql'
\i '/docker-entrypoint-initdb.d/logs/tables.sql'

\i '/docker-entrypoint-initdb.d/sensors/indexes.sql'

\i '/docker-entrypoint-initdb.d/seek/users_seek.sql'
\i '/docker-entrypoint-initdb.d/seek/reactors_seek.sql'
\i '/docker-entrypoint-initdb.d/seek/sensors_seek.sql'
\i '/docker-entrypoint-initdb.d/seek/feasibility_demo_seek.sql'

