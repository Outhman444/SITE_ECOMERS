<?php
// update_time.php
include("../include/database.php");

if (isset($_POST['product_id'])) {
    // جلب بيانات المنتج من قاعدة البيانات
    $product_id = $_POST['product_id'];
    $selct_produit = $conection->prepare("SELECT * FROM auctions WHERE id =?");
    
    $selct_produit->execute([$product_id]);
    $produit = $selct_produit->fetch(PDO::FETCH_ASSOC);

    $endTime = strtotime($produit["end_date"]);
    $currentTime = time();
    $timeRemaining = $endTime - $currentTime;











    if ($timeRemaining > 0) {

        $days = floor($timeRemaining / (60 * 60 * 24));
        $hours = floor(($timeRemaining % (60 * 60 * 24)) / (60 * 60));
        $minutes = floor(($timeRemaining % (60 * 60)) / 60);
        $seconds = floor($timeRemaining % 60);
    } else {
        $days = $hours = $minutes = $seconds = 0;
    }

 
    echo json_encode([
        'days' => $days,
        'hours' => $hours,
        'minutes' => $minutes,
        'seconds' => $seconds
    ]);
}
?>
