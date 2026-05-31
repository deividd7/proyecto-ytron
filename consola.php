<?php
/**
 * Interfaz de consola web.
 * Se conecta vía SSH a contenedores Docker.
 */
    include 'cabecera.php';

    // protege sesion estrictamente
    if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit();
    }

    $server_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
    if (!$server_id) {
        header("Location: mis_servidores.php");
        exit();
    }

    require_once __DIR__ . '/db_conexion.php';
    $conexion = getDbConnection();

    // verifica estado y propiedad
    if (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1) {
        $sql = "SELECT s.id, s.estado, p.nombre as plan_nombre FROM servidor s JOIN plan p ON s.plan_id = p.id WHERE s.id = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "i", $server_id);
    } else {
        $sql = "SELECT s.id, s.estado, p.nombre as plan_nombre FROM servidor s JOIN plan p ON s.plan_id = p.id WHERE s.id = ? AND s.usuario_id = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $server_id, $_SESSION['usuario_id']);
    }
    
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $servidor = mysqli_fetch_assoc($resultado);

    if (!$servidor || $servidor['estado'] !== 'activo') {
        // rechaza consola servidor inactivo
        header("Location: mis_servidores.php?error=consola_denegada");
        exit();
    }
    mysqli_stmt_close($stmt);
    mysqli_close($conexion);
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-white"><i class="bi bi-terminal text-success"></i> Consola del Servidor: srv_<?php echo $server_id; ?></h2>
            <p class="text-muted mb-0">Plan Activo: <span class="badge bg-primary"><?php echo htmlspecialchars($servidor['plan_nombre']); ?></span> | Terminal Segura SSH-Docker</p>
        </div>
        <a href="mis_servidores.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Volver al Panel</a>
    </div>

    <div class="card bg-black text-white p-3 shadow-lg border-0 yt-console-container">
        <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-2 mb-3">
            <div class="d-flex gap-2">
                <span class="rounded-circle bg-danger d-inline-block yt-mac-btn"></span>
                <span class="rounded-circle bg-warning d-inline-block yt-mac-btn"></span>
                <span class="rounded-circle bg-success d-inline-block yt-mac-btn"></span>
            </div>
            <span class="text-muted small">www-data@yt-mgmt → ssh → ytronadm@yt-node-01 (srv_<?php echo $server_id; ?>)</span>
        </div>

        <div id="output-consola" class="overflow-auto bg-dark p-3 text-success mb-3 yt-console-output">
            <span class="text-warning">[YTRON] Conectando con el contenedor srv_<?php echo $server_id; ?> a través del túnel SSH...</span><br>
        </div>

        <form id="form-comando" class="input-group">
            <span class="input-group-text bg-secondary text-white border-0 font-monospace">></span>
            <input type="text" id="input-cmd" class="form-control bg-dark text-white border-0 font-monospace" placeholder="Escribe un comando (ej: help, list, say Hola, time set day)..." autocomplete="off" required>
            <button class="btn btn-success" type="submit" id="btn-enviar"><i class="bi bi-send"></i> Enviar</button>
        </form>
    </div>

    <div class="mt-3 text-center">
        <small class="text-muted">
            <i class="bi bi-shield-check"></i> Los comandos se ejecutan vía <strong>rcon-cli</strong> dentro del contenedor Docker. 
            La conexión pasa por un túnel SSH cifrado entre la DMZ y la red GAME.
            Los logs se actualizan automáticamente cada 3 segundos.
        </small>
    </div>
</div>

<script>
    const SERVER_ID = <?php echo $server_id; ?>;
    const outputBox = document.getElementById('output-consola');
    let pollingActivo = true;
    let ultimoLog = "";

    function cargarLogs() {
        if (!pollingActivo) return;

        fetch('api_consola.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${SERVER_ID}&accion=leer`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.logs) {
                const logsUnidos = data.logs.join('\n');
                // evita parpadeos al actualizar
                if (logsUnidos !== ultimoLog) {
                    ultimoLog = logsUnidos;
                    let html = '';
                    data.logs.forEach(linea => {
                        let clase = 'text-success';
                        if (linea.includes('WARN')) clase = 'text-warning';
                        else if (linea.includes('ERROR') || linea.includes('FATAL')) clase = 'text-danger';
                        else if (linea.includes('INFO')) clase = 'text-info';
                        html += `<span class="${clase}">${escapeHtml(linea)}</span><br>`;
                    });
                    
                    // mantiene historial comandos visible
                    const historialComandos = document.getElementById('historial-comandos')?.innerHTML || '';
                    
                    outputBox.innerHTML = html + `<div id="historial-comandos" class="mt-3 border-top border-secondary pt-2">${historialComandos}</div>`;
                    outputBox.scrollTop = outputBox.scrollHeight;
                }
            }
        });
    }

    document.getElementById('form-comando').addEventListener('submit', function(e) {
        e.preventDefault();
        const input = document.getElementById('input-cmd');
        const comando = input.value.trim();
        if (!comando) return;

        const btnEnviar = document.getElementById('btn-enviar');
        btnEnviar.disabled = true;
        input.value = '';

        // muestra comando enviado
        let divHistorial = document.getElementById('historial-comandos');
        if(!divHistorial) {
            outputBox.innerHTML += `<div id="historial-comandos" class="mt-3 border-top border-secondary pt-2"></div>`;
            divHistorial = document.getElementById('historial-comandos');
        }
        
        divHistorial.innerHTML += `<span class="text-white fw-bold">admin@srv_${SERVER_ID}:~$ ${escapeHtml(comando)}</span><br>`;
        outputBox.scrollTop = outputBox.scrollHeight;

        fetch('api_consola.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${SERVER_ID}&accion=comando&comando=${encodeURIComponent(comando)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.output && data.output.length > 0) {
                data.output.forEach(linea => {
                    divHistorial.innerHTML += `<span class="text-cyan fw-bold" style="color: #00d4ff;">[Respuesta RCON] ${escapeHtml(linea)}</span><br>`;
                });
            } else if (data.status === 'success') {
                divHistorial.innerHTML += `<span class="text-cyan fw-bold" style="color: #00d4ff;">[Respuesta RCON] Comando ejecutado (sin salida).</span><br>`;
            }
            if (data.status === 'error') {
                divHistorial.innerHTML += `<span class="text-danger">[ERROR] ${escapeHtml(data.message)}</span><br>`;
            }
            outputBox.scrollTop = outputBox.scrollHeight;
            cargarLogs(); // recarga logs instantaneamente
        })
        .finally(() => {
            btnEnviar.disabled = false;
            input.focus();
        });
    });

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    cargarLogs();
    setInterval(cargarLogs, 3000);

    document.addEventListener('visibilitychange', () => {
        pollingActivo = !document.hidden;
        if (pollingActivo) cargarLogs();
    });
</script>

<?php 
    include 'footer.php'; 
?>