<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Resultado_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        // Cargar el modelo de usuarios del servicio web
        $this->load->model('Usuario_model');
    }

    /**
     * Obtiene los últimos ganadores (solo 1er puesto)
     */
    public function get_ultimos_ganadores($limit = 3)
    {
        // Obtener resultados publicados
        $this->db->select('rp.usuario_email, rp.posicion, rp.concurso_id')
                 ->from('resultados_publicados rp')
                 ->where('rp.posicion', 1)
                 ->order_by('rp.fecha_publicacion', 'DESC')
                 ->limit($limit);

        $resultados = $this->db->get()->result_array();

        $ganadores = [];
        foreach ($resultados as $r) {
            $usuario = $this->Usuario_model->get_by_email($r['usuario_email']);
            if (!$usuario) continue;

            $concurso = $this->db->select('titulo')->where('id', $r['concurso_id'])->get('concursos')->row();

            $ganadores[] = [
                'posicion'         => $r['posicion'],
                'nombres'          => $usuario['nombres'],
                'apellidos'        => $usuario['apellidos'],
                'foto_url'         => $usuario['foto_url'] ?? null,
                'unidad'           => $usuario['unidad'],
                'ciudad'           => $usuario['ciudad'],
                'titulo_concurso'  => $concurso ? $concurso->titulo : 'Concurso desconocido'
            ];
        }

        return $ganadores;
    }

    /**
     * Obtiene los ganadores por concurso con foto y video del nominado final
     */
    public function get_ganadores_por_concurso($concurso_id)
    {
        // Obtener resultados publicados
        $this->db->select('rp.usuario_email, rp.posicion, nf.imagen_nominado, nf.video_nominado')
                 ->from('resultados_publicados rp')
                 ->join('nominados_finales nf', 
                        "nf.concurso_id = rp.concurso_id AND nf.usuario_email = rp.usuario_email", 'left')
                 ->where('rp.concurso_id', $concurso_id)
                 ->order_by('rp.posicion', 'ASC');

        $resultados = $this->db->get()->result_array();

        $ganadores = [];
        foreach ($resultados as $r) {
            $usuario = $this->Usuario_model->get_by_email($r['usuario_email']);
            if (!$usuario) continue;

            $ganadores[] = [
                'posicion'         => $r['posicion'],
                'nombres'          => $usuario['nombres'],
                'apellidos'        => $usuario['apellidos'],
                'unidad'           => $usuario['unidad'],
                'ciudad'           => $usuario['ciudad'],
                'provincia'        => $usuario['provincia'],
                'usuario_id'       => null,
                'usuario_email'    => $r['usuario_email'],
                'foto_url'         => $usuario['foto_url'] ?? null,
                'imagen_nominado'  => $r['imagen_nominado'],
                'video_nominado'   => $r['video_nominado']
            ];
        }

        return $ganadores;
    }

    /**
     * Obtiene los nominados finales con conteo de votos, ordenados por votos (desc)
     */
    public function get_nominados_finales_con_votos($concurso_id)
    {
        // Obtener emails de nominados finales
        $this->db->select('nf.usuario_email')
                 ->from('nominados_finales nf')
                 ->where('nf.concurso_id', $concurso_id);

        $finales = $this->db->get()->result_array();
        $emails = array_column($finales, 'usuario_email');

        if (empty($emails)) {
            return [];
        }

        // Contar votos por email
        $this->db->select('nominado_email, COUNT(*) as total_votos')
                 ->from('votaciones')
                 ->where('concurso_id', $concurso_id)
                 ->where_in('nominado_email', $emails)
                 ->group_by('nominado_email');

        $votos = $this->db->get()->result_array();
        $votos_map = array_column($votos, 'total_votos', 'nominado_email');

        // Combinar con datos del usuario
        $nominados = [];
        foreach ($emails as $email) {
            $usuario = $this->Usuario_model->get_by_email($email);
            if (!$usuario) continue;

            $nominados[] = [
                'id'            => null,
                'usuario_email' => $email,
                'apellidos'     => $usuario['apellidos'],
                'nombres'       => $usuario['nombres'],
                'provincia'     => $usuario['provincia'],
                'ciudad'        => $usuario['ciudad'],
                'unidad'        => $usuario['unidad'],
                'total_votos'   => $votos_map[$email] ?? 0
            ];
        }

        // Ordenar por votos (desc)
        usort($nominados, function($a, $b) {
            return $b['total_votos'] - $a['total_votos'];
        });

        return $nominados;
    }

    /**
     * Obtiene los ganadores ya publicados con el conteo de votos desde la tabla votaciones
     */
    public function get_ganadores_guardados($concurso_id)
    {
        // Obtener resultados publicados
        $this->db->select('rp.usuario_email, rp.posicion')
                 ->from('resultados_publicados rp')
                 ->where('rp.concurso_id', $concurso_id)
                 ->order_by('rp.posicion', 'ASC');

        $resultados = $this->db->get()->result_array();

        // Contar votos
        $this->db->select('nominado_email, COUNT(*) as total_votos')
                 ->from('votaciones')
                 ->where('concurso_id', $concurso_id)
                 ->group_by('nominado_email');

        $votos = $this->db->get()->result_array();
        $votos_map = array_column($votos, 'total_votos', 'nominado_email');

        $ganadores = [];
        foreach ($resultados as $r) {
            $usuario = $this->Usuario_model->get_by_email($r['usuario_email']);
            if (!$usuario) continue;

            $ganadores[] = [
                'usuario_id'      => null,
                'usuario_email'   => $r['usuario_email'],
                'posicion'        => $r['posicion'],
                'apellidos'       => $usuario['apellidos'],
                'nombres'         => $usuario['nombres'],
                'provincia'       => $usuario['provincia'],
                'ciudad'          => $usuario['ciudad'],
                'unidad'          => $usuario['unidad'],
                'total_votos'     => $votos_map[$r['usuario_email']] ?? 0
            ];
        }

        return $ganadores;
    }
}