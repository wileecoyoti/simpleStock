# simpleStock
## a small PHP/MySQL inventory management project
This is built to be able to run on a small web server or something like a local XAMPP installation. Very little overhead, as long as it answers on HTTP and has PHP and MySQL installed the service should work. No tail of libraries or dependencies to have to mess around with. As it stands the whole project is under 60K, so very lightweight. Obviously this will grow with your list of components and stock/product/order histories.

![.](https://github.com/wileecoyoti/simpleStock/blob/main/Screenshot1.png)

### Basic Setup
Should be able to establish a database using the SQL query in databaseSQL.txt.

You'll need to modify the values in config.php to match your database credentials.

edit config.php by marking out (// before) these lines:
```
if (!isset($_SESSION['user_id'])) {
//    header("Location: login.php");
//    exit();
}
```
Register your first user by going to www.yoursite.com/directory/register.php.
Switch those lines back to:
```
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
```
### Use This Thing (stuff to know)
**Orders** are made up of **Products**, **Products** are made of **Stock**. I know this doesn't always work this way, for example in my own use there are single component stock items that are also sold as the final product. If this is your situation then create a product assembly (WidgetProduct) that has that single component (WidgetComponent) as its complete Bill of Materials (BOM). You'll have an extra step of creating stock, then converting that stock to an assembly so that it shows up in your order options.

Understanding that, the normal order of things is to start with "Manage Components." You'll add your parts, set up initial quantities, and then move on to Manage Product Assemblies.

Under Product Assemblies you can create a new product name, press "edit" and then start adding components and quantities of those components that are used to make a completed assembly. For example you might have 1 housing, 1 circuit board, 1 battery, and 6 screws.

From that point every time you get a new shipment of stock? Add/Remove Stock. Built a dozen units? Stock -> Product Assemblies. Got an order? Product Assemblies -> Orders.

Did inventory and it doesn't match your system's numbers? You can reconcile that by changing the values in either Manage Components or Manage Product Assemblies.

### This is far from perfect
I made this because I needed it. There's plenty to be improved upon and it's likely that as simple as it is you'll find things that don't act the way you expect. Feel free to request changes, make a fork, or whatever. I'll likely keep making minor changes as I go too.

