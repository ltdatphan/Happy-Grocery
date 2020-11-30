<?php

/** @var mysqli $conn */

function isValidProduct($link, $product_id)
{
    return mysqli_query($link, "SELECT * FROM Products WHERE id=$product_id");
}

$action = $_GET['action'] ?? null;

if (isset($_POST['id']) && !isValidProduct($conn, (int)$_POST['id'])) {
    echo "<h2>INVALID PRODUCT ID</h2>";
    exit;
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

switch ($action) {
    case 'add':
        $product_id = (int)$_POST['id'];
        $quantity = (int)$_POST['quantity'];
        if (array_key_exists($product_id, $_SESSION['cart'])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
        break;
    case 'remove':

        $product_id = (int)$_POST['id'];
        if (array_key_exists($product_id, $_SESSION['cart'])) {
            unset($_SESSION['cart'][$product_id]);
        }
        break;
    case 'set':
        $product_id = (int)$_POST['id'];
        $quantity = (int)$_POST['quantity'];
        $_SESSION['cart'][$product_id] = $quantity;
        break;
    case 'clear':
        $_SESSION['cart'] = [];
        break;
}

// Clear products in cart with quantity less than 1
$_SESSION['cart'] = array_filter($_SESSION['cart'], function ($var) {
    return $var > 0;
});

if (!empty($_SESSION['cart'])) {
    $cart_ids_string = implode(',', $cart_ids = array_keys($_SESSION['cart']));

    $result = mysqli_query($conn, "SELECT * FROM Products WHERE id in ($cart_ids_string) ORDER BY prod_name");
}

if (!is_null($action)) {
    header('content-type: text/plain;charset=utf8');
    $url_info = parse_url($_SERVER["REQUEST_URI"]);
    
    $url_query = isset($url_info['query']) ? $url_info['query'] : '';

    parse_str($url_query, $query_info);
    unset($query_info['action']);
    $new_url = $urlinfo['path'] . '?' . http_build_query($query_info);
    header("Location: " . $new_url, TRUE, 303);
}
