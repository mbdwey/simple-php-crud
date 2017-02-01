<?php if (isset($message)): ?>
 <div class="alert alert-info">
  <?php echo $message; ?>
<?php endif; ?>
</div>
<form role="form" method="post">
    <div class="form-group">
        <label for="author_name">
            User name:
        </label>
        <input type="author_name" class="form-control" id="author_name" name="author_name" />
    </div>
    <div class="form-group">
        <label for="password">
            Password:
        </label>
        <input type="password" class="form-control" id="password" name="author_password" />
    </div>
    <div class="checkbox">
        <label>
            <input type="checkbox"/>
            Remember me
        </label>
    </div>
    <button type="submit" class="btn btn-default" name="submit">
        Submit
    </button>
</form>
