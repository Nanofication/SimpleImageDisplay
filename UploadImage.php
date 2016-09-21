<html lang="en">

<head>
  <title>Bootstrap Example</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <script type="text/javascript" src="jquery-version.js"></script>
  <script type = "text/javascript" src="AjaxForm.js"></script>
</head>

	<body>
		<form action = "UploadImage.php" method = "POST" enctype = "multipart/form-data" class = "ajaxform">
			<input type = "file" name = "image">
			<br/><br/>
			<input type = "text" name = "caption">
			<br/><br/>
			<input type = "submit" name = "submit" value = "Upload">
			<br/><br/>

<?php
	ini_set('mysql.connect_timeout', 300);
	ini_set('default_socket_timeout',3);


	$con = mysqli_connect("localhost", "root", "", "image_schema");
	
	if(isset($_POST['submit'])){
		if(getimagesize($_FILES['image']['tmp_name']) == FALSE){
			echo "Please select an image.";
		}
		else{
			$image = addslashes($_FILES['image']['tmp_name']);
			$image = file_get_contents($image);
			$image = base64_encode($image);
			$caption = mysqli_real_escape_string($con, $_POST['caption']);
			SaveImage($con, $image, $caption);
		}
	}
	
	function SaveImage($con, $image, $caption){
		//$con = mysqli_connect("localhost", "root", "", "image_schema");
		mysqli_select_db($con, "image_db");
		$qry = "INSERT INTO image_db (image,caption) values ('$image', '$caption')";
		
		$result = mysqli_query($con, $qry) or die ("Error: ".mysqli_error($con));
		
		if($result){
			echo "<br/>Image Uploaded<br/><br/><br/>";
		}
		else{
			echo "<br/>Image NOT Uploaded";
		}
	}
	
	//$con = mysqli_connect("localhost", "root", "", "image_schema");
	mysqli_select_db($con, "image_db");
	$qry = "Select * from image_db ORDER BY image_id";
	$result = mysqli_query($con, $qry);
	
	$row = mysqli_fetch_row($result);
	
	// Total Row Count
	$rows = $row[0];
	
	// Number of results to display per page
	$resultsPerPage = 10;
	
	// page number of last page
	$lastPageNum = ceil($rows/$resultsPerPage);
	
	if($lastPageNum < 1){
		$lastPageNum = 1; 
	}
	
	$pageNum = 1;
	
	if(isset($_GET['pn'])){
		$pageNum = preg_replace('#[^0-9]#', '', $_GET['pn']);
	}
	
	// Make sure the page number isn't below 1 or more than last page
	if($pageNum < 1){
		$pageNum = 1;
	}
	else if($pageNum > $lastPageNum){
		$pageNum = $lastPageNum;
	}
	
	$limit = 'LIMIT '.($pageNum - 1) * $resultsPerPage .',' .$resultsPerPage;
	
	$qry = "Select * from image_db ORDER BY image_id DESC $limit";
	
	$result = mysqli_query($con, $qry);
	
	// Tell user what page they are on
	$textLine = "Page <b>$pageNum</b> of <b>$lastPageNum</b>";

	// Pagination section
	$pagination = '';
	
	// If there is more than 1 page worth of results
	if($lastPageNum != 1){
		if($pageNum > 1){
			$previous = $pageNum - 1;
			$pagination .= '<a href="' .$_SERVER['PHP_SELF'].'?pn='.$previous.'">Previous</a> &nbsp; &nbsp; ';
			
			// Render clickable number links that appear left of page number
			for($i = $pageNum - 4; $i < $pageNum; $i++){
				if($i > 0){
					$pagination .= '<a href="'.$_SERVER['PHP_SELF'].'?pn='.$i.'">'.$i.'</a> &nbsp; ';
				}
			}
		
		}
		
	}
	
	// Render the target page number
	$pagination .= ''.$pageNum.' &nbsp; ';
	
	// Render clickable number links from right of page number
	for($i = $pageNum + 1; $i <= $lastPageNum; ++$i){
		$pagination .= '<a href="'.$_SERVER['PHP_SELF'].'?pn='.$i.'">'.$i.'</a> &nbsp; ';
		if($i >= $pageNum + 4){
			break;
		}
	}
	
	// Check if we are on the last page and generating "Next"
	if ($pageNum != $lastPageNum){
		$next = $pageNum + 1;
		$pagination .= ' &nbsp; &nbsp; <a href="'.$_SERVER['PHP_SELF'].'?pn='.$next.'">Next</a> ';
	}
	
	while($row = mysqli_fetch_array($result)){
		echo '<img height = "300" width = "300" src = "data:image;base64, '.$row[1].' "> ';
		echo $row[2]; 
		echo "<br/>";
	}
	
	mysqli_close($con);

?>

		<h2><?php echo $textLine; ?> Paged</h2>
		<p><?php echo $pagination; ?></p>
		</form>
	</body>
</html>