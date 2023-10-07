<?php
require 'koneksi.php'; // Koneksi ke database


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tangani permintaan POST untuk menyimpan data ke database
    $postData = json_decode(file_get_contents('php://input'), true);

    // Pastikan data yang diterima sesuai dengan yang diharapkan
    if (
        isset($postData['date']) &&
        isset($postData['caption']) &&
        isset($postData['link']) &&
        isset($postData['likes']) &&
        isset($postData['comments']) &&
        isset($postData['followers']) &&
        isset($postData['engagement']) &&
        isset($postData['sentimen'])
    ) {
        $date = $postData['date'];
        $caption = $postData['caption'];
        $link = $postData['link'];
        $likes = $postData['likes'];
        $comments = $postData['comments'];
        $followers = $postData['followers'];
        $engagement = $postData['engagement'];
        $sentimen = $postData['sentimen'];

        // Siapkan query SQL untuk memeriksa apakah data dengan nilai tertentu sudah ada dalam tabel
        $checkQuery = "SELECT * FROM profil_data WHERE date = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("s", $date);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            // Data sudah ada, tidak perlu menyimpan data baru yang identik
            echo json_encode(['message' => 'Data sudah ada dalam database.']);
        } else {
            // Data belum ada, jalankan perintah SQL INSERT untuk menyimpan data baru
            $insertQuery = "INSERT INTO profil_data (date, caption, link, likes, comments, followers, engagement, sentimen) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertQuery);

            // Bind parameter ke query INSERT
            $insertStmt->bind_param("sssiiiis", $date, $caption, $link, $likes, $comments, $followers, $engagement, $sentimen);

            // Eksekusi query INSERT
            if ($insertStmt->execute()) {
                echo json_encode(['message' => 'Data telah disimpan di database.']);
            } else {
                echo json_encode(['message' => 'Gagal menyimpan data di database.' . $insertStmt->error]);
            }
        }
    } else {
        echo json_encode(['message' => 'Data yang diterima tidak lengkap atau salah.']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Instagram Profile Post Data</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            margin: 20px 0;
            color: #333; /* Ubah warna judul */
        }

        form {
            text-align: center;
            max-width: 400px; /* Batasi lebar form */
            margin: 0 auto; /* Pusatkan form di tengah halaman */
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold; /* Beri teks label ketebalan */
            color: #555; /* Ubah warna teks label */
        }

        input[type="text"] {
            padding: 10px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 20px; /* Tambahkan jarak antara input dan tombol */
        }

        button[type="button"] {
            background-color: #000;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
            transition: background-color 0.3s; /* Tambahkan efek hover */
        }

        button[type="button"]:hover {
            background-color: #41d11c;
        }

        #profile-data {
            margin-top: 20px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); /* Tambahkan bayangan ke tabel */
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .loading-spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 4px solid #3498db;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Style untuk Word Cloud Container */
        #wordcloud-container {
            display: none;
            text-align: center;
        }

        #wordcloud {
            margin-top: 20px;
        }

        /* Style untuk Network Word Cloud Container */
        #network-wordcloud-container {
            display: none;
            text-align: center;
        }

        #network-wordcloud {
            margin-top: 20px;
        }
    </style>
    <!-- Tambahkan script untuk D3.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/5.16.0/d3.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/d3-cloud/2.0.13/d3.layout.cloud.min.js"></script>
</head>
<body>
    <h1>Instagram Profile Posts Data</h1>
    <form>
        <label for="username">Enter Instagram Username:</label>
        <input type="text" id="username" name="username" required>
        <label for="postLimit">Limit the number of posts to:</label>
        <input type="number" id="postLimit" name="postLimit" min="1" value="10">
        <button type="button" id="fetch-button">Fetch Data</button>
        <a href="#" id="download-csv-link">Download CSV</a>
    </form>

    <!-- Display the fetched data here -->
    <div id="profile-data" style="display: none;">
        <!-- Table to display the fetched data -->
        <table id="post-table">
            <thead>
                <tr>
                    <th>Post Date</th>
                    <th>Caption</th>
                    <th>Link</th>
                    <th>Likes</th>
                    <th>Comments</th>
                    <th>Followers</th>
                    <th>Average</th>
                    <th>Sentimen</th>
                </tr>
            </thead>
            <tbody>
                <!-- Posts will be added here dynamically -->
            </tbody>
        </table>
    </div>

    <!-- Tampilkan status loading -->
    <div id="loading-status" style="display: none;">
        <div class="loading-spinner"></div>
        Fetching Data... Please Wait.
    </div>

    <!-- Tambahkan div untuk Word Cloud -->
    <div id="wordcloud-container" style="display: none; text-align: center;"> <!-- Atur style display ke "none" secara awal -->
        <h2>Word Cloud</h2>
        <div id="wordcloud"></div>
        <!-- Tampilkan gambar Word Cloud di sini -->
        <img src="" alt="Word Cloud" id="dynamic-wordcloud-image">
    </div>

    <!-- Tambahkan div untuk Network Word Cloud -->
    <div id="network-wordcloud-container" style="display: none; text-align: center;"> <!-- Atur style display ke "none" secara awal -->
        <h2>Network Word Cloud</h2>
        <div id="network-wordcloud"></div>
        <!-- Tampilkan gambar Network Word Cloud di sini -->
        <img src="" alt="Network Word Cloud" id="dynamic-network-wordcloud-image">
    </div>

    <script>
// Fungsi untuk mengambil dan menampilkan Network Word Cloud
function fetchNetworkWordCloud(username) {
    const url = `/get-network-wordcloud?username=${username}`;

    // Tampilkan status loading
    const loadingStatus = document.getElementById('loading-status');
    loadingStatus.style.display = 'block';

    fetch(url)
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        // Setelah mendapatkan respons, sembunyikan status loading
        loadingStatus.style.display = 'none';

        // Tampilkan Network Word Cloud
        document.getElementById('network-wordcloud-container').style.display = 'block'; // Tampilkan kontainer

        // Dengan asumsi data berisi nama file gambar
        const networkWordCloudImageFilename = data.imageFilename;

        // Bangun URL untuk gambar
        const imageUrl = `/static/${networkWordCloudImageFilename}`; // Ubah path gambar sesuai keinginan

        // Setel sumber gambar
        document.getElementById('dynamic-network-wordcloud-image').src = imageUrl; // Tampilkan gambar network word cloud
    })
    .catch(error => {
        console.error('Error fetching network word cloud data:', error);
    });
}




        // Add click event listener to the "Fetch Data" button
        document.getElementById('fetch-button').addEventListener('click', function() {
            const username = document.getElementById('username').value;
            const postLimit = document.getElementById('postLimit').value; // Ambil nilai batasan postingan
            const url = `http://127.0.0.1:5000/get-instagram-profile?username=${username}&postLimit=${postLimit}`;
            // Tampilkan status loading
            const loadingStatus = document.getElementById('loading-status');
            loadingStatus.style.display = 'block';

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    // Assign the fetched data to the profile_data variable
                    const profile_data = data;

                    // Create a table row for each post
                    const tableBody = document.querySelector('#post-table tbody');
                    tableBody.innerHTML = '';

                    // Setelah mendapatkan respons, sembunyikan status loading
                    loadingStatus.style.display = 'none';

                    profile_data.forEach(post => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${post.date}</td>
                            <td>${post.caption}</td>
                            <td><a href="${post.link}" target="_blank">Link Postingan</a></td>
                            <td>${post.likes}</td>
                            <td>${post.comments}</td>
                            <td>${post.followers}</td>
                            <td>${post.engagement}</td>
                            <td>${post.sentimen}</td>
                        `;
                        tableBody.appendChild(row);

                        // Simpan data ke dalam database MySQL
                        const postData = {
                            date: post.date,
                            caption: post.caption,
                            link: post.link,
                            likes: post.likes,
                            comments: post.comments,
                            followers: post.followers,
                            engagement: post.engagement,
                            sentimen: post.sentimen
                        };

                        // Buat permintaan AJAX untuk menyimpan data ke dalam database
                        fetch('index.php', {
                            method: 'POST',
                            body: JSON.stringify(postData),
                            headers: {
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(result => {
                            console.log('Data telah disimpan di database:', result);
                        })
                        .catch(error => {
                            console.error('Error saat menyimpan data:', error);
                        });
                    });

                    // Setelah mendapatkan respons, tampilkan gambar Word Cloud dan Table profile data
                    document.getElementById('profile-data').style.display = 'block';
                    document.getElementById('dynamic-wordcloud-image').src = 'static/wordcloud.png?' + new Date().getTime();
                    document.getElementById('wordcloud-container').style.display = 'block'; // Ubah style menjadi "block"

                    // Fetch and display Network Word Cloud
                    fetchNetworkWordCloud(username);
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                });
        });

        // Function to generate and trigger the CSV download
        function downloadCSV(username) {
            const url = `http://127.0.0.1:5000/download-csv?username=${username}`;

            fetch(url, {
                method: 'GET',
                mode: 'cors', // Tambahkan ini
            })
                .then(response => response.blob()) // Menggunakan response.blob() untuk mengambil sebagai blob
                .then(blob => {
                    // Buat objek Blob URL
                    const blobUrl = window.URL.createObjectURL(blob);

                    // Buat elemen anchor untuk mengunduh
                    const a = document.createElement('a');
                    a.href = blobUrl;
                    a.download = `profile_data_${username}.csv`;

                    // Simulasikan klik pada elemen anchor untuk mengunduh file
                    a.click();

                    // Hapus objek Blob URL setelah pengunduhan
                    window.URL.revokeObjectURL(blobUrl);
                })
                .catch(error => {
                    console.error('Error fetching data for CSV:', error);
                });
        }

        // Add click event listener to the "Download CSV" link
        document.getElementById('download-csv-link').addEventListener('click', function(event) {
            event.preventDefault(); // Mencegah tautan mengarahkan ke halaman baru

            // Panggil fungsi downloadCSV untuk mengunduh CSV
            const username = document.getElementById('username').value;
            downloadCSV(username);
        });

    </script>
</body>
</html>
