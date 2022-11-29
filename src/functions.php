<?php

/** csatlakozik a scriptben létrehozott adatbázishoz
 */
function initDb()
{
    return mysqli_connect('mysql', 'rozi', 'llALKOKO1_12-KnxO', 'phpdb');
}

/** bezárja az adatbázist
 */
function closeDb($db)
{
    mysqli_close($db);
}

/** ha be van lépve, akkor visszatér a felhasználóval
 */
function is_logged_in()
{
    return isset($_SESSION['user']);
}

/** felhasználó admin?
 */
function is_admin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

/** átirányítás függvény egy célpontra
 */
function redirect($target)
{
    header("Location: " . $target);
    exit(0);
}

/** azonos oldal újratöltése
 */
function reload_page()
{
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit(0);
}

/** kilépés (felhasználó adatok törlése sessionből), login php-ra való visszalépés
 */
function logout()
{
    unset($_SESSION['role']);
    unset($_SESSION['user']);
    session_destroy();
    redirect("login.php");
}

/** hibaüzenet függvények
 */
function has_error()
{
    return isset($_SESSION['errormessage']);
}
function clear_error_message()
{
    unset($_SESSION['errormessage']);
}

function print_error($message)
{
    print("<h2 style='color: red;'>" . $message . "</h2>");
}

function set_error_message($message)
{
    $_SESSION['errormessage'] = $message;
}