<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Contact;

class ContactoController extends Controller
{
    private Contact $model;

    public function __construct()
    {
        $this->model = new Contact();
    }

    public function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Construir redirección relativa al proyecto (evita /html/... en localhost)
            $base = rtrim(dirname(dirname(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))), '/');
            $target = $base . '/html/contacto.php';
            header('Location: ' . $target, true, 303);
            exit;
        }

        // Aceptar formulario (application/x-www-form-urlencoded) por compatibilidad
        $data = $_POST;
        $ok = $this->model->create($data);

        // Detección de solicitudes AJAX/JSON para responder primero y enviar correo después
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $xhr = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $wantsJson = $xhr || stripos($accept, 'application/json') !== false;

        if ($wantsJson) {
            // Responder inmediatamente al cliente
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => (bool)$ok,
                'message' => $ok
                    ? '¡Tu mensaje fue enviado correctamente!'
                    : 'Hubo un problema al enviar tu mensaje. Inténtalo de nuevo.'
            ], JSON_UNESCAPED_UNICODE);

            // Intentar finalizar la respuesta y continuar en background
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            } else {
                @ob_end_flush();
                @flush();
            }

            // Enviar correo en background (no bloquear la respuesta)
            $this->enviarCorreo($ok, $data);
            return; // Evitar continuar al bloque de redirección
        }

        // Flujo normal (no AJAX): enviar correo y luego redirigir
        $this->enviarCorreo($ok, $data);

        // Construir URL base del proyecto a partir de la ruta actual
        $base = rtrim(dirname(dirname(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))), '/');
        $target = $base . '/html/contacto.php?enviado=' . ($ok ? '1' : '0');
        // Redirigir de vuelta con mensaje de estado
        header('Location: ' . $target, true, 303);
        exit;
    }
    
    private function enviarCorreo(bool $ok, array $data): void
    {
        // Envío de correo informativo (opcional) con soporte para CC vía campo oculto `_cc`
        try {
            $to = 'alaskaproyectosena@gmail.com';
            $nombre = trim($data['nombre-contacto'] ?? '');
            $email = trim($data['email-contacto'] ?? '');
            $telefono = trim($data['telefono-contacto'] ?? '');
            $asunto = trim($data['asunto-contacto'] ?? '');
            $mensaje = trim($data['mensaje-contacto'] ?? '');
            $ccRaw = trim($data['_cc'] ?? '');

            if ($ok && $to && $asunto && $mensaje) {
                $subject = '[Contacto] ' . $asunto;
                $bodyLines = [
                    'Has recibido un nuevo mensaje desde el formulario de contacto:',
                    '',
                    'Nombre: ' . $nombre,
                    'Email: ' . $email,
                    'Teléfono: ' . $telefono,
                    '',
                    'Mensaje:',
                    $mensaje,
                ];
                $body = implode("\r\n", $bodyLines);

                $headers = [];
                $headers[] = 'MIME-Version: 1.0';
                $headers[] = 'Content-Type: text/plain; charset=UTF-8';
                // Usar un From fijo que coincida con la cuenta autenticada en SMTP/sendmail
                $headers[] = 'From: Alaska - Formulario <alaskaproyectosena@gmail.com>';
                // Usar el correo del usuario como Reply-To para poder responderle directamente
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $headers[] = 'Reply-To: ' . $email;
                }

                // Soporte para uno o varios CC separados por coma
                if ($ccRaw !== '') {
                    $ccs = array_filter(array_map('trim', explode(',', $ccRaw)));
                    $validCcs = array_values(array_filter($ccs, function ($addr) {
                        return filter_var($addr, FILTER_VALIDATE_EMAIL);
                    }));
                    if (!empty($validCcs)) {
                        $headers[] = 'Cc: ' . implode(', ', $validCcs);
                    }
                }

                // Ensamblar cabeceras con CRLF (Windows-friendly)
                $headersStr = implode("\r\n", $headers);

                // Enviar correo y registrar diagnóstico si falla (error_log)
                $sent = mail($to, $subject, $body, $headersStr);
                if (!$sent) {
                    error_log('[Contacto] mail() falló. To=' . $to . ' Subject=' . $subject . ' Headers=' . str_replace("\r\n", ' | ', $headersStr));
                }
            }
        } catch (\Throwable $e) {
            // Silenciar errores de correo para no romper el flujo del formulario
        }
    }
}
