<?php
/**
 * Módulo de Gestión de Pedidos y Filtros
 * Implementa la separación de lógica para el catálogo y la validación de montos.
 */
class GestionPedidos {
    private array $catalogo;

    public function __construct(array $catalogo) {
        $this->catalogo = $catalogo;
    }

    /**
     * Filtra los productos por término de búsqueda normalizado
     */
    public function filtrarProductos(string $criterio): array {
        $termino = strtolower(trim($criterio));
        if (empty($termino)) {
            return $this->catalogo;
        }

        return array_filter($this->catalogo, function($producto) use ($termino) {
            return strpos(strtolower($producto['nombre']), $termino) !== false ||
                   strpos(strtolower($producto['categoria']), $termino) !== false;
        });
    }

    /**
     * Calcula el subtotal e IVA (19%) para el carrito de compras en sesión
     */
    public function calcularResumenCarrito(array $carritoSesion): array {
        $subtotal = 0;
        foreach ($carritoSesion as $id => $cantidad) {
            if (isset($this->catalogo[$id])) {
                $subtotal += $this->catalogo[$id]['precio'] * $cantidad;
            }
        }
        $iva = $subtotal * 0.19;
        return [
            'subtotal' => $subtotal,
            'iva' => $iva,
            'total' => $subtotal + $iva
        ];
    }
}
?>
