<?php
if (isset($_COOKIE["user_information"])) {
    header("Location: home/home.php");
    exit();
}






if (isset($_POST["login"])) {
    if (!empty($_POST["email"]) && !empty($_POST["password"])) {
        $email = $_POST["email"];
        $pass = $_POST["password"];
        require_once("include/database.php");

        $serch_user = $conection->prepare("SELECT * FROM user");
        $serch_user->execute();
        $infotableu = $serch_user->fetchAll(PDO::FETCH_ASSOC);
        $valide = FALSE;

        foreach ($infotableu as $data_info) {
            if ($data_info["email_user"] == $email && $data_info["password_user"] == $pass) {
                $valide = TRUE;
                
                setcookie("user_information", json_encode($data_info), time() + 15552000, "/");
            }
        }

        if ($valide == TRUE) {
            header("Location:home/home.php");
            exit();
        } else {
            ?>
            <div class="container">
                Email or password incorrect!
            </div>
            <?php
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
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
    <title>Login - QuickBid</title>
    <link rel="stylesheet" href="./css/style.css">
        <link rel="icon" type="image/jpeg" href="image/images.jpeg">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="page-container login-page">
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
               
                
                <div id="login-form-content">
                    <h2 class="form-title">Login</h2>
                    
                    <form action="login.php" method="post">
                        <div class="form-group">
                            <label for="login-email">Email:</label>
                            <input type="email" id="login-email" name="email" required>
                            <span class="focus-border"></span>
                        </div>
                        <div class="form-group">
                            <label for="login-password">Password:</label>
                            <input type="password" id="login-password" name="password" required>
                            <span class="focus-border"></span>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary">Login</button>
                    </form>
                    <div class="toggle-form">
                        <p>Don't have an account? <a href="register.php">Create a new account</a></p>
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
