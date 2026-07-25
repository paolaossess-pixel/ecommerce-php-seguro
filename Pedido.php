<?php
/**
 * Clase Pedido que representa la abstracción de una orden de compra
 * e incorpora métodos de gestión y búsqueda personalizada.
 */
class Pedido {
    private string $descripcion;
    private string $tipoPedido;
    private string $producto;
    private int $unidades;
    private string $observaciones;
    private string $direccionEntrega; 
    private string $estadoEnvio;

    public function __construct(string $descripcion, string $tipoPedido, string $producto, int $unidades, string $observaciones, string $direccionEntrega) {
        $this->descripcion = $descripcion;
        $this->tipoPedido = $tipoPedido;
        $this->producto = $producto;
        $this->unidades = $unidades;
        $this->observaciones = $observaciones;
        $this->direccionEntrega = $direccionEntrega;
        $this->estadoEnvio = "En Preparación"; 
    }

    public function getDescripcion(): string { return $this->descripcion; }
    public function getTipoPedido(): string { return $this->tipoPedido; }
    public function getProducto(): string { return $this->producto; }
    public function getUnidades(): int { return $this->unidades; }
    public function getObservaciones(): string { return $this->observaciones; }
    public function getDireccionEntrega(): string { return $this->direccionEntrega; }
    public function getEstadoEnvio(): string { return $this->estadoEnvio; }

    public function setEstadoEnvio(string $nuevoEstado): void {
        $this->estadoEnvio = $nuevoEstado;
    }

    public function coincideBusqueda(string $criterio): bool {
        $termino = strtolower(trim($criterio));
        return (strpos(strtolower($this->producto), $termino) !== false) || 
               (strpos(strtolower($this->descripcion), $termino) !== false) ||
               (strpos(strtolower($this->direccionEntrega), $termino) !== false);
    }
}
?>