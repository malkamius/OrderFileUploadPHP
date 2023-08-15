<?php 
require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/Layout/header.php"); 
require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/PHPInclude/Orders.php");
require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/PHPInclude/OrderFormat.php");
if(!isset($_REQUEST["viewOrderKey"]))
	$SignInManager->Authorize(array("ADMINISTRATOR", "BROWSE"));

if(isset($_REQUEST["id"]))
{
	$order = $OrdersContext->GetOrder($_REQUEST["id"]);
	if(isset($_REQUEST["viewOrderKey"]) && $_REQUEST["viewOrderKey"] != $order->ViewOrderKey)
	{
		$order = new Order();
	}
	else
		$files = $OrdersContext->GetFiles($order->OrderId);
}
else
{
	$order = new $Order();
	$files = array();
}
	echo FormatOrderAsHTML($order, isset($_REQUEST["viewOrderKey"]));
?>
<?php require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/Layout/footer.php");