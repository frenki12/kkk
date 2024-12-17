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

// Menghapus data berdasarkan ID
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM perusahaan WHERE id = $delete_id");
    header("Location: perusahaan.php?provinsi=" . urlencode($_GET['provinsi']));
    exit();
}

// Mengedit data berdasarkan ID
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $result = $conn->query("SELECT * FROM perusahaan WHERE id = $edit_id");
    $row = $result->fetch_assoc();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nama = $_POST['nama'];
        $provinsi = $_POST['provinsi'];
        $nilai = $_POST['nilai'];
        $grade = $nilai >= 91 ? 'A' : ($nilai >= 71 ? 'B' : 'C');

        // Update data perusahaan
        $conn->query("UPDATE perusahaan SET nama_perusahaan = '$nama', provinsi = '$provinsi', nilai = $nilai, grade = '$grade' WHERE id = $edit_id");
        header("Location: perusahaan.php?provinsi=" . urlencode($provinsi));
        exit();
    }
}

// Menampilkan data perusahaan berdasarkan provinsi
if (isset($_GET['provinsi'])) {
    $provinsi = $_GET['provinsi'];
    $query = "SELECT * FROM perusahaan WHERE provinsi = '$provinsi'";
    $result = $conn->query($query);

    // Menghitung total nilai semua perusahaan di provinsi
    $total_query = "SELECT SUM(nilai) AS total_nilai FROM perusahaan WHERE provinsi = '$provinsi'";
    $total_result = $conn->query($total_query);
    $total_row = $total_result->fetch_assoc();
    $total_nilai = $total_row['total_nilai'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Perusahaan di Provinsi <?= htmlspecialchars($provinsi) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .container {
            margin-top: 20px;
        }

        .company-card {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            color: white;
        }

        .grade-a {
            background-color: green;
        }

        .grade-b {
            background-color: yellow;
            color: black;
        }

        .grade-c {
            background-color: red;
        }

        .total-nilai {
            font-weight: bold;
            margin-top: 20px;
            color: darkblue;
        }

        a {
            text-decoration: none;
            color: blue;
        }

        a:hover {
            text-decoration: underline;
        }

        button {
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Tombol Kembali ke Index -->
        <a href="index.php" style="
            display: inline-block;
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: bold;
        ">
            &larr; Kembali ke Index
        </a>

        <h1>Data Perusahaan di Provinsi <?= htmlspecialchars($provinsi) ?></h1>

        <!-- Total Nilai Semua Perusahaan -->
        <div class="total-nilai">
            Total Nilai Semua Perusahaan: <?= number_format($total_nilai, 2) ?>
        </div>

        <!-- Edit Data -->
        <?php if (isset($row)): ?>
            <form action="" method="POST">
                <label for="nama">Nama Perusahaan:</label>
                <input type="text" name="nama" id="nama" value="<?= $row['nama_perusahaan'] ?>" required>

                <label for="provinsi">Provinsi:</label>
                <input type="text" name="provinsi" value="<?= $row['provinsi'] ?>" readonly>

                <label for="nilai">Nilai:</label>
                <input type="number" name="nilai" id="nilai" min="0" max="100" value="<?= $row['nilai'] ?>" required>

                <button type="submit">Update</button>
            </form>
        <?php endif; ?>

        <!-- Menampilkan Perusahaan -->
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php
            // Tentukan kelas CSS berdasarkan nilai
            $grade_class = $row['nilai'] >= 91 ? 'grade-a' : ($row['nilai'] >= 71 ? 'grade-b' : 'grade-c');
            ?>
            <div class="company-card <?= $grade_class ?>">
                <h4><?= htmlspecialchars($row['nama_perusahaan']) ?></h4>
                <p>Nilai: <?= $row['nilai'] ?> | Grade: <?= $row['grade'] ?></p>
                <a href="perusahaan.php?edit_id=<?= $row['id'] ?>&provinsi=<?= urlencode($provinsi) ?>">Edit</a> |
                <a href="perusahaan.php?delete_id=<?= $row['id'] ?>&provinsi=<?= urlencode($provinsi) ?>"
                   onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
            </div>
        <?php endwhile; ?>
    </div>
</body>

</html>
