<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Policies</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="icon" type="image/jpeg" href="../image/images.jpeg">

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        /* General Styles */
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.6;
            transition: all 0.3s ease-in-out;
            
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 1400px;
            margin: 20px auto;
        }
        .container {
            max-width: 900px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1s ease-in-out;
        }
        h1, h2 {
            color: #007bff;
            text-align: center;
            margin-bottom: 15px;
        }
        .section>p {
            margin-bottom: 15px;
        }

        /* Sections */
        .section {
            margin-bottom: 30px;
            padding: 20px;
            border-left: 5px solid #007bff;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: slideIn 0.5s ease-in-out;
        }
        .section:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        /* Lists */
        ul {
            padding: 0;
            list-style-type: none;
        }
        a>ul>li::before {
            content: "✔";
            color: #007bff;
            font-weight: bold;
            display: inline-block; 
            width: 1.2em;
            margin-right: 8px;
        }

        /* Buttons */
        .button {
            display: block;
            width: max-content;
            margin: 20px auto;
            padding: 12px 25px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .button:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 15px;
            background: #007bff;
            color: white;
            margin-top: 30px;
            border-radius: 0 0 10px 10px;
            font-size: 14px;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                width: 90%;
                padding: 15px;
            }
            .section {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
<?php include("../include/navbar.php")?>
    <div class="container">
        <h1>Terms and Policies</h1>
        <div class="section">
            <h2>Terms and Conditions</h2>
            <p>Welcome to our website. By using this site, you agree to the following terms:</p>
            <ul>
                <li>The website must be used for legal purposes only.</li>
                <li>Content may not be republished or redistributed without permission.</li>
                <li>The website is not responsible for any losses resulting from usage.</li>
                <li>The site reserves the right to modify these terms at any time without prior notice.</li>
            </ul>
        </div>
        <div class="section">
            <h2>Privacy Policy</h2>
            <p>We respect your privacy and are committed to protecting your data. When using our website, we may collect certain information such as:</p>
            <ul>
                <li>Personal data provided during registration.</li>
                <li>Cookies to improve user experience.</li>
                <li>We will not share your data with any third party without your permission.</li>
                <li>You can request to delete your data at any time by contacting us.</li>
            </ul>
        </div>
        <div class="section">
            <h2>Cookie Policy</h2>
            <p>Our website uses cookies to provide a better user experience. You can adjust your browser settings to disable them if you wish.</p>
        </div>
       
        <a href="https://www.linkedin.com/in/otman-yes" target="_blank" class="button">Contact Us</a>
    </div>
    <?php include("../include/footer.php")?>
</body>
</html>
