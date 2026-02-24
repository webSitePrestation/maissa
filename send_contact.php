<?php
/**
 * send_contact.php — Maîtresse Maissa
 * Traitement du formulaire de contact via PHPMailer
 * 
 * Prérequis : installer PHPMailer via Composer
 *   composer require phpmailer/phpmailer
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'vendor/autoload.php';

/* ─── Headers JSON ─── */
header('Content-Type: application/json; charset=utf-8');

/* ─── Vérification méthode POST ─── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

/* ─── Récupération & nettoyage des champs ─── */
$prenom      = trim(strip_tags($_POST['prenom']     ?? ''));
$contact     = trim(strip_tags($_POST['contact']    ?? ''));
$typeSeance  = trim(strip_tags($_POST['type-seance'] ?? ''));
$message     = trim(strip_tags($_POST['message']    ?? ''));
$respect     = isset($_POST['respect']) ? true : false;

/* ─── Validation serveur ─── */
$errors = [];

if (empty($prenom))     $errors[] = 'Le prénom ou pseudo est requis.';
if (empty($contact))    $errors[] = 'Le contact est requis.';
if (empty($typeSeance)) $errors[] = 'Le type de séance est requis.';
if (empty($message))    $errors[] = 'Le message est requis.';
if (!$respect)          $errors[] = 'Vous devez accepter les conditions.';

$typesAutorisés = ['voiture', 'airbnb'];
if (!empty($typeSeance) && !in_array($typeSeance, $typesAutorisés)) {
    $errors[] = 'Type de séance invalide.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

/* ─── Configuration SMTP ─── */
// 👇 Remplis ces valeurs avec tes identifiants
define('SMTP_HOST',     'smtp.gmail.com');       // Ex: smtp.gmail.com / smtp.ovh.net
define('SMTP_PORT',     587);                    // 587 (TLS) ou 465 (SSL)
define('SMTP_SECURE',   PHPMailer::ENCRYPTION_STARTTLS); // ou ENCRYPTION_SMTPS pour SSL
define('SMTP_USERNAME', 'ton.email@gmail.com');  // Ton adresse email expéditrice
define('SMTP_PASSWORD', 'ton_mot_de_passe_app'); // Mot de passe ou App Password Gmail
define('MAIL_FROM',     'ton.email@gmail.com');  // Adresse expéditrice (identique à USERNAME)
define('MAIL_FROM_NAME','Site Maîtresse Maissa');
define('MAIL_TO',       'ton.email@gmail.com');  // Adresse qui reçoit les demandes
define('MAIL_TO_NAME',  'Maîtresse Maissa');

/* ─── Labels lisibles ─── */
$typeLabel = [
    'voiture' => 'Séance pieds en voiture',
    'airbnb'  => 'Séance pieds en Airbnb (occasionnel)',
];
$typeSeanceLabel = $typeLabel[$typeSeance] ?? $typeSeance;

/* ─── Corps de l'email HTML ─── */
$htmlBody = '
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <style>
    body { font-family: Georgia, serif; background:#0f0f0f; color:#f5f0eb; margin:0; padding:0; }
    .wrap { max-width:580px; margin:0 auto; padding:2rem; }
    .header { border-bottom:1px solid #c9a96e; padding-bottom:1rem; margin-bottom:1.5rem; }
    .header h1 { font-size:1.3rem; color:#c9a96e; letter-spacing:0.1em; margin:0; }
    .header p { font-size:0.75rem; color:#c97b8e; letter-spacing:0.15em; text-transform:uppercase; margin:0.3rem 0 0; }
    .field { margin-bottom:1rem; }
    .field label { display:block; font-size:0.7rem; letter-spacing:0.15em; text-transform:uppercase; color:#c9a96e; margin-bottom:0.3rem; }
    .field span { display:block; font-size:1rem; color:#f5f0eb; background:#1e1b1b; padding:0.7rem 1rem; border-left:2px solid #c9a96e; }
    .message-box { font-size:1rem; color:#f5f0eb; background:#1e1b1b; padding:1rem; border-left:2px solid #c97b8e; white-space:pre-wrap; }
    .footer { margin-top:2rem; font-size:0.7rem; color:#f5f0eb; opacity:0.4; border-top:1px solid #1e1b1b; padding-top:1rem; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="header">
      <h1>Nouvelle demande de séance</h1>
      <p>Site Maîtresse Maissa • ' . date('d/m/Y à H:i') . '</p>
    </div>
    <div class="field">
      <label>Prénom / Pseudo</label>
      <span>' . htmlspecialchars($prenom) . '</span>
    </div>
    <div class="field">
      <label>Contact (X, Telegram…)</label>
      <span>' . htmlspecialchars($contact) . '</span>
    </div>
    <div class="field">
      <label>Type de séance</label>
      <span>' . htmlspecialchars($typeSeanceLabel) . '</span>
    </div>
    <div class="field">
      <label>Message</label>
      <div class="message-box">' . htmlspecialchars($message) . '</div>
    </div>
    <div class="footer">
      Ce message a été envoyé depuis le formulaire de réservation du site Maîtresse Maissa.
    </div>
  </div>
</body>
</html>';

/* ─── Corps texte plain (fallback) ─── */
$textBody = "Nouvelle demande de séance — " . date('d/m/Y à H:i') . "\n\n"
          . "Prénom / Pseudo : {$prenom}\n"
          . "Contact         : {$contact}\n"
          . "Type de séance  : {$typeSeanceLabel}\n\n"
          . "Message :\n{$message}\n";

/* ─── Envoi avec PHPMailer ─── */
$mail = new PHPMailer(true);

try {
    // Serveur SMTP
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    // Expéditeur & destinataire
    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    $mail->addAddress(MAIL_TO, MAIL_TO_NAME);
    $mail->addReplyTo(MAIL_FROM, $prenom); // Pas d'email utilisateur, reply-to générique

    // Contenu
    $mail->isHTML(true);
    $mail->Subject = "💌 Nouvelle demande : {$typeSeanceLabel} — {$prenom}";
    $mail->Body    = $htmlBody;
    $mail->AltBody = $textBody;

    $mail->send();

    echo json_encode([
        'success' => true,
        'message' => 'Votre demande a bien été envoyée. Je vous recontacterai rapidement.'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de l\'envoi. Réessayez ou contactez-moi directement sur X.',
        // 'debug' => $mail->ErrorInfo  // ← décommenter en dev pour voir l'erreur SMTP
    ]);
}
