<?php

if ($_SERVER['HTTP_HOST'] === 'localhost') {
    $host = 'shinycar24.de';
    $baseUrl = 'https://' . $host;
    $con = mysqli_connect($host, "d03807a4", "admin20", "d03807a4");
    $serverName = 'w01c3226.kasserver.com';
} else if ($_SERVER['HTTP_HOST'] === 'shinycar24.de') {
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = 'https://' . $host;
    $con = mysqli_connect("localhost", "d03807a4", "admin20", "d03807a4");
    $serverName = 'w01c3226.kasserver.com';
} else if ($_SERVER['HTTP_HOST'] === 'corona-php-api.herokuapp.com') {
    $host = 'shinycar24.de';
    $baseUrl = 'https://' . $host;
    $con = mysqli_connect($host, "d03807a4", "admin20", "d03807a4");
    $serverName = 'w01c3226.kasserver.com';
} else {

	$host = $_SERVER['HTTP_HOST'];
	$baseUrl = 'https://' . $host;
	$con = mysqli_connect("localhost", "d0384f19", "######", "d0384f19");
	$serverName = 'w01c831e.kasserver.com';

}


$secret_key = "COV_SCHNELLTEST";

mysqli_set_charset($con, 'utf8');