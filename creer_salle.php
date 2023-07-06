<?php
// Vérifier si l'utilisateur est connecté
session_start();
if (!isset($_SESSION['utilisateur'])) {
  // Rediriger vers la page de connexion
  header('Location: login.php');
  exit();
}

// Inclure le fichier de configuration de la base de données
require_once 'config.php';

// Récupérer les types d'équipements depuis la base de données
try {
  $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $stmt = $conn->query("SELECT * FROM types_equipements");
  $typesEquipements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
  echo 'Erreur de connexion à la base de données : ' . $e->getMessage();
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nomSalle = $_POST['nom_salle'];
  $descriptionSalle = $_POST['description_salle'];
  $etatSalle = $_POST['etat_salle'];
  $equipementsSalle = $_POST['equipements_salle'];

  // Insérer la nouvelle salle dans la base de données
  try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("INSERT INTO salles (nom, description, etat) VALUES (:nom, :description, :etat)");
    $stmt->bindParam(':nom', $nomSalle);
    $stmt->bindParam(':description', $descriptionSalle);
    $stmt->bindParam(':etat', $etatSalle);
    $stmt->execute();

    // Récupérer l'id de la salle nouvellement créée
    $salleId = $conn->lastInsertId();

    // Associer les équipements à la salle
    foreach ($equipementsSalle as $typeEquipementId) {
      $stmt = $conn->prepare("INSERT INTO equipements (salle_id, type, etat) VALUES (:salle_id, :type, 1)");
      $stmt->bindParam(':salle_id', $salleId);
      $stmt->bindParam(':type', $typeEquipementId);
      $stmt->execute();
    }

    echo 'La salle a été créée avec succès !';

    // Rediriger vers la page de modification de la salle
    header('Location: modifier_salle.php?id=' . $salleId);
    exit();

  } catch(PDOException $e) {
    echo 'Erreur de connexion à la base de données : ' . $e->getMessage();
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Créer une salle et associer des équipements</title>
</head>
<body>
  <a href="gestion_salles.php">Accueil</a>
  <h2>Créer une salle et associer des équipements</h2>
  <form method="post" action="">
    <div>
      <label for="nom_salle">Nom :</label>
      <input type="text" id="nom_salle" name="nom_salle" required>
    </div>
    <div>
      <label for="description_salle">Description :</label>
      <textarea id="description_salle" name="description_salle"></textarea>
    </div>
    <div>
      <label for="etat_salle">État :</label>
      <select id="etat_salle" name="etat_salle" required>
        <option value="1">Fonctionnelle</option>
        <option value="0">Non fonctionnelle</option>
      </select>
    </div>
    <div>
      <label for="equipements_salle">Équipements :</label>
      <select id="equipements_salle" name="equipements_salle[]" multiple required>
        <?php foreach ($typesEquipements as $typeEquipement) : ?>
          <option value="<?php echo $typeEquipement['id']; ?>"><?php echo $typeEquipement['nom']; ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <input type="submit" value="Créer">
    </div>
  </form>
</body>
</html>
