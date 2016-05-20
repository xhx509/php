<?php

///////// GET COMMAND PARAMETERS
if ($argc < 4) {
     echo "USAGE: php sendasap.php <Portal_username> <Portal_password> <ESN>\n";
     exit();
}
$username = $argv[1];
$password = $argv[2];
$esn = $argv[3];


///////// ACCOMMODATE LOCAL ENVIRONMENT (change cookie file here if desired)

$cookiefile = "local_cookies";

///////// SET UP DATA STRUCTURES

// Login data structure

$userdata = array(
    'NAME'      => $username,
    'PASSWORD'  => $password
);
// Parameters for the command

$cmddata = array(
     'esn'      => $esn
);
// Command with wrapping information (what the command is, etc.)
$cmdwrapped = array(
     'file'     => 'devchange',
     'action'   => 'sendasap',
     'data'     => $cmddata
);

// Options for making the network connection
$curlopts = array(
     CURLOPT_HTTPHEADER      => array("Content-Type: text/json"),
     CURLOPT_COOKIEJAR       => $cookiefile,
     CURLOPT_COOKIEFILE      => $cookiefile,
     CURLOPT_POST            => 1,
     CURLOPT_RETURNTRANSFER  => 1
);
///////// LOG IN

// Set up the login request
$ch = curl_init();
curl_setopt_array($ch, $curlopts);
curl_setopt($ch,
CURLOPT_URL,"https://mapdata.assetlinkglobal.com/Portal/autologin.php");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('USER'=>$userdata)));
// Perform the request
$rtn_login = curl_exec($ch);
// The curl handle could be reused, but we create a clean one each time just-in-case
curl_close($ch);
unset($ch);
// Check the login result
$login_result = json_decode($rtn_login);

if ($login_result->USER->STATUS != "VERIFIED") {
      echo "Unable to login for user '$username': received\n";
      echo $rtn_login."\n";
      exit();
}
///////// SEND COMMAND
// Set up the command request
$ch = curl_init();
curl_setopt_array($ch, $curlopts);
curl_setopt($ch,
CURLOPT_URL,"https://mapdata.assetlinkglobal.com/Portal/instructions.php");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array($cmdwrapped)));
// Perform the request
$rtn_cmd = curl_exec($ch);
curl_close($ch);
unset($ch);
// Check the command result
$cmd_result = json_decode($rtn_cmd);

if ($cmd_result[0]->result != "success") {
     echo "Unable to send command to unit '$esn': received\n";
     echo $rtn_cmd."\n";
}
else {
     echo "Command sent to unit $esn;\n";
     echo "server file ".$cmd_result[0]->data->text."\n";
}

///////// LOG OUT
// Set up the logout request

$ch = curl_init();
curl_setopt_array($ch, $curlopts);
curl_setopt($ch,
CURLOPT_URL,"https://mapdata.assetlinkglobal.com/Portal/autologin.php");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array("LOGOUT"=>true)));

// Perform the request (silent to avoid annoying the user)
$rtn_login = curl_exec($ch);
curl_close($ch);
unset($ch);
// Done!

