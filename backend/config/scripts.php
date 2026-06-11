<?php

/**
 * Configurare centralizată pentru scripturile long-running.
 *
 * Modifică valorile de mai jos, apoi rulează comenzile Docker:
 *
 *   docker compose restart simulator      # după schimbarea SIMULATOR_TICK_INTERVAL
 *   docker compose restart aggregator     # după schimbarea AGGREGATOR_INTERVAL
 *   docker compose restart cleanup        # după schimbarea CLEANUP_INTERVAL
 *
 *   docker compose logs -f simulator      # vezi log-urile în timp real
 *   docker compose logs -f aggregator
 *   docker compose logs -f cleanup
 */

define('SIMULATOR_TICK_INTERVAL', 1);
define('AGGREGATOR_INTERVAL', 10);
define('CLEANUP_INTERVAL', 20);
// Șterge date mai vechi de CLEANUP_INTERVAL secunde. Dacă CLEANUP_INTERVAL=20,
// păstrează doar ultimele 20s de date. Dacă e 3600, păstrează ultima oră.
