<?php
include("../include/database.php");

$pr_images=$conection->prepare("SELECT images FROM auctions ");
$pr_images->execute();
$tex_images=$pr_images->fetch(PDO::FETCH_COLUMN);
$t_imgs=explode(",",$tex_images);

// hna select info dyal produit mn riR image
foreach();
$selct_produit=$conection->prepare("SELECT id FROM auctions ");
$selct_produit->execute();

$produits_assoc=$selct_produit->fetchAll(PDO::FETCH_ASSOC);
print_r($produits_assoc)







?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auction Product Card</title>
    <link rel="stylesheet" href="../css/view_Auction.css">
   
</head>

<body>
<?php
include("../include/navbar.php")?>

<?php
foreach($produits_assoc as $produit){?>
 <form method="POST">
        
        
        <div class="auction-card">
            <div class="image-slider">
                <div class="category">Electronics</div>
                <div class="slider-wrapper">
                    <?php
                    foreach($t_imgs as $image){?>
                        <img src="<?php echo $image?>" alt="">
                    <?php
                    }
                    ?>
                 
                </div>
                <div class="slider-nav">
                    <div class="slider-dot active"></div>
                    <div class="slider-dot"></div>
                    <div class="slider-dot"></div>
                </div>
                <div class="slider-arrows">
                    <div class="arrow prev">❮</div>
                    <div class="arrow next">❯</div>
                </div>
            </div>
            
            <div class="product-info">
                <h2 class="product-title"><?php echo $selct_produit["product_name"]?></h2>
                <p class="product-description">A sophisticated phone with a professional camera, powerful processor, and a distinctive AMOLED screen, 256GB storage, and 12GB RAM, in excellent condition.</p>
                
                <div class="price-section">
                    <div class="current-price">5,200 SAR</div>
                    <div class="bid-count">23 Bids</div>
                </div>
                
                <div class="countdown-section">
                    <div class="countdown-title">Remaining time for the auction</div>
                    <div class="countdown">
                        <div class="countdown-item">
                            <div class="countdown-value" id="days">02</div>
                            <div class="countdown-label">Days</div>
                        </div>
                        <div class="countdown-item">
                            <div class="countdown-value" id="hours">18</div>
                            <div class="countdown-label">Hours</div>
                        </div>
                        <div class="countdown-item">
                            <div class="countdown-value" id="minutes">45</div>
                            <div class="countdown-label">Minutes</div>
                        </div>
                        <div class="countdown-item">
                            <div class="countdown-value" id="seconds">30</div>
                            <div class="countdown-label">Seconds</div>
                        </div>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <!-- Bid input field -->
                    <input type="number" class="bid-input" placeholder="Enter bid amount" min="0" />
                    <button class="bid-button" type="submit">Bid Now</button>
                    <button class="share-button" >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M13.5 1a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM11 2.5a2.5 2.5 0 1 1 .603 1.628l-6.718 3.12a2.499 2.499 0 0 1 0 1.504l6.718 3.12a2.5 2.5 0 1 1-.488.876l-6.718-3.12a2.5 2.5 0 1 1 0-3.256l6.718-3.12A2.5 2.5 0 0 1 11 2.5zm-8.5 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm11 5.5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z"/>
    
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        
        </form>
        
    <script>
        // Image Slider Functionality
        const sliderWrapper = document.querySelector('.slider-wrapper');
        const dots = document.querySelectorAll('.slider-dot');
        const prevBtn = document.querySelector('.prev');
        const nextBtn = document.querySelector('.next');
        let currentIndex = 0;
        const slideCount = document.querySelectorAll('.slider-wrapper img').length;
        
        function updateSlider() {
            sliderWrapper.style.transform = `translateX(${currentIndex * -100}%)`;
            
            // Update dots
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentIndex);
            });
        }
        
        // Click events for dots
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentIndex = index;
                updateSlider();
            });
        });
        
        // Arrow navigation
        prevBtn.addEventListener('click', () => {
            currentIndex = (currentIndex - 1 + slideCount) % slideCount;
            updateSlider();
        });
        
        nextBtn.addEventListener('click', () => {
            currentIndex = (currentIndex + 1) % slideCount;
            updateSlider();
        });
        
        
        // Share button functionality
        const shareButton = document.querySelector('.share-button');
        shareButton.addEventListener('click', () => {
            alert('Product link copied, you can share it now!');
        });
    </script> 

</body>

    
</html>
<?php
}?>


    
   