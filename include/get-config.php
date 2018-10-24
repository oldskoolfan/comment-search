<?php

$config = parse_ini_file(__DIR__ . '/../config.ini');

require $config['mysql_connect_path'];

$con->select_db('commentsearchdb');