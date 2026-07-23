<?php

namespace App\Imports;

use App\Models\GradeConfig;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GradeConfigImport implements ToModel, WithHeadingRow
{
    /**
     * Map each row from the Excel file to a GradeConfig model.
     * Expected columns in Excel/CSV: freq_min, freq_max, grade_name, hex_color, score
     */
    public function model(array $row)
    {
        // Clean and validate values
        return new GradeConfig([
            'freq_min'   => floatval($row['freq_min'] ?? 0),
            'freq_max'   => floatval($row['freq_max'] ?? 0),
            'grade_name' => strval($row['grade_name'] ?? 'Unknown'),
            'hex_color'  => strval($row['hex_color'] ?? '#CCCCCC'),
            'score'      => intval($row['score'] ?? 0),
        ]);
    }
}
