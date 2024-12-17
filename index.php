<?php
// Koneksi ke database
$host = '127.0.0.1:3307';
$user = 'root';
$password = '';
$dbname = 'kkk';
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Menangani impor data dari Excel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    require 'vendor/autoload.php'; // Library PHPSpreadsheet

    $file = $_FILES['excel_file']['tmp_name'];
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
    $data = $spreadsheet->getActiveSheet()->toArray();

    foreach ($data as $index => $row) {
        if ($index === 0)
            continue; // Lewati header
        $nama = $row[0];
        $provinsi = $row[1];
        $nilai = $row[2];
        $grade = $nilai >= 91 ? 'A' : ($nilai >= 71 ? 'B' : 'C');
        $conn->query("INSERT INTO perusahaan (nama_perusahaan, provinsi, nilai, grade) VALUES ('$nama', '$provinsi', $nilai, '$grade')");
    }

    echo "<script>alert('Data berhasil diimpor');</script>";
}

// Menangani input manual
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama'])) {
    $nama = $_POST['nama'];
    $provinsi = $_POST['provinsi'];
    $nilai = $_POST['nilai'];
    $grade = $nilai >= 91 ? 'A' : ($nilai >= 71 ? 'B' : 'C');

    $conn->query("INSERT INTO perusahaan (nama_perusahaan, provinsi, nilai, grade) VALUES ('$nama', '$provinsi', $nilai, '$grade')");
    echo "<script>alert('Data berhasil ditambahkan');</script>";
}

// Mengambil data dari database
$query = "SELECT provinsi, AVG(nilai) AS avg_nilai, 
          COUNT(*) AS total, 
          SUM(CASE WHEN grade = 'A' THEN 1 ELSE 0 END) AS grade_a,
          SUM(CASE WHEN grade = 'B' THEN 1 ELSE 0 END) AS grade_b,
          SUM(CASE WHEN grade = 'C' THEN 1 ELSE 0 END) AS grade_c
          FROM perusahaan 
          GROUP BY provinsi 
          ORDER BY avg_nilai DESC"; // Urutkan berdasarkan rata-rata nilai
$result = $conn->query($query);
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Ambil 3 provinsi dengan nilai rata-rata tertinggi
$top_3 = array_slice($data, 0, 3);

// Cari provinsi
if (isset($_GET['btn_cari_provinsi']) && isset($_GET['cari_provinsi'])) {
    $provinsi_dicari = $_GET['cari_provinsi'];
    $query_provinsi = "SELECT provinsi, AVG(nilai) AS avg_nilai FROM perusahaan WHERE provinsi = '$provinsi_dicari' GROUP BY provinsi";
    $result_provinsi = $conn->query($query_provinsi);
    $cari_data = $result_provinsi->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik Perusahaan</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .container {
            margin: 20px;
        }

        .top-provinsi,
        .search-result {
            margin-top: 20px;
        }

        .bar {
            height: 20px;
            margin: 5px 0;
            color: white;
            text-align: center;
            line-height: 20px;
        }

        .grade_a {
            background-color: green;
        }

        .grade_b {
            background-color: yellow;
        }

        .grade_c {
            background-color: red;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Statistik Perusahaan Antar Provinsi</h1>

        <!-- Form Impor Data -->
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="excel_file">Impor Data Excel:</label>
            <input type="file" name="excel_file" id="excel_file" accept=".xlsx, .xls" required>
            <button type="submit">Impor Data</button>
        </form>

        <!-- Form Input Manual -->
        <form action="" method="POST">
            <h2>Input Data Manual</h2>
            <label for="nama">Nama Perusahaan:</label>
            <input type="text" name="nama" required>
            <label for="provinsi">Provinsi:</label>
            <select name="provinsi" required>
                <option value="">-- Pilih Provinsi --</option>
                <option value="Aceh">Aceh</option>
                <option value="Sumatera Utara">Sumatera Utara</option>
                <option value="Sumatera Barat">Sumatera Barat</option>
                <option value="Riau">Riau</option>
                <option value="Kepulauan Riau">Kepulauan Riau</option>
                <option value="Jambi">Jambi</option>
                <option value="Sumatera Selatan">Sumatera Selatan</option>
                <option value="Bangka Belitung">Bangka Belitung</option>
                <option value="Bengkulu">Bengkulu</option>
                <option value="Lampung">Lampung</option>
                <option value="DKI Jakarta">DKI Jakarta</option>
                <option value="Jawa Barat">Jawa Barat</option>
                <option value="Banten">Banten</option>
                <option value="Jawa Tengah">Jawa Tengah</option>
                <option value="Daerah Istimewa Yogyakarta">Daerah Istimewa Yogyakarta</option>
                <option value="Jawa Timur">Jawa Timur</option>
                <option value="Bali">Bali</option>
                <option value="Nusa Tenggara Barat">Nusa Tenggara Barat</option>
                <option value="Nusa Tenggara Timur">Nusa Tenggara Timur</option>
                <option value="Kalimantan Barat">Kalimantan Barat</option>
                <option value="Kalimantan Tengah">Kalimantan Tengah</option>
                <option value="Kalimantan Selatan">Kalimantan Selatan</option>
                <option value="Kalimantan Timur">Kalimantan Timur</option>
                <option value="Kalimantan Utara">Kalimantan Utara</option>
                <option value="Sulawesi Utara">Sulawesi Utara</option>
                <option value="Gorontalo">Gorontalo</option>
                <option value="Sulawesi Tengah">Sulawesi Tengah</option>
                <option value="Sulawesi Selatan">Sulawesi Selatan</option>
                <option value="Sulawesi Barat">Sulawesi Barat</option>
                <option value="Sulawesi Tenggara">Sulawesi Tenggara</option>
                <option value="Maluku">Maluku</option>
                <option value="Maluku Utara">Maluku Utara</option>
                <option value="Papua">Papua</option>
                <option value="Papua Barat">Papua Barat</option>
                <option value="Papua Selatan">Papua Selatan</option>
                <option value="Papua Tengah">Papua Tengah</option>
                <option value="Papua Pegunungan">Papua Pegunungan</option>
                <option value="Papua Barat Daya">Papua Barat Daya</option>

            </select>
            <label for="nilai">Nilai:</label>
            <input type="number" name="nilai" min="0" max="100" required>
            <button type="submit">Tambah Data</button>
        </form>

        <!-- Pencarian Provinsi -->
        <h2>Cari Provinsi</h2>
        <form action="" method="GET">
            <label for="cari_provinsi">Pilih Provinsi:</label>
            <select name="cari_provinsi" required>
                <option value="Aceh">Aceh</option>
                <option value="Sumatera Utara">Sumatera Utara</option>
                <option value="Sumatera Barat">Sumatera Barat</option>
                <option value="Riau">Riau</option>
                <option value="Kepulauan Riau">Kepulauan Riau</option>
                <option value="Jambi">Jambi</option>
                <option value="Sumatera Selatan">Sumatera Selatan</option>
                <option value="Bangka Belitung">Bangka Belitung</option>
                <option value="Bengkulu">Bengkulu</option>
                <option value="Lampung">Lampung</option>
                <option value="DKI Jakarta">DKI Jakarta</option>
                <option value="Jawa Barat">Jawa Barat</option>
                <option value="Banten">Banten</option>
                <option value="Jawa Tengah">Jawa Tengah</option>
                <option value="Daerah Istimewa Yogyakarta">Daerah Istimewa Yogyakarta</option>
                <option value="Jawa Timur">Jawa Timur</option>
                <option value="Bali">Bali</option>
                <option value="Nusa Tenggara Barat">Nusa Tenggara Barat</option>
                <option value="Nusa Tenggara Timur">Nusa Tenggara Timur</option>
                <option value="Kalimantan Barat">Kalimantan Barat</option>
                <option value="Kalimantan Tengah">Kalimantan Tengah</option>
                <option value="Kalimantan Selatan">Kalimantan Selatan</option>
                <option value="Kalimantan Timur">Kalimantan Timur</option>
                <option value="Kalimantan Utara">Kalimantan Utara</option>
                <option value="Sulawesi Utara">Sulawesi Utara</option>
                <option value="Gorontalo">Gorontalo</option>
                <option value="Sulawesi Tengah">Sulawesi Tengah</option>
                <option value="Sulawesi Selatan">Sulawesi Selatan</option>
                <option value="Sulawesi Barat">Sulawesi Barat</option>
                <option value="Sulawesi Tenggara">Sulawesi Tenggara</option>
                <option value="Maluku">Maluku</option>
                <option value="Maluku Utara">Maluku Utara</option>
                <option value="Papua">Papua</option>
                <option value="Papua Barat">Papua Barat</option>
                <option value="Papua Selatan">Papua Selatan</option>
                <option value="Papua Tengah">Papua Tengah</option>
                <option value="Papua Pegunungan">Papua Pegunungan</option>
                <option value="Papua Barat Daya">Papua Barat Daya</option>

            </select>
            <button type="submit" name="btn_cari_provinsi">Cari</button>
        </form>

        <?php if (isset($cari_data)): ?>
            <div class="search-result" style="background-color: yellow; padding: 10px;">
                <p>Provinsi: <?= htmlspecialchars($provinsi_dicari) ?></p>
                <p>Rata-rata Nilai: <?= number_format($cari_data['avg_nilai'], 2) ?></p>
                <a href="perusahaan.php?provinsi=<?= urlencode($provinsi_dicari) ?>">Lihat Detail Perusahaan</a>
            </div>
        <?php endif; ?>

        <!-- Menampilkan 3 Provinsi Tertinggi -->
        <h2>3 Provinsi Tertinggi Berdasarkan Rata-rata Nilai</h2>
        <div class="top-provinsi">
            <?php foreach ($top_3 as $index => $item): ?>
                <div
                    style="background-color: <?= $index == 0 ? 'green' : ($index == 1 ? 'yellow' : 'red') ?>; heigt: 30px; width: 80px; padding: 5px; margin-bottom: 5px;">
                    <a href="perusahaan.php?provinsi=<?= urlencode($item['provinsi']) ?>" style="color: white; text-decoration: none;">
                    <p><?= $item['provinsi'] ?>: <?= number_format($item['avg_nilai'], 2) ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Grafik Pie Chart -->
        <canvas id="pie-chart" width="10" height="10"></canvas>
        <script>
            var ctx = document.getElementById('pie-chart').getContext('2d');
            var pieChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['<?= $top_3[0]['provinsi'] ?>', '<?= $top_3[1]['provinsi'] ?>', '<?= $top_3[2]['provinsi'] ?>'],
                    datasets: [{ data: [<?= $top_3[0]['avg_nilai'] ?>, <?= $top_3[1]['avg_nilai'] ?>, <?= $top_3[2]['avg_nilai'] ?>], backgroundColor: ['green', 'yellow', 'red'] }]
                }
            });
        </script>
    </div>
</body>

</html>