<?php
session_start();

if (!isset($_POST['username']) or !isset($_POST['password'])) {
    // Rediriger vers la page de connexion
    header('Location: index.html');
    exit();
}

// Récupérer les données du formulaire de connexion
$username = $_POST['username'];
$password = $_POST['password'];

// Inclure le fichier de configuration de la base de données
require_once 'config.php';

try {
  $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Vérifier les informations d'authentification
  $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE nom_utilisateur = :username");
  $stmt->bindParam(':username', $username);
  $stmt->execute();
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user['mot_de_passe'])) {
    // Authentification réussie, enregistrer l'utilisateur dans une variable de session
    $_SESSION['utilisateur'] = $user;

    // Rediriger vers la page de gestion des salles
    header('Location: gestion_salles.php');
    exit();
  } else {
    // Afficher un message d'erreur
    echo 'Identifiant ou mot de passe incorrect.';
  }
} catch(PDOException $e) {
  echo 'Erreur de connexion à la base de données : ' . $e->getMessage();
}
?>
