<?php

namespace App;

use Exception;

class Employee
{
    public static function list()
    {
        global $conn;

        try {
            $sql = "SELECT * FROM employees LIMIT 5";

            $statement = $conn->prepare($sql);
            $statement->execute();
            $records = [];

            while ($row = $statement->fetch()) {
                array_push($records, $row);
            }

            return $records;
        } catch (Exception $e) {
            error_log($e->getMessage());
        }

        return null;
    }
}