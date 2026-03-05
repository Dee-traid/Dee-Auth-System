session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html>
<body>
    <script>
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user_name');
        window.location.href = "/Dee-Auth-System/views/index.php"; 
    </script>
</body>
</html>