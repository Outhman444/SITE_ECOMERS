<?php
session_start();
if(!isset($_SESSION["user_information"])){
    header("Location:../index.php");
}
$data_info=$_SESSION["user_information"];
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electronic QuickBid System</title>
    <link rel="stylesheet" href="../css/navbar.css">
</head>
<body>
    <!-- Header Structure -->
    <header class="header">
        <div class="header-container">
            <!-- Logo -->
            <a href="../home/home.php" class="logo">
                <i class="fas fa-gavel"></i>
                Auction System
            </a>

            <!-- Mobile Menu Button -->
            <button class="menu-toggle">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Navigation Menu -->
            <ul class="nav-menu">
                <li><a href="../home/home.php">Home</a></li>
                <li><a href="../home/add_Auction.php">Add Auction</a></li>
                <li><a href="../home/view_Auction.php">Auction List</a></li>
                <li><a href="results.html">Auction Results</a></li>
                <li><a href="terms.html">Terms & Policies</a></li>
            </ul>

            <!-- User Menu -->
            <div class="user-menu">
                <!-- Search Box -->
                <div class="search-box">
                    <input type="text" placeholder="Search auctions...">
                    <button><i class="fas fa-search"></i></button>
                </div>

                <!-- User Profile Dropdown -->
                <div class="dropdown">
                    <div class="user-profile">
                        <div class="user-avatar">
                            <img src="../<?php echo $data_info['image_user'] ?>" alt="User Profile">
                        </div>
                        <span class="user-name"><?php echo $data_info['name_user'] ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-content">
                        <a href="../home/profile.php"><i class="fas fa-user"></i> My Profile</a>
                        <a href="my-auctions.html"><i class="fas fa-gavel"></i> My Auctions</a>
                        <a href="my-bids.html"><i class="fas fa-hand-paper"></i> My Bids</a>
                        <a href="dashboard.html"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                        <a href="../logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Page Content -->
    <div class="content">
        <!-- Content goes here -->
    </div>

    <!-- Adding Font Awesome for icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>

    <!-- Script for mobile menu toggle -->
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        let dropdown = document.querySelector(".dropdown"); // العنصر الرئيسي للقائمة
        let dropdownBtn = document.querySelector(".user-profile"); // الزر الذي عند الضغط عليه تظهر القائمة
        let dropdownContent = document.querySelector(".dropdown-content");

        dropdownBtn.addEventListener("click", function (event) {
            event.stopPropagation();
            dropdownContent.classList.toggle("show");
        });

        document.addEventListener("click", function (event) {
            if (!dropdown.contains(event.target)) {
                dropdownContent.classList.remove("show");
            }
        });
        
        // Toggle mobile menu visibility
        const toggleButton = document.querySelector(".menu-toggle");  // Corrected class name
        const navMenu = document.querySelector(".nav-menu");

        toggleButton.addEventListener("click", function () {
            navMenu.style.display = navMenu.style.display === "block" ? "none" : "block";
        });
    });
    </script>
</body>
</html>
