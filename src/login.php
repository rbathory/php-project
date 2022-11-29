<?php
SESSION_start();
require "functions.php";

/** ha be van lépve, akkor a megfelelő ablakba küldi a felhasználót role-tól függően
 */
if (is_logged_in()) {
    goto_default();
}

/** belépéskor hajtja végre
 */
if (isset($_POST['login'])) {
    login();
}


print_login_form();

/** megvizsgálja, hogy a felhasználó jogosult e a belépésre (felhasználó jelszó ellenőrzés)
 */
function login()
{
    $db = initDb();
    $username = $_POST['username'];
    $password_hash = hash('sha256', $_POST['password']);
    $sql = 'SELECT role FROM `users` WHERE username="' . $username . '" and password="' . $password_hash . '" ';
    $result = mysqli_query($db, $sql);
    if (mysqli_num_rows($result) != 1) { // csak egy ilyen felhasználó jelszó páros van-e
        closeDb($db);
        set_error_message('Sikertelen belépés');
        reload_page();
    }
    $role = mysqli_fetch_assoc($result)['role'];
    $_SESSION['role'] = $role;
    $_SESSION['user'] = $username;
    closeDb($db);
    clear_error_message();
    goto_default();

}

/** login form kirakása
 */
function print_login_form()
{
    echo '<Center><H2>Belépés</H2>';
    if (isset($_SESSION['errormessage'])) {
        print_error( $_SESSION['errormessage']);
    }
    echo '<FORM NAME="loginform" method="POST">
	Felhasználónév<BR><INPUT TYPE="text" name="username"><p>
	Jelszó<BR><INPUT TYPE="password" name="password"><P>
	<INPUT TYPE="submit" name="login" value="Belépés">
</FORM></Center>';
}

/** átirányítás
 */
function goto_default()
{
    if (is_admin()) {
        redirect("admin.php"); // ha admin akkor átdobja automatikusan az admin php-ra
    }
    redirect("akasztofa.php"); // ha játékos akkor az akasztófa php-ra
}




