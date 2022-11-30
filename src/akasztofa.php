<?php
SESSION_start();
require "functions.php"; // másik php beolvasása

mysqli_report(MYSQLI_REPORT_STRICT);

if (!is_logged_in()) { // ha nincs belépve akkor automatikusan átdobja a login php-ra
    redirect("login.php");
}

if (isset($_GET['logout'])) {
    logout();
}

$db = initDb();
$d_scores = get_stats(true);
$t_scores = get_stats(false);
echo 'Üdv az oldalon: ' . $_SESSION['user'] . '<BR>';


mb_internal_encoding('UTF-8');
print '<P ALIGN="RIGHT">';
if (is_admin()) {
    print '<A href="admin.php" style="margin-right: 10px">Admin oldalra</A>';
}
print '<A href="?logout">Kilépés</A>';
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
    $_SESSION['gyozelmek'] = 0; // győzelem számláló elindítása
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
                print 'Kitaláltad a keresett szót :)' . '<br>';
                $_SESSION['gyozelmek'] =+1 ; // győzelmek számának növelése
                update_games(true); // győzelemnél 1 értéket ír az adatbázisba
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
    update_games(false); // veszítésnél 0 értéket ír az adatbázisba

    $done = true;
}

print ('<H1 style="letter-spacing: 5px">' . $has . '</H1>');
print 'Tippek: ' . join(", ", $_SESSION['tippek']) . '<br>';
print 'Próbálkozások száma: ' . $_SESSION['prob'] . '<br>';
print "<div style='color: blue; font-weight: bold'>Győzelmek száma: </div>" . $_SESSION['gyozelmek'];
print '<BR><a href ="akasztofa.php?new=on"> Új szó </a>';

/** ameddig nem találja ki, addig tippelhet (form)
 */
if (!$done) {
    print '<FORM NAME="form1" action="akasztofa.php" method="POST">
    Tipp: <INPUT TYPE="text" name="tipp" autofocus>
    <INPUT type="submit" name="submit" value="Próbál">
    </FORM>';
}

print "<P>";
print_scores(true);
print_scores(false);

print '</center>';
closeDb($db);
##################################################################x

/** kezdő értékek, tömbök létreghozása
 */
function init($szavak)
{
    $_SESSION['szo'] = $szavak[array_rand($szavak)]; // az szavak tömbből egy randomot választ
    $_SESSION['hiba'] = 0; // hibaszámláló létrehozása
    $_SESSION['tippek'] = [];
    $_SESSION['has'] = str_repeat('_', mb_strlen($_SESSION['szo'], 'UTF-8'));
    $_SESSION['prob'] = 0;
}

/** győzelmek folyamatos frissítése, adatbázisba feltöltése dátummal
 */
function update_games($won)
{
    global $db;
    $user = $_SESSION['user'];
    $today = date("Y-m-d");
    $sql = $won ? // gyózelem vagy veszteség
        "INSERT INTO games values('$user','$today',1,0)" :
        "INSERT INTO games values('$user','$today',0,1)";
    mysqli_query($db, $sql);
}

/** napi- és örök rekord táblázatba rendezése
 */
function print_scores($daily)
{
    $records = get_stats($daily); // melyiket ?
    $type = $daily ? "Napi" : "Örök";

    print "<H3>$type rangsor</H3>";
    print "<TABLE border='1px'>";
    foreach ($records as $line) {
        print "<TR><TD>{$line[0]}</TD><TD>{$line[1]}/{$line[2]}</TD></TR>\n";
    }
    print "</TABLE><P>";
}

/** napi- és örök rekord lekérdezése
 */
function get_stats($daily)
{
    global $db;
    $today = date("Y-m-d");
    $condition = $daily ? "where day=STR_TO_DATE('{$today}','%Y-%m-%d') " : ""; // napi szűrő hozzáadása ha kell
    $sql = "select username, SUM(WON) as wins ,SUM(LOST)+SUM(WON) as total from games {$condition}group by username"; // felhasználónév, győzelmek, nyerési arány

    $result = mysqli_query($db, $sql);
    $records = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $records[] = [$row['username'], $row['wins'], $row['total']];
    }
    usort($records, 'cmp_stats'); // nyerési arány szerinti sorrendbe rendezés spéci függvénnyel
    return $records;
}

/** nyerési arány szerinti sorba rendező
 */
function cmp_stats($a, $b)
{
    $r1 = $a[1] / $a[2];
    $r2 = $b[1] / $b[2];
    if ($r1 == $r2) return 0;
    return $r1 < $r2 ? 1 : -1;
}


