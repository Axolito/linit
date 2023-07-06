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

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nomType = $_POST['nom_type'];

  // Insérer le nouveau type d'équipement dans la base de données
  try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("INSERT INTO types_equipements (nom) VALUES (:nom)");
    $stmt->bindParam(':nom', $nomType);
    $stmt->execute();

    echo 'Le type d\'équipement a été créé avec succès !';
  } catch(PDOException $e) {
    echo 'Erreur de connexion à la base de données : ' . $e->getMessage();
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Créer un type d'équipement</title>
</head>
<body>
  <a href="gestion_salles.php">Accueil</a>
  <h2>Créer un type d'équipement</h2>
  <form method="post" action="">
    <div>
      <label for="nom_type">Nom :</label>
      <input type="text" id="nom_type" name="nom_type" required>
    </div>
    <div>
      <input type="submit" value="Créer">
    </div>
  </form>
</body>
</html>
