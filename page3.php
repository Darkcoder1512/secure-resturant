<?php
require_once __DIR__ . '/connect.php';
require_once __DIR__ . '/csrf.php';
// Basic security headers to reduce common risks
header_remove('X-Powered-By');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; script-src 'self'");
if (!isset($con) || !($con instanceof mysqli)) { die('Unable to connect..!!'); }
// For all POST actions from page2.php, verify CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$tok = $_POST['csrf_token'] ?? '';
	if (!csrf_check($tok)) {
		http_response_code(403);
		die('Invalid CSRF token');
	}
}
$con->query("CREATE TABLE IF NOT EXISTS `order_status` (
  `Order_id` int(11) NOT NULL,
  `serve_status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1");
$query="select * from order_status";
$result=mysqli_query($con,$query);
	
     if(isset($_POST['menu']))/*Display menu*/
	{
		require_once __DIR__ . '/connect.php';
		if(!$con || !($con instanceof mysqli)) { die('Unable to connect..!!'); }
				
		// Use direct SELECT instead of stored procedure
		$q = "SELECT food.category_id, categories.category_name, food.Food_id, food.Food_name, food.Price, food.Rate, food.Prep_time, food.Spice_level, food.Is_Jain, food.Food_description FROM food LEFT JOIN categories ON categories.category_id = food.category_id ORDER BY food.category_id, food.spice_level ASC";
		$result=mysqli_query($con,$q);
	
		if(mysqli_num_rows($result))
		{
			echo "<h1 >&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;Menu</h1>";
			echo " 	category_id	&emsp;	category name &emsp; food id &emsp; food name &emsp; price &emsp; rate &emsp; prep_time &emsp; spice_level &emsp; is_jain &emsp; food_description &emsp;<br/>";
			while($row=mysqli_fetch_array($result))
			{
				echo "<br/>&emsp;&emsp;&emsp;".$row['category_id'];
				echo "&emsp;&emsp;&emsp;".$row['category_name'];				
				echo "&emsp;&emsp;&emsp;".$lname=$row['Food_id'];
				echo "&emsp;&emsp;&emsp;".$city=$row['Food_name'];
				echo "&emsp;&emsp;".$cno=$row['Prep_time'];
				echo "&emsp;&emsp;&emsp;".$salary=$row['Spice_level'];
				echo "&emsp;&emsp;&emsp;".$state=$row['Is_Jain'];				
				echo "&emsp;&emsp;".$address=$row['Price'];
				echo "&emsp;&emsp;".$pcode=$row['Rate'];
				echo "&emsp;&emsp;".$state=$row['Food_description'];
				echo "<br/>";
				
			}
				
		}
		else
		{
			echo 'Data is not found ...!!';
		}
	   header("refresh:5; url=page2.php");
	}
	else if(isset($_POST['book']))/*book table*/
	{
		require_once __DIR__ . '/connect.php';
		if(!$con || !($con instanceof mysqli)) { die('Unable to connect..!!'); }
		$con->set_charset('utf8mb4');

		// Ensure bookings table exists
		$con->query("CREATE TABLE IF NOT EXISTS table_bookings (
		  id INT AUTO_INCREMENT PRIMARY KEY,
		  customer_name VARCHAR(100) NOT NULL,
		  people_count INT NOT NULL,
		  table_no VARCHAR(20) NOT NULL,
		  booking_time DATETIME NOT NULL,
		  status ENUM('Booked','Seated','Cancelled') NOT NULL DEFAULT 'Booked'
		) ENGINE=InnoDB DEFAULT CHARSET=latin1");

		// Inputs (aligning with existing form names where possible)
		$customerName = isset($_POST['custid']) ? trim($_POST['custid']) : '';
		$peopleCount = isset($_POST['mem']) ? (int)$_POST['mem'] : 0;
		$tableNo = isset($_POST['table_no']) ? trim($_POST['table_no']) : '';
		$bookingTime = date('Y-m-d H:i:s');

		if ($customerName === '' || $peopleCount <= 0) {
			echo "Please provide customer id/name and number of members.";
		} else {
			// Assign a table if not provided: pick smallest available label T1..T20
			if ($tableNo === '') {
				$assigned = '';
				for ($i=1; $i<=20; $i++) {
					$candidate = 'T'.$i;
					$stmt = $con->prepare("SELECT id FROM table_bookings WHERE table_no = ? AND status IN ('Booked','Seated') LIMIT 1");
					$stmt->bind_param('s', $candidate);
					$stmt->execute();
					$stmt->store_result();
					if ($stmt->num_rows === 0) { $assigned = $candidate; $stmt->close(); break; }
					$stmt->close();
				}
				$tableNo = $assigned !== '' ? $assigned : '';
			}

			if ($tableNo === '') {
				echo "No tables available right now.";
			} else if ($peopleCount > 6) {
				echo "This table cannot accommodate more than 6 people.";
			} else {
				// Ensure not already booked
				$stmt = $con->prepare("SELECT id FROM table_bookings WHERE table_no = ? AND status IN ('Booked','Seated') LIMIT 1");
				$stmt->bind_param('s', $tableNo);
				$stmt->execute();
				$stmt->store_result();
				$taken = $stmt->num_rows > 0;
				$stmt->close();

				if ($taken) {
					echo "Selected table is already booked or occupied.";
				} else {
					// Insert booking
					$ins = $con->prepare('INSERT INTO table_bookings (customer_name, people_count, table_no, booking_time, status) VALUES (?,?,?,?,\'Booked\')');
					$ins->bind_param('siss', $customerName, $peopleCount, $tableNo, $bookingTime);
					if ($ins->execute()) {
						echo "Your table id is: " . htmlspecialchars($tableNo) . " for " . htmlspecialchars($customerName) . ".";
					} else {
						echo "Failed to book table: " . mysqli_error($con);
					}
					$ins->close();
				}
			}
		}
	   header("refresh:9; url=page2.php");
	}
	else if(isset($_POST['served']))/*order status*/
	{
		
		$con = mysqli_connect('localhost','root','');
		if(!$con)
		{
			die('Unable to connect..!!');
		}
		
		mysqli_select_db($con,'restaurant');
		
		$var3= isset($_POST['orderid1']) ? (int)$_POST['orderid1'] : 0;
		// Use prepared statements to avoid injection & ensure numeric Order_id
		$stmt = $con->prepare("UPDATE order_status SET serve_status='C served' WHERE Order_id=?");
		$stmt->bind_param('i', $var3);
		$stmt->execute();
		$stmt->close();
		$con->query("UPDATE order_status SET serve_status='served' WHERE serve_status='C served'");
		if(mysqli_affected_rows($con)>0)
		{
			echo "Order Served";
		}
		else
		{
			echo "Order not Served";
		}	
		
	   header("refresh:2; url=page2.php");
	}
	else if(isset($_POST['Calculatedis']))/*cal disc*/
	{
			require_once __DIR__ . '/connect.php';
			if(!$con || !($con instanceof mysqli)) { die('Unable to connect..!!'); }
			$con->set_charset('utf8mb4');
			// Ensure bill table exists
			$con->query("CREATE TABLE IF NOT EXISTS bill (
			  Bill_no int(11) NOT NULL AUTO_INCREMENT,
			  Amount bigint(20) DEFAULT NULL,
			  Net_amount bigint(20) DEFAULT NULL,
			  Tax float DEFAULT NULL,
			  Discount float DEFAULT NULL,
			  Table_id varchar(8) DEFAULT NULL,
			  Cust_id varchar(8) DEFAULT NULL,
			  Order_id int(11) DEFAULT NULL,
			  PRIMARY KEY (Bill_no)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1");

			$oid = (int)($_POST['orderid2'] ?? 0);
			// Fetch timing and table/customer info
			$info = $con->query("SELECT Start_time, End_time, Approx_Prep_time, Table_id, Cust_id FROM order_details WHERE Order_id=".$oid." LIMIT 1");
			if ($info && $row = $info->fetch_assoc()) {
				$start = $row['Start_time'];
				$end = $row['End_time'];
				$approx = $row['Approx_Prep_time'];
				$tid = $row['Table_id'];
				$cid = $row['Cust_id'];
				$start_ts = strtotime($start ?: '00:00:00');
				$end_ts = strtotime($end ?: date('H:i:s'));
				$approx_ts = strtotime($approx ?: '00:00:00');
				$diff_seconds = max(0, $end_ts - $start_ts);
				$approx_seconds = max(0, $approx_ts - strtotime('00:00:00'));
				$disc = ($diff_seconds > $approx_seconds) ? 300 : 0;
				// Insert bill discount row for this order using prepared statement
				$ins = $con->prepare('INSERT INTO bill (Discount, Order_id, Table_id, Cust_id) VALUES (?, ?, ?, ?)');
				$ins->bind_param('diss', $disc, $oid, $tid, $cid);
				$ins->execute();
				$ins->close();
				echo "<br/> Discount : " . $disc;
			} else {
				echo 'Order not found for discount calculation';
			}
			header("refresh:10; url=page2.php");
	}
	else if(isset($_POST['Calculatebill']))/*cal bill*/
	{
			require_once __DIR__ . '/connect.php';
			if(!$con || !($con instanceof mysqli)) { die('Unable to connect..!!'); }
			$con->set_charset('utf8mb4');
			// Ensure bill table exists
			$con->query("CREATE TABLE IF NOT EXISTS bill (
			  Bill_no int(11) NOT NULL AUTO_INCREMENT,
			  Amount bigint(20) DEFAULT NULL,
			  Net_amount bigint(20) DEFAULT NULL,
			  Tax float DEFAULT NULL,
			  Discount float DEFAULT NULL,
			  Table_id varchar(8) DEFAULT NULL,
			  Cust_id varchar(8) DEFAULT NULL,
			  Order_id int(11) DEFAULT NULL,
			  PRIMARY KEY (Bill_no)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1");

			$oid = (int)($_POST['orderid2'] ?? 0);
			// Sum item amounts for the order
			$sumStmt = $con->prepare('SELECT SUM(Amount) AS tot FROM Order_items WHERE Order_id=?');
			$sumStmt->bind_param('i', $oid);
			$sumStmt->execute();
			$sumRes = $sumStmt->get_result();
			$tot = 0;
			if ($sumRes && ($srow = $sumRes->fetch_assoc()) && $srow['tot'] !== null) {
				$tot = (float)$srow['tot'];
			}
			// Get discount if present
			$discStmt = $con->prepare('SELECT Discount, Table_id, Cust_id FROM bill WHERE Order_id=? ORDER BY Bill_no DESC LIMIT 1');
			$discStmt->bind_param('i', $oid);
			$discStmt->execute();
			$discRes = $discStmt->get_result();
			$disc = 0.0; $tid = null; $cid = null;
			if ($discRes && ($drow = $discRes->fetch_assoc())) {
				$disc = (float)($drow['Discount'] ?? 0);
				$tid = $drow['Table_id'];
				$cid = $drow['Cust_id'];
			} else {
				// fallback table/customer from order_details
				$info = $con->query("SELECT Table_id, Cust_id FROM order_details WHERE Order_id=".$oid." LIMIT 1");
				if ($info && ($ir = $info->fetch_assoc())) { $tid = $ir['Table_id']; $cid = $ir['Cust_id']; }
			}
			$tax = round($tot * 0.18, 2);
			$net = max(0, $tot + $tax - $disc);
			// Upsert bill
			$ins = $con->prepare('INSERT INTO bill (Amount, Net_amount, Tax, Discount, Table_id, Cust_id, Order_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
			$ins->bind_param('dddddsi', $tot, $net, $tax, $disc, $tid, $cid, $oid);
			if ($ins->execute()) {
				echo 'Bill calculated successfully';
			} else {
				echo 'Bill calculation failed';
			}
			header("refresh:10; url=page2.php");
	}
	else if(isset($_POST['paybill']))/*pay bill*/
	{
			require_once __DIR__ . '/connect.php';
			if(!$con || !($con instanceof mysqli)) { die('Unable to connect..!!'); }
			$con->set_charset('utf8mb4');
			// Ensure Payement table exists (matching dump ordering)
			$con->query("CREATE TABLE IF NOT EXISTS Payement (
			  Payement_date date DEFAULT NULL,
			  receipt varchar(50) DEFAULT NULL,
			  Payement_mode varchar(10) DEFAULT NULL,
			  Paid_amount bigint(20) DEFAULT NULL,
			  Bill_no int(11) DEFAULT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=latin1");

			$method = $_POST['method'] ?? '';
			$billno = (int)($_POST['billno2'] ?? 0);
			// Fetch net amount from bill
			$b = $con->query("SELECT Net_amount FROM bill WHERE Bill_no=".$billno." LIMIT 1");
			if ($b && ($br = $b->fetch_assoc()) && $br['Net_amount'] !== null) {
				$paid = (float)$br['Net_amount'];
				$ins = $con->prepare('INSERT INTO Payement (Payement_date, receipt, Payement_mode, Paid_amount, Bill_no) VALUES (CURDATE(), ?, ?, ?, ?)');
				$empty = '';
				$ins->bind_param('ssdi', $empty, $method, $paid, $billno);
				if ($ins->execute()) {
					echo 'Payment successfully done..';
				} else {
					echo 'Payment not done..';
				}
			} else {
				echo 'Bill not found';
			}
			header("refresh:10; url=page2.php");
	}
	else if(isset($_POST['place']))/*place order*/
	{
		require_once __DIR__ . '/connect.php';
		if(!$con || !($con instanceof mysqli)) { die('Unable to connect..!!'); }
		$con->set_charset('utf8mb4');
				// Ensure order_details table exists
		$con->query("CREATE TABLE IF NOT EXISTS order_details (
		  Cust_id varchar(8) DEFAULT NULL,
		  Order_date date DEFAULT NULL,
		  Start_time time DEFAULT NULL,
		  End_time time DEFAULT NULL,
		  Approx_Prep_time time DEFAULT NULL,
		  Table_id varchar(8) DEFAULT NULL,
		  order_id int(11) NOT NULL AUTO_INCREMENT,
		  PRIMARY KEY (order_id)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1");
		
		$cid = isset($_POST['cid']) ? trim($_POST['cid']) : '';
		$foodname = isset($_POST['fname']) ? trim($_POST['fname']) : '';
		$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
		$jain = isset($_POST['jain']) ? trim($_POST['jain']) : '';
		
		// Find price and food_id
		$stmt = $con->prepare('SELECT Price, Food_id FROM Food WHERE Food_name = ? LIMIT 1');
		$stmt->bind_param('s', $foodname);
		$stmt->execute();
		$res = $stmt->get_result();
		if ($res && $row = $res->fetch_assoc()) {
			$price = intval($row['Price']);
			$fid = $row['Food_id'];
			$amount = $price * max(1, $quantity);
			// Get current order_id for customer if exists
			$oidRes = $con->query("SELECT Order_id FROM order_details WHERE Cust_id='".$con->real_escape_string($cid)."' LIMIT 1");
			$oid = null;
			if ($oidRes && $oidRow = $oidRes->fetch_assoc()) { $oid = $oidRow['Order_id']; }
			// If no order, create basic order_details row
			if (!$oid) {
				$con->query("INSERT INTO order_details (Cust_id, Start_time) VALUES ('".$con->real_escape_string($cid)."', CURTIME())");
				$oid = $con->insert_id ?: ($con->query("SELECT Order_id FROM order_details WHERE Cust_id='".$con->real_escape_string($cid)."' ORDER BY order_id DESC LIMIT 1") && ($tmp=$con->store_result()));
			}
			// Ensure Order_items table exists
			$con->query("CREATE TABLE IF NOT EXISTS Order_items (
			  Price int(11) DEFAULT NULL,
			  Quantity int(11) DEFAULT NULL,
			  Amount int(11) DEFAULT NULL,
			  Food_id varchar(8) DEFAULT NULL,
			  Foodname varchar(20) DEFAULT NULL,
			  jain varchar(3) DEFAULT NULL,
			  Order_id int(11) DEFAULT NULL,
			  ord_time time DEFAULT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=latin1");
			// Insert order item
			$ins = $con->prepare('INSERT INTO Order_items (Price, Quantity, Amount, Food_id, Foodname, jain, Order_id, ord_time) VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIME)');
			$ins->bind_param('iiisssi', $price, max(1,$quantity), $amount, $fid, $foodname, $jain, $oid);
			if ($ins->execute()) {
				// Mark order_details date, reset times, ensure status table exists and set not served
				$con->query("UPDATE order_details SET Order_date=CURRENT_DATE, End_time=NULL, Approx_Prep_time=NULL WHERE Cust_id='".$con->real_escape_string($cid)."'");
				$con->query("CREATE TABLE IF NOT EXISTS order_status (Order_id int(11) NOT NULL, serve_status varchar(20) DEFAULT NULL, PRIMARY KEY (Order_id)) ENGINE=InnoDB DEFAULT CHARSET=latin1");
				// Upsert 'Not served' status
				$oidInt = (int)$oid;
				$chk = $con->query("SELECT Order_id FROM order_status WHERE Order_id=".$oidInt." LIMIT 1");
				if ($chk && $chk->num_rows > 0) {
					$con->query("UPDATE order_status SET serve_status='Not served' WHERE Order_id=".$oidInt);
				} else {
					$con->query("INSERT INTO order_status (Order_id, serve_status) VALUES (".$oidInt.", 'Not served')");
				}
				echo 'Order placed successfully ..';
			} else {
				echo 'Order not placed ..';
			}
		} else {
			echo 'Food not found ..';
		}
	   header("refresh:10; url=page2.php");
	}
	else if(isset($_POST['showstatus']))/*show order status*/
	{
		require_once __DIR__ . '/connect.php';
		if(!$con || !($con instanceof mysqli)) { die('Unable to connect..!!'); }
		$con->set_charset('utf8mb4');
		// Ensure order_status table exists
		$con->query("CREATE TABLE IF NOT EXISTS order_status (Order_id int(11) NOT NULL, serve_status varchar(20) DEFAULT NULL, PRIMARY KEY (Order_id)) ENGINE=InnoDB DEFAULT CHARSET=latin1");
		$res = $con->query("SELECT Order_id, serve_status FROM order_status ORDER BY Order_id DESC");
		if ($res && $res->num_rows > 0) {
			echo "<h3>Order Status</h3>";
			echo "Order_id &emsp; Status<br/>";
			while ($row = $res->fetch_assoc()) {
				echo (int)$row['Order_id'] . "&emsp;" . htmlspecialchars($row['serve_status']) . "<br/>";
			}
		} else {
			echo 'No orders yet.';
		}
		header("refresh:10; url=page2.php");
	}
	else if(isset($_POST['gete']))/*get emp data*/
	{
		require_once __DIR__ . '/connect.php';
		if(!$con || !($con instanceof mysqli)) { die('Unable to connect..!!'); }
		$con->set_charset('utf8mb4');
		// Ensure employee table exists
		$con->query("CREATE TABLE IF NOT EXISTS employee (
		  Emp_id varchar(8) NOT NULL,
		  Emp_fname varchar(10) NOT NULL,
		  Designation varchar(10) DEFAULT NULL,
		  Work_type varchar(30) DEFAULT NULL,
		  Address varchar(50) DEFAULT NULL,
		  City varchar(10) DEFAULT NULL,
		  Emp_lname varchar(10) NOT NULL,
		  State varchar(20) DEFAULT NULL,
		  Pincode varchar(6) DEFAULT NULL,
		  Contact_no varchar(13) DEFAULT NULL,
		  Emp_salary float DEFAULT NULL,
		  hiring_date date DEFAULT NULL,
		  bdate date DEFAULT NULL,
		  PRIMARY KEY (Emp_id)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1");

		$q = "SELECT Emp_id, Emp_fname, Emp_lname, Emp_salary, hiring_date FROM employee ORDER BY Emp_id";
		$res = mysqli_query($con, $q);
		if (!$res) {
			echo 'Employee query failed: ' . mysqli_error($con);
		} else if (mysqli_num_rows($res) > 0) {
			echo "<h3>Employee basic data</h3>";
			echo "Emp_id &emsp; First Name &emsp; Last Name &emsp; Salary &emsp; Hiring Date<br/>";
			while ($row = mysqli_fetch_assoc($res)) {
				echo $row['Emp_id'] . "&emsp;" . $row['Emp_fname'] . "&emsp;" . $row['Emp_lname'] . "&emsp;" . $row['Emp_salary'] . "&emsp;" . $row['hiring_date'] . "<br/>";
			}
		} else {
			echo 'No employees found.';
		}
	   header("refresh:10; url=page2.php");
	}
	else if(isset($_POST['varyprice']))/*price variation*/
	{
		
		$con = mysqli_connect('localhost','root','');
		if(!$con)
		{
			die('Unable to connect..!!');
		}
		
		mysqli_select_db($con,'restaurant');

		// Ensure price_increase table exists
		$con->query("CREATE TABLE IF NOT EXISTS price_increase (
		  cur_date datetime DEFAULT NULL,
		  field_name varchar(50) DEFAULT NULL,
		  before_value float DEFAULT NULL,
		  after_value float DEFAULT NULL,
		  Food_id varchar(8) DEFAULT NULL,
		  Food_name varchar(20) DEFAULT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=latin1");
		
		$query10 = "select * from price_increase ORDER BY cur_date DESC";
		$res10=mysqli_query($con,$query10);
		if($res10 && mysqli_num_rows($res10) > 0)
		{
			echo "<br/> Date of inc/dec: &emsp;&emsp; field name: &emsp;&emsp; before value :  &emsp;&emsp;after value: ";
			while($row=mysqli_fetch_array($res10))
			{
				echo "<br/>&emsp;&emsp;&emsp;"   .$row['cur_date'];
				echo "&emsp;&emsp;&emsp;" .$row['field_name'];				
				echo "&emsp;&emsp;&emsp;&emsp;&emsp;".$row['before_value'];
				echo "&emsp;&emsp;&emsp;&emsp;&emsp;".$row['after_value'];
				echo "<br/>";
			}
		}
		else {
			echo 'No price changes recorded yet. Use "Simulate price change" to add one.';
		}
	   header("refresh:10; url=page2.php");
	   
	}
	else if(isset($_POST['varysimulate']))/*simulate a price change*/
	{
		require_once __DIR__ . '/connect.php';
		if(!$con || !($con instanceof mysqli)) { die('Unable to connect..!!'); }
		$con->set_charset('utf8mb4');
		// Ensure food and price_increase tables
		$con->query("CREATE TABLE IF NOT EXISTS food (
		  Food_id varchar(8) NOT NULL,
		  Food_name varchar(20) NOT NULL,
		  Price int(11) DEFAULT NULL,
		  Rate float DEFAULT NULL,
		  Prep_time time DEFAULT NULL,
		  Spice_level int(11) DEFAULT NULL,
		  Is_Jain varchar(3) DEFAULT NULL,
		  Food_description varchar(100) DEFAULT NULL,
		  category_id varchar(8) DEFAULT NULL,
		  PRIMARY KEY (Food_id)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1");
		$con->query("CREATE TABLE IF NOT EXISTS price_increase (
		  cur_date datetime DEFAULT NULL,
		  field_name varchar(50) DEFAULT NULL,
		  before_value float DEFAULT NULL,
		  after_value float DEFAULT NULL,
		  Food_id varchar(8) DEFAULT NULL,
		  Food_name varchar(20) DEFAULT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=latin1");

		// Pick a food row to update
		$pick = $con->query("SELECT Food_id, Food_name, Price FROM food ORDER BY Food_id LIMIT 1");
		if ($pick && ($f = $pick->fetch_assoc())) {
			$fid = $f['Food_id'];
			$fname = $f['Food_name'];
			$before = (float)$f['Price'];
			$after = $before + 10;
			$up = $con->prepare('UPDATE food SET Price=? WHERE Food_id=?');
			$up->bind_param('ds', $after, $fid);
			$up->execute();
			$up->close();
			$log = $con->prepare("INSERT INTO price_increase (cur_date, field_name, before_value, after_value, Food_id, Food_name) VALUES (NOW(), 'Price', ?, ?, ?, ?)");
			$log->bind_param('ddss', $before, $after, $fid, $fname);
			$log->execute();
			$log->close();
			echo 'Simulated price change for ' . htmlspecialchars($fname) . ' (' . htmlspecialchars($fid) . ') from ' . $before . ' to ' . $after;
		} else {
			echo 'No food items available to update.';
		}
	   header("refresh:5; url=page2.php");
	}
	else if(isset($_POST['leftemp']))/*employee left*/
	{
		
		$con = mysqli_connect('localhost','root','');
		if(!$con)
		{
			die('Unable to connect..!!');
		}
		
		mysqli_select_db($con,'restaurant');

		// Ensure left_employee table exists
		$con->query("CREATE TABLE IF NOT EXISTS left_employee (
		  user_name varchar(8) DEFAULT NULL,
		  datetime datetime NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=latin1");
		
		$query11 = "select * from left_employee";
		$res11=mysqli_query($con,$query11);
		if(mysqli_num_rows($res11))
		{
			echo "<br/> username &emsp;&emsp; date & time  ";
			while($row=mysqli_fetch_array($res11))
			{
				echo "<br/>&emsp;"   .$row['user_name'];
				echo "&emsp;&emsp;&emsp;" .$row['datetime'];		
				echo "<br/>";
			}
				
		}
	   header("refresh:10; url=page2.php");
	}
?>