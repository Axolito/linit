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

// Récupérer les salles depuis la base de données
try {
  $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $stmt = $conn->query("SELECT * FROM salles ORDER BY nom");
  $salles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
  echo 'Erreur de connexion à la base de données : ' . $e->getMessage();
}

// Déconnexion de l'utilisateur
if (isset($_GET['logout'])) {
  session_destroy();
  header('Location: login.php');
  exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Gestion des salles</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h2>Gestion des salles</h2>
  
  <a href="?logout=true">Déconnexion</a>

  <h3>Création</h3>

  <a href="creer_type_equipement.php">Créer un équipement</a>
  <a href="creer_salle.php">Créer une salle</a>

  <h3>Liste des salles</h3>

  <?php if (count($salles) > 0) : ?>
    <table>
      <thead>
        <tr>
          <th>Nom</th>
          <th>Description</th>
          <th>État</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($salles as $salle) : ?>
          <tr>
            <td><?php echo $salle['nom']; ?></td>
            <td><?php echo $salle['description']; ?></td>
            <td class="<?php echo ($salle['etat'] ? 'good' : 'bad'); ?>"><?php echo ($salle['etat'] ? 'Fonctionnelle' : 'Non fonctionnelle'); ?></td>
            <td><a href="consulter_salle.php?id=<?php echo $salle['id']; ?>">Consulter</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else : ?>
    <p>Aucune salle à afficher.</p>
  <?php endif; ?>

</body>
</html>
