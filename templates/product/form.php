 <div class="alert alert-info">
<?php
if (isset($message))
{
  echo $message;
}
?>
</div>
<form action="" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label for="name">
            Product name
        </label>
        <input type="text" class="form-control" id="name" placeholder="Product name" name="product_name" value="<?php echo (isset($item['product_name'])) ? $item['product_name'] : ''; ?>" />
    </div>
    <div class="form-group">
        <label for="category">
            Category Parent
        </label>
        <select class="form-control" id="category" name="category_id">
          <?php foreach ($categories_list as $category): ?>
            <option value="<?php echo $category['category_id']; ?>" <?php if (isset($item['category_id']) && $item['category_id'] == $category['category_id']): ?>selected="selected"<?php endif; ?>>
                <?php echo $category['category_title']; ?>
            </option>
          <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <?php if (isset($item['product_picture_url']) && !empty($item['product_picture_url'])): ?>
        <?php echo "<img src='{$item['product_picture_url']}' class='img-thumbnail'  width='450' height='300'>"; ?>
        <?php endif; ?>
    </div>
    <div class="form-group">
        <label for="picture">
            Product picture
        </label>
        <input type="file" id="picture" name="product_picture" />
        <p class="help-block">
            please upload image ** TDO pass settings **
        </p>
    </div>
    <button type="submit" class="btn btn-default" name="submit">
        Submit
    </button>
</form>
