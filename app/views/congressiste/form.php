<?php
// $item may be set for edit, $errors may be set, $hotels and $organismes provided by controller
$isEdit = isset($item) && !empty($item);
$values = $isEdit ? $item : ($_POST ?? []);
$hotels = $hotels ?? [];
$organismes = $organismes ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= $isEdit ? 'Modifier' : 'Créer' ?> un congressiste</title>
    <link rel="stylesheet" href="/Congrès/css/style.css">
    <style>
        .form-row{margin-bottom:12px}
        .form-row label{display:block;margin-bottom:6px;font-weight:600}
        .form-row input[type="text"],
        .form-row input[type="email"],
        .form-row input[type="number"],
        .form-row select{width:100%;padding:8px;border-radius:6px;border:1px solid #e6e9ee}
        .form-actions{display:flex;gap:8px;align-items:center}
    </style>
</head>
<body>
    <div class="container">
        <header class="site-header">
            <div>
                <h1 class="site-title"><a href="index.php" style="text-decoration:none;color:inherit"><?= $isEdit ? 'Modifier' : 'Créer' ?> un congressiste</a></h1>
                <p class="muted small">Veuillez renseigner les informations ci-dessous</p>
            </div>
            <nav class="nav">
                <a href="index.php?c=congressiste&a=list" class="btn secondary small">Retour à la liste</a>
                <?php if (!empty($_SESSION['user'])): ?>
                    <a href="index.php?action=logout" class="btn secondary small" style="color:var(--danger)">Déconnexion</a>
                <?php endif; ?>
            </nav>
        </header>

        <div class="card">
            <?php if (!empty($errors)): ?>
                <ul style="color:red">
                <?php foreach ($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <form method="post" novalidate>
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id_congressiste" value="<?=htmlspecialchars($values['id_congressiste'] ?? $values['id'] ?? '')?>">
                <?php endif; ?>

                <div class="form-row">
                    <label>Nom *</label>
                    <input type="text" name="nom" required value="<?=htmlspecialchars($values['nom'] ?? '')?>">
                </div>

                <div class="form-row">
                    <label>Prénom *</label>
                    <input type="text" name="prenom" required value="<?=htmlspecialchars($values['prenom'] ?? '')?>">
                </div>

                <div class="form-row">
                    <label>Email *</label>
                    <input type="email" name="email" required value="<?=htmlspecialchars($values['email'] ?? '')?>">
                </div>

                <div class="form-row">
                    <label>Hôtel</label>
                    <select name="hotel_id">
                        <option value="">-- Aucun --</option>
                        <?php foreach ($hotels as $h):
                            $hid = $h['id'] ?? $h['id_hotel'] ?? '';
                            $hname = $h['nom'] ?? $h['name'] ?? ("Hôtel #".$hid);
                            $sel = ((string)($values['hotel_id'] ?? $values['id_hotel'] ?? '') === (string)$hid) ? 'selected' : '';
                        ?>
                            <option value="<?=htmlspecialchars($hid)?>" <?= $sel ?>><?=htmlspecialchars($hname)?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <label>
                        <input type="hidden" name="acompte" value="0">
                        <input type="checkbox" name="acompte" value="1" <?= !empty($values['acompte']) ? 'checked' : '' ?>> Acompte reçu
                    </label>
                </div>

                <div class="form-row">
                    <label>Supplément petit‑déjeuner</label>
                    <?php $pd = $values['supplement_petit_dejeuner'] ?? $values['supplement_petit_dej'] ?? ''; ?>
                    <select name="supplement_petit_dejeuner">
                        <option value="">-- choix --</option>
                        <option value="Oui" <?= ($pd === 'Oui') ? 'selected' : '' ?>>Oui</option>
                        <option value="Non" <?= ($pd === 'Non') ? 'selected' : '' ?>>Non</option>
                    </select>
                </div>

                <div class="form-row">
                    <label>Nombre d'étoiles souhaité</label>
                    <input type="number" name="nb_etoile_souhaite" min="1" max="5" value="<?=htmlspecialchars($values['nb_etoile_souhaite'] ?? '')?>">
                </div>

                <div class="form-row">
                    <label>Organisme payeur</label>
                    <select name="organisme_id">
                        <option value="">-- Aucun --</option>
                        <?php foreach ($organismes as $o):
                            $oid = $o['id'] ?? $o['id_organisme'] ?? '';
                            $oname = $o['nom'] ?? $o['name'] ?? ("Organisme #".$oid);
                            $sel = ((string)($values['organisme_id'] ?? $values['id_organisme'] ?? '') === (string)$oid) ? 'selected' : '';
                        ?>
                            <option value="<?=htmlspecialchars($oid)?>" <?= $sel ?>><?=htmlspecialchars($oname)?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row form-actions">
                    <button class="btn" type="submit"><?= $isEdit ? 'Mettre à jour' : 'Créer' ?></button>
                    <a class="btn secondary" href="index.php?c=congressiste&a=list">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
