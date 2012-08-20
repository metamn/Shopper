

<?php 
  if ($_POST) {    
    if (wp_verify_nonce( $_POST['nonce'], 'admin-customers-edit' )) {
      
      global $wpdb;
      $ret = $wpdb->query( 
        $wpdb->prepare( 
        "
          INSERT INTO wp_shopper_profiles
          (name, email, phone, address, city)
          VALUES (%s, %s, %s, %s, %s) ON DUPLICATE KEY UPDATE " .
          "name=VALUES(name), email=VALUES(email), phone=VALUES(phone), address=VALUES(address), city=VALUES(city)"
        , 
        array($_POST['name'],$_POST['email'], $_POST['phone'], $_POST['address'], $_POST['city'])
        )
      );
      
      if ($ret != false) {
        echo "Modificare cu succes!" . "<br/><br/>";        
      }
      echo "<a href='" . admin_url() ."admin.php?page=shopper-customers'>Inapoi la pagina de Cumparatori</a>";
    } 
  } else {  
?>


<?php 
  /*
  $id = $_REQUEST['profile'];
  
  global $wpdb;
  $profile = $wpdb->get_results(
    "SELECT * FROM wp_shopper_profiles " .
    "WHERE id='" . $id . "'"     
  );
  
  $p = $profile[0];
  */

  $item = shopper_admin_form_header($_REQUEST['profile'], "profiles");
  $p = $item->data;
  
  $fields = array();
  $fields[] = array(
    "title" => "Nume",
    "value" => $p->name,
    "id" => "name" 
  );
  $fields[] = array(
    "title" => "Email",
    "value" => $p->email,
    "id" => "email" 
  );  
  $fields[] = array(
    "title" => "Telefon",
    "value" => $p->phone,
    "id" => "phone" 
  );  
?>

<h1><?php echo $item->page_title ?> cumparator</h1>

<?php print_r($customers->get_columns()) ?>

<?php echo shopper_admin_form_body($p->id, $fields, 'admin-customers-edit', $item->button_title); ?>


<?php } ?>
