<?php
SESSION_start();

mysqli_report(MYSQLI_REPORT_STRICT);


$db = initDb();

if (isset($_POST['login'])) {
    login();
    echo 'Üdv az oldalon: '.$_POST['username'].'<BR>';
    if ($_SESSION['role']=='admin'){
        echo '<A href="?add=on">Új felhasználó</A><BR>';
        $sql='SELECT username as `user`, password as `pass`, role FROM `users`';
        $result=mysqli_query($db,$sql);
        while ($row=mysqli_fetch_assoc($result)){
            echo $row['user'].'<A href="?mod=on&user='.$row['user'].'"> Módosítás</A><BR>';
        }
    }	else {
        $sql='SELECT username as `user`, password as `pass` FROM `users` WHERE username="'.$_SESSION['user'].'"';
        $result=mysqli_query($db,$sql);
        $row=mysqli_fetch_assoc($result);
        echo $row['user'].'<A href="?mod=on&user='.$row['user'].'"> Módosítás</A><BR>';


    }
}
if (isset($_GET['logout']))
{
    logout();
    header("Location: akasztofa.php");
}

if (!isset($_SESSION['user'])) {
    print_login_form();
    closedb($db);
    exit(0);
}

if(isset($_GET['add']) &&  $_GET['add'] =='on' && $_SESSION['rule']=='admin'){ // új felhasználó form
    echo '<FORM NAME="form1" action="akasztofa.php" method="POST">
	<INPUT TYPE="text" name="user">
	<INPUT TYPE="text" name="pass">
	<INPUT TYPE="text" name="role">
	<INPUT TYPE="submit" name="add" value="Létrehoz">
	</FORM>';
}

if (isset($_POST['add']) && $_SESSION['rule']=='admin'){ // új felhasználó adat hozzáadás
    $sql='INSERT INTO `users` SET username="'.$_POST['user'].'", password="'.$_POST['pass'].'", role="'.$_POST['role'].'"'; //????
    if (mysqli_query($db,$sql)){
        echo '<B>Sikeres felvitel</B><BR>';
    } else {
        echo '<B>HIBA</B><BR>';
    }
}

if(isset($_GET['mod']) &&  $_GET['mod'] =='on'){ // módosítás form
    if ($_SESSION['role']=='admin'){
        $sql='SELECT * FROM `user` WHERE nev="'.$_GET['user'].'"';
    } else { // az aktuális userre keressen
        $sql='SELECT * FROM `user` WHERE nev="'.$_SESSION['user'].'"';
    }
    $result=mysqli_query($db,$sql);
    $row=mysqli_fetch_assoc($result);
    echo '<FORM NAME="form1" action="akasztofa.php?user='.$_GET['user'].'" method="POST">
	<INPUT TYPE="text" name="user" value="'.$row['nev'].'">
	<INPUT TYPE="text" name="pass" value="'.$row['jelszo'].'">
	<INPUT TYPE="submit" name="mod" value="Módosít">
	</FORM>';
}
// ha a felhasználó módosítás formot elküljük, akkor ezáltal modosítsuk az adatokat (a post tömbben az új adatok, a get-ben a régi adat található, ami alapján modosítunk)
if (isset($_POST['mod'])){
    if ($_SESSION['jog']==1){
        $sql='UPDATE `user` SET nev="'.$_POST['user'].'", jelszo="'.$_POST['pass'].'" WHERE nev="'.$_GET['user'].'"';
    } else {
        $sql='UPDATE `user` SET nev="'.$_POST['user'].'", jelszo="'.$_POST['pass'].'" WHERE nev="'.$_SESSION['name'].'"';
    }
    if (mysqli_query($db,$sql)){
        echo '<B>Sikeres módosítás</B><BR>';
    } else {
        echo '<B>HIBA</B><BR>';
    }
}

mb_internal_encoding('UTF-8');
print '<P ALIGN="RIGHT"><A href="?logout">Kilépés</A>';
print '<center>';
$done = false;


if (!array_key_exists('szavak', $_SESSION)) {
    print("Beolvasom" . '<br>');
    $szoveg = mb_strtolower('ALMA körte Szilva csereSZNYE ananász meggy citrom szŐLő narancs DATOlya KIWI banán kókusz sárkánygyümölcs', 'UTF-8'); // kisbetű lesz csak
    $szavak0 = explode(' ', $szoveg); // tömb a szövegből aminek elemei a szavak

    $szavak = [];
    for ($i = 0; $i < count($szavak0); $i++) {
        $szo = $szavak0[$i];
        if (mb_strlen($szo, 'UTF-8') >= 4 && mb_strlen($szo, 'UTF-8') <= 12) // i. elem hosszától függően kerülhet be a keresett szavak tömbjébe
        {
            $szavak[] = $szo;
        }
    }
    $_SESSION['szavak'] = $szavak;
    init($szavak); // újratöltésnél újrakezd
    $_SESSION['gyozelmek']=0;
}


if (isset($_GET['new']) && $_GET['new'] == 'on') // van egy új szó (új játék)
{
    init($_SESSION['szavak']);
}

$hiba = $_SESSION['hiba'];
$szo = $_SESSION['szo'];
$has = $_SESSION['has'];

print 'A keresett szó: ' . $szo . '<br>'; // ELLENŐRZŐ PONT


function utf8_cserel($has, $i, $a)
{
    return mb_substr($has, 0, $i) . $a . mb_substr($has, $i + 1);
}


if (isset($_POST["submit"])) // beküldök egy tippet
{
    $a = $_POST['tipp'];
    if (mb_strlen($a, 'UTF-8') == 1) { //ha nem hosszabb a beírt tipp, mint 1 betű, csak akkor megy tovább

        $found = false;
        if (!in_array($a, $_SESSION['tippek'])) {
            $_SESSION['tippek'][] = $a;
            $_SESSION['prob']++;
            for ($i = 0; $i < mb_strlen($szo, 'UTF-8'); $i++) {
                if ($a == mb_substr($szo, $i, 1, 'UTF-8')) {
                    $has = utf8_cserel($has, $i, $a); //   $has[$i] = $a;
                    $found = true;

                }
            }
            if (!$found) {
                $hiba++;
                $_SESSION['hiba'] = $hiba;
            } else {
                $_SESSION['has'] = $has;
            }
            if ($has == $szo) {
                print 'Kitaláltad a keresett szót :)'.'<br>';
                $_SESSION['gyozelmek']+=1;
                $done = true;

            }
        } else {
            print "<div style='color: red; font-weight: bold'>Már tippeltél erre a betűre!</div>";
        }
    } else {
        print "<div style='color: red; font-weight: bold'>Nem megfelelő hosszúságú a tipped, egy betűt adjál meg!</div>";
    }

}

print('<img src="images/kep' . $hiba . '.png">') . '<br>';
if ($hiba >= 10) {
    print 'Felakasztottak, vesztettél :(';
    $done = true;
}
print ('<H1 style="letter-spacing: 5px">' . $has . '</H1>');
print 'Tippek: ' . join(", ", $_SESSION['tippek']) . '<br>';
print 'Próbálkozások száma: ' . $_SESSION['prob'] . '<br>';
print "<div style='color: blue; font-weight: bold'>Győzelmek száma: </div>".$_SESSION['gyozelmek'];
print '<BR><a href ="akasztofa.php?new=on"> Új szó </a>';

if (!$done) {
    print '<FORM NAME="form1" action="akasztofa.php" method="POST">
    Tipp: <INPUT TYPE="text" name="tipp">
    <INPUT type="submit" name="submit" value="Próbál">
    </FORM>';
}

/**
 * ez a függvény azt csinálja, hogy
 * @param $szavak
 * @return int
 */
function init($szavak)
{
    $_SESSION['szo'] = $szavak[array_rand($szavak)]; // az szavak tömbből egy randomot választ
    $_SESSION['hiba'] = 0; // hibaszámláló létrehozása
    $_SESSION['tippek'] = [];
    $_SESSION['has'] = str_repeat('_', mb_strlen($_SESSION['szo'], 'UTF-8'));
    $_SESSION['prob'] = 0;
}

function print_login_form()
{
    echo '<Center><H2>Belépés</H2>
<FORM NAME="loginform" method="POST">
	Felhasználónév<BR><INPUT TYPE="text" name="username"><p>
	Jelszó<BR><INPUT TYPE="password" name="password"><P>
	<INPUT TYPE="submit" name="login" value="Belépés">
</FORM></Center>';
}

function login()
{
    global $db;
    $username = $_POST['username'];
    $password_hash = hash('sha256', $_POST['password']);
    $sql = 'SELECT role FROM `users` WHERE username="' . $username . '" and password="' . $password_hash . '" ';
    $result = mysqli_query($db, $sql);
    if (mysqli_num_rows($result) != 1) {
        echo '<FONT COLOR="red"><B>Sikertelen belépés</B></FONT><P>';
        return;
    }
    $role = mysqli_fetch_assoc($result)['role'];
    $_SESSION['role'] = $role;
    $_SESSION['user'] = $username;

}

function logout()
{
    unset($_SESSION['role']);
    unset($_SESSION['user']);
}

function initDb()
{
    return mysqli_connect('79.139.60.134', 'rozi', 'jasjkkjLJIJ_1231kJ', 'phpdb');
}

function closeDb($db)
{
    mysqli_close($db);
}


print '</center>';
?>

