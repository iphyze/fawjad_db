<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contact Form Message</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Montserrat', Arial, sans-serif;
      background-color: white;
      margin: 0;
      padding: 0;
    }
    * { box-sizing: border-box; }
    .main-container {
      max-width: 600px;
      padding: 50px 10px;
      margin: 0 auto;
    }
    .container {
      background: #fafafa;
      border-radius: 20px;
      padding: 20px;
      text-align: center;
    }
    .contact-img{
        margin-bottom: 20px;
        width: 150px;
        height: auto;
        object-fit: contain;
    }
    .contact-container {
      background: #ffffff;
      padding: 20px 15px;
      margin-bottom: 20px;
      border-radius: 10px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    .header {
      color: #bd8e0a;
      font-size: 20px;
      font-weight: 600;
    }
    .title {
      font-size: 14px;
      font-weight: 600;
      color: #bd8e0a;
      text-align: left;
      margin-bottom: 5px;
    }
    .title-text {
      font-size: 14px;
      color: #333333;
      text-align: left;
    }
    .footer {
      font-size: 12px;
      color: #aaaaaa;
      margin-top: 30px;
    }
  </style>
</head>

<body>
  <div class="main-container">
    <div class="container">

      <img src="http://api.fawjadglobal.com/servers/fawjad_db/uploads/logo.png" alt="Fawjad Logo" class="contact-img">

      <div class="contact-container">
        <h2 class="header">Contact Form Message</h2>

        <h3 class="title">Name</h3>
        <p class="title-text"><?= htmlspecialchars($fullName) ?></p>

        <h3 class="title">Email</h3>
        <p class="title-text"><?= htmlspecialchars($email) ?></p>

        <h3 class="title">Phone</h3>
        <p class="title-text"><?= htmlspecialchars($phone) ?></p>
      </div>

      <div class="contact-container">
        <h3 class="title">Message</h3>
        <p class="title-text"><?= nl2br(htmlspecialchars($message)) ?></p>
      </div>

      <div class="footer">
        <p>Fawjad Team</p>
      </div>

    </div>
  </div>
</body>
</html>
