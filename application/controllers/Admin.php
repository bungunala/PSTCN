<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Admin
 * 
 * Controlador para el rol Administrador.
 * Permite gestionar concursos, nominados y resultados.
 
 */
class Admin extends CI_Controller
{


    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }

        if ($this->session->userdata('rol') !== 'administrador') {
            show_error('Acceso no autorizado. Requiere rol de administrador.', 403);
        }

        // Cargar modelos
        $this->load->model('Concurso_model');
        $this->load->model('Nominacion_model');
        $this->load->model('Usuario_model'); // ← Añade esta línea
        $this->load->model('Resultado_model');

        // Cargar librerías y helpers
        $this->load->helper('form');
        $this->load->library('form_validation');
    }

    /**
     * Pantalla principal de administración: listado de concursos
     */
    public function index()
    {
        // Obtener filtros
        $estado = $this->input->get('estado');
        $fecha_desde = $this->input->get('fecha_desde');
        $fecha_hasta = $this->input->get('fecha_hasta');

        // Aplicar filtros en el modelo
        $data['concursos'] = $this->Concurso_model->get_all_filtered($estado, $fecha_desde, $fecha_hasta);
        $data['estado_filtro'] = $estado;
        $data['fecha_desde_filtro'] = $fecha_desde;
        $data['fecha_hasta_filtro'] = $fecha_hasta;

        $this->load->view('admin/listar_concursos', $data);
    }

    /**
     * Mostrar formulario para crear un nuevo concurso
     */
    /**
     * Mostrar formulario para crear un nuevo concurso con selección de nominados
     */
    public function crear_concurso()
    {
        $usuarios = $this->Usuario_model->get_all_usuarios();
        $data['usuarios'] = $usuarios;
        $this->load->view('admin/crear_concurso', $data);
    }

    /**
     * Guardar un nuevo concurso y sus nominados iniciales
     */
    public function guardar_concurso()
    {
        // Validación del formulario
        $this->form_validation->set_rules('titulo', 'Título', 'required|min_length[5]');
        $this->form_validation->set_rules('descripcion', 'Descripción', 'max_length[500]');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            $usuarios = $this->Usuario_model->get_all_usuarios();
            $data['usuarios'] = $usuarios;
            $this->load->view('admin/crear_concurso', $data);
            return;
        }

        // Verificar si se seleccionó al menos un nominado
        $nominados_ids = $this->input->post('nominado_id') ?: [];
        if (empty($nominados_ids)) {
            $this->session->set_flashdata('error', 'Debe seleccionar al menos un nominado.');
            $usuarios = $this->Usuario_model->get_all_usuarios();
            $data['usuarios'] = $usuarios;
            $this->load->view('admin/crear_concurso', $data);
            return;
        }
        //var_dump($nominados_ids);
        //die();

        // Subir imagen
        $imagen_url = null;
        if (!empty($_FILES['imagen']['name'])) {
            $config['upload_path'] = FCPATH . 'public/assets/images/concursos/';
            $config['allowed_types'] = 'png';
            $config['max_size'] = 5120; // 5MB
            $config['encrypt_name'] = TRUE;

            // Asegurarse de que la carpeta exista
            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0777, TRUE);
                //mkdir($upload_path, 0777, TRUE);
            }

            $this->load->library('upload', $config);
            $this->upload->initialize($config); // Asegurar que la configuración se aplique

            if (!$this->upload->do_upload('imagen')) {
                $error_msg = $this->upload->display_errors();
                die($error_msg);
                $error_msg = $this->upload->display_errors('', '');
                log_message('error', 'Upload error: ' . $error_msg);
                $this->session->set_flashdata('error', 'Error al subir la imagen: ' . $error_msg);
                $usuarios = $this->Usuario_model->get_all_usuarios();
                $data['usuarios'] = $usuarios;
                $this->load->view('admin/crear_concurso', $data);
                return;
            } else {
                $upload_data = $this->upload->data();
                $imagen_url = '/public/assets/images/concursos/' . $upload_data['file_name'];
            }
        }

        // Datos del concurso (sin el campo 'id')
        $data_concurso = [
            'titulo' => $this->input->post('titulo'),
            'descripcion' => $this->input->post('descripcion'),
            'imagen_url' => $imagen_url,
            'estado' => 'diseño',
            'creado_por' => $this->session->userdata('email'),
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'modificado_por' => $this->session->userdata('email'),
            'fecha_ult_modificacion' => date('Y-m-d H:i:s')
        ];

        // Iniciar transacción
        $this->db->trans_start();

        $this->Concurso_model->insert($data_concurso);
        $concurso_id = $this->db->insert_id();

        // foreach ($nominados_ids as $usuario_id) {
        //     $this->Nominacion_model->insert_nominado_inicial($concurso_id, $usuario_id);
        // }        
        foreach ($nominados_ids as $email) {
            // Validar que el email exista en el sistema (LDAP)            
            $usuario = $this->Usuario_model->get_by_email($email);
            if (!$usuario) {
                continue; // Saltar si no es válido
            }            
            $this->Nominacion_model->insert_nominado_inicial($concurso_id, $email);
        }

        $this->db->trans_complete();
        // var_dump($this->db->trans_status());
        // die();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Error al guardar el concurso. Transacción fallida.');
            //log_message('error', 'Transacción de base de datos fallida.');
            echo "error";
            die();
        } else {
            $this->session->set_flashdata('success', 'Concurso y nominados iniciales creados exitosamente.');
            redirect('admin/index');
            echo "success";
            die();
        }

        // Si llega aquí, hubo un error y debe recargar la vista
        $usuarios = $this->Usuario_model->get_all_usuarios();
        $data['usuarios'] = $usuarios;
        $this->load->view('admin/crear_concurso', $data);
    }
    /**
     * Gestionar nominados de un concurso
     */
    /**
     * Editar concurso: mostrar datos y listas de nominados
     */
    public function gestionar_nominados($concurso_id)
    {
        $concurso = $this->Concurso_model->get_by_id($concurso_id);
        if (!$concurso) {
            show_error('Concurso no encontrado.', 404);
        }

        $nominados_iniciales = $this->Nominacion_model->get_nominados_iniciales_con_votos($concurso_id);
        $nominados_finales = $this->Nominacion_model->get_nominados_finales($concurso_id);

        $data['concurso'] = $concurso;
        $data['nominados_iniciales'] = $nominados_iniciales;
        $data['nominados_finales'] = $nominados_finales;

        $this->load->view('admin/gestionar_nominados', $data);
    }

    /**
     * Guardar cambios en el concurso y nominados finales
     */



    public function guardar_nominados()
    {
        $concurso_id = $this->input->post('concurso_id');
        $titulo = $this->input->post('titulo');
        $descripcion = $this->input->post('descripcion');
        $estado = $this->input->post('estado');
        $nominados_finales_ids = $this->input->post('nominados_finales') ?: [];

        // Validar estado
        if ($estado === 'votacion' && empty($nominados_finales_ids)) {
            $this->session->set_flashdata('error', 'No se puede pasar a votación sin nominados finales.');
            redirect('admin/gestionar_nominados/' . $concurso_id);
        }

        // Actualizar concurso
        $this->Concurso_model->update($concurso_id, [
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'estado' => $estado,
            'modificado_por' => $this->session->userdata('user_id'),
            'fecha_ult_modificacion' => date('Y-m-d H:i:s')
        ]);

        // Sincronizar nominados finales
        $this->db->trans_start();

        // Eliminar todos los actuales
        $this->db->delete('nominados_finales', ['concurso_id' => $concurso_id]);

        // Insertar los nuevos
        foreach ($nominados_finales_ids as $usuario_email) {
            $this->Nominacion_model->add_nominado_final($concurso_id, $usuario_email);
        }

        // Subir fotos y videos
        foreach ($nominados_finales_ids as $usuario_id) {
            $imagen_url = null;
            $video_url = null;

            if (!empty($_FILES['imagen_' . $usuario_id]['name'])) {
                $config['upload_path'] = './public/assets/fotos/';
                $config['allowed_types'] = 'png';
                $config['max_size'] = 5120;
                $config['encrypt_name'] = TRUE;

                $this->load->library('upload');
                $_FILES['file']['name'] = $_FILES['imagen_' . $usuario_id]['name'];
                $_FILES['file']['type'] = $_FILES['imagen_' . $usuario_id]['type'];
                $_FILES['file']['tmp_name'] = $_FILES['imagen_' . $usuario_id]['tmp_name'];
                $_FILES['file']['error'] = $_FILES['imagen_' . $usuario_id]['error'];
                $_FILES['file']['size'] = $_FILES['imagen_' . $usuario_id]['size'];

                $this->upload->initialize($config);
                if ($this->upload->do_upload('file')) {
                    $upload_data = $this->upload->data();
                    $imagen_url = '/assets/fotos/' . $upload_data['file_name'];
                }
            }

            if (!empty($_FILES['video_' . $usuario_id]['name'])) {
                $config['upload_path'] = './public/assets/videos/';
                $config['allowed_types'] = 'mp4';
                $config['max_size'] = 92160;
                $config['encrypt_name'] = TRUE;

                $this->load->library('upload');
                $_FILES['file']['name'] = $_FILES['video_' . $usuario_id]['name'];
                $_FILES['file']['type'] = $_FILES['video_' . $usuario_id]['type'];
                $_FILES['file']['tmp_name'] = $_FILES['video_' . $usuario_id]['tmp_name'];
                $_FILES['file']['error'] = $_FILES['video_' . $usuario_id]['error'];
                $_FILES['file']['size'] = $_FILES['video_' . $usuario_id]['size'];

                $this->upload->initialize($config);
                if ($this->upload->do_upload('file')) {
                    $upload_data = $this->upload->data();
                    $video_url = '/assets/videos/' . $upload_data['file_name'];
                }
            }

            if ($imagen_url || $video_url) {
                $this->Nominacion_model->update_media($concurso_id, $usuario_id, $imagen_url, $video_url);
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() !== FALSE) {
            $this->session->set_flashdata('success', 'Concurso, nominados finales y media actualizados.');
        } else {
            $this->session->set_flashdata('error', 'Error al actualizar los datos.');
        }

        redirect('admin/gestionar_nominados/' . $concurso_id);
    }

    public function guardar_nominados_old()
    {
        $concurso_id = $this->input->post('concurso_id');
        $titulo = $this->input->post('titulo');
        $descripcion = $this->input->post('descripcion');
        $estado = $this->input->post('estado');
        $nominados_finales_ids = $this->input->post('nominados_finales') ?: [];

        // Validar estado
        if ($estado === 'votacion' && empty($nominados_finales_ids)) {
            $this->session->set_flashdata('error', 'No se puede pasar a votación sin nominados finales.');
            redirect('admin/gestionar_nominados/' . $concurso_id);
        }

        // Actualizar concurso
        $this->Concurso_model->update($concurso_id, [
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'estado' => $estado,
            'modificado_por' => $this->session->userdata('user_id'),
            'fecha_ult_modificacion' => date('Y-m-d H:i:s')
        ]);

        // Sincronizar nominados finales
        $this->db->trans_start();

        // Eliminar todos los actuales
        $this->db->delete('nominados_finales', ['concurso_id' => $concurso_id]);

        // Insertar los nuevos
        foreach ($nominados_finales_ids as $usuario_id) {
            $this->Nominacion_model->add_nominado_final($concurso_id, $usuario_id);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() !== FALSE) {
            $this->session->set_flashdata('success', 'Concurso y nominados finales actualizados.');
        } else {
            $this->session->set_flashdata('error', 'Error al actualizar los nominados finales.');
        }

        redirect('admin/gestionar_nominados/' . $concurso_id);
    }

    /**
     * Ver resultados del concurso
     */
    public function ver_resultados($concurso_id)
    {
        $concurso = $this->Concurso_model->get_by_id($concurso_id);
        $resultados = $this->Resultado_model->get_resultados_por_concurso($concurso_id);

        $data['concurso'] = $concurso;
        $data['resultados'] = $resultados;
        $this->load->view('admin/ver_resultados', $data);
    }

    /**
     * Guardar foto y video de nominados finales
     */
    public function guardar_media()
    {
        $concurso_id = $this->input->post('concurso_id');
        $usuario_ids = $this->input->post('usuario_id');

        if (!$usuario_ids) {
            $this->session->set_flashdata('error', 'No se seleccionaron nominados.');
            redirect('admin/gestionar_nominados/' . $concurso_id);
        }

        foreach ($usuario_ids as $usuario_id) {
            $imagen_url = null;
            $video_url = null;

            // Subir foto
            if (!empty($_FILES['imagen_' . $usuario_id]['name'])) {
                $config['upload_path'] = './public/assets/fotos/';
                $config['allowed_types'] = 'png';
                $config['max_size'] = 5120;
                $config['encrypt_name'] = TRUE;

                $this->load->library('upload');
                $_FILES['file']['name'] = $_FILES['imagen_' . $usuario_id]['name'];
                $_FILES['file']['type'] = $_FILES['imagen_' . $usuario_id]['type'];
                $_FILES['file']['tmp_name'] = $_FILES['imagen_' . $usuario_id]['tmp_name'];
                $_FILES['file']['error'] = $_FILES['imagen_' . $usuario_id]['error'];
                $_FILES['file']['size'] = $_FILES['imagen_' . $usuario_id]['size'];

                $this->upload->initialize($config);
                if ($this->upload->do_upload('file')) {
                    $upload_data = $this->upload->data();
                    $imagen_url = '/assets/fotos/' . $upload_data['file_name'];
                } else {
                    $this->session->set_flashdata('error', 'Error en foto de ' . $this->get_nombre_usuario($usuario_id) . ': ' . $this->upload->display_errors());
                }
            }

            // Subir video
            if (!empty($_FILES['video_' . $usuario_id]['name'])) {
                $config['upload_path'] = './public/assets/videos/';
                $config['allowed_types'] = 'mp4';
                $config['max_size'] = 92160;
                $config['encrypt_name'] = TRUE;

                $this->load->library('upload');
                $_FILES['file']['name'] = $_FILES['video_' . $usuario_id]['name'];
                $_FILES['file']['type'] = $_FILES['video_' . $usuario_id]['type'];
                $_FILES['file']['tmp_name'] = $_FILES['video_' . $usuario_id]['tmp_name'];
                $_FILES['file']['error'] = $_FILES['video_' . $usuario_id]['error'];
                $_FILES['file']['size'] = $_FILES['video_' . $usuario_id]['size'];

                $this->upload->initialize($config);
                if ($this->upload->do_upload('file')) {
                    $upload_data = $this->upload->data();
                    $video_url = '/assets/videos/' . $upload_data['file_name'];
                } else {
                    $this->session->set_flashdata('error', 'Error en video de ' . $this->get_nombre_usuario($usuario_id) . ': ' . $this->upload->display_errors());
                }
            }

            // Guardar en la base de datos
            $this->Nominacion_model->update_media($concurso_id, $usuario_id, $imagen_url, $video_url);
        }

        $this->session->set_flashdata('success', 'Fotos y videos guardados correctamente.');
        redirect('admin/gestionar_nominados/' . $concurso_id);
    }

    /**
     * Mostrar pantalla para elegir ganadores
     */
    public function elegir_ganadores($concurso_id)
    {
        $concurso = $this->Concurso_model->get_by_id($concurso_id);
        if (!$concurso || $concurso['estado'] !== 'cerrado') {
            show_error('Este concurso no está cerrado o no existe.', 403);
        }

        // Nominados finales con votos
        $nominados = $this->Resultado_model->get_nominados_finales_con_votos($concurso_id);

        // Ganadores ya guardados (para cargar en la lista de abajo)
        $ganadores_guardados = $this->Resultado_model->get_ganadores_guardados($concurso_id);

        $data['concurso'] = $concurso;
        $data['nominados'] = $nominados;
        $data['ganadores_guardados'] = $ganadores_guardados;

        $this->load->view('admin/elegir_ganadores', $data);
    }

    /**
     * Guardar los resultados publicados
     */
    public function guardar_ganadores()
    {
        $concurso_id = $this->input->post('concurso_id');
        $ganadores_ids = $this->input->post('ganador_id') ?: [];
        $posiciones = $this->input->post('posicion') ?: [];

        if (empty($ganadores_ids)) {
            $this->session->set_flashdata('error', 'Debe seleccionar al menos un ganador.');
            redirect('admin/elegir_ganadores/' . $concurso_id);
        }

        if (count($ganadores_ids) !== count(array_unique($ganadores_ids))) {
            $this->session->set_flashdata('error', 'No se permiten ganadores duplicados.');
            redirect('admin/elegir_ganadores/' . $concurso_id);
        }

        $this->db->trans_start();
        $this->db->delete('resultados_publicados', ['concurso_id' => $concurso_id]);

        foreach ($ganadores_ids as $index => $usuario_id) {
            $data = [
                'concurso_id' => $concurso_id,
                'usuario_email' => $usuario_id,
                'posicion' => $posiciones[$index],
                'fecha_publicacion' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('resultados_publicados', $data);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() !== FALSE) {
            $this->session->set_flashdata('success', 'Ganadores guardados exitosamente.');
        } else {
            $this->session->set_flashdata('error', 'Error al guardar los ganadores.');
        }

        redirect('admin/index');
    }

    /**
     * Exportar todos los concursos a Excel
     */
    public function exportar_concursos()
    {
        $this->load->library('excel');
        $spreadsheet = $this->excel->getSpreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Listado de Concursos');

        // Encabezados
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Nombre del Concurso');
        $sheet->setCellValue('C1', 'Estado');
        $sheet->setCellValue('D1', 'Fecha de Creación');
        $sheet->setCellValue('E1', 'Total Nominaciones');

        // Datos
        $concursos = $this->Concurso_model->get_all_with_counts();
        $row = 2;
        foreach ($concursos as $concurso) {
            $sheet->setCellValue("A{$row}", $concurso['id']);
            $sheet->setCellValue("B{$row}", $concurso['titulo']);
            $sheet->setCellValue("C{$row}", ucfirst($concurso['estado']));
            $sheet->setCellValue("D{$row}", date('d/m/Y H:i', strtotime($concurso['fecha_creacion'])));
            $sheet->setCellValue("E{$row}", $concurso['total_nominaciones']);
            $row++;
        }

        // Estilo
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('E')->setWidth(20);

        $this->excel->save('Concursos_' . date('Ymd') . '.xlsx');
    }

    /**
     * Exportar detalles de un concurso a Excel
     */
    public function exportar_concurso_detalle($concurso_id)
    {
        $this->load->library('excel');
        $spreadsheet = $this->excel->getSpreadsheet();
        $sheet = $spreadsheet->getActiveSheet();


        $concurso = $this->Concurso_model->get_by_id($concurso_id);
        if (!$concurso) {
            show_error('Concurso no encontrado.');
        }

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Detalle Concurso');

        // Cabecera
        $sheet->setCellValue('A1', 'Nombre del Concurso');
        $sheet->setCellValue('B1', $concurso['titulo']);
        $sheet->setCellValue('A2', 'Descripción');
        $sheet->setCellValue('B2', $concurso['descripcion']);
        $sheet->setCellValue('A3', 'Estado');
        $sheet->setCellValue('B3', ucfirst($concurso['estado']));
        $sheet->setCellValue('A4', 'Fecha de Creación');
        $sheet->setCellValue('B4', date('d/m/Y H:i', strtotime($concurso['fecha_creacion'])));
        $sheet->setCellValue('A5', 'Creado por');
        $sheet->setCellValue('B5', $this->get_nombre_usuario($concurso['creado_por']));

        // Estilo cabecera
        $sheet->getStyle('A1:B5')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(50);

        $fila = 7;

        // Nominados Iniciales
        $nominados_iniciales = $this->Nominacion_model->get_nominados_iniciales($concurso_id);
        $sheet->setCellValue("A{$fila}", 'NOMINADOS INICIALES');
        $sheet->getStyle("A{$fila}")->getFont()->setBold(true);
        $fila += 2;

        $sheet->setCellValue("A{$fila}", 'Nro');
        $sheet->setCellValue("B{$fila}", 'Apellidos');
        $sheet->setCellValue("C{$fila}", 'Nombres');
        $sheet->setCellValue("D{$fila}", 'Provincia');
        $sheet->setCellValue("E{$fila}", 'Ciudad');
        $sheet->setCellValue("F{$fila}", 'Unidad');
        $fila++;

        foreach ($nominados_iniciales as $index => $nom) {
            $sheet->setCellValue("A{$fila}", $index + 1);
            $sheet->setCellValue("B{$fila}", $nom['apellidos'] ?? '');
            $sheet->setCellValue("C{$fila}", $nom['nombres'] ?? '');
            $sheet->setCellValue("D{$fila}", $nom['provincia'] ?? '');
            $sheet->setCellValue("E{$fila}", $nom['ciudad'] ?? '');
            $sheet->setCellValue("F{$fila}", $nom['unidad'] ?? '');
            $fila++;
        }

        $fila += 2;

        // Nominados Finales
        $nominados_finales = $this->Nominacion_model->get_nominados_finales($concurso_id);
        $sheet->setCellValue("A{$fila}", 'NOMINADOS FINALES');
        $sheet->getStyle("A{$fila}")->getFont()->setBold(true);
        $fila += 2;

        $sheet->setCellValue("A{$fila}", 'Nro');
        $sheet->setCellValue("B{$fila}", 'Apellidos');
        $sheet->setCellValue("C{$fila}", 'Nombres');
        $sheet->setCellValue("D{$fila}", 'Provincia');
        $sheet->setCellValue("E{$fila}", 'Ciudad');
        $sheet->setCellValue("F{$fila}", 'Unidad');
        $fila++;

        foreach ($nominados_finales as $index => $nom) {
            $sheet->setCellValue("A{$fila}", $index + 1);
            $sheet->setCellValue("B{$fila}", $nom['apellidos'] ?? '');
            $sheet->setCellValue("C{$fila}", $nom['nombres'] ?? '');
            $sheet->setCellValue("D{$fila}", $nom['provincia'] ?? '');
            $sheet->setCellValue("E{$fila}", $nom['ciudad'] ?? '');
            $sheet->setCellValue("F{$fila}", $nom['unidad'] ?? '');
            $fila++;
        }

        $fila += 2;

        // Ganadores
        $ganadores = $this->Resultado_model->get_ganadores_por_concurso($concurso_id);
        $sheet->setCellValue("A{$fila}", 'GANADORES');
        $sheet->getStyle("A{$fila}")->getFont()->setBold(true);
        $fila += 2;

        $sheet->setCellValue("A{$fila}", 'Nro');
        $sheet->setCellValue("B{$fila}", 'Apellidos');
        $sheet->setCellValue("C{$fila}", 'Nombres');
        $sheet->setCellValue("D{$fila}", 'Unidad');
        $sheet->setCellValue("E{$fila}", 'Ciudad');
        $sheet->setCellValue("F{$fila}", 'Posición');
        $fila++;

        foreach ($ganadores as $index => $g) {
            $sheet->setCellValue("A{$fila}", $index + 1);
            $sheet->setCellValue("B{$fila}", $g['apellidos'] ?? '');
            $sheet->setCellValue("C{$fila}", $g['nombres'] ?? '');
            $sheet->setCellValue("D{$fila}", $g['unidad'] ?? '');
            $sheet->setCellValue("E{$fila}", $g['ciudad'] ?? '');
            $sheet->setCellValue("F{$fila}", $g['posicion'] ?? '' . '°');
            $fila++;
        }

        $this->excel->save($spreadsheet,"Concurso_{$concurso['titulo']}_Detalle.xlsx");
    }

    // Método auxiliar
    private function get_nombre_usuario($id)
    {
        $usuario = $this->db->select('nombres, apellidos')->where('id', $id)->get('usuarios')->row();
        return $usuario ? $usuario->nombres . ' ' . $usuario->apellidos : 'Desconocido';
    }



}