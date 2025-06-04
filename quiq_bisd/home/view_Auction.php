<?php
include("../include/database.php");

// Retrieve auction data with user
$selct_produit = $conection->prepare("
    SELECT DISTINCT auctions.*, user.name_user, user.image_user 
    FROM auctions 
    JOIN user ON auctions.id_user = user.id_user
    WHERE auctions.end_date > NOW()
    ORDER BY auctions.start_date DESC
");
$selct_produit->execute();
$produits_assoc = $selct_produit->fetchAll(PDO::FETCH_ASSOC);

// Container for displaying error or success messages
$message = '';

if (isset($_POST["bid"])) {
    if (!empty($_POST["price"]) && isset($_POST["auction_id"])) {
        // Convert values to numbers for correct comparison
        $new_price = floatval($_POST["price"]);
        $auction_id = intval($_POST["auction_id"]);

        // Retrieve selected auction data directly
        $get_auction = $conection->prepare("SELECT id, id_user, starting_price FROM auctions WHERE id = :auction_id AND end_date > NOW()");
        $get_auction->bindParam(':auction_id', $auction_id, PDO::PARAM_INT);
        $get_auction->execute();
        $auction = $get_auction->fetch(PDO::FETCH_ASSOC);

        if ($auction) {
            // Retrieve user_id from cookies
            $user_id = isset($_COOKIE["user_id"]) ? intval($_COOKIE["user_id"]) : 1; // Default to 1 if cookie is not set
            $current_price = floatval($auction["starting_price"]);

            // Check that the new bid is higher than the current price
            if ($new_price > $current_price) {
                // Insert the new bid into the bids table
                $insert_bid = $conection->prepare("INSERT INTO bids (id_user, auction_id, price) VALUES (:user_id, :auction_id, :price)");
                $insert_bid->execute([
                    ":user_id" => $user_id,
                    ":auction_id" => $auction_id,
                    ":price" => $new_price
                ]);

                // Update the starting price in the auction table
                $update_auction = $conection->prepare("UPDATE auctions SET starting_price = :new_price WHERE id = :auction_id");
                $update_auction->execute([
                    ":auction_id" => $auction_id,
                    ":new_price" => $new_price
                ]);
                $message = "<p class='success'>Bid placed successfully! New price is: $new_price MAD</p>";
                header("Refresh: 1; url=" . $_SERVER['PHP_SELF']);
            } else {
                $message = "<p class='error'>Bid amount must be higher than the current price ($current_price MAD)!</p>";
            }
        } else {
            $message = "<p class='error'>No active auction found!</p>";
        }
    } else {
        $message = "<p class='error'>Please enter a price!</p>";
    }
}

// Calculate remaining time for countdown
$countdowns = [];
$current_time = new DateTime();
foreach ($produits_assoc as $produit) {
    $end_date = new DateTime($produit['end_date']);
    $interval = $current_time->diff($end_date);

    $countdowns[$produit['id']] = [
        'days' => $interval->days,
        'hours' => $interval->h,
        'minutes' => $interval->i,
        'seconds' => $interval->s
    ];
}
?>


<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-RMTZ216R9L"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-RMTZ216R9L');
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auction Product Card</title>
    <link rel="stylesheet" href="../css/view_Auction.css">
        <link rel="icon" type="image/jpeg" href="../image/images.jpeg">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body>

    <?php include("../include/navbar.php"); ?>

    <!-- Display messages if any -->
    <?php if (!empty($message)): ?>
        <div class="message-container">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="auction-container">
        <?php foreach ($produits_assoc as $produit): ?>
            <form method="POST">
                <div class="auction-card" data-product-id="<?= $produit['id']; ?>">
                    <div class="image-slider">
                        <div class="category"><?php echo ($produit["category"]); ?></div>
                        <div class="slider-wrapper">
                            <?php
                            $product_images = explode(",", $produit["images"]);
                            foreach ($product_images as $image): ?>
                                <img src="<?php echo ($image); ?>" alt="Product Image">
                            <?php endforeach; ?>
                        </div>

                        <div class="slider-nav">
                            <?php for ($i = 0; $i < count($product_images); $i++): ?>
                                <div class="slider-dot <?php echo $i === 0 ? 'active' : ''; ?>"></div>
                            <?php endfor; ?>
                        </div>

                        <div class="slider-arrows">
                            <div class="arrow prev">❮</div>
                            <div class="arrow next">❯</div>
                        </div>
                    </div>

                    <div class="product-info">
                        <div class="containername_title">
                            <h2 class="product-title"><?php echo ($produit["product_name"]); ?></h2>
                            <div class="user-info">
                                <img src="../<?php echo ($produit["image_user"]); ?>" alt="user image" class="user-profile-image">
                                <a href="open_messages.php?user_id=<?php echo $produit["id_user"]; ?>&auction_id=<?php echo $produit["id"]; ?>" class="name_user">
                                    <?php echo ($produit["name_user"]); ?>
                                </a>
                            </div>
                        </div>
                        <p class="product-description">
                            <?php echo ($produit["description"]); ?>
                        </p>

                        <div class="price-section">
                            <div class="current-price">
                                <i class="fas fa-tag"></i> <?php echo ($produit["starting_price"]); ?> <span class="name-money">USD</span>
                            </div>
                            <div class="bid-count">
                                <i class="fas fa-gavel"></i>
                                <?php
                                $bid_count = $conection->prepare("SELECT COUNT(*) FROM bids WHERE auction_id = :auction_id");
                                $bid_count->execute([":auction_id" => $produit["id"]]);
                                echo $bid_count->fetchColumn() . " Bids";
                                ?>
                            </div>
                        </div>

                        <div class="countdown-section">
                            <div class="countdown-title">
                                <span class="icon-container"><i class="fas fa-hourglass-half icon-timer"></i></span>
                                Remaining time for the auction
                            </div>
                            <div class="countdown">
                                <div class="countdown-item">
                                    <div class="countdown-value" id="days-<?php echo $produit['id']; ?>">
                                        <?php echo $countdowns[$produit['id']]['days']; ?>
                                    </div>
                                    <div class="countdown-label">Days</div>
                                </div>
                                <div class="countdown-item">
                                    <div class="countdown-value" id="hours-<?php echo $produit['id']; ?>">
                                        <?php echo $countdowns[$produit['id']]['hours']; ?>
                                    </div>
                                    <div class="countdown-label">Hours</div>
                                </div>
                                <div class="countdown-item">
                                    <div class="countdown-value" id="minutes-<?php echo $produit['id']; ?>">
                                        <?php echo $countdowns[$produit['id']]['minutes']; ?>
                                    </div>
                                    <div class="countdown-label">Minutes</div>
                                </div>
                                <div class="countdown-item">
                                    <div class="countdown-value" id="seconds-<?php echo $produit['id']; ?>">
                                        <?php echo $countdowns[$produit['id']]['seconds']; ?>
                                    </div>
                                    <div class="countdown-label">Seconds</div>
                                </div>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <!-- Use step="0.01" to allow decimal values -->
                            <input type="number" class="bid-input" placeholder="Enter bid amount"
                                min="<?php echo floatval($produit["starting_price"]) + 0.01; ?>"
                                step="0.01" name="price" />
                            <input type="hidden" name="auction_id" value="<?php echo $produit['id']; ?>" />
                            <button class="bid-button" type="submit" name="bid">Bid Now</button>
                            <button type="button" class="share-button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M13.5 1a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM11 2.5a2.5 2.5 0 1 1 .603 1.628l-6.718 3.12a2.499 2.499 0 0 1 0 1.504l6.718 3.12a2.5 2.5 0 1 1-.488.876l-6.718-3.12a2.5 2.5 0 1 1 0-3.256l6.718-3.12A2.5 2.5 0 0 1 11 2.5zm-8.5 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm11 5.5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        <?php endforeach; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Function to update countdown via AJAX
        function updateCountdown(productId) {
            $.ajax({
                url: 'update_time.php',
                type: 'POST',
                data: {
                    product_id: productId
                },
                success: function(response) {
                    try {
                        var data = JSON.parse(response);

                        $('#days-' + productId).text(data.days);
                        $('#hours-' + productId).text(data.hours);
                        $('#minutes-' + productId).text(data.minutes);
                        $('#seconds-' + productId).text(data.seconds);
                    } catch (e) {
                        console.error("Error parsing response:", e);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error connecting to server:", error);
                }
            });
        }

        <?php foreach ($produits_assoc as $produit): ?>
            setInterval(function() {
                updateCountdown(<?php echo $produit['id']; ?>);
            }, 1000);
        <?php endforeach; ?>

        // Add auto-dismiss functionality for messages
        document.addEventListener('DOMContentLoaded', function() {
            const messageContainer = document.querySelector('.message-container');
            if (messageContainer) {
                // Auto-dismiss after 5 seconds
                setTimeout(function() {
                    messageContainer.style.opacity = '0';
                    setTimeout(function() {
                        messageContainer.style.display = 'none';
                    }, 500);
                }, 5000);

                // Close button functionality
                messageContainer.addEventListener('click', function(e) {
                    if (e.target.tagName.toLowerCase() !== 'p') {
                        messageContainer.style.opacity = '0';
                        setTimeout(function() {
                            messageContainer.style.display = 'none';
                        }, 500);
                    }
                });
            }
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.auction-card').forEach(card => {
                const sliderWrapper = card.querySelector('.slider-wrapper');
                const dots = card.querySelectorAll('.slider-dot');
                const prevBtn = card.querySelector('.prev');
                const nextBtn = card.querySelector('.next');
                const slides = card.querySelectorAll('.slider-wrapper img');

                if (!sliderWrapper || slides.length === 0) return;

                let currentIndex = 0;
                const slideCount = slides.length;

                function updateSlider() {
                    sliderWrapper.style.transform = `translateX(${currentIndex * -100}%)`;
                    dots.forEach((dot, index) => {
                        dot.classList.toggle('active', index === currentIndex);
                    });
                }

                dots.forEach((dot, index) => {
                    dot.addEventListener('click', () => {
                        currentIndex = index;
                        updateSlider();
                    });
                });

                prevBtn.addEventListener('click', (event) => {
                    event.preventDefault();
                    currentIndex = (currentIndex - 1 + slideCount) % slideCount;
                    updateSlider();
                });

                nextBtn.addEventListener('click', (event) => {
                    event.preventDefault();
                    currentIndex = (currentIndex + 1) % slideCount;
                    updateSlider();
                });
            });

            document.querySelectorAll('.share-button').forEach(button => {
                button.addEventListener('click', (event) => {
                    event.preventDefault();
                    alert('Product link copied, you can share it now!');
                });
            });
        });
    </script>

</body>

</html>