<!DOCTYPE html>
<html lang="en">
<head>

    <script>
        const token = localStorage.getItem('auth_token');
        if (!token) {
            window.location.href = "../index.php";
        }
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Dee-Triad</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />
    <link rel="icon" type="image/png" href="../images/favicon.png">
</head>
<body class="dashboard-body">

  <div class="dashboard-card">
            <div class="dashboard-box">
                <h2 class="dashboard-header-text">Welcome, <span id="display-name">User</span>!</h2>
                
                <div class="dashboard-detail-row">
                    <span class="dashboard-label">Account Status</span>
                    <div class="dashboard-value">
                        <span class="dashboard-status-dot"></span> Online
                    </div>
                </div>

                <div class="dashboard-detail-row">
                    <span class="dashboard-label">User ID</span>
                    <div class="dashboard-value" id="display-id">
                        Loading...
                    </div>
                </div>

                <div class="dashboard-footer">
                    <button id="logout-btn" class="dashboard-button" style="border:none; cursor:pointer;">Logout</button>
                </div>
            </div>
        </div>
    </div>

<script>
        document.addEventListener("DOMContentLoaded", () => {
            const userName = localStorage.getItem('user_name');
            const userId = localStorage.getItem('user_id');
            if (userName) {
                document.getElementById('display-name').textContent = userName || 'User';
            }

            if (userId) {
                document.getElementById('display-id').textContent = userId || 'N/A';
            }

            document.getElementById('logout-btn').addEventListener('click', () => {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user_name');
                localStorage.removeItem('user_id');
                window.location.href = "/Dee-Auth-System/views/logout.php";
            });
            
        });
    </script>
</body>
</html>