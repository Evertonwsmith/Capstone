var page_num = 0;
var page_amount;
var asc;
var col;
var default_reload_destination = "../pages/define_reload_page.php";
var original_search = true;

function reload_content(results_div, extra_post) {
    var url;
    if (typeof reload_destination === 'undefined') {
        url = default_reload_destination;
    } else {
        url = reload_destination;
    }
    var contents_xhttp = new XMLHttpRequest;
    contents_xhttp.open('POST', url, true);
    contents_xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    var post = "";

    if (results_div === undefined || results_div === null) results_div = document.getElementById("search_results");

    if (results_div !== undefined || results_div !== null) {
        //Add search keys to POST variables
        var param = [];
        var result = results_div.getElementsByTagName("input");
        for (let i = 0; i < result.length; i++) {
            param.push(result[i].value);
        }
        if (param.length > 0) {
            post = post + "search_key=" + JSON.stringify(param);
            if (original_search) {
                col = "search_relevance"; // Set search results to appear in order
                //reset page num
                page_num = 0;
                original_search = false;
            }
        }
    }

    //Add page_amount to POST variables
    if (page_amount !== undefined) {
        if (post.length > 0) post = post + "&";
        post = post + "page_amount=" + page_amount;
    }

    //Add page number to POST variables
    if (page_num !== undefined) {
        if (post.length > 0) post = post + "&";
        post = post + "page_num=" + page_num;
    }

    //Add asc to POST variables
    if (asc !== undefined) {
        if (post.length > 0) post = post + "&";
        post = post + "asc=" + asc;
    }

    //Add col to POST variables
    if (col !== undefined) {
        if (post.length > 0) post = post + "&";
        post = post + "col=" + col;
    }

    if (extra_post === undefined || extra_post === null) {
        if (typeof window.extra_post !== "undefined") {
            //get globally set variable
            extra_post = window.extra_post;
        }
    }

    //Add additional POST variables
    if (extra_post !== undefined && extra_post !== null) {
        for (var key in extra_post) {
            if (extra_post.hasOwnProperty(key)) {
                if (post.length > 0) post = post + "&";
                post = post + key + "=" + extra_post[key];
            }
        }
    }

    contents_xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            //Add the response to the page
            var content = document.getElementById("reload-content-container");
            content.innerHTML = this.response;
        }
    }

    contents_xhttp.send(post);
}

function search(results_div) {
    if (results_div !== undefined) original_search = true;
    reload_content(results_div);
}