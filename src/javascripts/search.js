var page_num = 0;
var limit = 9;
var default_destination_page = "../pages/define_destination_page.php";

function search(results_div) {
    var url;
    if (typeof destination_page === 'undefined') {
        url = default_destination_page;
    } else {
        url = destination_page;
    }
    var results_xhttp = new XMLHttpRequest;
    results_xhttp.open('POST', url, true);
    results_xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    var post = "";

    if (results_div !== undefined && results_div !== null) {
        //Add search keys to POST variables
        var param = [];
        var result = results_div.getElementsByTagName("input");
        for (let i = 0; i < result.length; i++) {
            param.push(result[i].value);
        }
        if (param.length > 0) {
            post = post + "search_key=" + JSON.stringify(param);
        }
    }

    //Add limit to POST variables
    if (limit !== undefined) {
        if (post.length > 0) post = post + "&";
        post = post + "limit=" + limit;
    }

    //Add page number to POST variables
    if (page_num !== undefined) {
        if (post.length > 0) post = post + "&";
        post = post + "page_num=" + page_num;
    }

    results_xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            var items = document.getElementById("item-container");
            items.innerHTML = this.response;
            //Sort results if variable is declared
            if (typeof sort_by !== 'undefined' && sort_by !== undefined) {
                sort(sort_by);
            }
        }
    }

    results_xhttp.send(post);
}