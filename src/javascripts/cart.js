function set_session(post, onsuccess) {
    //console.log(post);
    $.ajax("set_session_variable.php", {
        type: "POST",
        data: post,
        success: function (data) {
            //console.log(data);
            if (data === "success" && onsuccess !== undefined) {
                onsuccess();
            }
        }
    });
}

function add_to_cart(post) {
    $.ajax("add_to_cart.php", {
        type: "POST",
        data: post,
        success: function (data) {
            //console.log(data);
            if (data === "success") {
                window.location.replace("cart.php");
            }
        }
    });
}