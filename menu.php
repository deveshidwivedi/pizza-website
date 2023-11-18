<!-- BASE STRUCTURE !!! MUST BE INCLUDED IN EVERY PAGE  -->

<?php

include 'config.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
};

if (isset($_POST['register'])) {

    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_STRING);
    $pass = sha1($_POST['pass']);
    $pass = filter_var($pass, FILTER_SANITIZE_STRING);
    $cpass = sha1($_POST['cpass']);
    $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

    $select_user = $conn->prepare("SELECT * FROM `user` WHERE name = ? AND email = ?");
    $select_user->execute([$name, $email]);

    if ($select_user->rowCount() > 0) {
        $message[] = 'username or email already exists!';
    } else {
        if ($pass != $cpass) {
            $message[] = 'confirm password not matched!';
        } else {
            $insert_user = $conn->prepare("INSERT INTO `user`(name, email, password) VALUES(?,?,?)");
            $insert_user->execute([$name, $email, $cpass]);
            $message[] = 'registered successfully, login now please!';
        }
    }
}

if (isset($_POST['update_qty'])) {
    $cart_id = $_POST['cart_id'];
    $qty = $_POST['qty'];
    $qty = filter_var($qty, FILTER_SANITIZE_STRING);
    $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
    $update_qty->execute([$qty, $cart_id]);
    $message[] = 'cart quantity updated!';
}

if (isset($_GET['delete_cart_item'])) {
    $delete_cart_id = $_GET['delete_cart_item'];
    $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
    $delete_cart_item->execute([$delete_cart_id]);
    header('location:index.php');
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('location:index.php');
}

if (isset($_POST['add_to_cart'])) {

    if ($user_id == '') {
        $message[] = 'please login first!';
    } else {

        $pid = $_POST['pid'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $image = $_POST['image'];
        $qty = $_POST['qty'];
        $qty = filter_var($qty, FILTER_SANITIZE_STRING);

        $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND name = ?");
        $select_cart->execute([$user_id, $name]);

        if ($select_cart->rowCount() > 0) {
            $message[] = 'already added to cart';
        } else {
            $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
            $insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image]);
            $message[] = 'added to cart!';
        }
    }
}

if (isset($_POST['order'])) {

    if ($user_id == '') {
        $message[] = 'please login first!';
    } else {
        $name = $_POST['name'];
        $name = filter_var($name, FILTER_SANITIZE_STRING);
        $number = $_POST['number'];
        $number = filter_var($number, FILTER_SANITIZE_STRING);
        $address = 'flat no.' . $_POST['flat'] . ', ' . $_POST['street'] . ' - ' . $_POST['pin_code'];
        $address = filter_var($address, FILTER_SANITIZE_STRING);
        $method = $_POST['method'];
        $method = filter_var($method, FILTER_SANITIZE_STRING);
        $total_price = $_POST['total_price'];
        $total_products = $_POST['total_products'];

        $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
        $select_cart->execute([$user_id]);

        if ($select_cart->rowCount() > 0) {
            $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?)");
            $insert_order->execute([$user_id, $name, $number, $method, $address, $total_products, $total_price]);
            $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
            $delete_cart->execute([$user_id]);
            $message[] = 'order placed successfully!';
        } else {
            $message[] = 'your cart empty!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Responsive Pizza Shop Website Design</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/menu.css">

</head>

<body>

    <?php
    if (isset($message)) {
        foreach ($message as $message) {
            echo '
         <div class="message">
            <span>' . $message . '</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
        }
    }
    ?>

    <!-- header section starts  -->

    <header class="header">

        <section class="flex">

            <a href="#home" class="logo"><img class="logo_img" width="175px" src="project_images/wide_logo.png"></a>

            <nav class="navbar">
                <a href="#home">home</a>
                <a href="#about">about</a>
                <a href="#menu">menu</a>
                <a href="#order">order</a>
                <a href="#faq">faq</a>
            </nav>

            <div class="icons">
                <div id="menu-btn" class="fas fa-bars"></div>
                <div id="user-btn" class="fas fa-user"></div>
                <div id="order-btn" class="fas fa-box"></div>
                <?php
                $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
                $count_cart_items->execute([$user_id]);
                $total_cart_items = $count_cart_items->rowCount();
                ?>
                <div id="cart-btn" class="fas fa-shopping-cart"><span>(<?= $total_cart_items; ?>)</span></div>
            </div>

        </section>

    </header>


    <div class="user-account">

        <section>

            <div id="close-account"><span>close</span></div>

            <div class="user">
                <?php
                $select_user = $conn->prepare("SELECT * FROM `user` WHERE id = ?");
                $select_user->execute([$user_id]);
                if ($select_user->rowCount() > 0) {
                    while ($fetch_user = $select_user->fetch(PDO::FETCH_ASSOC)) {
                        echo '<p>welcome ! <span>' . $fetch_user['name'] . '</span></p>';
                        echo '<a href="index.php?logout" class="btn">logout</a>';
                    }
                } else {
                    echo '<p><span>you are not logged in now!</span></p>';
                }
                ?>
            </div>

            <div class="display-orders">
                <?php
                $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
                $select_cart->execute([$user_id]);
                if ($select_cart->rowCount() > 0) {
                    while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
                        echo '<p>' . $fetch_cart['name'] . ' <span>(' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ')</span></p>';
                    }
                } else {
                    echo '<p><span>your cart is empty!</span></p>';
                }
                ?>
            </div>

            <div class="flex">

                <form action="user_login.php" method="post">
                    <h3>login now</h3>
                    <input type="email" name="email" required class="box" placeholder="enter your email" maxlength="50">
                    <input type="password" name="pass" required class="box" placeholder="enter your password" maxlength="20">
                    <input type="submit" value="login now" name="login" class="btn">
                </form>

                <form action="" method="post">
                    <h3>register now</h3>
                    <input type="text" name="name" oninput="this.value = this.value.replace(/\s/g, '')" required class="box" placeholder="enter your username" maxlength="20">
                    <input type="email" name="email" required class="box" placeholder="enter your email" maxlength="50">
                    <input type="password" name="pass" required class="box" placeholder="enter your password" maxlength="20" oninput="this.value = this.value.replace(/\s/g, '')">
                    <input type="password" name="cpass" required class="box" placeholder="confirm your password" maxlength="20" oninput="this.value = this.value.replace(/\s/g, '')">
                    <input type="submit" value="register now" name="register" class="btn">
                </form>

            </div>

        </section>

    </div>

    <div class="my-orders">

        <section>

            <div id="close-orders"><span>close</span></div>

            <h3 class="title"> my orders </h3>

            <?php
            $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ?");
            $select_orders->execute([$user_id]);
            if ($select_orders->rowCount() > 0) {
                while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
            ?>
                    <div class="box">
                        <p> placed on : <span><?= $fetch_orders['placed_on']; ?></span> </p>
                        <p> name : <span><?= $fetch_orders['name']; ?></span> </p>
                        <p> number : <span><?= $fetch_orders['number']; ?></span> </p>
                        <p> address : <span><?= $fetch_orders['address']; ?></span> </p>
                        <p> payment method : <span><?= $fetch_orders['method']; ?></span> </p>
                        <p> total_orders : <span><?= $fetch_orders['total_products']; ?></span> </p>
                        <p> total price : <span>$<?= $fetch_orders['total_price']; ?>/-</span> </p>
                        <p> payment status : <span style="color:<?php if ($fetch_orders['payment_status'] == 'pending') {
                                                                    echo 'red';
                                                                } else {
                                                                    echo 'green';
                                                                }; ?>"><?= $fetch_orders['payment_status']; ?></span> </p>
                    </div>
            <?php
                }
            } else {
                echo '<p class="empty">nothing ordered yet!</p>';
            }
            ?>

        </section>

    </div>

    <div class="shopping-cart">

        <section>

            <div id="close-cart"><span>close</span></div>

            <?php
            $grand_total = 0;
            $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $select_cart->execute([$user_id]);
            if ($select_cart->rowCount() > 0) {
                while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
                    $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
                    $grand_total += $sub_total;
            ?>
                    <div class="box">
                        <a href="index.php?delete_cart_item=<?= $fetch_cart['id']; ?>" class="fas fa-times" onclick="return confirm('delete this cart item?');"></a>
                        <img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="">
                        <div class="content">
                            <p> <?= $fetch_cart['name']; ?> <span>(<?= $fetch_cart['price']; ?> x <?= $fetch_cart['quantity']; ?>)</span></p>
                            <form action="" method="post">
                                <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
                                <input type="number" name="qty" class="qty" min="1" max="99" value="<?= $fetch_cart['quantity']; ?>" onkeypress="if(this.value.length == 2) return false;">
                                <button type="submit" class="fas fa-edit" name="update_qty"></button>
                            </form>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo '<p class="empty"><span>your cart is empty!</span></p>';
            }
            ?>

            <div class="cart-total"> grand total : <span>$<?= $grand_total; ?>/-</span></div>

            <a href="#order" class="btn">order now</a>

        </section>

    </div>


    <!-- BASE STRUCTURE !!! MUST BE INCLUDED IN EVERY PAGE  -->



    <section id="menu" class="menu">
        <h1 class="heading">OUR MENU</h1>

        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid">

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="#pizzas">Pizzas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#sides">Sides</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#beverages">Beverages</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#desserts">Desserts</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </section>
    <section id="menu" class="menu">

        <div class="box-container">

            <?php
            $select_products = $conn->prepare("SELECT * FROM `products`");
            $select_products->execute();
            if ($select_products->rowCount() > 0) {
                while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
            ?>
                    <div class="box">
                        <img src="uploaded_img/<?= $fetch_products['image'] ?>" alt="">
                        <div class="name"><?= $fetch_products['name'] ?></div>
                        <form action="" method="post">
                            <input type="hidden" name="pid" value="<?= $fetch_products['id'] ?>">
                            <input type="hidden" name="name" value="<?= $fetch_products['name'] ?>">
                            <input type="hidden" name="price" value="<?= $fetch_products['regular_price'] ?>">
                            <input type="hidden" name="image" value="<?= $fetch_products['image'] ?>">

                            <!-- <div class="radio-container">
                  <div class="custom-radio">
                     <input type="radio" id="$fetch_products['id']-regular" name="$fetch_products['id']-size" checked="">
                     <label class="radio-label" for="$fetch_products['id']-regular">
                        <div class="radio-circle"></div>
                        <span class="radio-text">Regular ($<span><?= $fetch_products['regular_price']; ?></span>/-)</span>
                     </label>
                     <input type="radio" id="$fetch_products['id']-medium" name="$fetch_products['id']-size">
                     <label class="radio-label" for="$fetch_products['id']-medium">
                        <div class="radio-circle"></div>
                        <span class="radio-text">Medium ($<span><?= $fetch_products['medium_price']; ?></span>/-)</span>
                     </label>
                     <input type="radio" id="$fetch_products['id']-large" name="$fetch_products['id']-size">
                     <label class="radio-label" for="$fetch_products['id']-large">
                        <div class="radio-circle"></div>
                        <span class="radio-text">Large ($<span><?= $fetch_products['large_price']; ?></span>/-)</span>
                     </label>
                  </div>
               </div> -->
                            <select size="3" class="select-size" name="sizes">
                                <option value="regular">Regular ($<span><?= $fetch_products['regular_price']; ?></span>)</option>
                                <option value="medium">Medium ($<span><?= $fetch_products['medium_price']; ?></span>)</option>
                                <option value="large">Large ($<span><?= $fetch_products['large_price']; ?></span>) </option>
                            </select>

                            <div class="crust-toppings">
                                <select class="select-crust" data-value="Crust" name="Crust">
                                    <option value="nht">New Hand Tossed</option>
                                    <option value="wtc">100% Wheat Thin Crust (+$2)</option>
                                    <option value="nht">Cheese Burst (+$5)</option>
                                    <option value="fpp">Fresh Pan Pizza</option>
                                </select>
                            </div>
                            <span> Extra toppings cost $0.5</span>
                            <div class="crust-toppings">
                                <select class="select-toppings" data-value="toppings" name="toppings">
                                    <option value="f">NONE</option>
                                    <option value="t">Grilled Mushrooms</option>
                                    <option value="t">Onion</option>
                                    <option value="t">Crisp Capsicum</option>
                                    <option value="t">Fresh Tomatoes</option>
                                    <option value="t">Paneer</option>
                                    <option value="t">Jalepeno</option>
                                    <option value="t">Green and Black Olives</option>
                                </select>
                            </div>


                            <div class="button">
                                <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
                                <input type="submit" class="btn" name="add_to_cart" value="add to cart">
                            </div>
                        </form>
                    </div>
            <?php
                }
            } else {
                echo '<p class="empty">no products added yet!</p>';
            }
            ?>

        </div>

    </section>
    <section id="pizzas" class="menu">
        <h3 class="head2">Pizzas</h3>




        <div class="box-container">

            <div class="box">
                <div class="price">Rs. 320</div>
                <img src="project_images/pizza-1.jpg" alt="">
                <div class="name">Cheesy Delight</div>
                <form action="" method="post">
                    <input type="number" min="1" max="100" value="1" class="qty" name="qty">
                    <input type="submit" value="add to cart" name="add_to_cart" class="btn">
                </form>
            </div>

            <div class="box">
                <div class="price">Rs. 450</div>
                <img src="project_images/pizza-2.jpg" alt="">
                <div class="name">Pepperoni Perfection</div>
                <form action="" method="post">
                    <input type="number" min="1" max="100" value="1" class="qty" name="qty">
                    <input type="submit" value="add to cart" name="add_to_cart" class="btn">
                </form>
            </div>

            <div class="box">
                <div class="price">Rs. 380</div>
                <img src="project_images/pizza-3.jpg" alt="">
                <div class="name">Cheesy Tomato Symphony</div>
                <form action="" method="post">
                    <input type="number" min="1" max="100" value="1" class="qty" name="qty">
                    <input type="submit" value="add to cart" name="add_to_cart" class="btn">
                </form>
            </div>

            <div class="box">
                <div class="price">Rs. 420</div>
                <img src="project_images/pizza-4.jpg" alt="">
                <div class="name">Farmhouse</div>
                <form action="" method="post">
                    <input type="number" min="1" max="100" value="1" class="qty" name="qty">
                    <input type="submit" value="add to cart" name="add_to_cart" class="btn">
                </form>
            </div>

            <div class="box">
                <div class="price">Rs. 510</div>
                <img src="project_images/pizza-5.jpg" alt="">
                <div class="name">Mushroom Marvel</div>
                <form action="" method="post">
                    <input type="number" min="1" max="100" value="1" class="qty" name="qty">
                    <input type="submit" value="add to cart" name="add_to_cart" class="btn">
                </form>
            </div>

            <div class="box">
                <div class="price">Rs. 290</div>
                <img src="project_images/pizza-6.jpg" alt="">
                <div class="name">Margherita Classic</div>
                <form action="" method="post">
                    <input type="number" min="1" max="100" value="1" class="qty" name="qty">
                    <input type="submit" value="add to cart" name="add_to_cart" class="btn">
                </form>
            </div>

            <div class="box">
                <div class="price">Rs. 570</div>
                <img src="project_images/pizza-7.jpg" alt="">
                <div class="name">Hawaiian Retreat</div>
                <form action="" method="post">
                    <input type="number" min="1" max="100" value="1" class="qty" name="qty">
                    <input type="submit" value="add to cart" name="add_to_cart" class="btn">
                </form>
            </div>

            <div class="box">
                <div class="price">Rs. 430</div>
                <img src="project_images/pizza-8.jpg" alt="">
                <div class="name">Veggie Delight</div>
                <form action="" method="post">
                    <input type="number" min="1" max="100" value="1" class="qty" name="qty">
                    <input type="submit" value="add to cart" name="add_to_cart" class="btn">
                </form>
            </div>

            <div class="box">
                <div class="price">Rs. 450</div>
                <img src="project_images/pizza-9.jpg" alt="">
                <div class="name">Mediterranean Harvest</div>
                <form action="" method="post">
                    <input type="number" min="1" max="100" value="1" class="qty" name="qty">
                    <input type="submit" value="add to cart" name="add_to_cart" class="btn">
                </form>
            </div>

        </div>

    </section>
    <section id="sides" class="menu">
        <h3 class="head2">Sides</h3>
        <div class="box-container">

            <div class="box">
                <div class="price">Rs. 280</div>
                <img src="project_images/pasta1.jpg" alt="">
                <div class="name">Arrabiata Pasta</div>
                <form action="" method="post">
                    <input type="number" min="1" max="100" value="1" class="qty" name="qty">
                    <input type="submit" value="add to cart" name="add_to_cart" class="btn">
                </form>
            </div>

            <div class="box">
                <div class="price">Rs. 280</div>
                <img src="project_images/pasta2.jpg" alt="">
                <div class="name">Alfredo Pasta</div>
                <form action="" method="post">
                    <input type="number" min="1" max="100" value="1" class="qty" name="qty">
                    <input type="submit" value="add to cart" name="add_to_cart" class="btn">
                </form>
            </div>

            <div class="box">
                <div class="price">Rs. 250</div>
                <img src="project_images/pasta3.jpg" alt="">
                <div class="name">Pesto Pasta</div>
                <form action="" method="post">
                    <input type="number" min="1" max="100" value="1" class="qty" name="qty">
                    <input type="submit" value="add to cart" name="add_to_cart" class="btn">
                </form>
            </div>


            <div class="box">
                <div class="price">Rs. 150</div>
                <img src="project_images/fries.jpg" alt="">
                <div class="name">Potato Fries</div>
                <form action="" method="post">
                    <input type="number" min="1" max="100" value="1" class="qty" name="qty">
                    <input type="submit" value="add to cart" name="add_to_cart" class="btn">
                </form>
            </div>

            <div class="box">
                <div class="price">Rs. 200</div>
                <img src="project_images/nachos.jpg" alt="">
                <div class="name">Loaded Nachos</div>
                <form action="" method="post">
                    <input type="number" min="1" max="100" value="1" class="qty" name="qty">
                    <input type="submit" value="add to cart" name="add_to_cart" class="btn">
                </form>
            </div>

            <div class="box">
                <div class="price">Rs. 230</div>
                <img src="project_images/salad.jpg" alt="">
                <div class="name">Zucchini Salad</div>
                <form action="" method="post">
                    <input type="number" min="1" max="100" value="1" class="qty" name="qty">
                    <input type="submit" value="add to cart" name="add_to_cart" class="btn">
                </form>
            </div>

        </div>
    </section>
    <section id="beverages" class="menu">
        <h3 class="head2">Beverages</h3>

        <div class="box-container">

            <div class="box">
                <div class="price">Rs. 60</div>
                <img src="project_images/coke.jpg" alt="">
                <div class="name">Coca-Cola</div>
                <form action="" method="post">
                    <input type="number" min="1" max="100" value="1" class="qty" name="qty">
                    <input type="submit" value="add to cart" name="add_to_cart" class="btn">
                </form>
            </div>


            <div class="box">
                <div class="price">Rs. 60</div>
                <img src="project_images/fanta.jpg" alt="">
                <div class="name">Fanta</div>
                <form action="" method="post">
                    <input type="number" min="1" max="100" value="1" class="qty" name="qty">
                    <input type="submit" value="add to cart" name="add_to_cart" class="btn">
                </form>
            </div>


            <div class="box">
                <div class="price">Rs. 60</div>
                <img src="project_images/sprite.jpg" alt="">
                <div class="name">Sprite</div>
                <form action="" method="post">
                    <input type="number" min="1" max="100" value="1" class="qty" name="qty">
                    <input type="submit" value="add to cart" name="add_to_cart" class="btn">
                </form>
            </div>
        </div>
    </section>
    <section id="desserts" class="menu">
        <h3 class="head2">Desserts</h3>
        <div class="box-container">

            <div class="box">
                <div class="price">Rs. 150</div>
                <img src="images/cake1.jpg" alt="">
                <div class="name">Choco Lava Cake</div>
                <form action="" method="post">
                    <input type="number" min="1" max="100" value="1" class="qty" name="qty">
                    <input type="submit" value="add to cart" name="add_to_cart" class="btn">
                </form>
            </div>

            <div class="box">
                <div class="price">Rs. 180</div>
                <img src="images/cake2.jpg" alt="">
                <div class="name">Classic Cheesecake</div>
                <form action="" method="post">
                    <input type="number" min="1" max="100" value="1" class="qty" name="qty">
                    <input type="submit" value="add to cart" name="add_to_cart" class="btn">
                </form>
            </div>

            <div class="box">
                <div class="price">Rs. 200</div>
                <img src="images/sundae.jpg" alt="">
                <div class="name">Choco Sundae</div>
                <form action="" method="post">
                    <input type="number" min="1" max="100" value="1" class="qty" name="qty">
                    <input type="submit" value="add to cart" name="add_to_cart" class="btn">
                </form>
            </div>
        </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

</body>