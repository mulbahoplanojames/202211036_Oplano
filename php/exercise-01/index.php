<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - My PHP Site</title>
   <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <a href="index.php" class="active">Home</a>
        <a href="about.php">About</a>
        <a href="services.php">Services</a>
        <a href="contact.php">Contact</a>
    </nav>
    <h1>Welcome to the Home Page</h1>
    <p>This is the main landing page of the website.</p>


    <?php 
    //  String 
    $name = "Oplano";
    $food = "pizza";
    $email = "oplano@example.com";

    // Integer
    $age = 25;
    $users = 1000;
    $quantity = 10;

    // Float
    $GPA = 3.5;
    $price = 10.99;
    $tax_rate = 0.07;

    // Boolean
    $employed = true;
    $online = false;
    $for_sale = true;

    echo "The total price of the order is $" . ($price * $quantity) . "<br>";
    echo "This is " . $name . "<br>";
    echo "This is my " . $age . " year old<br>";
    echo "hello " . $name . "<br>";
    echo "I like " . $food . "<br>";
    echo "My email is " . $email . "<br><br>";
    
    echo "My name is " . $name . " and I am " . $age . " years old.<br>";
    echo "The price of the pizza is $" . $price . "<br>";
    echo "You are " . $age . " years old and your email is " . $email . "<br><br>";
    
    echo "There are " . $users . " users and the quantity is " . $quantity . "<br>";
    echo "The total price is $" . ($price * $quantity) . "<br>";
    echo "You would like to order " . $quantity . " pizzas and the total price is $" . ($price * $quantity) . "<br>";
    echo "Your GPA is " . $GPA . "<br>";
    echo "Your pizza is $" . $price . "<br>";
    echo "The tax is " . ($tax_rate * 100) . "%<br>";
    echo "The total price with tax is $" . ($price * (1 + $tax_rate)) . "<br>";
    echo "The total price for " . $quantity . " pizzas with tax is $" . ($price * $quantity * (1 + $tax_rate)) . "<br><br>";
    
    echo "Online status: " . ($online ? "Online" : "Offline") . "<br>";
    echo "Employment status: " . ($employed ? "Employed" : "Unemployed") . "<br>";
    echo "For sale: " . ($for_sale ? "Yes" : "No") . "<br>";
    echo "You have ordered " . $quantity . " pizzas and the total price with tax is $" . ($price * $quantity * (1 + $tax_rate)) . "<br>";
    echo "You have ordered " . $quantity . " x pizzas<br>";
    echo "Your total price with tax is $" . ($price * $quantity) . "<br>";

    
 //     ?>
</body>
</html>
