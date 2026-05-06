<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuclear Plant Control | Start</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="page-shell">
        <header class="topbar">
            <div class="brand">
                <strong>Nuclear Plant Control</strong>
                <span>Consolă industrială pentru monitorizarea centralelor nucleare</span>
            </div>
            <nav class="nav-links" aria-label="Navigare principală">
                <a href="login.html">Login</a>
                <a href="#overview">Overview</a>
            </nav>
        </header>

        <main class="hero" id="overview">
            <section>
                <p class="eyebrow">SYSTEM BOOT / START PAGE</p>
                <h1>Acces rapid la controlul operațional al centralelor</h1>
                <p>
                    Interfață multi-pagină construită în stil terminal, cu accent pe vizibilitate,
                    status critic și navigare directă între modulele de operare. Pagina de start introduce
                    fluxul aplicației și oferă acces către zona de autentificare.
                </p>
                <p class="inline-status">SYSTEM STATUS: ONLINE</p>

                <div class="hero-actions">
                    <a class="button" href="login.html">Accesează login</a>
                    <a class="button secondary" href="login.html">Intrare operator</a>
                </div>

                <div class="stats" aria-label="Indicatori sintetici">
                    <div class="stat">
                        <strong>12</strong>
                        <span>centrale monitorizate</span>
                    </div>
                    <div class="stat">
                        <strong>04</strong>
                        <span>alerte active</span>
                    </div>
                    <div class="stat">
                        <strong>99%</strong>
                        <span>disponibilitate sistem</span>
                    </div>
                </div>
            </section>

            <aside class="image-frame" aria-label="Ilustrație sugestivă a centrului de comandă">
                <img src="http://localhost:8081/assets/startpage_asset.jpeg" alt="Ilustrație centru de comandă" style="width:100%;height:auto;border-radius:12px;object-fit:cover;"/>
            </aside>
        </main>
    </div>
</body>
</html>