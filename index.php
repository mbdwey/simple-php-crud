<?php
$config = [];
$config['settings'] = [
	'rememberMeTimeOut' => 7
];
$config['db'] = [
	'host' => 'localhost',
	'dbname' => '',
	'username' => '',
	'password' => '',
];
function exception_handler($exception)
{
	echo <<<EOL
<!DOCTYPE html>
<html>
	<head>
		<title>Error !</title>
	</head>
	<body>
		<h1>Sorry , i got internal error!</h1>
		<pre>{$exception->getMessage()}</pre>
	</body>
</html>
EOL;
	die;
}
set_exception_handler('exception_handler');

class Views
{
	public $templates = array();
  private $templates_path;
	public function __construct()
	{
		$this->templates_path =  dirname( realpath( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
	}
  /*
    extract variables so it's readable from template scope
    then find template and execute it's value using eval
  */
	public function page($module = 'home',$pageKey = null,$variables = array(),$views = null)
	{
    if (empty($views))
    {
      $views = $this;
    }
    $template_file_path = $this->templates_path . $module . DIRECTORY_SEPARATOR . $pageKey . '.php';
		if (!file_exists($template_file_path))
		{
			throw new Exception('requested page template not found @path {$template_file_path}.');
		}
    // unset($variables['this'])
    extract($variables);
    // TBD :D , use layouts
    echo <<<EOL
<!DOCTYPE html>
<html>
  <head>
    <title>{$pageKey}</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
  </head>
  <section>
EOL;
    if (($module != 'home' && $pageKey != 'index') || ($module == 'home' && $pageKey == 'message'))
    // if ($module != 'home' && ($pageKey != 'index' || $pageKey == 'message'))
    {
      echo '<ul><li><a href="?page=index">Home</a></li></ul>';
    }
    /* eval('?>'.$this->templates[$pageKey].'<?php;'); */
    include($template_file_path);
		echo <<<EOL
  </section>
</html>
EOL;
	}

  // render partial content
  public function block($module = 'home',$pageKey = null,$variables = array(),$views = null)
  {
    if (empty($views))
    {
      $views = $this;
    }
    $template_file_path = $this->templates_path . $module . DIRECTORY_SEPARATOR . $pageKey . '.php';
    if (!file_exists($template_file_path))
    {
      throw new Exception('requested block template not found.');
    }
    extract($variables);
    include($template_file_path);
  }

}

/**
*
*/
class Database
{
	private $connectionRefrence = false;
	/*
		if i got validate connection i will try to connect or ask user to check connection settings
	*/
	public function __construct($config = null)
	{
		if (
        is_array($config) &&
        isset($config['host'], $config['dbname'], $config['username'], $config['password']) &&
        !empty($config['host']) && !empty($config['dbname']) &&
        !empty($config['username']) && !empty($config['password'])
    )
		{
			$this->do_connect($config);
		}
		else
		{
			throw new Exception("Looks like you are trying to run this script for the first time , please check DB settings.");
		}
	}

	/*
		try to connect
			success
				set connection reference
      false
        throw exception
    TBD most functon share same validation , set
	*/
	private function do_connect($config)
	{
		try {
			$conn = new PDO("mysql:host={$config['host']};dbname={$config['dbname']}", $config['username'], $config['password']);
			// set the PDO error mode to exception
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $conn->exec("SET CHARACTER SET utf8");
			$this->connectionRefrence = $conn;
		}
		catch(PDOException $e)
		{
			throw new Exception("Connection failed: " . $e->getMessage());
		}
	}

  public function do_query($sql)
  {
    if (!$this->connectionRefrence)
    {
      throw new Exception("Not connected");
    }
    $this->connectionRefrence->exec($sql);
  }

  public function do_prepend($sql)
  {
    if (!$this->connectionRefrence)
    {
      throw new Exception("Not connected");
    }
    return $this->connectionRefrence->prepare($sql);
  }

  private function do_fetch($query,$variables)
  {
    if (!$this->connectionRefrence)
    {
      throw new Exception("Not connected");
    }
    $stream = $this->connectionRefrence->prepare($query);
    // TOBD not sure about setting fetch style this way !
    $stream->setFetchMode(PDO::FETCH_NAMED);
    $stream->setFetchMode(PDO::FETCH_ASSOC);
    $stream->execute($variables);
    return $stream;
  }

	public function fetchAllAssoc($query,$variables = array())
	{
    return $this->do_fetch($query,$variables)->fetchAll();
	}

  public function fetch($query,$variables = array())
  {
    return $this->do_fetch($query,$variables)->fetch();
  }
  /*
    close connection while this object destroyed
  */
	public function __destruct()
	{
		$this->connectionRefrence = null;
	}
}

interface ModuleInterface
{
  public function create($values = array());
  public function browse($records_count = 25);
  public function item($record_id = null);
  public function update($record_id = null,$values = array());
  public function delete($record_id = null);
  /*
    TBD : use validate function with columns array to gain validation rules
    disabled as it's only test :]
  */
  //public function validate();
}

/**
* Main modules parent
*/
class Modules
{

  public $table_name;
  public $primary_key;
  protected $databaseRefrence;

  public function __construct($databaseRefrence  = null)
  {
    if (empty($databaseRefrence))
    {
      global $database;
      $this->databaseRefrence = $database;
    }
  }

  /*
    return nested array from db records
  */
  public function browse($records_count = 25)
  {
    $query = "select * from {$this->table_name} limit {$records_count}";
    return $this->databaseRefrence->fetchAllAssoc($query);
  }

  // return single item
  public function item($record_id = null)
  {
    $query = "select * from {$this->table_name} where {$this->primary_key} = :record_id";
    return $this->databaseRefrence->fetch($query,array('record_id' => $record_id));
  }

  /*
    @$values indexed array (key == column, value = cel value)
  */
  public function create($values = array())
  {
    $query = "
      INSERT INTO {$this->table_name}
      (".implode(',',array_keys($values)).")
      VALUES (:".implode(', :',array_keys($values)).")
    ";
    $recurce = $this->databaseRefrence->do_prepend($query);
    $recurce->execute($values);
  }

  /*
    @$record_id unique index
    @$values indexed array (key == column, value = cel value)
  */
  public function update($record_id = null,$values = array())
  {
    $query = "UPDATE {$this->table_name} SET ";
    $vars_list = [];
    foreach ($values as $key => $value)
    {
      $vars_list[] = "`{$key}` = :$key";
    }
    $query .= implode(',', $vars_list);
    $query .= " WHERE `{$this->primary_key}` = :record_id";
    $values['record_id'] = $record_id;
    $recurce = $this->databaseRefrence->do_prepend($query);
    $recurce->execute($values);
  }

  /*
    delete using primary key id
  */
  public function delete($record_id = null)
  {
    $query = "delete from {$this->table_name} where {$this->primary_key} = {$record_id}";
    return $this->databaseRefrence->do_query($query);
  }
}
/**
* Product Module class
*/
class Product extends Modules implements ModuleInterface
{
  public $columns = array('category_id','product_name');
  public function __construct()
  {
    parent::__construct();
    $this->table_name = 'products';
    $this->primary_key = 'product_id';
  }

  // overwrite browse
  public function browse($records_count = 25)
  {
    // use static ref
    $category = New Category;
    $query = "
      select product.*,category.* from {$this->table_name} as product
      left join {$category->table_name} as category
        on (category.category_id = product.category_id)
       limit {$records_count}";
    return $this->databaseRefrence->fetchAllAssoc($query);
  }

  public function item($record_id = null)
  {
    $item = parent::item($record_id);
    if (is_array($item) && isset($item['product_picture']) && !empty($item['product_picture']))
    {
      $item['product_picture_url'] = dirname($_SERVER['REQUEST_URI'])."/uploads/".$item['product_picture'];
    }
    return $item;
  }
}

/**
* Category Module class
*/
class Category extends Modules implements ModuleInterface
{
  public $columns = array('category_id','category_title','category_parent');
  public function __construct()
  {
    parent::__construct();
    $this->table_name = 'categories';
    $this->primary_key = 'category_id';
  }
  // overwrite category browse
  public function browse($records_count = 25)
  {
    $query = "
      select category.*,parent_category.category_id as parent_id,parent_category.category_title as parent_title from {$this->table_name} as category
      left join {$this->table_name} as parent_category
        on (category.category_parent = parent_category.category_id)
       limit {$records_count}";
    return $this->databaseRefrence->fetchAllAssoc($query);
  }
}

/**
* Author as class
*/
class Author extends Modules implements ModuleInterface
{
  /*
    other columns
      author_remember_me
      author_browser_signature
      author_remember_timeout
  */
  public $columns = array('author_id','author_name','author_group');
  public function __construct()
  {
    parent::__construct();
    $this->table_name = 'authors';
    $this->columns = array('author_id','author_name','author_username','author_group','author_password');
  }

  /*
    fetch user using username and password
  */
  public function check_user_login($author_username,$author_password)
  {
    if (empty($author_username) || empty($author_password))
    {
      throw new Exception("bad auth , check_user_login");
    }
    $query = "
      select * from {$this->table_name} where `author_username` = :author_username and `author_password` = :author_password";
      $variables = array(
        ':author_username' => $author_username,
        ':author_password' => SimpleAuth::hash_password($author_password)
      );
    return $this->databaseRefrence->fetch($query,$variables);
  }

  public function check_user_rememberme($author_code)
  {
    $query = "
      select * from {$this->table_name} where `author_remember_timeout` >= :author_remember_timeout and `author_remember_me` = :author_remember_me";
      $variables = array(
        ':author_remember_timeout' => time(),
        ':author_remember_me' => $author_code
      );
    return $this->databaseRefrence->fetch($query,$variables);
  }
}

class Routes
{
  private $active_module = null;
  private $allowed_modules = [
    'product' => 'Product',
    'category' => 'Category'
  ];
  private $allowed_routes = [
    'index' => 'render_index',
    'view' => 'render_view',
    'add' => 'render_form',
    'edit' => 'render_form',
    'delete' => 'render_delete',
    'login' => 'render_login',
    'logout' => 'render_logout',
  ];

  /*
    if path are registered
      call render_page to display this path
    or
      call 404 page not found
  */
  public function __construct($path = 'index',$variables = array())
  {
    $this->append_get_variables($variables);
    if (array_key_exists($path, $this->allowed_routes))
    {
      $page = $this->allowed_routes[$path];
      $this->render_page($page,$path,$variables);
    }
    else
    {
      $this->render_page('404',$path,$variables);
    }
  }

  private function append_get_variables(&$variables)
  {
    // help protect other modules
    if (isset($_GET['module']) && array_key_exists($_GET['module'], $this->allowed_modules))
    {
      $variables['module']  = $_GET['module'];
    }
    else
    {
      $variables['module']  = null;
    }
    // set record_id if id get var presented and integer validation passed
    if (isset($_GET['id']) && is_numeric($_GET['id']))
    {
      $variables['record_id']  = $_GET['id'];
    }
    else
    {
      $variables['record_id']  = null;
    }
  }

  /*
    $pageKey        (string) : page that allowed and should have callable function
    $original_path  (string) : original user path
    find and run or throw error asking user to report this page
  */
  public function render_page($page,$original_path,$variables)
  {
    if (method_exists($this,$page))
    {
      $this->$page($original_path,$variables);
    }
    else
    {
      throw new Exception("please report this page to web-master !");
    }
  }

  private function render_index($original_path,$variables)
  {
    $products = new Product;
    $producs_list = $products->browse();
    $categories = new Category;
    $categories_list = $categories->browse();
    $views = new Views;
    $views->page(
      'home',
      'index',
      array(
        'producs_list' => $producs_list,
        'categories_list' => $categories_list,
      ),
      $views
    );
  }


  private function render_login($original_path,$variables)
  {
    $message = null;
    $author = new Author;
    $views = new Views;
    if (isset($_POST['submit']))
    {
      if (!isset($_POST['author_name'],$_POST['author_password']))
      {
        throw new Exception("please enter login details");
      }
      $user = $author->check_user_login($_POST['author_name'],$_POST['author_password']);
      if (!empty($user['author_id']))
      {
        SimpleAuth::do_user_login($user,$author);
        $message = "Login Success !";
      }
      else
      {
        $message = "Invalid credential";
      }
    }
    $views->page('author','login',array('message' => $message),$views);
  }



  private function render_form($original_path,$variables)
  {
    $message = null;
    $item = null;
    $categories_list = array();
    $module = $variables['module'];
    $views = new Views;
    $module_object = New $module;
    if (isset($_POST['submit']))
    {
      $new_item = array();
      foreach ($module_object->columns as $colum)
      {
        if (isset($_POST[$colum]) && !empty($_POST[$colum]))
        {
          $new_item[$colum] = $_POST[$colum];
        }
      }
      // ugly hack
      if (!empty($_FILES['product_picture']["name"]))
      {
        $new_item['product_picture'] = my_file_upload('product_picture');
      }
      // TBD call some validate
      switch ($original_path)
      {
        case 'edit':
          $module_object->update($variables['record_id'],$new_item);
          $message = "<p>{$module} item updated";
        break;
        default:
        case 'add':
          $module_object->create($new_item);
          $message = "<p>New {$module} item created";
        break;
      }
    }
    if (isset($variables['record_id']))
    {
      $item = $module_object->item($variables['record_id']);
    }
    // view form
    $categories = new Category;
    $categories_list = $categories->browse();
    $views->page(
      $module,
      'add',
      array(
        // 'products' => $products,
        'categories_list' => $categories_list,
        'message' => $message,
        'item' => $item,
      ),
      $views
    );
  }

  private function render_view($original_path,$variables)
  {
    if (empty($variables['module']) || empty($variables['record_id']))
    {
      throw new Exception("un-authorized action.");
    }
    // get item
    $module = $this->allowed_modules[$variables['module']];
    $module_object = New $module;
    $views = new Views;
    $message = null;
    if (! $item = $module_object->item($variables['record_id']))
    {
      $message = 'item not found';
    }
    $module = $variables['module'];
    $views->page(
      $module,
      'view',
      array(
        'item' => $item,
        'message' => $message
      ),
      $views
    );
  }

  private function render_delete($original_path,$variables)
  {
    if (empty($variables['module']) || empty($variables['record_id']))
    {
      throw new Exception("un-authorized action.");
    }
    // validate item exists
    $module_key =$this->allowed_modules[$variables['module']];
    $module_object = New $module_key;
    if (! $item = $module_object->item($variables['record_id']))
    {
      $message = 'item not found';
    }
    else
    {
      $module_object->delete($variables['record_id']);
      $message = 'item has been deleted';
    }
    $views = new Views;
    $views->page(
      'home',
      'message',
      array('message' => $message)
    );
  }
}

/**
* my simple authentication class
*/
// class SimpleAuth extends Author
class SimpleAuth
{

  public function do_user_login($userObject = array(),$author = null)
  {
    if ($userObject['author_id'])
    {
      $_SESSION['author_id'] = $userObject['author_id'];
      if (!isset($_COOKIE['remember_me']))
      {
        $remember_me_timeout = time() + 7 * 24 * 3600;
        $remember_me_key = substr( md5(rand()), 0, 32);
        $author->update($userObject['author_id'],array(
          'author_remember_me' => $remember_me_key,
          // 'author_browser_signature' = > '', TBD add more security layer
          'author_remember_timeout' => $remember_me_timeout
        ));
        setcookie('remember_me', $remember_me_key, $remember_me_timeout);
      }
    }
    else
    {
      throw new Exception("bad auth call , please contact web-master");
    }
  }

  public static function check_rememberme_key()
  {
    if (isset($_COOKIE['remember_me']))
    {
      $author = new Author;
      $user = $author->check_user_rememberme($_COOKIE['remember_me']);
      if ($user['author_id'])
      {
        SimpleAuth::do_user_login($user,$author);
        return true;
      }
      else
      {
        return false;
      }
    }
  }

  public static function user_is_logged_in()
  {
    session_start();
    if (isset($_SESSION['author_id']))
    {
      return true;
    }
    else
    {
      return self::check_rememberme_key();
    }
  }

  // just simple duble md5 for now , static so author class could use it fast
  public static function hash_password($password)
  {
    return md5(md5($password));
  }
}
################################################################################
// Function
################################################################################
/*
  master source http://www.w3schools.com/php/php_file_upload.asp
   ** secure file name . extension
*/
function my_file_upload($fileKey)
{
  if (empty($_FILES[$fileKey]["name"]))
  {
    throw new Exception("bad file_upload call");
  }
  $target_dir = dirname( realpath( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
  // validate permissions
  if (!is_writeable($target_dir))
  {
    throw new Exception("please check write permission @path {$target_dir}");
  }
  $target_file = $target_dir . basename($_FILES[$fileKey]["name"]);
  $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
  $new_file_name = md5(basename($_FILES[$fileKey]["name"]).time()).".".$imageFileType;
  $target_file = $target_dir . $new_file_name;

  // Check if image file is a actual image or fake image
  $check = getimagesize($_FILES[$fileKey]["tmp_name"]);
  if($check == false)
  {
    throw new Exception("File is not an image.");
  }
  // Check if file already exists
  if (file_exists($target_file))
  {
    throw new Exception("Sorry, file already exists.");
  }
  // Check file size
  if ($_FILES[$fileKey]["size"] > 500000)
  {
    throw new Exception("Sorry, your file is too large.");
  }
  // Allow certain file formats
  $allowed_ext = array('jpg','png','jpeg','gif');
  if(!in_array($imageFileType, $allowed_ext))
  {
    throw new Exception("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
  }
  if (move_uploaded_file($_FILES[$fileKey]["tmp_name"], $target_file))
  {
    return $new_file_name;
  }
  else
  {
    throw new Exception("Sorry, there was an error uploading your file.");
  }
}
################################################################################
// main bootstrap
################################################################################
// assign page
if (isset($_GET['page']) && !empty($_GET['page']))
{
  $page = $_GET['page'];
}
else
{
  $page = 'index';
}
// assuming all action will need db connection
$database = new Database($config['db']);
// force login if not user
if (!SimpleAuth::user_is_logged_in())
{
  $page = 'login';
}
$routes  = New Routes($page);
?>
