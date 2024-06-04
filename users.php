<?php
include 'db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_count_query = "SELECT COUNT(*) as count FROM cart WHERE user_id = $user_id";
    $cart_count_result = $conn->query($cart_count_query);

    if ($cart_count_result->num_rows > 0) {
        $row = $cart_count_result->fetch_assoc();
        $cart_count = $row['count'];
    }
}

if (!isset($_SESSION['user_id'])) {
    header("Location: /");
    exit;
}

$user_id = $_SESSION['user_id'];

$check_admin_query = "SELECT role FROM users WHERE id = $user_id";
$check_admin_result = $conn->query($check_admin_query);

$is_admin = false;

if ($check_admin_result) {
    if ($check_admin_result->num_rows > 0) {
        $row = $check_admin_result->fetch_assoc();
        $user_role = $row['role'];
        $is_admin = ($user_role === 'admin');
    } else {
        echo "No rows returned for the user ID: $user_id<br>";
    }
} else {
    echo "Error executing the query: " . $conn->error . "<br>";
}

if (!$is_admin) {
    echo "You don't have permission to access this page.";
    exit;
}

$cart_count = 0;
$cart_count_query = "SELECT COUNT(*) as count FROM cart WHERE user_id = ?";
$cart_count_stmt = $conn->prepare($cart_count_query);
$cart_count_stmt->bind_param("i", $user_id);
$cart_count_stmt->execute();
$cart_count_result = $cart_count_stmt->get_result();

if ($cart_count_result->num_rows > 0) {
    $row = $cart_count_result->fetch_assoc();
    $cart_count = $row['count'];
}

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_query = "SELECT username, profile_picture FROM users WHERE id = $user_id";
    $user_result = $conn->query($user_query);

    if ($user_result && $user_result->num_rows > 0) {
        $user_data = $user_result->fetch_assoc();
        $username = $user_data['username'];
        $profile_picture = $user_data['profile_picture'];
        if (empty($profile_picture)) {
            $default_avatar = 'https://upload.wikimedia.org/wikipedia/commons/a/ac/Default_pfp.jpg';
            $profile_picture = $default_avatar;
        }
    } else {
        $default_avatar = 'https://upload.wikimedia.org/wikipedia/commons/a/ac/Default_pfp.jpg';
        $profile_picture = $default_avatar;
    }
}
if (isset($_SESSION['dark_mode'])) {
    $darkMode = $_SESSION['dark_mode'];
} else {
    $darkMode = false;
}

function getTotalCartQuantity() {
    global $conn;

    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT SUM(quantity) AS totalQuantity FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['totalQuantity'];
        }
    }
    return 0;
}

$totalCartQuantity = getTotalCartQuantity();

if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $delete_query = "DELETE FROM users WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $delete_id);

    if ($delete_stmt->execute()) {
        header("Location: users.php");
        exit;
    } else {
        echo "Error deleting user: " . $conn->error;
    }
}

$limit = 12;
if (isset($_GET["page"])) { 
    $page  = $_GET["page"]; 
} else { 
    $page=1; 
};  

$start_from = ($page-1) * $limit;  

$user_query = "SELECT * FROM users LIMIT $start_from, $limit";
$user_result = $conn->query($user_query);

$total_query = "SELECT COUNT(*) FROM users";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_row();
$total_records = $total_row[0];
$total_pages = ceil($total_records / $limit);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-<correct_hash_here>" crossorigin="anonymous">
    <title>User Management</title>
</head>
<body>
    <div class="wrapper">
    <div class="header">
    <div class="header-items">
        <div class="main-nav-items">
            <div class="title">
                <a href="/">
                    <img src="uploads/citystore-logo.png" alt="Logo" class="logo">
                </a>
                <a href="javascript:void(0);" class="icon" onclick="myFunction()">
                    <i class="fa-solid fa-bars"></i>
                </a>
            </div>
            <div class="title-search">
                <div class="nav-buttons" id="myLinks">
                    <?php if (isset($is_admin) && $is_admin): ?>
                        <div class="admin-panel">
                            <a href="dashboard.php" class="btn admin-btn">Admin Panel</a>
                            <a href="users.php" class="btn admin-btn">Users</a>
                        </div>
                    <?php endif; ?>
                    <a href="/" class="btn">Home</a>
                    <a href="about.php" class="btn">About</a>
                    <a href="contacts.php" class="btn">Contacts</a>
                    <a href="shopping_cart.php" class="btn">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if(isset($_SESSION['user_id']) && $totalCartQuantity > 0): ?>
                            <span id="cartCount"><?php echo $totalCartQuantity; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="other-nav-items">
            <div class="nav-buttons" id="mySearch">
                <button id="toggle" class="btn" onclick="toggleMoonOutline()"><i class="fas fa-moon"></i></button>
                <?php if (isset($_SESSION["user_id"])): ?>
                <div class="avatar-container">
                    <a href="users_profile.php">
                        <img src="<?php echo $profile_picture; ?>" class="avatar">
                    </a>

                </div>
            <?php endif; ?>
                <div class="logout">
            

            <?php
            if (isset($_SESSION["user_id"])) {
                echo "<a href='logout.php' class='logout-btn'><i class='fa-solid fa-right-from-bracket'></i> Sign Out</a>";
            } else {
                echo "<a href='#' class='logout-btn' onclick='openLoginModal()'><i class='fas fa-sign-in-alt'></i> Sign In</a>";
            }
            ?>
            </div>
            </div>
        </div>
    </div>
</div>

<div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeLoginModal()">&times;</span>
            <div id="loginContent" class="form">
            </div>
        </div>
    </div>
    <script>
    function openLoginModal() {
        const modal = document.getElementById("loginModal");
        modal.style.display = "block";
        document.body.style.overflow = 'hidden';

        loadLoginFormContent();
    }

    function closeLoginModal() {
        const modal = document.getElementById("loginModal");
        modal.style.display = "none";
        document.body.style.overflow = '';
    }

    function loadLoginFormContent() {
        const loginContent = document.getElementById("loginContent");
        const xhr = new XMLHttpRequest();
        xhr.open("GET", "login.php", true);
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        loginContent.innerHTML = response.content;
                        const existingErrorMessage = loginContent.querySelector('.error');
                        if (!existingErrorMessage) {
                            const errorMessage = document.createElement('p');
                            errorMessage.className = 'error';
                            errorMessage.textContent = response.error;
                            loginContent.insertBefore(errorMessage, loginContent.firstChild);
                        } else {
                            existingErrorMessage.textContent = response.error;
                            existingErrorMessage.classList.add('shake');
                            setTimeout(function() {
                                existingErrorMessage.classList.remove('shake');
                            }, 1000);
                        }
                    } else {
                        loginContent.innerHTML = response.content;
                        loginContent.querySelector('form').addEventListener('submit', function (event) {
                            event.preventDefault();
                            const formData = new FormData(this);
                            const xhr = new XMLHttpRequest();
                            xhr.open("POST", "login.php", true);
                            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                            xhr.onreadystatechange = function () {
                                if (xhr.readyState === 4 && xhr.status === 200) {
                                    const response = JSON.parse(xhr.responseText);
                                    if (response.error) {
                                        const existingErrorMessage = loginContent.querySelector('.error');
                                        if (!existingErrorMessage) {
                                            const errorMessage = document.createElement('p');
                                            errorMessage.className = 'error';
                                            errorMessage.textContent = response.error;
                                            loginContent.insertBefore(errorMessage, loginContent.firstChild);
                                        } else {
                                            existingErrorMessage.textContent = response.error;
                                            existingErrorMessage.classList.add('shake');
                                            setTimeout(function() {
                                                existingErrorMessage.classList.remove('shake');
                                            }, 1000);
                                        }
                                    } else if (response.redirect) {
                                        window.location.href = response.redirect;
                                    } else {
                                        loginContent.innerHTML = response.content;
                                    }
                                }
                            };
                            xhr.send(formData);
                        });
                    }
                } else if (xhr.status === 403) {
                    const errorMessage = document.createElement('p');
                    errorMessage.className = 'error';
                    errorMessage.textContent = 'Access forbidden';
                    loginContent.insertBefore(errorMessage, loginContent.firstChild);
                } else {
                    const errorMessage = document.createElement('p');
                    errorMessage.className = 'error';
                    errorMessage.textContent = 'An error occurred while processing your request';
                    loginContent.insertBefore(errorMessage, loginContent.firstChild);
                }
            }
        };
        xhr.send();
    }

    function openRegisterModal() {
        const modal = document.getElementById("loginModal");
        modal.style.display = "block";
        loadRegisterFormContent();
    }

    function loadRegisterFormContent() {
        const registerContent = document.getElementById("loginContent");
        const xhr = new XMLHttpRequest();
        xhr.open("GET", "register.php", true);
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        registerContent.innerHTML = response.content;
                        const existingErrorMessage = registerContent.querySelector('.error');
                        if (!existingErrorMessage) {
                            const errorMessage = document.createElement('span');
                            errorMessage.className = 'error';
                            errorMessage.textContent = response.error;
                            registerContent.insertBefore(errorMessage, registerContent.firstChild);
                        } else {
                            existingErrorMessage.textContent = response.error;
                            existingErrorMessage.classList.add('shake');
                            setTimeout(function() {
                                existingErrorMessage.classList.remove('shake');
                            }, 1000);
                        }
                    } else if (response.success) {
                        alert(response.success);
                        closeLoginModal();
                    } else {
                        registerContent.innerHTML = response.content;
                        registerContent.querySelector('form').addEventListener('submit', function (event) {
                            event.preventDefault();
                            const formData = new FormData(this);
                            const xhr = new XMLHttpRequest();
                            xhr.open("POST", "register.php", true);
                            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                            xhr.onreadystatechange = function () {
                                if (xhr.readyState === 4 && xhr.status === 200) {
                                    const response = JSON.parse(xhr.responseText);
                                    if (response.error) {
                                        const existingErrorMessage = registerContent.querySelector('.error');
                                        if (!existingErrorMessage) {
                                            const errorMessage = document.createElement('span');
                                            errorMessage.className = 'error';
                                            errorMessage.textContent = response.error;
                                            registerContent.insertBefore(errorMessage, registerContent.firstChild);
                                        } else {
                                            existingErrorMessage.textContent = response.error;
                                            existingErrorMessage.classList.add('shake');
                                            setTimeout(function() {
                                                existingErrorMessage.classList.remove('shake');
                                            }, 1000);
                                        }
                                    } else if (response.success) {
                                        alert(response.success);
                                        closeLoginModal();
                                    } else {
                                        registerContent.innerHTML = response.content;
                                    }
                                }
                            };
                            xhr.send(formData);
                        });
                    }
                } else if (xhr.status === 403) {
                    const errorMessage = document.createElement('span');
                    errorMessage.className = 'error';
                    errorMessage.textContent = 'Access forbidden. Please try again.';
                    registerContent.innerHTML = '';
                    registerContent.appendChild(errorMessage);
                }
            }
        };
        xhr.send();
    }

function openPasswordResetModal() {
        const modal = document.getElementById("loginModal");
        modal.style.display = "block";

        loadPasswordResetFormContent();
    }

    function loadPasswordResetFormContent() {
    const resetContent = document.getElementById("loginContent");
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "password_reset.php", true);
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.error) {
                    resetContent.innerHTML = response.content;
                    const existingErrorMessage = resetContent.querySelector('.error');
                    if (!existingErrorMessage) {
                        const errorMessage = document.createElement('span');
                        errorMessage.className = 'error';
                        errorMessage.textContent = response.error;
                        resetContent.insertBefore(errorMessage, resetContent.firstChild);
                    } else {
                        existingErrorMessage.textContent = response.error;
                        existingErrorMessage.classList.add('shake');
                        setTimeout(function() {
                            existingErrorMessage.classList.remove('shake');
                        }, 1000);
                    }
                } else {
                    resetContent.innerHTML = response.content;
                    resetContent.querySelector('form').addEventListener('submit', function (event) {
                        event.preventDefault();
                        const formData = new FormData(this);
                        const xhr = new XMLHttpRequest();
                        xhr.open("POST", "password_reset.php", true);
                        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                        xhr.onreadystatechange = function () {
                            if (xhr.readyState === 4 && xhr.status === 200) {
                                const response = JSON.parse(xhr.responseText);
                                if (response.error) {
                                    const existingErrorMessage = resetContent.querySelector('.error');
                                    if (!existingErrorMessage) {
                                        const errorMessage = document.createElement('span');
                                        errorMessage.className = 'error';
                                        errorMessage.textContent = response.error;
                                        resetContent.insertBefore(errorMessage, resetContent.firstChild);
                                    } else {
                                        existingErrorMessage.textContent = response.error;
                                        existingErrorMessage.classList.add('shake');
                                        setTimeout(function() {
                                            existingErrorMessage.classList.remove('shake');
                                        }, 1000);
                                    }
                                } else if (response.success_message) {
                                    const existingErrorMessage = resetContent.querySelector('.error');
                                    if (existingErrorMessage) {
                                        existingErrorMessage.remove();
                                    }
                                    const successMessage = document.createElement('span');
                                    successMessage.className = 'success-message';
                                    successMessage.textContent = response.success_message;
                                    resetContent.insertBefore(successMessage, resetContent.firstChild);
                                    resetContent.querySelector('form').remove();
                                    setTimeout(function() {
                                        openLoginModal();
                                    }, 8000);
                                } else {
                                    resetContent.innerHTML = response.content;
                                }
                            }
                        };
                        xhr.send(formData);
                    });
                }
            } else if (xhr.status === 403) {
                const errorMessage = document.createElement('span');
                errorMessage.className = 'error';
                errorMessage.textContent = 'Access forbidden';
                resetContent.insertBefore(errorMessage, resetContent.firstChild);
            } else {
                const errorMessage = document.createElement('span');
                errorMessage.className = 'error';
                errorMessage.textContent = 'An error occurred while processing your request';
                resetContent.insertBefore(errorMessage, resetContent.firstChild);
            }
        }
    };
    xhr.send();
}
</script>
<?php if(isset($_SESSION['registration_success'])): ?>
    <script>
        alert("<?php echo $_SESSION['registration_success']; ?>");
        <?php unset($_SESSION['registration_success']); ?>
    </script>
<?php endif; ?>
<script>
    const root = document.documentElement;
const toggle = document.getElementById("toggle");
const darkMode = <?php echo json_encode($darkMode); ?>;

if (darkMode) {
  root.classList.add("dark-theme");
}

toggle.addEventListener("click", () => {
  root.classList.toggle("dark-theme");
  const isDarkMode = root.classList.contains("dark-theme");

  const xhr = new XMLHttpRequest();
  xhr.open("GET", "update_dark_mode.php?darkMode=" + isDarkMode, true);
  xhr.send();
});
    function toggleMoonOutline() {
        var toggleButton = document.getElementById('toggle');
        toggleButton.classList.toggle('clicked');
    }
</script>
<script>
    function myFunction() {
        var x = document.getElementById("myLinks");
        var y = document.getElementById("mySearch");
        var z = document.getElementById("myLinks2");

        if (x.style.display === "flex") {
            x.style.display = "none";
        } else {
            x.style.display = "flex";
        }

        if (y.style.display === "flex") {
            y.style.display = "none";
        } else {
            y.style.display = "flex";
        }

        if (z.style.display === "flex") {
            z.style.display = "none";
        } else {
            z.style.display = "flex";
        }
    }
</script>
<div class="container">
    <div class="main-page-wrapper">
        <h1>Users</h1>
        <div class="user-cards">
            <?php if ($user_result->num_rows > 0): ?>
                <?php while ($user = $user_result->fetch_assoc()): ?>
                    <div class="user-card">
                        <img src="<?php echo $user['profile_picture']; ?>" alt="Profile Picture" class="profile-pic">
                        <div class="user-info">
                            <h2><?php echo $user['username']; ?></h2>
                            <p>Email: <?php echo $user['email']; ?></p>
                            <p>Role: <?php echo $user['role']; ?></p>
                        </div>
                        <div class="user-actions">
                            <a href="users.php?delete=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');" class="action-btn delete-btn"><i class="fas fa-trash-alt"></i></a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No users found.</p>
            <?php endif; ?>
        </div>
        <div class="pagination">
            <?php if ($total_pages > 1): ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="users.php?page=<?php echo $i; ?>" class="<?php echo ($page == $i) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
    </div>
    </div>
    <footer>
        <?php include 'footer.php'; ?>
    </footer>
</div>
    
</body>
</html>