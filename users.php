<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

$is_admin = false;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $check_admin_query = "SELECT role FROM users WHERE id = ?";
    $check_admin_stmt = $conn->prepare($check_admin_query);
    $check_admin_stmt->bind_param("i", $user_id);
    $check_admin_stmt->execute();
    $check_admin_result = $check_admin_stmt->get_result();

    if ($check_admin_result->num_rows > 0) {
        $row = $check_admin_result->fetch_assoc();
        $is_admin = ($row['role'] === 'admin');
    }
}

$searchQuery = '';
$roleFilter = '';

if (isset($_GET['search_query']) || isset($_GET['role'])) {
    $searchQuery = isset($_GET['search_query']) ? $_GET['search_query'] : '';
    $roleFilter = isset($_GET['role']) ? $_GET['role'] : '';

    $sql = "SELECT * FROM users WHERE (username LIKE ? OR email LIKE ?)";
    if ($roleFilter) {
        $sql .= " AND role = ?";
        $stmt = $conn->prepare($sql);
        $searchParam = "%" . $searchQuery . "%";
        $stmt->bind_param("sss", $searchParam, $searchParam, $roleFilter);
    } else {
        $stmt = $conn->prepare($sql);
        $searchParam = "%" . $searchQuery . "%";
        $stmt->bind_param("ss", $searchParam, $searchParam);
    }
    $stmt->execute();
    $user_result = $stmt->get_result();
} else {
    $sql = "SELECT * FROM users";
    $user_result = $conn->query($sql);
}

// Check if the user is an admin
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
    $delete_query = "SELECT role FROM users WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $delete_id);
    $delete_stmt->execute();
    $delete_result = $delete_stmt->get_result();

    if ($delete_result->num_rows > 0) {
        $delete_user = $delete_result->fetch_assoc();
        if ($delete_user['role'] !== 'admin') {
            $delete_query = "DELETE FROM users WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_query);
            $delete_stmt->bind_param("i", $delete_id);

            if ($delete_stmt->execute()) {
                header("Location: users.php");
                exit;
            } else {
                echo "Error deleting user: " . $conn->error;
            }
        } else {
            echo "Cannot delete an admin user.";
        }
    } else {
        echo "User not found.";
    }
}

$sql = "SELECT * FROM users WHERE 1=1";
$params = [];
$types = '';

if (isset($_GET['search_query'])) {
    $searchQuery = $_GET['search_query'];
    $sql .= " AND (username LIKE ? OR email LIKE ?)";
    $params[] = '%' . $searchQuery . '%';
    $params[] = '%' . $searchQuery . '%';
    $types .= 'ss';
}

if (isset($_GET['role']) && $_GET['role'] !== '') {
    $roleFilter = $_GET['role'];
    $sql .= " AND role = ?";
    $params[] = $roleFilter;
    $types .= 's';
}

$sql .= " LIMIT ?, ?";
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$start_from = ($page - 1) * $limit;
$params[] = $start_from;
$params[] = $limit;
$types .= 'ii';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$user_result = $stmt->get_result();

$total_query = "SELECT COUNT(*) FROM users WHERE 1=1";
if ($searchQuery) {
    $total_query .= " AND (username LIKE '%$searchQuery%' OR email LIKE '%$searchQuery%')";
}
if ($roleFilter) {
    $total_query .= " AND role = '$roleFilter'";
}
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
                <div class="search-categories">
                    <div class="category-dropdown">
                        <select id="role-dropdown" onchange="window.location.href='users.php?role=' + this.value">
                            <option value="" <?php if (!$roleFilter) echo 'selected'; ?>>All Users</option>
                            <option value="user" <?php if ($roleFilter == 'user') echo 'selected'; ?>>Users</option>
                            <option value="admin" <?php if ($roleFilter == 'admin') echo 'selected'; ?>>Admins</option>
                        </select>
                        <i class="fa-solid fa-caret-down"></i>
                    </div>
                    <div class="search-container">
                        <form method="GET" action="users.php" class="search-form">
                            <div class="search-wrapper">
                                <input name="search_query" placeholder="Search for users..." class="search-input" value="<?php echo htmlspecialchars($searchQuery); ?>">
                                <button name="search" class="search-btn"><i class="fas fa-search"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="products">
                    <?php if ($user_result->num_rows > 0): ?>
                        <?php while ($user = $user_result->fetch_assoc()): ?>
                            <div class='product-card' data-user-id='<?php echo $user['id']; ?>'>
                                <div class='admin-buttons'>
                                    <?php if ($user['role'] !== 'admin'): ?>
                                        <form id='delete_form_<?php echo $user['id']; ?>' method='POST' action='' style='display:inline-block;'>
                                            <input type='hidden' name='delete_user_id' value='<?php echo $user['id']; ?>'>
                                            <a href='#' class='remove-btn' onclick='submitForm(<?php echo $user['id']; ?>)'><i class='fas fa-trash-alt'></i></a>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                <a href='#'>
                                    <div class='product-card-top' style="width: auto; height: 80%; object-fit: cover; margin: auto; display: flex; width: 80%; height: 80%; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                        <img class='product-card-img' style="box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px; height: 80%; width: auto; max-width: 100%; margin: auto; width: 80%; height: 70%; object-fit: cover; border-radius: 50%;" src='<?php echo $user['profile_picture']; ?>' alt='Profile Picture'>
                                    </div>
                                    <div class='box-down'>
                                        <div class='card-footer'>
                                            <div class='img-info'>
                                                <span class='p-name'><?php echo $user['username']; ?></span>
                                                <span class='p-company'><?php echo $user['email']; ?></span>
                                            </div>
                                            <div class='img-role <?php echo ($user['role'] === 'admin') ? 'admin-highlight' : ''; ?>'>
                                                <span><?php echo $user['role']; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
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
            <?php include 'footer.php'; ?>
    </div>
<div id="myModal" class="modal">
  <div class="modal-content">
  <span class="close" onclick="closeModal()">&times;</span>
    <div id="modal-body"></div>
  </div>
</div>
<script>
    function submitForm(userId) {
        if(confirm('Are you sure you want to delete this user?')) {
            document.getElementById('delete_form_' + userId).submit();
        }
    }
</script>
<script>
// Get the modal
var modal = document.getElementById("myModal");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// When the user clicks on a user card, open the modal
document.querySelectorAll('.product-card').forEach(item => {
  item.addEventListener('click', event => {
    var userId = item.dataset.userId;
    fetchUserOrders(userId);
    modal.style.display = "block";
  });
});


function closeModal() {
    document.getElementById('myModal').style.display = 'none';
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}

// Fetch user orders and populate the modal body
function fetchUserOrders(userId) {
  fetch('get_user_orders.php?user_id=' + userId)
    .then(response => response.text())
    .then(data => {
      document.getElementById('modal-body').innerHTML = data;
    });
}
</script>
</body>
</html>