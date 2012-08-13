<h1>Modificare / Adaugare metoda de livrare</h1>

<?php 
  if ($_POST) {    
    if (wp_verify_nonce( $_POST['nonce'], 'admin-delivery-edit' )) {
      
      global $wpdb;
      $ret = $wpdb->query( 
        $wpdb->prepare( 
        "
          INSERT INTO wp_shopper_order_delivery
          (id, name, description, price, duration)
          VALUES (%s, %s, %s, %s, %s) ON DUPLICATE KEY UPDATE " .
          "name=VALUES(name), description=VALUES(description), price=VALUES(price), duration=VALUES(duration)"
        , 
        array($_POST['id'], $_POST['name'],$_POST['description'], $_POST['price'], $_POST['duration'])
        )
      );
            
      if ($ret != false) {
        echo "Modificare /adaugare cu succes!" . "<br/><br/>";        
      }
      echo "<a href='" . admin_url() ."admin.php?page=shopper-delivery'>Inapoi la Metoda de livrare</a>";
    } 
  } else {  
?>


<?php 
  $id = $_REQUEST['delivery'];
  
  global $wpdb;
  if (isset($id)) {
    // Edit    
    $items = $wpdb->get_results(
      "SELECT * FROM wp_shopper_order_delivery " .
      "WHERE id='" . $id . "'"     
    );
    
    $item = $items[0];
  } else {
    // Add new    
    $wpdb->query( 
      $wpdb->prepare( 
      "
        INSERT INTO wp_shopper_order_delivery
        (name, description, price, duration)
        VALUES (%s, %s, %s, %s)"
      , 
      array('', '', '', '')
      )
    );
    
    $item = new stdClass();
    $item->id = $wpdb->insert_id;
    $item->name = '';
    $item->description = '';
    $item->price = '';
    $item->duration = '';
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
        <th><label>Metoda de livrare</label></th>
        <td>
          <input type="text" class="regular-text" value="<?php echo $item->name ?>" id="name" name="name">
        </td>
      </tr>
      <tr>
        <th><label>Descriere</label></th>
        <td>
          <input type="text" class="regular-text" value="<?php echo $item->description ?>" id="description" name="description">
        </td>
      </tr>
      <tr>
        <th><label>Pret</label></th>
        <td>
          <input type="text" class="regular-text" value="<?php echo $item->price ?>" id="price" name="price">
        </td>
      </tr>
      <tr>
        <th><label>Timp livrare</label></th>
        <td>
          <input type="text" class="regular-text" value="<?php echo $item->duration ?>" id="duration" name="duration">
        </td>
      </tr>
    </tbody>
  </table>
  <input type="hidden" value="<?php echo $item->id ?>" id="id" name="id">
  <input type="hidden" value="<?php echo wp_create_nonce('admin-delivery-edit') ?>" id="nonce" name="nonce">
  <p class="submit"><input type="submit" value="Salvare modificari" class="button-primary" id="submit" name="submit"></p>
</form>
<?php } ?>
