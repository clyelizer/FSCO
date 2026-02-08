// JavaScript principal pour la plateforme d'examens

document.addEventListener('DOMContentLoaded', function() {
    // Initialisation
    initializeApp();
});

function initializeApp() {
    // Gestion des messages d'alerte
    setupAlerts();

    // Gestion des formulaires
    setupForms();

    // Gestion des timers
    setupTimers();

    // Gestion des confirmations
    setupConfirmations();

    // Gestion responsive
    setupResponsive();
}

function setupAlerts() {
    // Auto-disparition des alertes après 5 secondes
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
}

function setupForms() {
    // Validation côté client pour les formulaires
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });

    // Gestion des champs de mot de passe
    setupPasswordFields();
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'Ce champ est requis');
            isValid = false;
        } else {
            clearFieldError(field);
        }
    });

    // Validation spécifique pour les emails
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        if (field.value && !isValidEmail(field.value)) {
            showFieldError(field, 'Format d\'email invalide');
            isValid = false;
        }
    });

    // Validation des mots de passe
    const passwordFields = form.querySelectorAll('input[type="password"]');
    if (passwordFields.length >= 2) {
        const password = passwordFields[0].value;
        const confirmPassword = passwordFields[1].value;

        if (password && confirmPassword && password !== confirmPassword) {
            showFieldError(passwordFields[1], 'Les mots de passe ne correspondent pas');
            isValid = false;
        }
    }

    return isValid;
}

function showFieldError(field, message) {
    clearFieldError(field);

    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;

    field.parentNode.appendChild(errorDiv);
    field.classList.add('field-error-input');
}

function clearFieldError(field) {
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    field.classList.remove('field-error-input');
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function setupPasswordFields() {
    // Affichage/masquage des mots de passe
    const passwordContainers = document.querySelectorAll('.password-container');

    passwordContainers.forEach(container => {
        const input = container.querySelector('input[type="password"]');
        const toggle = container.querySelector('.password-toggle');

        if (input && toggle) {
            toggle.addEventListener('click', function() {
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                this.textContent = type === 'password' ? '👁️' : '🙈';
            });
        }
    });
}

function setupTimers() {
    // Gestion des timers d'examen
    const timers = document.querySelectorAll('.exam-timer');

    timers.forEach(timer => {
        const endTime = new Date(timer.dataset.endTime).getTime();

        function updateTimer() {
            const now = new Date().getTime();
            const distance = endTime - now;

            if (distance <= 0) {
                timer.innerHTML = '<span class="timer-expired">Temps écoulé !</span>';
                // Auto-submit du formulaire si nécessaire
                const form = timer.closest('form');
                if (form) {
                    form.submit();
                }
                return;
            }

            const hours = Math.floor(distance / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            timer.innerHTML = `
                <span class="timer-display">
                    ${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}
                </span>
            `;

            // Alerte quand il reste 5 minutes
            if (distance <= 300000 && distance > 299000) {
                showAlert('Attention : il ne reste que 5 minutes !', 'warning');
            }
        }

        updateTimer();
        setInterval(updateTimer, 1000);
    });
}

function setupConfirmations() {
    // Gestion des confirmations pour les actions dangereuses
    const confirmLinks = document.querySelectorAll('a[data-confirm], button[data-confirm]');

    confirmLinks.forEach(element => {
        element.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Êtes-vous sûr de vouloir effectuer cette action ?';

            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });
}

function setupResponsive() {
    // Gestion du menu mobile
    const nav = document.querySelector('.main-nav');
    if (nav) {
        const menuToggle = document.createElement('button');
        menuToggle.className = 'menu-toggle';
        menuToggle.innerHTML = '☰';
        menuToggle.style.display = 'none';

        nav.parentNode.insertBefore(menuToggle, nav);

        menuToggle.addEventListener('click', function() {
            nav.classList.toggle('mobile-menu-open');
        });

        // Afficher/masquer le bouton selon la taille d'écran
        function checkScreenSize() {
            if (window.innerWidth <= 768) {
                menuToggle.style.display = 'block';
                nav.classList.add('mobile-menu');
            } else {
                menuToggle.style.display = 'none';
                nav.classList.remove('mobile-menu', 'mobile-menu-open');
            }
        }

        window.addEventListener('resize', checkScreenSize);
        checkScreenSize();
    }
}

function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = `
        <span class="alert-message">${message}</span>
        <button class="alert-close" onclick="this.parentNode.remove()">×</button>
    `;

    const container = document.querySelector('.container') || document.body;
    container.insertBefore(alertDiv, container.firstChild);

    // Auto-disparition
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Fonctions utilitaires
function ajaxRequest(url, method = 'GET', data = null) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();

        xhr.open(method, url, true);
        xhr.setRequestHeader('Content-Type', 'application/json');

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    resolve(response);
                } catch (e) {
                    resolve(xhr.responseText);
                }
            } else {
                reject(new Error(`HTTP ${xhr.status}: ${xhr.statusText}`));
            }
        };

        xhr.onerror = function() {
            reject(new Error('Erreur réseau'));
        };

        if (data) {
            xhr.send(JSON.stringify(data));
        } else {
            xhr.send();
        }
    });
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Auto-save pour les formulaires d'examen
function setupAutoSave(formSelector, interval = 30000) {
    const form = document.querySelector(formSelector);
    if (!form) return;

    function autoSave() {
        const formData = new FormData(form);
        const data = {};

        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }

        // Sauvegarde locale en attendant la sauvegarde serveur
        localStorage.setItem('exam_autosave', JSON.stringify({
            data: data,
            timestamp: Date.now()
        }));

        // TODO: Implémenter la sauvegarde serveur
        console.log('Auto-sauvegarde effectuée', data);
    }

    // Sauvegarde automatique
    setInterval(autoSave, interval);

    // Sauvegarde avant fermeture de la page
    window.addEventListener('beforeunload', autoSave);

    // Restaurer les données sauvegardées
    const saved = localStorage.getItem('exam_autosave');
    if (saved) {
        const { data, timestamp } = JSON.parse(saved);

        // Vérifier si la sauvegarde n'est pas trop ancienne (24h)
        if (Date.now() - timestamp < 24 * 60 * 60 * 1000) {
            for (let key in data) {
                const input = form.querySelector(`[name="${key}"]`);
                if (input) {
                    input.value = data[key];
                }
            }

            showAlert('Données restaurées depuis la dernière sauvegarde automatique', 'info');
        }

        // Nettoyer le localStorage
        localStorage.removeItem('exam_autosave');
    }
}

// Gestion du drag & drop pour les uploads
function setupDragDrop(dropZoneSelector, fileInputSelector) {
    const dropZone = document.querySelector(dropZoneSelector);
    const fileInput = document.querySelector(fileInputSelector);

    if (!dropZone || !fileInput) return;

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    function highlight() {
        dropZone.classList.add('drag-over');
    }

    function unhighlight() {
        dropZone.classList.remove('drag-over');
    }

    dropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        fileInput.files = files;
        // Déclencher l'événement change
        fileInput.dispatchEvent(new Event('change'));
    }
}

// Export des fonctions globales
window.ExamPlatform = {
    showAlert,
    ajaxRequest,
    setupAutoSave,
    setupDragDrop
};