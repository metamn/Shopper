<h1>Modificare / Adaugare statut si email</h1>

<?php 
  if ($_POST) {    
    if (wp_verify_nonce( $_POST['nonce'], 'admin-status-edit' )) {
      
      global $wpdb;
      $ret = $wpdb->query( 
        $wpdb->prepare( 
        "
          INSERT INTO wp_shopper_order_status
          (id, name, email_subject, email_body)
          VALUES (%s, %s, %s, %s) ON DUPLICATE KEY UPDATE " .
          "name=VALUES(name), email_subject=VALUES(email_subject), email_body=VALUES(email_body)"
        , 
        array($_POST['id'], $_POST['name'],$_POST['email_subject'], $_POST['email_body'])
        )
      );
            
      if ($ret != false) {
        echo "Modificare /adaugare cu succes!" . "<br/><br/>";        
      }
      echo "<a href='" . admin_url() ."admin.php?page=shopper-status'>Inapoi la Email</a>";
    } 
  } else {  
?>


<?php 
  $id = $_REQUEST['status'];
  
  global $wpdb;
  if (isset($id)) {
    // Edit    
    $status = $wpdb->get_results(
      "SELECT * FROM wp_shopper_order_status " .
      "WHERE id='" . $id . "'"     
    );
    
    $item = $status[0];
  } else {
    // Add new    
    $wpdb->query( 
      $wpdb->prepare( 
      "
        INSERT INTO wp_shopper_order_status
        (name, email_subject, email_body)
        VALUES (%s, %s, %s)"
      , 
      array('', '', '')
      )
    );
    
    $item = new stdClass();
    $item->id = $wpdb->insert_id;
    $item->name = '';
    $item->email_subject = '';
    $item->email_body = '';
  }
?>

<form action="" method="post">
  <table class="form-table">
    <tbody>
      <tr>
        <th><label>#</label></th>
        <td>
          <?php echo $item->id; ?>
        </td>
      </tr>
      <tr>
        <th><label>Statut</label></th>
        <td>
          <input type="text" class="regular-text" value="<?php echo $item->name ?>" id="name" name="name">
        </td>
      </tr>
      <tr>
        <th><label>Titlu email</label></th>
        <td>
          <input type="text" class="regular-text" value="<?php echo $item->email_subject ?>" id="email_subject" name="email_subject">
        </td>
      </tr>
      <tr>
        <th><label>Continut email</label></th>
        <td>
          <textarea id="email_body" name="email_body"><?php echo $item->email_body ?></textarea>
        </td>
      </tr>
    </tbody>
  </table>
  <input type="hidden" value="<?php echo $item->id ?>" id="id" name="id">
  <input type="hidden" value="<?php echo wp_create_nonce('admin-status-edit') ?>" id="nonce" name="nonce">
  <p class="submit"><input type="submit" value="Salvare modificari" class="button-primary" id="submit" name="submit"></p>
</form>
<?php } ?>
