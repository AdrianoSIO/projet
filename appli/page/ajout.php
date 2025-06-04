<title>Ajout produits</title>
<?php include '../visuel/barre.php'; ?> 

<div class="container my-4">
    <div class="card">
        <div class="card-header" style="background-color: rgb(206, 0, 0); color: white; font-size: 1.5rem; text-align: center;">
            Nouveauté ou Futur ajout de produit
        </div>
        <div class="card-body">
            <?php
            include '../donnée/connect.php';
            $message = '';

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajout_produit'])) {
                $nom = trim($_POST['nom'] ?? '');
                $format = trim($_POST['format'] ?? '');
                $format = $format === '' ? null : $format;
                $actif = ($_POST['actif'] ?? '1') === '1' ? 1 : 0;
                $id_marque = $_POST['id_marque'] ?? null;
                $id_categorie = $_POST['id_categorie'] ?? null;

                $stock_actuel = is_numeric($_POST['stock_actuel'] ?? '') ? intval($_POST['stock_actuel']) : 0;
                $seuil_min = is_numeric($_POST['seuil_min'] ?? '') ? intval($_POST['seuil_min']) : 0;
                $seuil_max = is_numeric($_POST['seuil_max'] ?? '') ? intval($_POST['seuil_max']) : 0;

                if ($nom && $id_marque && $id_categorie) {
                    try {
                        $checkSql = "SELECT COUNT(*) FROM Produit 
                                     WHERE LOWER(nom) = LOWER(:nom) 
                                     AND id_marque = :id_marque 
                                     AND id_categorie = :id_categorie 
                                     AND ((format IS NULL AND :format IS NULL) OR LOWER(format) = LOWER(:format))";
                        $check = $pdo->prepare($checkSql);
                        $check->execute([
                            ':nom' => $nom,
                            ':format' => $format,
                            ':id_marque' => $id_marque,
                            ':id_categorie' => $id_categorie
                        ]);
                        $exists = $check->fetchColumn();

                        if ($exists > 0) {
                            $message = '<div class="alert alert-warning">Ce produit existe déjà avec les mêmes paramètres.</div>';
                        } else {
                            $stmt = $pdo->prepare("INSERT INTO Produit (nom, format, actif, id_marque, id_categorie)
                                                   VALUES (:nom, :format, :actif, :id_marque, :id_categorie)");
                            $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
                            $stmt->bindValue(':format', $format, $format === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                            $stmt->bindValue(':actif', $actif, PDO::PARAM_INT);
                            $stmt->bindValue(':id_marque', $id_marque, PDO::PARAM_INT);
                            $stmt->bindValue(':id_categorie', $id_categorie, PDO::PARAM_INT);
                            $stmt->execute();

                            $id_produit = $pdo->lastInsertId();

                            $stmtStock = $pdo->prepare("INSERT INTO QuantiteStock (id_produit, stock_actuel, seuil_min, seuil_max)
                                                        VALUES (:id_produit, :stock_actuel, :seuil_min, :seuil_max)");
                            $stmtStock->execute([
                                ':id_produit' => $id_produit,
                                ':stock_actuel' => $stock_actuel,
                                ':seuil_min' => $seuil_min,
                                ':seuil_max' => $seuil_max
                            ]);

                            $message = '<div class="alert alert-success">Produit ajouté avec succès, avec ses paramètres de stock.</div>';
                        }
                    } catch (PDOException $e) {
                        $message = '<div class="alert alert-danger">Erreur lors de l\'ajout : ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                } else {
                    $message = '<div class="alert alert-warning">Veuillez remplir tous les champs obligatoires.</div>';
                }
            }

            try {
                $stmt = $pdo->query("SELECT id_marque, nom FROM Marque ORDER BY nom");
                $marques = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo '<div class="alert alert-danger">Erreur lors de la récupération des marques : ' . htmlspecialchars($e->getMessage()) . '</div>';
            }

            try {
                $stmt = $pdo->query("SELECT id_categorie, nom FROM Categorie ORDER BY nom");
                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo '<div class="alert alert-danger">Erreur lors de la récupération des catégories : ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>

            <?= $message ?>
            <form method="post" class="row g-3" id="form-ajout">
                <input type="hidden" name="ajout_produit" value="1">
                <div class="col-md-4">
                    <label for="nom" class="form-label">Nom du produit</label>
                    <input type="text" class="form-control" id="nom" name="nom" required>
                </div>
                <div class="col-md-4">
                    <label for="format" class="form-label">Format (sans unité)</label>
                    <input type="text" class="form-control" id="format" name="format">
                </div>
                <div class="col-md-4">
                    <label for="actif" class="form-label">Statut</label>
                    <select class="form-select" id="actif" name="actif" required>
                        <option value="1">Actif</option>
                        <option value="0">Inactif</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="id_marque" class="form-label">Marque</label>
                    <select class="form-select" id="id_marque" name="id_marque" required>
                        <option value="">Sélectionner une marque</option>
                        <?php foreach ($marques as $marque): ?>
                            <option value="<?= htmlspecialchars($marque['id_marque']) ?>">
                                <?= htmlspecialchars($marque['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="id_categorie" class="form-label">Catégorie</label>
                    <select class="form-select" id="id_categorie" name="id_categorie" required>
                        <option value="">Sélectionner une catégorie</option>
                        <?php foreach ($categories as $categorie): ?>
                            <option value="<?= htmlspecialchars($categorie['id_categorie']) ?>">
                                <?= htmlspecialchars($categorie['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="stock_actuel" class="form-label">Stock actuel</label>
                    <input type="number" class="form-control" id="stock_actuel" name="stock_actuel" min="0" required>
                </div>
                <div class="col-md-4">
                    <label for="seuil_min" class="form-label">Seuil minimum</label>
                    <input type="number" class="form-control" id="seuil_min" name="seuil_min" min="0" required>
                </div>
                <div class="col-md-4">
                    <label for="seuil_max" class="form-label">Seuil maximum</label>
                    <input type="number" class="form-control" id="seuil_max" name="seuil_max" min="0" required>
                </div>

                <div class="col-12 text-center">
                    <button type="button" class="btn btn-danger" style="background-color: rgb(206, 0, 0);" onclick="verifierProduit()">
                        Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Bootstrap -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmModalLabel">Produit déjà existant</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body" id="modalBodyText">
        Ce produit existe déjà. Voulez-vous quand même l'ajouter ?
      </div>
      <div class="modal-footer">
        <!-- Oui = annuler -->
        <button type="button" class="btn btn-secondary" id="btnOui">Oui (annuler)</button>
        <!-- Non = ajouter quand même -->
        <button type="button" class="btn btn-danger" id="btnNon">Non, ajouter quand même</button>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS Bundle (Popper inclus) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

<script>
function verifierProduit() {
    const form = document.getElementById('form-ajout');
    const formData = new FormData(form);
    const nomProduit = document.getElementById('nom').value;

    fetch('check_produit.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.exists) {
            document.getElementById('modalBodyText').textContent = 
                `Le produit "${nomProduit}" existe déjà. Voulez-vous quand même l'ajouter ?`;

            const modalElement = document.getElementById('confirmModal');
            const modal = new bootstrap.Modal(modalElement);
            modal.show();

            const btnOui = document.getElementById('btnOui');
            const btnNon = document.getElementById('btnNon');

            // Nettoyer les events pour éviter doublons si modal réouvert
            btnOui.onclick = null;
            btnNon.onclick = null;

            btnOui.onclick = function() {
                // Annuler = fermer modal, ne rien faire d'autre
                modal.hide();
            };

            btnNon.onclick = function() {
                // Ajouter quand même = fermer modal et soumettre
                modal.hide();
                form.submit();
            };

        } else {
            form.submit();
        }
    })
    .catch(err => {
        console.error("Erreur lors de la vérification :", err);
        alert("Erreur lors de la vérification du produit.");
    });
}
</script>
