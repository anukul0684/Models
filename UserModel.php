<?php

namespace App\Models;

/**
 * @file - UserModel.php
 * @desc - Child class extended from class Model
 * @author  - Anu Kulshrestha <[<email address>]>
 * @updated on - 2020-09-30
 */
class UserModel extends Model
{
    // assigning the table name and id to the properties of this class
    protected $table='user_registration';
    protected $key='id';

    public function all()
    {
        $query = "SELECT * FROM {$this->table} where user_type<>'admin' order by first_name ASC";
        $stmt = self::$dbh->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * [saveUser - save details from user_registration page into database]
     * @param  string $user [$_POST values]
     * @return integer lastInsertId  [primary key of the record]
     */
    public function saveUser($user) :int
    {
        $query = "Insert Into user_registration
                    (
                      first_name,
                      last_name,
                      street_details,
                      city_name,
                      province_name,
                      country_name,
                      postal_code, 
                      email,
                      phone,
                      user_type,
                      user_password,
                      created_at,
                      user_active
                    )
                  Values
                    (
                      :first_name, 
                      :last_name, 
                      :street_details, 
                      :city_name, 
                      :province_name, 
                      :country_name, 
                      :postal_code, 
                      :email, 
                      :phone, 
                      :user_type, 
                      :user_password, 
                      NOW(),
                      :user_active
                    )"; 
        //place holders beginning with :
         
        $stmt = self::$dbh->prepare($query);
        
        $params = array(
            ':first_name'=> $user['first_name'],
            ':last_name'=> $user['last_name'],
            ':street_details'=> $user['street_details'],
            ':city_name'=> $user['city_name'],
            ':province_name'=> $user['province_name'],
            ':country_name'=> $user['country_name'],
            ':postal_code' => $user['postal_code'],
            ':email' => $user['email'],
            ':phone' => $user['phone'],       
            ':user_type' => 'customer',
            ':user_password' => password_hash($user['user_password'], PASSWORD_DEFAULT),
            ':user_active' => 'Yes'
        );
        
        $stmt->execute($params);

        return self::$dbh->lastInsertID();       
    }


    /**
     * [getUser - get user details for profile page]
     * @param  int $userid [user id]
     * @param  string $dbh [database connection variable]
     * @return array [details of user from 4 tables user_registration, 
     *                country_names, province names, city_names]
     */
    public function one($userid) :array
    {        
        $query = 'SELECT 
                    u.id,
                    u.user_type,
                    u.first_name as "First Name",
                    u.last_name as "Last Name",
                    u.email as "Email",
                    u.phone as "Mobile",
                    u.street_details as "Address",
                    u.postal_code as "Postal Code",
                    u.city_name as City,
                    u.province_name as Province,
                    u.country_name as Country,
                    u.created_at as "Registered On",
                    IFNULL(u.updated_at,"NOT_UPDATED_YET") as "Updated On",
                    u.user_password as "User Password"           
                    FROM 
                    user_registration u
                    WHERE 
                    u.id = :userid';

        $stmt = self::$dbh->prepare($query);

        $params = array(
            ':userid' => $userid
        );
        
        $stmt->execute($params);
        
        //fetch single result as an associative array
        return $stmt->fetch(\PDO::FETCH_ASSOC);        
    }


    /**
     * [checkUser - get details from user_registration page for email id entered]
     * @param  string $email [email id entered on login page]
     * @return array [details of user]
     */
    public function checkUser($email)
    {
        $query = 'SELECT * FROM user_registration WHERE email=:email';
        $stmt = self::$dbh->prepare($query);
        $params = array(
            ':email'=> $email
        );
        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // public function delete($id)
    // {

    // }

    // public function create($array)
    // {

    // }

    // public function save($array)
    // {
        
    // }
}