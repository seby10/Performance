<?php
class ProductosRepository
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAll()
    {
        $stmt = $this->db->query("SELECT * FROM PRODUCTOS");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByCodigo($codigo)
    {
        $stmt = $this->db->prepare("SELECT * FROM PRODUCTOS WHERE COD_PRO = :codigo");
        $stmt->execute(['codigo' => $codigo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO PRODUCTOS (COD_PRO, NOM_PRO, MAR_PRO, PRE_UNI_PRO, EXISTENCIA) 
                                   VALUES (:codigo, :nombre, :marca, :precio, :existencia)");
        return $stmt->execute([
            'codigo' => $data['codigo'],
            'nombre' => $data['nombre'],
            'marca' => $data['marca'],
            'precio' => $data['precio'],
            'existencia' => $data['existencia']
        ]);
    }

    public function updateStock($codigo, $cantidad)
    {
        $stmt = $this->db->prepare("UPDATE PRODUCTOS SET EXISTENCIA = EXISTENCIA - :cantidad 
                                   WHERE COD_PRO = :codigo");
        return $stmt->execute([
            'codigo' => $codigo,
            'cantidad' => $cantidad
        ]);
    }
}
