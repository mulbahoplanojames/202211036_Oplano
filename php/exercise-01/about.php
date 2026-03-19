<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - My PHP Site</title>
   <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <a href="index.php">Home</a>
        <a href="about.php" class="active">About</a>
        <a href="services.php">Services</a>
        <a href="contact.php">Contact</a>
    </nav>
    <h1>About Us</h1>
    <p>Learn more about our company and what we do.</p>
    <p>We are dedicated to providing excellent services to our clients.</p>



    <?php
        $dateOfBirth = new DateTime('1996-04-13');
        $currentDate = new DateTime();

        $interval = $currentDate->diff($dateOfBirth);
        $age = $interval->y;

        if ($age >= 18) {
            echo "You are eligible to open an account";
        } elseif ($age === 0) {
            echo "You are not yet born";
        } else {
            echo "You are not eligible to open an account";
        }
    ?>

</body>
</html>
