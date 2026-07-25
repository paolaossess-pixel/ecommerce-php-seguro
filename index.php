<?php
// Configuración avanzada de seguridad y persistencia para evitar expiración prematura (Semana 5)
ini_set('session.gc_maxlifetime', 14400);
ini_set('session.cookie_lifetime', 14400);

session_set_cookie_params([
    'lifetime' => 14400,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = []; 
}

// Medida de Seguridad: Validar Huella Digital básica (User Agent)
if (!isset($_SESSION['user_agent'])) {
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
} elseif ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

// Catálogo maestro de productos en el servidor
$productosCatalogo = [
    1 => ["nombre" => "Televisor Smart 4K", "precio" => 450000, "categoria" => "Electrónica"],
    2 => ["nombre" => "Audífonos Bluetooth", "precio" => 35000, "categoria" => "Audio"],
    3 => ["nombre" => "Teclado Mecánico RGB", "precio" => 65000, "categoria" => "Computación"],
    4 => ["nombre" => "Monitor Gamer 24 pulgadas", "precio" => 180000, "categoria" => "Computación"],
    5 => ["nombre" => "Cámara Fotográfica Reflex", "precio" => 520000, "categoria" => "Electrónica"],
    6 => ["nombre" => "Parlante Portátil Waterproof", "precio" => 45000, "categoria" => "Audio"]
];

// Sistema de ofertas flash controladas por sesión PHP
$ofertasDisponibles = [
    "🔥 ¡Oferta Flash! 10% de descuento en toda la categoría Computación usando el código COMPRASEGURA.",
    "⚡ Promoción técnica: Envío Express Gratis en Temuco por compras superiores a $100.000.",
    "🎁 Descuento especial: Agrega unos Audífonos Bluetooth y obtén un 15% de rebaja en tu próxima Orden de Compra."
];

if (!isset($_SESSION['oferta_mostrada']) || rand(1, 3) === 1) {
    $_SESSION['oferta_mostrada'] = $ofertasDisponibles[array_rand($ofertasDisponibles)];
}

$notificarProducto = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion_carrito']) && $_POST['accion_carrito'] === 'agregar') {
        $idProd = (int)$_POST['producto_id'];
        if (array_key_exists($idProd, $productosCatalogo)) {
            if (isset($_SESSION['carrito'][$idProd])) {
                $_SESSION['carrito'][$idProd]++;
            } else {
                $_SESSION['carrito'][$idProd] = 1;
            }
            session_regenerate_id(true);
            $nombreCodificado = urlencode($productosCatalogo[$idProd]['nombre']);
            header("Location: index.php?agregado=" . $nombreCodificado);
            exit;
        }
    }
    
    if (isset($_POST['accion_carrito']) && $_POST['accion_carrito'] === 'vaciar') {
        $_SESSION['carrito'] = [];
        session_regenerate_id(true);
        header("Location: index.php?vaciado=1");
        exit;
    }
}

if (isset($_GET['agregado'])) {
    $notificarProducto = htmlspecialchars($_GET['agregado']);
}
if (isset($_GET['vaciado'])) {
    $notificarProducto = "__VACIADO__";
}

$totalProductosCarrito = array_sum($_SESSION['carrito']);

$montoTotalCarrito = 0;
foreach ($_SESSION['carrito'] as $id => $cantidad) {
    if (isset($productosCatalogo[$id])) {
        $montoTotalCarrito += $productosCatalogo[$id]['precio'] * $cantidad;
    }
}

function procesarResena(string $comentario, int $estrellas): string {
    $comentarioLimpio = htmlspecialchars(stripslashes(trim($comentario)));
    $visualEstrellas = str_repeat("⭐", $estrellas);
    return "
    <div class='review-card-item'>
        <span class='review-stars'>{$visualEstrellas}</span>
        <p class='review-text'>\"{$comentarioLimpio}\"</p>
    </div>";
}

$mensajeReview = "";
$nombreProductoReview = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!empty($_POST['comentario'])) {
        $nombreProductoReview = trim($_POST['prod_nombre']);
        $mensajeReview = procesarResena($_POST['comentario'], (int)$_POST['calificacion']);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plataforma E-commerce Segura PHP</title>
    <style>
        :root {
            --primary: #2c3e50;
            --accent: #2ecc71;
            --accent-hover: #27ae60;
            --bg-main: #f8fafc;
            --card-bg: #ffffff;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        body { 
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; 
            margin: 0; padding: 0; background-color: var(--bg-main); 
            color: var(--text-dark); display: flex; flex-direction: column; min-height: 100vh; 
        }

        header { 
            background-color: var(--primary); color: #ffffff; 
            position: sticky; top: 0; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.08); 
        }
        
        .header-container {
            max-width: 1200px; margin: 0 auto;
            display: flex; justify-content: space-between; align-items: center;
            padding: 15px 20px; box-sizing: border-box;
        }

        header h1 { margin: 0; font-size: 22px; font-weight: 700; letter-spacing: -0.5px; }

        .cart-box-header { 
            background-color: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 8px 18px; border-radius: 20px; font-weight: 600; font-size: 14px;
            display: flex; align-items: center; gap: 10px; color: #ffffff; cursor: pointer;
            transition: all 0.2s ease; user-select: none;
        }
        .cart-box-header:hover { background-color: rgba(255, 255, 255, 0.2); }
        #cart-count { background: var(--accent); color: white; padding: 2px 8px; border-radius: 10px; font-size: 12px; }

        main { max-width: 1200px; margin: 0 auto; padding: 30px 20px; flex: 1; width: 100%; box-sizing: border-box; }
        
        .search-container { text-align: center; margin-bottom: 35px; }
        #search-input { 
            width: 100%; max-width: 500px; padding: 14px 20px; font-size: 15px; 
            border: 1px solid var(--border-color); border-radius: 30px; outline: none; 
            box-sizing: border-box; transition: all 0.2s ease;
        }
        #search-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.15); }
        
        .section-title { text-align: center; margin-bottom: 30px; font-size: 24px; color: var(--primary); font-weight: 700; }

        #results-container { 
            display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; margin-bottom: 50px; 
        }

        .product-card { 
            background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px; padding: 24px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: space-between;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.05); }

        .product-card h3 { margin: 12px 0 6px 0; font-size: 18px; color: var(--primary); }
        .product-card .price { font-size: 20px; font-weight: 700; color: #e74c3c; margin: 0 0 16px 0; }
        .product-card .category { font-size: 10px; color: var(--text-muted); text-transform: uppercase; background: #f1f5f9; padding: 4px 10px; border-radius: 12px; font-weight: 700; align-self: flex-start; }
        
        .btn-general { width: 100%; padding: 12px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: background 0.2s; text-align: center; box-sizing: border-box; }
        .btn-add-cart { background-color: var(--accent); color: white; }
        .btn-add-cart:hover { background-color: var(--accent-hover); }
        
        .inline-review-form { margin-top: 15px; border-top: 1px solid var(--border-color); padding-top: 12px; }
        .review-toggle { font-size: 13px; color: var(--text-muted); font-weight: 600; cursor: pointer; }
        .form-mini-control { width: 100%; padding: 8px; font-size: 13px; border-radius: 6px; border: 1px solid var(--border-color); margin-top: 8px; margin-bottom: 8px; box-sizing: border-box; }
        
        .cart-dropdown {
            display: none; position: fixed; top: 75px; right: 20px; 
            background: white; border: 1px solid var(--border-color); width: 360px;
            border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); padding: 20px; z-index: 2000;
        }
        .cart-dropdown.active { display: block; }
        .cart-item-row { display: flex; justify-content: space-between; font-size: 14px; padding: 8px 0; border-bottom: 1px dashed var(--border-color); }
        .cart-total-row { display: flex; justify-content: space-between; font-weight: 700; font-size: 16px; margin-top: 15px; padding-top: 10px; color: var(--primary); }
        
        .card-premium { 
            display: none; background: var(--card-bg); padding: 35px; border-radius: 16px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid var(--border-color);
            max-width: 650px; margin: 40px auto; border-top: 4px solid var(--accent);
        }
        .card-premium.active { display: block; }
        .card-premium h3 { margin-top: 0; color: var(--primary); font-size: 22px; font-weight: 700; margin-bottom: 20px; text-align: center; }
        
        .form-grid-two { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .form-group { margin-bottom: 18px; display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-weight: 600; color: #475569; font-size: 14px; }
        .form-control { width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid var(--border-color); box-sizing: border-box; font-size: 14px; }
        .form-control:focus { border-color: var(--accent); outline: none; }
        
        #notification-area { position: fixed; bottom: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 12px; }
        .toast-notification { background-color: var(--primary); color: #ffffff; padding: 15px 24px; border-radius: 8px; font-size: 14px; min-width: 320px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); animation: slideIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); font-weight: 500; border-left: 5px solid var(--accent); }
        .toast-promo { border-left-color: #f1c40f !important; background-color: #1e293b; }
        
        footer { background-color: var(--primary); color: #94a3b8; text-align: center; padding: 25px 0; font-size: 14px; margin-top: 50px; }
        @keyframes slideIn { from { transform: translateY(50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
</head>
<body>

    <header>
        <div class="header-container">
            <h1>E-commerce</h1>
            <div class="cart-box-header" id="cart-toggle-btn">
                <span>🛒 Carrito de Sesión</span>
                <span id="cart-count"><?php echo $totalProductosCarrito; ?></span>
            </div>
        </div>
    </header>

    <div class="cart-dropdown" id="cart-dropdown-box">
        <h4 style="margin-top:0; border-bottom: 2px solid var(--primary); padding-bottom:8px; color: var(--primary);">Resumen del Carrito (Sesión)</h4>
        <?php if ($totalProductosCarrito === 0): ?>
            <p style="color: var(--text-muted); font-size: 14px; text-align:center;">El carrito está vacío.</p>
        <?php else: ?>
            <div style="max-height: 200px; overflow-y: auto;">
                <?php foreach ($_SESSION['carrito'] as $id => $cantidad): ?>
                    <?php if (isset($productosCatalogo[$id])): $p = $productosCatalogo[$id]; ?>
                        <div class="cart-item-row">
                            <span><?php echo htmlspecialchars($p['nombre']); ?> (x<?php echo $cantidad; ?>)</span>
                            <span style="font-weight:600;">$<?php echo number_format($p['precio'] * $cantidad, 0, ',', '.'); ?></span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <div class="cart-total-row">
                <span>Total General:</span>
                <span>$<?php echo number_format($montoTotalCarrito, 0, ',', '.'); ?></span>
            </div>
            <div style="display:flex; gap:10px; margin-top:15px;">
                <form action="index.php" method="POST" style="width:50%;">
                    <input type="hidden" name="accion_carrito" value="vaciar">
                    <button type="submit" class="btn-general" style="background:#e74c3c; color:white; padding:8px;">Vaciar</button>
                </form>
                <button class="btn-general" style="background:var(--accent); color:white; width:50%; padding:8px;" onclick="irACheckout()">Pagar</button>
            </div>
        <?php endif; ?>
    </div>

    <main>
        <?php if (!empty($mensajeReview)): ?>
            <div style="background: #e8f8f0; border: 1px solid var(--accent); padding: 15px; border-radius: 12px; margin: 0 auto 30px auto; max-width: 650px;">
                <h4 style="color: var(--accent-hover); margin: 0 0 5px 0;">✅ ¡Nueva Reseña para <?php echo htmlspecialchars($nombreProductoReview); ?>!</h4>
                <?php echo $mensajeReview; ?>
            </div>
        <?php endif; ?>

        <section class="search-container">
            <input type="text" id="search-input" placeholder="🔍 Buscar por nombre de producto...">
        </section>

        <section>
            <h2 class="section-title">Catálogo de Productos Inteligente</h2>
            <div id="results-container">
                <?php foreach ($productosCatalogo as $id => $p): ?>
                    <div class="product-card" data-nombre="<?php echo htmlspecialchars(strtolower($p['nombre'])); ?>">
                        <div style="display:flex; flex-direction:column;">
                            <span class="category"><?php echo htmlspecialchars($p['categoria']); ?></span>
                            <h3><?php echo htmlspecialchars($p['nombre']); ?></h3>
                            <p class="price">$<?php echo number_format($p['precio'], 0, ',', '.'); ?></p>
                        </div>
                        <div>
                            <form action="index.php" method="POST">
                                <input type="hidden" name="accion_carrito" value="agregar">
                                <input type="hidden" name="producto_id" value="<?php echo $id; ?>">
                                <button type="submit" class="btn-general btn-add-cart">Agregar al Carrito</button>
                            </form>
                            <div class="inline-review-form">
                                <details>
                                    <summary class="review-toggle">Dejar Opinión</summary>
                                    <form action="index.php" method="POST" style="margin-top: 5px;">
                                        <input type="hidden" name="prod_nombre" value="<?php echo htmlspecialchars($p['nombre']); ?>">
                                        <select name="calificacion" class="form-mini-control" required>
                                            <option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
                                            <option value="4">⭐⭐⭐⭐ (4/5)</option>
                                            <option value="3">⭐⭐⭐ (3/5)</option>
                                            <option value="2">⭐⭐ (2/5)</option>
                                            <option value="1">⭐ (1/5)</option>
                                        </select>
                                        <textarea name="comentario" rows="2" placeholder="Tu experiencia..." class="form-mini-control" required></textarea>
                                        <button type="submit" name="submit_review" class="btn-general" style="background:#3498db; color:white; padding:8px; font-size:12px; border-radius:6px;">Publicar Reseña</button>
                                    </form>
                                </details>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="card-premium" id="checkout-section">
            <h3>Registrar Orden de Compra Seguro</h3>
            <form action="procesar_pedido.php" method="POST">
                
                <div class="form-grid-two">
                    <div class="form-group">
                        <label for="cliente_nombre">Nombre Completo Comprador:</label>
                        <input type="text" id="cliente_nombre" name="cliente_nombre" placeholder="Ej: Juan Pérez" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="cliente_rut">RUT Cliente:</label>
                        <input type="text" id="cliente_rut" name="cliente_rut" placeholder="Ej: 12.345.678-9" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Tipo / Canal de Pedido:</label>
                    <div style="display: flex; gap: 25px; font-size: 14px; padding: 6px 0;">
                        <label style="cursor:pointer;"><input type="radio" name="tipo_pedido" value="Despacho Estándar" checked> Despacho Estándar</label>
                        <label style="cursor:pointer;"><input type="radio" name="tipo_pedido" value="Retiro Express"> Retiro Express</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción / Notas del Pedido:</label>
                    <input type="text" id="descripcion" name="descripcion" placeholder="Ej: Adquisición corporativa para área técnica..." class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="direccion_entrega">Dirección de Despacho:</label>
                    <input type="text" id="direccion_entrega" name="direccion_entrega" placeholder="Ej: Av. Alemania 0211, Temuco" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="observaciones">Indicaciones Especiales de Packing:</label>
                    <textarea id="observaciones" name="observaciones" rows="2" placeholder="Ej: Envolver en plástico burbuja..." class="form-control"></textarea>
                </div>

                <button type="submit" class="btn-general" style="background: var(--accent); color:white; font-size:16px; padding:14px; border-radius: 8px;">Finalizar Pago y Generar Comprobante</button>
            </form>
        </section>
    </main>

    <div id="notification-area"></div>
    <footer>Programación Web II | Semana 5</footer>

    <script>
        const cartToggleBtn = document.getElementById("cart-toggle-btn");
        const cartDropdownBox = document.getElementById("cart-dropdown-box");
        const checkoutSection = document.getElementById("checkout-section");

        cartToggleBtn.addEventListener("click", () => {
            cartDropdownBox.classList.toggle("active");
        });

        function irACheckout() {
            cartDropdownBox.classList.remove("active");
            checkoutSection.classList.add("active");
            checkoutSection.scrollIntoView({ behavior: 'smooth' });
        }

        const searchInput = document.getElementById("search-input");
        const cards = document.querySelectorAll(".product-card");
        searchInput.addEventListener("input", (e) => {
            const query = e.target.value.toLowerCase().trim();
            cards.forEach(card => {
                const nombre = card.getAttribute("data-nombre");
                card.style.display = nombre.includes(query) ? "flex" : "none";
            });
        });

        function lanzarNotificacion(msg, esPromo = false) {
            const area = document.getElementById("notification-area");
            const toast = document.createElement("div");
            toast.className = "toast-notification";
            if (esPromo) toast.classList.add("toast-promo");
            toast.textContent = msg;
            area.appendChild(toast);
            setTimeout(() => { toast.remove(); }, 3500);
        }

        const productoCargado = "<?php echo $notificarProducto; ?>";
        if (productoCargado !== "") {
            if (productoCargado === "__VACIADO__") {
                lanzarNotificacion("🗑 El carrito de compras ha sido vaciado.");
            } else {
                lanzarNotificacion(`🛒 "${productoCargado}" agregado al carrito con éxito.`);
            }
        }

        const promocionActiva = "<?php echo isset($_SESSION['oferta_mostrada']) ? addslashes($_SESSION['oferta_mostrada']) : ''; ?>";
        if (promocionActiva !== "") {
            setTimeout(() => { lanzarNotificacion(promocionActiva, true); }, 1000);
        }
    </script>
</body>
</html>