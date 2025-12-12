<?php
require_once __DIR__ . '/connect.php';
if (!isset($con) || !($con instanceof mysqli)) {
    die('Unable to connect..!!');
}

$con->set_charset('utf8mb4');

// Create categories table
$ddlCategories = "CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` varchar(8) NOT NULL,
  `category_name` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";
if (!$con->query($ddlCategories)) {
    die('Failed to create categories: ' . $con->error);
}

// Seed categories if empty
$res = $con->query('SELECT COUNT(*) AS c FROM categories');
$count = ($res && ($row = $res->fetch_assoc())) ? intval($row['c']) : 0;
if ($count === 0) {
    $con->query("INSERT INTO categories (category_id, category_name) VALUES ('1','maincourse'),('2','street   food')");
}

// Create food table
$ddlFood = "CREATE TABLE IF NOT EXISTS `food` (
  `Food_id` varchar(8) NOT NULL,
  `Food_name` varchar(20) NOT NULL,
  `Price` int(11) DEFAULT NULL,
  `Rate` float DEFAULT NULL,
  `Prep_time` time DEFAULT NULL,
  `Spice_level` int(11) DEFAULT NULL,
  `Is_Jain` varchar(3) DEFAULT NULL,
  `Food_description` varchar(100) DEFAULT NULL,
  `category_id` varchar(8) DEFAULT NULL,
  PRIMARY KEY (`Food_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";
if (!$con->query($ddlFood)) {
    die('Failed to create food: ' . $con->error);
}

// Seed initial items if empty
$res2 = $con->query('SELECT COUNT(*) AS c FROM food');
$count2 = ($res2 && ($row2 = $res2->fetch_assoc())) ? intval($row2['c']) : 0;
if ($count2 === 0) {
    $seed = "INSERT INTO food (Food_id, Food_name, Price, Rate, Prep_time, Spice_level, Is_Jain, Food_description, category_id) VALUES
('F101','PavBhaji',100,4,'00:00:20',3,'yes','Famous item of Streets of india','1'),
('F102','VadaPav',40,4,'00:00:10',4,'yes','Famous item of Mumbai','2'),
('F103','PaniPuri',60,4.5,'00:00:20',5,'yes','Popular street food','2'),
('F104','Frankie',100,4,'00:00:20',2,'yes','Delicious healthy food','1'),
('F105','Sandwich',90,3.5,'00:01:59',3,'yes','with variety of sauces','1'),
('F106','v.Burger',120,4.5,'00:00:20',4,'yes','pretty form of potato','1'),
('F201','Biryani',220,4.6,'00:00:30',3,'no','Aromatic spiced rice with meat/veggies','1'),
('F202','Nihari',250,4.7,'00:00:45',2,'no','Slow-cooked stew rich and hearty','1'),
('F203','Burger',150,4.3,'00:00:15',3,'yes','Classic veg burger with crunchy patty','2'),
('F204','Pizza',300,4.5,'00:00:25',2,'yes','Cheesy pizza with assorted toppings','1')";
    if (!$con->query($seed)) {
        die('Failed to seed food: ' . $con->error);
    }
}

echo 'Menu tables ready.';
?>