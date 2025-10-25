<?php
// Simulation de données de publication (à remplacer par une requête à la base de données)
$publication = [
    'id' => 1,
    'titre' => 'Appartement moderne en centre-ville',
    'description' => 'Magnifique appartement de 75m² situé en plein cœur de la ville. Comprend 2 chambres, une cuisine équipée, un grand salon et une salle de bain moderne. Proche de tous les commerces et transports en commun.',
    'prix' => 850,
    'surface' => 75,
    'chambres' => 2,
    'sdb' => 1,
    'ville' => 'Paris',
    'date_publication' => '20/10/2023',
    'likes' => 24,
    'dislikes' => 3,
    'commentaires' => 5,
    'images' => [
        'https://camerounmaison.com/detail_maison/1207',
        'https://camerounmaison.com/detail_maison/1207',
        'https://camerounmaison.com/detail_maison/1207',
        'https://camerounmaison.com/detail_maison/1207',
    ]
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publication - <?= htmlspecialchars($publication['titre']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .publication-container {
            max-width: 1000px;
            margin: 30px auto;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        .main-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.3s;
        }
        .main-image:hover {
            transform: scale(1.02);
        }
        .thumbnail-container {
            display: flex;
            flex-direction: column;
            height: 400px;
            gap: 10px;
        }
        .thumbnail {
            width: 100%;
            height: calc(50% - 5px);
            object-fit: cover;
            cursor: pointer;
            border-radius: 5px;
            transition: transform 0.3s, opacity 0.3s;
        }
        .thumbnail:hover {
            transform: scale(1.05);
            opacity: 0.9;
        }
        .more-images {
            position: relative;
            width: 100%;
            height: calc(50% - 5px);
            background-color: #f8f9fa;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .more-images:hover {
            background-color: #e9ecef;
        }
        .publication-info {
            padding: 20px;
        }
        .publication-title {
            font-size: 1.8rem;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        .publication-meta {
            color: #6c757d;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
        .publication-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }
        .publication-description {
            line-height: 1.6;
            color: #495057;
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .interaction-buttons {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-top: 1px solid #e9ecef;
            border-bottom: 1px solid #e9ecef;
            margin: 20px 0;
        }
        .btn-interaction {
            display: flex;
            align-items: center;
            gap: 5px;
            background: none;
            border: none;
            color: #6c757d;
            padding: 5px 15px;
            border-radius: 20px;
            transition: all 0.3s;
        }
        .btn-interaction:hover {
            background-color: #f1f3f5;
            color: #2c3e50;
        }
        .btn-interaction.liked {
            color: #dc3545;
        }
        .btn-interaction.disliked {
            color: #0d6efd;
        }
        .comment-section {
            margin-top: 20px;
        }
        .comment-form {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="publication-container">
            <!-- En-tête de la publication -->
            <div class="row g-0">
                <!-- Image principale -->
                <div class="col-md-8">
                    <img src="<?= htmlspecialchars($publication['images'][0]) ?>" alt="Image principale" class="main-image">
                </div>
                
                <!-- Miniatures des images supplémentaires -->
                <div class="col-md-4 p-2">
                    <div class="thumbnail-container">
                        <?php for ($i = 1; $i < min(3, count($publication['images'])); $i++): ?>
                            <img src="<?= htmlspecialchars($publication['images'][$i]) ?>" alt="Image <?= $i + 1 ?>" class="thumbnail">
                        <?php endfor; ?>
                        
                        <?php if (count($publication['images']) > 3): ?>
                            <div class="more-images" data-bs-toggle="modal" data-bs-target="#galleryModal">
                                <i class="fas fa-images fa-2x mb-2"></i>
                                <div>+<?= count($publication['images']) - 3 ?> photos</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Informations de la publication -->
            <div class="publication-info">
                <h1 class="publication-title"><?= htmlspecialchars($publication['titre']) ?></h1>
                
                <div class="publication-meta">
                    <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($publication['ville']) ?></span> • 
                    <span><i class="fas fa-calendar-alt"></i> Publié le <?= $publication['date_publication'] ?></span>
                </div>
                
                <div class="publication-price">
                    <?= number_format($publication['prix'], 0, ',', ' ') ?> FCFA / mois
                </div>
                
                <div class="publication-meta">
                    <span><i class="fas fa-ruler-combined"></i> <?= $publication['surface'] ?> m²</span> • 
                    <span><i class="fas fa-bed"></i> <?= $publication['chambres'] ?> chambres</span> • 
                    <span><i class="fas fa-bath"></i> <?= $publication['sdb'] ?> salle de bain</span>
                </div>
                
                <div class="publication-description">
                    <?= nl2br(htmlspecialchars($publication['description'])) ?>
                </div>
                
                <!-- Boutons d'interaction -->
                <div class="interaction-buttons">
                    <button class="btn-interaction like-btn" data-publication-id="<?= $publication['id'] ?>">
                        <i class="far fa-thumbs-up"></i>
                        <span class="like-count"><?= $publication['likes'] ?></span>
                    </button>
                    
                    <button class="btn-interaction dislike-btn" data-publication-id="<?= $publication['id'] ?>">
                        <i class="far fa-thumbs-down"></i>
                        <span class="dislike-count"><?= $publication['dislikes'] ?></span>
                    </button>
                    
                    <button class="btn-interaction ms-auto">
                        <i class="far fa-comment"></i>
                        <span><?= $publication['commentaires'] ?> commentaires</span>
                    </button>
                    
                    <button class="btn btn-primary">
                        <i class="fas fa-phone-alt me-2"></i> Contacter
                    </button>
                </div>
                
                <!-- Section commentaires -->
                <div class="comment-section">
                    <h5>Commentaires (<?= $publication['commentaires'] ?>)</h5>
                    
                    <form class="comment-form">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" placeholder="Ajouter un commentaire...">
                            <button class="btn btn-outline-primary" type="submit">Publier</button>
                        </div>
                    </form>
                    
                    <!-- Liste des commentaires (à implémenter dynamiquement) -->
                    <div class="list-group">
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Jean Dupont</h6>
                                <small class="text-muted">Il y a 2 jours</small>
                            </div>
                            <p class="mb-1">Très bel appartement, je suis intéressé !</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal pour la galerie d'images -->
    <div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Galerie photos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="galleryCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php foreach ($publication['images'] as $index => $image): ?>
                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                    <img src="<?= htmlspecialchars($image) ?>" class="d-block w-100" alt="Image <?= $index + 1 ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Précédent</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Suivant</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gestion des likes/dislikes (à implémenter avec AJAX)
        document.querySelectorAll('.like-btn, .dislike-btn').forEach(button => {
            button.addEventListener('click', function() {
                const isLike = this.classList.contains('like-btn');
                const publicationId = this.dataset.publicationId;
                
                // Animation visuelle
                if (isLike) {
                    this.classList.toggle('liked');
                    const icon = this.querySelector('i');
                    icon.classList.toggle('far');
                    icon.classList.toggle('fas');
                    
                    // Mise à jour du compteur
                    const countElement = this.querySelector('.like-count');
                    countElement.textContent = parseInt(countElement.textContent) + (this.classList.contains('liked') ? 1 : -1);
                } else {
                    this.classList.toggle('disliked');
                    const icon = this.querySelector('i');
                    icon.classList.toggle('far');
                    icon.classList.toggle('fas');
                    
                    // Mise à jour du compteur
                    const countElement = this.querySelector('.dislike-count');
                    countElement.textContent = parseInt(countElement.textContent) + (this.classList.contains('disliked') ? 1 : -1);
                }
                
                // Ici, vous ajouterez l'appel AJAX pour enregistrer le like/dislike en base de données
                console.log(`${isLike ? 'Like' : 'Dislike'} pour la publication ${publicationId}`);
            });
        });
        
        // Permettre de cliquer sur les miniatures pour les afficher en grand
        document.querySelectorAll('.thumbnail').forEach((thumbnail, index) => {
            thumbnail.addEventListener('click', function() {
                const mainImage = document.querySelector('.main-image');
                const currentSrc = mainImage.src;
                mainImage.src = this.src;
                this.src = currentSrc;
            });
        });
    </script>
</body>
</html>