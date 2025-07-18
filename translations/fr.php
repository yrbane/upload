<?php declare(strict_types=1);

return [
    'app' => [
        'title' => 'Partage de Fichiers',
        'upload' => 'Téléverser',
        'select_file' => 'Sélectionner un fichier',
        'drag_drop' => 'Glissez et déposez un fichier ici ou cliquez pour sélectionner',
        'uploading' => 'Téléversement en cours...',
        'upload_success' => 'Fichier téléversé avec succès',
        'file_url' => 'URL du fichier',
        'copy_link' => 'Copier le lien',
        'delete' => 'Supprimer',
        'confirm_delete' => 'Êtes-vous sûr de vouloir supprimer ce fichier ?',
        'my_files' => 'Mes fichiers',
        'no_files' => 'Aucun fichier téléversé',
        'language' => 'Langue',
        'change_language' => 'Changer de langue',
        'uploaded_label' => 'Envoyé',
        'error_deleting' => 'Erreur lors de la suppression du fichier',
        'picsum_link' => 'Images libres par Picsum',
        'github_link' => 'Voir sur GitHub'
    ],
    'error' => [
        'csrf_invalid' => 'Token CSRF invalide',
        'file_not_sent' => 'Aucun fichier envoyé.',
        'file_too_large' => 'Le fichier est trop volumineux (max {size})',
        'file_not_found' => 'Fichier non trouvé',
        'unauthorized' => 'Vous n\'êtes pas autorisé à supprimer ce fichier.',
        'database_error' => 'Fichier non trouvé dans la base de données.',
        'upload_failed' => 'Échec du téléversement',
        'network_error' => 'Erreur réseau',
        'method_not_allowed' => 'Méthode non autorisée',
        'not_found' => 'Non trouvé'
    ],
    'success' => [
        'upload_complete' => 'Téléversement terminé avec succès',
        'file_deleted' => 'Fichier supprimé avec succès',
        'link_copied' => 'Lien copié dans le presse-papiers'
    ],
    'security' => [
        'report_title' => 'Rapport de Sécurité',
        'directory_permissions' => 'Permissions des Dossiers',
        'file_access_security' => 'Sécurité d\'Accès aux Fichiers',
        'upload_security' => 'Sécurité des Téléversements',
        'directory_traversal' => 'Traversée de Répertoire',
        'php_files_outside_public' => 'Fichiers PHP hors public',
        'config_files_accessible' => 'Fichiers de config accessibles',
        'htaccess_protection' => 'Protection .htaccess',
        'php_execution_blocked' => 'Exécution PHP bloquée',
        'safety_status' => 'Statut de sécurité',
        'protected' => 'Protégé',
        'accessible' => 'Accessible',
        'enabled' => 'Activé',
        'missing' => 'Manquant',
        'blocked' => 'Bloqué',
        'allowed' => 'Autorisé',
        'safe' => 'Sûr',
        'vulnerable' => 'Vulnérable'
    ]
];