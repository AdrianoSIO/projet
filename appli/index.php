<?php
session_start();

include 'donnée/connect.php';
$pdo = new PDO('mysql:host=' . $DB_HOST . ';dbname=razanateraa_cinema;charset=utf8', $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// Variables pagination, recherche et filtre actif
$limit = 30;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$actifFilter = isset($_GET['actif']) ? $_GET['actif'] : null;
if ($actifFilter !== null && !in_array($actifFilter, ['0', '1'], true)) {
    $actifFilter = null; // filtre actif uniquement 0 ou 1
}

// Construction clause WHERE
$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(p.nom LIKE :search OR m.nom LIKE :search OR c.nom LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}
if ($actifFilter !== null) {
    $where[] = "p.actif = :actif";
    $params[':actif'] = (int)$actifFilter;
}

$whereSql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Comptage total produits avec jointures (LEFT JOIN pour ne pas perdre de produits)
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM Produit p
                            LEFT JOIN Marque m ON p.id_marque = m.id_marque
                            LEFT JOIN Categorie c ON p.id_categorie = c.id_categorie
                            $whereSql");
$stmtCount->execute($params);
$totalProduits = (int)$stmtCount->fetchColumn();
$totalPages = (int)ceil($totalProduits / $limit);

// Récupération produits avec limite, offset et calcul stock actuel
$orderBy = 'p.nom ASC';
if ($search === '' && $actifFilter === null) {
    $orderBy = 'p.id_produit ASC';
}
$sql = "SELECT p.*, m.nom AS nom_marque, c.nom AS nom_categorie,
               COALESCE((SELECT SUM(qs.stock_actuel) FROM QuantiteStock qs WHERE qs.id_produit = p.id_produit), 0) AS stock_actuel
        FROM Produit p
        LEFT JOIN Marque m ON p.id_marque = m.id_marque
        LEFT JOIN Categorie c ON p.id_categorie = c.id_categorie
        $whereSql
        ORDER BY $orderBy
        LIMIT :limit OFFSET :offset";


$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $type = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue($key, $val, $type);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

// Initialisation panier session
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Supprimer produit du panier
if (isset($_GET['supprimer'])) {
    $idSuppr = $_GET['supprimer'];
    unset($_SESSION['panier'][$idSuppr]);
    // Redirection pour éviter double suppression au refresh
    $redirectParams = $_GET;
    unset($redirectParams['supprimer']);
    header('Location: index.php?' . http_build_query($redirectParams));
    exit;
}

// Gestion ajout/modification panier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['quantites'], $_POST['id_produits'], $_POST['actions'])) {
        foreach ($_POST['id_produits'] as $i => $idProduit) {
            $quantite = (int)($_POST['quantites'][$i] ?? 0);
            $action = $_POST['actions'][$i] ?? 'ajouter';
            if ($quantite > 0) {
                $_SESSION['panier'][$idProduit] = [
                    'quantite' => $quantite,
                    'action' => $action
                ];
            }
        }
    }
   if (isset($_POST['valider_panier'])) {

    // 🔽 Création d'une commande avec la date actuelle
    $stmtCommande = $pdo->prepare("INSERT INTO Commande (date_commande) VALUES (NOW())");
    $stmtCommande->execute();
    $idCommande = $pdo->lastInsertId(); // Récupère l'ID de la commande

    // 🔽 Préparation des requêtes d'insertion
    $stmtAddition = $pdo->prepare("INSERT INTO Addition (id_commande, id_produit, quantite) VALUES (?, ?, ?)");
    $stmtMouvement = $pdo->prepare("INSERT INTO Mouvement (id_commande, id_produit, quantite, type_mouvement) VALUES (?, ?, ?, ?)");

    foreach ($_SESSION['panier'] as $idProduit => $details) {
        $quantite = $details['quantite'];
        $action = $details['action'];

        // Récupérer la quantité actuelle
        $stmtStock = $pdo->prepare("SELECT stock_actuel FROM QuantiteStock WHERE id_produit = ?");
        $stmtStock->execute([$idProduit]);
        $result = $stmtStock->fetch();

        if (!$result) continue;

        $stockActuel = (int)$result['stock_actuel'];

        // Calcul du nouveau stock
        if ($action === 'ajouter') {
            $nouveauStock = $stockActuel + $quantite;
            $typeMouvement = 'entrée';
        } elseif ($action === 'supprimer') {
            $nouveauStock = max(0, $stockActuel - $quantite);
            $typeMouvement = 'sortie';
        } else {
            continue;
        }

        // Mise à jour du stock
        $stmtUpdate = $pdo->prepare("UPDATE QuantiteStock SET stock_actuel = ? WHERE id_produit = ?");
        $stmtUpdate->execute([$nouveauStock, $idProduit]);

        // Insertion dans Addition
        $stmtAddition->execute([$idCommande, $idProduit, $quantite]);

        // Insertion dans Mouvement (avec id_commande)
        $stmtMouvement->execute([$idCommande, $idProduit, $quantite, $typeMouvement]);
    }

    // Vider le panier
    $_SESSION['panier'] = [];

    // Redirection
    header('Location: index.php?success=1');
    exit;
}

}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Produits</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
</head>
<body>

<?php include 'visuel/special.php'; ?>

<div class="container my-4">
  <div class="card">
    <div class="card-header text-center" style="background-color: rgb(206, 0, 0); color: white; font-size: 1.5rem;">
      Liste des produits
    </div>

    <!-- Boutons filtre actifs / inactifs -->
    <div class="text-center mb-3">
      <a href="?actif=1<?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"
         class="btn btn-success<?= $actifFilter === '1' ? ' active' : '' ?>"
         style="background-color: rgb(0, 206, 27);">
        Afficher actifs
      </a>
      <a href="?actif=0<?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"
         class="btn btn-secondary<?= $actifFilter === '0' ? ' active' : '' ?>"
         style="background-color: rgb(206, 0, 0);">
        Afficher inactifs
      </a>
      <a href="index.php" class="btn btn-outline-dark">
        Réinitialiser
      </a>
    </div>

    <!-- Formulaire recherche -->
    <form method="get" class="my-3 d-flex justify-content-center" role="search">
      <input type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Rechercher un produit, marque ou catégorie" class="form-control w-50" />
      <?php if ($actifFilter !== null): ?>
        <input type="hidden" name="actif" value="<?= htmlspecialchars($actifFilter) ?>">
      <?php endif; ?>
      <button type="submit" class="btn btn-primary ms-2" style="background-color: rgb(206, 0, 0)">Rechercher</button>
    </form>

    <div class="p-3" style="font-size: 0.95rem;">
      <p>Une case orange indique un produit inactif (Actif = 0).</p>
      <p>Une case colorée dans le stock actuel signale un besoin de réapprovisionnement.</p>
    </div>

    <form method="post">
      <div class="card-body">

        <?php if (isset($_GET['success'])): ?>
          <div class="alert alert-success">
            Mouvements enregistrés, stocks mis à jour, commande créée avec succès.
          </div>
        <?php endif; ?>

        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead class="table-secondary" style="background-color: #232323;">
              <tr>
                <th>Identifiant</th>
                <th>Nom</th>
                <th>Format</th>
                <th>Actif</th>
                <th>Marque</th>
                <th>Catégorie</th>
                <th>Stock actuel</th>
                <th>Quantité</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($totalProduits === 0): ?>
                <tr><td colspan="9" class="text-center">Aucun produit trouvé.</td></tr>
              <?php else: ?>
                <?php while ($row = $stmt->fetch()): ?>
                  <?php
                  $stock = $row['stock_actuel'] ?? 0;
                  $seuil_min = $row['seuil_min'] ?? 0;
                  $actif = $row['actif'];

                  $highlightStockStyle = ($stock < $seuil_min) ? 'style="background-color: #ff9999;"' : '';
                  $actifClass = $actif == 1 ? 'table-success' : 'table-danger';
                  $inactiveStyle = $actif == 0 ? 'style="background-color: orange;"' : '';
                  ?>
                  <tr>
                    <td <?= $inactiveStyle ?>><?= htmlspecialchars($row['id_produit']) ?></td>
                    <td <?= $inactiveStyle ?>><?= htmlspecialchars($row['nom']) ?></td>
                    <td><?= htmlspecialchars($row['format']) ?></td>
                    <td class="<?= $actifClass ?>"><?= $actif == 1 ? 'Actif' : 'Inactif' ?></td>
                    <td><?= htmlspecialchars($row['nom_marque']) ?></td>
                    <td><?= htmlspecialchars($row['nom_categorie']) ?></td>
                    <td <?= $highlightStockStyle ?>><?= $stock ?></td>
                    <td>
                      <input type="number" name="quantites[]" class="form-control" min="0" value="0" style="width: 90px;">
                      <input type="hidden" name="id_produits[]" value="<?= htmlspecialchars($row['id_produit']) ?>">
                    </td>
                    <td>
                      <select name="actions[]" class="form-select">
                        <option value="ajouter">Ajouter</option>
                        <option value="supprimer">Supprimer</option>
                      </select>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <button type="submit" name="voir_panier" class="btn btn-warning">
          <i class="bi bi-clipboard-check"></i> Mouvements du stock
        </button>

        <?php if (!empty($_SESSION['panier'])): ?>
          <div class="mt-4 p-3 border bg-light">
            <h5>Contenu du panier :</h5>
            <ul class="list-group">
              <?php foreach ($_SESSION['panier'] as $id => $details): ?>
                <?php
                $stmtNom = $pdo->prepare("SELECT nom FROM Produit WHERE id_produit = ?");
                $stmtNom->execute([$id]);
                $produit = $stmtNom->fetch();
                $nomProduit = $produit ? $produit['nom'] : 'Produit inconnu';
                $actionText = $details['action'] === 'ajouter' ? 'ajouter' : 'retirer';
                ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <?= "Vous souhaitez $actionText {$details['quantite']} de " . htmlspecialchars($nomProduit) . "." ?>
                  <a href="?supprimer=<?= urlencode($id) ?>" class="btn btn-sm btn-danger ms-3">
                    <i class="bi bi-trash"></i> Supprimer
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
            <button type="submit" name="valider_panier" class="btn btn-success mt-3">
              <i class="bi bi-check-circle"></i> Valider le panier
            </button>
          </div>
        <?php endif; ?>

      </div>
    </form>

    <nav class="my-4">
      <ul class="pagination justify-content-center">
        <?php
        // Prépare les paramètres à passer dans les liens de pagination
        $baseQuery = [];
        if ($search !== '') {
            $baseQuery['search'] = $search;
        }
        if ($actifFilter !== null) {
            $baseQuery['actif'] = $actifFilter;
        }
        ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <?php
          $baseQuery['page'] = $i;
          $link = 'index.php?' . http_build_query($baseQuery);
          ?>
          <li class="page-item <?= $page === $i ? 'active' : '' ?>">
            <a href="<?= $link ?>" class="page-link"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
