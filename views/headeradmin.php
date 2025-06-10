<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link href='https://unpkg.com/boxicons@2.1.4/dist/boxicons.js' rel='stylesheet'>
    <link rel="stylesheet" href="../Admin-HTML/css/admin.css">
    <title>Dashboard Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- SIDEBAR -->
    <section id="sidebar">
        <div class="logo">
            <a>Go<span>Maggot</span></a>
        </div>
        <ul class="side-menu top">
            <li class="active">
                <a href="../pages/dashboard.php">
                    <i class='bx bxs-dashboard'></i>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <li class="active">
                <a href="../pages/galeri.php">
                    <i class='bx bx-images'></i>
                    <span class="text">Galery</span>
                </a>
            </li>
            <li>
                <a href="publikasi.php">
                    <i class='bx bx-library' ></i>
                    <span class="text">Publication</span>
                </a>
            </li>
            <li>
                <a href="produk.php">
                    <i class='bx bxs-shopping-bag-alt'></i>
                    <span class="text">Product</span>
                </a>
            </li>
            <li>
                <a href="user.php">
                    <i class='bx bxs-user'></i>
                    <span class="text">User</span>
                </a>
            </li>
             <li class="active">
                <a href="reviewsadmin.php">
                    <i class='bx  bx-star-square'></i> 
                    <span class="text">Reviews</span>
                </a>
            </li>
            <li>
                <a href="chat.php">
                    <i class='bx bxs-message-dots'></i>
                    <span class="text">Chat</span>
                </a>
            </li>
            <!--
            <li>
                <a href="admin.html">
                    <i class='bx bxs-group'></i>
                    <span class="text">Admin</span>
                </a>
            </li>
            -->

        </ul>
    </section>

    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>
            
            <form action="#">
                <div class="form-input">
                    <input type="search" placeholder="Search...">
                    <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
                </div>
            
            </form>
            <input type="checkbox" id="switch-mode" hidden>
            <label for="switch-mode" class="switch-mode"></label>
            <a href="#" class="notification" onclick="toggleNotificationMenu(event)">
                <i class='bx bxs-bell'></i>
                <span class="num">5</span>
            </a>
            
            <div class="notification-menu" id="notificationMenu">
                <h4>Notifications</h4>
                <ul>
                    <li>
                        <a href="#">
                            <p><strong>New Order</strong> from Sakira Amirah</p>
                            <span>5 minutes ago</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <p><strong>Payment Received</strong> for Order #1234</p>
                            <span>30 minutes ago</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <p><strong>System Update</strong> scheduled for tonight</p>
                            <span>1 hour ago</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <p><strong>New Message</strong> from Sandi Waluyo</p>
                            <span>2 hours ago</span>
                        </a>
                    </li>
                </ul>
                <a href="#" class="view-all">View All Notifications</a>
            </div>
            
            <div class="profile" onclick="toggleDropdown()">
            <img src="../Admin-HTML/images/FOTO Amelia Waruwu.jpg" alt="Admin Profile">
            <div class="profile-dropdown" id="profileDropdown">
                <ul>
                    <li><a href="#" onclick="toggleEditProfileMenu(event)">Edit Profile</a></li>
                    <li><a href="home.php">Home</a></li>
                    <li><a href="settings.php">Settings</a></li>
                    <li><a href="login.php" class="logout">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <script src="../Admin-HTML/js/scriptadmin.js"></script>
    <script src="../Admin-HTML/js/script1.js"></script>
