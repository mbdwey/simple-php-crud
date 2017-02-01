<?php
if (isset($message))
{
  echo $message;
}
?>
<div>
  <h1>View Products!</h1>
  <?php
  if (!isset($products) || !is_array($products) || count($products) == 0)
  {
    echo "<p>No products found</p>";
  }
  else
  {
    echo "<table style='width:100%'>\n";
    echo "<tr><th>Id</th><th>Name</th><th>Category</th><th>Actions</th></tr>\n";
    foreach ($products as $key => $product) {
      echo "<tr>
        <td>{$product['product_id']}</td>
        <td><a href='?page=view&module=product&id={$product['product_id']}'>{$product['product_name']}</a></td>
        <td><a href='?page=view&module=category&id={$product['category_id']}'>{$product['category_title']}</a></td>
        <td>
          <ul>
            <li>
              <a href='?page=delete&module=product&id={$product['product_id']}'>[delete]</a>
            </li>
            <li>
              <a href='?page=edit&module=product&id={$product['product_id']}'>[edit]</a>
            </li>

          </ul>
        </td>
      </tr>\n";
    }
    echo "</table>";
  }
  ?>
</div>
