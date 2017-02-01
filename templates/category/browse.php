<?php
if (isset($message))
{
  echo $message;
}
?>
<div>
  <h1>View categories!</h1>
  <?php
  if (!isset($categories) || !is_array($categories) || count($categories) == 0)
  {
    echo "<p>No categories found</p>";
  }
  else
  {
    echo "<table style='width:100%'>\n";
    echo "<tr><th>Id</th><th>Name</th><th>Parent</th><th>Actions</th></tr>\n";
    foreach ($categories as $category) {
      echo "<tr>
        <td>{$category['category_id']}</td>
        <td><a href='?page=view&module=category&id={$category['category_id']}'>{$category['category_title']}</a></td>
        <td><a href='?page=view&module=category&id={$category['parent_id']}'>{$category['parent_title']}</a></td>
        <td>
          <ul>
            <li>
              <a href='?page=delete&module=category&id={$category['category_id']}'>[delete]</a>
            </li>
            <li>
              <a href='?page=edit&module=category&id={$category['category_id']}'>[edit]</a>
            </li>

          </ul>
        </td>
      </tr>\n";
    }
    echo "</table>";
  }
  ?>
</div>
