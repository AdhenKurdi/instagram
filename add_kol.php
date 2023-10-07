<?php
require 'koneksi.php'; // Koneksi ke database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nameKol = $_POST['name_kol'];
    $rateCard = $_POST['rate_card'];
    $idSosmed = $_POST['id_sosmed'];
    $idInfluencer = $_POST['id_influencer'];
    $contactPerson = $_POST['contact_person'];
    $sparkAdsCode = $_POST['spark_ads_code'];
    
    $apiUrl = 'http://127.0.0.1:5000/get-instagram-profile?username=' . $nameKol;

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $average = 0;
    $followers = 0;

    $queryKol = "SELECT * FROM kol WHERE name_kol = '$nameKol'"; 
    $result = $conn->query($queryKol);


    $data = json_decode($response, true);
    foreach ($data as $item) {
        $average += ($item["engagement"]); // ambil nilai likes + comments
        $followers = $item["followers"]; // ambil jumlah followers
    }

    $averageResult = $average / count($data); // (likes + comments) / jumlah data postingan
    $roundedAverageResult = intval($averageResult); // dibulatkan ;

    if ($result->num_rows > 0) {
        echo "Username sudah digunakan, silakan gunakan username lain.";
    } else {
        $sql = "INSERT INTO kol (
            name_kol, rate_card, contact_person,spark_ads_code,id_sosmed,id_influencer,average,followers
        ) VALUES (
            '$nameKol', $rateCard, '$contactPerson','$sparkAdsCode',$idSosmed,$idInfluencer,$roundedAverageResult,$followers
        )";

        if ($conn->query($sql) === TRUE) {
            // header('Location: list_kol.php');
            echo "Data berhasil ditambahkan" ;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
        $conn->close();
    }
   

    
}
?>

<h2>Tambah KOL Baru</h2>
<form method="POST" action="add_kol.php">
    Nama Kol: <input type="text" name="name_kol" required><br><br>
    Rate Card: <input type="text" name="rate_card" type="number" required><br><br>
    Jns sosmed:
    <select name="id_sosmed">
       
        <?php
            require 'koneksi.php'; // Koneksi ke database
            $sosmedQuery = "SELECT * FROM sosmed";
            $sosmedResult = $conn->query($sosmedQuery);
            if ($sosmedResult->num_rows > 0) {
                while ($row = $sosmedResult->fetch_assoc()) {
                    echo "<option value='" . $row['id_sosmed'] . "'>" . $row['name_sosmed'] . "</option>";
                }
            }
            $conn->close();
        ?>

    </select><br><br>

    Type Influencer:
    <select name="id_influencer">
       
        <?php
            require 'koneksi.php'; // Koneksi ke database
            $influencerQuery = "SELECT * FROM influencer";
            $influencerResult = $conn->query($influencerQuery);
            if ($influencerResult->num_rows > 0) {
                while ($row = $influencerResult->fetch_assoc()) {
                    echo "<option value='" . $row['id_influencer'] . "'>" . $row['name_influencer'] . "</option>";
                }
            }
            $conn->close();
        ?>

    </select><br><br>
    Contact Person: <input  name="contact_person" type="number" required><br><br>
    Spark Ads Code: <input type="text" name="spark_ads_code"  required><br><br>

    <input type="submit" name="submit" value="Tambah KOL">
</form>

<head> 
<style>
     @import url('https://fonts.googleapis.com/css2?family=Poppins&display=swap');
    *{
            font-family: 'Poppins';
            font-size: 16px;
    }

    h2 {
        text-align: center;
        color: #333;
    }

    form {
        max-width: 400px;
        margin: 0 auto;
        padding: 20px;
        background-color: #fff;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        border-radius: 5px;
    }

    form input[type="text"],
    form input[type="number"],
    form select {
        width: 100%;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 3px;
    }

    form select {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background: transparent;
        background-image: url('arrow.png'); /* Ganti 'arrow.png' dengan path gambar panah dropdown yang Anda inginkan */
        background-repeat: no-repeat;
        background-position: right center;
        padding-right: 30px; /* Sesuaikan padding-right sesuai dengan lebar gambar panah */
    }

    form input[type="submit"] {
        background-color: #007bff;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 3px;
        cursor: pointer;
    }

    form input[type="submit"]:hover {
        background-color: #0056b3;
    }

</style>

</head>