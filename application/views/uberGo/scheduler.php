  <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false&libraries=places&key=AIzaSyB6ky0s6kmaxH15hsxsNHKuZeI6n_OG2eA"></script>
  <script type="text/javascript">

  var source, destination,time,email,estimate,display_name,src_ref,dest_ref,route_reference;
  var recRows = <?php echo(json_encode($data)); ?>;

   function GetUberGoTime(rowNum) {
           
               var src_lat=recRows[rowNum]["src_lat"];
               var src_long=recRows[rowNum]["src_long"];
               console.log(src_lat+"  "+src_long);
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
       
         function GetRoute(rowNum) {

            source = recRows[rowNum]["src_lat"]+","+recRows[rowNum]["src_long"];
            destination = recRows[rowNum]["dest_lat"]+","+recRows[rowNum]["dest_long"];
            route_reference = recRows[rowNum]["route_reference"];
            email = recRows[rowNum]["email"];
            src_ref = recRows[rowNum]["src_ref"];
            dest_ref = recRows[rowNum]["dest_ref"];
            time = recRows[rowNum]["dtime"];
            var flag = 1;
           
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
	        	url: "insertDB", 
	        	type: "POST",
                data: {'source': source, 'destination': destination, 'route_reference' : route_reference, "duration" : duration , "estimate" : estimate  , "email" : email , "time" : time , "src_ref" : src_ref , "dest_ref" : dest_ref ,"flag" : flag},
	        	success: function(data){
	        		data= JSON.parse(data);
	        		
	        		console.log("INSERTDB success");
                    if(data["status"] == 1)
                    {
                        console.log(data["message"]);
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


    console.log("calling functions");
    for(var i=0;i<recRows.length;i++)
    {
      
        GetUberGoTime(i);
        GetRoute(i);  
    }
      
 
  </script>