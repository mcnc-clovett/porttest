<?php
$client_ip = $_SERVER['REMOTE_ADDR'];
#$server_port = $_SERVER['SERVER_PORT'];
#$server_name = $_SERVER['SERVER_NAME'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$http_host = $_SERVER['HTTP_HOST'];
if (strpos($http_host, ":") !== false) {
	[$server_name, $server_port] = explode(":", $http_host);
} else {
	$server_name = $http_host;
	$server_port = "80";
}

if (preg_match("/^(Wget|curl)/", $user_agent)) {
	header("Content-Type: text/plain");
	echo "Port test successful!\n";
	echo "Your IP: $client_ip\n";
} else {
// Function to parse the services file and create a port-to-service mapping array
function parseServicesFile($file_path)
{
    $service_map = array();

    if (($handle = fopen($file_path, "r")) !== false) {
        while (($line = fgets($handle)) !== false) {
            if (preg_match('/^\s*#/', $line)) continue; // Skip comment lines
            if (preg_match('/^\s*$/', $line)) continue; // Skip empty lines

            list($service_name, $port, $protocol) = preg_split('/\s+/', $line, 3);
            $service_map[(int)$port] = $service_name;
        }
        fclose($handle);
    }

    return $service_map;
}

// Path to the services file on your system
$services_file_path = '/etc/services';

// Parse the services file and get the port-to-service mapping
$port_service_map = parseServicesFile($services_file_path);

if (isset($port_service_map[$server_port])) {
    $service_name = $port_service_map[$server_port];
} else {
    $service_name = "Unknown";
}

echo "
<!DOCTYPE html>
<html>
<head>
<title>Client IP and Port</title>
<style type='text/css'>
body {
	font-family: sans-serif;
	font-size: 0.9em;
}
</style>

</head>

<body>
<h1>Outgoing port tester</h1>

This server listens on all TCP ports, allowing you to test any outbound TCP port.

<p>
You have reached this page on port <b>$server_port</b> (from http host header).<br/>
</p>

Your network allows you to use this port.
(Assuming that your network is not doing advanced traffic filtering.)

<p>
Network service: <b>$service_name</b><br/>
Your outgoing IP: <b>$client_ip</b></p>

<h2>Test a port using a command</h2>

<pre>
$ telnet $server_name $server_port
Trying ...
Connected to $server_name.
Escape character is '^]'.
</pre>
<pre>
$ nc -v $server_name $server_port 
Connection to $server_name $server_port port [tcp/daytime] succeeded!
</pre>
<pre>
$ curl $server_name:$server_port
Port test successful!
Your IP: $client_ip</pre>
<pre>
$ wget -qO- $server_name:$server_port 
Port test successful!
Your IP: $client_ip</pre>
<pre>
# For Windows PowerShell users
PS C:\&gt; Test-NetConnection -InformationLevel detailed -ComputerName $server_name -Port $server_port</pre>

<h2>Test a port using your browser</h2>

<p>
In your browser address bar: <strong>http://$server_name:XXXX</strong>
</p>

Examples: <br/>
<a href='http://$server_name:3389'>http://$server_name:3389</a> <br/>
<a href='http://$server_name:445'>http://$server_name:445</a> <br/>
<a href='http://$server_name:777'>http://$server_name:777</a> <br/>

<p>
Your browser can block network ports normally used for purposes other than Web browsing. In this case you should use tools like telnet, netcat or nmap to test the port.
</p>

</body>
</html>";
}

#print_r($_SERVER);
?>
