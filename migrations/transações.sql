USE dados;

CREATE TABLE Orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idUser INT,
    dataPedido DATE,
    status VARCHAR(30),
    idFornecedor INT,
    dataConfirmacao DATE,
    dataDevolucao DATE,
    motivoDevolucao TEXT,
    motivoRecusa TEXT,
    total DOUBLE
);


CREATE TABLE OrderItems (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idOrder VARCHAR(10), 
    idProduct INT,
    quantity DOUBLE,
    value DOUBLE
);

CREATE TABLE Payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idUser INT,
    idOrder INT,
    status VARCHAR(50),
    datePayment DATE,
    total DOUBLE
);
