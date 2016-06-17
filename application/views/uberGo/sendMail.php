<?php 
$errors = '';
$myemail = 'chaurasiapp@yahoo.com';//<-----Put Your email address here.


$name = $data["name"] ;
$email_address = $data['email']; 
$message = $data['message']; 

if (!preg_match(
"/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i", 
$email_address))
{
   $errors .= "\n Error: Invalid email address";
}

if( empty($errors))
{
$to = $myemail; 
$email_subject = "Track This BUS Contact form submission: $name";
$email_body = "You have received a new message. ".
" Here are the details:\n Name: $name \n Email: $email_address \n Message \n $message"; 

$headers = "From: $myemail\n"; 
$headers .= "Reply-To: $email_address";

mail($to,$email_subject,$email_body,$headers);

echo "sent mail";
//redirect to the 'thank you' page
//header('Location: contact-form-thank-you.html');
} 
?>
