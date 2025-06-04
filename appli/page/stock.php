<?php
include '../donnée/connect.php';
?>
<title>Stock</title>
<?php include '../visuel/barre.php'; ?>

<div class="container my-4">
    <div class="card">
        <div class="card-header" style="background-color: rgb(206, 0, 0); color: white; font-size: 1.5rem; text-align: center;">
            Niveau de stock des produits
        </div>
        <div class="card-body">
            <?php
            // Make sure $DB_HOST, $DB_USER, $DB_PASS are defined in connect.php
            $pdo = new PDO('mysql:host=' . $DB_HOST . ';dbname=razanateraa_cinema;charset=utf8', $DB_USER, $DB_PASS);

            $message = '';

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Récupération de tous les tableaux
                $ids = $_POST['id_stock'];
                $seuils_min = $_POST['seuil_min'];
                $seuils_max = $_POST['seuil_max'];
                $stocks = $_POST['stock_actuel'];

                $nomsErreurs = [];

                if (isset($_POST['update_one'])) {
                    // Cas d'une seule ligne modifiée
                    $i = (int)$_POST['ligne_index'];
                    $id_stock = (int)$ids[$i];
                    $seuil_min = (int)$seuils_min[$i];
                    $stock_actuel = (int)$stocks[$i];
                    $seuil_max = $seuils_max[$i] !== '' ? (int)$seuils_max[$i] : $stock_actuel + 10;

                    $stmtNom = $pdo->prepare("SELECT p.nom FROM Produit p INNER JOIN QuantiteStock qs ON p.id_produit = qs.id_produit WHERE qs.id_stock = :id_stock");
                    $stmtNom->execute([':id_stock' => $id_stock]);
                    $nom = $stmtNom->fetchColumn();

                    $stmt = $pdo->prepare("UPDATE QuantiteStock SET seuil_min = :min, seuil_max = :max, stock_actuel = :actuel WHERE id_stock = :id");
                    $ok = $stmt->execute([
                        ':min' => $seuil_min,
                        ':max' => $seuil_max,
                        ':actuel' => $stock_actuel,
                        ':id' => $id_stock
                    ]);

                    $message = $ok
                        ? '<div class="alert alert-success text-center">Stock modifié pour <strong>' . htmlspecialchars($nom) . '</strong>.</div>'
                        : '<div class="alert alert-danger text-center">Erreur pour <strong>' . htmlspecialchars($nom) . '</strong>.</div>';
                }

                if (isset($_POST['update_all'])) {
                    foreach ($ids as $i => $id_stock) {
                        $id_stock = (int)$id_stock;
                        $seuil_min = (int)$seuils_min[$i];
                        $stock_actuel = (int)$stocks[$i];
                        $seuil_max = $seuils_max[$i] !== '' ? (int)$seuils_max[$i] : $stock_actuel + 10;

                        $stmtNom = $pdo->prepare("SELECT p.nom FROM Produit p INNER JOIN QuantiteStock qs ON p.id_produit = qs.id_produit WHERE qs.id_stock = :id_stock");
                        $stmtNom->execute([':id_stock' => $id_stock]);
                        $nom = $stmtNom->fetchColumn();

                        $stmt = $pdo->prepare("UPDATE QuantiteStock SET seuil_min = :min, seuil_max = :max, stock_actuel = :actuel WHERE id_stock = :id");
                        $ok = $stmt->execute([
                            ':min' => $seuil_min,
                            ':max' => $seuil_max,
                            ':actuel' => $stock_actuel,
                            ':id' => $id_stock
                        ]);

                        if (!$ok) $nomsErreurs[] = $nom ?: "ID $id_stock";
                    }

                    if (empty($nomsErreurs)) {
                        $message = '<div class="alert alert-success text-center">Tous les stocks ont été mis à jour avec succès.</div>';
                    } else {
                        $message = '<div class="alert alert-danger text-center">Erreur pour : <strong>' . implode(', ', array_map('htmlspecialchars', $nomsErreurs)) . '</strong></div>';
                    }
                }
            }
            

            $parPage = 30;
            $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $parPage;
            $total = $pdo->query("SELECT COUNT(*) FROM QuantiteStock")->fetchColumn();

            $sql = "SELECT qs.id_stock, qs.id_produit, p.nom AS nom_produit, qs.seuil_min, qs.seuil_max, qs.stock_actuel
                    FROM QuantiteStock qs
                    INNER JOIN Produit p ON qs.id_produit = p.id_produit
                    ORDER BY qs.id_stock ASC
                    LIMIT :offset, :limit";
            $stmtStock = $pdo->prepare($sql);
            $stmtStock->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmtStock->bindValue(':limit', $parPage, PDO::PARAM_INT);
            $stmtStock->execute();

            ?>

            <?= $message ?>

            <form method="post">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th>ID Stock</th>
                                <th>Produit</th>
                                <th>Seuil Min</th>
                                <th>Seuil Max</th>
                                <th>Stock Actuel</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 0; ?>
                            <?php while ($row = $stmtStock->fetch(PDO::FETCH_ASSOC)) : ?>
                                <tr>
                                    <td>
                                        <input type="hidden" name="id_stock[]" value="<?= $row['id_stock'] ?>">
                                        <?= $row['id_stock'] ?>
                                    </td>
                                    <td>
                                        <input type="hidden" name="id_produit[]" value="<?= $row['id_produit'] ?>">
                                        <?= htmlspecialchars($row['nom_produit']) ?>
                                    </td>
                                    <td><input type="number" name="seuil_min[]" value="<?= $row['seuil_min'] ?>" class="form-control"></td>
                                    <td><input type="number" name="seuil_max[]" value="<?= $row['seuil_max'] ?>" class="form-control"></td>
                                    <td><input type="number" name="stock_actuel[]" value="<?= $row['stock_actuel'] ?>" class="form-control"></td>
                                    <td>
                                        <button type="submit" name="update_one" value="1" class="btn btn-danger btn-sm" style="background-color: rgb(206, 0, 0);"
                                                onclick="document.getElementById('ligne_index').value = <?= $i ?>;">
                                            Modifier
                                        </button>
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <input type="hidden" name="ligne_index" id="ligne_index" value="">
                    <div class="text-center mt-3">
                        <button type="submit" name="update_all" class="btn btn-danger">Valider tous les stocks</button>
                    </div>
                </div>
            </form>

            <?php include '../visuel/pagination.php'; ?>
        </div>
    </div>
</div>
