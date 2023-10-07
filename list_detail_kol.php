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
        *{
            font-family: 'Poppins';
            font-size: 16px;
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

        .add-button:hover {
            background-color: #005f7f;
        }
    </style>
</head>
<body>
    <h2>Daftar Detail KOL</h2>
    <table border="1">
        <tr>
            <th>Created_at</th>
            <th>Name</th>
            <th>Link Postingan</th>
            <th>Rate Card Tawar</th>
            <th>Sales</th>
            <th>Omset</th>
            <th>Nama Brand</th>
            <th>Nama Program</th>
            <th>fee</th>
            <th>date</th>
            <th>likes</th>
            <th>comments</th>
        </tr>
        <?php
        $query = "SELECT  detail_kol.created_at, kol.name_kol, detail_kol.link_postingan, 
                  detail_kol.rate_card_tawar, detail_kol.sales, detail_kol.omset, brand.name_brand,
                  program.name_program, detail_kol.fee,detail_kol.date_post,detail_kol.likes,detail_kol.comments
                  FROM detail_kol
                  INNER JOIN kol ON detail_kol.id_kol = kol.id
                  INNER JOIN brand ON detail_kol.id_brand = brand.id_brand
                  INNER JOIN program ON detail_kol.id_program = program.id_program";
        $result = $conn->query($query);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['created_at'] . "</td>";
                echo "<td>" . $row['name_kol'] . "</td>";
                echo "<td>" . $row['link_postingan'] . "</td>";
                echo "<td>" . $row['rate_card_tawar'] . "</td>";
                echo "<td>" . $row['sales'] . "</td>";
                echo "<td>" . $row['omset'] . "</td>";
                echo "<td>" . $row['name_brand'] . "</td>";
                echo "<td>" . $row['name_program'] . "</td>";
                echo "<td>" . $row['fee'] . "</td>";
                echo "<td>" . $row['date_post'] . "</td>";
                echo "<td>" . $row['likes'] . "</td>";
                echo "<td>" . $row['comments'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='9'>Tidak ada tugas yang ditemukan.</td></tr>";
        }
        $conn->close();
        ?>
    </table>
    <p> <button class="add-button" onclick="location.href='add_detail_kol.php'">Tambah Detail KOL</button> </p>
</body>
</html>
