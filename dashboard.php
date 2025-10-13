<?php
// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT name, email, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Récupérer les statistiques selon le rôle
if ($user_role === 'landlord') {
    // Statistiques pour les propriétaires
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT p.id) as total_properties,
            COUNT(DISTINCT t.id) as total_tenants,
            COALESCE(SUM(CASE WHEN r.status = 'paid' THEN r.amount ELSE 0 END), 0) as total_income,
            COUNT(DISTINCT CASE WHEN r.status = 'pending' THEN r.id END) as pending_payments
        FROM properties p
        LEFT JOIN tenants t ON p.id = t.property_id
        LEFT JOIN rentals r ON t.id = r.tenant_id
        WHERE p.owner_id = ?
    ");
} else {
    // Statistiques pour les locataires
    $stmt = $pdo->prepare("
        SELECT 
            p.address,
            p.city,
            p.rental_amount,
            py.payment_date as due_date,
            py.status as rent_status,
            py.amount as rent_amount
        FROM tenants t
        JOIN properties p ON t.property_id = p.id
        LEFT JOIN payments py ON t.id = py.tenant_id AND py.status = 'pending'
        WHERE t.user_id = ?
        ORDER BY py.payment_date ASC
        LIMIT 1
    ");
}

$stmt->execute([$user_id]);
$stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                        <h5 class="mt-2 mb-0"><?php echo htmlspecialchars($user['name']); ?></h5>
                        <small class="text-muted"><?php echo ucfirst($user_role); ?></small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" onclick="openTab(event, 'overview')">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Vue d'ensemble
                            </a>
                        </li>
                        <?php if ($user_role === 'landlord'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="#" onclick="openTab(event, 'properties')">
                                    <i class="bi bi-house-door me-2"></i>
                                    Mes biens
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" onclick="openTab(event, 'tenants')">
                                    <i class="bi bi-people me-2"></i>
                                    Locataires
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="#" onclick="openTab(event, 'rental')">
                                    <i class="bi bi-house me-2"></i>
                                    Mon logement
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="openTab(event, 'payments')">
                                <i class="bi bi-cash-coin me-2"></i>
                                Paiements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="openTab(event, 'documents')">
                                <i class="bi bi-file-earmark-text me-2"></i>
                                Documents
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link text-danger" href="logout.php">
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
                    <h1 class="h2">Tableau de bord</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-download me-1"></i> Exporter
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-printer me-1"></i> Imprimer
                            </button>
                        </div>
                        <?php if ($user_role === 'landlord'): ?>
                            <a href="add-property.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i> Ajouter un bien
                            </a>
                        <?php else: ?>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                <i class="bi bi-credit-card me-1"></i> Effectuer un paiement
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Vue d'ensemble -->
                <div id="overview" class="tab-content">
                    <?php if ($user_role === 'landlord'): ?>
                        <!-- Tableau de bord propriétaire -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Biens immobiliers</h6>
                                        <h2 class="mb-0"><?php echo $stats['total_properties'] ?? 0; ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Locataires</h6>
                                        <h2 class="mb-0"><?php echo $stats['total_tenants'] ?? 0; ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Revenus mensuels</h6>
                                        <h2 class="mb-0"><?php echo number_format($stats['total_income'] ?? 0, 2, ',', ' '); ?> €</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-dark">
                                    <div class="card-body">
                                        <h6 class="card-title">Paiements en attente</h6>
                                        <h2 class="mb-0"><?php echo $stats['pending_payments'] ?? 0; ?></h2>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Revenus mensuels</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="paymentsChart" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Paiements récents</h5>
                                    </div>
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">Appartement T2</h6>
                                                <small class="text-success">Payé</small>
                                            </div>
                                            <p class="mb-1">750 € - 05/11/2023</p>
                                            <small class="text-muted">Dupont Martin</small>
                                        </div>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">Maison F3</h6>
                                                <small class="text-warning">En attente</small>
                                            </div>
                                            <p class="mb-1">950 € - 03/11/2023</p>
                                            <small class="text-muted">Durand Sophie</small>
                                        </div>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">Studio</h6>
                                                <small class="text-success">Payé</small>
                                            </div>
                                            <p class="mb-1">600 € - 01/11/2023</p>
                                            <small class="text-muted">Martin Pierre</small>
                                        </div>
                                    </div>
                                    <div class="card-footer text-center">
                                        <a href="payments.php" class="btn btn-sm btn-outline-primary">Voir tout</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Tableau de bord locataire -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Mon logement</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if ($stats): ?>
                                            <h4><?php echo htmlspecialchars($stats['address'] . ', ' . $stats['city']); ?></h4>
                                            <p class="text-muted">Loyer: <?php echo number_format($stats['rental_amount'] ?? 0, 2, ',', ' '); ?> €/mois</p>
                                            <div class="alert alert-info">
                                                <i class="bi bi-info-circle me-2"></i>
                                                Votre prochain loyer de <?php echo number_format($stats['rent_amount'] ?? 0, 2, ',', ' '); ?> € est dû le 
                                                <?php echo date('d/m/Y', strtotime($stats['due_date'])); ?>
                                            </div>
                                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                                <i class="bi bi-credit-card me-1"></i> Payer maintenant
                                            </button>
                                        <?php else: ?>
                                            <div class="text-center py-4">
                                                <i class="bi bi-house-x fs-1 text-muted mb-3"></i>
                                                <p class="text-muted">Aucun logement enregistré</p>
                                                <a href="#" class="btn btn-primary">Ajouter un logement</a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Derniers paiements</h5>
                                    </div>
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">Loyer Novembre 2023</h6>
                                                <small class="text-success">Payé</small>
                                            </div>
                                            <p class="mb-1">750 € - 05/11/2023</p>
                                            <small class="text-muted">Référence: LOC20231105</small>
                                        </div>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">Loyer Octobre 2023</h6>
                                                <small class="text-success">Payé</small>
                                            </div>
                                            <p class="mb-1">750 € - 05/10/2023</p>
                                            <small class="text-muted">Référence: LOC20231005</small>
                                        </div>
                                    </div>
                                    <div class="card-footer text-center">
                                        <a href="payments.php" class="btn btn-sm btn-outline-primary">Voir l'historique</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Documents importants</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Nom du document</th>
                                                        <th>Date d'ajout</th>
                                                        <th>Taille</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>Contrat de location</td>
                                                        <td>15/06/2023</td>
                                                        <td>1.2 Mo</td>
                                                        <td>
                                                            <a href="#" class="btn btn-sm btn-outline-primary">
                                                                <i class="bi bi-download"></i> Télécharger
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>État des lieux d'entrée</td>
                                                        <td>20/06/2023</td>
                                                        <td>2.5 Mo</td>
                                                        <td>
                                                            <a href="#" class="btn btn-sm btn-outline-primary">
                                                                <i class="bi bi-download"></i> Télécharger
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Règlement intérieur</td>
                                                        <td>10/06/2023</td>
                                                        <td>850 Ko</td>
                                                        <td>
                                                            <a href="#" class="btn btn-sm btn-outline-primary">
                                                                <i class="bi bi-download"></i> Télécharger
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Autres onglets -->
                <div id="properties" class="tab-content" style="display: none;">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Mes biens immobiliers</h5>
                            <a href="add-property.php" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i> Ajouter un bien
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Adresse</th>
                                            <th>Ville</th>
                                            <th>Type</th>
                                            <th>Loyer</th>
                                            <th>Locataire</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>15 Rue de la Paix</td>
                                            <td>Paris</td>
                                            <td>Appartement T2</td>
                                            <td>850 €</td>
                                            <td>Dupont Martin</td>
                                            <td><span class="badge bg-success">Loué</span></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="property-details.php?id=1" class="btn btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="edit-property.php?id=1" class="btn btn-outline-secondary">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button class="btn btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>8 Avenue des Champs-Élysées</td>
                                            <td>Lyon</td>
                                            <td>Maison F3</td>
                                            <td>1 200 €</td>
                                            <td>Durand Sophie</td>
                                            <td><span class="badge bg-success">Loué</span></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="property-details.php?id=2" class="btn btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="edit-property.php?id=2" class="btn btn-outline-secondary">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button class="btn btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>22 Rue de la République</td>
                                            <td>Marseille</td>
                                            <td>Studio</td>
                                            <td>600 €</td>
                                            <td>Martin Pierre</td>
                                            <td><span class="badge bg-success">Loué</span></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="property-details.php?id=3" class="btn btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="edit-property.php?id=3" class="btn btn-outline-secondary">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button class="btn btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="payments" class="tab-content" style="display: none;">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Gestion des paiements</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($user_role === 'landlord'): ?>
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <div class="input-group mb-3">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text" class="form-control" placeholder="Rechercher un locataire...">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-select">
                                            <option selected>Tous les statuts</option>
                                            <option>Payé</option>
                                            <option>En attente</option>
                                            <option>En retard</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <span class="input-group-text">Période</span>
                                            <input type="month" class="form-control" value="<?php echo date('Y-m'); ?>">
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Référence</th>
                                            <?php if ($user_role === 'landlord'): ?>
                                                <th>Locataire</th>
                                                <th>Bien</th>
                                            <?php endif; ?>
                                            <th>Période</th>
                                            <th>Montant</th>
                                            <th>Date de paiement</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>PAY20231105</td>
                                            <?php if ($user_role === 'landlord'): ?>
                                                <td>Dupont Martin</td>
                                                <td>15 Rue de la Paix, Paris</td>
                                            <?php endif; ?>
                                            <td>Novembre 2023</td>
                                            <td>850,00 €</td>
                                            <td>05/11/2023</td>
                                            <td><span class="badge bg-success">Payé</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-receipt"></i> Reçu
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>PAY20231005</td>
                                            <?php if ($user_role === 'landlord'): ?>
                                                <td>Dupont Martin</td>
                                                <td>15 Rue de la Paix, Paris</td>
                                            <?php endif; ?>
                                            <td>Octobre 2023</td>
                                            <td>850,00 €</td>
                                            <td>05/10/2023</td>
                                            <td><span class="badge bg-success">Payé</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-receipt"></i> Reçu
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>PAY20230905</td>
                                            <?php if ($user_role === 'landlord'): ?>
                                                <td>Dupont Martin</td>
                                                <td>15 Rue de la Paix, Paris</td>
                                            <?php endif; ?>
                                            <td>Septembre 2023</td>
                                            <td>850,00 €</td>
                                            <td>05/09/2023</td>
                                            <td><span class="badge bg-success">Payé</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-receipt"></i> Reçu
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <nav aria-label="Pagination" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Précédent</a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">Suivant</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>

                <div id="documents" class="tab-content" style="display: none;">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Mes documents</h5>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                                <i class="bi bi-upload me-1"></i> Téléverser
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <i class="bi bi-file-earmark-pdf fs-1 text-danger"></i>
                                            <h5 class="card-title mt-2">Contrat de location</h5>
                                            <p class="card-text text-muted">Ajouté le 15/06/2023</p>
                                            <p class="card-text">1.2 Mo</p>
                                            <div class="btn-group">
                                                <a href="#" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-download"></i> Télécharger
                                                </a>
                                                <button class="btn btn-outline-danger btn-sm">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <i class="bi bi-file-earmark-image fs-1 text-primary"></i>
                                            <h5 class="card-title mt-2">État des lieux d'entrée</h5>
                                            <p class="card-text text-muted">Ajouté le 20/06/2023</p>
                                            <p class="card-text">2.5 Mo</p>
                                            <div class="btn-group">
                                                <a href="#" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-download"></i> Télécharger
                                                </a>
                                                <button class="btn btn-outline-danger btn-sm">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <i class="bi bi-file-earmark-text fs-1 text-secondary"></i>
                                            <h5 class="card-title mt-2">Règlement intérieur</h5>
                                            <p class="card-text text-muted">Ajouté le 10/06/2023</p>
                                            <p class="card-text">850 Ko</p>
                                            <div class="btn-group">
                                                <a href="#" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-download"></i> Télécharger
                                                </a>
                                                <button class="btn btn-outline-danger btn-sm">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenu des autres onglets -->
                <div id="tenants" class="tab-content" style="display: none;">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Gestion des locataires</h5>
                        </div>
                        <div class="card-body">
                            <p>Contenu de la gestion des locataires à venir...</p>
                        </div>
                    </div>
                </div>

                <div id="rental" class="tab-content" style="display: none;">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Mon logement</h5>
                        </div>
                        <div class="card-body">
                            <p>Détails de votre logement à venir...</p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Paiement -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Effectuer un paiement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="paymentForm">
                        <div class="mb-3">
                            <label for="paymentAmount" class="form-label">Montant</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="paymentAmount" value="850" required>
                                <span class="input-group-text">€</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="paymentDate" class="form-label">Date du paiement</label>
                            <input type="date" class="form-control" id="paymentDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="paymentMethod" class="form-label">Moyen de paiement</label>
                            <select class="form-select" id="paymentMethod" required>
                                <option value="">Sélectionner un moyen de paiement</option>
                                <option value="card">Carte bancaire</option>
                                <option value="transfer">Virement bancaire</option>
                                <option value="check">Chèque</option>
                                <option value="cash">Espèces</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="paymentReference" class="form-label">Référence</label>
                            <input type="text" class="form-control" id="paymentReference" placeholder="Numéro de transaction ou de chèque">
                        </div>
                        <div class="mb-3">
                            <label for="paymentNotes" class="form-label">Notes</label>
                            <textarea class="form-control" id="paymentNotes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="confirmPayment">Confirmer le paiement</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Téléversement de document -->
    <div class="modal fade" id="uploadDocumentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Téléverser un document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="documentUploadForm">
                        <div class="mb-3">
                            <label for="documentType" class="form-label">Type de document</label>
                            <select class="form-select" id="documentType" required>
                                <option value="">Sélectionner un type</option>
                                <option value="contract">Contrat de location</option>
                                <option value="inventory">État des lieux</option>
                                <option value="rules">Règlement intérieur</option>
                                <option value="other">Autre</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="documentTitle" class="form-label">Titre du document</label>
                            <input type="text" class="form-control" id="documentTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="documentFile" class="form-label">Fichier</label>
                            <input class="form-control" type="file" id="documentFile" required>
                            <div class="form-text">Formats acceptés : PDF, JPG, PNG (max. 10 Mo)</div>
                        </div>
                        <div class="mb-3">
                            <label for="documentNotes" class="form-label">Notes</label>
                            <textarea class="form-control" id="documentNotes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="uploadDocument">Téléverser</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Initialisation des tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Gestion du formulaire de paiement
        document.getElementById('confirmPayment').addEventListener('click', function() {
            // Ici, vous ajouterez la logique de traitement du paiement
            alert('Paiement effectué avec succès !');
            var paymentModal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
            paymentModal.hide();
        });

        // Gestion du téléversement de document
        document.getElementById('uploadDocument').addEventListener('click', function() {
            // Ici, vous ajouterez la logique de téléversement
            alert('Document téléversé avec succès !');
            var uploadModal = bootstrap.Modal.getInstance(document.getElementById('uploadDocumentModal'));
            uploadModal.hide();
        });

        // Initialisation des graphiques
        document.addEventListener('DOMContentLoaded', function() {
            // Graphique des paiements (exemple)
            var ctx = document.getElementById('paymentsChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
                        datasets: [{
                            label: 'Revenus mensuels (€)',
                            data: [2500, 2500, 2500, 2500, 2500, 2500, 2500, 2500, 2500, 2500, 2500, 2500],
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
        });
    </script>
</body>
</html>
