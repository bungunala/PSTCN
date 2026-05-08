<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/

// Ruta por defecto
//$route['default_controller'] = 'auth/login';
$route['default_controller'] = 'auth/autologin';
$route['auth/autologin'] = 'auth/autologin';
$route['auth/logout'] = 'auth/logout';

// Redirige cualquier intento de acceso directo a login
$route['auth/login'] = 'home';

// Rutas para Admin
$route['admin'] = 'admin/index';
$route['admin/crear_concurso'] = 'admin/crear_concurso';
$route['admin/guardar_concurso'] = 'admin/guardar_concurso';
$route['admin/gestionar_nominados/(:num)'] = 'admin/gestionar_nominados/$1';
$route['admin/guardar_nominados'] = 'admin/guardar_nominados';
$route['admin/ver_resultados/(:num)'] = 'admin/ver_resultados/$1';
$route['admin/elegir_ganadores/(:num)'] = 'admin/elegir_ganadores/$1';
$route['admin/guardar_ganadores'] = 'admin/guardar_ganadores';
$route['admin/exportar_concursos'] = 'admin/exportar_concursos';
$route['admin/exportar_concurso_detalle/(:num)'] = 'admin/exportar_concurso_detalle/$1';
$route['admin/eliminar_media'] = 'admin/eliminar_media';
$route['admin/eliminar_imagen_concurso'] = 'admin/eliminar_imagen_concurso';

// Rutas para Usuario
$route['usuario/nominar/(:num)'] = 'usuario/nominar/$1';
$route['usuario/procesar_nominacion'] = 'usuario/procesar_nominacion';
$route['usuario/votar/(:num)'] = 'usuario/votar/$1';
$route['usuario/procesar_voto'] = 'usuario/procesar_voto';

// Rutas para Home
$route['home/resultados/(:num)'] = 'home/resultados/$1';

// Si usas una URL amigable para el listado
$route['admin/concurso/(:num)'] = 'admin/gestionar_nominados/$1';

// Cualquier otra ruta no definida
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;




