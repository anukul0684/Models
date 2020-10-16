<?php

namespace App\Models;

/**
 * @file - AppointmentModel.php
 * @desc - Child class extended from class Model
 * @author  - Anu Kulshrestha <[<email address>]>
 * @updated on - 2020-09-30
 */
class AppointmentModel extends Model
{
    // assigning the table name and id to the properties of this class
    protected $table='appointments';
    protected $key='appointment_id';

    // public function delete($id)
    // {

    // }

    // public function create($array)
    // {

    // }
     
    /**
     * [userWiseOrders function to get top purchasers at website]
     * @return [array] [query output]
     */
    public function userWiseOrders()
    {
      $query = "SELECT u.first_name,
                      u.last_name, 
                      a.user_id, 
                      count(a.appointment_id) 
                    FROM appointments a 
                    join user_registration u on u.id=a.user_id
                    where appointment_active = 'Confirmed'
                    Group By a.user_id order by count(a.appointment_id) DESC
                    Limit 3";
      $stmt = self::$dbh->prepare($query);
      $stmt->execute();
      return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } 

    /**
     * [serviceWiseOrders function to get top ordered services through appointments]
     * @return [array] [query output]
     */
    public function serviceWiseOrders()
    {
      $query = "SELECT s.service_name,
                      s.service_category,
                      count(bas.service_id) 
                    FROM appointment_services bas 
                    join services s on bas.service_id=s.service_id                    
                    Group By bas.service_id order by count(bas.service_id) DESC 
                    Limit 3";
      $stmt = self::$dbh->prepare($query);
      $stmt->execute();
      return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    
    /**
     * [userAppointments function to get all booked appointments of a user]
     * @param  [int] $userid [user id]
     * @return [array]         [query output]
     */
    public function userAppointments($userid)
    {
        $query = "SELECT * FROM appointments where user_id=:user_id";
        $stmt = self::$dbh->prepare($query);
        $params = array(':user_id' => $userid);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * [one function to get the collective data for 
     * appointment booked by a user using single or multiple services details]
     * @param  [int] $id [appointment id]
     * @return [array]     [query output]
     */
    public function one($id)
    {
        $query = 'SELECT 
                    s.service_name,
                    u.first_name,
                    s.service_category,
                    s.service_price,
                    bas.service_quantity,
                    bas.service_totalcost,
                    ba.appointment_date,
                    ba.appointment_time,
                    ba.created_at,
                    ba.total_appointment_cost,
                    ba.gst_cost,
                    ba.total_cost,
                    bas.service_card           
                    FROM 
                    appointment_services bas
                    JOIN user_registration u ON u.id = bas.user_id
                    JOIN appointments ba ON ba.appointment_id=bas.appointment_id
                    join services s ON s.service_id = bas.service_id
                    WHERE 
                    ba.appointment_id = :appointmentid';

        $stmt = self::$dbh->prepare($query);

        $params = array(
            ':appointmentid' => $id
        );
        
        $stmt->execute($params);
        
        //fetch single result as an associative array
        return $stmt->fetchAll(\PDO::FETCH_ASSOC); 
    }

    /**
     * [save function to save appointment and the services booked in that appointment]
     * @param  [array] $array        [cart details]
     * @param  [int] $userid       [user id]
     * @param  [date] $booking_dt   [date of booking]
     * @param  [time] $booking_time [time of booking]
     * @param  [string] $serviceCard  [last four digits of card]
     * @return  appointment id [<saved appointment id>]
     */
    public function save($array,$userid,$booking_dt,$booking_time,$serviceCard)
    {
        try{
            self::$dbh->beginTransaction();

            $total_appointment_cost=$array['appointment_cost'];
            $gst_cost=$array['gst_cost'];
            $total_cost = $array['total_cost'];

            $query="Insert Into appointments
                    (
                      user_id,
                      appointment_date,
                      appointment_time,
                      created_at,
                      appointment_active,
                      total_appointment_cost,
                      gst_cost,
                      total_cost
                    )
                  Values
                    (
                      :user_id, 
                      :booking_dt, 
                      :booking_time, 
                      NOW(), 
                      :appointment_active, 
                      :total_appointment_cost, 
                      :gst_cost, 
                      :total_cost
                    )";

            $stmt = self::$dbh->prepare($query);
        
            $params = array(
                ':user_id'=> $userid,
                ':booking_dt'=> $booking_dt,
                ':booking_time'=> $booking_time,
                ':appointment_active'=> 'Confirmed',
                ':total_appointment_cost'=> $total_appointment_cost,
                ':gst_cost'=> $gst_cost,
                ':total_cost' => $total_cost
            );
        
            $stmt->execute($params);

            $saved_appointment_id = self::$dbh->lastInsertID();
            if($saved_appointment_id)
            {
                for($i=0;$i<count($array['cart']);$i++)
                {                
                    foreach($array['cart'][$i] as $service_data)
                    {
                        $serviceId=$array['cart'][$i][$i]['service_id'];
                        $servicePrice=$array['cart'][$i][$i]['service_price'];
                        $serviceCategory=$array['cart'][$i][$i]['service_category'];
                        $serviceType=$array['cart'][$i][$i]['service_type'];
                        $serviceQuantity=$array['cart'][$i][$i]['service_quantity'];
                        $service_totalcost=$array['cart'][$i][$i]['service_totalcost'];
                        $query="Insert Into appointment_services
                                (
                                  appointment_id,
                                  user_id,
                                  service_id,                              
                                  created_at,
                                  service_price,
                                  service_category,
                                  service_type,
                                  service_quantity,
                                  service_totalcost,
                                  service_card
                                )
                              Values
                                (
                                  :appointment_id, 
                                  :user_id, 
                                  :service_id, 
                                  NOW(), 
                                  :service_price, 
                                  :service_category, 
                                  :service_type, 
                                  :service_quantity,
                                  :service_totalcost,
                                  :service_card
                                )";

                        $stmt = self::$dbh->prepare($query);
                    
                        $params = array(
                            ':appointment_id'=> $saved_appointment_id,
                            ':user_id'=> $userid,
                            ':service_id'=> $serviceId,
                            ':service_price'=> $servicePrice,
                            ':service_category'=> $serviceCategory,
                            ':service_type'=> $serviceType,
                            ':service_quantity' => $serviceQuantity,
                            ':service_totalcost' => $service_totalcost,
                            ':service_card' => $serviceCard
                        );
                    
                        $stmt->execute($params);

                        
                        $bookedServices[$saved_appointment_id][] = self::$dbh->lastInsertID();
                    }
                }
            }
            self::$dbh->commit();
            return $saved_appointment_id;
        } catch (Exception $saveException) {
            self::$dbh->rollback();
            throw $saveException;
        }        
    }
}