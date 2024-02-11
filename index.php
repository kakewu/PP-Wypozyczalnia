<?php

class Klient {
    public $id;
    public $imieNazwisko;
    public $dataPrawaJazdy;

    public function __construct($id, $imieNazwisko, $dataPrawaJazdy) {
        $this->id = $id;
        $this->imieNazwisko = $imieNazwisko;
        $this->dataPrawaJazdy = $dataPrawaJazdy;
    }
}

class Samochod {
    public $id;
    public $markaModel;
    public $segment;
    public $rodzajPaliwa;
    public $status;
    public $cenaZaDobe;

    public function __construct($id, $markaModel, $segment, $rodzajPaliwa, $status, $cenaZaDobe) {
        $this->id = $id;
        $this->markaModel = $markaModel;
        $this->segment = $segment;
        $this->rodzajPaliwa = $rodzajPaliwa;
        $this->status = $status;
        $this->cenaZaDobe = $cenaZaDobe;
    }

    public function wypozycz() {
        $this->status = 'niedostępny';
    }
}

function obliczRozniceLat($data1, $data2) {
    $diff = date_diff(date_create($data1), date_create($data2));
    return $diff->y;
}

function wypozyczSamochod($klient, $segment, $rodzajPaliwa, $dni, &$samochody) {
    $dostepneSamochody = array_filter($samochody, function($samochod) use ($segment, $rodzajPaliwa) {
        return $samochod->segment == $segment && $samochod->rodzajPaliwa == $rodzajPaliwa && $samochod->status == 'dostępny';
    });

    if (empty($dostepneSamochody)) {
        echo "Przykro mi, brak dostępnych samochodów spełniających podane kryteria.<br>";
        return;
    }

    $wybranySamochod = reset($dostepneSamochody);

    $podstawowaCena = $wybranySamochod->cenaZaDobe * $dni;

    $roznicaLat = obliczRozniceLat($klient->dataPrawaJazdy, date('Y-m-d'));
    if ($roznicaLat < 4 && $segment == 'premium') {
        echo "Klient nie może wypożyczyć samochodu segmentu premium, ponieważ ma prawo jazdy krócej niż 4 lata.<br>";
        return;
    }
    if ($roznicaLat < 4 && $segment == 'kompakt') {
        $segment = 'mini'; 
    }
    if ($dni > 7) {
        $podstawowaCena -= $wybranySamochod->cenaZaDobe; 
    }
    if ($dni > 30) {
        $podstawowaCena -= $wybranySamochod->cenaZaDobe * 3; 
    }

    $wybranySamochod->wypozycz(); 

    echo "Data wypożyczenia: " . date('Y-m-d') . "<br>";
    echo "Imię i nazwisko klienta: $klient->imieNazwisko <br>";
    echo "Data zwrotu pojazdu: " . date('Y-m-d', strtotime("+$dni days")) . "<br>";
    echo "Marka i model pojazdu: $wybranySamochod->markaModel <br>";
    echo "Całkowita cena za wypożyczenie: $podstawowaCena PLN <br>";
}

$klienci = array(
    new Klient(1, 'Jan Nowak', '2021-03-04'),
    new Klient(2, 'Agnieszka Kowalska', '1999-01-15'),
    new Klient(3, 'Robert Lewandowski', '2010-12-18'),
    new Klient(4, 'Zofia Plucińska', '2020-04-29'),
    new Klient(5, 'Grzegorz Braun', '2015-07-12')
);

$samochody = array(
    new Samochod(1, 'Skoda Citigo', 'mini', 'benzyna', 'dostępny', 70),
    new Samochod(2, 'Toyota Aygo', 'mini', 'benzyna', 'dostępny', 90),
    new Samochod(3, 'Fiat 500', 'mini', 'elektryczny', 'dostępny', 110),
    new Samochod(4, 'Ford Focus', 'kompakt', 'diesel', 'dostępny', 160),
    new Samochod(5, 'Kia Ceed', 'kompakt', 'benzyna', 'dostępny', 150),
    new Samochod(6, 'Volkswagen Golf', 'kompakt', 'benzyna', 'dostępny', 160),
    new Samochod(7, 'Hyundai Kona Electric', 'kompakt', 'elektryczny', 'dostępny', 180),
    new Samochod(8, 'Audi A6 Allroad', 'premium', 'diesel', 'dostępny', 290),
    new Samochod(9, 'Mercedes E270 AMG', 'premium', 'benzyna', 'dostępny', 320),
    new Samochod(10, 'Tesla Model S', 'premium', 'elektryczny', 'dostępny', 350)
);

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wypożyczalnia samochodów</title>
    <style>
        .dostepny { color: green; }
        .niedostepny { color: red; }
    </style>
</head>
<body>

<h2>Lista klientów:</h2>
<ul>
    <?php foreach ($klienci as $klient): ?>
        <li>ID: <?php echo $klient->id; ?> - <?php echo $klient->imieNazwisko; ?></li>
    <?php endforeach; ?>
</ul>

<h2>Lista marek samochodów:</h2>
<ul>
    <?php foreach ($samochody as $samochod): ?>
        <?php
            $statusClass = $samochod->status == 'dostępny' ? 'dostepny' : 'niedostepny';
        ?>
        <li class="<?php echo $statusClass; ?>"><?php echo $samochod->markaModel; ?> - <?php echo $samochod->status; ?></li>
    <?php endforeach; ?>
</ul>

<h2>Formularz wypożyczenia samochodu:</h2>
<form action="" method="post">
    <label for="klientId">Wybierz klienta:</label>
    <select name="klientId" id="klientId">
        <?php foreach ($klienci as $klient): ?>
            <option value="<?php echo $klient->id; ?>"><?php echo $klient->imieNazwisko; ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="segment">Wybierz segment samochodu (mini, kompakt, premium):</label>
    <select name="segment" id="segment">
        <option value="mini">Mini</option>
        <option value="kompakt">Kompakt</option>
        <option value="premium">Premium</option>
    </select><br><br>

    <label for="rodzajPaliwa">Wybierz rodzaj paliwa:</label>
    <select name="rodzajPaliwa" id="rodzajPaliwa">
        <option value="benzyna">Benzyna</option>
        <option value="diesel">Diesel</option>
        <option value="elektryczny">Elektryczny</option>
    </select><br><br>

    <label for="dni">Ilość dni wypożyczenia:</label>
    <input type="number" name="dni" id="dni" min="1" required><br><br>

    <button type="submit">Wypożycz</button>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $wybranyKlientId = $_POST["klientId"];
    $wybranySegment = $_POST["segment"];
    $wybranyRodzajPaliwa = $_POST["rodzajPaliwa"];
    $wybraneDni = $_POST["dni"];

    $wybranyKlient = null;
    foreach ($klienci as $klient) {
        if ($klient->id == $wybranyKlientId) {
            $wybranyKlient = $klient;
            break;
        }
    }

    if ($wybranyKlient) {
        echo "<h2>Wynik wypożyczenia:</h2>";
        wypozyczSamochod($wybranyKlient, $wybranySegment, $wybranyRodzajPaliwa, $wybraneDni, $samochody);
    } else {
        echo "<p>Nie znaleziono wybranego klienta.</p>";
    }
}
?>

</body>
</html>
