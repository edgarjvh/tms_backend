<?php

// Show all errors for a full diagnosis
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = 'ssl://smtp.gmail.com';
$port = 465;
$timeout = 30; // 30 second timeout

echo "<h1>Gmail SMTP Connection Test</h1>";
echo "<p>Attempting to connect to: <strong>$host</strong> on port <strong>$port</strong>...</p>";

// Attempt to open the socket connection
$socket = @stream_socket_client($host . ':' . $port, $errno, $errstr, $timeout);

// Check the result
if ($socket) {
    echo '<p style="color:green; font-weight:bold;">CONNECTION SUCCESSFUL!</p>';
    echo "<p>Your PHP environment can correctly connect to Gmail's SMTP server.</p>";
    echo "<p>If this test works but Laravel fails, the problem is a very specific, hidden configuration issue within your Laravel project.</p>";
    fclose($socket);
} else {
    echo '<p style="color:red; font-weight:bold;">CONNECTION FAILED.</p>';
    echo "<p><strong>Error number:</strong> " . $errno . "</p>";
    echo "<p><strong>Error message:</strong> " . $errstr . "</p>";
    echo "<hr>";
    echo "<p><strong>Conclusion:</strong> The problem is with your server's configuration (PHP/OpenSSL) or your network, not with Laravel. Your server cannot establish an SSL connection to Gmail.</p>";
}

echo "<hr>";
echo "<h3>Next Steps</h3>";
echo "<p>If the test failed, the most likely causes are:</p>";
echo "<ul>";
echo "<li><strong>Firewall, Antivirus, or Proxy:</strong> A security program on your machine or network is blocking the outgoing connection on port 465. This is the most common cause.</li>";
echo "<li><strong>Hosting Provider:</strong> Some internet or hosting providers block SMTP ports to prevent spam.</li>";
echo "<li><strong>DNS Issue:</strong> Your server cannot resolve the address 'smtp.gmail.com'. Try running 'ping smtp.gmail.com' from your server's terminal.</li>";
echo "<li><strong>Outdated OpenSSL Version:</strong> Your PHP's OpenSSL version is too old and does not support Gmail's modern security protocols.</li>";
echo "</ul>";

?>
