<?php
require_once '../includes/config.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Tableau de bord Administrateur';

// Récupérer les statistiques
$stats = [
    'total_users' => $pdo->query("SELECT COUNT(*) as count FROM users")->fetch()['count'],
    'total_properties' => $pdo->query("SELECT COUNT(*) as count FROM properties")->fetch()['count'],
    'total_tenants' => $pdo->query("SELECT COUNT(*) as count FROM tenants")->fetch()['count'],
    'total_payments' => $pdo->query("SELECT COUNT(*) as count FROM payments")->fetch()['count'],
    'recent_payments' => $pdo->query("SELECT p.*, u.name as user_name, pr.title as property_title 
                                     FROM payments p 
                                     JOIN users u ON p.tenant_id = u.id 
                                     JOIN properties pr ON p.property_id = pr.id 
                                     ORDER BY p.payment_date DESC LIMIT 5")->fetchAll(),
    'recent_users' => $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll()
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Administrateur - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white"><?php echo SITE_NAME; ?></h4>
                        <p class="text-white-50">Administration</p>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="bi bi-people me-2"></i>
                                Utilisateurs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="properties.php">
                                <i class="bi bi-house-door me-2"></i>
                                Biens immobiliers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="payments.php">
                                <i class="bi bi-cash-coin me-2"></i>
                                Paiements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="documents.php">
                                <i class="bi bi-file-earmark-text me-2"></i>
                                Documents
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link text-danger" href="../logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Tableau de bord Administrateur</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Exporter</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Imprimer</button>
                        </div>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6 class="card-title">Utilisateurs</h6>
                                <h2 class="mb-0"><?php echo $stats['total_users']; ?></h2>
                                <a href="users.php" class="text-white small">Voir la liste</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6 class="card-title">Biens immobiliers</h6>
                                <h2 class="mb-0"><?php echo $stats['total_properties']; ?></h2>
                                <a href="properties.php" class="text-white small">Voir la liste</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6 class="card-title">Locataires</h6>
                                <h2 class="mb-0"><?php echo $stats['total_tenants']; ?></h2>
                                <a href="tenants.php" class="text-white small">Voir la liste</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <h6 class="card-title">Paiements</h6>
                                <h2 class="mb-0"><?php echo $stats['total_payments']; ?></h2>
                                <a href="payments.php" class="text-dark small">Voir la liste</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Derniers paiements -->
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Derniers paiements</h5>
                            </div>
                            <div class="card-body">
                                <?php if (count($stats['recent_payments']) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Locataire</th>
                                                    <th>Bien</th>
                                                    <th>Montant</th>
                                                    <th>Date</th>
                                                    <th>Statut</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($stats['recent_payments'] as $payment): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($payment['user_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($payment['property_title']); ?></td>
                                                        <td><?php echo number_format($payment['amount'], 2, ',', ' '); ?> €</td>
                                                        <td><?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $payment['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                                                <?php 
                                                                    echo $payment['status'] === 'completed' ? 'Payé' : 
                                                                         ($payment['status'] === 'pending' ? 'En attente' : ucfirst($payment['status'])); 
                                                                ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info mb-0">Aucun paiement récent.</div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer text-end">
                                <a href="payments.php" class="btn btn-sm btn-outline-primary">Voir tous les paiements</a>
                            </div>
                        </div>
                    </div>

                    <!-- Derniers utilisateurs -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Derniers utilisateurs</h5>
                            </div>
                            <div class="card-body">
                                <?php if (count($stats['recent_users']) > 0): ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($stats['recent_users'] as $user): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h6>
                                                    <small class="text-<?php 
                                                        echo $user['role'] === 'admin' ? 'danger' : 
                                                            ($user['role'] === 'landlord' ? 'primary' : 'success');
                                                    ?>">
                                                        <?php 
                                                            echo $user['role'] === 'admin' ? 'Admin' : 
                                                                ($user['role'] === 'landlord' ? 'Propriétaire' : 'Locataire');
                                                        ?>
                                                    </small>
                                                </div>
                                                <p class="mb-1 small text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                                                <small>Inscrit le <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info mb-0">Aucun utilisateur récent.</div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer text-end">
                                <a href="users.php" class="btn btn-sm btn-outline-primary">Voir tous les utilisateurs</a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Initialisation des graphiques
        document.addEventListener('DOMContentLoaded', function() {
            // Graphique des utilisateurs par rôle (exemple)
            var ctx = document.getElementById('usersChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Administrateurs', 'Propriétaires', 'Locataires'],
                        datasets: [{
                            data: [1, 1, 1], // À remplacer par des données réelles
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.7)',
                                'rgba(54, 162, 235, 0.7)',
                                'rgba(75, 192, 192, 0.7)'
                            ],
                            borderColor: [
                                'rgba(255, 99, 132, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(75, 192, 192, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
