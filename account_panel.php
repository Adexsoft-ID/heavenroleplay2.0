<?php
session_start();
include 'php/database.php'; // Pastikan Anda menghubungkan ke database

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: register-login/login.php");
    exit();
}

// Ambil data pengguna dari database
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Proses update data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $agama = $_POST['agama'];
    $nomor_hp = $_POST['nomor_hp'];
    $gender = $_POST['gender'];
    $password = $_POST['password'];
    
    // Proses upload foto
    $profile_picture = $user['profile_picture']; // Default to current picture
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "img/profiles/"; // Pastikan folder ini ada
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        
        // Cek apakah file sudah ada
        if (file_exists($target_file)) {
            echo json_encode(['status' => 'error', 'message' => 'File sudah ada, silakan ganti nama file!']);
            exit();
        } else {
            // Upload file
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $profile_picture = $target_file;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan saat mengupload gambar!']);
                exit();
            }
        }
    }

    // Update data ke database
    $update_query = "UPDATE users SET username='$username', email='$email', agama='$agama', nomor_hp='$nomor_hp', gender='$gender', profile_picture='$profile_picture' WHERE id='$user_id'";
    
    // Jika update berhasil
    if (mysqli_query($conn, $update_query)) {
        // Update session profile picture
        $_SESSION['profile_picture'] = $profile_picture; // Update session dengan gambar baru
        echo json_encode(['status' => 'success', 'message' => 'Data profil berhasil diperbarui!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan saat memperbarui data: ' . mysqli_error($conn)]);
    }

    // Jika password diisi, update password
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password='$hashed_password' WHERE id='$user_id'");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <title>Profile Update</title>
</head>
<body>
    <section id="content">
        <main class="container mt-5">
            <div class="head-title">
                <h1>Update Profile</h1>
            </div>
            <form id="updateProfileForm" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo $user['username']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="agama" class="form-label">Agama</label>
                    <input type="text" class="form-control" id="agama" name="agama" value="<?php echo $user['agama']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="nomor_hp" class="form-label">Nomor HP</label>
                    <input type="text" class="form-control" id="nomor_hp" name="nomor_hp" value="<?php echo $user['nomor_hp']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="gender" class="form-label">Gender</label>
                    <select class="form-select" id="gender" name="gender" required>
                        <option value="Laki-laki" <?php echo ($user['gender'] == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                        <option value="Perempuan" <?php echo ($user['gender'] == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="profile_picture" class="form-label">Foto Profil</label>
                    <input type="file" class="form-control" id="profile_picture" name="profile_picture">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password (Kosongkan jika tidak ingin mengubah)</label>
                    <input type="password" class="form-control" id="password" name="password">
                </div>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </main>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#updateProfileForm').on('submit', function(e) {
                e.preventDefault(); // Mencegah form dari pengiriman default

                // Mengirim data form menggunakan AJAX
                $.ajax({
                    url: 'account_panel.php', // URL ke file PHP
                    type: 'POST',
                    data: new FormData(this), // Mengirim data form
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        const result = JSON.parse(response);
                        if (result.status === 'success') {
                            Swal.fire({
                                title: 'Sukses!',
                                text: result.message,
                                icon: 'success'
                            }).then(() => {
                                window.location.href = 'index.php'; // Arahkan ke halaman index setelah sukses
                            });
                        } else {
                            Swal.fire('Gagal!', result.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Gagal!', 'Terjadi kesalahan saat menghubungi server.', 'error');
                    }
                });
            });
        });
    </script>
</body>
</html>