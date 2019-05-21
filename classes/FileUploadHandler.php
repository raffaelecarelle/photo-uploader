<?php

namespace App;

class FileUploadHandler
{
    private static $error_messages = array(
        'upload_max_filesize' => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        'no_files' => 'No file was uploaded',
        'accept_file_types' => 'Filetype not allowed',
        'no_destination_folder' => 'Missing a destination folder',
        'failed_to_write' => 'Failed to write file to disk',
        'post_max_size' => 'The uploaded file exceeds the post_max_size directive in php.ini',
        'max_file_size' => 'File is too big',
        'min_file_size' => 'File is too small',
        'abort' => 'File upload aborted',
        'image_resize' => 'Failed to resize image'
    );

    private static $successMessage = 'Upload complete successfully!';

    private static $typesAllowed = [
        'image/jpg',
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/bmp',
    ];

    private $pathToSaveFiles;

    private $createThumbs;

    private $files;

    private $errorsOccurred = array();

    public function __construct($pathToSaveFiles = '/photo-uploader/files/', bool $createThumbs = true)
    {
        $this->pathToSaveFiles = $pathToSaveFiles;
        $this->createThumbs = $createThumbs;
        var_dump(static::getBytesFromIniProperty('upload_max_filesize'));
    }

    /**
     * Initialize upload process
     */
    public function initialize()
    {
        $this->files = static::createFromGlobals($_FILES);

        if (empty($this->files)) {

            $this->errorsOccurred = static::$error_messages['no_files'];

            return false;

        }

        $this->startUpload();

        if (empty($this->errorsOccurred)) {

            return ['status' => 'success', 'message' => static::$successMessage];

        }

        return $this->errorsOccurred;
    }

    public function startUpload()
    {
        /** @var File $file */
        foreach ($this->files as $file) {

            if ($this->validate($file)) {

                return $file->save($this->pathToSave);

            }
        }
    }

    public function validate(File $file): bool
    {
        if ($file->getSize() >= static::getBytesFromIniProperty('upload_max_filesize')) {

            $this->errorsOccurred[] = static::$error_messages['upload_max_filesize'];

        }

        if (\in_array($file->getType(), static::$typesAllowed)) {

            $this->errorsOccurred[] = static::$error_messages['accept_file_types'];

        }

        if (!\is_dir($this->pathToSaveFiles)){

            $this->errorsOccurred[] = static::$error_messages['no_destination_folder'];

        }

        if (!\is_dir($_SERVER['DOCUMENT_ROOT'] . $this->pathToSaveFiles)){

            $this->errorsOccurred[] = static::$error_messages['no_destination_folder'];

        }

        if (!\is_writable($_SERVER['DOCUMENT_ROOT'] . $this->pathToSaveFiles)){

            $this->errorsOccurred[] = static::$error_messages['failed_to_write'];

        }
    }

    public static function getBytesFromIniProperty(string $property): int
    {
        $property = trim($property);
        $stringValue = \ini_get($property);
        $intValue = (int)$stringValue;
        $lastChr = strtolower($stringValue[strlen($stringValue) - 1]);
        switch ($lastChr) {
            case 'g':
                $intValue *= (1024 * 1024 * 1024);
                break;
            case 'm':
                $intValue *= (1024 * 1024);
                break;
            case 'k':
                $intValue *= 1024;
                break;
        }

        return $intValue;
    }

    /**
     * Create instance files from PHP global variable $_FILES
     */
    public static function createFromGlobals(): array
    {
        $files = array();

        foreach ($_FILES as $globalFile) {
            $file = new File(
                $globalFile['name'],
                $globalFile['type'],
                $globalFile['size'],
                $globalFile['tmp_name'],
                $globalFile['error']
            );

            $files[] = $file;
        }

        return $files;
    }

}