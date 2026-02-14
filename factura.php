<?php 
    include 'cabecera.php'; 

    
    //Eliminamos las etiquetas de apertura y cierre de body y html porque ya se encuentran en la cabecera y el footer


    //Métodos de seguridad
    //Inicio de sesión del usuario, si no ha iniciado sesión, te redirige a login. Impide que usuarios no logueados puedan acceder
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit();
    }


    //Validar que se ha proporcionado un ID por la URL
    //Esto evita que la página intente hacer consultas a la base de datos sin un número de factura
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        header("Location: perfil.php?error=id_ausente");
        exit();
    }




    $factura_id = $_GET['id'];
    $usuario_id = $_SESSION['usuario_id'];
    $conexion = mysqli_connect("localhost", "root", "", "ytronhosting");

    //Muestra los datos de la tabla factura (con una sentencia preparada para evitar inyeccioones SQL)
    $sql_f = "SELECT * FROM factura WHERE id = ? AND usuario_id = ?";
    $stmt_f = mysqli_prepare($conexion, $sql_f);
    mysqli_stmt_bind_param($stmt_f, "ii", $factura_id, $usuario_id);
    mysqli_stmt_execute($stmt_f);
    $res_f = mysqli_stmt_get_result($stmt_f);
    $factura = mysqli_fetch_assoc($res_f);


    //Si se intenta acceder a una factura de otro usuario, muestra el siguiente error por una pantalla emergente
    if (!$factura) {
        header("Location: perfil.php?error=acceso_denegado");   //Se redirige al usuario a perfil.php junto con la URL "?error=acceso_denegado"
        exit();
    }


    //Muestra los datos da la tabla líneas de la factura (con una sentencia preparada para evitar inyeccioones SQL)
    $sql_l = "SELECT * FROM linea WHERE factura_id = ?";
    $stmt_l = mysqli_prepare($conexion, $sql_l);
    mysqli_stmt_bind_param($stmt_l, "i", $factura_id);
    mysqli_stmt_execute($stmt_l);
    $lineas = mysqli_stmt_get_result($stmt_l);
?>

<div class="container py-5">
    <div class="card shadow-lg p-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="text-primary fw-bold">Ytron Hosting</h1>
                <p class="text-muted">Factura #<?php echo $factura['id']; ?></p>
            </div>
            <div class="text-end">
                <span class="badge <?php echo $factura['pagada'] ? 'bg-success' : 'bg-warning text-dark'; ?> mb-2">
                    <?php echo $factura['pagada'] ? 'PAGADA' : 'PENDIENTE DE PAGO'; ?>
                </span>
                <p class="mb-0">Vence: <strong><?php echo date('d/m/Y', strtotime($factura['fecha_vencimiento'])); ?></strong></p>
            </div>
        </div>

        <hr>

        <table class="table table-borderless mt-4">
            <thead class="table-light">
                <tr>
                    <th>Concepto</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-end">Precio Ud.</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_factura = 0;
                while ($l = mysqli_fetch_assoc($lineas)): 
                    $subtotal = $l['cantidad'] * $l['precio_ud'];
                    $total_factura += $subtotal;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($l['concepto']); ?></td>
                    <td class="text-center"><?php echo $l['cantidad']; ?></td>
                    <td class="text-end"><?php echo number_format($l['precio_ud'], 2); ?> €</td>
                    <td class="text-end fw-bold"><?php echo number_format($subtotal, 2); ?> €</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr class="border-top">
                    <td colspan="3" class="text-end h4">Total Pagado:</td>
                    <td class="text-end h4 text-primary"><?php echo number_format($total_factura, 2); ?> €</td>
                </tr>
            </tfoot>
        </table>

        <div class="mt-5 d-print-none text-center">
            <button onclick="window.print();" class="btn btn-outline-dark me-2 factura-boton">
                <i class="bi bi-printer"></i> Imprimir / Guardar PDF
            </button>
            <a href="perfil.php" class="btn btn-primary factura-boton">Volver a mi Perfil</a>
        </div>
    </div>
</div>

<?php 
    include 'footer.php'; 
?>