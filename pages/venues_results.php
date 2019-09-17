<?php
require_once '../src/page_manager.php';
require_once '../src/search_catalog.php';
require_once '../src/venue.php';
require_once '../src/my_sql.php';
include_once '../src/my_sql_cred.php';

//Create page manager
$pm = new page_manager("", "1", "venue_name", "9", "0");

//Define headers
$headers = ["search_relevance" => true, "venue_name" => true];
$pm->col = isset($pm->col) ? $pm->col : "venue_name";
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
                $button = "<div>" . venue::get_table_header($name) . "</div><div>" . $pm->get_list_button($name) . "</div>";
                $sorter .= "<div class='sort-holder-spot-reserve'>$button</div><div class='sort-holder' onclick='this.getElementsByTagName(\"button\")[0].click()'>$button</div>";
            } else {
                $content = venue::get_table_header($name);
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

// Get all sorted venue names
$venues = venue::get_all_names($pm->col, $pm->asc, null);
$count = my_sql::select("count(venueName) as total", "venueaccount LEFT JOIN useraccount ON venueaccount.userEmail=useraccount.userEmail", "useraccount.isActive='1'");
$total_results = $count == false ? 0 : $count[0]['total'];

// Whether results should be kept in search relevance order
$search_order = $pm->col === "search_relevance";

//sort and cross filter the venues with search results
$sorted = search_catalog::filter_sort_results($search_keys, $venues, $search_order);
if ($sorted) {
    $venues = $sorted;
    $total_results = count($venues);
}

//Make the venue objects
$displayed_venues = array();
$relevance = 1;
$num = 0;
foreach ($venues as $index => $id) {
    if ($num >= $pm->offset && $num - $pm->offset < $pm->page_amount) {
        $venue = new venue($id);
        //Set the relevance
        $venue->set_relevance($relevance);
        //add to array
        array_push($displayed_venues, $venue);
    }
    $relevance++;
    $num++;
}

// echo the page buttons
$page_buttons = $pm->get_page_buttons($total_results);
echo $page_buttons . "<br>";

//Echo post container
echo "<div class='row'>";

// output data of each venue
$count = 0;
foreach ($displayed_venues as $venue) {
    $count++;
    $tagless_desc = strip_tags($venue->get_description());
    echo ("<div class='col row justify-content-center'>
            <div class='card-holder venue-card-holder'>
                <div class='card venue-card'>
                    <a class='store-item' href=\"venue_page.php?venue_name=" . $venue->get_venue_name() . "\">
                        <span>
                            <div class='card-img-top venue-card-img'>" . $venue->get_profile_image() . "</div>
                            <span class='venue-card-img over'><p class='over-text'>" . $tagless_desc . "</p></span>
                        </span>
                        <div class='card-body'>
                            <h4>" . $venue->get_venue_name() . "</h4>
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
