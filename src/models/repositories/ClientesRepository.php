<?php
class ClientesRepository
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAll()
    {
        $stmt = $this->db->query("SELECT * FROM CLIENTES");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByCedula($cedula)
    {
        $stmt = $this->db->prepare("SELECT * FROM CLIENTES WHERE CED_CLI = :cedula");
        $stmt->execute(['cedula' => $cedula]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO CLIENTES (CED_CLI, NOM_CLI, APE_CLI, DIR_CLI, TEL_CLI) 
                                   VALUES (:cedula, :nombre, :apellido, :direccion, :telefono)");
        return $stmt->execute([
            'cedula' => $data['cedula'],
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'direccion' => $data['direccion'],
            'telefono' => $data['telefono']
        ]);
    }

    public function exists($cedula)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM CLIENTES WHERE CED_CLI = :cedula");
        $stmt->execute(['cedula' => $cedula]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
