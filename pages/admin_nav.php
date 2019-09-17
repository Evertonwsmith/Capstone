<?php  ?>
<div class="row">
    <h2 class="admin-nav-title my-auto">Admin Portal</h2>
    <div class="admin-navbar">
        <div class="row">
            <script>
                function admin_nav_link_select(elem) {
                    let nodes = document.getElementsByClassName('admin-nav-link');
                    for (let i = 0; i < nodes.length; i++) {
                        nodes[i].parentElement.classList.remove('admin-nav-link-selected');
                    }
                    elem.classList.add('admin-nav-link-selected');

                    // Set current reload_destination page based on the tab selected
                    switch (elem.id) {
                        case "product":
                            load_catalog("product");
                            reload_destination = "admin_product.php";
                            break;
                        case "blogpost":
                            load_catalog("blogpost");
                            reload_destination = "admin_blogpost.php";
                            break;
                        case "useraccount":
                            load_catalog("useraccount");
                            reload_destination = "admin_user.php";
                            break;
                        case "orders":
                            load_catalog("orders");
                            reload_destination = "admin_order.php?archive_orders=0";
                            break;
                        default:
                            reload_destination = "";
                            break;
                    }

                    //load the requested content (from 'admin_reload.js');
                    reload_content();
                }
            </script>
            <a id='orders' class="col" href="admin.php?tab=orders">
                <h2 class="admin-nav-link my-auto">Orders</h2>
            </a>
            <a id='product' class="col" href="admin.php?tab=product">
                <h2 class="admin-nav-link my-auto">Products</h2>
            </a>
            <a id='blogpost' class="col" href="admin.php?tab=blogpost">
                <h2 class="admin-nav-link my-auto">Blogposts</h2>
            </a>
            <a id='useraccount' class="col" href="admin.php?tab=useraccount">
                <h2 class="admin-nav-link my-auto">Users</h2>
            </a>
        </div>
    </div>
</div>
<br><br><br>