
<?php
ini_set("display_errors", 0);

session_start();

$id_userrrr = $_SESSION['user_information']["id_user"];

if (isset($_POST["submit_produit"])) {
   
    $productName = trim($_POST['productName']);
    $productCategory = trim($_POST['productCategory']);
    $productDescription = trim($_POST['productDescription']);
    $startingPrice = floatval($_POST['startingPrice']);
    $auctionDuration = intval($_POST['auctionDuration']);

    
   
    if (empty($productName) || empty($productCategory) || empty($productDescription) || $startingPrice <= 0 || $auctionDuration <= 0) {
        die("Invalid input! Please fill all required fields correctly.");
    }

    
    $uploadDir = "../stock_image_product/";
    $uploadedFiles = [];

    if (!empty($_FILES['productImages']['name'][0])) {
        foreach ($_FILES['productImages']['tmp_name'] as $key => $tmpName) {
            $fileName = basename($_FILES['productImages']['name'][$key]);
            $targetFile = $uploadDir.$fileName; 
            
    
            $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($fileType, $allowedTypes)) {
                if (move_uploaded_file($tmpName, $targetFile)) {
                    $uploadedFiles[] = $targetFile;
                }
            }
        }
    }
 
   
    $imagesPath = implode(",", $uploadedFiles);
    
    
    require_once("../include/database.php");
    $stmt=$conection->prepare("INSERT INTO auctions (product_name, category, description, starting_price, duration, images,id_user) VALUES (?, ?, ?, ?, ?, ?,?)");
    $stmt->execute([$productName, $productCategory, $productDescription, $startingPrice, $auctionDuration, $imagesPath,$id_userrrr]);
   
    if (isset($stmt)) {
        echo "Auction item added successfully!";
    } 
}

?>


<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add a New Auction Item</title>
    <link rel="stylesheet" href="style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/add_Auction.css">
</head>
<body>
    
<?php
    include("../include/navbar.php")?>
    <div class="container">
        <div class="header_add_auction">
            <h1><i class="fas fa-gavel"></i> Add a New Auction Item</h1>
        </div>
        
        <form id="addItemForm" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="productName" class="required">Product Name</label>
                <i class="fas fa-tag form-icon"></i>
                <input type="text" id="productName" name="productName" placeholder="Enter product name" >
            </div>
            
            <div class="form-group">
                <label for="productCategory" class="required">Category</label>
                <i class="fas fa-list-alt form-icon"></i>
                <select id="productCategory" name="productCategory" >
                    <option value="" disabled selected>Select a category</option>
                    <option value="electronics">Electronics</option>
                    <option value="clothing">Clothing & Accessories</option>
                    <option value="home">Home Furniture</option>
                    <option value="sports">Sports</option>
                    <option value="books">Books</option>
                    <option value="collectibles">Collectibles</option>
                    <option value="art">Art & Antiques</option>
                    <option value="vehicles">Vehicles</option>
                    <option value="jewelry">Jewelry</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="productDescription" class="required">Detailed Description</label>
                <i class="fas fa-align-left form-icon"></i>
                <textarea id="productDescription" name="productDescription" placeholder="Enter a detailed description including condition, specifications, year of manufacture, etc..." ></textarea>
            </div>
            
            <div class="form-group">
                <label for="startingPrice" class="required">Starting Price (USD)</label>
                <i class="fas fa-money-bill-wave form-icon"></i>
                <input type="number" id="startingPrice" name="startingPrice" min="1" step="1" placeholder="Enter starting price" >
                <p class="note">The auction will start from this price</p>
            </div>
            
            <div class="form-group">
                <label for="auctionDuration" class="required">Auction Duration</label>
                <i class="fas fa-clock form-icon"></i>
                <div class="auction-duration">
                    <select id="auctionDuration" name="auctionDuration" >
                        <option value="" disabled selected>Select auction duration</option>
                        <option value="1">1 Day</option>
                        <option value="3">3 Days</option>
                        <option value="5">5 Days</option>
                        <option value="7">1 Week</option>
                        <option value="14">2 Weeks</option>
                        <option value="30">1 Month</option>
                    </select>
                </div>
                <p class="note">The auction will automatically end after the selected duration</p>
            </div>
            
            <div class="form-group">
                <label for="productImages" class="required">Product Images</label>
                <div class="image-upload" id="imageUploadArea">
                    <input type="file" id="productImages" name="productImages[]" accept="image/*" multiple hidden >
                    <i class="fas fa-cloud-upload-alt upload-icon"></i>
                    <p>Click to add images or drag and drop images here</p>
                    <p class="note">You can upload up to 5 images focusing on showing all product details and condition</p>
                </div>
                <div class="preview-images" id="previewImages"></div>
            </div>
            
            <button type="submit" class="submit-btn" name="submit_produit">
                <i class="fas fa-hammer"></i>
                Submit Item for Auction
            </button>
        </form>
    </div>

    <div class="loading" id="loading">
        <div class="spinner"></div>
    </div>

    <div class="success-msg" id="successMsg">
        <i class="fas fa-check-circle success-icon"></i>
        <h3>Item Submitted Successfully!</h3>
        <p>Your item will be reviewed and listed in the auction soon</p>
    </div>
  


<script>
    // Handling image uploads and previews
    const imageUploadArea = document.getElementById('imageUploadArea');
    const productImages = document.getElementById('productImages');
    const previewImages = document.getElementById('previewImages');
    const loading = document.getElementById('loading');
    const successMsg = document.getElementById('successMsg');

    imageUploadArea.addEventListener('click', () => {
        productImages.click();
    });

    imageUploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        imageUploadArea.style.borderColor = '#1a73e8';
        imageUploadArea.style.backgroundColor = 'rgba(26, 115, 232, 0.1)';
    });

    imageUploadArea.addEventListener('dragleave', () => {
        imageUploadArea.style.borderColor = '#ccc';
        imageUploadArea.style.backgroundColor = '#e8f0fe';
    });

    imageUploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        imageUploadArea.style.borderColor = '#ccc';
        imageUploadArea.style.backgroundColor = '#e8f0fe';

        if (e.dataTransfer.files.length > 0) {
            productImages.files = e.dataTransfer.files;
            showImagePreviews();
        }
    });

    productImages.addEventListener('change', showImagePreviews);

    function showImagePreviews() {
        previewImages.innerHTML = '';

        const files = productImages.files;
        const maxImages = 5;

        for (let i = 0; i < Math.min(files.length, maxImages); i++) {
            const file = files[i];

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();

                reader.onload = (e) => {
                    const previewContainer = document.createElement('div');
                    previewContainer.className = 'preview-container';

                    const img = document.createElement('img');
                    img.src = e.target.result;

                    const removeBtn = document.createElement('div');
                    removeBtn.className = 'remove-image';
                    removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                    removeBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        previewContainer.remove();
                    });

                    previewContainer.appendChild(img);
                    previewContainer.appendChild(removeBtn);
                    previewImages.appendChild(previewContainer);
                };

                reader.readAsDataURL(file);
            }
        }
    }


</script>
</body>
<?php
    include("../include/footer.php")?>
</html>

    
</body>