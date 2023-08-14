<?php 
require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/header.php"); 
require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/Orders.php");

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
?>
<?php
	if($order->OrderId != 0)
	{
		?>
<h4>Order</h4>
    <hr />
    <dl class="row">
        <dt class="col-sm-2">
            Order Id
        </dt>
        <dd class="col-sm-10">
            <?php echo htmlspecialchars($order->OrderId); ?>
        </dd>
        <dt class="col-sm-2">
            Contact Name
        </dt>
        <dd class="col-sm-10">
            <?php echo htmlspecialchars($order->ContactName); ?>
        </dd>
        <dt class="col-sm-2">
            Phone Number
        </dt>
        <dd class="col-sm-10">
            <?php echo htmlspecialchars($order->PhoneNumber); ?>
        </dd>
        <dt class="col-sm-2">
            Email Address
        </dt>
        <dd class="col-sm-10">
            <?php echo htmlspecialchars($order->EmailAddress); ?>
        </dd>
        <dt class="col-sm-2">
            Company Name
        </dt>
        <dd class="col-sm-10">
            <?php echo htmlspecialchars($order->CompanyName); ?>
        </dd>
        <dt class="col-sm-2">
            Address 1
        </dt>
        <dd class="col-sm-10">
            <?php echo htmlspecialchars($order->Address1); ?>
        </dd>
        <dt class="col-sm-2">
            Address 2
        </dt>
        <dd class="col-sm-10">
            <?php echo htmlspecialchars($order->Address2); ?>
        </dd>
        <dt class="col-sm-2">
            City
        </dt>
        <dd class="col-sm-10">
            <?php echo htmlspecialchars($order->City); ?>
        </dd>
        <dt class="col-sm-2">
            State
        </dt>
        <dd class="col-sm-10">
            <?php echo htmlspecialchars($order->State); ?>
        </dd>
        <dt class="col-sm-2">
            Zip Code
        </dt>
        <dd class="col-sm-10">
            <?php echo htmlspecialchars($order->ZipCode); ?>
        </dd>
        <dt class="col-sm-2">
            Date Due
        </dt>
        <dd class="col-sm-10">
            <?php echo htmlspecialchars($order->DueDate); ?>
        </dd>
        <dt class="col-sm-2">
            Time Due
        </dt>
        <dd class="col-sm-10">
            <?php echo htmlspecialchars($order->DueTime); ?>
        </dd>
		<dt class="col-sm-2">
            Date Created
        </dt>
        <dd class="col-sm-10">
            <?php echo htmlspecialchars($order->DateCreated); ?>
        </dd>
        
        <dt class="col-sm-2">
            Project Number
        </dt>
        <dd class="col-sm-10">
            <?php echo htmlspecialchars($order->ProjectNumber); ?>
        </dd>
        <dt class="col-sm-2">
            PO Number
        </dt>
        <dd class="col-sm-10">
            <?php echo htmlspecialchars($order->PurchaseOrderNumber); ?>
        </dd>
        <dt class="col-sm-2">
            Project Name
        </dt>
        <dd class="col-sm-10">
            <?php echo htmlspecialchars($order->ProjectName); ?>
        </dd>
        <dt class="col-sm-2">
            Notes
        </dt>
        <dd class="col-sm-10">
            <?php echo htmlspecialchars($order->Notes); ?>
        </dd>
	    <dt class="col-sm-2">
            Status
        </dt>
        <dd class="col-sm-10">
            <?php echo htmlspecialchars($order->Status); ?>
        </dd>
    </dl>
	<table class="styled-table">
		<thead>
			<tr>
				<th>
					File Name
				</th>
				<th>
					Content Type
				</th>
				<th>
					Size
				</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
<?php
		foreach($files as $fileinfo)
		{
			print("<tr>\n");
            print("<td>".htmlspecialchars($fileinfo->FileName)."</td>\n");
			print("<td>".htmlspecialchars($fileinfo->ContentType)."</td>\n");
			print("<td>".$fileinfo->Length."</td>\n");
			print("<td>\n");
			if(file_exists($fileinfo->StoredFilePath))
			{
				print("<a href='/fileupload/ViewFile.php?id=".$fileinfo->FileId."&download=false". 
				(isset($_REQUEST["viewOrderKey"])? "&viewOrderKey=".$_REQUEST["viewOrderKey"] : "" )."' target='_blank'>View</a> ");
				print("<a href='/fileupload/ViewFile.php?id=".$fileinfo->FileId."&download=true".
				(isset($_REQUEST["viewOrderKey"])? "&viewOrderKey=".$_REQUEST["viewOrderKey"] : "" )."'>Download</a>\n");
			}
			print("</td>\n");
            print("</tr>\n");
		}
		print("</tbody>\n");
		print("</table>\n");
		
        print("<a href='/fileupload/DownloadZip.php?id=".$order->OrderId."'>Download ZIP</a>\n");
	}
	else
		print("Order not found.");
?>
<?php require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/footer.php");