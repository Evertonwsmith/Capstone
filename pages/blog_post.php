<?php
//$post from parent script
$date = date_create($post->get_timestamp());
$b_d = date_format($date, "Y/m/d");
?>

<div>
    <div class="row justify-content-center">
        <div class="col-8 col-xl-10" align="center" style="padding-top: 2rem;">
            <div align="left">
                <h1><?php echo $post->get_title(); ?></h1>
                <hr>
                <p>Date Published: <?php echo $b_d; ?></p>
            </div>
            <div align="left">
                <p class="lead"><?php echo $post->get_text(); ?></p>
            </div>
        </div>

    </div>
</div>