<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $location = $_POST["location"];
    $size = $_POST["size"];
    $availability = $_POST["availability"];
    $price = $_POST["price"];
    
    // Handle Image Upload
    $targetDir = "uploads/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true); // Create uploads directory if not exists
    }

    $imagePath = "";
    if (!empty($_FILES["image"]["name"])) {
        $imageName = basename($_FILES["image"]["name"]);
        $imagePath = $targetDir . $imageName;
        
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath)) {
            echo "<script>alert('Error uploading image.');</script>";
            $imagePath = "";
        }
    }

    // Insert into Database
    $query = "INSERT INTO billboards (location, size, availability, price, image) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    
    // ✅ FIX: Corrected "sssdss" → "sssds"
    $stmt->bind_param("sssds", $location, $size, $availability, $price, $imagePath);

    if ($stmt->execute()) {
        echo "<script>alert('Billboard added successfully!'); window.location='view_billboards.php';</script>";
    } else {
        echo "<script>alert('Error adding billboard.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Billboard</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 0; background: url('images/f4.jpg') no-repeat center center fixed; background-size: cover; display: flex; justify-content: flex-end; align-items: center; height: 100vh; padding-right: 50px;">

    <div style="width: 400px; background: rgba(236, 169, 68, 0.9); padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.3);">
        <h2 style="text-align: center; color: #333;">Add New Billboard</h2>

        <form method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column;">
            <label style="margin-bottom: 5px; font-weight: bold;">Location:</label>
            <input type="text" name="location" required style="padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px;">

            <label style="margin-bottom: 5px; font-weight: bold;">Size:</label>
            <input type="text" name="size" required style="padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px;">

            <label style="margin-bottom: 5px; font-weight: bold;">Availability:</label>
            <select name="availability" required style="padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px;">
                <option value="Available">Available</option>
                <option value="Booked">Booked</option>
            </select>

            <label style="margin-bottom: 5px; font-weight: bold;">Price (KSh):</label>
            <input type="number" name="price" required style="padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px;">

            <label style="margin-bottom: 5px; font-weight: bold;">Upload Image:</label>
            <input type="file" name="image" accept="image/*" required style="margin-bottom: 10px;">

            <button type="submit" style="background: #28a745; color: white; padding: 10px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;">
                Add Billboard
            </button>
        </form>

        <br>
        <a href="view_billboards.php" style="text-decoration: none; display: block; text-align: center; background: #007bff; color: white; padding: 10px; border-radius: 5px;">View Billboards</a>
    </div>

</body>
</html>
