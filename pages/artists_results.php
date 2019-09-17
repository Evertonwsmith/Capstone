<?php
require_once '../src/page_manager.php';
require_once '../src/search_catalog.php';
require_once '../src/artist.php';
require_once '../src/my_sql.php';
include_once '../src/my_sql_cred.php';

//Create page manager
$pm = new page_manager("", "1", "artist_name", "9", "0");

//Define headers
$headers = ["search_relevance" => true, "artist_name" => true];
$pm->col = isset($pm->col) ? $pm->col : "artist_name";
?>
<div class='row'>
    <div class='my-auto'>
        <h4>Sort By: </h4>
    </div>
    <div class='row'>
        <?php
        foreach ($headers as $name => $sortable) {
            $sorter = "<div class='public_sort_container'>";
            if ($sortable) {
                $button = "<div>" . artist::get_table_header($name) . "</div><div>" . $pm->get_list_button($name) . "</div>";
                $sorter .= "<div class='sort-holder-spot-reserve'>$button</div><div class='sort-holder' onclick='this.getElementsByTagName(\"button\")[0].click()'>$button</div>";
            } else {
                $content = artist::get_table_header($name);
                $sorter .= "<div class='sort-holder-spot-reserve'>$content</div><div class='sort-holder'>$content</div>";
            }
            $sorter .= "</div>";
            echo $sorter;
        }
        ?>
    </div>
</div>
<hr>
<br><br>
<?php
//Get search results
$search_keys = search_catalog::get_search_keys();

// Get all sorted artist names
$artists = artist::get_all_names($pm->col, $pm->asc, null);
$count = my_sql::select("count(artistName) as total", "artistaccount LEFT JOIN useraccount ON artistaccount.userEmail=useraccount.userEmail", "useraccount.isActive='1'");
$total_results = $count == false ? 0 : $count[0]['total'];

// Whether results should be kept in search relevance order
$search_order = $pm->col === "search_relevance";

//sort and cross filter the artists with search results
$sorted = search_catalog::filter_sort_results($search_keys, $artists, $search_order);
if ($sorted) {
    $artists = $sorted;
    $total_results = count($artists);
}

//Make the artist objects
$displayed_artists = array();
$relevance = 1;
$num = 0;
foreach ($artists as $index => $id) {
    if ($num >= $pm->offset && $num - $pm->offset < $pm->page_amount) {
        $artist = new artist($id);
        //Set the relevance
        $artist->set_relevance($relevance);
        //add to array
        array_push($displayed_artists, $artist);
    }
    $relevance++;
    $num++;
}

// echo the page buttons
$page_buttons = $pm->get_page_buttons($total_results);
echo $page_buttons . "<br>";

//Echo post container
echo "<div class='row'>";

// output data of each artist
$count = 0;
foreach ($displayed_artists as $artist) {
    $count++;
    $tagless_desc = strip_tags($artist->get_description());
    echo ("<div class='col row justify-content-center'>
            <div class='card-holder artist-card-holder'>
                <div class='card artist-card'>
                    <a class='store-item' href=\"artist_page.php?artist_name=" . $artist->get_artist_name() . "\">
                        <span>
                            <div class='card-img-top artist-card-img'>" . $artist->get_profile_image() . "</div>
                            <span class='artist-card-img over'><p class='over-text'>" . $tagless_desc . "</p></span>
                        </span>
                        <div class='card-body'>
                            <h4>" . $artist->get_artist_name() . "</h4>
                        </div>
                    </a>
                </div>
            </div>
        </div>");
}

for ($count; $count % 3 != 0; $count++) {
    //Placeholder for emtpy slots
    echo "<div class='col'><div style='width: 20rem;'></div></div>";
}

echo "</div>";

//Echo end page buttons
echo $page_buttons . "<br>";
