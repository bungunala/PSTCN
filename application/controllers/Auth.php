<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth
 * 
 * Controlador para manejar el login de usuarios. no deberia existit sino hacerlo desde wordpress o el ldap
 */
class Auth extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Usuario_model');
        $this->load->model('Rol_model'); // Cargamos el modelo de roles
        $this->load->model('Auth_model');
    }

    /**
     * Punto de autenticación automática
     * Uso: /auth/autologin?id=usuario@produccion.gob.ec
     */
    public function autologin()
    {
        $email = $this->input->get('id'); // 'id' es el email

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->mostrar_error('Correo inválido o no proporcionado.');
            return;
        }

        // Validar con el servicio web (LDAP/Zimbra)
        $usuario_valido = $this->Auth_model->validar_usuario_ldap($email);

        if (!$usuario_valido) {
            $this->mostrar_error('Acceso no autorizado. Usuario no encontrado o inactivo.');
            return;
        }

        // Crear sesión
        $data_session = [
            'logged_in' => TRUE,
            'email'     => $usuario_valido['email'],
            'nombres'   => $usuario_valido['nombres'],
            'apellidos' => $usuario_valido['apellidos'],
            'nombre_completo' => $usuario_valido['nombre_completo'],
            'rol'       => 'usuario', // Por defecto
            'user_id'   => $usuario_valido['id'] ?? null
        ];

        // Si es administrador (puedes definir quién lo es por email o ID)
        $emails_admin = ['lkaviedes@produccion.gob.ec', 'rpalomeque@produccion.gob.ec'];
        if (in_array($email, $emails_admin)) {
            $data_session['rol'] = 'administrador';
        }

        $this->session->set_userdata($data_session);

        // Redirigir al home
        redirect('home');
    }

    /**
     * Mostrar página de error
     */
    private function mostrar_error($mensaje)
    {
        $data['mensaje'] = $mensaje;
        $this->load->view('auth/error', $data);
    }


    public function login()
    {
        $data['title'] = 'Iniciar Sesión';
        $this->load->view('auth/login', $data);
    }
 
    public function authenticate()
    {
        $email = $this->input->post('email');

        $usuario = $this->Usuario_model->get_by_email($email);

        if ($usuario && $usuario['activo'] == 1) {
            // Obtener rol desde la base de datos
            $rol_nombre = $this->Rol_model->get_rol_nombre($usuario['id']);

            if (!$rol_nombre) {
                $this->session->set_flashdata('error', 'Usuario sin rol asignado.');
                redirect('auth/login');
            }

            // Crear sesión
            $session_data = array(
                'user_id' => $usuario['id'],
                'email' => $usuario['email'],
                'nombre_completo' => $usuario['nombres'] . ' ' . $usuario['apellidos'],
                'rol' => $rol_nombre,
                'logged_in' => TRUE
            );
            $this->session->set_userdata($session_data);

            redirect('home');
        } else {
            $this->session->set_flashdata('error', 'Correo no válido o usuario inactivo.');
            redirect('auth/login');
        }
    }

    /**
 * verificar_bd
 * probr en: ~/auth/verificar_bd
 */
public function verificar_bd()
{
    try {
        // Cargar la base de datos
        $this->load->database();
    } catch (Exception $e) {
        echo "<h1 style='color: red; text-align: center; margin-top: 100px;'>
                ❌ Error al cargar la base de datos
              </h1>";
        echo "<p style='text-align: center; font-size: 16px;'>
                <strong>Mensaje:</strong> {$e->getMessage()}<br>
                Revisa la configuración en <code>application/config/database.php</code>
              </p>";
        return;
    }   
    // Intentar ejecutar una consulta simple
    $query = $this->db->query("SELECT 1 AS connected");

    if ($query) {
        $row = $query->row();
        if ($row->connected == 1) {
            echo "<h1 style='color: green; text-align: center; margin-top: 100px;'>
                    ✅ Conexión con la base de datos: EXITOSA
                  </h1>";
            echo "<p style='text-align: center; font-size: 16px;'>
                    - Servidor: <strong>{$this->db->hostname}</strong><br>
                    - Base de datos: <strong>{$this->db->database}</strong><br>
                    - Puerto: <strong>" . parse_url($this->db->dsn, PHP_URL_PORT) . "</strong><br>
                    - Driver: <strong>PostgreSQL (PDO)</strong>
                  </p>";
        }
    } else {
        $error = $this->db->error();
        echo "<h1 style='color: red; text-align: center; margin-top: 100px;'>
                ❌ Error de conexión con la base de datos
              </h1>";
        echo "<p style='text-align: center; font-size: 16px;'>
                <strong>Mensaje:</strong> {$error['message']}<br>
                Revisa la configuración en <code>application/config/database.php</code>
              </p>";
    }
}

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('https://intranet.produccion.gob.ec/');
    }
}