<?php

declare(strict_types=1);

namespace App\services;

class FileUploadService
{
    private const UPLOAD_DIR = __DIR__ . '/../../public/uploads/';
    private const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2 Mo
    private const ALLOWED_MIME_TYPES = [
    'image/jpg' => 'jpg',
    'image/jpeg' => 'jpg',
    'image/png' => 'png',   
    'image/gif' => 'gif',
    ];
    

    public static function handleAvatarUpload(array $file, $upload_dir = self::UPLOAD_DIR, $max_file_zise = self::MAX_FILE_SIZE, $allowed_mime_type = self::ALLOWED_MIME_TYPES): string
    {
        if (
            !isset($file['error'], $file['tmp_name'], $file['name'], $file['size'], $file['type']) ||
            $file['error'] !== UPLOAD_ERR_OK
        ) {
            throw new \RuntimeException("Erreur lors de l'upload du fichier.");
        }

        if ($file['size'] > $max_file_zise) {
            throw new \RuntimeException("Fichier trop volumineux (max 2 Mo).");
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!array_key_exists($mimeType, $allowed_mime_type)) {
            throw new \RuntimeException("Type de fichier non autorisé : $mimeType");
        }

        $extension = $allowed_mime_type[$mimeType];
        // construction du filename => stockage BDD
        $safeName = uniqid($file['name'], true) . '.' . $extension;

        // Crée le dossier s'il n'existe pas
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0775, true);
        }

        $destination = $upload_dir . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new \RuntimeException("Échec lors du déplacement du fichier.");
        }

        // Retourner le chemin relatif pour la base de données
        return $safeName;
    }

     /**
     * Supprime un ancien avatar
     * @param string $filename Le nom du fichier à supprimer
     * @return bool True si le fichier a été supprimé, false sinon
     */
    public static function deleteOldAvatar(string $filename, $upload_dir = self::UPLOAD_DIR): bool
    {
        $filepath = $upload_dir . $filename;
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    }

}
