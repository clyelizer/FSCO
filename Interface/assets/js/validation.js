/**
 * JavaScript pour la page de validation
 */

// Variables globales
let validationResults = [];
let currentValidation = null;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    loadLastValidation();
});

/**
 * Charger la dernière validation
 */
async function loadLastValidation() {
    try {
        const response = await fetch('api/validation.php?last=true');
        const data = await response.json();
        
        if (data.success && data.validation) {
            displayValidationResults(data.validation);
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

/**
 * Lancer une validation
 */
async function runValidation() {
    const btn = document.getElementById('runValidationBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Validation en cours...';
    
    try {
        const response = await fetch('api/validation.php', {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.success) {
            displayValidationResults(data.validation);
            showToast('Validation terminée avec succès', 'success');
        } else {
            showToast(data.message || 'Erreur lors de la validation', 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showToast('Erreur de connexion au serveur', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-play"></i> Lancer la validation';
    }
}

/**
 * Afficher les résultats de validation
 */
function displayValidationResults(validation) {
    currentValidation = validation;
    
    // Mettre à jour les statistiques
    document.getElementById('totalChecks').textContent = validation.total_checks;
    document.getElementById('passedChecks').textContent = validation.passed_checks;
    document.getElementById('failedChecks').textContent = validation.failed_checks;
    document.getElementById('warnings').textContent = validation.warnings;
    
    // Mettre à jour la barre de progression
    const progress = (validation.passed_checks / validation.total_checks) * 100;
    document.getElementById('progressBar').style.width = `${progress}%`;
    document.getElementById('progressText').textContent = `${progress.toFixed(1)}%`;
    
    // Mettre à jour le statut global
    const statusElement = document.getElementById('globalStatus');
    statusElement.className = `status-badge status-${validation.status}`;
    statusElement.innerHTML = `
        <i class="fas ${getStatusIcon(validation.status)}"></i>
        ${formatStatus(validation.status)}
    `;
    
    // Afficher les résultats détaillés
    renderValidationResults(validation.results);
}

/**
 * Rendre les résultats de validation
 */
function renderValidationResults(results) {
    const container = document.getElementById('validationResults');
    
    if (!results || results.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-check-circle"></i>
                <p>Aucun résultat de validation</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = results.map(result => `
        <div class="validation-item status-${result.status}">
            <div class="validation-header">
                <div class="validation-status">
                    <i class="fas ${getStatusIcon(result.status)}"></i>
                    <span>${formatStatus(result.status)}</span>
                </div>
                <div class="validation-category">
                    <i class="fas fa-folder"></i>
                    <span>${result.category}</span>
                </div>
            </div>
            <div class="validation-body">
                <div class="validation-name">
                    <i class="fas fa-cube"></i>
                    <span>${result.name}</span>
                </div>
                <div class="validation-message">
                    ${escapeHtml(result.message)}
                </div>
            </div>
            ${result.details ? `
                <div class="validation-details">
                    <button class="toggle-details" onclick="toggleDetails('${result.id}')">
                        <i class="fas fa-chevron-down"></i>
                        Détails
                    </button>
                    <div id="details-${result.id}" class="details-content" style="display: none;">
                        <pre>${JSON.stringify(result.details, null, 2)}</pre>
                    </div>
                </div>
            ` : ''}
            ${result.suggestions && result.suggestions.length > 0 ? `
                <div class="validation-suggestions">
                    <h4><i class="fas fa-lightbulb"></i> Suggestions</h4>
                    <ul>
                        ${result.suggestions.map(suggestion => `
                            <li>${escapeHtml(suggestion)}</li>
                        `).join('')}
                    </ul>
                </div>
            ` : ''}
        </div>
    `).join('');
}

/**
 * Basculer l'affichage des détails
 */
function toggleDetails(id) {
    const details = document.getElementById(`details-${id}`);
    const button = details.previousElementSibling;
    const icon = button.querySelector('i');
    
    if (details.style.display === 'none') {
        details.style.display = 'block';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        details.style.display = 'none';
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}

/**
 * Formater le statut
 */
function formatStatus(status) {
    const statusMap = {
        'passed': 'Réussi',
        'failed': 'Échoué',
        'warning': 'Avertissement'
    };
    return statusMap[status] || status;
}

/**
 * Obtenir l'icône du statut
 */
function getStatusIcon(status) {
    const iconMap = {
        'passed': 'fa-check-circle',
        'failed': 'fa-times-circle',
        'warning': 'fa-exclamation-triangle'
    };
    return iconMap[status] || 'fa-circle';
}

/**
 * Échapper le HTML
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Exporter le rapport
 */
async function exportReport(format) {
    if (!currentValidation) {
        showToast('Aucune validation à exporter', 'warning');
        return;
    }
    
    try {
        const response = await fetch(`api/validation.php?export=${format}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                validation_id: currentValidation.id
            })
        });
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `validation_${new Date().toISOString().split('T')[0]}.${format}`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            showToast(`Rapport exporté en ${format.toUpperCase()}`, 'success');
        } else {
            showToast('Erreur lors de l\'export', 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showToast('Erreur de connexion au serveur', 'error');
    }
}

/**
 * Afficher un toast
 */
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

/**
 * Configurer les écouteurs d'événements
 */
function setupEventListeners() {
    document.getElementById('runValidationBtn').addEventListener('click', runValidation);
    document.getElementById('exportJsonBtn').addEventListener('click', () => exportReport('json'));
    document.getElementById('exportHtmlBtn').addEventListener('click', () => exportReport('html'));
}
