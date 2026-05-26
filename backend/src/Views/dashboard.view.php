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
                <span>Overview operațional pentru centralele nucleare</span>
            </div>
            <nav class="nav-links" aria-label="Navigare principală">
                <a href="/start">Start</a>
                <a href="/dashboard" class="active">Dashboard</a>
                <a href="/logout">Logout</a>
            </nav>
        </header>

        <main class="hero" id="dashboard">
            <section>
                <p class="eyebrow">CONTROL PANEL / OVERVIEW</p>
                <h1>Bun venit, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Operator'); ?>!</h1>
                <p>
                    Ești conectat cu succes. Aici pornește navigarea către notificări, aprobări, harta centralelor și lista de unități.
                </p>

                <div class="status-card" style="margin: 18px 0 26px;">
                    <p class="inline-status" style="margin-bottom: 8px;">USER SESSION ACTIVE</p>
                    <h2 style="margin: 0 0 6px;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Operator'); ?></h2>
                    <p style="margin: 0; color: var(--muted);">
                        <?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>
                        <?php if (!empty($_SESSION['user_role'])): ?>
                            · Rol: <?php echo htmlspecialchars($_SESSION['user_role']); ?>
                        <?php endif; ?>
                    </p>
                </div>

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

                <div class="hero-actions" style="margin-top: 30px; flex-wrap: wrap; gap: 12px;">
                    <a class="button" href="/notifications.html">Notificări</a>
                    <a class="button" href="/approvals.html">Aprobări</a>
                    <a class="button" href="/map.html">Harta centralelor</a>
                    <a class="button secondary" href="/power-plants/list.html">Lista centralelor</a>
                    <a class="button secondary" href="/logout">Deconectare</a>
                </div>

                <div class="field-grid" style="margin-top: 28px;">
                    <div class="card">
                        <p class="eyebrow">NAVIGARE RAPIDĂ</p>
                        <h3>Fluxul din diagrama paginilor</h3>
                        <p style="color: var(--muted);">Start → Login → Dashboard → Notificări / Aprobări / Harta centralelor / Lista centralelor.</p>
                    </div>
                    <div class="card">
                        <p class="eyebrow">NEXT STEP</p>
                        <h3>Centrale și reactoare</h3>
                        <p style="color: var(--muted);">Din lista de centrale poți intra în view/update, apoi în date de bază, geologice și tehnice.</p>
                        <div class="button-row" style="margin-top: 14px;">
                            <a class="button secondary" href="/power-plants/list.html">Deschide lista</a>
                            <a class="button secondary" href="/reactor.html">Vezi reactor</a>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>