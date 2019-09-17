<?php
$page_title = "About Sleepovers";
include "header.php";

$employee_info = "Some quick example text to build on the card title and make up the bulk of the card's content.
Testing how much content can fit in this little paragraph tag. I can't think of much to say about someone other
then that they have worked here for a very long time, could I become a poet with this?";

if (strlen($employee_info) > 100) {
	$employee_info = substr($employee_info, 0, 300) . "...";
}
?>
<br>
<div class="jumbotron jumbotron-fluid" style="text-align: center; margin-top: 3%; height: 17rem;">
	<div class="container">
		<h1 style="color: black;" class="display-4">About Us</h1>
		<hr class="about_hr">
	</div>
</div>
<div class="row">
	<div class="col-12" id="about_container" style="margin-top: 5%;">
		<div class="about_title">
			<h2>About Sleepovers</h2>
		</div>
		<div>
			<hr>
		</div>
		<div class='about_text'>
			<p class="p_about" id="first_paragraph">
				Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmodtempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
				consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
				cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
				proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
			</p>

			<p class="p_about">Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?</p>

			<p class="p_about">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>

		</div>
		<hr>
	</div>
</div>

<div class="row justify-content-around" style="padding-top: 8%;">
	<div class="col-12" style="text-align: center;">
		<h2>Meet the Team</h2>
		<hr>
		<br><br>
	</div>
	<div class="col-12 col-md-6 col-lg-4 col-xl-4 row justify-content-center">
		<div class="card employee_card" style="width: 18rem;">
			<div class="employee_image_container">
				<img id="scott_gibson" src="../img/scott_profile_image_bw.jpg" class="employee_image" alt="..." onmouseover="hover(this, this.id);" onmouseout="unhover(this, this.id);" />
			</div>
			<div class="card-body employee_info">
				<h4 class="card-title employee_name">Scott Gibson</h4>
				<hr class="about_hr">
				<div class="employee_role">
					<p class="p_about">Founder</p>
				</div>
				<p class="card-text p_about"><?php echo $employee_info; ?></p>
				<button type="button" class="btn employee_info_button" data-toggle="modal" data-target="#employee_container_1">Learn More</button>
			</div>
		</div>
	</div>
	<div class="col-12 col-md-6 col-lg-4 col-xl-4 row justify-content-center">
		<div class="card employee_card" style="width: 18rem;">
			<div class="employee_image_container">
				<img id="angie_pinchbeck" src="../img/Angie_Pinchbeck_profile_image_bw.jpeg" class="employee_image" alt="..." onmouseover="hover(this, this.id);" onmouseout="unhover(this, this.id);" />
			</div>
			<div class="card-body employee_info">
				<h4 class="card-title employee_name">Angie Pinchbeck</h4>
				<hr class="about_hr">
				<div class="employee_role">
					<p class="p_about">Website Admin</p>
				</div>
				<p class="card-text p_about"><?php echo $employee_info; ?></p>
				<button type="button" class="btn employee_info_button" data-toggle="modal" data-target="#employee_container_2">Learn More</button>
			</div>
		</div>
	</div>
	<div class="col-12 col-md-6 col-lg-4 col-xl-4 row justify-content-center">
		<div class="card employee_card" style="width: 18rem;">
			<div class="employee_image_container">
				<img id="" src="../pages/blank-profile-picture-973460_960_720.png" class="employee_image" alt="..." onmouseover="hover(this, this.id);" onmouseout="unhover(this, this.id);" />
			</div>
			<div class="card-body employee_info">
				<h4 class="card-title employee_name">Card title</h4>
				<hr class="about_hr">
				<div class="employee_role">
					<p class="p_about">Sales Rep</p>
				</div>
				<p class="card-text p_about"><?php echo $employee_info; ?></p>
				<button type="button" class="btn employee_info_button" data-toggle="modal" data-target="#employee_container_3">Learn More</button>
			</div>
		</div>
	</div>
	<div class="col-12 col-md-6 col-lg-4 col-xl-4 row justify-content-center">
		<div class="card employee_card" style="width: 18rem;">
			<div class="employee_image_container">
				<img id="" src="../pages/blank-profile-picture-973460_960_720.png" class="employee_image" alt="..." onmouseover="hover(this, this.id);" onmouseout="unhover(this, this.id);" />
			</div>
			<div class="card-body employee_info">
				<h4 class="card-title employee_name">Card title</h4>
				<hr class="about_hr">
				<div class="employee_role">
					<p class="p_about">Sales Rep</p>
				</div>
				<p class="card-text p_about"><?php echo $employee_info; ?></p>
				<button type="button" class="btn employee_info_button" data-toggle="modal" data-target="#employee_container_3">Learn More</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="employee_container_1" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true" style="color: white;">
	<div class="modal-dialog modal-dialog-scrollable" role="document">
		<div class="modal-content employee_content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalScrollableTitle">Scott Gibson</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="employee_image_container" style="margin-bottom: 0; margin-top: 2%;">
				<img src="../img/scott_profile_image.jpg" class="employee_image" alt="...">
			</div>
			<div class="modal-body">
				Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmodtempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
				consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
				cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
				proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="employee_container_2" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable" role="document">
		<div class="modal-content employee_content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalScrollableTitle">Angie Pinchbeck</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="employee_image_container" style="margin-bottom: 0; margin-top: 2%;">
				<img src="../img/Angie_Pinchbeck_profile_image.jpeg" class="employee_image" alt="...">
			</div>
			<div class="modal-body">
				Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmodtempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
				consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
				cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
				proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="employee_container_3" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalScrollableTitle">Modal title</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div style="color: black;" class="modal-body">
				Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmodtempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
				consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
				cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
				proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!--
	Image References:
	Scott Profile:
	https://www.kelownacapnews.com/entertainment/sleepovers-for-life-preserves-new-b-c-music-in-vinyl/ 
	Angie Profile:
	https://www.researchgate.net/profile/Angie_Pinchbeck
	-->
<script>
	function hover(element, id) {
		switch (id) {
			case "scott_gibson":
				element.setAttribute('src', '../img/scott_profile_image.jpg');
				break;
			case "angie_pinchbeck":
				element.setAttribute("src", "../img/Angie_Pinchbeck_profile_image.jpeg");
				break;
		}
	}

	function unhover(element, id) {
		switch (id) {
			case "scott_gibson":
				element.setAttribute('src', '../img/scott_profile_image_bw.jpg');
				break;
			case "angie_pinchbeck":
				element.setAttribute("src", "../img/Angie_Pinchbeck_profile_image_bw.jpeg");
				break;
		}
	}
</script>
<?php include "footer.php"; ?>