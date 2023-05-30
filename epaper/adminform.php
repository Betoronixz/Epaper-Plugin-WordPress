<form enctype="multipart/form-data" method="post" style="margin-top: 30px; background-image: linear-gradient(to bottom right, lightblue, white);


        box-shadow: 0 0 10px rgba(33, 150, 243, 0.5);
        padding: 10px;">
        <label for="paper_file">Select a PDF file to upload:</label>
        <input type="file" accept=".pdf" name="paper_file" id="paper_file" required>
        <input type="text"  name="paper_title" placeholder="Paper Title" required>
        <input type="submit" name="submit" value="Upload">
    </form>
    <?php
    if (isset($_POST['submit'])) {
        $paper_file = $_FILES['paper_file'];
        $paper_title=$_POST["paper_title"];

        // Check if the file is a PDF
        if ($paper_file['type'] == 'application/pdf') {
            $imagick = new Imagick();
            $imagick->readImage($paper_file['tmp_name']);   
            $page_count = $imagick->getNumberImages(); // Get the number of pages in the PDF

            for ($i = 0; $i < $page_count; $i++) {
                // Convert the current page to JPG
                $imagick->readImage($paper_file['tmp_name'] . "[" . $i . "]");
                $imagick->setImageFormat('jpg');
                $jpg_file_path = wp_upload_dir()["path"] . "/" . $paper_file["name"] . '_' . ($i+1) . '.jpg';
                $imagick->writeImage($jpg_file_path);
                $imagick->clear();
                $imagick->destroy();

                // Save the JPG to the media library
                $attachment = array(
                    'post_mime_type' => 'image/jpeg',
                    'post_title' => sanitize_file_name($paper_file['name'] . '_' . ($i+1)),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );
                $attach_id = wp_insert_attachment($attachment, $jpg_file_path);
                $jpg_url = wp_get_attachment_url($attach_id);

                // Insert the image URL into the database
                global $wpdb;
                $wpdb->insert($wpdb->prefix . 'crop_epaper', array(
                    'paper_title' => $paper_title,
                    'paper_file' => $jpg_url,
                ));
            }
            echo '<p>PDF file uploaded successfully.</p>';
        } else {
            echo '<p>Please upload a PDF file.</p>';
            echo '<p><a href="' . $_SERVER['PHP_SELF'] . '">Try again</a></p>';
        }
    }

    include plugin_dir_path(__FILE__) ."eptable.php";
    if(isset($_GET["paper_id"])){
        global $wpdb;
    
        $paper = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "crop_epaper WHERE paper_id = " . $_GET["paper_id"]);
    
        if ($paper) {
            $date = date('Y-m-d', strtotime($paper->created_at));
    
            $wpdb->query("DELETE FROM " . $wpdb->prefix . "crop_epaper WHERE DATE(created_at) = '$date'");
        }
    }
    
    

    ?>