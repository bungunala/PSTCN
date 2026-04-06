<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Votacion
 * 
 * Controlador para manejar la votación vía AJAX.
 * 
 */
class Votacion extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            show_error('Acceso no autorizado', 403);
        }
        $this->load->model('Votacion_model');
    }

    public function votar()
    {
        $concurso_id = $this->input->post('concurso_id');
        $nominado_id = $this->input->post('nominado_id');
        $votante_id = $this->session->userdata('user_id');

        if ($this->Votacion_model->ya_voto($concurso_id, $votante_id)) {
            echo json_encode(['success' => false, 'message' => 'Ya ha votado.']);
            return;
        }

        $data = [
            'id' => $this->generate_uuid(),
            'concurso_id' => $concurso_id,
            'votante_id' => $votante_id,
            'nominado_id' => $nominado_id,
            'fecha_voto' => date('Y-m-d H:i:s')
        ];

        if ($this->Votacion_model->registrar_voto($data)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar voto.']);
        }
    }

    private function generate_uuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}