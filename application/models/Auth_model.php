<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_model extends CI_Model {

    private $api_url = 'http://preproduccion.produccion.gob.ec/intranet/wp-json/ppu/v1/usuarios';

    /**
 * Verifica si un email existe y está activo en el servicio LDAP/Zimbra
 *
 * @param string $email Email del usuario (ej: candrade@produccion.gob.ec)
 * @return array|false Datos del usuario o false si no válido
 */
public function validar_usuario_ldap($email)
{
    // Limpiar y validar email
    $email = strtolower(trim($email));
    $usuarios = null;
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        log_message('error', 'Email inválido en validación LDAP: ' . $email);
            return false;
        }

        $cache_file = APPPATH . 'cache/ldap_usuarios.json';
        $tiempo_cache = 3600; // 1 hora

        if (file_exists($cache_file) && (time() - filemtime($cache_file) < $tiempo_cache)) {
            $usuarios = json_decode(file_get_contents($cache_file), true);
        } else {
            // Intentar obtener datos del servicio web
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->api_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json']
            ]);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Validar respuesta HTTP
            if ($http_code !== 200 || !$response) {
                log_message('error', 'Error al conectar con el servicio LDAP. Código: ' . $http_code);
                return false;
            }

            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'Error al decodificar JSON del servicio LDAP');
                return false;
            }

            // El servicio devuelve un objeto con una clave "users" que contiene el array
            // o directamente un array. Vamos a manejar ambos casos.

            $usuarios = is_array($data) ? $data : [];
        }
        // Si el JSON tiene una estructura como {"users": [...]}
        if (isset($data['users']) && is_array($data['users'])) {
            $usuarios = $data['users'];
        }

        // Buscar usuario por "core:user_email"
        foreach ($usuarios as $user) {        
        //echo trim($user['meta:first_name'] ?? '') . ' ' . trim($user['meta:last_name'] ?? '');
        if (isset($user['core:user_email']) && strtolower($user['core:user_email']) === $email) {
            // Mapear datos a un formato limpio
            return [
                'id'         => $user['core:ID'] ?? null,
                'email'      => $user['core:user_email'],
                'nombres'    => trim($user['meta:first_name'] ?? ''),
                'apellidos'  => trim($user['meta:last_name'] ?? ''),
                'nombre_completo'  => trim($user['meta:first_name'] ?? '') . ' ' . trim($user['meta:last_name'] ?? ''),
                'provincia'  => $user['meta:provincia'] ?? '',
                'ciudad'     => $user['meta:ciudad'] ?? '',
                'unidad'     => $user['meta:unidad_organica_fisica'] ?? '',
                'cargo'      => $user['meta:cargo'] ?? '',
                'cedula'     => $user['meta:cedula'] ?? '',
                'estado'     => 'activo' // Asumimos que si está en el listado, está activo
            ];
        }
    }    

    // No encontrado
    log_message('info', 'Usuario no encontrado en LDAP: ' . $email);
    return false;
}

    // En Auth_model.php
    private function _get_usuarios_ldap()
    {
        $cache_file = APPPATH . 'cache/ldap_usuarios.json';
        $tiempo_cache = 3600; // 1 hora

        if (file_exists($cache_file) && (time() - filemtime($cache_file) < $tiempo_cache)) {
            return json_decode(file_get_contents($cache_file), true);
        }

        $ch = curl_init($this->api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response && json_decode($response)) {
            file_put_contents($cache_file, $response);
            return json_decode($response, true);
        }

        return false;
    }
}