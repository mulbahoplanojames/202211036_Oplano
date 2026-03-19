<?php
// Simple API test script
// Run this to test your API endpoints

$base_url = "http://localhost"; 

echo "=== Blood Donation API Test ===\n\n";

// Test 1: Get API Info
echo "1. Testing GET / (API Info)\n";
$response = file_get_contents($base_url . "/");
echo "Response: " . $response . "\n\n";

// Test 2: Get All Donors
echo "2. Testing GET /api/donors (Get All Donors)\n";
$response = file_get_contents($base_url . "/api/donors");
echo "Response: " . $response . "\n\n";

// Test 3: Register New Donor
echo "3. Testing POST /api/donors (Register Donor)\n";
$donor_data = array(
    'name' => 'Test User',
    'blood_type' => 'O+',
    'city' => 'Kigali',
    'phone' => '+250788999999',
    'last_donation_date' => '2024-01-15'
);

$options = array(
    'http' => array(
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($donor_data)
    )
);
$context  = stream_context_create($options);
$response = file_get_contents($base_url . "/api/donors", false, $context);
echo "Response: " . $response . "\n\n";

// Test 4: Search by Blood Type
echo "4. Testing GET /api/donors?blood_type=O+ (Search by Blood Type)\n";
$response = file_get_contents($base_url . "/api/donors?blood_type=O+");
echo "Response: " . $response . "\n\n";

// Test 5: Search by City
echo "5. Testing GET /api/donors?city=Kigali (Search by City)\n";
$response = file_get_contents($base_url . "/api/donors?city=Kigali");
echo "Response: " . $response . "\n\n";

// Test 6: Get Emergency Donors
echo "6. Testing GET /api/emergency-donors (Emergency Donors)\n";
$response = file_get_contents($base_url . "/api/emergency-donors");
echo "Response: " . $response . "\n\n";

echo "=== Test Complete ===\n";
?>
