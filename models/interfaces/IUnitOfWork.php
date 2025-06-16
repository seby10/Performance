<?php
interface IUnitOfWork
{
    public function beginTransaction();
    public function commit();
    public function rollback();
    public function getClientesRepository();
    public function getProductosRepository();
    public function getVentasRepository();
}
