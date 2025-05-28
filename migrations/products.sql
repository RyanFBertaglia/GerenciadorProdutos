CREATE TABLE produtos (
    idProduct INT AUTO_INCREMENT PRIMARY KEY,
    price DECIMAL(10, 2) NOT NULL,
    description VARCHAR(255) NOT NULL,
    supplier VARCHAR(100) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255) NOT NULL
);

-- Inserção de dados de exemplo
INSERT INTO produtos (price, description, supplier, stock, image) VALUES
(129.99, 'Smartphone Galaxy S23', 'Samsung', 15, 'galaxy_s23.jpg'),
(899.90, 'Notebook Dell Inspiron', 'Dell', 8, 'dell_inspiron.jpg'),
(49.99, 'Fone Bluetooth JBL', 'JBL', 23, 'jbl_headphones.jpg'),
(1999.00, 'TV LG OLED 55"', 'LG', 5, 'lg_oled.jpg');