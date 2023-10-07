<?php
require 'koneksi.php'; // Koneksi ke database
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daftar Tugas</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins&display=swap');
        body {
            font-family: Arial, sans-serif;
        }

        h2 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .add-button {
            background-color: #008CBA;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 10px;
            cursor: pointer;
        }

        *{
            font-family: 'Poppins';
            font-size: 16px;
        }

        .add-button:hover {
            background-color: #005f7f;
        }
    </style>
</head>
<body>
    <h2>Daftar KOL</h2>
    <table border="1">
        <tr>
            <th>Created_at</th>
            <th>Name</th>
            <th>Type Influencer</th>
            <th>Rate Card</th>
            <th>Sosmed</th>
            <th>Average</th>
            <th>Followers</th>
            <th>Contact Person</th>
            <th>Spark Ads Code</th>
        </tr>
        <?php
        $query = "SELECT kol.created_at, kol.name_kol, influencer.name_influencer, kol.rate_card, sosmed.name_sosmed, kol.average, kol.followers, kol.contact_person, kol.spark_ads_code
                  FROM kol
                  INNER JOIN influencer ON kol.id_influencer = influencer.id_influencer
                  INNER JOIN sosmed ON kol.id_sosmed = sosmed.id_sosmed";
        $result = $conn->query($query);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['created_at'] . "</td>";
                echo "<td>" . $row['name_kol'] . "</td>";
                echo "<td>" . $row['name_influencer'] . "</td>";
                echo "<td>" . $row['rate_card'] . "</td>";
                echo "<td>" . $row['name_sosmed'] . "</td>";
                echo "<td>" . $row['average'] . "</td>";
                echo "<td>" . $row['followers'] . "</td>";
                echo "<td>" . $row['contact_person'] . "</td>";
                echo "<td>" . $row['spark_ads_code'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='9'>Tidak ada tugas yang ditemukan.</td></tr>";
        }
        $conn->close();
        ?>
    </table>
    <p> <button class="add-button" onclick="location.href='add_kol.php'">Tambah KOL</button> </p>
    <p> <button class="add-button" onclick="location.href='list_detail_kol.php'">Lihat List Detail KOL</button> </p>
    <p> <button class="add-button" onclick="location.href='index.php'">Scrape Instagram</button> </p>
</body>
</html>
