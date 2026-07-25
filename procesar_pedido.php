<?php
require_once 'Pedido.php';

session_start();

$errores = [];
$pedidoObjeto = null;

// Medida de protección complementaria de la Semana 5: Validar consistencia de identidad
if (!isset($_SESSION['user_agent']) || $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recuperar los nuevos campos obligatorios del comprador añadidos en el rediseño UI/UX
    $cliente_nombre = !empty($_POST['cliente_nombre']) ? trim($_POST['cliente_nombre']) : null;
    $cliente_rut = !empty($_POST['cliente_rut']) ? trim($_POST['cliente_rut']) : null;
    
    $tipo_pedido = !empty($_POST['tipo_pedido']) ? trim($_POST['tipo_pedido']) : null;
    $descripcion = !empty($_POST['descripcion']) ? trim($_POST['descripcion']) : null;
    $direccion_entrega = !empty($_POST['direccion_entrega']) ? trim($_POST['direccion_entrega']) : null;
    $observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';

    // SOLUCIÓN AL ERROR: Extraer el volumen global de artículos directamente desde el array de sesión PHP
    $totalUnidadesSesion = !empty($_SESSION['carrito']) ? array_sum($_SESSION['carrito']) : 0;

    // Validaciones del lado del servidor adaptadas a la persistencia del carrito
    if ($totalUnidadesSesion <= 0) {
        $errores[] = "Debe especificar un artículo técnico agregándolo previamente a su carrito.";
        $errores[] = "La cantidad debe ser de al menos 1 unidad.";
    }
    if (empty($cliente_nombre)) $errores[] = "El nombre del comprador es un parámetro obligatorio.";
    if (empty($cliente_rut)) $errores[] = "El RUT del cliente es mandatorio para el registro transaccional.";
    if (empty($descripcion)) $errores[] = "La descripción del pedido es obligatoria según la rúbrica.";
    if (empty($direccion_entrega)) $errores[] = "La dirección de entrega es mandatoria para el despacho logístico.";

    if (empty($errores)) {
        // Estructuración del detalle unificando la identidad del cliente en el objeto pedido
        $detalleArticulo = "Checkout de: " . $cliente_nombre . " (RUT: " . $cliente_rut . ") - Compra Segura mediante Sesión.";
        
        // Instanciación formal del Objeto Pedido mediante operador 'new' (POO)
        $pedidoObjeto = new Pedido($descripcion, $tipo_pedido, $detalleArticulo, $totalUnidadesSesion, $observaciones, $direccion_entrega);
        
        // Cambio de estado dinámico que valida la transacción
        $pedidoObjeto->setEstadoEnvio("Pago Validado de Forma Segura y Orden en Ruta");
        
        // Al completar la transacción con éxito, procedemos a limpiar el carrito de compras de la sesión
        $_SESSION['carrito'] = [];
        session_regenerate_id(true); // Cambiar ID de sesión para mitigar Session Hijacking
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Pedido Seguro</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f6f9; color: #333; padding: 40px; margin: 0; }
        .container { background: white; max-width: 650px; margin: 40px auto; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; }
        h2 { color: #2c3e50; border-bottom: 2px solid #2ecc71; padding-bottom: 10px; margin-top: 0; }
        .data-grid { margin-top: 20px; text-align: left; background: #fafafa; padding: 15px; border-radius: 6px; border: 1px solid #eee; }
        .data-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed #ddd; font-size: 14px; }
        .data-row:last-child { border-bottom: none; }
        .label { font-weight: bold; color: #7f8c8d; }
        .badge { background: #2ecc71; color: white; padding: 4px 12px; border-radius: 12px; font-size: 13px; font-weight: bold; }
        .error-box { background: #f8d7da; color: #721c24; padding: 20px; border-radius: 6px; border-left: 5px solid #dc3545; text-align: left; }
        .btn-back { display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #3498db; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <?php if ($pedidoObjeto): ?>
        <h2>✔ Transacción Completada de Forma Segura</h2>
        <p>Los parámetros HTTP POST e internos de sesión instanciaron correctamente las propiedades:</p>
        
        <div class="data-grid">
            <div class="data-row">
                <span class="label">Información del Comprador:</span>
                <span style="color: #2c3e50; font-weight: bold; text-align: right;"><?php echo htmlspecialchars($pedidoObjeto->getProducto()); ?></span>
            </div>
            <div class="data-row">
                <span class="label">Total Unidades Adquiridas:</span>
                <span><?php echo $pedidoObjeto->getUnidades(); ?> ítems</span>
            </div>
            <div class="data-row">
                <span class="label">Tipo de Pedido:</span>
                <span><?php echo htmlspecialchars($pedidoObjeto->getTipoPedido()); ?></span>
            </div>
            <div class="data-row">
                <span class="label">Descripción del Pedido:</span>
                <span><?php echo htmlspecialchars($pedidoObjeto->getDescripcion()); ?></span>
            </div>
            <div class="data-row">
                <span class="label">Dirección de Entrega:</span>
                <span style="color: #2980b9; font-weight: bold;"><?php echo htmlspecialchars($pedidoObjeto->getDireccionEntrega()); ?></span>
            </div>
            <div class="data-row">
                <span class="label">Observaciones:</span>
                <span><?php echo htmlspecialchars($pedidoObjeto->getObservaciones()) ?: 'Sin especificaciones'; ?></span>
            </div>
            <div class="data-row" style="margin-top: 15px; background: #e8f8f0; padding: 12px; border-radius: 4px;">
                <span class="label" style="color: #27ae60;">Estado de Gestión de Sesión:</span>
                <span class="badge"><?php echo htmlspecialchars($pedidoObjeto->getEstadoEnvio()); ?></span>
            </div>
        </div>
        
        <a href="index.php" class="btn-back">Regresar al Catálogo</a>
        
    <?php else: ?>
        <div class="error-box">
            <h3 style="margin-top:0;">⚠ Error Crítico en el Checkout</h3>
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="index.php" class="btn-back" style="background:#dc3545;">Volver al Catálogo</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>