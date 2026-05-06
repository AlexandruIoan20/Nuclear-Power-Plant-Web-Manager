<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuclear Plant Control | Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="page-shell login-layout">
        <section class="login-card" aria-labelledby="login-title">
            <p class="eyebrow">ACCESS TERMINAL</p>
            <h1 id="login-title">Autentificare operator</h1>
            <p>
                Introdu datele de acces pentru a continua către dashboard-ul de monitorizare.
            </p>

            <form class="field-grid" action="dashboard.html" method="get">
                <div class="field">
                    <label for="username">Nume utilizator</label>
                    <input id="username" name="username" type="text" placeholder="operator.nuclear" autocomplete="username" required>
                </div>

                <div class="field">
                    <label for="password">Parolă</label>
                    <input id="password" name="password" type="password" placeholder="••••••••" autocomplete="current-password" required>
                </div>

                <div class="button-row">
                    <input type="submit" value="Conectare">
                    <a class="button secondary" href="index.html">Înapoi la start</a>
                </div>
            </form>

            <div class="notice" style="margin-top: 22px;">
                <strong class="inline-status">SECURE CHANNEL READY</strong>
                <p class="footer-note">Pagina este intenționat statică și pregătită pentru integrarea paginilor următoare ale fluxului multi-pagină.</p>
            </div>
        </section>
    </div>
</body>
</html>