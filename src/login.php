<?php
SESSION_start();
require "functions.php";


if (is_logged_in()) {
    goto_default();
}

if (isset($_POST['login'])) {
    login();
}
print_login_form();

function login()
{
    $db = initDb();
    $username = $_POST['username'];
    $password_hash = hash('sha256', $_POST['password']);
    $sql = 'SELECT role FROM `users` WHERE username="' . $username . '" and password="' . $password_hash . '" ';
    $result = mysqli_query($db, $sql);
    if (mysqli_num_rows($result) != 1) {
        closeDb($db);
        $_SESSION['errormessage'] = 'Sikertelen belépés';
        reload_page();
    }
    $role = mysqli_fetch_assoc($result)['role'];
    $_SESSION['role'] = $role;
    $_SESSION['user'] = $username;
    closeDb($db);
    goto_default();

}

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


function goto_default()
{
    if (is_admin()) {
        redirect("admin.php");
    }
    redirect("akasztofa.php");
}




