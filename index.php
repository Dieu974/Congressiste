<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// controllers requiring authentification
$protectedControllers = ['congressiste', 'hotel', 'organisme'];

// logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['user']);
    header('Location: index.php');
    exit;
}

// handle login POST
$loginError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    $redirect = $_POST['redirect'] ?? 'index.php';
    if ($user === 'admin' && $pass === 'admin') {
        $_SESSION['user'] = 'admin';
        header('Location: $redirect' === '' ? $redirect : $redirect);
        exit;
    } else {
        $loginError = 'Identifiants incorrects (admin / admin)';
    }
}

$controller = $_GET['c'] ?? 'home';
$action = $_GET['a'] ?? 'index';

// if controller is protected and user not logged, show login form
if (in_array($controller, $protectedControllers, true) && empty($_SESSION['user'])) {
    $redirectUrl = 'index.php?c=' . urlencode($controller) . '&a=' . urlencode($action);
    ?>
    <!doctype html>
    <html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Connexion requise</title>
        <link rel="stylesheet" href="/Congrès/css/style.css">
        <style>
            /* petite adaptation pour le formulaire de login */
            .login-card{max-width:420px;margin:60px auto;padding:20px;border-radius:8px;background:#fff;box-shadow:0 8px 24px rgba(12,20,40,.06)}
            .login-card h2{margin:0 0 12px}
            .login-card label{display:block;margin-bottom:8px;font-size:.9rem}
            .login-card input{width:100%;padding:8px 10px;margin-top:4px;border:1px solid #e6e9ee;border-radius:6px}
            .login-actions{display:flex;gap:8px;align-items:center;margin-top:12px}
        </style>
    </head>
    <body>
    <div class="container">
        <div class="login-card">
            <h2>Connexion requise</h2>
            <?php if ($loginError): ?>
                <p style="color:#b91c1c;font-weight:600"><?= htmlspecialchars($loginError) ?></p>
            <?php endif; ?>
            <form method="post" action="<?= htmlspecialchars($redirectUrl) ?>">
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectUrl) ?>">
                <label>Identifiant
                    <input type="text" name="username" value="" autocomplete="username">
                </label>
                <label>Mot de passe
                    <input type="password" name="password" autocomplete="current-password">
                </label>
                <div class="login-actions">
                    <button class="btn" type="submit" name="login">Se connecter</button>
                    <div class="muted small">Utilise <strong>admin</strong> / <strong>admin</strong></div>
                </div>
            </form>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// normal routing to controllers
$controllerName = ucfirst($controller) . "Controller";
$controllerFile = __DIR__ . "/app/controllers/" . $controllerName . ".php";

if (file_exists($controllerFile)) {
    require $controllerFile;
    $ctrl = new $controllerName();
    if (method_exists($ctrl, $action)) {
        $ctrl->$action();
    } else {
        echo "Action introuvable";
    }
} else {
    echo "Contrôleur introuvable";
}
?>
<?php
// Index minimal — utilise css/style.css pour rendu fluide et animations
?><!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Congrès - Accueil</title>
  <link rel="stylesheet" href="/Congrès/css/style.css">
</head>
<body>
  <div class="container">
    <header class="site-header">
      <div>
        <h1 class="site-title">Application Congrès</h1>
        <p class="muted small">Gestion des congressistes, hôtels et organismes</p>
      </div>
      <!-- Liens supprimés -->
    </header>

    <main>
      <section class="card">
        <h2 style="margin-top:0">Bienvenue</h2>
        <p class="muted">Utilise les boutons ci‑dessous pour accéder rapidement aux fonctionnalités.</p>

        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px">
          <a href="index.php?c=congressiste&a=list" class="btn">Voir la liste</a>
          <a href="index.php?c=congressiste&a=create" class="btn secondary">Nouveau congressiste</a>
          <a href="index.php?c=hotel&a=list" class="btn" style="background:#10b981">Hôtels</a>
          <a href="index.php?c=organisme&a=list" class="btn" style="background:#f59e0b">Organismes</a>
        </div>
      </section>
        </ul>
      </section>
    </main>
  </div>
</body>
</html>