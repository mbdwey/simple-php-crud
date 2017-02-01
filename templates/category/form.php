<form action="" method="post">
    <div class="form-group">
        <label for="name">
            Category name
        </label>
        <input type="text" class="form-control" id="name" placeholder="Category name" name="category_title" value="<?php echo (isset($item['category_title'])) ? $item['category_title'] : ''; ?>" />
    </div>
    <div class="form-group">
        <label for="category">
            Category Parent
        </label>
        <select class="form-control" id="category" name="category_parent">
          <?php
          $found_parent = false;
          foreach ($categories_list as $category):
            ?>
            <option value="<?php echo $category['category_id']; ?>" <?php if (isset($item['category_parent']) && $item['category_parent'] == $category['category_id']): $found_parent = true; ?>selected="selected"<?php endif; ?>>
                <?php echo $category['category_title']; ?>
            </option>
          <?php endforeach; ?>
            <option value="0" <?php if (!$found_parent): ?>selected="selected"<?php endif; ?>>Parent Category</option>
        </select>
    </div>
    <button type="submit" class="btn btn-default" name="submit">
        Submit
    </button>
</form>
