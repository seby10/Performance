<?php
require_once 'interfaces/IUnitOfWork.php';
require_once 'repositories/ClientesRepository.php';
require_once 'repositories/ProductosRepository.php';
require_once 'repositories/VentasRepository.php';

class UnitOfWork implements IUnitOfWork
{
    private $connection;
    private $clientesRepository;
    private $productosRepository;
    private $ventasRepository;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
        $this->clientesRepository = new ClientesRepository($connection);
        $this->productosRepository = new ProductosRepository($connection);
        $this->ventasRepository = new VentasRepository($connection);
    }

    public function beginTransaction()
    {
        if (!$this->connection->inTransaction()) {
            $this->connection->beginTransaction();
        }
    }

    public function commit()
    {
        if ($this->connection->inTransaction()) {
            $this->connection->commit();
        }
    }

    public function rollback()
    {
        if ($this->connection->inTransaction()) {
            $this->connection->rollBack();
        }
    }

    public function getClientesRepository()
    {
        return $this->clientesRepository;
    }

    public function getProductosRepository()
    {
        return $this->productosRepository;
    }

    public function getVentasRepository()
    {
        return $this->ventasRepository;
    }
}
