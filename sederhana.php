<?php
// config.php
$host = 'localhost';
$dbname = 'tiket_bus';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// search.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $departure = $_POST['departure'];
    $arrival = $_POST['arrival'];
    $date = $_POST['date'];
    
    $stmt = $pdo->prepare("
        SELECT r.*, b.name as bus_name, b.class, b.facilities 
        FROM routes r 
        JOIN buses b ON r.bus_id = b.id 
        WHERE r.departure_city LIKE ? AND r.arrival_city LIKE ?
    ");
    
    $stmt->execute(["%$departure%", "%$arrival%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($results);
}

// booking.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $route_id = $_POST['route_id'];
    $passenger_name = $_POST['passenger_name'];
    $passenger_email = $_POST['passenger_email'];
    $passenger_phone = $_POST['passenger_phone'];
    $seat_number = $_POST['seat_number'];
    
    // Insert passenger
    $stmt = $pdo->prepare("INSERT INTO passengers (name, email, phone) VALUES (?, ?, ?)");
    $stmt->execute([$passenger_name, $passenger_email, $passenger_phone]);
    $passenger_id = $pdo->lastInsertId();
    
    // Create booking
    $stmt = $pdo->prepare("INSERT INTO bookings (route_id, passenger_id, seat_number, booking_date, status) VALUES (?, ?, ?, NOW(), 'confirmed')");
    $stmt->execute([$route_id, $passenger_id, $seat_number]);
    
    echo json_encode(['success' => true, 'booking_id' => $pdo->lastInsertId()]);
}
?>