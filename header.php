<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

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
?>
<div class="header">
    <div class="header-items">
        <div class="main-nav-items">
            <div class="title">
                <a href="http://localhost/citystore/">
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
                        </div>
                    <?php endif; ?>
                    <a href="http://localhost/citystore/" class="btn">Home</a>
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
                <div class="logout">
            <?php if (isset($_SESSION["user_id"])): ?>
                <div class="avatar-container">
                    <a href="users_profile.php">
                        <img src="<?php echo $profile_picture; ?>" class="avatar">
                    </a>

                </div>
            <?php endif; ?>

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
                                } else if (response.redirect) {
                                    window.location.href = response.redirect;
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
                errorMessage.textContent = 'Access forbidden';
                registerContent.insertBefore(errorMessage, registerContent.firstChild);
            } else {
                const errorMessage = document.createElement('span');
                errorMessage.className = 'error';
                errorMessage.textContent = 'An error occurred while processing your request';
                registerContent.insertBefore(errorMessage, registerContent.firstChild);
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