<?php

    /**
     * @file UserModelFunctions.php
     * @author  Anu Kulshrestha <[<kulshrestha-a@webmail.uwinnipeg.ca>]>
     * @updated 2020-09-08
     */
     

    /**
     * [saveUser - save details from user_registration page into database]
     * @param  string $user [$_POST values]
     * @param  string $dbh  [database connection variable]
     * @return integer lastInsertId  [primary key of the record]
     */
    function saveUser($user, $dbh) :int
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
                      updated_at
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
                      NOW()
                    )"; 
        //place holders beginning with :
         
        $stmt = $dbh->prepare($query);
        
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
            ':user_password' => password_hash($user['user_password'], PASSWORD_DEFAULT)
        );
        
        $stmt->execute($params);

        return $dbh->lastInsertID();       
    }

    /**
     * [getUser - get user details for profile page]
     * @param  int $userid [user id]
     * @param  string $dbh [database connection variable]
     * @return array [details of user from 4 tables user_registration, 
     *                country_names, province names, city_names]
     */
    function getUser($userid,$dbh) :array
    {        
        $query = 'SELECT 
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
                    u.updated_at as "Updated On",
                    u.user_password as "User Password"           
                    FROM 
                    user_registration u
                    WHERE 
                    u.id = :userid';

        $stmt = $dbh->prepare($query);

        $params = array(
            ':userid' => $userid
        );
        
        $stmt->execute($params);
        
        //fetch single result as an associative array
        return $stmt->fetch(PDO::FETCH_ASSOC);        
    }


    /**
     * [checkUser - get details from user_registration page for email id entered]
     * @param  string $email [email id entered on login page]
     * @param  PDO $dbh   [database connection variable]
     * @return array [details of user]
     */
    function checkUser($email,$dbh)
    {
        $query = 'SELECT * FROM user_registration WHERE email=:email';
        $stmt = $dbh->prepare($query);
        $params = array(
            ':email'=> $email
        );
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    
