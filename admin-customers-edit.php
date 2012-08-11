<h1>Modificare cumparator</h1>

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
        echo "<a href='" . admin_url() ."admin.php?page=shopper-customers'>Inapoi la pagina de Cumparatori</a>";
      }
      
    } 
  } else {  
?>


<?php 
  $id = $_REQUEST['profile'];
  
  global $wpdb;
  $profile = $wpdb->get_results(
    "SELECT * FROM wp_shopper_profiles " .
    "WHERE id='" . $id . "'"     
  );
  
  $p = $profile[0];

?>

<form action="" method="post">
  <table class="form-table">
    <tbody>
      <tr>
        <th><label>Nume</label></th>
        <td>
          <input type="text" class="regular-text" value="<?php echo $p->name ?>" id="name" name="name">
        </td>
      </tr>
      <tr>
        <th><label>Email</label></th>
        <td>
          <input type="text" class="regular-text" value="<?php echo $p->email ?>" id="email" name="email">
        </td>
      </tr>
      <tr>
        <th><label>Telefon</label></th>
        <td>
          <input type="text" class="regular-text" value="<?php echo $p->phone ?>" id="phone" name="phone">
        </td>
      </tr>
      <tr>
        <th><label>Adresa</label></th>
        <td>
          <input type="text" class="regular-text" value="<?php echo $p->address ?>" id="address" name="address">
        </td>
      </tr>
      <tr>
        <th><label>Oras</label></th>
        <td>
          <input type="text" class="regular-text" value="<?php echo $p->city ?>" id="city" name="city">
        </td>
      </tr>
    </tbody>
  </table>
  <input type="hidden" value="<?php echo $id ?>" id="id" name="id">
  <input type="hidden" value="<?php echo wp_create_nonce('admin-customers-edit') ?>" id="nonce" name="nonce">
  <p class="submit"><input type="submit" value="Salvare modificari" class="button-primary" id="submit" name="submit"></p>
</form>
<?php } ?>
