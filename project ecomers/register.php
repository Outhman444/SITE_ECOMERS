<?php
if (isset($_POST["register"])) {
    if (!empty($_POST["name"]) && !empty($_POST["email"]) && !empty($_POST["password"]) && !empty($_POST["telephone"]) && !empty($_POST["city"]) && !empty($_FILES["image"]["name"])) {

        $name = $_POST["name"];
        $email = $_POST["email"];
        $pass = $_POST["password"]; 
        $telephone = $_POST["telephone"];
        $city = $_POST["city"];

        $fn = $_FILES["image"]["name"];
        $dt = "stock_image_user/" . $fn;

        move_uploaded_file($_FILES["image"]["tmp_name"], $dt);
        $image = $dt;
        
        require_once("include/database.php");
        $valide_email= $conection->prepare("SELECT * FROM user WHERE email_user=? ");
        $valide_email->execute([$email]);
        
       
        $add_use_rsql = $conection->prepare("INSERT INTO user(name_user,email_user,password_user,tel_user,city,image_user) VALUES(?,?,?,?,?,?)");
        if($valide_email->rowCount()==1){?>
                  <div class="container">
        
                    email already existe!!
   
                        </div>
        <?php
        }else{
            
            $add_use_rsql->execute([$name,$email,$pass,$telephone,$city,$image]);
            header("Location:login.php");
            
        }
        
        
    
       
             

       
         
         

           
 

        
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Account - QuickBid</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>


    <div class="page-container register-page">
        <div class="main-container centered">
            <div class="container">
                <div class="decorative-dots"></div>
                <div class="corner-decoration"></div>
                <div class="logo">
                    <div class="logo-border"></div>
                    <div class="logo-circle">
                    <img src="./image/logo.jpg" alt="">
                    </div>
                    <div class="site-name">QuickBid</div>
                </div>
                
                <div id="register-form-content">
                    <h2 class="form-title">Create New Account</h2>
                    
                    <form method="post"  enctype="multipart/form-data" >
                        <div class="form-group">
                            <label for="reg-name">Name:</label>
                            <input type="text" id="reg-name" name="name" required>
                            <span class="focus-border"></span>
                        </div>
                        <div class="form-group">
                            <label for="reg-email">Email:</label>
                            <input type="email" id="reg-email" name="email" required>
                            <span class="focus-border"></span>
                        </div>
                        <div class="form-group">
                            <label for="reg-password">Password:</label>
                            <input type="password" id="reg-password" name="password" required>
                            <span class="focus-border"></span>
                        </div>
                        <div class="form-group">
                            <label for="reg-telephone">Phone Number:</label>
                            <input type="tel" id="reg-telephone" name="telephone" required>
                            <span class="focus-border"></span>
                        </div>
                        <div class="form-group">
                            <label for="reg-city">City:</label>
                            <input type="text" id="reg-city" name="city" required>
                            <span class="focus-border"></span>
                        </div>
                        <div class="form-group">
                            <label for="reg-image">image:</label>
                            <input type="file" id="reg-image" name="image" required>
                            <span class="focus-border"></span>
                        </div>
                        <button type="submit" class="btn btn-primary" name="register">Register</button>
                    </form>
                    
                    <div class="toggle-form">
                        <p>Already have an account? <a href="login.php">Login</a></p>
                    </div>
                    <div class="back-link">
                        <a href="index.php">Return to home page</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</body>
<?php include("include/footer.php")?>
</html>