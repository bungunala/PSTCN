<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Rol_model
 *
 * Modelo para gestionar los roles de usuario (administrador, usuario).
 * 
* al momento solo pued estar un usuario con un rol
 */
class Rol_model extends CI_Model {

    /**
     * Obtiene el rol principal de un usuario
     * 
     * @param int $usuario_id ID del usuario
     * @return string|null Nombre del rol ('administrador', 'usuario') o null si no tiene
     */
    public function get_rol_nombre($usuario_id)
    {
        $query = $this->db->select('r.nombre')
                          ->from('usuarios_roles ur')
                          ->join('roles r', 'ur.rol_id = r.id')
                          ->where('ur.usuario_id', $usuario_id)
                          ->limit(1)
                          ->get();

        if ($query->num_rows() > 0) {
            return $query->row()->nombre;
        }

        return null;
    }

    /**
     * Verifica si un usuario tiene un rol específico
     * 
     * @param int $usuario_id
     * @param string $rol_nombre Ej: 'administrador'
     * @return bool
     */
    public function tiene_rol($usuario_id, $rol_nombre)
    {
        $rol = $this->get_rol_nombre($usuario_id);
        return ($rol === $rol_nombre);
    }
}