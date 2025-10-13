// Fonction pour afficher/masquer les mots de passe
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de l'affichage des mots de passe
    const togglePassword = document.querySelectorAll('.toggle-password');
    
    togglePassword.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    });

    // Initialisation des tooltips Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialisation des popovers Bootstrap
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Gestion des messages flash
    const alertList = document.querySelectorAll('.alert');
    alertList.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

// Fonction pour confirmer les actions importantes
function confirmAction(message = 'Êtes-vous sûr de vouloir effectuer cette action ?') {
    return confirm(message);
}

// Fonction pour formater les dates
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('fr-FR', options);
}

// Fonction pour formater les montants
function formatAmount(amount) {
    return new Intl.NumberFormat('fr-FR', { 
        style: 'currency', 
        currency: 'EUR',
        minimumFractionDigits: 2
    }).format(amount);
}

// Gestion des onglets du tableau de bord
function openTab(evt, tabName) {
    const tabContents = document.getElementsByClassName('tab-content');
    for (let i = 0; i < tabContents.length; i++) {
        tabContents[i].style.display = 'none';
    }

    const tabButtons = document.getElementsByClassName('tab-button');
    for (let i = 0; i < tabButtons.length; i++) {
        tabButtons[i].className = tabButtons[i].className.replace(' active', '');
    }

    document.getElementById(tabName).style.display = 'block';
    if (evt) {
        evt.currentTarget.className += ' active';
    } else if (tabButtons.length > 0) {
        tabButtons[0].className += ' active';
    }
}

// Initialisation des graphiques
function initCharts() {
    // Graphique des paiements (exemple)
    const ctx = document.getElementById('paymentsChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
                datasets: [{
                    label: 'Paiements reçus',
                    data: [1200, 1900, 1500, 2000, 1800, 2100],
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

// Initialisation au chargement du DOM
document.addEventListener('DOMContentLoaded', function() {
    // Afficher le premier onglet par défaut
    openTab(null, 'overview');
    
    // Initialiser les graphiques
    initCharts();
});
