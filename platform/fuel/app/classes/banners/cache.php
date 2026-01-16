<?php

use Services\Logs\FileLoggerService;

class Banners_Cache
{

    /**
     * Cache directory for banners
     * @var string
     */
    public $cache_directory = '../platform/fuel/app/tmp/banners';

    /**
     * Current site language
     * @var string|null
     */
    private $lang = null;

    /**
     * Information about lottery
     * @var array|null
     */
    private $lottery = null;

    /**
     * Banner size
     * @var string|null
     */
    private $banner = null;

    /**
     * Color type
     * @var string|null
     */
    private $type = null;

    /**
     * Cached filename
     * @var string
     */
    private $filename;

    /**
     * Translations
     * @var string
     */
    private $translations;

    /**
     * Creates unique hash (for cached filename) based on the most important variables
     *
     * @return string
     */
    private function get_hash()
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $slug = '';
        if (isset($this->lottery) && isset($this->lottery['slug'])) {
            $slug = $this->lottery['slug'];
        } else {
            $fileLoggerService->error(
                "No lottery slug value is given!"
            );
        }
        
        $banner = '';
        if (!empty($this->banner)) {
            $banner = $this->banner;
        } else {
            ;           // Note: Should it be logged or not?
        }
        
        $lang = '';
        if (!empty($this->lang)) {
            $lang = $this->lang;
        } else {
            ;           // Note: Should it be logged or not?
        }
        
        $current_jackpot = '';
        if (isset($this->lottery) && isset($this->lottery['current_jackpot'])) {
            $current_jackpot = $this->lottery['current_jackpot'];
        } else {
            $fileLoggerService->warning(
                "No lottery current_jackpot value given!"
            );
        }
        
        $type = '';
        if (!empty($this->type)) {
            $type = $this->type;
        } else {
            ;           // Note: Should it be logged or not?
        }
        
        $nearest_jackpot = '';
        if (isset($this->translations) &&
            isset($this->translations['nearest_jackpot'])
        ) {
            $nearest_jackpot = $this->translations['nearest_jackpot'];
        } else {
            $fileLoggerService->error(
                "No nearest_jackpot value given for translations!"
            );
        }
        
        $play_now = '';
        if (isset($this->translations) &&
            isset($this->translations['play_now'])
        ) {
            $play_now = $this->translations['play_now'];
        } else {
            $fileLoggerService->error(
                "No play_now value given for translations!"
            );
        }
        
        return md5(
            $slug.
            $banner.
            $lang.
            $current_jackpot.
            $type.
            $nearest_jackpot.
            $play_now
        );
    }
    
    /**
     *
     * @return string
     */
    private function get_file_path(): string
    {
        $file_path = realpath($this->cache_directory) . '/' . $this->filename;
        
        return $file_path;
    }

    /** Checks if cache file exists and sets main variables */
    public function check(
        array $lottery = null,
        string $banner = null,
        string $lang = null,
        string $type = null,
        array $translations = null
    ): bool {
        // Set main variables + get hash
        $this->lang = $lang;
        $this->lottery = $lottery;
        $this->banner = $banner;
        $this->type = $type;
        $this->translations = $translations;

        $hash = $this->get_hash();
        $this->filename = $hash . '.jpg';

        $file_path = $this->get_file_path();
   
        // Check if cached file exists
        return file_exists($file_path);
    }

    /**
     * Reads cached file
     *
     * @return void
     */
    public function get_banner(): void
    {
        header('Content-Type: image/jpeg');

        $file_path = $this->get_file_path();

        readfile($file_path);
    }

    /**
     * Writes image to a file
     *
     * @param obj $imagick - imagick image object
     * @return void
     */
    public function write_image($imagick): void
    {
        // Write file
        $file_path = $this->get_file_path();

        $imagick->writeImage($file_path);
    }
}
