<?php declare(strict_types=1);

return [
    'app' => [
        'title' => 'Compartir Archivos',
        'upload' => 'Subir',
        'select_file' => 'Seleccionar un archivo',
        'drag_drop' => 'Arrastra y suelta un archivo aquí o haz clic para seleccionar',
        'uploading' => 'Subiendo...',
        'upload_success' => 'Archivo subido exitosamente',
        'file_url' => 'URL del archivo',
        'copy_link' => 'Copiar enlace',
        'delete' => 'Eliminar',
        'confirm_delete' => '¿Estás seguro de que quieres eliminar este archivo?',
        'my_files' => 'Mis archivos',
        'no_files' => 'No hay archivos subidos',
        'language' => 'Idioma',
        'change_language' => 'Cambiar idioma'
    ],
    'error' => [
        'csrf_invalid' => 'Token CSRF inválido',
        'file_not_sent' => 'No se envió ningún archivo.',
        'file_too_large' => 'El archivo es demasiado grande (máx {size})',
        'file_not_found' => 'Archivo no encontrado',
        'unauthorized' => 'No tienes autorización para eliminar este archivo.',
        'database_error' => 'Archivo no encontrado en la base de datos.',
        'upload_failed' => 'Falló la subida',
        'network_error' => 'Error de red',
        'method_not_allowed' => 'Método no permitido',
        'not_found' => 'No encontrado'
    ],
    'success' => [
        'upload_complete' => 'Subida completada exitosamente',
        'file_deleted' => 'Archivo eliminado exitosamente',
        'link_copied' => 'Enlace copiado al portapapeles'
    ],
    'security' => [
        'report_title' => 'Informe de Seguridad',
        'directory_permissions' => 'Permisos de Directorio',
        'file_access_security' => 'Seguridad de Acceso a Archivos',
        'upload_security' => 'Seguridad de Subida',
        'directory_traversal' => 'Traversal de Directorio',
        'php_files_outside_public' => 'Archivos PHP fuera de público',
        'config_files_accessible' => 'Archivos de configuración accesibles',
        'htaccess_protection' => 'Protección .htaccess',
        'php_execution_blocked' => 'Ejecución PHP bloqueada',
        'safety_status' => 'Estado de seguridad',
        'protected' => 'Protegido',
        'accessible' => 'Accesible',
        'enabled' => 'Habilitado',
        'missing' => 'Faltante',
        'blocked' => 'Bloqueado',
        'allowed' => 'Permitido',
        'safe' => 'Seguro',
        'vulnerable' => 'Vulnerable'
    ]
];