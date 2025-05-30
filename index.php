<?php
session_start();
$loggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="pl">

<head>
   <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalendarz Rodzinny</title>
    <link rel="stylesheet" href="style.css" />
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales/pl.global.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f0f0f0;
        }

        #top-bar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 15px;
        }

        #login-logout-btn {
            background-color: #4dabf7;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        #calendar {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div id="top-bar">
        <?php if ($loggedIn): ?>
            <a href="logout.php" id="login-logout-btn">Wyloguj</a>
        <?php else: ?>
            <a href="login.php" id="login-logout-btn">Zaloguj się</a>
        <?php endif; ?>
    </div>



<h1>Kalendarz Rodzinny</h1>
    <div id="calendar"></div>

<div id="overlay" class="hidden"></div>

<div id="task-panel" class="hidden">
    <button id="close-panel" class="close-btn">✖</button>
    <h2 id="selected-date">Zadania na dzień</h2>
    <ul id="task-list"></ul>

    <h3>Dodaj wpis</h3>
    <form id="add-task-form">
        <label>
            Typ:
            <select id="entry-type">
                <option value="task">Zadanie</option>
            </select>
        </label>
        <input type="text" id="entry-text" placeholder="Opis pełny..." required><br>
        <input type="text" id="entry-short" placeholder="Skrót zadania (np. Zmywarka)" required><br>
        <input type="text" id="assigned-to" placeholder="Dla kogo (opcjonalnie)"><br>
        <button type="submit">Dodaj</button>
      

        
    </form>
</div>

  
   
    <script>
    const IS_LOGGED_IN = <?= $loggedIn ? 'true' : 'false' ?>;
</script>
<script src="script.js"></script>

</body>

</html>
