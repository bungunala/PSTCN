<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Concurso_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        // Puedes cargar la base de datos si no está autocargada
        // $this->load->database();
    }

    /**
     * Obtiene todos los concursos
     */
    public function get_all()
    {
        return $this->db->get('concursos')->result_array();
    }

    /**
     * Obtiene concursos por estado
     * @param array $estados
     */
    public function get_by_estado($estados)
    {
        $this->db->where_in('estado', $estados);
        return $this->db->get('concursos')->result_array();
    }

    public function get_all_with_counts()
    {
        $this->db->select('c.*, COUNT(n.id) as total_nominaciones')
                 ->from('concursos c')
                 ->join('nominaciones n', 'n.concurso_id = c.id', 'left')
                 ->group_by('c.id')
                 ->order_by('c.fecha_creacion', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * Obtiene un concurso por su ID
     * 
     * @param int $id
     * @return array|null
     */
    public function get_by_id($id)
    {
        return $this->db->get_where('concursos', array('id' => $id))->row_array();
    }

 
/**
 * Actualiza un concurso
 */
public function update($id, $data)
{
    $this->db->where('id', $id);
    return $this->db->update('concursos', $data);
}

    /**
     * Obtiene todos los concursos con filtros opcionales
     * 
     * @param string $estado
     * @param string $fecha_desde
     * @param string $fecha_hasta
     * @return array
     */
    public function get_all_filtered($estado = null, $fecha_desde = null, $fecha_hasta = null)
    {
        $this->db->select('id, titulo, estado, fecha_creacion')
                ->from('concursos')
                ->order_by('fecha_creacion', 'DESC');

        if ($estado) {
            $this->db->where('estado', $estado);
        }

        if ($fecha_desde) {
            $this->db->where('fecha_creacion >=', $fecha_desde . ' 00:00:00');
        }

        if ($fecha_hasta) {
            $this->db->where('fecha_creacion <=', $fecha_hasta . ' 23:59:59');
        }

        return $this->db->get()->result_array();
    }
    public function insert($data)
    {
        return $this->db->insert('concursos', $data);
    }
    
}