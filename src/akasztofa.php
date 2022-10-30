<?php
SESSION_start();
mb_internal_encoding('UTF-8');
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
                print 'Kitaláltad a keresett szót :)';
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

print '</center>';
?>

