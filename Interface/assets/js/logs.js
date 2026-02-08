/**
 * JavaScript pour la page de logs
 */

// Variables globales
let logs = [];
let filteredLogs = [];
let currentPage = 1;
const logsPerPage = 50;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    loadLogs();
});

/**
 * Charger les logs
 */
async function loadLogs() {
    try {
        const response = await fetch('api/logs.php');
        const data = await response.json();
        
        if (data.success) {
            logs = data.logs;
            applyFilters();
        } else {
            showToast(data.message || 'Erreur lors du chargement des logs', 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showToast('Erreur de connexion au serveur', 'error');
    }
}

/**
 * Appliquer les filtres
 */
function applyFilters() {
    const level = document.getElementById('filterLevel').value;
    const source = document.getElementById('filterSource').value;
    const startDate = document.getElementById('filterStartDate').value;
    const endDate = document.getElementById('filterEndDate').value;
    const search = document.getElementById('filterSearch').value.toLowerCase();
    
    filteredLogs = logs.filter(log => {
        // Filtre par niveau
        if (level && log.level !== level) return false;
        
        // Filtre par source
        if (source && log.source !== source) return false;
        
        // Filtre par date
        if (startDate && new Date(log.timestamp) < new Date(startDate)) return false;
        if (endDate && new Date(log.timestamp) > new Date(endDate + 'T23:59:59')) return false;
        
        // Filtre par recherche
        if (search) {
            const searchText = `${log.message} ${log.context ? JSON.stringify(log.context) : ''}`.toLowerCase();
            if (!searchText.includes(search)) return false;
        }
        
        return true;
    });
    
    currentPage = 1;
    renderLogs();
    updatePagination();
}

/**
 * Rendre les logs
 */
function renderLogs() {
    const container = document.getElementById('logsContainer');
    
    if (filteredLogs.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-file-alt"></i>
                <p>Aucun log trouvé</p>
            </div>
        `;
        return;
    }
    
    const startIndex = (currentPage - 1) * logsPerPage;
    const endIndex = startIndex + logsPerPage;
    const pageLogs = filteredLogs.slice(startIndex, endIndex);
    
    container.innerHTML = pageLogs.map(log => `
        <div class="log-entry level-${log.level}">
            <div class="log-header">
                <div class="log-timestamp">
                    <i class="fas fa-clock"></i>
                    <span>${formatTimestamp(log.timestamp)}</span>
                </div>
                <div class="log-level">
                    <i class="fas ${getLevelIcon(log.level)}"></i>
                    <span>${formatLevel(log.level)}</span>
                </div>
                <div class="log-source">
                    <i class="fas fa-cube"></i>
                    <span>${escapeHtml(log.source)}</span>
                </div>
            </div>
            <div class="log-message">
                ${escapeHtml(log.message)}
            </div>
            ${log.context ? `
                <div class="log-context">
                    <button class="toggle-context" onclick="toggleContext('${log.id}')">
                        <i class="fas fa-chevron-down"></i>
                        Contexte
                    </button>
                    <div id="context-${log.id}" class="context-content" style="display: none;">
                        <pre>${JSON.stringify(log.context, null, 2)}</pre>
                    </div>
                </div>
            ` : ''}
        </div>
    `).join('');
}

/**
 * Basculer l'affichage du contexte
 */
function toggleContext(id) {
    const context = document.getElementById(`context-${id}`);
    const button = context.previousElementSibling;
    const icon = button.querySelector('i');
    
    if (context.style.display === 'none') {
        context.style.display = 'block';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        context.style.display = 'none';
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}

/**
 * Mettre à jour la pagination
 */
function updatePagination() {
    const totalPages = Math.ceil(filteredLogs.length / logsPerPage);
    const pagination = document.getElementById('pagination');
    
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    let html = `
        <button class="pagination-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(${currentPage - 1})">
            <i class="fas fa-chevron-left"></i>
        </button>
    `;
    
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            html += `
                <button class="pagination-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">
                    ${i}
                </button>
            `;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            html += '<span class="pagination-ellipsis">...</span>';
        }
    }
    
    html += `
        <button class="pagination-btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="changePage(${currentPage + 1})">
            <i class="fas fa-chevron-right"></i>
        </button>
    `;
    
    pagination.innerHTML = html;
}

/**
 * Changer de page
 */
function changePage(page) {
    currentPage = page;
    renderLogs();
    updatePagination();
}

/**
 * Formater le timestamp
 */
function formatTimestamp(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

/**
 * Formater le niveau
 */
function formatLevel(level) {
    const levelMap = {
        'debug': 'Debug',
        'info': 'Info',
        'warning': 'Avertissement',
        'error': 'Erreur',
        'critical': 'Critique'
    };
    return levelMap[level] || level;
}

/**
 * Obtenir l'icône du niveau
 */
function getLevelIcon(level) {
    const iconMap = {
        'debug': 'fa-bug',
        'info': 'fa-info-circle',
        'warning': 'fa-exclamation-triangle',
        'error': 'fa-times-circle',
        'critical': 'fa-skull-crossbones'
    };
    return iconMap[level] || 'fa-circle';
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
 * Exporter les logs
 */
async function exportLogs(format) {
    if (filteredLogs.length === 0) {
        showToast('Aucun log à exporter', 'warning');
        return;
    }
    
    try {
        const response = await fetch(`api/logs.php?export=${format}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                filters: {
                    level: document.getElementById('filterLevel').value,
                    source: document.getElementById('filterSource').value,
                    start_date: document.getElementById('filterStartDate').value,
                    end_date: document.getElementById('filterEndDate').value,
                    search: document.getElementById('filterSearch').value
                }
            })
        });
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `logs_${new Date().toISOString().split('T')[0]}.${format}`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            showToast(`Logs exportés en ${format.toUpperCase()}`, 'success');
        } else {
            showToast('Erreur lors de l\'export', 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showToast('Erreur de connexion au serveur', 'error');
    }
}

/**
 * Vider les logs
 */
async function clearLogs() {
    if (!confirm('Êtes-vous sûr de vouloir vider tous les logs ? Cette action est irréversible.')) {
        return;
    }
    
    try {
        const response = await fetch('api/logs.php', {
            method: 'DELETE'
        });
        
        const data = await response.json();
        
        if (data.success) {
            logs = [];
            filteredLogs = [];
            renderLogs();
            updatePagination();
            showToast('Logs vidés avec succès', 'success');
        } else {
            showToast(data.message || 'Erreur lors du vidage des logs', 'error');
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
    document.getElementById('filterLevel').addEventListener('change', applyFilters);
    document.getElementById('filterSource').addEventListener('change', applyFilters);
    document.getElementById('filterStartDate').addEventListener('change', applyFilters);
    document.getElementById('filterEndDate').addEventListener('change', applyFilters);
    document.getElementById('filterSearch').addEventListener('input', debounce(applyFilters, 300));
    document.getElementById('refreshLogsBtn').addEventListener('click', loadLogs);
    document.getElementById('exportJsonBtn').addEventListener('click', () => exportLogs('json'));
    document.getElementById('exportCsvBtn').addEventListener('click', () => exportLogs('csv'));
    document.getElementById('clearLogsBtn').addEventListener('click', clearLogs);
}

/**
 * Debounce function
 */
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
