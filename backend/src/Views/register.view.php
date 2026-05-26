<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuclear Plant Control | Register</title>
    <link rel="stylesheet" href="http://localhost:8081/style.css">
</head>
<body>
    <div class="page-shell login-layout">
        <section class="login-card" aria-labelledby="register-title">
            <p class="eyebrow">CREATE ACCOUNT</p>
            <h1 id="register-title">Creare cont operator</h1>
            <p>
                Creează un cont nou pentru a accesa sistemul de monitorizare al centralelor nucleare.
            </p>

            <?php if (isset($_SESSION['register_error'])): ?>
                <div class="error-message" role="alert" aria-live="assertive" style="color: #ff6b6b; padding: 12px; background: rgba(255, 107, 107, 0.1); border-radius: 4px; margin-bottom: 16px;">
                    <strong>Eroare:</strong> <?php echo htmlspecialchars($_SESSION['register_error']); ?>
                </div>
                <?php unset($_SESSION['register_error']); ?>
            <?php endif; ?>

            <form class="field-grid" method="post" action="/register" id="registerForm">
                <div class="field">
                    <label for="name">Nume complet</label>
                    <input id="name" name="name" type="text" placeholder="Ion Popescu" autocomplete="name" required>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" placeholder="ion.popescu@nuclear.ro" autocomplete="email" required>
                </div>

                <div class="field">
                    <label for="password">Parolă</label>
                    <input id="password" name="password" type="password" placeholder="••••••••" autocomplete="new-password" required>
                </div>

                <div class="field">
                    <label for="password_confirm">Confirmă parolă</label>
                    <input id="password_confirm" name="password_confirm" type="password" placeholder="••••••••" autocomplete="new-password" required>
                </div>

                <div class="button-row">
                    <input type="submit" value="Creare cont">
                    <a class="button secondary" href="/login">Ai deja cont?</a>
                </div>
            </form>

            <div class="notice" style="margin-top: 22px;">
                <strong class="inline-status">SECURE CHANNEL READY</strong>
                <p class="footer-note">Conexiune criptată. Datele tale vor fi protejate cu bcrypt encryption.</p>
            </div>
        </section>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const password_confirm = document.getElementById('password_confirm').value;

            if (password !== password_confirm) {
                e.preventDefault();
                alert('Parolele nu se potrivesc!');
                return false;
            }

            if (password.length < 6) {
                e.preventDefault();
                alert('Parola trebuie să aibă cel puțin 6 caractere!');
                return false;
            }
        });
    </script>
</body>
</html>
