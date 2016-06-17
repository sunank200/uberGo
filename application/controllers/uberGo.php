<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class uberGo extends CI_CONTROLLER
{

  public function __construct() {
        parent::__construct();
        $this->load->model('uberGo_model');
       
    }

    public function index()
    {
        $rec =  $this->home();
    }

    public function home()
    {

        $view_html = array(
            $this->load->view('uberGo/header'),
            $this->load->view('uberGo/index'),
            $this->load->view('uberGo/footer')
            );
        return $view_html;
    }
    
    public function checkDB()
    {
    
      $ans[]=array();
       
        $source=$this->input->post('source');
        $destination=$this->input->post('destination');
        $time=$this->input->post('time');
        $email=$this->input->post('email');

        // $source = "17.435358,78.340747";
        // $destination = "17.398377,78.558265";
        // $time = "06:00:00";
        // $email = "def@gmail.com";

        $src_id = -1;
        $dest_id = -1;
       
            $val=$this->uberGo_model->searchReference($source); 
            if($val)
                $src_id = $val[0]->lat_long_reference ;
            else
            {
               $val=$this->uberGo_model->insertLatLongRef($source);
               $val=$this->uberGo_model->searchReference($source); 
               $src_id = $val[0]->lat_long_reference ;
            }

            $val=$this->uberGo_model->searchReference($destination); 
            if($val)
                $dest_id = $val[0]->lat_long_reference ;
            else
            {
               $val=$this->uberGo_model->insertLatLongRef($destination);
               $val=$this->uberGo_model->searchReference($destination);
               $dest_id = $val[0]->lat_long_reference ; 
            }
    
          
           if($src_id != -1 && $dest_id!= -1)
           {

            $val=$this->uberGo_model->checkDB($src_id,$dest_id);
            if($val)
            {

            $ans["status"]=1;
            $ans["route_reference"] = $val[0]->route_reference;
            $ans["src_ref"]= $src_id;
            $ans["dest_ref"]= $dest_id;
            $ans["ubertime"]=$val[0]->ubertime;
            $ans["duration"]=$val[0]->duration;
            $ans["last_update"]=$val[0]->last_update;
            $ans["message"] = "Present in DB";

            }
            else
            {
            $ans["status"]=0;
            $ans["route_reference"] = -1;
            $ans["src_ref"]= $src_id;
            $ans["dest_ref"]= $dest_id;
            $ans["message"] = "Not Present in DB";
            }
           }
           else
           {

            $ans["status"]=0;
            $ans["message"] = "Not Present in DB";
           
           }        
            echo json_encode($ans);
            return;
    }

   public function testmodel()
   {
    $route_reference=4;$email="abc@gmail.com";$desired_time="14:05:00";
         $val = $this->uberGo_model->fetchUsermailRowsToUpdate($route_reference,$email,$desired_time);
         echo "____ BACK FROM MODEL___";
         print_r($val);
   }
    public function insertDB()
    {

        $source=$this->input->post('source');
        $destination=$this->input->post('destination');
        $route_reference=$this->input->post('route_reference');
        $travel_duration=$this->input->post('duration');
        $uber_duration=$this->input->post('estimate');
        $email=$this->input->post('email');
        $desired_time=$this->input->post('time');
        $src_ref = $this->input->post('src_ref');
        $dest_ref = $this->input->post('dest_ref');
        $flag = $this->input->post('flag');

        // $source="17.435358,78.340747";
        // $destination="17.398377,78.558265";
        // $route_reference=6;
        // $travel_duration=5267;
        // $uber_duration=240;
        // $email="def@gmail.com";
        // $desired_time="21:00:00";
        // $src_ref = 56;
        // $dest_ref = 51;
        // $flag = 0;

        date_default_timezone_set("Asia/Kolkata");
        $curr_time = date('Y-m-d H:i:s'); 
        $curr_time = strtotime($curr_time);

         if($route_reference == -1)
         {

              $val=$this->uberGo_model->insertDB($src_ref,$dest_ref,$travel_duration,$uber_duration,$curr_time);
              if($val)
              {
                 $val1=$this->uberGo_model->searchDB($src_ref,$dest_ref);
                 $route_reference = $val1[0]->route_reference;
              }
         }
         else
         {
             $val=$this->uberGo_model->updateDB($route_reference,$src_ref,$dest_ref,$travel_duration,$uber_duration,$curr_time);  
         }

         $offset = 19800;
         $time = strtotime($desired_time) ;
         $time = $time - $uber_duration - $travel_duration ;
         $tentative_time = gmdate('H:i:s',$time+$offset);
         $desired_time = $this->scheduleTime($curr_time,$time,$email,$route_reference,$desired_time,$flag);
         $val = $this->uberGo_model->fetchUsermailRowsToUpdate($route_reference,$email,$desired_time);
         if($val)
         {
          foreach ($val as $row)
          {
             $rest_flag = 1;
             $time = strtotime($row->dtime);
             $time = $time - $uber_duration - $travel_duration ;
             $this->scheduleTime($curr_time,$time,$row->email,$route_reference,$row->dtime,$rest_flag);           
          }
         }

         $ans[]=array();
         $ans["status"] = 1;
         $ans["message"] = "succesfully registered";
         $ans["flag"] = $flag;
         if($flag == 0 && $desired_time != 0)
         $ans["tentative_time"]= $desired_time;
         else
         $ans["tentative_time"]= $tentative_time;

         echo json_encode($ans);
         return ;
    }


    function scheduleTime($curr_time,$time,$email,$route_reference,$desired_time,$flag)
    {

      $curr_time = $curr_time - strtotime('TODAY');
      $time = $time - strtotime('TODAY');
      $offset = 19800 ;

      if($curr_time >= $time)
      {

        //echo "(send mail".$desired_time.")";
        //now delete that entry as mail is already sent
        $val=$this->uberGo_model->deleteRowUsermail($email,$route_reference,$desired_time);
        $curr_time = $curr_time + strtotime('TODAY') ;
        $curr_time = gmdate('H:i:s',$curr_time+$offset);
        return $curr_time;
      }
      else if($time - $curr_time >7200 )
      {
        $time = $time + strtotime('TODAY') - 7200 ;
        $time = gmdate('H:i:s',$time+$offset);
        //echo "(>7200:".$time.")";
        if($flag == 0)
        $val=$this->uberGo_model->insertUsermail($email,$time,$route_reference,$desired_time);
        else
        $val=$this->uberGo_model->updateUsermail($email,$time,$route_reference,$desired_time);
          
        return 0;
      }

      $diff = $time - $curr_time ;
      if($diff > 0)
      {
       
       if($diff >= 3600)
       {
        $temp = $diff - 1800 ;
        $time = $time + strtotime('TODAY') - $temp ;
        $time = gmdate('H:i:s',$time+$offset);
        //echo "(>3600:".$time.")";
        if($flag == 0)
        $val=$this->uberGo_model->insertUsermail($email,$time,$route_reference,$desired_time);
        else
        $val=$this->uberGo_model->updateUsermail($email,$time,$route_reference,$desired_time);  
        return 0;
       }
       else if($diff >= 1800)
       {
        $temp = $diff - 900 ;
        $time = $time + strtotime('TODAY') - $temp;
        $time = gmdate('H:i:s',$time+$offset);
        //echo "(>1800:".$time.")";
        if($flag == 0)
        $val=$this->uberGo_model->insertUsermail($email,$time,$route_reference,$desired_time);
        else
        $val=$this->uberGo_model->updateUsermail($email,$time,$route_reference,$desired_time);  
        return 0;
       }
       else if($diff >= 600)
       {
        $temp = $diff - 600 ;
        $time = $time + strtotime('TODAY') - $temp;
        $time = gmdate('H:i:s',$time+$offset);
        //echo "(>600:".$time.")";
        if($flag == 0)
        $val=$this->uberGo_model->insertUsermail($email,$time,$route_reference,$desired_time);
        else
        $val=$this->uberGo_model->updateUsermail($email,$time,$route_reference,$desired_time);  
        return 0;
       }
       else
       {
        //echo "(send mail2".$desired_time.")";
        //echo "send mail 2";
        $val=$this->uberGo_model->deleteRowUsermail($email,$route_reference,$desired_time);
        $curr_time = $curr_time + strtotime('TODAY') ;
        $curr_time = gmdate('H:i:s',$curr_time+$offset);
        return $curr_time;
        
       }

      }
      else
      {
        //echo "this case not posssible";
        return;
      }

    }

   
   function scheduler()
   {
        date_default_timezone_set("Asia/Kolkata");
        $curr_time = date('H:i:s'); 
        //$curr_time = "11:29:00";
        $sec_curr_time = strtotime($curr_time);
        $sec_start_time = $sec_curr_time - 60 ;
        $sec_end_time = $sec_curr_time + 60 ;
        $offset = 19800;
        $start_time = gmdate('H:i:s',$sec_start_time+$offset);
        $end_time = gmdate('H:i:s',$sec_end_time+$offset);

        $val = $this->uberGo_model->timeToUpdate($start_time,$end_time);
        if($val)
        {
        $count = 0;
        $data[] = array();
        foreach ($val as $row) 
        {
          //print_r($row);
          $data[$count]["email"] = $row->email;
          $data[$count]["time"] = $row->time;
          $data[$count]["route_reference"] = $row->route_reference;
          $data[$count]["dtime"] = $row->dtime;
          $count++;
        }
      
        $subject = array_reduce($data, function($final, $article){
             static $seen = array();
            if ( ! array_key_exists($article['route_reference'], $seen)) {
                  $seen[$article['route_reference']] = NULL;
                  $final[] = $article;
        }
        return $final;
         });

       $len = sizeof($subject);

       for($i=0;$i<$len;$i++)
       {
        
         $val = $this->uberGo_model->getlocationref($subject[$i]["route_reference"]);
         $src_ref = $val[0]->src_ref;
         $dest_ref = $val[0]->dest_ref;
         $val = $this->uberGo_model->getLatLongFromRef($src_ref);
         $subject[$i]["src_ref"] = $src_ref;
         $subject[$i]["src_lat"]=$val[0]->latitude;
         $subject[$i]["src_long"]=$val[0]->longitude;
         $val = $this->uberGo_model->getLatLongFromRef($dest_ref);
         $subject[$i]["dest_ref"] = $dest_ref;
         $subject[$i]["dest_lat"]=$val[0]->latitude;
         $subject[$i]["dest_long"]=$val[0]->longitude;
       }

        $ans["data"] = $subject;
        //$this->load->view('uberGo/scheduler', $ans);
         $view_html = array(
            $this->load->view('uberGo/header'),
            $this->load->view('uberGo/scheduler',$ans),
            $this->load->view('uberGo/footer')
            );
      }

   }

      public function sendMail()
      {
       
       $config = Array(
          'protocol' => 'sendmail',
          //'smtp_host' => 'ssl://smtp.googlemail.com',
          //'smtp_port' => 465,
          //'smtp_user' => 'agarwal.karnika@gmail.com',// your mail name
          //'smtp_pass' => 'shinchan123',
          //'mailtype'  => 'html', 
           'mailpath' => '/usr/sbin/sendmail',
          'charset'   => 'iso-8859-1',
          'wordwrap' => TRUE
          );

       $this->load->library('email',$config);
       $this->email->initialize($config);
       $message = "testing mail from ci to u..testing..yipeee sent";
       $this->email->from('agarwal.karnika@gmail.com', 'karnika');//your mail address and name
       $this->email->to('sunank200@gmail.com'); //receiver mail
       $this->email->subject('testing');
       $this->email->message($message);
       $this->email->send(); //sending mail

        // $ans["name"] = "ankit";
        // $ans["email"] = "sunank200@gmail.com";
        // $ans["message"] = "hi it this u received..testing";

        // $res["data"] = $ans ;

        //  $this->load->view('uberGo/sendMail', $res);


      }

      function send_mail()
      {
        $this->load->library('email'); // load email library
        $this->email->from('agarwal.karnika@gmail.com', 'karnika');
        $this->email->to('sunank200@gmail.com');
        $this->email->cc('agarwal.karnika@gmail.com'); 
        $this->email->subject('Your Subject');
        $this->email->message('testing mail sending via ci');
        //$this->email->attach('/path/to/file1.png'); // attach file
        //$this->email->attach('/path/to/file2.pdf');
        if ($this->email->send())
            echo "Mail Sent!";
        else
            echo "There is error in sending mail!";

      }
}