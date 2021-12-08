<?php

class BulkUpdateController
{   
    /**
     * prepareUpdate
     *
     * All rows must have a fixed amount of columns and all of them must have a value.
     *
     * They must have a column called "id" (you can edit it to your needs).
     *
     * For MS SQL and MySQL.
     * @param  array $array: e.g. [[columnname => value, columnname2 => value2], [columnname => value, columnname2 => value2]]
     * @param  string $table: name of table to update
     * @return string: SQL string to perform the Bulk Update
     */
    public function prepareUpdate(array $array, string $table): string
    {
        $columnname = null;
        $columnNumber = count($array[0]);
        $sqld = 'UPDATE ' . $table . ' SET ';
        $amount = count($array) * $columnNumber * $columnNumber;
        $counter = 0;
        $index = 0;
        $columns = [];
        $idsarray = [];
        while ($amount !== $counter) {
            foreach ($array as $value) {
                $id = 0;
                $columns = array_keys($value);
                foreach ($value as $key => $nested) {
                    if ($key === 'id') {
                        $id = $nested;
                        if (count($idsarray) !== count($array)) {
                            array_push($idsarray, $nested);
                        }
                    } else {
                        if (is_null($columnname)) {
                            $columnname = $columns[$index];
                            if ($columnname !== 'id') {
                                $sqld .= $columnname;
                                $sqld .= ' = CASE';
                            }
                        }
                        if ($columnname === $key) {
                            $sqld .= ' WHEN id = ' . $id . ' THEN ' . $this->checkValue($nested) . ' ';
                        }
                    }
                    $counter++;
                }
            }
            if ($columnname !== 'id') {
                if ($index === $columnNumber - 1) {
                    $sqld .= 'ELSE ' . $columns[$index] . ' END ';
                    $index++;
                } else {
                    $sqld .= 'ELSE ' . $columns[$index] . ' END, ';
                    $index++;
                    $columnname = null;
                }
            } else {
                $columnname = null;
                $index++;
            }
        }
        $sqld .= 'WHERE id IN (';
        foreach ($idsarray as $key => $value) {
            if ($key === count($idsarray) - 1) {
                $sqld .= $value . '';
            } else {
                $sqld .= $value . ', ';
            }
        }
        $sqld .= ');';
        return ($sqld);
    }
    
    // Function to show some values the SQL needs explicitly e.g null, true, false and apostrophe (for strings)
    private function checkValue($value): string
    {
        if (is_string($value)) {
            return '\'' . $value . '\'';
        }
        if (is_bool($value)) {
            if ($value) {
                return 'true';
            } else {
                return 'false';
            }
        }
        if (is_null($value)) {
            return 'null';
        }
        return $value;
    }
}
