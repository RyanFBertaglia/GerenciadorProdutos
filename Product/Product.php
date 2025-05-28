<?php

namespace Product;

class Produto {
    private $idProduct;
    private $price;
    private $description;
    private $supplier;
    private $stock;
    private $image;

    public function __construct($idProduct, $price, $description, $supplier, $stock, $image) {
        $this->idProduct = $idProduct;
        $this->price = $price;
        $this->description = $description;
        $this->supplier = $supplier;
        $this->stock = $stock;
        $this->image = $image;
    }

    public function getIdProduct() {
        return $this->idProduct;
    }

    public function getPrice() {
        return number_format($this->price, 2, ',', '.');
    }

    public function getDescription() {
        return $this->description;
    }

    public function getSupplier() {
        return $this->supplier;
    }

    public function getStock() {
        return $this->stock;
    }

    public function getImage() {
        return $this->image;
    }

    // Método para verificar disponibilidade
    public function isAvailable() {
        return $this->stock > 0;
    }
}
?>
