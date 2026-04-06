<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Usuario_model
 *
 * Modelo para obtener usuarios desde el servicio web institucional (LDAP/Zimbra).
 * 
 */
class Usuario_model extends CI_Model {

    private $api_url = 'http://preproduccion.produccion.gob.ec/intranet/wp-json/ppu/v1/usuarios';
    private $cache_lifetime = 3600; // 1 hora de caché

    /**
     * Obtiene un usuario por email desde el servicio web
     */
    public function get_by_email($email)
    {
        $email = strtolower(trim($email));
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $usuarios = $this->get_all_usuarios();
        foreach ($usuarios as $user) {
            if ($user['email'] === $email) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Obtiene un usuario por ID desde el servicio web
     * Nota: El ID aquí es el core:ID del servicio
     */
    public function get_by_id($id)
    {
        $id = (int)$id;
        if (!$id) {
            return null;
        }

        $usuarios = $this->get_all_usuarios();
        foreach ($usuarios as $user) {
            if ($user['id'] == $id) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Obtiene todos los usuarios activos desde el servicio web
     * Usa caché para mejorar rendimiento
     */
    public function get_all_usuarios()
    {
        $cache_file = APPPATH . 'cache/ldap_usuarios.json';

        // Verificar si hay caché válida
        if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $this->cache_lifetime) {
            $data = json_decode(file_get_contents($cache_file), true);           
            if (is_array($data)) {
                return $this->mapear_usuarios($data);
            }
        }

        // No hay caché o está vencida: obtener del servicio web
        $usuarios = $this->fetch_from_api();
        //var_dump($usuarios);
        //die();
        if ($usuarios !== false) {
            // Guardar en caché
            file_put_contents($cache_file, json_encode($usuarios));
            return $this->mapear_usuarios($usuarios);
        }

        // Si falla el API, intentar usar caché aunque esté vencida
        if (file_exists($cache_file)) {
            $data = json_decode(file_get_contents($cache_file), true);
            if (is_array($data)) {
                log_message('error', 'API LDAP caído, usando caché vencida');
                return $this->mapear_usuarios($data);
            }
        }

        log_message('error', 'No se pudo obtener lista de usuarios ni de API ni de caché');
        return [];
    }

    /**
     * Llama al servicio web y obtiene los usuarios
     */
    private function fetch_from_api()
    {
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

        if ($http_code !== 200 || !$response) {
            log_message('error', 'Error API LDAP (' . $http_code . '): ' . substr($response, 0, 200));
            return false;
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', 'JSON inválido en respuesta LDAP: ' . json_last_error_msg());
            return false;
        }

        // Manejar estructura: array plano o { "users": [...] }
        if (isset($data['users']) && is_array($data['users'])) {
            return $data['users'];
        }

        if (is_array($data) && count($data) > 0) {
            return $data;
        }

        return false;
    }

    /**
     * Mapea los datos del servicio web a un formato limpio
     */
    private function mapear_usuarios($usuarios_raw)
    {
        $usuarios = [];
        foreach ($usuarios_raw as $user) {
            if (!isset($user['core:user_email'])) continue;
            if ($this->validar_datos_completos($user)) {
                $usuarios[] = [
                    //'id'         => $user['core:ID'] ?? null,
                    'id'         => $user['core:user_email'] ?? null,
                    'email'      => $user['core:user_email'],
                    'nombres'    => trim($user['meta:first_name'] ?? ''),
                    'apellidos'  => trim($user['meta:last_name'] ?? ''),
                    'provincia'  => $user['meta:provincia'] ?? '',
                    'ciudad'     => $user['meta:ciudad'] ?? '',
                    'unidad'     => $user['meta:unidad_organica_fisica'] ?? '',
                    //'genero'     => $this->inferir_genero($user['meta:first_name'] ?? ''),
                    'genero'     => $user['meta:genero'] ?? '',
                    'cargo'      => $user['meta:cargo'] ?? '',
                    'cedula'     => $user['meta:cedula'] ?? ''
                ];
            }
        }
        //var_dump($usuarios);
        //die();
        // Ordenar por apellidos, nombres
        usort($usuarios, function ($a, $b) {
            $nombre_a = $a['apellidos'] . ' ' . $a['nombres'];
            $nombre_b = $b['apellidos'] . ' ' . $b['nombres'];
            return strcmp($nombre_a, $nombre_b);
        });

        return $usuarios;
    }

    private function validar_datos_completos($user) {
        return !empty($user['core:user_email']) &&
               !empty($user['meta:first_name']) &&
               !empty($user['meta:last_name']) 
               //&& !empty($user['meta:genero']) &&
               //!empty($user['meta:cedula'])
               ;
    }
}