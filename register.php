<?php
require_once 'includes/config.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $user_type = $_POST['user_type'] ?? 'tenant';
    
    // Validation
    if (empty($name)) {
        $errors[] = 'Le nom est requis';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Veuillez entrer une adresse email valide';
    }
    
    if (strlen($password) < 8) {
        $errors[] = 'Le mot de passe doit contenir au moins 8 caractères';
    }
    
    if ($password !== $password_confirm) {
        $errors[] = 'Les mots de passe ne correspondent pas';
    }
    
    // Vérification si l'email existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = 'Cette adresse email est déjà utilisée';
    }
    
    // Si pas d'erreurs, on crée l'utilisateur
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = ($user_type === 'landlord') ? 'landlord' : 'tenant';
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $hashed_password, $role]);
            
            $success = true;
            
            // Connexion automatique après inscription
            $user_id = $pdo->lastInsertId();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_role'] = $role;
            
            // Redirection vers le tableau de bord approprié
            header('Location: dashboard.php');
            exit();
            
        } catch (PDOException $e) {
            $errors[] = 'Une erreur est survenue lors de l\'inscription';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">Créer un compte</h2>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form action="register.php" method="POST" novalidate>
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom complet</label>
                                <input type="text" class="form-control" id="name" name="name" required 
                                       value="<?php echo htmlspecialchars($name ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Adresse email</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">Minimum 8 caractères</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password_confirm" class="form-label">Confirmer le mot de passe</label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Je suis :</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="user_type" id="tenant" value="tenant" 
                                           <?php echo (!isset($user_type) || $user_type === 'tenant') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="tenant">
                                        Un locataire
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="user_type" id="landlord" value="landlord"
                                           <?php echo (isset($user_type) && $user_type === 'landlord') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="landlord">
                                        Un propriétaire/bailleur
                                    </label>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">S'inscrire</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            Vous avez déjà un compte ? <a href="login.php">Connectez-vous</a>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 text-center">
                    <p class="text-muted">En vous inscrivant, vous acceptez nos <a href="#">Conditions d'utilisation</a> et notre <a href="#">Politique de confidentialité</a>.</p>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
