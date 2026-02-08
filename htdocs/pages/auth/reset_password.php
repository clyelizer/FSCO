<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation mot de passe - FSCo</title>
    <link rel="stylesheet" href="../../index.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background-color: var(--light-bg);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .auth-card {
            background: var(--card-bg);
            padding: 2.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 400px;
            text-align: center;
            border: 1px solid var(--border-color);
        }

        .auth-logo {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            display: block;
            text-decoration: none;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
        }

        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 1rem;
            color: var(--text-color);
            background-color: var(--light-bg);
            transition: border-color 0.3s;
        }

        .form-input:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .btn-block {
            width: 100%;
            margin-top: 1rem;
        }

        .message {
            margin-top: 1rem;
            font-size: 0.9rem;
            display: none;
            padding: 10px;
            border-radius: 5px;
        }

        .message.success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .message.error {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Password Toggle Styles */
        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-muted);
            z-index: 10;
        }

        .toggle-password:hover {
            color: var(--primary-color);
        }
    </style>
</head>

<body>

    <div class="auth-card">
        <a href="../../index.php" class="auth-logo">
            <i class="fas fa-brain"></i> FSCo
        </a>

        <?php if (isset($_GET['token'])): ?>
            <!-- STEP 2: NEW PASSWORD -->
            <h2>Nouveau mot de passe</h2>
            <form id="form-reset-password">
                <input type="hidden" id="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                <div class="form-group">
                    <label class="form-label">Nouveau mot de passe</label>
                    <div class="password-wrapper">
                        <input type="password" id="new-password" class="form-input" required minlength="6">
                        <i class="fas fa-eye toggle-password" onclick="togglePassword('new-password', this)"></i>
                    </div>
                </div>
                <button type="submit" class="btn btn--primary btn-block">Modifier</button>
                <div class="message" id="message"></div>
            </form>
        <?php else: ?>
            <!-- STEP 1: REQUEST RESET -->
            <h2>Mot de passe oublié</h2>
            <p style="color: #666; margin-bottom: 1.5rem;">Entrez votre email pour recevoir un lien de réinitialisation.</p>
            <form id="form-request-reset">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" id="email" class="form-input" required>
                </div>
                <button type="submit" class="btn btn--primary btn-block">Envoyer le lien</button>
                <div class="message" id="message"></div>
                <a href="login.php"
                    style="display: block; margin-top: 1rem; color: var(--secondary-color); font-size: 0.9rem;">Retour à la
                    connexion</a>
            </form>
        <?php endif; ?>
    </div>

    <script>
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        const requestForm = document.getElementById('form-request-reset');
        const resetForm = document.getElementById('form-reset-password');
        const messageDiv = document.getElementById('message');

        if (requestForm) {
            requestForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const email = document.getElementById('email').value;
                const btn = e.target.querySelector('button');

                btn.disabled = true;
                btn.textContent = 'Envoi...';
                messageDiv.style.display = 'none';

                try {
                    const formData = new FormData();
                    formData.append('action', 'reset_request');
                    formData.append('email', email);

                    const response = await fetch('auth_actions.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();

                    messageDiv.textContent = data.message;
                    messageDiv.className = 'message ' + data.status;
                    messageDiv.style.display = 'block';
                } catch (err) {
                    messageDiv.textContent = "Erreur serveur.";
                    messageDiv.className = 'message error';
                    messageDiv.style.display = 'block';
                } finally {
                    btn.disabled = false;
                    btn.textContent = 'Envoyer le lien';
                }
            });
        }

        if (resetForm) {
            resetForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const password = document.getElementById('new-password').value;
                const token = document.getElementById('token').value;
                const btn = e.target.querySelector('button');

                btn.disabled = true;
                btn.textContent = 'Modification...';
                messageDiv.style.display = 'none';

                try {
                    const formData = new FormData();
                    formData.append('action', 'reset_password');
                    formData.append('token', token);
                    formData.append('password', password);

                    const response = await fetch('auth_actions.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();

                    messageDiv.textContent = data.message;
                    messageDiv.className = 'message ' + data.status;
                    messageDiv.style.display = 'block';

                    if (data.status === 'success') {
                        setTimeout(() => {
                            window.location.href = 'login.php';
                        }, 2000);
                    }
                } catch (err) {
                    messageDiv.textContent = "Erreur serveur.";
                    messageDiv.className = 'message error';
                    messageDiv.style.display = 'block';
                } finally {
                    btn.disabled = false;
                    btn.textContent = 'Modifier';
                }
            });
        }
    </script>

</body>

</html>