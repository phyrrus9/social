<?php

	$GLOBALS['SQL_SERVER'] = "localhost";
	$GLOBALS['SQL_USER']   = "root";
	$GLOBALS['SQL_PASS']   = "alpine";
	$GLOBALS['SQL_DB']     = "social"; //fuck this

	/*
mysql> show tables;                                                                                                                                            
+------------------+                                                                                                                                           
| Tables_in_social |                                                                                                                                           
+------------------+                                                                                                                                           
| friends          |                                                                                                                                           
| posts            |                                                                                                                                           
| users            |                                                                                                                                           
+------------------+ 
mysql> describe friends;                                                                                                                                       
+--------+---------+------+-----+---------+-------+                                                                                                            
| Field  | Type    | Null | Key | Default | Extra |                                                                                                            
+--------+---------+------+-----+---------+-------+                                                                                                            
| owner  | int(11) | NO   |     | NULL    |       | //UID of original                                                                                                            
| friend | int(11) | NO   |     | NULL    |       | //person he/she is friends with                                                                                                           
+--------+---------+------+-----+---------+-------+                                                                                                            
mysql> describe posts;                                                                                                                                         
+--------+--------------+------+-----+---------+-------+                                                                                                       
| Field  | Type         | Null | Key | Default | Extra |                                                                                                       
+--------+--------------+------+-----+---------+-------+                                                                                                       
| pid    | int(11)      | NO   | PRI | NULL    |       | //post ID (auto generated)                                                                                                    
| time   | int(11)      | NO   |     | NULL    |       | //time of post (UNIX time)                                                                                                      
| parent | int(11)      | NO   |     | 0       |       | //parent (nonzero if comment, PID of original post/comment)                                                                                                      
| uid    | int(11)      | NO   |     | NULL    |       | //uid of poster                                                                                                      
| text   | varchar(255) | NO   |     | NULL    |       | //message contents                                                                                                      
+--------+--------------+------+-----+---------+-------+                                                                                                                                                                                                                                                                                                                                                                                                
mysql> describe users;                                                                                                                                         
+----------+-------------+------+-----+---------+-------+                                                                                                      
| Field    | Type        | Null | Key | Default | Extra |                                                                                                      
+----------+-------------+------+-----+---------+-------+                                                                                                      
| uid      | int(11)     | NO   | PRI | NULL    |       | //user id number (auto generated)                                                                                                     
| name     | int(11)     | NO   |     | NULL    |       | //user's display name
| username | varchar(60) | YES  |     | NULL    |       | //user's login ID                                                                                                     
| password | varchar(72) | YES  |     | NULL    |       | //password_hash($pass, PASSWORD_BCRYPT)                                                                                                     
+----------+-------------+------+-----+---------+-------+
	*/

?>