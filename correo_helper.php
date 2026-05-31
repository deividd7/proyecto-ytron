<?php
/**
 * correo_helper.php
 * Motor de notificaciones transaccionales.
 * Envía emails HTML con plantillas.
 */

function enviar_correo_ytron($destinatario, $asunto, $tipo_plantilla, $datos = []) {
    // configura cabeceras mime html
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Ytron Hosting <noreply@ytronhosting.com>\r\n";
    $headers .= "Reply-To: support@ytronhosting.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // diseno plantillas base
    $logo_url = "https://via.placeholder.com/150x40/0a0a0f/00e5a0?text=YTRON+HOSTING"; 
    $base_html_start = "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #050508; margin: 0; padding: 0; color: #c5c8d4; }
            .container { max-width: 600px; margin: 40px auto; background-color: #12121c; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
            .header { background: linear-gradient(135deg, #0a0a0f, #12121c); padding: 30px; text-align: center; border-bottom: 1px solid rgba(0, 212, 255, 0.2); }
            .content { padding: 40px; }
            .footer { background-color: #0a0a0f; padding: 20px; text-align: center; font-size: 12px; color: #6c757d; border-top: 1px solid rgba(255,255,255,0.05); }
            h1 { color: #ffffff; margin-top: 0; }
            p { line-height: 1.6; }
            .btn { display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #00d4ff, #00e5a0); color: #000000; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; }
            .highlight { color: #00e5a0; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <img src='{$logo_url}' alt='Ytron Hosting' style='max-height: 40px;'>
            </div>
            <div class='content'>";

    $base_html_end = "
            </div>
            <div class='footer'>
                <p>Este es un correo automático. Por favor, no respondas a este mensaje.</p>
                <p>&copy; " . date('Y') . " Ytron Hosting. Todos los derechos reservados.</p>
            </div>
        </div>
    </body>
    </html>";

    $cuerpo = "";

    switch ($tipo_plantilla) {
        case 'bienvenida':
            $nombre = htmlspecialchars($datos['nombre'] ?? 'Cliente');
            $cuerpo = "
                <h1>¡Bienvenido a la Élite, {$nombre}!</h1>
                <p>Tu cuenta premium en <strong>Ytron Hosting</strong> ha sido creada con éxito.</p>
                <p>Estás a un paso de experimentar nuestra infraestructura Zero-Touch impulsada por AMD Ryzen y NVMe RAID 10. Prepárate para el máximo rendimiento.</p>
                <center>
                    <a href='http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/login.php' class='btn'>Acceder al Panel</a>
                </center>
                <p>Si necesitas asistencia técnica, nuestro equipo de Soporte 24/7 está siempre disponible en la sección de Contacto o a través de nuestra comunidad de Discord.</p>";
            break;

        case 'recuperacion':
            $token = $datos['token'];
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            // genera url dinamica base
            $base_url = "http://" . $host . rtrim(dirname($_SERVER['PHP_SELF']), '/');
            $reset_link = $base_url . "/resetear_password.php?token=" . urlencode($token);
            $cuerpo = "
                <h1>Recuperación de Contraseña</h1>
                <p>Hemos recibido una solicitud para restablecer tu contraseña en Ytron Hosting.</p>
                <p>Haz clic en el botón inferior para establecer una nueva contraseña. Por motivos de seguridad, este enlace expirará en <span class='highlight'>30 minutos</span>.</p>
                <center>
                    <a href='{$reset_link}' class='btn'>Restablecer Contraseña</a>
                </center>
                <p>Si no has solicitado este cambio, por favor ignora este correo. Tu cuenta sigue siendo segura.</p>";
            break;

        case 'factura_compra':
            $plan_nombre = htmlspecialchars($datos['plan_nombre']);
            $factura_id = htmlspecialchars($datos['factura_id']);
            $precio = number_format($datos['precio'], 2);
            $cuerpo = "
                <h1>Despliegue Iniciado <span style='color: #00d4ff;'>#FA-{$factura_id}</span></h1>
                <p>Hemos procesado tu pago de <span class='highlight'>{$precio} €</span> y tu servidor está en fase de despliegue Zero-Touch.</p>
                <div style='background: rgba(0,0,0,0.2); padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 3px solid #00d4ff;'>
                    <h3 style='margin-top: 0; color: #fff;'>Detalles del Plan</h3>
                    <p style='margin: 0;'><strong>Plan:</strong> {$plan_nombre}</p>
                    <p style='margin: 0; margin-top: 5px;'><strong>Estado:</strong> Provisionando contenedor en Clúster HA...</p>
                </div>
                <p>En menos de 60 segundos tu servidor estará completamente operativo. Puedes consultar su estado e IP en tu panel de control.</p>
                <center>
                    <a href='http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/mis_servidores.php' class='btn'>Ver Mis Servidores</a>
                </center>";
            break;

        default:
            $cuerpo = "<p>Mensaje genérico del sistema.</p>";
            break;
    }

    $mensaje_html = $base_html_start . $cuerpo . $base_html_end;

    // ejecuta funcion mail php
    return @mail($destinatario, $asunto, $mensaje_html, $headers);
}
?>
