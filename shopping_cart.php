<?php
include "update_last_seen.php";
include 'db.php';

function updateCartTotal() {
    global $conn;

    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT COALESCE(SUM(products.price * cart.quantity), 0) AS totalSum 
                FROM cart 
                LEFT JOIN products ON cart.product_id = products.product_id 
                WHERE cart.user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
    
        if ($stmt->execute()) {
            $result = $stmt->get_result();
    
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $totalSum = $row['totalSum'];
                echo "Total: EUR " . number_format($totalSum, 2);
            } else {
                echo "Total: EUR 0";
            }
        } else {
            echo "Error fetching cart total: " . $stmt->error;
        }
    
        $stmt->close();
    }
}

$cart_count = 0;

if (isset($_GET['remove_id'])) {
    $cart_id = $_GET['remove_id'];

    $delete_query = "DELETE FROM cart WHERE cart_id = $cart_id";
    if ($conn->query($delete_query) === TRUE) {
        updateCartTotal();
        header("Location: shopping_cart.php");
        exit;
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

if (isset($_SESSION['user_id'])):
    $user_id = $_SESSION['user_id'];
    $cart_count_query = "SELECT COUNT(*) as count FROM cart WHERE user_id = $user_id";
    $cart_count_result = $conn->query($cart_count_query);

    if ($cart_count_result->num_rows > 0):
        $row = $cart_count_result->fetch_assoc();
        $cart_count = $row['count'];
    endif;

    $user_query = "SELECT role FROM users WHERE id = $user_id";
    $user_result = $conn->query($user_query);

    if ($user_result && $user_result->num_rows > 0):
        $user_data = $user_result->fetch_assoc();
        $user_role = $user_data['role'];
        if ($user_role === 'admin'):
            $is_admin = true;
        endif;
    endif;
endif;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-<correct_hash_here>" crossorigin="anonymous">
    <title>Shopping Cart - CityStore</title>
</head>

<script>
    function openLoginModal() {
    }
    function updateQuantity(cartId, change) {
    const quantityElement = document.getElementById(`quantity_${cartId}`);
    let quantity = parseInt(quantityElement.textContent);

    if (quantity + change >= 1) {
      quantity += change;
      quantityElement.textContent = quantity;

      const xhr = new XMLHttpRequest();
      xhr.open('GET', `update_quantity.php?cart_id=${cartId}&quantity=${quantity}`, true);
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
          updateCartCount();
          updateCartTotal();
        }
      };
      xhr.send();
    }
  }

  function updateCartCount() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_cart_count.php', true);
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4 && xhr.status === 200) {
        const cartCountElement = document.getElementById('cartCount');
        if (cartCountElement) {
          cartCountElement.textContent = xhr.responseText;
        }
      }
    };
    xhr.send();
  }

  function updateQuantityDisplay(cartId, quantity, updatedTotal, itemPrice) {
    const quantityElement = document.getElementById(`quantity_${cartId}`);
    if (quantityElement) {
        quantityElement.textContent = quantity;
    }

    const priceElement = document.getElementById(`price_${cartId}`);
    if (priceElement) {
        priceElement.textContent = 'EUR ' + updatedTotal.toFixed(2);
    }

    updateCartCount();

    const oldTotalElement = document.getElementById('totalSum');
    if (oldTotalElement) {
        const currentTotal = parseFloat(oldTotalElement.textContent.split(' ')[1].replace(',', ''));
        const difference = updatedTotal - (itemPrice * quantity);
        const newTotal = currentTotal + difference;
        oldTotalElement.textContent = 'Total: EUR ' + newTotal.toFixed(2);
    }
}

  function updateCartTotal() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'update_cart_total.php', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                const totalSumElement = document.getElementById('totalSum');
                if (totalSumElement) {
                    totalSumElement.innerHTML = xhr.responseText;
                }
            } else {
                console.error('Error updating cart total: ' + xhr.statusText);
            }
        }
    };
    xhr.send();
}
</script>

<body>
    <div class="wrapper">
            <?php include 'header.php'; ?>
        <div class="container">
            <div class="main-page-wrapper">
                <div class="cart-details" style="text-align: center;">
                    <h2>Shopping Cart</h2>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php
                        $totalSum = 0;
                        $cartCount = 0;

                        $user_id = $_SESSION['user_id'];
$sql = "SELECT cart.*, products.name, products.price, products.image_url
        FROM cart 
        INNER JOIN products ON cart.product_id = products.product_id
        WHERE cart.user_id = $user_id
        ORDER BY cart.cart_id DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0):
    while ($row = $result->fetch_assoc()):
        $itemImage = $row['image_url'];
        $itemName = $row['name'];
        $itemPrice = $row['price'];
        $quantity = $row['quantity'];
        $cartId = $row['cart_id'];
        $update_status_query = "UPDATE cart SET status = 'In cart' WHERE cart_id = $cartId";
        $conn->query($update_status_query);
                                echo "<script>updateQuantityDisplay($cartId, $quantity, $itemPrice * $quantity);</script>";
                                $cartCount += $quantity;
                                echo '<div class="cart-item">';
                                if (!empty($row['product_id'])):
                                    echo '<a href="product.php?id=' . $row['product_id'] . '">';
                                    echo '<img src="' . $itemImage . '" alt="Item Image">';
                                    echo '</a>';
                                else:
                                    echo '<span>Product ID not available</span>';
                                endif;
                                echo '<div class="cart-item-details">';
                                echo '<div class="item-name-price">';
                                echo '<h3>' . $itemName . '</h3>';
                                echo '<p class="cart-item-price" id="price_' . $row['cart_id'] . '" data-unit-price="' . $itemPrice . '">EUR ' . number_format($itemPrice, 2) . '</p>';
                                echo '</div>';
                                echo '<div class="cart-buttons">';
                                echo '<div class="quantity-controls">';
                                echo '<button class="quantity-btn-minus" onclick="updateQuantity(' . $row['cart_id'] . ', -1)">-</button>';
                                echo '<span class="quantity" id="quantity_' . $row['cart_id'] . '">' . $row['quantity'] . '</span>';
                                echo '<button class="quantity-btn" onclick="updateQuantity(' . $row['cart_id'] . ', 1)">+</button>';
                                echo '</div>';
                                echo '<div class="remove-button">';
                                echo '<a href="shopping_cart.php?remove_id=' . $row['cart_id'] . '" class="remove-btn">';
                                echo '<i class="fas fa-trash-alt" ></i>';
                                echo '</a>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';

                                $totalSum += ($itemPrice * $quantity);
                            endwhile;
                            echo '<p class="p-company" id="totalSum">Total: EUR ' . number_format($totalSum, 2) . '</p>';
                        else:
                            echo "<p>No items in the cart.</p>";
                        endif;
                    else: ?>
                        <p style="text-transform: uppercase; color:red; font-weight:bold; letter-spacing: 0.8px;">Please log in to view the cart.</p>
                        <a href="#" onclick="openLoginModal()" style="color:var(--accent-color); font-weight:bold; text-decoration:underline;">SIGN IN</a>
                    <?php endif; ?>
                </div>
                <?php if (isset($_SESSION['user_id']) && $cart_count > 0 && $totalSum >= 0): ?>
                    <div class="checkout-button">
                        <a href='cart-checkout.php'><input href='cart-checkout.php' type="submit" value="Proceed to checkout"></a>
                    </div>
                <?php endif; ?>
        </div>
    </div>
    <?php
    $conn->close();
    ?>
    <footer>
        <?php include 'footer.php'; ?>
    </footer>
</div>
</body>

</html>
