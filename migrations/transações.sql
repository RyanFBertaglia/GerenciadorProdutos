USE dados;
CREATE TABLE Orders (
    id INT PRIMARY KEY,
    idUser INT,
    dataPedido DATE,
    status VARCHAR(30),
    idFornecedor INT,
    total DOUBLE
);

CREATE TABLE OrderItems (
    id INT PRIMARY KEY,
    idOrder VARCHAR(10), 
    idProduct INT,
    quantity DOUBLE,
    value DOUBLE
);

CREATE TABLE Payments (
    id INT PRIMARY KEY,
    idUser INT,
    idOrder INT,
    status VARCHAR(50),
    datePayment DATE,
    total DOUBLE
);
