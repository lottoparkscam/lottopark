<?php

use Services\Logs\FileLoggerService;

/**
 * File reader decoding csv files into array of assoc arrays.
 */
final class Services_File_Reader
{
    /**
     *
     * @var array $file
     */
    private $file = null;

    /** @var int */
    private $delimiter;

    private array $headers;
    private FileLoggerService $fileLoggerService;

    /** @param array $headers */
    public function __construct($file, $headers, string $delimiter)
    {
        $this->file = $file;
        $this->headers = $headers;
        $this->delimiter = $delimiter;
        $this->fileLoggerService = Container::get(FileLoggerService::class);
    }

    /**
     *
     * @return array|bool
     */
    public function check_csv()
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $this->file['tmp_name']);
        finfo_close($finfo);

        $allowed_mime = [
            'text/x-comma-separated-values',
            'text/comma-separated-values',
            'application/octet-stream',
            'application/vnd.ms-excel',
            'application/x-csv',
            'text/x-csv',
            'text/csv',
            'application/csv',
            'application/excel',
            'application/vnd.msexcel',
            'text/plain'
        ];
                    
        if (in_array($mime, $allowed_mime) && is_uploaded_file($this->file['tmp_name'])) {
            return $this->read_csv();
        } else {
            return false;
        }
    }

    /**
     *
     * @return array|bool
     */
    public function read_csv()
    {
        set_time_limit(600);
        
        $data = [];
        $limit = 100;
        $file_path = $this->file['tmp_name'];

        try {
            $file_handler = fopen($file_path, "r");
            if ($file_handler === false) {
                return false;
            }

            fgetcsv($file_handler, 300, $this->delimiter);
            $offset = ftell($file_handler);
            while (!feof($file_handler)) {
                fseek($file_handler, $offset);
                $i = 0;
                while (($current_row = fgetcsv($file_handler, 300, $this->delimiter)) !== false) {
                    $i++;
                    $trimmed_row = array_map('trim', $current_row);
                    $new_row = array_combine($this->headers, $trimmed_row);
                    array_push($data, $new_row);

                    if ($i >= $limit) {
                        $offset = ftell($file_handler);
                        break;
                    }
                }
            }
            fclose($file_handler);

            return $data;
        } catch (\Throwable $e) {
            $this->fileLoggerService->error(
                $e->getMessage()
            );
            return false;
        }
    }
}
