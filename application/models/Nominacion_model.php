<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Nominacion_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        // Cargar el modelo de usuarios (del servicio web)
        $this->load->model('Usuario_model');
    }

    /**
     * Obtiene los nominados por concurso (usado en administración)
     * Devuelve datos completos obtenidos del servicio web
     */
    public function get_nominados_por_concurso($concurso_id)
    {
        // Obtener emails de nominados en este concurso
        $this->db->select('n.nominee_email')
                 ->from('nominaciones n')
                 ->where('n.concurso_id', $concurso_id)
                 ->group_by('n.nominee_email');

        $result = $this->db->get()->result_array();
        $emails = array_column($result, 'nominee_email');

        if (empty($emails)) {
            return [];
        }

        // Obtener datos reales de cada usuario desde el modelo
        $nominados = [];
        foreach ($emails as $email) {
            $usuario = $this->Usuario_model->get_by_email($email);
            if ($usuario) {
                $nominados[] = $usuario;
            }
        }

        // Ordenar por apellidos, nombres
        usort($nominados, function($a, $b) {
            $nombre_a = $a['apellidos'] . ' ' . $a['nombres'];
            $nombre_b = $b['apellidos'] . ' ' . $b['nombres'];
            return strcmp($nombre_a, $nombre_b);
        });

        return $nominados;
    }

    /**
     * Inserta o actualiza un registro de nominado (foto, video, visibilidad)
     */
    public function upsert_nominado_registrado($data)
    {
        $existing = $this->db->get_where('nominados_registrados', [
            'concurso_id' => $data['concurso_id'],
            'usuario_email' => $data['usuario_email']
        ])->row();

        if ($existing) {
            $this->db->where([
                'concurso_id' => $data['concurso_id'],
                'usuario_email' => $data['usuario_email']
            ])->update('nominados_registrados', $data);
        } else {
            $this->db->insert('nominados_registrados', $data);
        }
    }

    /**
     * Inserta una nominación
     */
    public function insert_nominacion($data)
    {
        // var_dump($data);
        // die();
        return $this->db->insert('nominaciones', $data);
    }

    /**
     * Inserta un nominado inicial
     */
    public function insert_nominado_inicial($concurso_id, $usuario_email)
    {
        $data = [
            'concurso_id' => $concurso_id,
            'usuario_email' => $usuario_email   
        ];
        return $this->db->insert('nominados_iniciales', $data);
    }

    /**
     * Actualiza la foto y video de un nominado final
     */
    public function update_media($concurso_id, $usuario_email, $imagen_url = null, $video_url = null)
    {
        $data = [];
        if ($imagen_url) $data['imagen_nominado'] = $imagen_url;
        if ($video_url) $data['video_nominado'] = $video_url;

        return $this->db->where(['concurso_id' => $concurso_id, 'usuario_email' => $usuario_email])
                        ->update('nominados_finales', $data);
    }

    /**
     * Obtiene los nominados iniciales de un concurso, con conteo de nominaciones
     * Usa email como identificador y complementa con datos del servicio web
     */
    public function get_nominados_iniciales_con_votos($concurso_id)
    {
        // Solo obtener emails y conteo
        $this->db->select('ni.usuario_email, COUNT(n.id) as total_nominaciones')
                 ->from('nominados_iniciales ni')
                 ->join('nominaciones n', "n.nominee_email = ni.usuario_email AND n.concurso_id = $concurso_id", 'left')
                 ->where('ni.concurso_id', $concurso_id)
                 ->group_by('ni.usuario_email')
                 ->order_by('total_nominaciones', 'DESC');

        $resultados = $this->db->get()->result_array();

        // Complementar con datos del servicio web
        $completo = [];
        foreach ($resultados as $row) {
            $usuario = $this->Usuario_model->get_by_email($row['usuario_email']);
            if (!$usuario) continue;

            $completo[] = [
                'usuario_email'         => $row['usuario_email'],
                'nombres'               => $usuario['nombres'],
                'apellidos'             => $usuario['apellidos'],
                'provincia'             => $usuario['provincia'],
                'ciudad'                => $usuario['ciudad'],
                'unidad'                => $usuario['unidad'],
                'total_nominaciones'    => $row['total_nominaciones']
            ];
        }

        return $completo;
    }

    /**
     * Obtiene los nominados finales de un concurso
     */
    public function get_nominados_finales($concurso_id)
    {
        $this->db->select('nf.usuario_email')
                 ->from('nominados_finales nf')
                 ->where('nf.concurso_id', $concurso_id);

        $resultados = $this->db->get()->result_array();
        $emails = array_column($resultados, 'usuario_email');

        if (empty($emails)) {
            return [];
        }

        $finales = [];
        foreach ($emails as $email) {
            $usuario = $this->Usuario_model->get_by_email($email);
            if ($usuario) {
                $finales[] = $usuario;
            }
        }

        // Ordenar por apellidos, nombres
        usort($finales, function($a, $b) {
            $nombre_a = $a['apellidos'] . ' ' . $a['nombres'];
            $nombre_b = $b['apellidos'] . ' ' . $b['nombres'];
            return strcmp($nombre_a, $nombre_b);
        });

        return $finales;
    }

    /**
     * Agrega un nominado final
     */
    public function add_nominado_final($concurso_id, $usuario_email)
    {
        $data = [
            'concurso_id' => $concurso_id,
            'usuario_email' => $usuario_email
        ];
        return $this->db->insert('nominados_finales', $data);
    }

    /**
     * Elimina un nominado final
     */
    public function remove_nominado_final($concurso_id, $usuario_email)
    {
        return $this->db->delete('nominados_finales', ['concurso_id' => $concurso_id, 'usuario_email' => $usuario_email]);
    }

    /**
     * Obtiene los nominados iniciales de un concurso (solo datos básicos)
     */
    public function get_nominados_iniciales($concurso_id)
    {
        $this->db->select('ni.usuario_email')
                 ->from('nominados_iniciales ni')
                 ->where('ni.concurso_id', $concurso_id);

        $resultados = $this->db->get()->result_array();
        $emails = array_column($resultados, 'usuario_email');

        $lista = [];
        foreach ($emails as $email) {
            $usuario = $this->Usuario_model->get_by_email($email);
            if ($usuario) {
                $lista[] = [
                    'id' => null,
                    'nombres' => $usuario['nombres'],
                    'apellidos' => $usuario['apellidos'],
                    'email' => $usuario['email'],
                    'ciudad' => $usuario['ciudad'],
                    'unidad' => $usuario['unidad'],
                    'foto_url' => $usuario['foto_url'] ?? null
                ];
            }
        }

        usort($lista, function($a, $b) {
            return strcmp($a['apellidos'] . $a['nombres'], $b['apellidos'] . $b['nombres']);
        });

        return $lista;
    }

    /**
     * Verifica si un usuario ya nominó en un concurso
     */
    public function ya_nominado($concurso_id, $nominador_email)
    {
        // var_dump($concurso_id, $nominador_email);
        // die();        
        return $this->db->where([
            'concurso_id' => $concurso_id,
            'nominador_email' => $nominador_email
        ])->count_all_results('nominaciones') > 0;
    }

    /**
     * Obtiene los nominados finales con foto y video
     */
    public function get_nominados_finales_con_media($concurso_id)
    {
        $this->db->select('nf.usuario_email, nf.imagen_nominado, nf.video_nominado')
                 ->from('nominados_finales nf')
                 ->where('nf.concurso_id', $concurso_id);

        $resultados = $this->db->get()->result_array();

        $lista = [];
        foreach ($resultados as $row) {
            $usuario = $this->Usuario_model->get_by_email($row['usuario_email']);
            if (!$usuario) continue;

            $lista[] = [
                'id' => null,
                'nombres' => $usuario['nombres'],
                'apellidos' => $usuario['apellidos'],
                'email' => $usuario['email'],
                'ciudad' => $usuario['ciudad'],
                'unidad' => $usuario['unidad'],
                'foto_url' => $row['imagen_nominado'] ?? $usuario['foto_url'] ?? null,
                'video_nominado' => $row['video_nominado']
            ];
        }

        usort($lista, function($a, $b) {
            return strcmp($a['apellidos'] . $a['nombres'], $b['apellidos'] . $b['nombres']);
        });

        return $lista;
    }
}