<?php
/**
 * Handler du formulaire de contact biarritzdomiciliation.com
 * Lit la config dans .env (situé dans le même dossier que ce script).
 * Envoie un mail via SMTP authentifié avec PHPMailer.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

require __DIR__ . '/lib/PHPMailer/Exception.php';
require __DIR__ . '/lib/PHPMailer/PHPMailer.php';
require __DIR__ . '/lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

function load_env(string $path): array {
    if (!is_readable($path)) {
        return [];
    }
    $env = [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (strlen($value) >= 2 && (
            ($value[0] === '"' && $value[-1] === '"') ||
            ($value[0] === "'" && $value[-1] === "'")
        )) {
            $value = substr($value, 1, -1);
        }
        $env[$key] = $value;
    }
    return $env;
}

function fail(int $status, string $message): void {
    http_response_code($status);
    echo json_encode(['ok' => false, 'error' => $message]);
    exit;
}

// Honeypot anti-bot : champ caché que les bots remplissent automatiquement
if (!empty($_POST['website'] ?? '')) {
    echo json_encode(['ok' => true]);
    exit;
}

$nom       = trim((string)($_POST['nom']       ?? ''));
$email     = trim((string)($_POST['email']     ?? ''));
$tel       = trim((string)($_POST['tel']       ?? ''));
$structure = trim((string)($_POST['structure'] ?? ''));
$formule   = trim((string)($_POST['formule']   ?? ''));
$message   = trim((string)($_POST['message']   ?? ''));

if ($nom === '' || mb_strlen($nom) > 120) {
    fail(400, 'Nom invalide.');
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 200) {
    fail(400, 'Email invalide.');
}
if (mb_strlen($tel) > 40) {
    fail(400, 'Téléphone invalide.');
}
if (mb_strlen($structure) > 80) {
    fail(400, 'Structure invalide.');
}
if (mb_strlen($formule) > 80) {
    fail(400, 'Formule invalide.');
}
if (mb_strlen($message) > 4000) {
    fail(400, 'Message trop long.');
}

$env = load_env(__DIR__ . '/.env');
foreach (['SMTP_HOST','SMTP_PORT','SMTP_USER','SMTP_PASS','MAIL_FROM','MAIL_TO'] as $key) {
    if (empty($env[$key])) {
        error_log("send-mail.php: missing env key $key");
        fail(500, 'Configuration serveur incomplète.');
    }
}

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = $env['SMTP_HOST'];
    $mail->Port       = (int)$env['SMTP_PORT'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $env['SMTP_USER'];
    $mail->Password   = $env['SMTP_PASS'];
    $mail->SMTPSecure = ($env['SMTP_SECURE'] ?? 'tls') === 'ssl'
        ? PHPMailer::ENCRYPTION_SMTPS
        : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->CharSet    = 'UTF-8';
    $mail->Timeout    = 15;

    $mail->setFrom($env['MAIL_FROM'], $env['MAIL_FROM_NAME'] ?? 'Biarritz Domiciliation');
    $mail->addAddress($env['MAIL_TO'], $env['MAIL_TO_NAME'] ?? 'Biarritz Domiciliation');
    $mail->addReplyTo($email, $nom);

    $prefix  = $env['MAIL_SUBJECT_PREFIX'] ?? '';
    $subject = $formule !== '' ? $formule : 'Demande de domiciliation';
    $mail->Subject = $prefix . $subject . ' — ' . $nom;

    $bodyTxt  = "Nouvelle demande depuis biarritzdomiciliation.com\n\n";
    $bodyTxt .= "Nom & prénom : $nom\n";
    $bodyTxt .= "Email        : $email\n";
    $bodyTxt .= "Téléphone    : " . ($tel       !== '' ? $tel       : '(non renseigné)') . "\n";
    $bodyTxt .= "Structure    : " . ($structure !== '' ? $structure : '(non renseigné)') . "\n";
    $bodyTxt .= "Formule      : " . ($formule   !== '' ? $formule   : '(non renseigné)') . "\n";
    $bodyTxt .= "\n--- Message ---\n";
    $bodyTxt .= ($message !== '' ? $message : '(aucun message)') . "\n";
    $bodyTxt .= "\n---\n";
    $bodyTxt .= 'IP : ' . ($_SERVER['REMOTE_ADDR'] ?? 'inconnue') . "\n";
    $bodyTxt .= 'Date : ' . date('Y-m-d H:i:s') . "\n";

    $mail->isHTML(false);
    $mail->Body = $bodyTxt;

    $mail->send();
    echo json_encode(['ok' => true]);
} catch (PHPMailerException $e) {
    error_log('send-mail.php SMTP error: ' . $mail->ErrorInfo);
    fail(500, "L'envoi a échoué. Merci de réessayer ou de nous appeler au 05 59 23 93 10.");
}
