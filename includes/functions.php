<?php
// Security functions
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Vehicle related functions
function getAvailableVehicles($startDate, $endDate) {
    global $pdo;
    $query = "SELECT * FROM vehicles WHERE id NOT IN (
        SELECT vehicle_id FROM reservations 
        WHERE (start_date <= :endDate AND end_date >= :startDate)
        AND status != 'cancelled'
    )";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Reservation functions
function calculateTotalPrice($vehicleId, $startDate, $endDate, $options = []) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT price_per_day FROM vehicles WHERE id = ?");
    $stmt->execute([$vehicleId]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $days = (strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24);
    $basePrice = $vehicle['price_per_day'] * $days;
    
    $optionsPrice = 0;
    foreach ($options as $option) {
        $optionsPrice += getOptionPrice($option);
    }
    
    return $basePrice + $optionsPrice;
}

// Email function
function sendConfirmationEmail($email, $reservationDetails) {
    $to = $email;
    $subject = "Confirmation de réservation - The Genuis";
    $message = "Merci pour votre réservation chez The Genuis.\n\n";
    $message .= "Détails de votre réservation:\n";
    $message .= $reservationDetails;
    
    $headers = "From: noreply@thegenuis.com";
    
    return mail($to, $subject, $message, $headers);
}
?>
