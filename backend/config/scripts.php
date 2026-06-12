<?php

/**
 *
 *   docker compose restart simulator      # după schimbarea SIMULATOR_TICK_INTERVAL
 *   docker compose restart aggregator     # după schimbarea AGGREGATOR_INTERVAL
 *   docker compose restart cleanup        # după schimbarea CLEANUP_INTERVAL
 *
 *   docker compose logs -f simulator      # vezi log-urile în timp real
 *   docker compose logs -f aggregator
 *   docker compose logs -f cleanup
 */

define('SIMULATOR_TICK_INTERVAL', 3);
define('AGGREGATOR_INTERVAL', 60);
define('CLEANUP_INTERVAL', 3600);