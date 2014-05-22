<?php

$dbh = new PDO('mysql:host=localhost;dbname=vktest', 'root', 'password');

$dbh->exec(
"CREATE TABLE IF NOT EXISTS projects ( id INT(10) NOT NULL AUTO_INCREMENT,
name VARCHAR(40),
actions TEXT,
PRIMARY KEY (id)
);");
