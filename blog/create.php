<?php
require_once "config.php";
 
$title = $description = $image = "";
$title_err = $description_err = $image_err = "";
 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $add_image = false;

    $input_title = trim($_POST["title"]);
    if (empty($input_title)) {
        $title_err = "Please enter a title.";
    } else {
        $title = $input_title;
    }
    
    $input_description = trim($_POST["description"]);
    if (empty($input_description)) {
        $description_err = "Please enter a description.";     
    } else{
        $description = $input_description;
    }

    if (!empty($_FILES["imageupload"]["name"])) {
        $add_image = true;
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["imageupload"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        $input_image = getimagesize($_FILES["imageupload"]["tmp_name"]);
        if ($input_image !== false) {
            $image = htmlspecialchars(basename( $_FILES["imageupload"]["name"]));
        } else {
            $image_err = "File is not an image.";
        }

        if (file_exists($target_file)) {
            $image_err .= "Sorry, file already exists.";
        }

        if ($_FILES["imageupload"]["size"] > 500000) {
            $image_err .= "Please upload image file size less than 500kB.";
        }

        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            $image_err .= "Please upload jpg, jpeg & png files.";
        }
    }
    
    if (empty($title_err) && empty($description_err)) {
        $sql = "INSERT INTO blog (title, description, image) VALUES (?, ?, ?)";

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sss", $param_title, $param_description, $param_image);
            
            $param_title = $title;
            $param_description = $description;
            $param_image = $image;
            
            if ($add_image) {
                if (mysqli_stmt_execute($stmt) && move_uploaded_file($_FILES["imageupload"]["tmp_name"], $target_file)) {
                    header("location: index.php");
                    exit();
                } else {
                    $image_err .= "Sorry, there was an error uploading your file.";
                    echo "Oops! Something went wrong. Please try again later.";
                }
            } else {
                if (mysqli_stmt_execute($stmt)) {
                    header("location: index.php");
                    exit();
                } else {
                    $image_err .= "Sorry, there was an error uploading your file.";
                    echo "Oops! Something went wrong. Please try again later.";
                }
            }
            
        }
         
        // Close statement
        mysqli_stmt_close($stmt);
    }
    
    // Close connection
    mysqli_close($link);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Blog</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .wrapper{
            width: 600px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="mt-5">Create Blog</h2>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $title; ?>">
                            <span class="invalid-feedback"><?php echo $title_err;?></span>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control <?php echo (!empty($description_err)) ? 'is-invalid' : ''; ?>"><?php echo $description; ?></textarea>
                            <span class="invalid-feedback"><?php echo $description_err;?></span>
                        </div>
                        <div class="form-group">
                            <label>Image</label>
                            <input type="file" name="imageupload" id="imageupload" class="form-control <?php echo (!empty($image_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $image; ?>">
                            <span class="invalid-feedback"><?php echo $image_err;?></span>
                        </div>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="index.php" class="btn btn-secondary ml-2">Cancel</a>
                    </form>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>