<?php
include("../include/database.php");

// Condensed function to get auctions based on different criteria
function getAuctions($conection, $criteria, $limit = 4) { // Changed default limit to 4
    $base_query = "SELECT auctions.*, user.name_user, user.image_user";
    $joins = "FROM auctions JOIN user ON auctions.id_user = user.id_user";
    $where = "WHERE auctions.end_date > NOW()";
    $order = "";
    $group = "";
    
    // Add specific parts based on criteria
    switch($criteria) {
        case 'ending_soon':
            $where = "WHERE auctions.end_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)";
            $order = "ORDER BY auctions.end_date ASC";
            break;
        case 'most_bids':
            $base_query .= ", COUNT(bids.id) as bid_count";
            $joins .= " LEFT JOIN bids ON auctions.id = bids.auction_id";
            $group = "GROUP BY auctions.id";
            $order = "ORDER BY bid_count DESC";
            break;
        case 'popular':
            $base_query .= ", COUNT(bids.id) as bid_count, MAX(bids.price) as highest_bid";
            $joins .= " LEFT JOIN bids ON auctions.id = bids.auction_id";
            $group = "GROUP BY auctions.id";
            $order = "ORDER BY highest_bid DESC, bid_count DESC";
            break;
    }
    
    $query = "$base_query $joins $where";
    if (!empty($group)) $query .= " $group";
    if (!empty($order)) $query .= " $order";
    if ($limit) $query .= " LIMIT $limit";
    
    $stmt = $conection->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get auctions with different criteria, limited to 4 items each
$produits_ending_soon = getAuctions($conection, 'ending_soon', 4);
$produits_most_bids = getAuctions($conection, 'most_bids', 4);
$produits_popular = getAuctions($conection, 'popular', 4);

// Get additional analytics data
// Average bid increase per category
$avg_bid_increase = $conection->prepare("
    SELECT a.category, AVG(b.price - a.starting_price) as avg_increase
    FROM auctions a
    JOIN bids b ON a.id = b.auction_id
    WHERE a.end_date > NOW()
    GROUP BY a.category
    ORDER BY avg_increase DESC
");
$avg_bid_increase->execute();
$bid_increases = $avg_bid_increase->fetchAll(PDO::FETCH_ASSOC);

// Auction success rate
$auction_success = $conection->prepare("
    SELECT 
        COUNT(DISTINCT a1.id) as auctions_with_bids,
        COUNT(DISTINCT a2.id) as total_auctions,
        ROUND((COUNT(DISTINCT a1.id) / COUNT(DISTINCT a2.id)) * 100, 1) as success_rate
    FROM 
        (SELECT id FROM auctions WHERE id IN (SELECT DISTINCT auction_id FROM bids)) a1,
        (SELECT id FROM auctions WHERE end_date > NOW()) a2
");
$auction_success->execute();
$success_rate = $auction_success->fetch(PDO::FETCH_ASSOC);

// User participation statistics
$user_stats = $conection->prepare("
    SELECT 
        COUNT(DISTINCT id_user) as active_bidders,
        COUNT(id) as total_bids,
        ROUND(COUNT(id) / COUNT(DISTINCT id_user), 1) as avg_bids_per_user
    FROM bids
    WHERE auction_id IN (SELECT id FROM auctions WHERE end_date > NOW())
");
$user_stats->execute();
$bidder_stats = $user_stats->fetch(PDO::FETCH_ASSOC);

// Container for displaying error or success messages
$message = '';

if (isset($_POST["bid"])) {
    if (!empty($_POST["price"]) && isset($_POST["auction_id"])) {
        // Convert values to numbers for correct comparison
        $new_price = floatval($_POST["price"]);
        $auction_id = intval($_POST["auction_id"]);

        // Retrieve selected auction data directly
        $get_auction = $conection->prepare("SELECT id, id_user, starting_price, category FROM auctions WHERE id = :auction_id AND end_date > NOW()");
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
                $message = "<p class='success'>Bid placed successfully! New price: $new_price MAD</p>";
                header("Refresh: 1; url=" . $_SERVER['PHP_SELF']);
            } else {
                $message = "<p class='error'>Bid amount must be higher than the current price ($current_price MAD)!</p>";
            }
        } else {
            $message = "<p class='error'>Active auction not found!</p>";
        }
    } else {
        $message = "<p class='error'>Please enter a price!</p>";
    }
}

// Condensed countdown function
function getCountdowns($produits) {
    $countdowns = [];
    $current_time = new DateTime();
    
    foreach ($produits as $produit) {
        $end_date = new DateTime($produit['end_date']);
        $interval = $current_time->diff($end_date);
        $countdowns[$produit['id']] = [
            'days' => $interval->days,
            'hours' => $interval->h,
            'minutes' => $interval->i,
            'seconds' => $interval->s
        ];
    }
    
    return $countdowns;
}

// Get all distinct categories with count
$categories_query = $conection->prepare("SELECT category, COUNT(*) as count 
                                      FROM auctions 
                                      WHERE end_date > NOW() 
                                      GROUP BY category 
                                      ORDER BY count DESC");
$categories_query->execute();
$categories = $categories_query->fetchAll(PDO::FETCH_ASSOC);

// Get trending search terms or popular keywords
$trending_query = $conection->prepare("SELECT auctions.category, COUNT(*) as search_count 
                                     FROM auctions 
                                     JOIN bids ON auctions.id = bids.auction_id 
                                     WHERE auctions.end_date > NOW() 
                                     GROUP BY auctions.category 
                                     ORDER BY search_count DESC 
                                     LIMIT 5");
$trending_query->execute();
$trending_terms = $trending_query->fetchAll(PDO::FETCH_ASSOC);

$countdowns_ending_soon = getCountdowns($produits_ending_soon);
$countdowns_most_bids = getCountdowns($produits_most_bids);
$countdowns_popular = getCountdowns($produits_popular);

// Modified function to display auction cards - now checks for empty result
function displayAuctionCards($produits, $countdowns, $conection) {
    if (empty($produits)) {
        echo '<div class="empty-message">No auctions available</div>';
        return false;
    }
    
    foreach ($produits as $produit): ?>
        <form method="POST" >
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
                            <img src="../<?php echo ($produit["image_user"]); ?>" alt="user image" class="user-profile-image"  >
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
                            // If we already have bid_count from the query, use it, otherwise count from database
                            if (isset($produit['bid_count'])) {
                                echo $produit['bid_count'] . " bids";
                            } else {
                                $bid_count = $conection->prepare("SELECT COUNT(*) FROM bids WHERE auction_id = :auction_id");
                                $bid_count->execute([":auction_id" => $produit["id"]]);
                                echo $bid_count->fetchColumn() . " bids";
                            }
                            ?>
                        </div>
                    </div>

                    <div class="countdown-section">
                        <div class="countdown-title">
                            <span class="icon-container"><i class="fas fa-hourglass-half icon-timer"></i></span>
                            Time Remaining
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
    <?php endforeach;
    
    return true;
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
    <title>Auctions Homepage</title>
        <link rel="icon" type="image/jpeg" href="../image/images.jpeg">

    <link rel="stylesheet" href="../css/view_Auction.css">
    <link rel="stylesheet" href="../css/home.css">
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

    <!-- Featured Banner Section -->
    <div class="banner-section">
        <h1>Welcome to Online Auctions</h1>
        <p>The perfect place to buy and sell products at competitive prices</p>
    </div>

    <!-- Dashboard Section -->
    <h2 class="section-title">Auction Trends Dashboard</h2>
    <div class="auctions-dashboard">
        <div class="dashboard-stats">
            <div>
                <i class="fas fa-fire"></i>
                <h3>Trending Now</h3>
                <div class="trending-keywords">
                    <?php foreach ($trending_terms as $term): ?>
                        <span class="trend-tag">
                            <?php echo $term['category']; ?> (<?php echo $term['search_count']; ?>)
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <i class="fas fa-chart-line"></i>
                <h3>Active Categories</h3>
                <div class="category-list">
                    <?php foreach ($categories as $category): ?>
                        <span class="category-tag">
                            <?php echo $category['category']; ?> (<?php echo $category['count']; ?>)
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- New Analytics Section -->
        <div class="dashboard-analytics">
            <div>
                <i class="fas fa-chart-pie"></i>
                <h3>Auction Analytics</h3>
                <p><i class="fas fa-percentage"></i> Auction Success Rate: <?php echo $success_rate['success_rate']; ?>%</p>
                <p><i class="fas fa-users"></i> Active Bidders: <?php echo $bidder_stats['active_bidders']; ?></p>
                <p><i class="fas fa-exchange-alt"></i> Avg. Bids Per User: <?php echo $bidder_stats['avg_bids_per_user']; ?></p>
            </div>

            <div>
                <i class="fas fa-arrow-up"></i>
                <h3>Average Bid Increase by Category</h3>
                <div class="category-increases">
                    <?php foreach ($bid_increases as $increase): ?>
                        <div class="category-increase">
                            <span class="category-name"><?php echo $increase['category']; ?></span>
                            <span class="increase-value"><?php echo round($increase['avg_increase'], 2); ?> MAD</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Most Bids Section -->
    <h2 class="section-title">Most Active Auctions</h2>
    <p class="section-description">Auctions with the highest number of bids from users</p>
    <div class="most-bids auction-container">
        <?php displayAuctionCards($produits_most_bids, $countdowns_most_bids, $conection); ?>
    </div>

    <!-- Auctions Ending Soon Section -->
    <h2 class="section-title">Ending Soon</h2>
    <p class="section-description">Don't miss these opportunities! Auctions ending within 24 hours</p>
    <div class="ending-soon auction-container">
        <?php displayAuctionCards($produits_ending_soon, $countdowns_ending_soon, $conection); ?>
    </div>

    <!-- Popular Auctions Section (Replacing Recent Auctions) -->
    <h2 class="section-title">Highest Value Auctions</h2>
    <p class="section-description">Auctions with the highest current bids</p>
    <div class="popular-auctions auction-container">
        <?php displayAuctionCards($produits_popular, $countdowns_popular, $conection); ?>
    </div>

    <!-- How It Works Section -->
    <div class="how-it-works">
        <h2 class="section-title">How Auctions Work</h2>
        <div class="steps">
            <div class="step">
                <i class="fas fa-search"></i>
                <h3>Browse Auctions</h3>
                <p>Search for products you're interested in and view their details</p>
            </div>
            <div class="step">
                <i class="fas fa-gavel"></i>
                <h3>Place Your Bid</h3>
                <p>Enter the amount you want to pay for the product</p>
            </div>
            <div class="step">
                <i class="fas fa-trophy"></i>
                <h3>Win the Auction</h3>
                <p>If your bid is the highest when the auction ends, you win!</p>
            </div>
        </div>
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

        // Initialize countdown for all products
        <?php 
        $all_products = array_merge($produits_ending_soon, $produits_most_bids, $produits_popular);
        $unique_products = array();
        
        // Filter for unique product IDs
        foreach ($all_products as $product) {
            $unique_products[$product['id']] = $product;
        }
        
        foreach ($unique_products as $produit): ?>
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


<?php include ("../include/footer.php")?>

</body>

</html>