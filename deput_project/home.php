<?php
// Connexion à la base de données
$host = 'localhost';
$dbname = 'gestion_loyers';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupération des propriétés disponibles
    $query = "SELECT * FROM properties WHERE status = 'available' ORDER BY created_at DESC";
    $stmt = $pdo->query($query);
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loyer.com - Trouvez votre logement idéal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .property-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 30px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .property-img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        .property-type {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9em;
        }
        .price-tag {
            font-size: 1.4em;
            font-weight: bold;
            color: #2c3e50;
        }
        .features i {
            margin-right: 5px;
            color: #3498db;
        }
        .search-bar {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Loyer.com</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="home.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Propriétés</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Contact</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <a href="login.php" class="btn btn-outline-light me-2">Connexion</a>
                    <a href="register.php" class="btn btn-primary">Inscription</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="bg-primary text-white py-5 mb-5">
        <div class="container text-center">
            <h1 class="display-4">Trouvez la maison de vos rêves</h1>
            <p class="lead">Découvrez notre sélection de propriétés à louer dans toute la France</p>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="container">
        <div class="search-bar">
            <form class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Ville, code postal ou quartier">
                </div>
                <div class="col-md-2">
                    <select class="form-select">
                        <option selected>Type de bien</option>
                        <option>Appartement</option>
                        <option>Maison</option>
                        <option>Studio</option>
                        <option>Chambre</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control" placeholder="Budget max">
                </div>
                <div class="col-md-2">
                    <select class="form-select">
                        <option selected>Pièces</option>
                        <option>1 pièce</option>
                        <option>2 pièces</option>
                        <option>3 pièces</option>
                        <option>4 pièces +</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Properties Grid -->
    <div class="container py-5">
        <h2 class="mb-4">Nos dernières annonces</h2>
        
        <?php if (count($properties) > 0): ?>
            <div class="row">
                <?php foreach ($properties as $property): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card property-card h-100">
                            <div class="position-relative">
                                <img src="https://via.placeholder.com/400x300?text=<?= urlencode($property['title']) ?>" 
                                     class="card-img-top property-img" 
                                     alt="<?= htmlspecialchars($property['title']) ?>">
                                <span class="property-type"><?= ucfirst($property['type']) ?></span>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($property['title']) ?></h5>
                                <p class="text-muted">
                                    <i class="fas fa-map-marker-alt"></i> 
                                    <?= htmlspecialchars($property['address']) ?>, <?= htmlspecialchars($property['postal_code']) ?> <?= htmlspecialchars($property['city']) ?>
                                </p>
                                <div class="features mb-3">
                                    <?php if ($property['surface_area']): ?>
                                        <span class="me-3"><i class="fas fa-ruler-combined"></i> <?= $property['surface_area'] ?> m²</span>
                                    <?php endif; ?>
                                    <?php if ($property['bedrooms']): ?>
                                        <span class="me-3"><i class="fas fa-bed"></i> <?= $property['bedrooms'] ?> ch.</span>
                                    <?php endif; ?>
                                    <?php if ($property['bathrooms']): ?>
                                        <span><i class="fas fa-bath"></i> <?= $property['bathrooms'] ?> sdb</span>
                                    <?php endif; ?>
                                </div>
                                <p class="card-text flex-grow-1">
                                    <?= nl2br(htmlspecialchars(mb_substr($property['description'], 0, 100) . '...')) ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <div class="price-tag">
                                        <?= number_format($property['rental_amount'], 0, ',', ' ') ?> €/mois
                                    </div>
                                    <a href="property.php?id=<?= $property['id'] ?>" class="btn btn-outline-primary">Voir plus</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                Aucune propriété disponible pour le moment. Revenez plus tard !
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Loyer.com</h5>
                    <p>Votre partenaire de confiance pour trouver le logement parfait.</p>
                </div>
                <div class="col-md-4">
                    <h5>Liens rapides</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">Accueil</a></li>
                        <li><a href="#" class="text-white">Propriétés</a></li>
                        <li><a href="#" class="text-white">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact</h5>
                    <p><i class="fas fa-envelope me-2"></i> contact@loyer.com</p>
                    <p><i class="fas fa-phone me-2"></i> +33 1 23 45 67 89</p>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; <?= date('Y') ?> Loyer.com - Tous droits réservés</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>