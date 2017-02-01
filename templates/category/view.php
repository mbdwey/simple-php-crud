<?php if (isset($message)): ?>
 <div class="alert alert-info">
  <?php echo $message; ?>
<?php endif; ?>
</div>
<div>
  <h1>View a category!</h1>
  <?php
  if (!isset($item) || !is_array($item))
  {
    echo "<p>Category not found</p>";
  }
  else
  {
    echo "<table style='width:100%'>\n";
      echo "
      <tr>
        <td>Category title :</td>
        <td>{$item['category_title']}</td>
      </tr>
      <tr>
        <td>Actions :</td>
        <td>
          <ul>
            <li>
              <a href='?page=delete&module=category&id={$item['category_id']}'>[delete]</a>
            </li>
            <li>
              <a href='?page=edit&module=category&id={$item['category_id']}'>[edit]</a>
            </li>
          </ul>
        </td>
      </tr>\n";
    echo "</table>";
  }
  ?>
</div>
