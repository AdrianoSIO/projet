<title>Produits Actif</title>
<?php include '../visuel/barre.php'; ?>

<div class="container my-4">
    <div class="card">
        <div class="card-header text-center" style="background-color: rgb(206, 0, 0); color: white; font-size: 1.5rem;">
            Liste des produits actifs et disponibles à la vente
        </div>
        <div class="card-body">

        <?php
        include '../donnée/connect.php';
        $pdo = new PDO('mysql:host=' . $DB_HOST . ';dbname=razanateraa_cinema;charset=utf8', $DB_USER, $DB_PASS);

        // Traitement du formulaire pour rendre un produit inactif
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rendre_inactif'])) {
            $idProduit = (int)$_POST['id_produit'];
            $update = $pdo->prepare("UPDATE Produit SET Actif = 0 WHERE id_produit = :id");
            $update->execute([':id' => $idProduit]);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

        // Pagination
        $parPage = 30;
        $page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $parPage;

        // Nombre total de produits actifs
        $total = $pdo->query("SELECT COUNT(*) FROM Produit WHERE Actif = 0")->fetchColumn();

        // Récupération des produits actifs avec pagination
        $sql = "SELECT p.id_produit, p.nom 
                FROM Produit p
                WHERE p.Actif = 0
                ORDER BY p.id_produit ASC
                LIMIT :offset, :parPage";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':parPage', $parPage, PDO::PARAM_INT);
        $stmt->execute();

        // Construction de l'URL de base pour pagination (conserve les autres paramètres GET)
        $queryParams = $_GET;
        unset($queryParams['page']);
        $urlBase = basename($_SERVER['PHP_SELF']);
        if (!empty($queryParams)) {
            $urlBase .= '?' . http_build_query($queryParams) . '&';
        } else {
            $urlBase .= '?';
        }
        ?>

        <div class="card mt-4">
            <div class="card-header text-center" style="background-color: #232323; color: white; font-size: 1.2rem;">
                Produits Inactifs
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th>Identifiant Produit</th>
                                <th>Nom du produit</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id_produit']) ?></td>
                                <td><?= htmlspecialchars($row['nom']) ?></td>
                                <td>
                                    <form method="post" style="margin:0;">
                                        <input type="hidden" name="id_produit" value="<?= htmlspecialchars($row['id_produit']) ?>">
                                        <button type="submit" name="rendre_actif" class="btn btn-danger btn-sm" style="background-color: rgb(206, 0, 0);">Rendre actif</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php include '../visuel/pagination.php'; ?>

            </div>
        </div>

        </div>
    </div>
</div>
