<?php

use Services\Logs\FileLoggerService;

class Model_Lottery_Source extends \Fuel\Core\Model_Crud
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'lottery_source';
    
    /**
     *
     * @return array
     */
    public static function get_sources()
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $sources = [];
        $results = [];
        
        $query = "SELECT * 
            FROM lottery_source 
            ORDER BY id";
        
        $db = DB::query($query);
        
        try {
            $results = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error($e->getMessage());
        }

        foreach ($results as $item) {
            $sources[$item['id']] = [$item['name'], $item['website']];
        }

        return $sources;
    }
}
