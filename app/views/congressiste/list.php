<?php
// $items available from controller
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Liste des congressistes</title>
    <link rel="stylesheet" href="/Congrès/css/style.css">
    <style>.sort-toggle{cursor:pointer;padding:6px 10px;border-radius:8px;border:1px solid #e6e9ee;background:#fff}.badge{padding:4px 8px;border-radius:6px;color:#fff;font-weight:600}.badge-yes{background:#28a745}.badge-no{background:#dc3545}.card table{width:100%;border-collapse:collapse}.card th,.card td{padding:8px 10px;border-bottom:1px solid #eef2f6;text-align:left}.actions a,.actions button{margin-right:6px}</style>
</head>
<body>
    <div class="container">
        <header class="site-header">
            <div class="site-title">Gestion des congressistes</div>
            <nav class="nav">
                <a href="index.php">Accueil</a>
                <a href="index.php?c=congressiste&a=list">Liste</a>
                <a class="btn" href="index.php?c=congressiste&a=create">Ajouter</a>
            </nav>
        </header>

        <div class="card">
            <div class="controls" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div>
                    <div class="small muted">Tri par nom :</div>
                </div>
                <div>
                    <button id="toggleSort" class="sort-toggle">A → Z</button>
                </div>
            </div>

            <?php if (!empty($_GET['created'])): ?><p style="color:var(--accent)">Ajouté</p><?php endif; ?>
            <?php if (!empty($_GET['updated'])): ?><p style="color:var(--accent)">Mis à jour</p><?php endif; ?>
            <?php if (!empty($_GET['deleted'])): ?><p style="color:var(--accent)">Supprimé</p><?php endif; ?>

            <table id="congTable" class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Hôtel</th>
                        <th>Acompte</th>
                        <th>Petit-déjeuner</th>
                        <th>Étoiles souhaitées</th>
                        <th>Organisme</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($items)): ?>
                    <tr><td colspan="10">Aucun congressiste</td></tr>
                <?php else: ?>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td><?=htmlspecialchars($it['nom'] ?? '')?></td>
                            <td><?=htmlspecialchars($it['prenom'] ?? '')?></td>
                            <td><?=htmlspecialchars($it['email'] ?? '')?></td>
                            <td><?=htmlspecialchars($it['nom_hotel'] ?? '—')?></td>
                            <td>
                                <?php
                                    $ac = $it['acompte'] ?? $it['acompte_recu'] ?? null;
                                    $has = in_array($ac, [1, '1', true, 'true', 'Oui', 'oui'], true);
                                ?>
                                <span class="badge <?= $has ? 'badge-yes' : 'badge-no' ?>"><?= $has ? 'Oui' : 'Non' ?></span>
                            </td>
                            <td>
                                <?php
                                    $pd = $it['supplement_petit_dejeuner'] ?? $it['supplement_petit_dej'] ?? null;
                                    if ($pd === null) {
                                        echo '—';
                                    } else {
                                        // accept "Oui"/"Non" or boolean
                                        if (in_array($pd, [1, '1', true, 'true', 'Oui', 'oui'], true)) {
                                            echo '<span class="badge badge-yes">Oui</span>';
                                        } elseif (in_array($pd, [0, '0', false, 'false', 'Non', 'non'], true)) {
                                            echo '<span class="badge badge-no">Non</span>';
                                        } else {
                                            echo htmlspecialchars($pd);
                                        }
                                    }
                                ?>
                            </td>
                            <td><?= htmlspecialchars($it['nb_etoile_souhaite'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($it['nom_organisme'] ?? '—') ?></td>
                            <td class="actions">
                                <a class="btn secondary" href="index.php?c=congressiste&a=edit&id=<?=urlencode($it['id_congressiste'])?>">Modifier</a>
                                <form method="post" action="index.php?c=congressiste&a=delete" style="display:inline" onsubmit="return confirm('Supprimer ?');">
                                    <input type="hidden" name="id" value="<?=htmlspecialchars($it['id_congressiste'])?>">
                                    <button class="btn" type="submit">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Client-side alphabetical sorting toggle (by last name, then first name)
        (function(){
            const btn = document.getElementById('toggleSort');
            const table = document.getElementById('congTable');
            let asc = true;

            function getRows(){
                return Array.from(table.tBodies[0].rows);
            }

            function compareRows(a,b){
                const nameA = (a.cells[0].textContent || '').trim().toLowerCase();
                const nameB = (b.cells[0].textContent || '').trim().toLowerCase();
                if(nameA === nameB){
                    const fA = (a.cells[1].textContent || '').trim().toLowerCase();
                    const fB = (b.cells[1].textContent || '').trim().toLowerCase();
                    return fA.localeCompare(fB);
                }
                return nameA.localeCompare(nameB);
            }

            function sortTable(){
                const rows = getRows();
                rows.sort(compareRows);
                if(!asc) rows.reverse();
                const tbody = table.tBodies[0];
                rows.forEach(r=>tbody.appendChild(r));
                btn.textContent = asc ? 'A → Z' : 'Z → A';
            }

            btn.addEventListener('click', function(){
                asc = !asc;
                sortTable();
            });

            // initial sort ascending
            sortTable();
        })();
    </script>
</body>
</html>
