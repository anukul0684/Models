<?php

namespace App\Models;

/**
 * @file - LogModel.php
 * @desc - Child class extended from class Model
 * @author  - Anu Kulshrestha <[<email address>]>
 * @updated on - 2020-09-30
 */
class LogModel extends Model
{
    // assigning the table name and id to the properties of this class
    protected $table='log';
    protected $key='id';

    /**
     * [save_log save log details into the table]
     * @param  [string] $event [string made to save details as log]
     * @return [int]        [id of inserted row]
     */
    public function save_log($event)
    {
        $query = "INSERT INTO log(event,created_at) VALUES(:event,NOW())";

        $stmt = self::$dbh->prepare($query);

        $params = array(
            ':event' => $event
        );

        $stmt->execute($params);

        return self::$dbh->lastInsertID();
    }

    /**
     * [showLog function for Admin Dashboard to view last 10 log entries]
     * @return [array] [query output]
     */
    public function showLog()
    {
        $query = "SELECT * 
                    FROM log
                    Order By id DESC 
                    Limit 10";
          $stmt = self::$dbh->prepare($query);
          $stmt->execute();
          return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}