<?php
/*
Plugin Name: Adjust Logo Size
Plugin URI: https://github.com/chochue/adjust-logo-size.git
Description: Ajusta el tamaño del logo al subirlo, manteniendo la proporción y añadiendo relleno blanco si es necesario.
Version: 1.1.0
Author: LSC Josue Arturo Crisanto Luna
Author URI: https://tusitio.com
GitHub Plugin URI: https://github.com/chochue/adjust-logo-size.git
*/


function resize_logo_image($file_path) {
    // Verificar si el archivo existe
    if (!file_exists($file_path)) {
        error_log("El archivo no existe: $file_path");
        return false;
    }

    // Verificar que el archivo sea una imagen válida
    $image_info = getimagesize($file_path);
    if (!$image_info) {
        error_log("No se pudo obtener información de la imagen: $file_path");
        return false;
    }

    // Validar MIME tipo
    $mime_type = $image_info['mime'];
    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($mime_type, $allowed_mime_types)) {
        error_log("Tipo MIME no soportado: $mime_type");
        return false;
    }

    // Obtener las dimensiones de la imagen
    $width = $image_info[0];
    $height = $image_info[1];

    // Crear imagen a partir del archivo original
    switch ($mime_type) {
        case 'image/jpeg':
            $src = imagecreatefromjpeg($file_path);
            break;
        case 'image/png':
            $src = imagecreatefrompng($file_path);
            break;
        case 'image/gif':
            $src = imagecreatefromgif($file_path);
            break;
        default:
            return false;
    }

    $new_width = 400;  // Ancho final deseado
    $new_height = 400; // Alto final deseado

    // Crear imagen de destino con fondo blanco
    $dst = imagecreatetruecolor($new_width, $new_height);
    $white = imagecolorallocate($dst, 255, 255, 255);
    imagefill($dst, 0, 0, $white);

    // Calcular nuevas dimensiones manteniendo la proporción
    $scale = min($new_width / $width, $new_height / $height);
    $resized_width = round($width * $scale);
    $resized_height = round($height * $scale);
    $dst_x = round(($new_width - $resized_width) / 2);
    $dst_y = round(($new_height - $resized_height) / 2);

    // Copiar y redimensionar la imagen original al lienzo con fondo blanco
    imagecopyresampled($dst, $src, $dst_x, $dst_y, 0, 0, $resized_width, $resized_height, $width, $height);

    // Guardar la imagen redimensionada
    $upload_dir = wp_upload_dir();
    $file_path_resized = $upload_dir['path'] . '/resized_' . basename($file_path);

    $save_result = false;
    switch ($mime_type) {
        case 'image/jpeg':
            $save_result = imagejpeg($dst, $file_path_resized, 100);
            break;
        case 'image/png':
            $save_result = imagepng($dst, $file_path_resized);
            break;
        case 'image/gif':
            $save_result = imagegif($dst, $file_path_resized);
            break;
    }

    // Liberar memoria
    imagedestroy($src);
    imagedestroy($dst);

    // Verificar que la imagen se haya guardado correctamente
    if (!$save_result || !file_exists($file_path_resized)) {
        error_log("No se pudo crear o guardar la imagen redimensionada: $file_path_resized");
        return false;
    }

    return $file_path_resized;
}