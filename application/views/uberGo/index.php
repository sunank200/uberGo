<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false&libraries=places&key=AIzaSyB6ky0s6kmaxH15hsxsNHKuZeI6n_OG2eA"></script>

    <script type="text/javascript">
        var source, destination,time,email,estimate,display_name,src_ref,dest_ref,route_reference,flag;
     
       $(document).ready(function(){
	    $("#sub1").click(function(){
	           
            source = document.getElementById("txtSource").value;
            destination = document.getElementById("txtDestination").value;
            time = document.getElementById("txtTime").value;
            email = document.getElementById("txtEmail").value;
            console.log(source);
            console.log(destination);
            console.log("came here");

             $.ajax({
	        	url: "index.php/uberGo/checkDB", 
	        	type: "POST",
                data: {'source': source, 'destination': destination, 'time' : time, 'email' : email},
	        	success: function(data){
	        		data= JSON.parse(data);
                    console.log("got data");
                    route_reference = data["route_reference"];
                    src_ref = data["src_ref"];
                    dest_ref = data["dest_ref"];
                    flag = 0;

	                if( data["status"] == 1)
	        		{
	        			console.log("found in db");
                        GetUberGoTime();
                        GetRoute();      
	        		}
	        		else
	        		{
                        console.log("ajax call");
                        GetUberGoTime();
	        			GetRoute();   	
	        		}
	            },
	            error: function (jqXHR, textStatus, errorThrown)
				{		
					console.log("Error:Not checked properly");
					return false;
				}
	      });
	     });
	    });


         function GetUberGoTime() {

               var src_array=source.split(',');
               var src_lat=parseFloat(src_array[0]);
               var src_long=parseFloat(src_array[1]);
               console.log("src_lat"+src_lat);
               console.log("src_long"+src_long);

                $.ajax({
                url: "https://api.uber.com/v1/estimates/time", 
                async : false,
                type: "GET",
                data: {'server_token' : "BPehDhjfmMaomcn2ZbnWuyaqRzrZoTS1ezAMlZs1" ,'start_latitude': src_lat,'start_longitude' : src_long},
                success: function(data){
                    //data= JSON.parse(data);
                    console.log(data);
                    console.log("uber return");
                    if(data.times.length > 0)
                    {
                    estimate = data.times[0].estimate;
                    display_name = data.times[0].localized_display_name;
                    console.log(estimate);
                    console.log(display_name);
                    }

                },
                error: function (jqXHR, textStatus, errorThrown)
                {       
                    console.log("UBER Error:Not checked properly");
                    return false;
                }
          });



         }
       
         function GetRoute() {    
           
            var service = new google.maps.DistanceMatrixService();
            service.getDistanceMatrix({
                origins: [source],
                destinations: [destination],
                travelMode: google.maps.TravelMode.DRIVING,
                unitSystem: google.maps.UnitSystem.METRIC,
                avoidHighways: false,
                avoidTolls: false
            }, function (response, status) {
                if (status == google.maps.DistanceMatrixStatus.OK && response.rows[0].elements[0].status != "ZERO_RESULTS") {
                    console.log("maps return");
                    var duration = response.rows[0].elements[0].duration.value;
                    console.log(duration);
                    console.log("testing data to be sent");
                    console.log(route_reference);
                    console.log(email);
                    console.log(src_ref);
                    console.log(dest_ref);
                    console.log(time);

                $.ajax({
	        	url: "index.php/uberGo/insertDB", 
	        	type: "POST",
                data: {'source': source, 'destination': destination, 'route_reference' : route_reference, "duration" : duration , "estimate" : estimate  , "email" : email , "time" : time , "src_ref" : src_ref , "dest_ref" : dest_ref , "flag" : flag },
	        	success: function(data){
	        		data= JSON.parse(data);
	        		
	        		console.log("INSERTDB success");
                    if(data["status"] == 1)
                    {
                        console.log(data["message"]);
                        if(data["flag"]==0)
                        {
                           var expected_time = data["tentative_time"];
                           console.log(expected_time);
                           var dvDistance = document.getElementById("dvDistance");
                           dvDistance.innerHTML = "";
                           dvDistance.innerHTML += "[" + expected_time + "] Requesting Uber API for [ "+email+" ]<br />";
                           dvDistance.innerHTML += "*Its the tentative time.<br />";
                        }
                    }
	        		
	            },
	            error: function (jqXHR, textStatus, errorThrown)
				{		
					console.log("Not inserted:some error occured");
					//showAlert("Some Error occured! Please reload/refresh the page and try again.");
					return false;
				}
	      });

                } else {
                    alert("Unable to find the distance via road.");
                }
            });
        }

        
</script>
<div class="container">
  <h2>Enter Route Details</h2>

  <table border="0" cellpadding="0" cellspacing="3">
        <tr>
            <td colspan="2">
                Source:
                <input type="text" id="txtSource" placeholder="Enter Source" style="width: 200px" />
                &nbsp; 
                Destination:
                <input type="text" id="txtDestination" placeholder="Enter Destination" style="width: 200px" />
                &nbsp;
                Time:
                <input type="text" id="txtTime" placeholder="Enter time" style="width: 200px" />
                &nbsp; 
                Email:
                <input type="email" id="txtEmail" placeholder="Enter email" style="width: 200px" />
                &nbsp; 
                <br />
                <br />

                <input type="button" value="Check_DB" id="sub1"  />
                <hr />
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div id="dvDistance">
                </div>
            </td>
        </tr>
       
    </table>
    
     <div id="div1"></div>
</div>

