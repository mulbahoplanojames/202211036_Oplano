<?php
header("Content-Type: application/json; charset=UTF-8");

echo json_encode(array(
    "status" => "success",
    "message" => "Public Emergency Blood Donation API",
    "version" => "1.0.0",
    "endpoints" => array(
        "GET /api/donors" => "Get all donors",
        "GET /api/donors?blood_type=O%2B" => "Search donors by blood type",
        "GET /api/donors?city=Kigali" => "Search donors by city",
        "POST /api/donors" => "Register a new donor",
        "DELETE /api/donors?id=1" => "Delete a donor (Admin)",
        "GET /api/emergency-donors" => "Get emergency donors (available immediately)"
    )
));
?>
