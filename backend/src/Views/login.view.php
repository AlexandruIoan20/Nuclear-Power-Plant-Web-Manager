<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuclear Plant Control | Login</title>
    <link rel="stylesheet" href="http://localhost:8081/style.css">
</head>
<body>
    <div class="page-shell login-layout">
        <section class="login-card" aria-labelledby="login-title">
            <p class="eyebrow">ACCESS TERMINAL</p>
            <h1 id="login-title">Autentificare operator</h1>
            <p>
                Introdu datele de acces pentru a continua către dashboard-ul de monitorizare.
            </p>

            <?php if (isset($_SESSION['login_error'])): ?>
                <div class="error-message" style="color: #ff6b6b; padding: 12px; background: rgba(255, 107, 107, 0.1); border-radius: 4px; margin-bottom: 16px;">
                    <strong>Eroare:</strong> <?php echo htmlspecialchars($_SESSION['login_error']); ?>
                </div>
                <?php unset($_SESSION['login_error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['register_success'])): ?>
                <div class="success-message" style="color: #00ff00; padding: 12px; background: rgba(0, 255, 0, 0.1); border-radius: 4px; margin-bottom: 16px;">
                    <strong>Succes:</strong> <?php echo htmlspecialchars($_SESSION['register_success']); ?>
                </div>
                <?php unset($_SESSION['register_success']); ?>
            <?php endif; ?>

            <form class="field-grid" method="post" action="http://localhost:8081/login" id="loginForm">
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" placeholder="operator@nuclear.ro" autocomplete="email" required>
                </div>

                <div class="field">
                    <label for="password">Parolă</label>
                    <input id="password" name="password" type="password" placeholder="••••••••" autocomplete="current-password" required>
                </div>

                <div class="button-row">
                    <input type="submit" value="Conectare">
                    <a class="button secondary" href="http://localhost:8081/register">Crează cont</a>
                </div>
            </form>

            <div class="notice" style="margin-top: 22px;">
                <strong class="inline-status">SECURE CHANNEL READY</strong>
                <p class="footer-note">Conexiune criptată. Sistemul utilizează sesiuni sigure pentru gestionarea stării de autentificare.</p>
            </div>
        </section>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            if (!email || !password) {
                e.preventDefault();
                alert('Vă rugăm completați toate câmpurile!');
                return false;
            }

            if (!email.includes('@')) {
                e.preventDefault();
                alert('Vă rugăm introduceți o adresă de email validă!');
                return false;
            }
        });
    </script>
</body>
</html>