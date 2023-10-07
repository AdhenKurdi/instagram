<?php
require 'koneksi.php'; // Koneksi ke database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $idKol = $_POST['id_kol'];

    $nameKol = "";
    $queryKol = "SELECT * FROM kol WHERE id = '$idKol'"; 
    $result = $conn->query($queryKol);
    if ($result) {
    // Jika query berhasil dieksekusi
        while ($row = $result->fetch_assoc()) {
            // Mengakses data hasil query
            $nameKol = $row["name_kol"];  
        }
    } else {
        // Jika query gagal
        echo "Error: " . $conn->error;
    }


    $linkPostingan = $_POST['link_postingan'];
    $rateCardTawar = $_POST['rate_card_tawar'];
    $sales = $_POST['sales'];
    $omset = $_POST['omset'];
    $idBrand = $_POST['id_brand'];
    $idProgram = $_POST['id_program'];
    $fee = $_POST['fee'];

    $apiUrl = 'http://127.0.0.1:5000/get-instagram-profile?username='  . $nameKol;

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

   

    $data = json_decode($response, true);
    $date = "";
    $likes = 0;
    $comments = 0;

    echo "<br>" ;
    echo $linkPostingan . "<br>";

    $filteredData = [];

    foreach ($data as $item) {
        $link = $item["shortcode"] ;
        echo $link . "<br>";
        if ($link == $linkPostingan) {
            $filteredData[] = $item;
            break;
        }
    }

   
    var_dump($filteredData);

    
    if(count($filteredData) > 0){
        foreach ($filteredData as $item) {
            $date  = date('Y-m-d', strtotime($item["date"]));
            $likes = $item["likes"]; 
            $comments = $item["comments"]; 
        }
        if($date != "" && $likes != 0 && $comments != 0){
            $sql = "INSERT INTO detail_kol (
                id_kol,link_postingan, rate_card_tawar, sales,omset,id_brand,id_program,fee,date_post,likes,comments
                ) VALUES (
                    $idKol, '$linkPostingan', '$rateCardTawar',$sales,$omset,$idBrand,$idProgram,$fee,'$date',$likes,$comments
                )";

            if ($conn->query($sql) === TRUE) {
                // header('Location: list_detail_kol.php');
                echo "Data berhasil ditambahkan" ;
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
            $conn->close();
        }else{
            echo "Ada yang tidak beres nih bos" ;
        }
    }else{
        echo "Tidak menemukan postinagn untuk username instagram" .$nameKol;
    }
    
}
?>

<h2>Tambah detail KOL Baru</h2>
<form method="POST" action="add_detail_kol.php">
    Nama Kol:
    <select name="id_kol">
       
        <?php
            require 'koneksi.php'; // Koneksi ke database
            $sosmedQuery = "SELECT * FROM kol";
            $sosmedResult = $conn->query($sosmedQuery);
            if ($sosmedResult->num_rows > 0) {
                while ($row = $sosmedResult->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['name_kol'] . "</option>";
                }
            }
            $conn->close();
        ?>

    </select><br><br>
    Link Postingan: <input type="text" name="link_postingan" type="text" required><br><br>
    Rate card tawar : <input type="text" name="rate_card_tawar" type="text" required><br><br>
    Sales : <input name="sales" type="number" required><br><br>
    Omset : <input name="omset" type="number" required><br><br>
    Brand:
    <select name="id_brand">
       
        <?php
            require 'koneksi.php'; // Koneksi ke database
            $brandQuery = "SELECT * FROM brand";
            $brandResult = $conn->query($brandQuery);
            if ($brandResult->num_rows > 0) {
                while ($row = $brandResult->fetch_assoc()) {
                    echo "<option value='" . $row['id_brand'] . "'>" . $row['name_brand'] . "</option>";
                }
            }
            $conn->close();
        ?>

    </select><br><br>

    Program Affiliator :
    <select name="id_program">
       
        <?php
            require 'koneksi.php'; // Koneksi ke database
            $programQuery = "SELECT * FROM program";
            $programResult = $conn->query($programQuery);
            if ($programResult->num_rows > 0) {
                while ($row = $programResult->fetch_assoc()) {
                    echo "<option value='" . $row['id_program'] . "'>" . $row['name_program'] . "</option>";
                }
            }
            $conn->close();
        ?>

    </select><br><br>

    Fee Affiliator: <input  name="fee" type="number" required><br><br>
    

    <input type="submit" name="submit" value="Tambah Detail KOL">
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