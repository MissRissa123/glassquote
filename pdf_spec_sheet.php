<?php
// Fetch the first name and last name from the POST data
$firstName = $_POST['first_name'];
$lastName = $_POST['last_name'];
$date = date('Y-m-d');

$html = <<<EOD
<h1>Quote Spec Sheet</h1>
<h2>{$firstName} {$lastName}</h2>
<p>Date: {$date}</p>
EOD;
