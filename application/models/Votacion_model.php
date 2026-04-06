<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Votacion_model extends CI_Model {

    /**
     * Verifica si un usuario ya votó en un concurso
     */
    public function ya_voto($concurso_id, $votante_id)
    {
        return $this->db->where(['concurso_id' => $concurso_id, 'votante_email' => $votante_id])
                        ->count_all_results('votaciones') > 0;
    }

    /**
     * Inserta un nuevo voto
     */
    public function insert_voto($data)
    {
        // No incluyas 'id' => null
        return $this->db->insert('votaciones', $data);
    }
}