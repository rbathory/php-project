<?php
SESSION_start();
$done = false;

if (!array_key_exists('szavak', $_SESSION)) {
    print("Beolvasom" . '<br>');
    $szoveg = strtolower('ALMA körte Szilva csereSZNYE ananász meggy citrom szŐLő narancs DATOlya KIWI banán kókusz sárkánygyümölcs'); // kisbetű lesz csak
    $szavak0 = explode(' ', $szoveg); // tömb a szövegből aminek elemei a szavak

    $szavak = [];
    for ($i = 0; $i < count($szavak0); $i++) {
        $szo = $szavak0[$i];
        if (strlen($szo) >= 4 && strlen($szo) <= 12) // i. elem hosszától függően kerülhet be a keresett szavak tömbjébe
        {
            $szavak[] = $szo;
        }
    }
}

$_SESSION['szavak'] = $szavak; // jó helyen van???
init($szavak); // újratöltésnél újrakezd

if (isset($_GET['new']) && $_GET['new'] == 'on') // van egy új szó (új játék)
{
    init($szavak);
}

$hiba = $_SESSION['hiba'];
$szo = $_SESSION['szo'];
$has = $_SESSION['has'];

print 'A keresett szó: ' . $szo . '<br>'; // ELLENŐRZŐ PONT


if (isset($_GET["submit"])) // beküldök egy tippet
{
    $a = $_GET['tipp'];
    if (strlen($a) == 1) { //ha nem hosszabb a beírt tipp, mint 1 betű, csak akkor megy tovább
        $_SESSION['prob']++;
        $found = false;
        $_SESSION['tippek'][] = $a; //jó heleyen??
        if (in_array($a, $_SESSION['tippek'])) {
            for ($i = 0; $i < strlen($szo); $i++) {
                if ($a == $szo[$i]) {
                    $has[$i] = $a;
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
                print 'Kitaláltad a keresett szót :)';
                $done = true;

            }
        }

        else {
            print "Már tippeltél erre a betűre";
            }}
    else {
    print "Nem megfelelő hosszúságú a tipped";
    }
}

print('<img src="images/kep' . $hiba . '.png">');
if ($hiba == 10) {
    print 'Felakasztottak, vesztettél :(';
}
print ('<H1 style="letter-spacing: 5px">' . $has . '</H1>');
print 'Tippek: '. $_SESSION['tippek'] . '<br>';
print 'Próbálkozások száma: ' . $_SESSION['prob'] . '<br>';
print '<BR><a href ="akasztofa.php?new=on"> Új szó </a>';

if (!$done) {
    print '<FORM NAME="form1" action="akasztofa.php" method="GET">
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
    $_SESSION['has'] = str_repeat('_', strlen($_SESSION['szo']));
    $_SESSION['prob'] = 0;
}

?>