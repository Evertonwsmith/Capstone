<?php
require_once '../src/page_manager.php';
require_once '../src/search_catalog.php';
require_once '../src/blogpost.php';
require_once '../src/my_sql.php';
include_once '../src/my_sql_cred.php';
?>
<div>
    <?php
    //Create page manager
    $pm = new page_manager("", "0", "timestamp", "25", "0");

    //Define headers
    $pm->col = isset($pm->col) ? $pm->col : "timestamp";

    //Get search results
    $search_keys = search_catalog::get_search_keys();

    // Get all sorted blogpost ids
    $blogposts = blogpost::get_all_ids($pm->col, $pm->asc);
    $count = my_sql::select("count(blogID) as total", "blogpost", "isPublic='1'");
    $total_results = $count == false ? 0 : $count[0]['total'];

    // Whether results should be kept in search relevance order
    $search_order = $pm->col === "search_relevance";

    //sort and cross filter the blogposts with search results
    $sorted = search_catalog::filter_sort_results($search_keys, $blogposts, $search_order);
    if ($sorted) {
        $blogposts = $sorted;
        $total_results = count($blogposts);
    }

    //Make the blogpost objects
    $displayed_blogposts = array();
    $relevance = 1;
    $num = 0;
    foreach ($blogposts as $index => $id) {
        if ($num >= $pm->offset && $num - $pm->offset < $pm->page_amount) {
            $blogpost = new blogpost($id);
            //Set the relevance
            $blogpost->set_relevance($relevance);
            //add to array
            array_push($displayed_blogposts, $blogpost);
        }
        $relevance++;
        $num++;
    }

    // echo the page buttons
    $page_buttons = $pm->get_page_buttons($total_results);
    echo $page_buttons . "<br>";

    //Echo post container
    echo "<div class='row'>";

    // output data of each blogpost
    foreach ($displayed_blogposts as $post) {
        if ($post->is_public()) {
            $date = date_create($post->get_timestamp());
            $b_d = date_format($date, "Y/m/d");
            $text = $post->get_text();
            $img_tag = strstr($text, "<img");
            if ($img_tag) {

                $img_tag_end = strpos($img_tag, ">");
                $img_tag = substr($img_tag, 0, $img_tag_end + strlen(">"));
                echo "<!--$img_tag-->";
            } else {
                $img_tag = "<img src='../src/stylesheets/img/Sleepovers-Logo-Dark.jpg'></img>";
            }
            $img_tag = "<div class='img-container'>$img_tag</div>";
            $text = strip_tags($text);
            if (strlen($text) > 100) {
                $text = substr($text, 0, 100) . "...";
            }
            echo
                "<div class='col-12 col-xl-12 blog_container'>
						<a href='blog_public.php?blog_id=" . $post->get_blog_id() . "'>
							<div class='row blog_row'>
                                <div class='col-4 align-self-center' style='width:20rem; max-height: 100%;'>
                                    <div class='blog-list-img'>
                                        $img_tag
                                    </div>
								</div>
								<div class='col-8 blog_info align-self-center flex-grow-1'>
									<div class='blog_title'>" . $post->get_title() . "</div>
									<hr>
									<div class='blog_timestamp'><small class='text-muted'>" . $b_d . "</small></div>
									<div class='helper'></div>
									<div class='blog_text hide-small'>" . $text . "</div>
								</div>
							</div>
						</a>
					</div>";
        }
    }

    echo "</div>";

    //Echo end page buttons
    echo $page_buttons . "<br>";
    ?>
</div>