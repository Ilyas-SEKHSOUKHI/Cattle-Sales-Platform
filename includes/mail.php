<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

/**
 * Envoie un email en utilisant Mailtrap (API) ou mail() comme fallback.
 * Supporte le contenu HTML via le paramètre $html.
 */
function sendMailWithFallback(string $to, string $subject, string $message, array $headers = [], string $html = ''): bool
{
    require_once __DIR__ . '/../config/smtp.php';

    if (!empty($smtpConfig['enabled']) && !empty($smtpConfig['host'])) {
        $apiKey = $smtpConfig['password'];
        if (empty($apiKey)) {
            return false;
        }

        $mailtrap = MailtrapClient::initSendingEmails(apiKey: $apiKey);

        $email = (new MailtrapEmail())
            ->from(new Address($smtpConfig['from_email'], $smtpConfig['from_name']))
            ->to(new Address($to))
            ->subject($subject)
            ->text($message)
            ->category('Ferme Tarmast');

        if ($html !== '') {
            $email->html($html);
        }

        $response = $mailtrap->send($email);
        $result = ResponseHelper::toArray($response);

        return !empty($result['success']) || !empty($result['message_ids']);
    }

    // Fallback: mail() natif PHP
    $headerString = '';
    if (!empty($headers)) {
        if (is_array($headers)) {
            $headerLines = [];
            foreach ($headers as $key => $value) {
                if (is_int($key)) {
                    $headerLines[] = $value;
                } else {
                    $headerLines[] = "$key: $value";
                }
            }
            $headerString = implode("\r\n", $headerLines);
        }
    }

    if ($html !== '') {
        $headerString .= ($headerString ? "\r\n" : '') . "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8";
        return mail($to, $subject, $html, $headerString);
    }

    return mail($to, $subject, $message, $headerString);
}

/**
 * Génère le template HTML premium pour l'email de vérification.
 */
function buildVerificationEmailHtml(string $nom, string $verifyUrl): string
{
    $escapedNom = htmlspecialchars($nom, ENT_QUOTES, 'UTF-8');
    $escapedUrl = htmlspecialchars($verifyUrl, ENT_QUOTES, 'UTF-8');

    return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Validez votre compte</title>
</head>
<body style="margin:0;padding:0;background-color:#FBF6EC;font-family:'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#FBF6EC;padding:40px 16px;">
  <tr>
    <td align="center">
      <table role="presentation" width="520" cellpadding="0" cellspacing="0" style="max-width:520px;width:100%;">
        
        <!-- Logo / Brand -->
        <tr>
          <td align="center" style="padding-bottom:28px;">
            <table role="presentation" cellpadding="0" cellspacing="0">
              <tr>
                <td style="background:#4CAF50;width:42px;height:42px;border-radius:12px;text-align:center;vertical-align:middle;">
                  <span style="font-size:22px;color:#fff;line-height:42px;">🐄</span>
                </td>
                <td style="padding-left:12px;font-size:20px;font-weight:700;color:#1B3A2B;letter-spacing:-0.3px;">
                  Ferme Tarmast
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Card principale -->
        <tr>
          <td style="background:#ffffff;border-radius:16px;border:1px solid #E3D9C2;box-shadow:0 12px 32px rgba(27,58,43,0.08);overflow:hidden;">
            
            <!-- Bandeau vert -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="background:linear-gradient(135deg,#4CAF50 0%,#3d9140 100%);padding:32px 36px 28px;text-align:center;">
                  <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 16px;">
                    <tr>
                      <td style="background:rgba(255,255,255,0.2);width:56px;height:56px;border-radius:50%;text-align:center;vertical-align:middle;">
                        <span style="font-size:28px;line-height:56px;">✉️</span>
                      </td>
                    </tr>
                  </table>
                  <h1 style="margin:0;font-size:22px;font-weight:700;color:#ffffff;line-height:1.3;">
                    Confirmez votre adresse email
                  </h1>
                  <p style="margin:8px 0 0;font-size:14px;color:rgba(255,255,255,0.85);line-height:1.5;">
                    Plus qu'une étape pour accéder à votre espace
                  </p>
                </td>
              </tr>
            </table>

            <!-- Contenu -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="padding:32px 36px 12px;">
                  <p style="margin:0 0 20px;font-size:15px;color:#2A2A25;line-height:1.65;">
                    Bonjour <strong style="color:#1B3A2B;">{$escapedNom}</strong>,
                  </p>
                  <p style="margin:0 0 20px;font-size:15px;color:#5C5B52;line-height:1.65;">
                    Merci pour votre inscription sur <strong style="color:#1B3A2B;">Ferme Tarmast</strong>. Pour activer votre compte et commencer à proposer vos offres sur notre cheptel, veuillez valider votre adresse email en cliquant sur le bouton ci-dessous :
                  </p>
                </td>
              </tr>
              
              <!-- Bouton CTA -->
              <tr>
                <td align="center" style="padding:8px 36px 28px;">
                  <table role="presentation" cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="background:#4CAF50;border-radius:10px;box-shadow:0 4px 14px rgba(76,175,80,0.35);">
                        <a href="{$escapedUrl}" target="_blank" style="display:inline-block;padding:14px 36px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;letter-spacing:0.3px;">
                          ✓&nbsp;&nbsp;Valider mon adresse email
                        </a>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>

              <!-- Lien alternatif -->
              <tr>
                <td style="padding:0 36px 28px;">
                  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F8F5ED;border-radius:10px;border:1px solid #E3D9C2;">
                    <tr>
                      <td style="padding:16px 20px;">
                        <p style="margin:0 0 8px;font-size:12px;font-weight:600;color:#5C5B52;text-transform:uppercase;letter-spacing:0.5px;">
                          Le bouton ne fonctionne pas ?
                        </p>
                        <p style="margin:0;font-size:13px;color:#4CAF50;word-break:break-all;line-height:1.5;">
                          <a href="{$escapedUrl}" style="color:#3d9140;text-decoration:underline;">{$escapedUrl}</a>
                        </p>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>

              <!-- Note de sécurité -->
              <tr>
                <td style="padding:0 36px 28px;">
                  <p style="margin:0;font-size:13px;color:#9A9889;line-height:1.55;font-style:italic;">
                    Si vous n'êtes pas à l'origine de cette inscription, vous pouvez ignorer cet email en toute sécurité.
                  </p>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="padding:28px 20px 0;text-align:center;">
            <p style="margin:0 0 6px;font-size:13px;color:#9A9889;line-height:1.5;">
              © {YEAR} Ferme Tarmast — Élevage de qualité
            </p>
            <p style="margin:0;font-size:12px;color:#B8B3A4;">
              Cet email a été envoyé automatiquement, merci de ne pas y répondre.
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
HTML;
}

/**
 * Génère le template HTML pour l'email de notification de connexion.
 */
function buildLoginNotificationEmailHtml(string $nom): string
{
    $escapedNom = htmlspecialchars($nom, ENT_QUOTES, 'UTF-8');
    $date = date('d/m/Y à H:i');

    return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion réussie</title>
</head>
<body style="margin:0;padding:0;background-color:#FBF6EC;font-family:'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#FBF6EC;padding:40px 16px;">
  <tr>
    <td align="center">
      <table role="presentation" width="520" cellpadding="0" cellspacing="0" style="max-width:520px;width:100%;">
        
        <!-- Logo -->
        <tr>
          <td align="center" style="padding-bottom:28px;">
            <table role="presentation" cellpadding="0" cellspacing="0">
              <tr>
                <td style="background:#4CAF50;width:42px;height:42px;border-radius:12px;text-align:center;vertical-align:middle;">
                  <span style="font-size:22px;color:#fff;line-height:42px;">🐄</span>
                </td>
                <td style="padding-left:12px;font-size:20px;font-weight:700;color:#1B3A2B;letter-spacing:-0.3px;">
                  Ferme Tarmast
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Card -->
        <tr>
          <td style="background:#ffffff;border-radius:16px;border:1px solid #E3D9C2;box-shadow:0 12px 32px rgba(27,58,43,0.08);padding:36px;">
            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 20px;">
              <tr>
                <td style="background:rgba(76,175,80,0.12);width:48px;height:48px;border-radius:50%;text-align:center;vertical-align:middle;">
                  <span style="font-size:24px;line-height:48px;">🔐</span>
                </td>
              </tr>
            </table>
            <h2 style="margin:0 0 16px;font-size:20px;font-weight:700;color:#1B3A2B;text-align:center;">
              Connexion réussie
            </h2>
            <p style="margin:0 0 16px;font-size:15px;color:#2A2A25;line-height:1.65;">
              Bonjour <strong style="color:#1B3A2B;">{$escapedNom}</strong>,
            </p>
            <p style="margin:0 0 16px;font-size:15px;color:#5C5B52;line-height:1.65;">
              Votre connexion à <strong>Ferme Tarmast</strong> a bien été effectuée le <strong>{$date}</strong>.
            </p>
            <p style="margin:0;font-size:13px;color:#A6512E;line-height:1.55;padding:12px 16px;background:rgba(166,81,46,0.08);border-radius:8px;border:1px solid rgba(166,81,46,0.15);">
              ⚠️ Si vous n'êtes pas à l'origine de cette connexion, veuillez contacter immédiatement l'administration.
            </p>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="padding:28px 20px 0;text-align:center;">
            <p style="margin:0 0 6px;font-size:13px;color:#9A9889;">
              © {YEAR} Ferme Tarmast — Élevage de qualité
            </p>
            <p style="margin:0;font-size:12px;color:#B8B3A4;">
              Cet email a été envoyé automatiquement, merci de ne pas y répondre.
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
HTML;
}
