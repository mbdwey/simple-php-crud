<?php if (isset($message)): ?>
 <div class="alert alert-info">
  <?php echo $message; ?>
<?php endif; ?>
</div>
<div>
  <h1>View a product!</h1>
  <?php
  if (!isset($item) || !is_array($item))
  {
    echo "<p>Product not found</p>";
  }
  else
  {
    echo "<table style='width:100%'>\n";
      echo "
      <tr>
        <td>Product name :</td>
        <td>{$item['product_name']}</td>
      </tr>
      <tr>
        <td>Product picture :</td>
        <td>
           <img src='{$item['product_picture_url']}' class='img-thumbnail'  width='450' height='300'>
        </td>
      </tr>
      <tr>
        <td>Actions :</td>
        <td>
          <ul>
            <li>
              <a href='?page=delete&module=product&id={$item['product_id']}'>[delete]</a>
            </li>
            <li>
              <a href='?page=edit&module=product&id={$item['product_id']}'>[edit]</a>
            </li>
          </ul>
        </td>
      </tr>\n";
    echo "</table>";
  }
  ?>
</div>
