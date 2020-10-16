<?php

namespace App\Models;

/**
 * @file - Model.php
 * @desc - Main class for extending for child class for all tables
 * @author  - Anu Kulshrestha <[<email address>]>
 * @updated on - 2020-09-18
 */
abstract class Model
{
    //properties for class and child class
    static protected $dbh; // one and only one and shared to child class
    protected $table;
    protected $key;

    /**
     * [init - initialize static variable of class with database object]
     * @param  PDO $dbh [Database object]
     */
    static public function init(\PDO $dbh)
    {
        self::$dbh=$dbh;
    }

    /**
     * [all - a generalize function that will be used by 
     * its child class for getting all records of a particular 
     * table name passed in here]
     * @return array [All records.]
     */
    public function all()
    {
        $query = "SELECT * FROM {$this->table}";
        $stmt = self::$dbh->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * [one - a generalize function that will be used by its
     * child class for getting one record for a particular id 
     * and particular table name passed in here]
     * @param  int $id [primary key or id of a record to fetch the record]
     * @return array     [record when found for the id passed to the query]
     */
    public function one($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE {$this->key} = :id";
        $stmt = self::$dbh->prepare($query);
        $params = array(':id' => $id);
        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }


    // abstract public function delete($id);
    // abstract public function create($array);
    // abstract public function save($array);
}