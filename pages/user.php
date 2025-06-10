<?php 
include '../logic/update/auth.php'; 
include '../views/headeradmin.php';
include '../configdb.php'; // Include file koneksi database

// Query untuk mengambil semua data pengguna
$sql = "SELECT id_pelanggan, username, email, role, foto_profil FROM pengguna";
$result = $conn->query($sql);

?>

<link rel="stylesheet" href="../Admin-HTML/css/admin.css">
<link rel="stylesheet" href="../Admin-HTML/css/adminUser.css">
<main>
    <div class="head-title">
        <div class="left">
            <h1>User Management</h1>
            <ul class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">User</a></li>
            </ul>
        </div>
        <a href="addUser.php" class="btn-download">
            <i class='bx bxs-plus-circle'></i>
            <span class="text">Add New User</span>
        </a>
    </div>

    <div class="table-data">
        <div class="order">
            <div class="head">
                <h3>User List</h3>
                <i class='bx bx-search'></i>
                <i class='bx bx-filter'></i>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Nama User</th>
                            <th class="hide-on-mobile">Email</th>
                            <th>Role</th>
                            <th class="hide-on-mobile">Foto Profil</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $photoPath = !empty($row['foto_profil']) && file_exists("../photos/" . $row['foto_profil'])
                                    ? "../photos/" . htmlspecialchars($row['foto_profil'])
                                    : "../Admin-HTML/img/no-avatar.png";

                                $userId = trim($row['id_pelanggan']);
                        ?>
                                <tr>
                                    <td data-label="Id"><?php echo htmlspecialchars($userId); ?></td>
                                    <td data-label="Nama User"><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td data-label="Email" class="hide-on-mobile"><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td data-label="Role">
                                        <span class="status <?php echo ($row['role'] == 'admin' ? 'process' : 'completed'); ?>">
                                            <?php echo htmlspecialchars($row['role']); ?>
                                        </span>
                                    </td>
                                    <td data-label="Foto Profil" class="hide-on-mobile">
                                        <?php if (!empty($row['foto_profil']) && file_exists("../photos/" . $row['foto_profil'])) : ?>
                                            <img src="<?php echo $photoPath; ?>" alt="Foto Profil" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                                        <?php else : ?>
                                            Tidak Ada
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Action" style="white-space: nowrap;">
                                        <button type="button"
                                                class="btn-action btn-edit"
                                                title="Edit User"
                                                style="margin-right: 5px;"
                                                onclick="window.location.href='editUser.php?id=<?php echo urlencode($userId); ?>'">
                                            <i class='bx bxs-edit'></i>
                                        </button>

                                        <button type="button"
                                                class="btn-action btn-delete"
                                                title="Delete User"
                                                onclick="deleteUser(<?php echo htmlspecialchars($userId); ?>, '<?php echo htmlspecialchars(addslashes($row['username'])); ?>')">
                                            <i class='bx bxs-trash'></i>
                                        </button>
                                    </td>
                                    </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='6'>Tidak ada data user.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script>
function deleteUser(userId, username) {
    if (confirm('Apakah Anda yakin ingin menghapus user ' + username + '?')) {
        window.location.href = 'deleteUser.php?id=' + encodeURIComponent(userId);
    }
}

// Override any existing JavaScript that might interfere
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.btn-edit');
    const deleteButtons = document.querySelectorAll('.btn-delete');

    console.log('Edit buttons:', editButtons.length);
    console.log('Delete buttons:', deleteButtons.length);

    editButtons.forEach(function(btn) {
        btn.style.pointerEvents = 'auto';
        btn.style.cursor = 'pointer';
    });

    deleteButtons.forEach(function(btn) {
        btn.style.pointerEvents = 'auto';
        btn.style.cursor = 'pointer';
    });
});
</script>

<script src="../Admin-HTML/js/AdminUser.js"></script>

<?php
$conn->close();
include '../views/footeradmin.php';
?>