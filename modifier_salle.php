<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur'])) {
  // Rediriger vers la page de connexion
  header('Location: login.php');
  exit();
}

// Inclure le fichier de configuration de la base de données
require_once 'config.php';

// Vérifier si l'ID de la salle est passé dans l'URL
if (!isset($_GET['id'])) {
  header('Location: gestion_salles.php');
  exit();
}

// Récupérer l'ID de la salle depuis l'URL
$salleId = $_GET['id'];

// Récupérer les détails de la salle depuis la base de données
try {
  $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $stmt = $conn->prepare("SELECT * FROM salles WHERE id = :id");
  $stmt->bindParam(':id', $salleId);
  $stmt->execute();
  $salle = $stmt->fetch(PDO::FETCH_ASSOC);

  // Vérifier si la salle existe
  if (!$salle) {
    header('Location: gestion_salles.php');
    exit();
  }

  // Récupérer les équipements de la salle depuis la base de données
  $stmt = $conn->prepare("SELECT e.id, t.nom AS type, e.etat, e.description FROM equipements e INNER JOIN types_equipements t ON e.type = t.id WHERE salle_id = :salle_id");
  $stmt->bindParam(':salle_id', $salleId);
  $stmt->execute();
  $equipements = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Récupérer tous les types d'équipements pour la modification
  $stmt = $conn->query("SELECT * FROM types_equipements");
  $typesEquipements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
  echo 'Erreur de connexion à la base de données : ' . $e->getMessage();
}

// Modifier l'état et la description de la salle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_salle'])) {
  $nouvelleDescription = $_POST['description'];
  $nouvelEtatSalle = $_POST['etat_salle'];

  $stmt = $conn->prepare("UPDATE salles SET description = :description, etat = :etat WHERE id = :id");
  $stmt->bindParam(':description', $nouvelleDescription);
  $stmt->bindParam(':etat', $nouvelEtatSalle);
  $stmt->bindParam(':id', $salleId);
  $stmt->execute();

  echo 'La description et l\'état de la salle ont été modifiés avec succès !';
}

// Modifier l'état et la description des équipements de la salle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_equipement'])) {
  $equipementId = $_POST['equipement_id'];
  $nouvelEtatEquipement = $_POST['etat_' . $equipementId];
  $nouvelleDescriptionEquipement = $_POST['description_equipement_' . $equipementId];

  $stmt = $conn->prepare("UPDATE equipements SET etat = :etat, description = :description WHERE id = :id");
  $stmt->bindParam(':etat', $nouvelEtatEquipement);
  $stmt->bindParam(':description', $nouvelleDescriptionEquipement);
  $stmt->bindParam(':id', $equipementId);
  $stmt->execute();

  echo 'L\'état et la description de l\'équipement ont été modifiés avec succès !';
}

// Ajouter un nouvel équipement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_equipement'])) {
  $nouveauEquipementId = $_POST['nouvel_equipement'];

  // Vérifier si l'équipement n'est pas déjà associé à la salle
  $stmt = $conn->prepare("SELECT COUNT(*) FROM equipements WHERE salle_id = :salle_id AND type = :type");
  $stmt->bindParam(':salle_id', $salleId);
  $stmt->bindParam(':type', $nouveauEquipementId);
  $stmt->execute();
  $count = $stmt->fetchColumn();

  if ($count === 0) {
    $stmt = $conn->prepare("INSERT INTO equipements (salle_id, type, etat) VALUES (:salle_id, :type, 1)");
    $stmt->bindParam(':salle_id', $salleId);
    $stmt->bindParam(':type', $nouveauEquipementId);
    $stmt->execute();

    echo 'Le nouvel équipement a été ajouté à la salle avec succès !';
  } else {
    echo 'Cet équipement est déjà associé à la salle.';
  }
}

// Supprimer un équipement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_equipement'])) {
  $equipementId = $_POST['supprimer_equipement'];

  $stmt = $conn->prepare("DELETE FROM equipements WHERE id = :id");
  $stmt->bindParam(':id', $equipementId);
  $stmt->execute();

  echo 'L\'équipement a été supprimé de la salle avec succès !';
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Modifier la salle</title>
</head>
<body>
  <a href="gestion_salles.php">Accueil</a>
  <h2>Modifier la salle <?php echo $salle['nom']; ?></h2>
  <form method="post" action="">
    <div>
      <label for="description">Nouvelle description :</label>
      <textarea id="description" name="description" required><?php echo $salle['description']; ?></textarea>
    </div>
    <div>
      <label for="etat_salle">Nouvel état de la salle :</label>
      <select id="etat_salle" name="etat_salle">
        <option value="1" <?php echo ($salle['etat'] ? 'selected' : ''); ?>>Fonctionnelle</option>
        <option value="0" <?php echo (!$salle['etat'] ? 'selected' : ''); ?>>Non fonctionnelle</option>
      </select>
    </div>
    <div>
      <input type="submit" name="modifier_salle" value="Modifier la salle">
    </div>
  </form>

  <h3>Équipements</h3>
  <?php foreach ($equipements as $equipement) : ?>
    <form method="post" action="">
      <input type="hidden" name="equipement_id" value="<?php echo $equipement['id']; ?>">
      <div>
        <label for="etat_<?php echo $equipement['id']; ?>">
          <?php echo $equipement['type']; ?> :
          <select id="etat_<?php echo $equipement['id']; ?>" name="etat_<?php echo $equipement['id']; ?>">
            <option value="1" <?php echo ($equipement['etat'] ? 'selected' : ''); ?>>Fonctionnel</option>
            <option value="0" <?php echo (!$equipement['etat'] ? 'selected' : ''); ?>>Non fonctionnel</option>
          </select>
        </label>
      </div>
      <div>
        <label for="description_equipement_<?php echo $equipement['id']; ?>">
          Description de <?php echo $equipement['type']; ?> :
          <textarea id="description_equipement_<?php echo $equipement['id']; ?>" name="description_equipement_<?php echo $equipement['id']; ?>"><?php echo $equipement['description']; ?></textarea>
        </label>
      </div>
      <div>
        <input type="submit" name="modifier_equipement" value="Modifier l'état et la description">
      </div>
    </form>
  <?php endforeach; ?>

  <h3>Ajouter un équipement</h3>
  <form method="post" action="">
    <div>
      <label for="nouvel_equipement">Nouvel équipement :</label>
      <select id="nouvel_equipement" name="nouvel_equipement" required>
        <option value="">Sélectionner un équipement</option>
        <?php foreach ($typesEquipements as $typeEquipement) : ?>
          <option value="<?php echo $typeEquipement['id']; ?>"><?php echo $typeEquipement['nom']; ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <input type="submit" name="ajouter_equipement" value="Ajouter">
    </div>
  </form>

  <h3>Supprimer un équipement</h3>
  <form method="post" action="">
    <div>
      <label for="supprimer_equipement">Équipement :</label>
      <select id="supprimer_equipement" name="supprimer_equipement" required>
        <option value="">Sélectionner un équipement à supprimer</option>
        <?php foreach ($equipements as $equipement) : ?>
          <option value="<?php echo $equipement['id']; ?>"><?php echo $equipement['type']; ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <input type="submit" value="Supprimer">
    </div>
  </form>
</body>
</html>
