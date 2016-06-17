<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class uberGo_model extends CI_Model {

	private $salt;

    public function __construct ()
    {
        parent::__construct();
        $this->salt = '$6$rounds=5000$usingashitstringfornidhipasmundra$';
    }

    public function checkDB($src_ref,$dest_ref) 
    {
         $this->db->select()->from('route_detail')->where(array("src_ref"=> $src_ref , "dest_ref" => $dest_ref))->limit(1);
         $query = $this->db->get();
         return $query->result();
    }

    public function searchLocation($place)
    {
        $this->db->select('lat_long_reference')->from('place_reference')->where('place', $place)->limit(1);
        $query = $this->db->get();
        return $query->result();
    }

    public function insertLocation($place,$id)
    {

         $this->db->insert('place_reference', array('place'=> $place , 'lat_long_reference'=> $id));
    }

    public function searchReference($lat_long_addr)
    {
        $latlong_array = explode(',',$lat_long_addr);
        $this->db->select('lat_long_reference')->from('lat_long')->where(array("latitude"=> $latlong_array[0] , "longitude" => $latlong_array[1]))->limit(1);
        $query = $this->db->get();
        return $query->result();
    }

    public function insertLatLongRef($lat_long_addr)
    {
       $latlong_array = explode(',',$lat_long_addr);
       $this->db->insert('lat_long', array('latitude'=> $latlong_array[0] , 'longitude'=> $latlong_array[1]));
    }


    public function searchLatLong($lat,$long)
    {
        $this->db->select('lat_long_reference')->from('lat_long')->where(array("latitude"=> $lat , "longitude" => $long))->limit(1);
        $query = $this->db->get();
        return $query->result();
    }

    public function insertLatLong($lat,$long)
    {
           $this->db->insert('lat_long', array('latitude'=> $lat , 'longitude'=> $long));
    }


    public function searchDB($src_ref,$dest_ref)
    {
          $this->db->select()->from('route_detail')->where(array('src_ref'=> $src_ref , 'dest_ref'=> $dest_ref ))->limit(1);
          $query = $this->db->get();
          return $query->result();
    }


    public function insertDB($src_ref,$dest_ref,$travel_duration,$uber_duration,$t)
    {
          $this->db->insert('route_detail', array('src_ref'=> $src_ref , 'dest_ref'=> $dest_ref , 'duration' => $travel_duration , 'ubertime' => $uber_duration , 'last_update' => $t));
          return TRUE;
    }


    public function updateDB($route_reference,$src_ref,$dest_ref,$travel_duration,$uber_duration,$t)
    {
         $data=array('duration'=> $travel_duration,'ubertime'=>$uber_duration,'last_update'=>$t);
         $this->db->where('route_reference',$route_reference);
         $this->db->update('route_detail',$data);
         return TRUE;
    }


    public function insertUsermail($email,$t,$route_reference,$desired_time)
    {
          $this->db->insert('usermail', array('email'=> $email , 'time'=> $t , 'route_reference' => $route_reference , 'dtime' => $desired_time ));
          return TRUE;
    }

    public function updateUsermail($email,$t,$route_reference,$desired_time)
    {
         $data=array('time'=> $t);
         $this->db->where(array('email'=> $email,'route_reference'=>$route_reference,'dtime'=>$desired_time));
         $this->db->update('usermail',$data);
         return TRUE;   
    }

    public function fetchUsermailRowsToUpdate($route_reference,$email,$desired_time)
    {
          $this->db->select();
          $this->db->where_in('route_reference',$route_reference);
          $this->db->where("(email != '".$email."' OR dtime != '".$desired_time."')");
          $this->db->from("usermail");
          $query = $this->db->get(); 
          $res = $query->result();
          return $res;
    }

    public function timeToUpdate($start_time,$end_time)
    {
         $this->db->select();
         $this->db->from("usermail");
         $this->db->where("time >=",$start_time);
         $this->db->where("time <=",$end_time);
         $query = $this->db->get();
         $res = $query->result();
         return $res;
    }

    public function getlocationref($route_reference)
    {
      $this->db->select()->from('route_detail')->where(array("route_reference"=> $route_reference))->limit(1);
      $query = $this->db->get();
      return $query->result();
    }

    public function getLatLongFromRef($ref)
    {
      $this->db->select()->from('lat_long')->where(array("lat_long_reference"=> $ref))->limit(1);
      $query = $this->db->get();
      return $query->result();
    }

    public function deleteRowUsermail($email,$route_reference,$desired_time)
    {
        $this->db->where(array('route_reference' => $route_reference , 'email' => $email , 'dtime' => $desired_time));
        $this->db->delete('usermail'); 
        return TRUE;
    }

	
}