

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../css/profile.css">
</head>
<body>
    <?php include("../include/navbar.php")?>
    <div class="container">
        <!-- Profile Header Section -->
        <div class="profile-header">
            <h1><i class="fas fa-user-circle"></i> Profile</h1>
        </div>
        
        <!-- Profile Content Section -->
        <div class="profile-content">
            <div class="profile-image">
                <!-- Profile Picture -->
                <img src="../<?php echo $data_info["image_user"]?>" alt="Profile Picture">
            </div>
            
            <div class="profile-details">
                <!-- Profile Information -->
                <div class="info-item">
                    <span class="info-label">Name</span>
                    <span class="info-value"><i class="fas fa-user"></i> <?php echo $data_info["name_user"]?> </span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">City</span>
                    <span class="info-value"><i class="fas fa-map-marker-alt"></i> <?php echo $data_info["city"]?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value"><i class="fas fa-envelope"></i> <?php echo $data_info["email_user"]?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Phone Number</span>
                    <span class="info-value"><i class="fas fa-phone-alt"></i><?php echo $data_info["tel_user"]?> </span>
                </div>
            </div>
        </div>
        
        <!-- Edit Profile Button -->
        <button class="edit-button" onclick="window.location.href='edit_profile.php'">
            <i class="fas fa-edit"></i> Edit Profile
        </button>
    </div>
</body>
<?php include("../include/footer.php")?>
</html>

<?php
if (isset($_GET["success-message"]) && $_GET["success-message"] == 1) {
?>
    <div class="success-message">
        ✅ Information updated successfully!
    </div>
<?php
}
?>
