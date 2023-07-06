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

  $stmt = $conn->prepare("SELECT * FROM salles WHERE id = :id ORDER BY nom");
  $stmt->bindParam(':id', $salleId);
  $stmt->execute();
  $salle = $stmt->fetch(PDO::FETCH_ASSOC);

  // Vérifier si la salle existe
  if (!$salle) {
    header('Location: gestion_salles.php');
    exit();
  }

  // Récupérer les équipements de la salle depuis la base de données
  $stmt = $conn->prepare("SELECT e.*, t.nom AS type FROM equipements e INNER JOIN types_equipements t ON e.type = t.id WHERE salle_id = :salle_id");
  $stmt->bindParam(':salle_id', $salleId);
  $stmt->execute();
  $equipements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
  echo 'Erreur de connexion à la base de données : ' . $e->getMessage();
}

// Redirection vers la page de modification de la salle
if (isset($_POST['modifier_salle'])) {
  header('Location: modifier_salle.php?id=' . $salleId);
  exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Consulter la salle</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <a href="gestion_salles.php">Accueil</a>
  <h2>Consulter la salle <?php echo $salle['nom']; ?></h2>
  <p>Description : <?php echo $salle['description']; ?></p>
  <p>État : <?php echo ($salle['etat'] ? 'Fonctionnelle' : 'Non fonctionnelle'); ?></p>

  <h3>Équipements</h3>
  <?php if (count($equipements) > 0) : ?>
    <table>
      <thead>
        <tr>
          <th>Type</th>
          <th>Description</th>
          <th>État</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($equipements as $equipement) : ?>
          <tr>
            <td><?php echo $equipement['type']; ?></td>
            <td><?php echo $equipement['description']; ?></td>
            <td class="<?php echo ($equipement['etat'] ? 'good' : 'bad'); ?>"><?php echo ($equipement['etat'] ? 'Fonctionnel' : 'Non fonctionnel'); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
 <?php else : ?>
    <p>Aucun équipement dans cette salle.</p>
  <?php endif; ?>

  <form method="post" action="">
    <input type="submit" name="modifier_salle" value="Modifier la salle">
  </form>
</body>
</html>
