<?php
header('Content-Type: application/json');
include '../donnée/connect.php';

$nom = $_POST['nom'] ?? '';
$format = $_POST['format'] ?? null;
$id_marque = $_POST['id_marque'] ?? null;
$id_categorie = $_POST['id_categorie'] ?? null;

if (!$nom || !$id_marque || !$id_categorie) {
    echo json_encode(['exists' => false]);
    exit;
}

$sql = "SELECT COUNT(*) FROM Produit 
        WHERE LOWER(nom) = LOWER(:nom) 
        AND id_marque = :id_marque 
        AND id_categorie = :id_categorie 
        AND ((format IS NULL AND :format IS NULL) OR LOWER(format) = LOWER(:format))";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':nom' => $nom,
    ':format' => $format,
    ':id_marque' => $id_marque,
    ':id_categorie' => $id_categorie
]);

$exists = $stmt->fetchColumn() > 0;

echo json_encode(['exists' => $exists]);
