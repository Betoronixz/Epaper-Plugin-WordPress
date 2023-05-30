<?php
add_shortcode("epdisplay", "ep_ds");

function ep_ds()
{
    ob_start();

    if (isset($_GET["id"])) {
        global $wpdb;
        // Get the ID card image from the database
        $table_name8 = $wpdb->prefix . "epaper_images"; // add prefix to table name
        $sql1 = "SELECT image_data FROM $table_name8 WHERE id = {$_GET['id']}"; // select the image data from the row with the specified ID
        $result2 = $wpdb->get_results($sql1, ARRAY_A); // use get_results to get data as associative array
        if (!empty($result2)) {
            $row2 = $result2[0];
            $image_path = wp_upload_dir()['path'] . '/' . basename($row2['image_data']);
            if (file_exists($image_path)) {
                echo '<img src="' . wp_upload_dir()['url'] . '/' . basename($row2['image_data']) . '">';
            } else {
                echo 'Image file not found';
            }
        } else {
            echo 'No image found';
        }
    } else {
?>

        <form method="GET" action="">
            <label for="">Select date</label>
            <input type="date" name="selected_date" style="height: 30px;" value="<?php echo date('Y-m-d'); ?>">
            <input type="submit" name="submit" value="Submit">
        </form>
        <div class="ep">
            <div class="eptable">
                <?php
                global $wpdb;
                $table_name = $wpdb->prefix . "crop_epaper";
                if (isset($_GET['selected_date'])) {
                    $selected_date = $_GET['selected_date'];
                } else {
                    $results2 = $wpdb->get_results("SELECT * FROM $table_name ORDER BY DATE(created_at) DESC");
                    $last_row = end($results2);
                    $selected_date = date('Y-m-d', strtotime($last_row->created_at));
                }
                $result = $wpdb->get_results("SELECT * FROM $table_name WHERE DATE(created_at)= '$selected_date' ORDER BY paper_file ASC");
                $first_image_url = '';
                if (!empty($result)) {
                    foreach ($result as $r) {
                        $file_url = $r->paper_file;
                        // Check if this is the first image and store its URL
                        if (empty($first_image_url)) {
                            $first_image_url = $file_url;
                        }
                ?>
                        <img src="<?php echo $file_url; ?>" alt="Image" class="im1">
                <?php }
                } else {
                    echo '<tr><td colspan="2">No newspapers found.</td></tr>';
                }
                ?>
            </div>
            <div class="epimage">
                <?php if (!empty($first_image_url)) { ?>
                    <img src="<?php echo $first_image_url; ?>" alt="" class="image">
                <?php } else { ?>
                    <p>No images found</p>
                <?php } ?>
            </div>
        </div>


        <div class="aj" style="display: none ;"><?php echo admin_url('admin-ajax.php') ?></div>

        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="img-container">
                            <div class="row">
                                <div class="col-md-8">
                                    <!--  default image where we will set the src via jquery-->
                                    <img id="image">
                                </div>
                                <div class="col-md-4">
                                    <div class="preview mx-0"></div>
                                    <button type="button" class="mx-2 my-3 btn btn-primary" id="crop">Download</button>
                                    <button type="button" class="mx-2 my-3 btn btn-primary" id="st" data-toggle="modal" data-target="#storeModal">Share it on social media</button>
                                    <button type="button" class="mx-1 my-3 btn btn-secondary" data-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="storeModal" tabindex="-1" role="dialog" aria-labelledby="storeModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="storeModalLabel">Share</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Share it</p>
                            <?php
                            global $wpdb;
                            $table_name = $wpdb->prefix . 'epaper_images';
                            $last_id = $wpdb->get_var("SELECT MAX(id) FROM $table_name");

                            // Add 1 to the last ID to get the new ID
                            $new_id = $last_id + 1;
                            ?>
                            <input type="text" class="form-control" value="<?php echo esc_url(get_permalink()) . '?id=' . $new_id; ?>" readonly>
                            <div class="social-icons p-3 d-flex justify-content-center">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(esc_url(get_permalink()) . '?id=' . $new_id); ?>" target="_blank"><i class=" mx-3 fab fa-facebook-f"></i></a>
                                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(esc_url(get_permalink()) . '?id=' . $new_id); ?>" target="_blank"><i class=" mx-3  fab fa-twitter"></i></a>
                                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(esc_url(get_permalink()) . '?id=' . $new_id); ?>" target="_blank"><i class=" mx-3  fab fa-linkedin"></i></a>
                                <a href="https://wa.me/?text=<?php echo urlencode(esc_url(get_permalink()) . '?id=' . $new_id); ?>" target="_blank"><i class=" mx-3  fab fa-whatsapp"></i></a>
                            </div>


                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
        <?php
    }
    return ob_get_clean();
}
        ?>