<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spreadsheet_autoloader {

    public function __construct()
    {
        spl_autoload_register(array($this, 'loader'));
    }

    private function loader($class)
    {
        // Solo carga clases de PhpOffice\PhpSpreadsheet
        if (strpos($class, 'PhpOffice\\PhpSpreadsheet\\') !== 0) {
            return;
        }

        $file = APPPATH . 'third_party/PhpSpreadsheet/src/' . str_replace('\\', '/', $class) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }
}