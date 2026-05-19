<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuclear Plant Control | Dashboard</title>
    <link rel="stylesheet" href="http://localhost:8081/style.css">
</head>
<body>
    <div class="page-shell">
        <header class="topbar">
            <div class="brand">
                <strong>Nuclear Plant Control</strong>
                <span>Panou de control pentru administratori</span>
            </div>
            <nav class="nav-links" aria-label="Navigare principală">
                <span style="color: #00ff00; font-weight: bold;">
                    <?php echo htmlspecialchars($_SESSION['username'] ?? 'Unknown'); ?> 
                    <small>(<?php echo htmlspecialchars($_SESSION['user_role'] ?? ''); ?>)</small>
                </span>
                <a href="/logout">Logout</a>
                <a href="/start">Start</a>
            </nav>
        </header>

        <main class="hero" id="dashboard">
            <section>
                <p class="eyebrow">CONTROL PANEL / DASHBOARD</p>
                <h1>Bun venit, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Operator'); ?>!</h1>
                <p>
                    Ești conectat cu succes. Acest panou este disponibil doar pentru utilizatorii autentificați.
                </p>

                <div class="stats" aria-label="Status utilizator">
                    <div class="stat">
                        <strong><?php echo htmlspecialchars($_SESSION['user_role'] ?? 'N/A'); ?></strong>
                        <span>Rol utilizator</span>
                    </div>
                    <div class="stat">
                        <strong><?php echo htmlspecialchars($_SESSION['user_email'] ?? 'N/A'); ?></strong>
                        <span>Email conectat</span>
                    </div>
                    <div class="stat">
                        <strong>ONLINE</strong>
                        <span>Status sesiune</span>
                    </div>
                </div>

                <div class="hero-actions" style="margin-top: 30px;">
                    <a class="button" href="/api/power-plants/list">Accesează centrale nucleare</a>
                    <a class="button secondary" href="/logout">Deconectare</a>
                </div>
            </section>
        </main>
    </div>
</body>
</html>