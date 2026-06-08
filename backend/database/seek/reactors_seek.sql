INSERT INTO reactor_schema (id, reactor_type, cooling_type)
SELECT 
    gen_random_uuid(), 
    r.reactor_type, 
    c.cooling_type
FROM 
    unnest(enum_range(NULL::reactor_types)) AS r(reactor_type)
CROSS JOIN 
    unnest(enum_range(NULL::cooling_types)) AS c(cooling_type);