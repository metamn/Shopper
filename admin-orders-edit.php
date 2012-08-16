<h1>Modificare comanda</h1>

<?php 
  if ($_POST) {    
    if (wp_verify_nonce( $_POST['nonce'], 'admin-orders-edit' )) {
      
      global $wpdb;
      $ret = $wpdb->query( 
        $wpdb->prepare("UPDATE wp_shopper_orders SET status_id = %s WHERE id = %s", 
        $_POST['status_id'], $_POST['id']) 
      );
            
      if ($ret != false) {
        echo "Modificare cu succes!" . "<br/><br/>";        
      }
      echo "<a href='" . admin_url() ."admin.php?page=shopper-orders'>Inapoi la Comenzi</a>";
    } 
  } else {  
?>


<?php 
  $id = $_REQUEST['order'];
  
  global $wpdb;
  $order = $wpdb->get_results(
    "SELECT * FROM wp_shopper_orders " .
    "WHERE id='" . $id . "'"     
  );
  
  $item = $order[0];

?>

<form action="" method="post">
  <table class="form-table">
    <tbody>
      <tr>
        <th><label>Numar comanda</label></th>
        <td>
          <?php echo $item->id; ?>
        </td>
      </tr>
      <tr>
        <th><label>Data</label></th>
        <td>
          <?php echo date('Y M d h:m', strtotime($item->date)); ?>
        </td>
      </tr>
      <tr>
        <th><label>Cumparator</label></th>
        <td>
          <?php
            $profile = $wpdb->get_results(
            "SELECT * FROM wp_shopper_profiles " .
            "WHERE wp_shopper_profiles.id = " . $item->profile_id
          ); 
          
          $p = $profile[0];
          
          // Edit link
          $link = "<a href='?page=shopper-customers&action=edit&profile=" . $p->id . "' title='Modificare cumparator'>";
          $link .= "Modificare cumparator</a>";          
          ?>    
          <ul>
            <li>ID     : <?php echo $p->id ?></li>
            <li>Nume   : <?php echo $p->name ?></li>
            <li>Email  : <?php echo $p->email ?></li>
            <li>Telefon: <?php echo $p->phone ?></li>
            <li>Adresa : <?php echo $p->address ?></li>
            <li>Oras   : <?php echo $p->city ?></li>
            <li><?php echo $link; ?></li>
          </ul>
        </td>
      </tr>
      <tr>
        <th><label>Produse</label></th>
        <td>
          <ul>
          <?php
            $products = $wpdb->get_results(
              "SELECT * FROM wp_shopper_order_items " .
              "WHERE wp_shopper_order_items.order_id = " . $item->id
            ); 
            foreach ($products as $p) { ?>
              <li>
                <ul>
                  <li>Nume: <?php echo $p->product_name; ?></li>
                  <li>Variatie: <?php echo $p->product_variation_name; ?></li>
                  <li>Cantitate: <?php echo $p->product_qty; ?></li>
                  <li>Pret: <?php echo $p->product_price; ?></li>
                </ul>
              </li>
              <li>&nbsp;</li>
            <?php }
          ?> 
          </ul> 
        </td>
      </tr>
      <tr>
        <th><label>Livrare</label></th>
        <td>
          <?php echo $item->delivery . ' RON ' ?>
        </td>
      </tr>
      <tr>
        <th><label>Total</label></th>
        <td>
          <?php echo $item->total; ?>
        </td>
      </tr>
      <tr>
        <th><label>Discount</label></th>
        <td>
          <?php echo $item->discount_id; ?>
        </td>
      </tr>
      <tr>
        <th><label>Valoare finala</label></th>
        <td>
          <?php echo $item->grand_total; ?>
        </td>
      </tr>
      <th><label>Statut</label></th>
        <td>
          <?php
            $status = $wpdb->get_results(
              "SELECT * FROM wp_shopper_order_status"
            );
            if (isset($status)) { ?>
              <select id="status_id" name="status_id">
              <?php foreach ($status as $s) {
                if ($s->id == $item->status_id) {
                  $selected = "selected";
                } else {
                  $selected = '';
                } ?>
                <option <?php echo $selected ?> value="<?php echo $s->id ?>"><?php echo $s->name ?></option>
              <?php }
            } else { ?>
              <input type="text" class="regular-text" value="<?php echo $item->status_id ?>" id="status_id" name="status_id">
          <?php } ?>              
        </td>
    </tbody>
  </table>
  <input type="hidden" value="<?php echo $id ?>" id="id" name="id">
  <input type="hidden" value="<?php echo wp_create_nonce('admin-orders-edit') ?>" id="nonce" name="nonce">
  <p class="submit"><input type="submit" value="Salvare modificari" class="button-primary" id="submit" name="submit"></p>
</form>
<?php } ?>
