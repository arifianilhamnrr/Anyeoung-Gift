<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Anyeoung Gift</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: '#D4AF37',
                        darkbg: '#0F0F0F'
                    }
                }
            }
        }
    </script>

    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;500&display=swap"
        rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .font-title {
            font-family: 'Playfair Display', serif;
        }
    </style>
</head>

<body class="bg-darkbg text-white flex flex-col min-h-screen">


    <?php include 'components/navbar.php'; ?>