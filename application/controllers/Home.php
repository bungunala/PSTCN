<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Home
 * 
 * Controlador principal para la página de inicio.
 * Muestra concursos activos ganadores recientes y gestionaacceso por rol.
 */
class Home extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        // Verificar autenticación
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }

        // Cargar modelos
        $this->load->model('Concurso_model');
        $this->load->model('Resultado_model');
        $this->load->model('Nominacion_model'); //  para verificar "ya_nominado"
        $this->load->model('Votacion_model'); //  para verificar "ya_votado"
        // Cargar librerías
        $this->load->library('session');
    }

    /**
     * Página principal
     */
    public function index()
    {
        $user_email = $this->session->userdata('email');
        $rol = $this->session->userdata('rol');

        // Si es administrador, redirigir a su panel
        if ($rol === 'administrador') {
            redirect('admin');
        }

        // Obtener concursos
        $concursos = $this->Concurso_model->get_all_with_counts_not_in_design();


        // Agregar estado de nominación para cada concurso
        foreach ($concursos as &$concurso) {            
            $concurso['ya_nominado'] = $this->Nominacion_model->ya_nominado($concurso['id'], $user_email);
            $concurso['ya_voto'] = $this->Votacion_model->ya_voto($concurso['id'], $user_email);
        }
 
        // Obtener los últimos 3 ganadores (solo 1er puesto)
        $ganadores = $this->Resultado_model->get_ultimos_ganadores(3);

        // Preparar datos para la vista
        $data['concursos'] = $concursos;
        $data['ganadores'] = $ganadores;

        // Cargar la vista
        $this->load->view('home/index', $data);
    }

    /**
 * Mostrar resultados de un concurso cerrado
 */
public function resultados($concurso_id)
{
    $concurso = $this->Concurso_model->get_by_id($concurso_id);
    if (!$concurso || $concurso['estado'] !== 'cerrado') {
        show_error('Este concurso aún no está cerrado.', 403);
    }

    // Obtener ganadores (ordenados por posición)
    $ganadores = $this->Resultado_model->get_ganadores_por_concurso($concurso_id);

    $data['concurso'] = $concurso;
    $data['ganadores'] = $ganadores;

    $this->load->view('home/resultados', $data);
}
}
