<?php
SESSION_start();
require "functions.php";
$ROLES = array("admin" => "Admin", "user" => "Játékos");

mysqli_report(MYSQLI_REPORT_STRICT);

if (!is_logged_in()) {
    redirect("login.php");
}

if (isset($_GET['logout']))
{
    logout();
}

$db = initDb();

if (!is_admin()) {
    print_error("Nem jogosult az oldal használatához !");
    exit(0);
}

# ide jönnek az admin műveletek
#
if (isset($_POST['add'])) { // új felhasználó adat hozzáadás
    if (user_exist($_POST['user'])) {
        set_error_message("A felhasználó már létezik!");
    } elseif (strlen($_POST['pass']) < 3) {
        set_error_message("Túl rövid jelszó");
    } elseif ($_POST['pass'] != $_POST['pass_again']) {
        set_error_message("Jelszó nem egyezik");
    } else {
        add_user();
    }
}


if (isset($_POST['del']) == 'true') { // felhasználó törlés
    if (!user_exist($_POST['user'])) {
        set_error_message("Nincs ilyen felhasználó ");
    } elseif ($_POST['user'] == $_SESSION['user']) { # ön törlés nem lehetséges
        set_error_message("Felhasználó nem törölhető ");
    } else {
        delete_user($_POST['user']);
    }

}
if (isset($_POST['mod']) == 'true') { // felhasználó role mentés
    # TODO role ellenőrzés
    mod_user($_POST['user'], $_POST['role']);
}
print '<P ALIGN="RIGHT"><A href="?logout">Kilépés</A>';

# táblázat
print '<CENTER>
<H1>Admin oldal</H1>';
if (has_error()) {
    print_error($_SESSION['errormessage']);
    clear_error();
}

print '
<TABLE BORDER="1px" CELLPADDING="5px">';
$sql = 'SELECT username as `user`, role FROM `users`';
$result = mysqli_query($db, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    print('<TR><FORM name="mod" METHOD="post">');
    print("<TD>{$row['user']}");
    print("<input type='hidden' name='user' value='{$row['user']}'>");
    print("</TD><TD>");
    print_role($row['role']);
    print "</TD>";

    if ($row['user'] == $_SESSION['user']) {
        print("<TD></TD>"); # magunkat soha ne töröljük
        print("<TD></TD>"); # magunkat soha ne zárjuk ki az adminságból
    } else {
        print("<TD><button type='submit' name='del'>Törlés</button></TD>");
        print("<TD><button type='submit' name='mod'>Szerepkör módosítása</button></TD>");}

    print('</FORM></TR>');
}

print '</TABLE>';
print '<H2>Új felhasználó létrehozása:</H2>';
print '<FORM autocomplete="off" method="POST">
	Felhasználónév : 
	<INPUT TYPE="text" name="user" autocomplete="off" > <P>
	Jelszó : 
	<INPUT TYPE="password" name="pass" autocomplete="off" ><P>
	Jelszó még egyszer: <INPUT TYPE="password" name="pass_again" autocomplete="off" ><P>
	<label for="role">Válasszon szerepkört:</label>';
print_role('user');
print '<P>
	<INPUT TYPE="submit" name="add" value="Létrehoz">
	</FORM>';
print "<A href='akasztofa.php'>Játszunk egyet mi is !</A>";
print '</CENTER>';

function add_user()
{
    global $db;
    $password = $_POST['pass'];
    $hash = hash('sha256', $password);
    $sql = "INSERT INTO users SET username='{$_POST['user']}', password='{$hash}', role='{$_POST['role']}'";
    if (!mysqli_query($db, $sql)) {
        set_error_message("HIBA történt");
    }
}

function mod_user($user, $role)
{
    global $db;
    $sql = "UPDATE users SET role='$role' where username='$user'";
    mysqli_query($db, $sql);
    if (mysqli_affected_rows($db) != 1) {
        set_error_message("HIBA történt");
    }

}

function delete_user($username)
{
    global $db;
    $sql = "DELETE FROM users WHERE username='{$username}'";
    mysqli_query($db, $sql);
    if (mysqli_affected_rows($db) != 1) {
        set_error_message("HIBA történt");
    }
}

function print_role($selected)
{
    global $ROLES;
    print '<select id="role" name="role">';
    foreach ($ROLES as $role => $display_name) {
        if ($role == $selected) {
            print "<option value='$role' selected='true'>$display_name</option>";
        } else {
            print "<option value='$role'>$display_name</option>";
        }
    }
    print '</select>';

}

function user_exist($username)
{
    global $db;
    $sql = "SELECT username FROM users WHERE username='$username'";
    $result = mysqli_query($db, $sql);
    return mysqli_num_rows($result) == 1;
}