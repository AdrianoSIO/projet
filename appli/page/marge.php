<title>Marge</title>
<?php include '../visuel/barre.php'; ?>

<div class="container my-4">
    <div class="card">
        <div class="card-header" style="background-color: rgb(206, 0, 0); color: white; font-size: 1.5rem; text-align: center;">
            Informations sur la marge des produits
        </div>
        <div class="card-body">
            <?php
            include '../donnée/connect.php';

            try {
                $pdo = new PDO('mysql:host=' . $DB_HOST . ';dbname=razanateraa_cinema;charset=utf8', $DB_USER, $DB_PASS);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die('<div class="alert alert-danger">Erreur de connexion à la base de données : ' . htmlspecialchars($e->getMessage()) . '</div>');
            }

            $message = '';

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_marge'])) {
                $id_marge = (int)$_POST['id_marge'];
                $id_produit = (int)$_POST['id_produit'];
                $qte = (int)$_POST['qte'];
                $prix_HT = (float)$_POST['prix_HT'];
                $pourcentage = (int)$_POST['pourcentage'];
                $prix_Vente = (float)$_POST['prix_Vente'];

                // Récupérer le nom du produit
                $stmtNom = $pdo->prepare("SELECT nom FROM Produit WHERE id_produit = ?");
                $stmtNom->execute([$id_produit]);
                $nom_produit = $stmtNom->fetchColumn();

                $sqlUpdate = "UPDATE Marge 
                              SET id_produit = :id_produit, qte = :qte, prix_HT = :prix_HT, 
                                  pourcentage = :pourcentage, prix_Vente = :prix_Vente
                              WHERE id_marge = :id_marge";

                $stmtUpdate = $pdo->prepare($sqlUpdate);
                $success = $stmtUpdate->execute([
                    ':id_produit' => $id_produit,
                    ':qte' => $qte,
                    ':prix_HT' => $prix_HT,
                    ':pourcentage' => $pourcentage,
                    ':prix_Vente' => $prix_Vente,
                    ':id_marge' => $id_marge
                ]);

                if ($success) {
                    $message = '<div class="alert alert-success text-center">Changement validé et accepté pour <strong>' . htmlspecialchars($nom_produit) . '</strong>.</div>';
                } else {
                    $message = '<div class="alert alert-danger text-center">Erreur lors de la modification de la marge pour <strong>' . htmlspecialchars($nom_produit) . '</strong>.</div>';
                }
            }

            // Pagination
            $parPage = 30;
            $page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $parPage;

            $total = $pdo->query("SELECT COUNT(*) FROM Marge")->fetchColumn();

            // Requête avec INNER JOIN pour récupérer nom produit
            $sqlMarge = "SELECT Marge.*, Produit.nom
                         FROM Marge 
                         INNER JOIN Produit ON Marge.id_produit = Produit.id_produit
                         ORDER BY Marge.id_marge ASC
                         LIMIT :offset, :parPage";

            $stmtMarge = $pdo->prepare($sqlMarge);
            $stmtMarge->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmtMarge->bindValue(':parPage', $parPage, PDO::PARAM_INT);
            $stmtMarge->execute();

            // URL base pagination
            $queryParams = $_GET;
            unset($queryParams['page']);
            $urlBase = basename($_SERVER['PHP_SELF']);
            $urlBase .= (!empty($queryParams)) ? '?' . http_build_query($queryParams) . '&' : '?';
            ?>

            <?= $message ?>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>ID</th>
                            <th>Produit</th>
                            <th>Quantité</th>
                            <th>Prix HT</th>
                            <th>%</th>
                            <th>Prix TTC</th>
                            <th>Prix U</th>
                            <th>Estimation</th>
                            <th>Prix Vente</th>
                            <th>Marge</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $stmtMarge->fetch(PDO::FETCH_ASSOC)) : ?>
                            <tr>
                                <form method="post">
                                    <td>
                                        <input type="hidden" name="id_marge" value="<?= htmlspecialchars($row['id_marge']) ?>">
                                        <?= htmlspecialchars($row['id_marge']) ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($row['nom']) ?>
                                        <input type="hidden" name="id_produit" value="<?= htmlspecialchars($row['id_produit']) ?>">
                                    </td>
                                    <td>
                                        <input type="number" name="qte" value="<?= htmlspecialchars($row['qte']) ?>" class="form-control" required min="0">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="prix_HT" value="<?= htmlspecialchars($row['prix_HT']) ?>" class="form-control" required min="0">
                                    </td>
                                    <td>
                                        <select name="pourcentage" class="form-control" required>
                                            <option value="5" <?= $row['pourcentage'] == 5 ? 'selected' : '' ?>>5%</option>
                                            <option value="20" <?= $row['pourcentage'] == 20 ? 'selected' : '' ?>>20%</option>
                                        </select>
                                    </td>
                                    <td><?= htmlspecialchars($row['prix_TTC']) ?></td>
                                    <td><?= htmlspecialchars($row['prix_U']) ?></td>
                                    <td><?= htmlspecialchars($row['prix_Esti']) ?></td>
                                    <td>
                                        <input type="number" step="0.01" name="prix_Vente" value="<?= htmlspecialchars($row['prix_Vente']) ?>" class="form-control" required min="0">
                                    </td>
                                    <td><?= htmlspecialchars($row['prix_Marge']) ?></td>
                                    <td>
                                        <button type="submit" name="update_marge" class="btn btn-danger btn-sm" style="background-color: rgb(206, 0, 0);">Modifier</button>
                                    </td>
                                </form>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <?php include '../visuel/pagination.php'; ?>

        </div>
    </div>
</div>
