<?php 
require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/Layout/header.php"); 
$SignInManager->Authorize(array("ADMINISTRATOR", "BROWSE"));
require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/PHPInclude/Orders.php"); 
?>
<?php 
global $OrdersContext;

$pagesize = 20;
if(isset($_REQUEST["page"]))
    $page = intval($_REQUEST["page"]);
else
    $page = 1;

$ordercount = $OrdersContext->GetOrderCount();
$orders = $OrdersContext->GetOrders(($page - 1) * $pagesize, $pagesize);

?>
<?php
if(count($orders) == 0)
    echo "No orders to see here.";
else
{
    echo "<table class='styled-table'>";
    echo "<thead><tr><th>Id</th><th>Contact</th><th>Status</th><th># files</th><th>Created</th><th>Due</th><th>Status</th><th></th></tr></thead>";
    foreach($orders as $order)
    {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($order->OrderId) . "</td>";
        echo "<td>" . htmlspecialchars($order->ContactName) . "</td>";
		echo "<td>" . htmlspecialchars($order->Status) . "</td>";
		echo "<td>" . htmlspecialchars($order->FileCount) . "</td>";
		echo "<td>" . htmlspecialchars($order->DateCreated) . "</td>";
		echo "<td>" . htmlspecialchars($order->DueDate) . " " . htmlspecialchars($order->DueTime) . "</td>";
		echo "<td>" . htmlspecialchars($order->Status) . "</td>";
        echo "<td><a href='/fileupload/ViewOrder.php?id=" . htmlspecialchars($order->OrderId) . "' class='button'>View</a></td>";
        echo "</tr>";
    }
    echo "</table>";
    for($i = 1; $i < ($ordercount / $pagesize) + 1; $i++)
    {
        if($i == $page)
            echo "<a>" . $i . "</a>";
        else
            echo "<a href='/fileupload/ViewOrders.php?page=" . $i . "'>" . $i . "</a>";
    }
}
?>
<?php require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/Layout/footer.php");
