<?php
namespace App\Controller;
use App\Model\Product;

class ProductsController {

    public function __construct(private $productModel) {
        $this->productModel = $productModel;
    }

    public function addProduct() {
        $erro = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        }

    }
}

?>