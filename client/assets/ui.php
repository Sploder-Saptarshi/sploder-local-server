<?php

session_start();
// Disable errors
// error_reporting(0);
// ini_set('display_errors', 0);
// ini_set('display_startup_errors', 0);

$configFile = __DIR__ . '/config.json';
$config = [
    'SERVER_HOST' => 'localhost',
    'SERVER_PORT' => 8080,
    'SERVER_PROTOCOL' => 'http',
];

function applyConfig($config, $configFile)
{
    // Check version compatibility
    // Request the server version
    echo "Checking client version compatibility with server...\n";
    $response = file_get_contents($config['SERVER_PROTOCOL'] . "://" . $config['SERVER_HOST'] . ":" . $config['SERVER_PORT'] . "/version.php");
    $serverVersion = trim($response);
    $clientVersion = trim(file_get_contents(__DIR__ . '/version.txt'));
    if($serverVersion !== $clientVersion) {
        echo "Client version is not compatible with server version.\n";
        echo "The client version must match the server version to continue.\n";
        echo "Server version: $serverVersion\n";
        echo "Client version: $clientVersion\n\n";
        // Options to change configuration or exit
        echo "1. Change configuration\n";
        echo "2. Exit\n";
        echo "Enter your choice: ";
        $choice = trim(fgets(STDIN));
        if ($choice == '1') {
            changeConfiguration($configFile);
        } else {
            echo "Exiting...\n";
            exit;
        }
    }
    if (defined('SERVER_HOST') || defined('SERVER_PORT') || defined('SERVER_PROTOCOL')) {
        echo "Configuration applied. The application will have to be restarted. Press any key to exit.";
        system('pause >nul');
        exit;
    }
    // Define or redefine constants
    if (!defined('SERVER_HOST') || SERVER_HOST !== $config['SERVER_HOST']) {
        define('SERVER_HOST', $config['SERVER_HOST']);
    }
    if (!defined('SERVER_PORT') || SERVER_PORT !== $config['SERVER_PORT']) {
        define('SERVER_PORT', $config['SERVER_PORT']);
    }
    if (!defined('SERVER_PROTOCOL') || SERVER_PROTOCOL !== $config['SERVER_PROTOCOL']) {
        define('SERVER_PROTOCOL', $config['SERVER_PROTOCOL']);
    }
}

function changeConfiguration($configFile, $config = null)
{
    system('clear');
    if ($config === null) {
        $config = [
            'SERVER_HOST' => 'localhost',
            'SERVER_PORT' => 8080,
            'SERVER_PROTOCOL' => 'http',
        ];
    }
    echo "Enter server host: ";
    $config['SERVER_HOST'] = trim(fgets(STDIN));

    echo "Enter server port: ";
    $config['SERVER_PORT'] = trim(fgets(STDIN));

    echo "Enter server protocol (http/https): ";
    $config['SERVER_PROTOCOL'] = trim(fgets(STDIN));

    $configContent = json_encode($config, JSON_PRETTY_PRINT);
    file_put_contents($configFile, $configContent);
    echo "Configuration saved.\n";
    applyConfig($config, $configFile);
}

function launchCreator($id){
    // Launch it without blocking the PHP script
    if($id == 5){
        system('start flash\flash.exe "' . SERVER_PROTOCOL .'://' . SERVER_HOST . ':' . SERVER_PORT . '/swf/creator' . $id . '.php?PHPSESSID=' . $_SESSION['sessionId'] . '&username=' . $_SESSION['username'] . '&userid='. $_SESSION['userid'].'&other="');
    } else {
    system('start flash\flash.exe "' . SERVER_PROTOCOL . '://' . SERVER_HOST . ':' . SERVER_PORT . '/swf/creator' . $id . '.swf?PHPSESSID=' . $_SESSION['sessionId'] . '&username=' . $_SESSION['username'] . '&userid='. $_SESSION['userid'].'&other="');
    }
}

function getFullgameSwf($swf){
    if($swf == 1){
        return "game1.swf";
    } else if($swf == 2){
        return "fullgame2_b17.swf";
    } else if($swf == 3){
        return "fullgame3.swf";
    } else if($swf == 5){
        return "fullgame5_b26s.swf";
    } else if($swf == 7){
        return "game7.swf";
    }
}

function launchGame($id,$swf)
{
    $fullgame = getFullgameSwf($swf);
    // Launch it without blocking the PHP script
    system('start flash\flash.exe ' . SERVER_PROTOCOL . '://' . SERVER_HOST . ':' . SERVER_PORT . '/swf/' . $fullgame . '?s=' . $id);
}

function gamesMenu()
{
    $invalid = true;
    $limit = 10;
    $offset = 0;
    $page = 1;

    do {
        system('clear');
        // Request public games JSON data from the server on games.php
        $response = file_get_contents(SERVER_PROTOCOL . "://" . SERVER_HOST . ":" . SERVER_PORT . "/games.php?limit=$limit&offset=$offset");
        $games = json_decode($response, true);

        // Check if no games are returned (last page)
        if (empty($games)) {
            if ($offset == 0) {
                echo "No games available.\n";
                echo "Press any key to continue...";
                system('pause >nul');
                $invalid = false;
                break;
            } else {
                echo "No more games to display.\n";
                echo "Press any key to continue...";
                system('pause >nul');
                $offset -= $limit;
                $page--;
                continue;
            }
        }

        echo "Games (Page $page):\n";
        echo str_pad("ID", 10) . str_pad("Title", 30) . str_pad("Author", 20) . "Date\n";
        echo str_repeat("-", 70) . "\n";
        foreach ($games as $game) {
            echo str_pad($game['g_id'], 10) . str_pad($game['title'], 30) . str_pad($game['author'], 20) . $game['date'] . "\n";
        }
        echo "Enter game ID to play, 'next' for next page, 'prev' for previous page, 'limit' to change number of games per page, or 'back' to go back: ";
        $choice = trim(fgets(STDIN));

        if ($choice == 'back') {
            echo "Going back...\n";
            $invalid = false;
        } elseif ($choice == 'next') {
            $offset += $limit;
            $page++;
        } elseif ($choice == 'prev') {
            if ($offset > 0) {
                $offset -= $limit;
                $page--;
            }
        } elseif ($choice == 'limit') {
            echo "Enter number of games per page (max 20): ";
            $newLimit = (int)trim(fgets(STDIN));
            if ($newLimit > 0 && $newLimit <= 20) {
                $limit = $newLimit;
                $offset = 0;
                $page = 1;
            } else {
                echo "Invalid limit. Please enter a number between 1 and 20.\n";
                echo "Press any key to continue...";
                system('pause >nul');
            }
        } else {
            $found = false;
            foreach ($games as $game) {
                if ($game['g_id'] == $choice) {
                    if ($game['g_swf'] == 1) {
                        echo "Playing game...\n";
                        launchGame($game['user_id'] . '_' . $game['g_id'], $game['g_swf']);
                    } else {
                        // Options to play the game or display its thumbnail
                        echo "1. Play game\n";
                        echo "2. Display thumbnail\n";
                        echo "Enter your choice: ";
                        $playChoice = trim(fgets(STDIN));

                        if ($playChoice == '1') {
                            echo "Playing game...\n";
                            launchGame($game['user_id'] . '_' . $game['g_id'], $game['g_swf']);
                        } elseif ($playChoice == '2') {
                            echo "Displaying thumbnail...\n";
                            system('start ' . SERVER_PROTOCOL . '://' . SERVER_HOST . ':' . SERVER_PORT . '/users/user'.$game['user_id'].'/images/proj'.$game['g_id'].'/image'.'.png');
                        } else {
                            echo "Invalid choice. Please try again.\n";
                            echo "Press any key to continue...";
                            system('pause >nul');
                        }
                    }
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                echo "Invalid game ID. Please try again.\n";
                echo "Press any key to continue...";
                system('pause >nul');
            }
        }
    } while ($invalid);
}

function makeGameMenu()
{
    $invalid = true;
    do {
        system('clear');
        echo "1. Shooter\n";
        echo "2. Platformer\n";
        echo "3. Physics Puzzle\n";
        echo "4. Algorithm\n";
        echo "5. Arcade\n";
        echo "6. Graphics\n";
        echo "7. Back\n";
        echo "Enter your choice: ";

        $choice = trim(fgets(STDIN));

        switch ($choice) {
            case '1':
                launchCreator(1);
                break;
            case '2':
                launchCreator(2);
                break;
            case '3':
                launchCreator(3);
                break;
            case '4':
                launchCreator(4);
                break;
            case '5':
                launchCreator(5);
                break;
            case '6':
                launchCreator(6);
                break;
            case '7':
                $invalid = false;
                break;
            default:
                echo "Invalid choice. Please try again.\n";
                echo "Press any key to continue...";
                system('pause >nul');
        }
    } while ($invalid);
}

function myGamesMenu() {
    $invalid = true;
    $userId = $_SESSION['userid'];
    $limit = 10; // Default limit to 10
    $offset = 0; // Default offset to 0
    $page = 1;

    do {
        system('clear');
        // Request user's games JSON data from the server on mygames.php
        $response = file_get_contents(SERVER_PROTOCOL . "://" . SERVER_HOST . ":" . SERVER_PORT . "/my/games.php?PHPSESSID=" . $_SESSION['sessionId'] . "&limit=$limit&offset=$offset");
        $games = json_decode($response, true);

        // Check if no games are returned
        if (empty($games)) {
            if ($offset == 0) {
            echo "You have no games.\n";
            echo "Press any key to continue...";
            system('pause >nul');
            $invalid = false;
            break;
            } else {
            echo "No more games to display.\n";
            echo "Press any key to continue...";
            system('pause >nul');
            $offset -= $limit;
            $page--;
            continue;
            }
        }

        echo "My Games (Page $page):\n";
        echo str_pad("ID", 10) . str_pad("Title", 30) . "Date\n";
        echo str_repeat("-", 50) . "\n";
        foreach ($games as $game) {
            echo str_pad($game['g_id'], 10) . str_pad($game['title'], 30) . $game['date'] . "\n";
        }
        echo "Enter game ID to play, 'delete' to delete a game, 'next' for next page, 'prev' for previous page, 'limit' to change number of games per page, or 'back' to go back: ";
        $choice = trim(fgets(STDIN));

        if ($choice == 'back') {
            echo "Going back...\n";
            $invalid = false;
        } elseif ($choice == 'next') {
            $offset += $limit;
            $page++;
        } elseif ($choice == 'prev') {
            if ($offset > 0) {
                $offset -= $limit;
                $page--;
            }
        } elseif ($choice == 'limit') {
            echo "Enter number of games per page (max 20): ";
            $newLimit = (int)trim(fgets(STDIN));
            if ($newLimit > 0 && $newLimit <= 20) {
                $limit = $newLimit;
                $offset = 0;
                $page = 1;
            } else {
                echo "Invalid limit. Please enter a number between 1 and 20.\n";
                echo "Press any key to continue...";
                system('pause >nul');
            }
        } elseif ($choice == 'delete') {
            echo "Enter game ID to delete: ";
            $deleteId = trim(fgets(STDIN));
            $deleteResponse = file_get_contents(SERVER_PROTOCOL . "://" . SERVER_HOST . ":" . SERVER_PORT . "/my/deletegame.php?PHPSESSID=".$_SESSION['sessionId']."&gameid=$deleteId");
            if ($deleteResponse == "true") {
                echo "Game deleted successfully.\n";
                echo "Press any key to continue...";
                system('pause >nul');
            } else {
                echo "Failed to delete game.\n";
                echo "Press any key to continue...";
                system('pause >nul');
            }
        } else {
            $found = false;
            foreach ($games as $game) {
                if ($game['g_id'] == $choice) {
                    if($game['ispublished'] == 0){
                        echo "The game is not published. Please publish it as private in the creator in order to play.\n";
                        echo "Press any key to continue...";
                        system('pause >nul');
                        $found = true;
                        break;
                    } else {
                        echo "Playing game...\n";
                        launchGame($game['user_id'] . '_' . $game['g_id'], $game['g_swf']);
                        $found = true;
                        break;
                    }
                }
            }
            if (!$found) {
                echo "Invalid game ID. Please try again.\n";
                echo "Press any key to continue...";
                system('pause >nul');
            }
        }
    } while ($invalid);
}

function displayMenu()
{
    system('clear');
    echo "Welcome to the Local Sploder Client.\n";
    echo "1. Login\n";
    echo "2. Register\n";
    echo "3. Change configuration\n";
    echo "4. Exit\n";
    echo "Enter your choice: ";
}

function login($input)
{
    // Use HTTP POST request to send login data to the server
    $data = http_build_query($input);
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n"
                . "Content-Length: " . strlen($data) . "\r\n",
            'content' => $data,
        ]
    ]);

    $response = file_get_contents(SERVER_PROTOCOL . "://" . SERVER_HOST . ":" . SERVER_PORT . "/login.php", false, $context);
    $responseData = json_decode($response, true);

    if ($responseData['success']) {
        echo "Login successful.\n";
        $_SESSION['loggedIn'] = true;
        $_SESSION['sessionId'] = $responseData['sessionId'];
        $_SESSION['userid'] = $responseData['userid'];
        $_SESSION['username'] = $input['username'];
        showDashboard($_SESSION['loggedIn']);
    } else {
        echo "Login failed: ". $responseData['message']."\n";
        echo "Press any key to continue...";
        system('pause >nul');
    }
}

function showDashboard($loggedIn)
{
    while ($loggedIn) {
        system('clear');
        echo "Welcome to the dashboard.\n";
        // Display options to make games, play games, log out
        echo "1. Make a game\n";
        echo "2. Play games\n";
        echo "3. My games\n";
        echo "4. Log out\n";
        echo "Enter your choice: ";

        $choice = trim(fgets(STDIN));

        switch ($choice) {
            case '1':
                makeGameMenu();
                break;
            case '2':
                gamesMenu();
                break;
            case '3':
                myGamesMenu();
                break;
            case '4':
                echo "Logging out...\n";
                $_SESSION['loggedIn'] = false;
                $loggedIn = false;
                break;
            default:
                echo "Invalid choice. Please try again.\n";
        }
    }
}

function loginMenu()
{
    system('clear');
    echo "Enter username: ";
    $username = trim(fgets(STDIN));

    echo "Enter password: ";
    $password = trim(fgets(STDIN));

    return [
        'username' => $username,
        'password' => $password,
    ];
}

function registerMenu()
{
    system('clear');
    // Add confirm password
    echo "Enter username: ";
    $username = trim(fgets(STDIN));

    echo "Enter password: ";
    $password = trim(fgets(STDIN));

    // Confirm
    echo "Confirm password: ";
    $confirmPassword = trim(fgets(STDIN));

    if ($password !== $confirmPassword) {
        echo "Password and confirmed password do not match.\n";
        return null;
    }

    return [
        'username' => $username,
        'password' => $password,
        'confirmPassword' => $confirmPassword,
    ];
}

function register($input)
{
    // Use HTTP POST request to send register data to the server
    $data = http_build_query($input);
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n"
                . "Content-Length: " . strlen($data) . "\r\n",
            'content' => $data,
        ]
    ]);

    $response = file_get_contents(SERVER_PROTOCOL . "://" . SERVER_HOST . ":" . SERVER_PORT . "/register.php", false, $context);
    if ($response != "true") {
        echo "Register failed.\n";
    } else {
        echo "Register successful.\n";
    }
}

if (!file_exists($configFile) || filesize($configFile) == 0) {
    changeConfiguration($configFile, $config);
} else {
    $config = json_decode(file_get_contents($configFile), true);
    applyConfig($config, $configFile);
}

while (true) {
    displayMenu();
    $choice = trim(fgets(STDIN));
    system('clear');
    switch ($choice) {
        case '1':
            $input = loginMenu();
            login($input);
            break;
        case '2':
            $input = registerMenu();
            if ($input !== null) {
                register($input);
            }
            // Add register functionality here
            break;
        case '3':
            changeConfiguration($configFile);
            break;
        case '4':
            echo "Exiting...\n";
            exit;
        default:
            echo "Invalid choice. Please try again.\n";
            echo "Press any key to continue...";
            system('pause >nul');
    }
}