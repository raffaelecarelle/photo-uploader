<?php

namespace App;

class File
{
    /**
     * Nome del file
     */
    private $filename;

    /**
     * Tipo di file (in formato MIME type)
     */
    private $type;

    /**
     * Dimensione del file
     */
    private $size;

    /**
     * Percorso e il nome del file temporaneo sul server
     */
    private $tmpname;

    /**
     * Un codice numerico compreso fra 0 e 8 indicante il tipo di errore che si è verificato, pari a 0 in assenza di errore.
     */
    private $error;

    public function __construct(string $filename, string $type, int $size, string $tmpname, int $error)
    {
        $this->filename = $filename;
        $this->type = $type;
        $this->size = $size;
        $this->tmpname = $tmpname;
        $this->error = $error;
    }

    /**
     * Save file on given path
     * @throws Exception
     */
    public function save(string $path): bool
    {
        if (!move_uploaded_file($this->filename, $path)) {
            throw new \Exception('The file could not be uploaded.');
        }

        return true;
    }

    /**
     * Delete file on given path
     * @throws Exception
     */
    public function delete(string $path): bool
    {
        if (!unlink($path)) {
            throw new \Exception('The file could not be deleted.');
        }

        return true;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getTmpname(): string
    {
        return $this->tmpname;
    }

    public function getError(): int
    {
        return $this->error;
    }
}