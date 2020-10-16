<?php

namespace App\Models;

/**
 * @file - ServiceModel.php
 * @desc - Child class extended from class Model
 * @author  - Anu Kulshrestha <[<email address>]>
 * @updated on - 2020-09-30
 */


class ServiceModel extends Model
{
    // assigning the table name and id to the properties of this class
    protected $table='services';
    protected $key='service_id';
    
    /**
     * [all function for service list on Admin Service List view Page]
     * @return [array] [query output]
     */
    public function all()
    {
        $query = "SELECT * FROM {$this->table} order by service_category ASC";
        $stmt = self::$dbh->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * [resultsPerPage function to get service list page wise]
     * @param  [int] $start_result [starting record in table]
     * @return [array]               [query output]
     */
    public function resultsPerPage($start_result)
    {
        $start_result = ($start_result-1)*MAXRESULTSPERPAGE;        
        $query = "SELECT * FROM services 
                    where service_active = 'Yes' 
                    order by service_category ASC
                    LIMIT :start_result, :end_result";
        $stmt = self::$dbh->prepare($query);       
        $stmt->bindValue(':start_result', (int) $start_result, \PDO::PARAM_INT);
        $stmt->bindValue(':end_result', (int) MAXRESULTSPERPAGE, \PDO::PARAM_INT);        
        $stmt->execute();        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * [getNumLinks function to get the total active services count 
     * and then divide into number of pages as per constant defined on index page]
     * @return [int] [total pages on pagination bar]
     */
    public function getNumLinks()
    {
        $query = "SELECT count(*) as num FROM services where service_active = 'Yes'";
        $stmt = self::$dbh->prepare($query);
        $stmt->execute();
        $total = $stmt->fetch(\PDO::FETCH_ASSOC)['num'];
        return ceil($total/MAXRESULTSPERPAGE);
    }

    /**
     * [getCategory function to get categories of Services in table for Category Menu]
     * @return [array] [query output]
     */
    public function getCategory()
    {
        $query = "SELECT service_category, count(service_category) 
                    FROM services 
                    where service_active = 'Yes'
                    Group By service_category
                    order by service_category ASC";
        $stmt = self::$dbh->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    /**
     * [getCategoryServices function to get service list category wise]
     * @param  [string] $category     [name of category]
     * @param  [int] $start_result [starting number of record in table]
     * @return [array]               [query output]
     */
    public function getCategoryServices($category,$start_result)
    {
        $start_result = ($start_result-1)*MAXRESULTSPERPAGE;
        
        if($category!='') {
            $query = "SELECT * FROM services 
                        where service_category=:service_category 
                        and service_active = 'Yes'
                        order by service_name ASC
                        LIMIT :start_result, :end_result";
            $stmt = self::$dbh->prepare($query);  
            $stmt->bindValue(':service_category', (string) $category, \PDO::PARAM_STR);   
            $stmt->bindValue(':start_result', (int) $start_result, \PDO::PARAM_INT);
            $stmt->bindValue(':end_result', (int) MAXRESULTSPERPAGE, \PDO::PARAM_INT);            
            $stmt->execute();
            //var_dump($stmt);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
    }

    /**
     * [getNumCategoryLinks function to get 
     * number of pages for category wise service list view]
     * @param  [string] $category [category name]
     * @return [int]           [Total number of pages on Pagination bar]
     */
    public function getNumCategoryLinks($category)
    {
        $query = "SELECT count(*) as num FROM services 
                    where service_category=:service_category and 
                            service_active = 'Yes'";
        $stmt = self::$dbh->prepare($query);
        $stmt->bindValue(':service_category', (string) $category, \PDO::PARAM_STR);
        $stmt->execute();
        $total = $stmt->fetch(\PDO::FETCH_ASSOC)['num'];
        return ceil($total/MAXRESULTSPERPAGE);
    }

    /**
     * [getOtherServices function to get other services of same category 
     * to display on service details page for user to know what else is there
     * in particular category of a chosen service on detail view]
     * @param  [int] $id       [service id]
     * @param  [string] $category [service category]
     * @return [array]           [query output]
     */
    public function getOtherServices($id,$category)
    {
        $query = "SELECT * FROM services WHERE service_id != :id 
                    and service_category=:category
                    and service_active = 'Yes'
                    order by service_name ASC" ;
        $stmt = self::$dbh->prepare($query);
        $params = array(':id' => $id, ':category' => $category);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * [searchResults function for search on Admin Services Page]
     * @return [array] [query output]
     */
    public function searchResults($search)
    {
        if($search!='') {
            $query = "SELECT * FROM services 
                        WHERE (service_name like '%".$search."%' 
                        OR service_category like '%".$search."%' 
                        OR service_description like '%".$search."%') 
                        and service_active = 'Yes'
                        order by service_name ASC";

            $stmt = self::$dbh->prepare($query);  
                          
            $stmt->execute();            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
    }


    /**
     * [searchResultsPerPage function to get search result on Services list page]
     * @param  [string] $search       [search content]
     * @param  [int] $start_result [starting number of record from pagination bar]
     * @return [array]               [query output]
     */
    public function searchResultsPerPage($search,$start_result)
    {
        $start_result = ($start_result-1)*MAXRESULTSPERPAGE;
        
        if($search!='') {
            $query = "SELECT * FROM services 
                        WHERE (service_name like '%".$search."%' 
                        OR service_category like '%".$search."%' 
                        OR service_description like '%".$search."%') 
                        and service_active = 'Yes'
                        order by service_name ASC
                        LIMIT :start_result, :end_result";

            $stmt = self::$dbh->prepare($query);  
            //$stmt->bindValue(':search', (string) $search, \PDO::PARAM_STR);    
            $stmt->bindValue(':start_result', (int) $start_result, \PDO::PARAM_INT);
            $stmt->bindValue(':end_result', (int) MAXRESULTSPERPAGE, \PDO::PARAM_INT);  
            $stmt->execute();            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
    }

    /**
     * [getNumSearchLinks function to get number of pages for the search view on
     * services list page. It is for pagination bar]
     * @param  [string] $search [search content]
     * @return [int]         [pages for pagination bar]
     */
    public function getNumSearchLinks($search)
    {
        $query = "SELECT count(*) as num FROM services 
                        WHERE (service_name like '%".$search."%' 
                        OR service_category like '%".$search."%' 
                        OR service_description like '%".$search."%') 
                        and service_active = 'Yes'";
        $stmt = self::$dbh->prepare($query);
        //$stmt->bindValue(':search', (string) $search, \PDO::PARAM_STR);
        $stmt->execute();
        $total = $stmt->fetch(\PDO::FETCH_ASSOC)['num'];
        return ceil($total/MAXRESULTSPERPAGE);
    }

    /**
     * [avgCost function to get the highest charged Services for Dashboard]
     * @return [array] [query output]
     */
    public function avgCost()
    {
        $query = "SELECT service_category, avg(service_price) 
                    FROM services 
                    where service_active = 'Yes'
                    Group By service_category Limit 3";
        $stmt = self::$dbh->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * [avgRating function to get highest rated category for Dashboard]
     * @return [type] [description]
     */
    public function avgRating()
    {
        $query = "SELECT service_category, avg(service_avg_rating) 
                    FROM services 
                    where service_active = 'Yes'
                    Group By service_category 
                    Order By avg(service_avg_rating) Desc Limit 3";
        $stmt = self::$dbh->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * [deleteService function to soft delete in primary table for users view]
     * @param  [int] $id [service id]
     * @return [int]     [description]
     */
    public function deleteService($id)
    {
        $query = "UPDATE services
                    SET service_active='No',
                    updated_at = NOW() 
                    WHERE service_id=:service_id"; 
        //place holders beginning with :
         
        $stmt = self::$dbh->prepare($query);
        
        $params = array(
            ':service_id'=> $id
        );
        
        $stmt->execute($params);

        return self::$dbh->lastInsertID();
    }


    /**
     * [updateService function to update service details selected by Admin]
     * @param  [int] $id          [service id]
     * @param  [string] $name        [service name]
     * @param  [float] $price       [service price]
     * @param  [string] $description [service description]
     * @param  [string] $activate    [service active status]
     * @return [int]              [query output]
     */
    public function updateService($id,$name,$price,$description,$activate)
    {
        $query = "UPDATE services
                    SET service_name=:name,
                    service_price=:price,
                    service_description=:description,
                    service_active = :updateActive,
                    updated_at = NOW() 
                    WHERE service_id=:service_id"; 
        //place holders beginning with :
         
        $stmt = self::$dbh->prepare($query);
        
        $params = array(
            ':service_id' => $id,
            ':name'=> $name,
            ':price'=> $price,
            ':description'=> $description,
            ':updateActive' => $activate
        );
        
        $stmt->execute($params);

        return self::$dbh->lastInsertID();
    }

    /**
     * [checkService function to check if particular service name exists in services table]
     * @param  [string] $name [service name]
     * @return [array]       [query output]
     */
    public function checkService($name)
    {
        $query = "SELECT * FROM services WHERE service_name=:name";
        $stmt = self::$dbh->prepare($query);
        $params = array(
            ':name'=> $name
        );

        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * [saveNewService function to save new service by Admin]
     * @param  [array] $array [record details of new service]
     * @return [int]        [query output]
     */
    public function saveNewService($array)
    {
        $query = "Insert Into services
                    (
                      service_name,
                      service_category,
                      service_type,
                      service_price,
                      service_description,
                      service_active,
                      service_image, 
                      created_at
                    )
                  Values
                    (
                      :service_name, 
                      :service_category, 
                      :service_type, 
                      :service_price, 
                      :service_description, 
                      :service_active, 
                      :service_image, 
                      NOW()
                    )"; 
        //place holders beginning with :
         
        $stmt = self::$dbh->prepare($query);
        
        $params = array(
            ':service_name'=> $array['serviceName'],
            ':service_category'=> $array['serviceCategory'],
            ':service_type'=> $array['serviceType'],
            ':service_price'=> $array['servicePrice'],
            ':service_description'=> $array['serviceDescription'],
            ':service_image'=> 'pending.jpg',
            ':service_active' => 'Yes'
        );
        
        $stmt->execute($params);

        return self::$dbh->lastInsertID();
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