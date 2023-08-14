<?php
class Order
{
	public $OrderId = 0;
	public $ContactName = "";
	public $PhoneNumber = "";
	public $EmailAddress = "";
	
	public $CompanyName = "";
	public $Address1 = "";
	public $Address2 = "";
	public $City = "";
	public $State = "";
	public $ZipCode = "";
	
	public $DueDate = "";
	public $DueTime = "";
	
	public $ProjectNumber = "";
	public $PurchaseOrderNumber = "";
	public $ProjectName = "";
	public $Notes = "";
	public $Status = "";
	public $ViewOrderKey = "";
	public $UploadFileKey = "";
	
}

class OrdersDbContext
{
	private $OrdersDBConnection = false;
	function GetOrderCount()
	{
		$result = 0;
		$sql = "SELECT count(orders.order_id) FROM orders;";

		if($stmt = mysqli_prepare($this->OrdersDBConnection, $sql)){
			
			// Attempt to execute the prepared statement
			if($stmt->execute()){
				// Store result
				$stmt->bind_result($result);
				
				$stmt->fetch();
			} 
			
			// Close statement
			$stmt->close();
		}
		
		return $result;
	}
	
	function GetOrders($startindex, $count)
	{
		$results = array();
		$sql = "SELECT order_id, contact_name, phone_number, email_address, 
					   company_name, address1, address2, city, state, zipcode,
					   duedate, duetime, project_number, purchase_order_number,
					   project_name, notes, status, view_order_key, upload_file_key 
				FROM orders LIMIT " . $startindex . ", " . $count;

        if($stmt = mysqli_prepare($this->OrdersDBConnection, $sql)){
            if($stmt->execute()){
                $stmt->bind_result($order_id,
				$contact_name, $phone_number, $email_address, 
				$company_name, $address1, $address2, $city, $state, $zipcode,
				$duedate, $duetime,
				$project_number, $purchase_order_number, $project_name, 
				$notes, $status, $view_order_key, $upload_file_key);
                
                while ($stmt->fetch()) {
                    $result = new Order();
					$result->OrderId = $order_id;
					$result->ContactName = $contact_name;
					$result->PhoneNumber = $phone_number;
					$result->EmailAddress = $email_address;
					 
					$result->CompanyName = $company_name;
					$result->Address1 = $address1;
					$result->Address2 = $address2;
					$result->City = $city;
					$result->State = $state;
					$result->ZipCode = $zipcode;
					 
					$result->DueDate = $duedate;
					$result->DueTime = $duetime;
					 
					$result->ProjectNumber = $project_number;
					$result->PurchaseOrderNumber = $purchase_order_number;
					$result->ProjectName = $project_name;
					$result->Notes = $notes;
					$result->Status = $status;
					$result->ViewOrderKey = $view_order_key;
					$result->UploadFileKey = $upload_file_key;
					$results[] = $result;
                }
                
            } 
            $stmt->close();
        }
		
		return $results;
	}
	
	function GetOrder($orderid)
	{
		$result = new Order();
		$sql = "SELECT order_id, contact_name, phone_number, email_address, 
					   company_name, address1, address2, city, state, zipcode,
					   duedate, duetime, project_number, purchase_order_number,
					   project_name, notes, status, view_order_key, upload_file_key 
				FROM orders WHERE order_id = ?;";

        if($stmt = mysqli_prepare($this->OrdersDBConnection, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "d", $param_id);
            
            // Set parameters
            $param_id = $orderid;
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Store result
                $stmt->bind_result($order_id,
				$contact_name, $phone_number, $email_address, 
				$company_name, $address1, $address2, $city, $state, $zipcode,
				$duedate, $duetime,
				$project_number, $purchase_order_number, $project_name, 
				$notes, $status, $view_order_key, $upload_file_key);
                
                if($stmt->fetch()) {
					$result->OrderId = $order_id;
					$result->ContactName = $contact_name;
					$result->PhoneNumber = $phone_number;
					$result->EmailAddress = $email_address;
					 
					$result->CompanyName = $company_name;
					$result->Address1 = $address1;
					$result->Address2 = $address2;
					$result->City = $city;
					$result->State = $state;
					$result->ZipCode = $zipcode;
					 
					$result->DueDate = $duedate;
					$result->DueTime = $duetime;
					 
					$result->ProjectNumber = $project_number;
					$result->PurchaseOrderNumber = $purchase_order_number;
					$result->ProjectName = $project_name;
					$result->Notes = $notes;
					$result->Status = $status;
					$result->ViewOrderKey = $view_order_key;
					$result->UploadFileKey = $upload_file_key;
                }
                
            } 
            
            // Close statement
            $stmt->close();
        }
		
		return result;
	}
	
	function __construct()
	{
		$this->OrdersDBConnection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, ORDERS_DB_NAME);
		// Check connection
		if($this->OrdersDBConnection === false){
			die("ERROR: Could not connect to Orders MySQL. " . mysqli_connect_error());
		}
	}
}

$OrdersContext = new OrdersDbContext();

?>