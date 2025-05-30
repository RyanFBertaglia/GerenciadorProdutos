<?php
class Product {
    private $id, $price, $description, $supplier, $stock, $image;

    public function __construct($id, $price, $description, $supplier, $stock, $image) {
        $this->id = $id;
        $this->price = $price;
        $this->description = $description;
        $this->supplier = $supplier;
        $this->stock = $stock;
        $this->image = $image;
    }

    public function getImage() {
        return $this->image;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getSupplier() {
        return $this->supplier;
    }

    public function getPrice() {
        return $this->price;
    }

    public function getStock() {
        return $this->stock;
    }

    public function isAvailable() {
        return $this->stock > 0;
    }
}
?>
