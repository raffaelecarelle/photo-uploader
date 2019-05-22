<?php

namespace App;

use Gumlet\ImageResize;

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
        'image/png'
    ];

    private $pathToSaveFiles;

    private $createVersions;

    private $files;

    private $errorsOccurred = array();

    private static $photoVersions = [
        'thumb' => '50x50',
        'small' => '150x150',
        'medium' => '767x767',
        'large' => '1024x768'
    ];

    public function __construct($pathToSaveFiles = '/photos/', $createVersions = true)
    {
        $this->pathToSaveFiles = $pathToSaveFiles;
        $this->createVersions = $createVersions;

        if(!file_exists($_SERVER['DOCUMENT_ROOT'] . $pathToSaveFiles)) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . $pathToSaveFiles, 0777, true);
        }
    }

    /**
     * Initialize upload process
     */
    public function initialize()
    {
        $this->files = static::createFromGlobals($_FILES);

        if (empty($this->files)) {

            return static::$error_messages['no_files'];

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

                $file->save($_SERVER['DOCUMENT_ROOT'] . $this->pathToSaveFiles);

                if ($this->createVersions) {

                    $this->createVersions($file);

                }

            }

        }

    }

    public function validate(File $file): bool
    {
        if ($file->getSize() >= static::getBytesFromIniProperty('upload_max_filesize')) {

            $this->errorsOccurred[$file->getId()] = static::$error_messages['upload_max_filesize'];

        }

        if (!\in_array($file->getType(), static::$typesAllowed)) {

            $this->errorsOccurred[$file->getId()] = static::$error_messages['accept_file_types'];

        }

        if (!\is_dir($_SERVER['DOCUMENT_ROOT'] . $this->pathToSaveFiles)) {

            $this->errorsOccurred[$file->getId()] = static::$error_messages['no_destination_folder'];

        }

        if (!\is_writable($_SERVER['DOCUMENT_ROOT'] . $this->pathToSaveFiles)) {

            $this->errorsOccurred[$file->getId()] = static::$error_messages['failed_to_write'];

        }

        if (empty($this->errorsOccurred)) {
            return true;
        }

        return false;

    }

    public function createVersions(File $file)
    {
        foreach (static::$photoVersions as $key => $value) {

            $resizer = new ImageResize($_SERVER['DOCUMENT_ROOT'] . $this->pathToSaveFiles . $file->getFilename());

            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $this->pathToSaveFiles . $key)) {
                mkdir($_SERVER['DOCUMENT_ROOT'] . $this->pathToSaveFiles . $key, 0777, true);
            }

            $resizer->resize(explode('x', $value)[0], explode('x', $value)[1], true);
            $resizer->save($_SERVER['DOCUMENT_ROOT'] . $this->pathToSaveFiles . $key . '/' . $file->getFilename());
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

        foreach ($_FILES as $id => $globalFile) {

            $file = new File((int)$id, $globalFile['name'], $globalFile['type'], $globalFile['size'], $globalFile['tmp_name'],
                $globalFile['error']);

            $files[] = $file;
        }

        return $files;
    }

}
