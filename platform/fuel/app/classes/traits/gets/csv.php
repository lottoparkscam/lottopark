<?php

/**
 * This trait allow to export CSV.
 * @author Michal Kowalczyk <michal.kowalczyk at gg.international>
 */
trait Traits_Gets_Csv
{

    /**
     * EXPORT CSV
     * @param string $filename the beginning of the file name
     * @param array $headers headers for csv
     * @param array $data data for csv
     * @return file
     */
    protected function get_csv_export(string $filename, array $headers, array $data)
    {
        //Generate filename based on short name and date
        $dt = new DateTime("now", new DateTimeZone("UTC"));
        $filename = $filename."_" . $dt->format("Y_m_d-H_i_s") . ".csv";

        //header('Content-Type: text/csv; charset=utf-8');
        header('Content-Encoding: UTF-8');
        header('Content-type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        echo "\xEF\xBB\xBF"; // UTF-8 BOM

        $output = fopen('php://output', 'w');
        
        // Put headers
        fputcsv($output, $headers);
        
        // Put each array row as csv row
        foreach ($data as $id => $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit();
    }
}
