<?php
// commandes.php avec recherche sur toutes les colonnes du INNER JOIN, mais affichage complet de chaque commande

$title = "Commande de produits";

include '../visuel/barre.php'; // Mini-nav-bar rouge
include '../donnée/connect.php';

try {
    $pdo = new PDO('mysql:host=' . $DB_HOST . ';dbname=razanateraa_cinema;charset=utf8', $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('<div class="alert alert-danger">Erreur de connexion à la base de données : ' . htmlspecialchars($e->getMessage()) . '</div>');
}

// Pagination
$perPage = 40;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $perPage;

// Champ recherche
$search = $_GET['search'] ?? '';
$search = trim($search);

$params = [];
$whereSub = '';

if ($search !== '') {
    // Recherche sur toutes les colonnes de Addition, Commande, Produit (mais pour filtrer les id_commande)
    $searchLike = '%' . $search . '%';

    // On crée un sous-requête pour récupérer les id_commande qui correspondent à la recherche dans au moins une colonne
    // Attention, on doit caster les colonnes en CHAR pour LIKE fonctionner partout
    
    $whereSub = "WHERE (";
    $conditions = [];

    // Addition (a)
    $columnsA = ['id_commande', 'id_produit', 'quantite']; // listes fixes, pour éviter SHOW COLUMNS en boucle
    foreach ($columnsA as $col) {
        $conditions[] = "CAST(a.$col AS CHAR) LIKE :search";
    }
    // Commande (c)
    $columnsC = ['id_commande', 'date_commande'];
    foreach ($columnsC as $col) {
        $conditions[] = "CAST(c.$col AS CHAR) LIKE :search";
    }
    // Produit (p)
    $columnsP = ['id_produit', 'nom']; 
    foreach ($columnsP as $col) {
        $conditions[] = "CAST(p.$col AS CHAR) LIKE :search";
    }

    $whereSub .= implode(' OR ', $conditions);
    $whereSub .= ")";

    $params[':search'] = $searchLike;
}

// Requête pour compter le total des commandes filtrées
$countSql = "
    SELECT COUNT(DISTINCT a.id_commande) AS total
    FROM Addition a
    INNER JOIN Commande c ON a.id_commande = c.id_commande
    INNER JOIN Produit p ON a.id_produit = p.id_produit
    $whereSub
";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRows = $countStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
$totalPages = ceil($totalRows / $perPage);

// Requête principale : on sélectionne les commandes dont l'id est dans la sous-requête filtrée
// Puis on fait le GROUP_CONCAT des produits

$mainSql = "
    SELECT 
        a.id_commande,
        c.date_commande,
        GROUP_CONCAT(CONCAT(p.nom, ' (x', a.quantite, ')') ORDER BY p.nom SEPARATOR ', ') AS details_produits,
        SUM(a.quantite) AS total_quantite
    FROM Addition a
    INNER JOIN Commande c ON a.id_commande = c.id_commande
    INNER JOIN Produit p ON a.id_produit = p.id_produit
    WHERE a.id_commande IN (
        SELECT DISTINCT a2.id_commande
        FROM Addition a2
        INNER JOIN Commande c2 ON a2.id_commande = c2.id_commande
        INNER JOIN Produit p2 ON a2.id_produit = p2.id_produit
        $whereSub
    )
    GROUP BY a.id_commande, c.date_commande
    ORDER BY a.id_commande DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($mainSql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

// Quantité totale globale filtrée (sur les commandes filtrées)
$totalSql = "
    SELECT SUM(a.quantite) AS total
    FROM Addition a
    INNER JOIN Commande c ON a.id_commande = c.id_commande
    INNER JOIN Produit p ON a.id_produit = p.id_produit
    WHERE a.id_commande IN (
        SELECT DISTINCT a2.id_commande
        FROM Addition a2
        INNER JOIN Commande c2 ON a2.id_commande = c2.id_commande
        INNER JOIN Produit p2 ON a2.id_produit = p2.id_produit
        $whereSub
    )
";
$totalStmt = $pdo->prepare($totalSql);
foreach ($params as $key => $val) {
    $totalStmt->bindValue($key, $val, PDO::PARAM_STR);
}
$totalStmt->execute();
$globalTotal = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>

<div class="container my-4">
  <div class="card">
    <div class="card-header text-center" style="background-color: rgb(206, 0, 0); color: white; font-size: 1.5rem;">
      Liste des additions (avec date de commande)
    </div>
    <div class="card-body">

      <!-- Formulaire de recherche -->
      <form method="get" class="mb-3 d-flex" role="search" aria-label="Recherche commandes">
        <input type="text" name="search" class="form-control me-2" placeholder="Recherche par produit, date format YYYY-MM-DD, ou autres..." value="<?= htmlspecialchars($search) ?>" />
        <button type="submit" class="btn btn-primary"style="background-color: rgb(206, 0, 0)">Rechercher</button>
      </form>

      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead class="table-secondary">
            <tr>
              <th>Identifiant Commande</th>
              <th>Date</th>
              <th>Produits (avec quantités)</th>
              <th>Quantité totale</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($totalRows == 0): ?>
              <tr><td colspan="4" class="text-center">Aucun résultat trouvé.</td></tr>
            <?php else: ?>
              <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                  <td><?= htmlspecialchars($row['id_commande']) ?></td>
                  <td><?= htmlspecialchars($row['date_commande']) ?></td>
                  <td><?= htmlspecialchars($row['details_produits']) ?></td>
                  <td><?= htmlspecialchars($row['total_quantite']) ?></td>
                </tr>
              <?php endwhile; ?>
            <?php endif; ?>
          </tbody>
          <tfoot>
            <tr class="table-dark fw-bold">
              <td colspan="3" class="text-end">Quantité totale globale :</td>
              <td><?= htmlspecialchars($globalTotal) ?></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Pagination -->
      <nav aria-label="Pagination" class="d-flex justify-content-center my-3">
        <ul class="pagination">
          <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
            <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= max(1, $page - 1) ?>">Précédent</a>
          </li>

          <?php
          $maxPagesToShow = 5;
          $startPage = max(1, $page - floor($maxPagesToShow / 2));
          $endPage = min($totalPages, $startPage + $maxPagesToShow - 1);
          if ($endPage - $startPage + 1 < $maxPagesToShow) {
              $startPage = max(1, $endPage - $maxPagesToShow + 1);
          }
          for ($i = $startPage; $i <= $endPage; $i++): ?>
            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
              <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>

          <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
            <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= min($totalPages, $page + 1) ?>">Suivant</a>
          </li>
        </ul>
      </nav>

    </div>
  </div>
</div>

</body>
</html>
