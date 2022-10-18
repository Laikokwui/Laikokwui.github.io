<?php
require_once "config.php";

$title = $description = $image = "";
$title_err = $description_err = $image_err = "";
 
if(isset($_POST["id"]) && !empty($_POST["id"])) {
    $id = $_POST["id"];
    $sql = "SELECT * FROM blog WHERE id = ?";

    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        
        $param_id = $id;

        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_array($result);
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
    }

    $input_title = trim($_POST["title"]);
    if (empty($input_title)) {
        $title_err = "Please enter a title.";
    } else {
        $title = $input_title;
    }
    
    $input_description = trim($_POST["description"]);
    if (empty($input_description)) {
        $description_err = "Please enter a description.";     
    } else {
        $description = $input_description;
    }

    if (isset($_POST['same_image'])) {
        $image = $row['image'];
    } else {
        $filepath = "";
        if (!empty($row['image'])) {
            $filepath = 'uploads/'.$row['image'];
            unlink($filepath);
        }
    }

    if (isset($_POST['same_image']) && !empty($_FILES["imageupload"]["name"])) {
        $image_err .= "Use previous Image is checked!";
    }

    if (!empty($_FILES["imageupload"]["name"]) && empty($image_err)) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["imageupload"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        
        $input_image = getimagesize($_FILES["imageupload"]["tmp_name"]);
        
        if ($input_image !== false) {
            $image = htmlspecialchars(basename( $_FILES["imageupload"]["name"]));
        } else {
            $image_err .= "File is not an image.";
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

    if (empty($title_err) && empty($description_err) && empty($image_err)) {
        $sql = "UPDATE blog SET title=?, description=?, image=? WHERE id=?";
         
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssi", $param_title, $param_description, $param_image, $param_id);
            
            $param_title = $title;
            $param_description = $description;
            $param_image = $image;
            $param_id = $id;

            if (!isset($_POST['same_image'])) {
                if(!empty($_FILES["imageupload"]["name"])) {
                    if (mysqli_stmt_execute($stmt) && move_uploaded_file($_FILES["imageupload"]["tmp_name"], $target_file)) {
                        header("location: index.php");
                        exit();
                    } else {
                        echo "Oops! Something went wrong. Please try again later.";
                    }
                } else {
                    if (mysqli_stmt_execute($stmt)) {
                        header("location: index.php");
                        exit();
                    } else {
                        echo "Oops! Something went wrong. Please try again later.";
                    }
                }
            } else {
                if (mysqli_stmt_execute($stmt)) {
                    header("location: index.php");
                    exit();
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }
            }
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($link);
} else {
    if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
        $id =  trim($_GET["id"]);
        
        $sql = "SELECT * FROM blog WHERE id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $param_id);
            
            $param_id = $id;
            
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);
    
                if(mysqli_num_rows($result) == 1){
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    
                    $title = $row["title"];
                    $description = $row["description"];
                    $image = $row["image"];
                } else{
                    header("location: error.php");
                    exit();
                }
                
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        mysqli_stmt_close($stmt);
        mysqli_close($link);
    } else {
        header("location: error.php");
        exit();
    }
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Blog</title>
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
                    <h2 class="mt-5">Update Blog</h2>
                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post"  enctype="multipart/form-data">
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
                            <label>Image: </label>
                            <?php 
                            if (!empty($image)) {
                                echo '
                                <div class="container">
                                    <div class="col-md-4 px-0">
                                        <img src="uploads/'.$image.'" class="img-thumbnail">
                                    </div>
                                </div><br>
                                <input type="checkbox" id="same_image" name="same_image" value="same_image">
                                <label for="same_image">Check to use this image</label><br>'; 
                            }
                            ?>
                            <input type="file" name="imageupload" id="imageupload" class="form-control <?php echo (!empty($image_err)) ? 'is-invalid' : ''; ?>">
                            <span class="invalid-feedback"><?php echo $image_err;?></span>
                        </div>
                        <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="index.php" class="btn btn-secondary ml-2">Cancel</a>
                    </form>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>