<?php
include '../views/headeradmin.php';

?>




<!-- MAIN -->
<link rel="stylesheet" href="../Admin-HTML/css/admin.css">
 <main>
            <div class="head-title">
                <div class="left">
                    <h1>Settings</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Settings</a></li>
                    </ul>
                </div>
            </div>

            <div class="settings-container">
                <div class="settings-section">
                    <h3>Profile Settings</h3>
                    <form class="settings-form">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" value="admin" />
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" value="admin@gomaggot.com" />
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" value="********" />
                        </div>
                        <button type="submit" class="btn-save">Save Changes</button>
                    </form>
                </div>

                <div class="settings-section">
                    <h3>Notification Settings</h3>
                    <div class="notification-settings">
                        <div class="setting-item">
                            <span>Email Notifications</span>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="setting-item">
                            <span>Push Notifications</span>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </main>

<?php
include '../views/footeradmin.php';
?>