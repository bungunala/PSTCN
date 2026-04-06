<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Usuario extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        $this->load->model('Auth_model');
        $this->load->model('Concurso_model');
        $this->load->model('Nominacion_model');
        $this->load->model('Votacion_model');
        
    }    

    /**
     * Mostrar pantalla de nominación
     */
    public function nominar($concurso_id)
    {
        $user_email = $this->session->userdata('user_email');

        // Verificar que el concurso exista y esté en estado 'nominacion'
        $concurso = $this->Concurso_model->get_by_id($concurso_id);
        if (!$concurso || $concurso['estado'] !== 'nominacion') {
            show_error('Este concurso no está en fase de nominación.', 403);
        }

        // Verificar si ya nominó
        $ya_nominado = $this->Nominacion_model->ya_nominado($concurso_id, $user_email);

        // Obtener nominados iniciales
        $nominados = $this->Nominacion_model->get_nominados_iniciales($concurso_id);

        $data['concurso'] = $concurso;
        $data['nominados'] = $nominados;
        $data['ya_nominado'] = $ya_nominado;        
        $this->load->view('usuario/nominar', $data);        
    }

    /**
     * Procesar la nominación
     */
    public function procesar_nominacion()
    {
        $user_id = $this->session->userdata('user_id');
        $user_email = $this->session->userdata('email'); // Asegúrate de guardar el email en login

        
        // Validar con servicio LDAP
        $usuario_valido = $this->Auth_model->validar_usuario_ldap($user_email);
        if (!$usuario_valido) {
            $this->session->set_flashdata('error', 'Su cuenta no está autorizada para participar.');
            redirect('home');
        }

        $concurso_id = $this->input->post('concurso_id');
        $nominee_email = $this->input->post('nominee_id');
        
        //var_dump($nominee_email);
        //die();  
        // Validar
        if (!$concurso_id || !$nominee_email) {
            $this->session->set_flashdata('error', 'Datos incompletos.');
            redirect('home');
        }

        

        // Verificar que el concurso esté en estado 'nominacion'
        $concurso = $this->Concurso_model->get_by_id($concurso_id);
        if (!$concurso || $concurso['estado'] !== 'nominacion') {
            $this->session->set_flashdata('error', 'El concurso no está en fase de nominación.');
            redirect('home');
        }

        // Verificar que no haya nominado ya
        if ($this->Nominacion_model->ya_nominado($concurso_id, $user_email)) {
            $this->session->set_flashdata('info', 'Ya ha ejercido su nominación.');
            redirect('home');
        }

        // Registrar nominación
        $data = [
            //'id' => null,
            'concurso_id' => $concurso_id,
            'nominador_email' => $user_email,
            'nominee_email' => $nominee_email,
            'fecha_nominacion' => date('Y-m-d H:i:s')
        ];
       
        if ($this->Nominacion_model->insert_nominacion($data)) {
            $this->session->set_flashdata('success', '¡Nominación exitosa! ' . $this->get_nombre_usuario($nominee_email) . ' ya está participando... ¡mucha suerte!');            
            redirect('home');
        } else {
            $this->session->set_flashdata('error', 'Error al registrar la nominación.');
            redirect('usuario/nominar/' . $concurso_id);
        }
    }

    private function get_nombre_usuario($email)
    {
        $usuario = $this->db->select('nombres, apellidos')->where('email', $email)->get('usuarios')->row();
        return $usuario ? $usuario->nombres . ' ' . $usuario->apellidos : 'este funcionario';
    }

    /**
     * Mostrar pantalla de votación
     */
    public function votar($concurso_id)
    {
        $user_email = $this->session->userdata('email');

        // Verificar que el concurso exista y esté en estado 'votacion'
        $concurso = $this->Concurso_model->get_by_id($concurso_id);
        if (!$concurso || $concurso['estado'] !== 'votacion') {
            show_error('Este concurso no está en fase de votación.', 403);
        }

        // Verificar si ya votó
        $ya_voto = $this->Votacion_model->ya_voto($concurso_id, $user_email);

        // Obtener nominados finales
        $nominados = $this->Nominacion_model->get_nominados_finales_con_media($concurso_id);

        $data['concurso'] = $concurso;
        $data['nominados'] = $nominados;
        $data['ya_voto'] = $ya_voto;

        $this->load->view('usuario/votar', $data);
    }

    /**
     * Procesar el voto
     */
    public function procesar_voto()
    {
        $user_id = $this->session->userdata('email');
        $email = $this->session->userdata('email'); // Asegúrate de guardar el email en login
        // Validar con servicio LDAP
        $usuario_valido = $this->Auth_model->validar_usuario_ldap($email);
        if (!$usuario_valido) {
            $this->session->set_flashdata('error', 'Su cuenta no está autorizada para participar.');
            redirect('home');
        }
        $concurso_id = $this->input->post('concurso_id');
        $nominado_id = $this->input->post('nominado_id');

        // var_dump($nominado_id);
        // die();

        // Validar
        if (!$concurso_id || !$nominado_id) {
            $this->session->set_flashdata('error', 'Datos incompletos.');
            redirect('home');
        }

        // Verificar que el concurso esté en estado 'votacion'
        $concurso = $this->Concurso_model->get_by_id($concurso_id);
        if (!$concurso || $concurso['estado'] !== 'votacion') {
            $this->session->set_flashdata('error', 'El concurso no está en fase de votación.');
            redirect('home');
        }

        // Verificar que no haya votado ya
        if ($this->Votacion_model->ya_voto($concurso_id, $user_id)) {
            $this->session->set_flashdata('info', 'Ya ha ejercido su voto.');
            redirect('home');
        }

        // Registrar voto
        $data = [
            //'id' => null,
            'concurso_id' => $concurso_id,
            'votante_email' => $user_id,
            'nominado_email' => $nominado_id,
            'fecha_voto' => date('Y-m-d H:i:s')
        ];

        if ($this->Votacion_model->insert_voto($data)) {
            $this->session->set_flashdata('success', '¡Voto exitoso!');
            redirect('home');
        } else {
            $this->session->set_flashdata('error', 'Error al registrar el voto.');
            redirect('usuario/votar/' . $concurso_id);
        }
    }
    
}