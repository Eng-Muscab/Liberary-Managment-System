<?php

$conn = new mysqli("localhost", "root", "", "education_liberary");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}