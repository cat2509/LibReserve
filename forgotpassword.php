<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: Arial, sans-serif; 
        }

        body { 
            background-color: #f9f9f9; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            flex-direction: column; 
        }

        .banner { 
            position: absolute; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 7vh; 
            background-color: #b91a1a; 
        }

        .container { 
            width: 400px; 
            background: white; 
            padding: 25px; 
            border-radius: 10px; 
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); 
            text-align: center; 
        }

        h1 { 
            color: #333; 
            margin-bottom: 15px; 
        }

        p { 
            font-size: 14px; 
            color: gray; 
            margin-bottom: 20px; 
        }

        .input { 
            margin-bottom: 15px; 
            text-align: left; 
        }

        .input label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: bold; 
        }

        .input input { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
            font-size: 16px; 
        }

        button { 
            width: 100%; 
            padding: 10px; 
            background-color: #b91a1a; 
            color: white; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 16px; 
            margin-top: 10px; 
        }

        button:hover { 
            background-color: #444444; 
        }
    </style>
</head>
<body>

    <div class="banner"></div>

    <div class="container">
        <h1>Forgot Password</h1>
        <p>Enter your email address below to reset your password.</p>

        <form method="post" action="send-password-reset.php">
            <div class="input">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <button type="submit">Send</button>
        </form>
    </div>

</body>
</html>
