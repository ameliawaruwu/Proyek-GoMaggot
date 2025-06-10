<?php
$user = [
  'photo' => 'profile.jpg',
  'nickname' => 'coolkid21',
  'name' => 'John Doe',
  'email' => 'john@example.com'
];
?>

<!DOCTYPE html>
<html>
<head>
  <title>User Profile Modal</title>
  <style>
    /* Modal styles */
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
    .modal-content { background: #fff; margin: 10% auto; padding: 20px; width: 400px; border-radius: 8px; position: relative; }
    .close { position: absolute; top: 10px; right: 15px; font-size: 20px; cursor: pointer; }
    .profile-img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; }
    input[type="text"], input[type="email"] { width: 100%; padding: 8px; margin: 8px 0; }
    button { padding: 8px 12px; cursor: pointer; }
  </style>
</head>
<body>

<button onclick="openModal()">Lihat Profil</button>

<!-- Modal -->
<div id="profileModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <form action="update_profile.php" method="post">
      <div style="text-align:center;">
        <img src="<?= $user['photo'] ?>" alt="Profile Picture" class="profile-img">
      </div>
      <label>Nickname</label>
      <input type="text" name="nickname" value="<?= $user['nickname'] ?>" required>

      <label>Nama</label>
      <input type="text" name="name" value="<?= $user['name'] ?>" required>

      <label>Email</label>
      <input type="email" name="email" value="<?= $user['email'] ?>" required>

      <div style="text-align: right; margin-top: 12px;">
        <button type="submit">Update Profile</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal() {
  document.getElementById("profileModal").style.display = "block";
}

function closeModal() {
  document.getElementById("profileModal").style.display = "none";
}

window.onclick = function(event) {
  const modal = document.getElementById("profileModal");
  if (event.target == modal) {
    closeModal();
  }
}
</script>

</body>
</html>
