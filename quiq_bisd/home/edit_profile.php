<?php
ini_set("display_errors", 0);

if (isset($_POST["cancel"])) {
    header("Location: profile.php");
    exit();
}

if (isset($_POST["save_info"])) {
    if (!empty($_POST["name"]) || !empty($_FILES["image"]["name"]) || !empty($_POST["city"]) || !empty($_POST["tel"]) || !empty($_POST["email"]) || (!empty($_POST["old_password"]) && !empty($_POST["new_password"]) && !empty($_POST["confirm_new_password"]))) {
        
        $name = $_POST["name"];
        $city = $_POST["city"];
        $tel = $_POST["tel"];
        $email = $_POST["email"];
        $old_password = $_POST["old_password"];
        $new_password = $_POST["new_password"];
        $confirm_new_password = $_POST["confirm_new_password"];

        $fn = $_FILES["image"]["name"];
        $dt = "stock_image_user/" . $fn;
        move_uploaded_file($_FILES["image"]["tmp_name"], $dt);
        $image = $dt;

        if (!isset($_COOKIE["user_information"])) {
            header("Location: ../index.php");
            exit();
        }

        $user_info = json_decode($_COOKIE["user_information"], true);
        $id_user = $user_info["id_user"];
        require_once("../include/database.php");

        if (empty($_FILES["image"]["name"])) {
            $image = $user_info["image_user"];
        }

        $valide_password = $conection->prepare("SELECT password_user FROM user WHERE password_user=? and id_user=?");
        $valide_password->execute([$old_password, $id_user]);

        if ($new_password == $confirm_new_password && $valide_password->rowCount() === 1) {
            $update_password = $conection->prepare("UPDATE user SET password_user=? WHERE id_user=?");
            $update_password->execute([$new_password, $id_user]);
            $password_accept = TRUE;
        } else {
            $password_accept = FALSE;
        }

        $valide_email = $conection->prepare("SELECT * FROM user WHERE email_user=?");
        $valide_email->execute([$email]);
        $value_email = $valide_email->fetch(PDO::FETCH_ASSOC);

        if ($valide_email->rowCount() == 0 || $value_email["email_user"] == $user_info["email_user"]) {
            $update_info = $conection->prepare("UPDATE user SET name_user=?, image_user=?, city=?, tel_user=?, email_user=? WHERE id_user=?");
            $update_info->execute([$name, $image, $city, $tel, $email, $id_user]);

            $user_info["name_user"] = $name;
            $user_info["image_user"] = $image;
            $user_info["city"] = $city;
            $user_info["email_user"] = $email;
            setcookie("user_information", json_encode($user_info), time() + 15552000, "/");

            header("Location: profile.php?success-message=1");
        } elseif ($password_accept == false) { ?>
            <div class="messgae_existe_email">
                ❌ email already exists!!
            </div>
        <?php }
    }
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
    <title>Edit Profile</title>
        <link rel="icon" type="image/jpeg" href="../image/images.jpeg">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="../css/edit_profile.css">
</head>
<body>
    <?php include("../include/navbar.php"); ?>
    <div class="container">
        <div class="edit-header animate__animated animate__fadeIn">
            <i class="fas fa-user-edit"></i>
            <h1>Edit Profile</h1>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <h2 class="section-title animate__animated animate__fadeIn">
                <i class="fas fa-id-card"></i> Personal Information
            </h2>
            <div class="form-group animate__animated animate__fadeIn">
                <div class="image-preview-container">
                    <img src="../<?php echo $data_info["image_user"] ?>" alt="Profile Picture" class="image-preview">
                    <div class="image-upload">
                        <label for="profile-image">
                            <i class="fas fa-camera"></i> Change Profile Picture:
                        </label>
                        <input type="file" id="profile-image" accept="image/*" name="image">
                    </div>
                </div>
            </div>
            <div class="form-group animate__animated animate__fadeIn">
                <label for="name">Name:</label>
                <div class="input-with-icon">
                    <input type="text" id="name" name="name" value='<?php echo $data_info["name_user"] ?>'>
                    <i class="fas fa-user"></i>
                </div>
            </div>
            <div class="form-group animate__animated animate__fadeIn">
                <label for="city">City:</label>
                <div class="input-with-icon">
                    <input type="text" id="city" name="city" value='<?php echo $data_info["city"] ?>'>
                    <i class="fas fa-map-marker-alt"></i>
                </div>
            </div>
            <div class="form-group animate__animated animate__fadeIn">
                <label for="email">Email Address:</label>
                <div class="input-with-icon">
                    <input type="email" id="email" name="email" value='<?php echo $data_info["email_user"] ?>'>
                    <i class="fas fa-envelope"></i>
                </div>
            </div>
            <div class="form-group animate__animated animate__fadeIn">
                <label for="telephone">Phone Number:</label>
                <div class="input-with-icon">
                    <input type="tel" id="telephone" name="tel" value='<?php echo $data_info["tel_user"] ?>'>
                    <i class="fas fa-phone"></i>
                </div>
            </div>
            <h2 class="section-title animate__animated animate__fadeIn">
                <i class="fas fa-lock"></i> Change Password
            </h2>
            <div class="form-group animate__animated animate__fadeIn">
                <label for="current-password">Old Password:</label>
                <div class="input-with-icon">
                    <input type="password" id="current-password" name="old_password">
                    <i class="fas fa-key"></i>
                </div>
            </div>
            <div class="form-group animate__animated animate__fadeIn">
                <label for="new-password">New Password:</label>
                <div class="input-with-icon">
                    <input type="password" id="new-password" name="new_password">
                    <i class="fas fa-lock"></i>
                </div>
            </div>
            <div class="form-group animate__animated animate__fadeIn">
                <label for="new-password">confirm New Password:</label>
                <div class="input-with-icon">
                    <input type="password" id="new-password" name="confirm_new_password">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn cancel-button" name="cancel";>
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn save-button" name="save_info">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</body>
<?php include("../include/footer.php"); ?>
</html>
