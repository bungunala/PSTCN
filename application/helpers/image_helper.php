<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Retorna la URL de la imagen del usuario.
 * Si no existe o el archivo no está, devuelve una imagen por defecto.
 *
 * @param string|null $foto_url URL relativa de la foto (ej: '/assets/fotos/123.png')
 * @return string URL completa de la imagen (con base_url)
 */
function user_image($foto_url)
{
    $default = base_url('public/assets/img/img_no_disponible.png');
    return !empty($foto_url) ? base_url($foto_url) : $default;
}