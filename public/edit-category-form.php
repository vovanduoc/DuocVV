<?php
	include_once('includes/connect_database.php'); 
	include_once('functions.php'); 
?>
<div id="content" class="container col-md-12">
	<?php 
		if(isset($_GET['id'])){
			$ID = $_GET['id'];
		}else{
			$ID = "";
		}
		
		// create array variable to store category data
		$category_data = array();
			
		$sql_query = "SELECT category_image 
				FROM tbl_news_category 
				WHERE cid = ?";
				
		$stmt_category = $connect->stmt_init();
		if($stmt_category->prepare($sql_query)) {	
			// Bind your variables to replace the ?s
			$stmt_category->bind_param('s', $ID);
			// Execute query
			$stmt_category->execute();
			// store result 
			$stmt_category->store_result();
			$stmt_category->bind_result($previous_category_image);
			$stmt_category->fetch();
			$stmt_category->close();
		}
		
			
		if(isset($_POST['btnEdit'])){
			$category_name = $_POST['category_name'];
			$author = $_POST['author'];
			
			// get image info
			$menu_image = $_FILES['category_image']['name'];
			$image_error = $_FILES['category_image']['error'];
			$image_type = $_FILES['category_image']['type'];
				
			// create array variable to handle error
			$error = array();
				
			if(empty($category_name)){
				$error['category_name'] = " <span class='label label-danger'>Must Insert!</span>";
			}			

			if(empty($author)){
				$error['author'] = " <span class='label label-danger'>Must Insert!</span>";
			}
			
			// common image file extensions
			$allowedExts = array("gif", "jpeg", "jpg", "png");
			
			// get image file extension
			error_reporting(E_ERROR | E_PARSE);
			$extension = end(explode(".", $_FILES["category_image"]["name"]));
			
			if(!empty($menu_image)){
				if(!(($image_type == "image/gif") || 
					($image_type == "image/jpeg") || 
					($image_type == "image/jpg") || 
					($image_type == "image/x-png") ||
					($image_type == "image/png") || 
					($image_type == "image/pjpeg")) &&
					!(in_array($extension, $allowedExts))){
					
					$error['category_image'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
				}
			}
            //PDF
            $menu_pdf = $_FILES['category_pdf']['name'];
            $pdf_type = $_FILES['category_pdf']['type'];
            $error_pdf = array();
            $extension_pdf = end(explode(".", $_FILES["category_pdf"]["name"]));
            //END PDF
			if(!empty($category_name) && !empty($author)&& !empty($menu_pdf) && empty($error['category_image'])){
					
				if(!empty($menu_image)){
					
					// create random image file name
					$string = '0123456789';
					$file = preg_replace("/\s+/", "_", $_FILES['category_image']['name']);
					$function = new functions;
                    $string_name = $function->get_random_string($string, 6);
					$category_image = $string_name."-".date("Y-m-d").".".$extension;
                    $menu_pdf = $string_name."-".date("Y-m-d").".".$extension_pdf;

					// delete previous image
					$delete = unlink('upload/category/'."$previous_category_image");
                    $pieces = explode(".", $previous_category_image);
                    $previous_category_pdf = $pieces[0]+".pdf";
					$delete_pdf = unlink('upload/pdf/'."$previous_category_pdf");

					// upload new image
					$upload = move_uploaded_file($_FILES['category_image']['tmp_name'], 'upload/category/'.$category_image);
                    move_uploaded_file($_FILES['category_pdf']['tmp_name'], 'upload/pdf/'.$menu_pdf);
					$sql_query = "UPDATE tbl_news_category 
							SET category_name = ?, author = ?, category_image = ?
							WHERE cid = ?";
							
					$upload_image = $category_image;
					$stmt = $connect->stmt_init();
					if($stmt->prepare($sql_query)) {	
						// Bind your variables to replace the ?s
						$stmt->bind_param('ssss', 
									$category_name, 
									$author, 
									$upload_image,
									$ID);
						// Execute query
						$stmt->execute();
						// store result 
						$update_result = $stmt->store_result();
						$stmt->close();
					}
				} else {
					
					$sql_query = "UPDATE tbl_news_category 
							SET category_name = ?, author = ?
							WHERE cid = ?";
					
					$stmt = $connect->stmt_init();
					if($stmt->prepare($sql_query)) {	
						// Bind your variables to replace the ?s
						$stmt->bind_param('sss', 
									$category_name, 
									$author, 
									$ID);
						// Execute query
						$stmt->execute();
						// store result 
						$update_result = $stmt->store_result();
						$stmt->close();
					}
				}
				
				// check update result
				if($update_result){
					$error['update_category'] = " <h4><div class='alert alert-success'>
														* Category success updated.
														<a href='category.php'>
														<i class='fa fa-check fa-lg'></i>
														</a></div>
												  </h4>";
				}else{
					$error['update_category'] = " <span class='label label-danger'>Failed to update category.</span>";
				}
			}
				
		}
			
		// create array variable to store previous data
		$data = array();
		
		$sql_query = "SELECT * 
				FROM tbl_news_category 
				WHERE cid = ?";
		
		$stmt = $connect->stmt_init();
		if($stmt->prepare($sql_query)) {	
			// Bind your variables to replace the ?s
			$stmt->bind_param('s', $ID);
			// Execute query
			$stmt->execute();
			// store result 
			$stmt->store_result();
			$stmt->bind_result($data['cid'], 
					$data['category_name'],
					$data['category_image'],
					$data['author'],
					$data['status']
					);
			$stmt->fetch();
			$stmt->close();
		}

		if(isset($_POST['btnCancel'])){
			header("location: category.php");
		}
		
	?>
	<div class="col-md-12">
		<h1>Edit Book</h1>
		<?php echo isset($error['update_category']) ? $error['update_category'] : '';?>
		<hr />
	</div>
	
	<div class="col-md-5">
		<form method="post"
			enctype="multipart/form-data">
			<label>Book Name :</label><?php echo isset($error['category_name']) ? $error['category_name'] : '';?>
			<input type="text" class="form-control" name="category_name" value="<?php echo $data['category_name']; ?>"/>
			<br/>
			<label>Author Name :</label><?php echo isset($error['author']) ? $error['author'] : '';?>
			<input type="text" class="form-control" name="author" value="<?php echo $data['author']; ?>"/>
			<br/>
			<label>Image :</label><?php echo isset($error['category_image']) ? $error['category_image'] : '';?>
			<input type="file" name="category_image" id="category_image" /><br />
            <label> Cần update cả hình ảnh và pdf để data đồng bộ! Thanks</label><br />
            <label>File PDF :</label><?php echo isset($error_pdf['category_pdf']) ? $error_pdf['category_pdf'] : '';?>
            <input type="file" name="category_pdf"/>
            <br/>
			<img src="upload/category/<?php echo $data['category_image']; ?>" width="200" height="280"/>
			<br/><br/>
	</div>

	<div class="col-md-3">
	<br>
		<div class="panel panel-default">
		<div class="panel-heading">Action</div>
			<div class="panel-body">
				<input type="submit" class="btn-primary btn" value="Update" name="btnEdit"/>
				<input type="submit" class="btn-danger btn" value="Cancel" name="btnCancel"/>
			</div>
		</div>
	</div>
		</form>


	<div class="separator"> </div>
</div>
	
<?php include_once('includes/close_database.php'); ?>