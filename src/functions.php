<?php

/** csatlakozik a scriptben létrehozott adatbázishoz
 */
function initDb()
{   //TODO környezeti változókból inkább ?
    return mysqli_connect('mysql', 'rozi', 'llALKOKO1_12-KnxO', 'phpdb');
}

/** bezárja az adatbázist
 */
function closeDb($db)
{
    mysqli_close($db);
}

/** ha be van lépve, akkor igaz
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

/** átirányítás függvény egy web címre
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

// hibaüzenet függvények

/** Van hiba ?
 * @return bool
 */
function has_error()
{
    return isset($_SESSION['errormessage']);
}

/** utolsó hiba türlése
 * @return void
 */
function clear_error_message()
{
    unset($_SESSION['errormessage']);
}

/** Adott üzenet hibaként való kiírása
 * @param $message üzenet
 * @return void
 */
function print_error($message)
{
    print("<h2 style='color: red;'>" . $message . "</h2>");
}

/** Hibaüzenet beállítása
 * @param $message
 * @return void
 */
function set_error_message($message)
{
    $_SESSION['errormessage'] = $message;
}