<?php
session_start();
$conn = new mysqli("localhost","root","","ewu_event_management");
if($conn->connect_error){ die("DB Error"); }

/* ---------------- LOGOUT ---------------- */
if(isset($_GET['logout'])){
    session_destroy();
    header("Location: index.php");
    exit();
}

/* ---------------- LOGIN ---------------- */
if(isset($_POST['login'])){
    $id=$_POST['id']; 
    $pass=$_POST['pass']; 
    $role=$_POST['role'];

    if($role=="admin"){
        $q=$conn->query("SELECT * FROM manager WHERE manager_id='$id' AND manager_pass='$pass'");
        if($q->num_rows==1){ $_SESSION['admin']=$id; }
        else $err="Invalid Admin Login";
    } else {
        $q=$conn->query("SELECT * FROM customers WHERE customer_id='$id' AND customer_pass='$pass'");
        if($q->num_rows==1){ $_SESSION['customer']=$id; }
        else $err="Invalid Customer Login";
    }
}

/* ---------------- REGISTER ---------------- */
if(isset($_POST['register'])){
    $id="C".rand(1000,9999);
    $conn->query("INSERT INTO customers(customer_id,customer_pass,customer_name,customer_address,customer_contact)
    VALUES('$id','$_POST[pass]','$_POST[name]','$_POST[address]','$_POST[contact]')");
    $msg="Registration Successful. Login now.";
}

/* ---------------- ADD EVENT (ADMIN) ---------------- */
if(isset($_POST['add_event'])){
    $conn->query("INSERT INTO events(event_id,event_name,event_type,event_date,venue_id,meal_id,guest_count,ticket_cost)
    VALUES('$_POST[eid]','$_POST[ename]','$_POST[etype]','$_POST[edate]','$_POST[venue]','$_POST[meal]',$_POST[guest],$_POST[ticket])");
}

/* ---------------- DELETE EVENT ---------------- */
if(isset($_GET['del_event'])){
    $conn->query("DELETE FROM events WHERE event_id='$_GET[del_event]'");
}

/* ---------------- BOOK EVENT ---------------- */
if(isset($_POST['book'])){
    $eid=$_POST['eid'];
    $qty=$_POST['qty'];
    $cid=$_SESSION['customer'];

    $cap=$conn->query("SELECT guest_count FROM events WHERE event_id='$eid'")
         ->fetch_assoc()['guest_count'];
    $used=$conn->query("SELECT SUM(total_cost/ (SELECT ticket_cost FROM events WHERE event_id='$eid')) AS t 
                        FROM bookings WHERE event_id='$eid'")
         ->fetch_assoc()['t'] ?? 0;

    if($used+$qty > $cap){
        $err="Not enough seats available";
    } else {
        $booking_id="B".rand(1000,9999);
        $booking_date=date("Y-m-d");
        $ticket_cost=$conn->query("SELECT ticket_cost FROM events WHERE event_id='$eid'")->fetch_assoc()['ticket_cost'];
        $total_cost=$ticket_cost*$qty;

        $conn->query("INSERT INTO bookings(booking_id,booking_date,event_id,customer_id,total_cost)
        VALUES('$booking_id','$booking_date','$eid','$cid',$total_cost)");

        $payment_id="P".rand(1000,9999);
        $conn->query("INSERT INTO cashflow(payment_id,customer_id,food_cost,venue_cost,ticket_earning,sponsor_funding,payment_method)
        VALUES('$payment_id','$cid',0,0,$total_cost,0,'Bkash')");

        $msg="Booking & Payment Successful";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>EWU Event Management</title>
<style>
body{font-family:Segoe UI;background:#f4f7f6;padding:20px}
.box{background:#fff;padding:20px;border-radius:8px;max-width:900px;margin:auto}
input,select,button{padding:8px;margin:5px;width:100%}
button{background:#3498db;color:#fff;border:none}
a{color:red;text-decoration:none}
.event{border:1px solid #ddd;padding:10px;margin:5px}
</style>
</head>

<body>
<div class="box">

<?php if(!isset($_SESSION['admin']) && !isset($_SESSION['customer'])){ ?>

<h2>Login</h2>
<form method="post">
<select name="role">
<option value="admin">Admin</option>
<option value="customer">Customer</option>
</select>
<input name="id" placeholder="ID" required>
<input type="password" name="pass" placeholder="Password" required>
<button name="login">Login</button>
</form>

<h3>Register (Customer)</h3>
<form method="post">
<input name="name" placeholder="Full Name" required>
<input name="address" placeholder="Address" required>
<input name="contact" placeholder="Contact Number" required>
<input type="password" name="pass" placeholder="Password" required>
<button name="register">Register</button>
</form>

<p style="color:red"><?= $err ?? "" ?></p>
<p style="color:green"><?= $msg ?? "" ?></p>

<?php } ?>

<!-- ================= ADMIN DASHBOARD ================= -->
<?php if(isset($_SESSION['admin'])){ ?>
<h2>Admin Dashboard</h2>
<a href="?logout=1">Logout</a>

<h3>Add Event</h3>
<form method="post">
<input name="eid" placeholder="E01" required>
<input name="ename" placeholder="Event Name" required>
<select name="etype" required>
<option value="ESPORTS">ESPORTS</option>
<option value="PRESENTATION">PRESENTATION</option>
<option value="PRIZE_GIVING">PRIZE_GIVING</option>
<option value="MEETING">MEETING</option>
<option value="SEMINAR">SEMINAR</option>
<option value="WORKSHOP">WORKSHOP</option>
<option value="ENTERTAINMENT">ENTERTAINMENT</option>
</select>
<input type="date" name="edate" required>
<input name="venue" placeholder="Venue ID" required>
<input name="meal" placeholder="Meal ID" required>
<input type="number" name="guest" placeholder="Guest Count" required>
<input type="number" step="0.01" name="ticket" placeholder="Ticket Cost" required>
<button name="add_event">Add Event</button>
</form>

<h3>Events</h3>
<?php
$r=$conn->query("SELECT * FROM events");
while($e=$r->fetch_assoc()){
echo "<div class='event'>
<b>{$e['event_name']}</b> | {$e['event_type']} | {$e['event_date']} | Guests: {$e['guest_count']} | {$e['ticket_cost']} Tk
<a href='?del_event={$e['event_id']}'> Delete</a>
</div>";
}
?>

<?php } ?>

<!-- ================= CUSTOMER DASHBOARD ================= -->
<?php if(isset($_SESSION['customer'])){ ?>
<h2>Customer Dashboard</h2>
<a href="?logout=1">Logout</a>

<h3>Available Events</h3>
<?php
$r=$conn->query("SELECT * FROM events");
while($e=$r->fetch_assoc()){
echo "<form method='post' class='event'>
<b>{$e['event_name']}</b> | {$e['event_type']} | {$e['event_date']} | {$e['ticket_cost']} Tk
<input type='hidden' name='eid' value='{$e['event_id']}'>
<input type='number' name='qty' min='1' placeholder='Quantity' required>
<button name='book'>Book</button>
</form>";
}
?>

<p style="color:red"><?= $err ?? "" ?></p>
<p style="color:green"><?= $msg ?? "" ?></p>

<?php } ?>

</div>
</body>
</html>
