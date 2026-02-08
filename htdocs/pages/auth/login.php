<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - FSCo</title>
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

        .auth-step {
            display: none;
        }

        .auth-step.active {
            display: block;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        .back-link {
            display: block;
            margin-top: 1.5rem;
            color: var(--secondary-color);
            font-size: 0.9rem;
            cursor: pointer;
        }

        .error-message {
            color: #dc2626;
            font-size: 0.9rem;
            margin-top: 0.5rem;
            display: none;
        }

        .user-greeting {
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            color: var(--text-color);
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

        <!-- STEP 1: EMAIL -->
        <div id="step-email" class="auth-step active">
            <h2>Connexion / Inscription</h2>
            <p style="color: #666; margin-bottom: 1.5rem;">Entrez votre email pour continuer</p>
            <form id="form-email">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" id="input-email" class="form-input" required placeholder="votre@email.com">
                    <div class="error-message" id="error-email"></div>
                </div>
                <button type="submit" class="btn btn--primary btn-block">Continuer</button>
            </form>
        </div>

        <!-- STEP 2: LOGIN (PASSWORD) -->
        <div id="step-login" class="auth-step">
            <div class="user-greeting">Bonjour <strong id="login-name">User</strong> !</div>
            <form id="form-login">
                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <div class="password-wrapper">
                        <input type="password" id="input-password-login" class="form-input" required>
                        <i class="fas fa-eye toggle-password"
                            onclick="togglePassword('input-password-login', this)"></i>
                    </div>
                    <div class="error-message" id="error-login"></div>
                    <div style="text-align: right; margin-top: 0.5rem;">
                        <a href="reset_password.php" style="font-size: 0.85rem; color: var(--primary-color);">Mot de
                            passe oublié ?</a>
                    </div>
                </div>
                <button type="submit" class="btn btn--primary btn-block">Se connecter</button>
                <div class="back-link" onclick="showStep('step-email')">Changer d'email</div>
            </form>
        </div>

        <!-- STEP 3: REGISTER -->
        <div id="step-register" class="auth-step">
            <h2>Créer un compte</h2>
            <p style="color: #666; margin-bottom: 1.5rem;">Il semble que vous soyez nouveau ici !</p>
            <form id="form-register">
                <div class="form-group">
                    <label class="form-label">Votre Nom Complet</label>
                    <input type="text" id="input-nom" class="form-input" required placeholder="Jean Dupont">
                </div>
                <div class="form-group">
                    <label class="form-label">Choisissez un mot de passe</label>
                    <div class="password-wrapper">
                        <input type="password" id="input-password-register" class="form-input" required minlength="6">
                        <i class="fas fa-eye toggle-password"
                            onclick="togglePassword('input-password-register', this)"></i>
                    </div>
                    <div class="error-message" id="error-register"></div>
                </div>
                <button type="submit" class="btn btn--primary btn-block">S'inscrire</button>
                <div class="back-link" onclick="showStep('step-email')">Changer d'email</div>
            </form>
        </div>
    </div>

    <script>
        let currentEmail = '';

        function showStep(stepId) {
            document.querySelectorAll('.auth-step').forEach(el => el.classList.remove('active'));
            document.getElementById(stepId).classList.add('active');
        }

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

        // STEP 1: CHECK EMAIL
        document.getElementById('form-email').addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('input-email').value.trim();
            const btn = e.target.querySelector('button');
            const errorDiv = document.getElementById('error-email');

            if (!email) return;

            btn.disabled = true;
            btn.textContent = 'Vérification...';
            errorDiv.style.display = 'none';

            try {
                const formData = new FormData();
                formData.append('action', 'check_email');
                formData.append('email', email);

                const response = await fetch('auth_actions.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                currentEmail = email;

                if (data.status === 'exists') {
                    document.getElementById('login-name').textContent = data.nom;
                    showStep('step-login');
                    document.getElementById('input-password-login').focus();
                } else {
                    showStep('step-register');
                    document.getElementById('input-nom').focus();
                }
            } catch (err) {
                errorDiv.textContent = "Une erreur est survenue. Veuillez réessayer.";
                errorDiv.style.display = 'block';
            } finally {
                btn.disabled = false;
                btn.textContent = 'Continuer';
            }
        });

        // STEP 2: LOGIN
        document.getElementById('form-login').addEventListener('submit', async (e) => {
            e.preventDefault();
            const password = document.getElementById('input-password-login').value;
            const btn = e.target.querySelector('button');
            const errorDiv = document.getElementById('error-login');

            btn.disabled = true;
            btn.textContent = 'Connexion...';
            errorDiv.style.display = 'none';

            try {
                const formData = new FormData();
                formData.append('action', 'login');
                formData.append('email', currentEmail);
                formData.append('password', password);

                const response = await fetch('auth_actions.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.status === 'success') {
                    window.location.href = data.redirect;
                } else {
                    errorDiv.textContent = data.message;
                    errorDiv.style.display = 'block';
                }
            } catch (err) {
                errorDiv.textContent = "Erreur de connexion.";
                errorDiv.style.display = 'block';
            } finally {
                btn.disabled = false;
                btn.textContent = 'Se connecter';
            }
        });

        // STEP 3: REGISTER
        document.getElementById('form-register').addEventListener('submit', async (e) => {
            e.preventDefault();
            const nom = document.getElementById('input-nom').value;
            const password = document.getElementById('input-password-register').value;
            const btn = e.target.querySelector('button');
            const errorDiv = document.getElementById('error-register');

            btn.disabled = true;
            btn.textContent = 'Inscription...';
            errorDiv.style.display = 'none';

            try {
                const formData = new FormData();
                formData.append('action', 'register');
                formData.append('email', currentEmail);
                formData.append('nom', nom);
                formData.append('password', password);

                const response = await fetch('auth_actions.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.status === 'success') {
                    window.location.href = data.redirect;
                } else {
                    errorDiv.textContent = data.message;
                    errorDiv.style.display = 'block';
                }
            } catch (err) {
                errorDiv.textContent = "Erreur d'inscription.";
                errorDiv.style.display = 'block';
            } finally {
                btn.disabled = false;
                btn.textContent = 'S\'inscrire';
            }
        });
    </script>

</body>

</html>