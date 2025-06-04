<?php
if (isset($_COOKIE["user_information"])) {
    header("Location: home/home.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<link rel="sitemap" type="application/xml" title="Sitemap" href="sitemap.xml">

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
    <title>QuickBidsX - Best Online Auction Site | Buy & Sell Electronics, Cars, Watches</title>
    <meta name="description" content="Join QuickBidsX, the best online auction platform to buy and sell electronics, cars, watches, real estate, antiques, and more at the best prices! Secure transactions & real-time bidding.">
    <meta name="keywords" content="online auctions, buy and sell, best auction site, live bidding, secure shopping, cheap electronics, luxury watches, real estate deals, rare antiques, used cars for sale">
    <link rel="stylesheet" href="./css/style.css">
<link rel="icon" type="image/jpeg" href="image/images.jpeg">        

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="page-container">
        <div class="site-description">
            <h1>Welcome to QuickBidsX - The Best Online Auction Platform</h1>
            <p>Looking to buy or sell electronics, cars, watches, or antiques? QuickBidsX offers a secure and transparent online auction experience.</p>
            <p>Sign up now and start bidding on exclusive products at unbeatable prices.</p>
            
            <div class="features">
                <div class="feature">
                    <div class="feature-icon">🛒</div>
                    <h3>Secure Shopping</h3>
                    <p>Guaranteed quality products and buyer protection</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">⏱️</div>
                    <h3>Live Auctions</h3>
                    <p>Real-time bidding on exclusive items</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">💰</div>
                    <h3>Save Money</h3>
                    <p>Find the best deals on high-value items</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">🔒</div>
                    <h3>100% Safe Transactions</h3>
                    <p>Secure payments and verified sellers</p>
                </div>
            </div>
        </div>
        
        <div class="main-container">
            <div class="decoration">
                <div class="decoration-img">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path fill="#1e293b" d="M128 352V128h256v224H128z"/>
                        <path fill="#2563eb" d="M112 96h288v256H112V96zm16 16v224h256V112H128z"/>
                        <path fill="#10b981" d="M80 64h352v320H80V64zm16 16v288h320V80H96z"/>
                        <path fill="#f97316" d="M376 16L256 48l120 32z"/>
                        <path fill="#8b5cf6" d="M249 183.5l-17 68 68-17z"/>
                        <path fill="#f59e0b" d="M256 200l51-51 51 51-51 51z"/>
                        <path fill="#fbbf24" d="M156 150h100v100H156z"/>
                        <path fill="#f8fafc" d="M224 384h64v64h-64z"/>
                        <path fill="#2563eb" d="M432 432H80l176-64z"/>
                    </svg>
                </div>
                <div class="auction-items">
                    <div class="auction-item" style="--i: 0">Luxury Watches</div>
                    <div class="auction-item" style="--i: 1">Latest Electronics</div>
                    <div class="auction-item" style="--i: 2">Real Estate Deals</div>
                    <div class="auction-item" style="--i: 3">Exclusive Jewelry</div>
                    <div class="auction-item" style="--i: 4">Rare Antiques</div>
                    <div class="auction-item" style="--i: 5">Used Cars for Sale</div>
                </div>
            </div>
            
            <div class="container">
                <div class="decorative-dots"></div>
                <div class="corner-decoration"></div>
                <div class="logo">
                    <div class="logo-border"></div>
                    <div class="logo-circle">
                    <img src="./image/logo.jpg" alt="QuickBidsX Online Auctions">
                    </div>
                    <div class="site-name">QuickBidsX</div>
                </div>
                
                <div class="welcome-section">
                    <h2 class="welcome-title">Join QuickBidsX Today</h2>
                    <p>Start bidding on high-value items with the best online auction platform</p>
                    <div class="auth-buttons">
                        <a href="login.php" class="btn btn-primary">Login</a>
                        <a href="register.php" class="btn btn-secondary">Create Account</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
 
</body>
<?php include("include/footer.php")?>
</html>