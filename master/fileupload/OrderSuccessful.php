<?php
	require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/header.php"); 
	require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/Orders.php"); 

	if(!isset($_REQUEST["id"]) || !isset($_REQUEST["viewOrderKey"]))
	{
		print("Order not found.");
	}
	else
	{
		$order = $OrdersContext->GetOrder($_REQUEST["id"]);
		
		if($order->ViewOrderKey == $_REQUEST["viewOrderKey"])
		{
			print('<div class="text-center">');
			print("Order #" . $order->OrderId . " submitted succesfully. ");
			print('</div>');
		}
		else
		{
			print("Order not found.");
		}	
	}
	
	require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/footer.php"); 