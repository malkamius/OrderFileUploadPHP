<?php
require_once($_SERVER['DOCUMENT_ROOT'] .'/fileupload/PHPInclude/config.php');
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
	public $DateCreated = "";
	public $FilesComplete = "";
	public $FileCount = 0;
	
	function GetXml()
	{
		$details = new SimpleXMLElement("<Order />");
		$details->addAttribute("Id", $this->OrderId);
		$details->addChild("ContactName", $this->ContactName);
		$details->addChild("PhoneNumber", $this->PhoneNumber);
		$details->addChild("EmailAddress", $this->EmailAddress);
		$details->addChild("CompanyName", $this->CompanyName);
		$details->addChild("Address1", $this->Address1);
		$details->addChild("Address2", $this->Address2);
		$details->addChild("City", $this->City);
		$details->addChild("State", $this->State);
		$details->addChild("ZipCode", $this->ZipCode);
		$details->addChild("DueDate", $this->DueDate);
		$details->addChild("DueTime", $this->DueTime);
		$details->addChild("ProjectNumber", $this->ProjectNumber);
		$details->addChild("PurchaseOrderNumber", $this->PurchaseOrderNumber);
		$details->addChild("ProjectName", $this->ProjectName);
		$details->addChild("Notes", $this->Notes);
		$dom = dom_import_simplexml($details)->ownerDocument;
		$dom->formatOutput = true;
		return $dom->saveXML();
	}
}

class FileInformation
{
	public $OrderId = 0;
	public $FileId = 0;
	public $FileName = "";
	public $StoredFilePath = "";
	public $ContentType = "";
	public $Length = 0;
	public $WrittenBytes = 0;
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
					   project_name, notes, status, view_order_key, upload_file_key,
					   date_created,
					   (SELECT SUM(files.length = files.written_bytes) = count(1) as FilesComplete FROM files WHERE files.order_id = orders.order_id) as FilesComplete,
					   (SELECT COUNT(1) FROM files WHERE files.order_id = orders.order_id) as FileCount
				FROM orders LIMIT " . $startindex . ", " . $count;

        if($stmt = mysqli_prepare($this->OrdersDBConnection, $sql)){
            if($stmt->execute()){
                $stmt->bind_result($order_id,
				$contact_name, $phone_number, $email_address, 
				$company_name, $address1, $address2, $city, $state, $zipcode,
				$duedate, $duetime,
				$project_number, $purchase_order_number, $project_name, 
				$notes, $status, $view_order_key, $upload_file_key, $date_created, $files_complete, $files_count);
                
                while ($stmt->fetch()) {
                    $result = new Order();
					$result->OrderId = $order_id;
					$result->ContactName = $contact_name;
					$result->PhoneNumber = preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $phone_number);
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
					$result->DateCreated = $date_created;
					$result->FilesComplete = $files_complete != 0;
					$result->FileCount = $files_count;
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
					   project_name, notes, status, view_order_key, upload_file_key,
					   date_created,
					   (SELECT SUM(files.length = files.written_bytes) = count(1) as FilesComplete FROM files WHERE files.order_id = orders.order_id) as FilesComplete,
					   (SELECT COUNT(1) FROM files WHERE files.order_id = orders.order_id) as FileCount
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
				$notes, $status, $view_order_key, $upload_file_key, $date_created, $files_complete, $files_count);
                
                if($stmt->fetch()) {
					$result->OrderId = $order_id;
					$result->ContactName = $contact_name;
					$result->PhoneNumber = preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $phone_number);
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
					$result->DateCreated = $date_created;
					$result->FilesComplete = $files_complete != 0;
					$result->FileCount = $files_count;
                }
                
            } 
            
            // Close statement
            $stmt->close();
        }
		
		return $result;
	}
	
	function GetOrderFilesComplete($orderid)
	{
		$result = 0;
		$sql = "SELECT SUM(files.length = files.written_bytes) = count(1) as FilesComplete FROM files WHERE files.order_id = ?;";

        if($stmt = mysqli_prepare($this->OrdersDBConnection, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "d", $param_id);
            
            // Set parameters
            $param_id = $orderid;
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Store result
                $stmt->bind_result($result);
                
                $stmt->fetch();
            } 
            
            // Close statement
            $stmt->close();
        }
		
		return $result != 0;
	}
	
	
	
	function SaveOrder($Order)
	{
		$result = 0;
		$sql = "INSERT INTO orders (contact_name, phone_number, email_address, 
					   company_name, address1, address2, city, state, zipcode,
					   duedate, duetime, project_number, purchase_order_number,
					   project_name, notes, status, view_order_key, upload_file_key, date_created)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW());";

        if($stmt = mysqli_prepare($this->OrdersDBConnection, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ssssssssssssssssss", $paramContactName, $paramPhoneNumber,
			$paramEmailAddress, $paramCompanyName, $paramAddress1,
			$paramAddress2, $paramCity, $paramState, $paramZipCode, $paramDueDate, 
			$paramDueTime, $paramProjectNumnber, $paramPurchaseOrderNumber, $paramProjectName, 
			$paramNotes, $paramStatus, $paramViewOrderKey, $paramUploadFileKey);
            
            // Set parameters
            $paramContactName = $Order->ContactName;
			$paramPhoneNumber = $Order->PhoneNumber;
			$paramEmailAddress = $Order->EmailAddress;
			$paramCompanyName = $Order->CompanyName;
			$paramAddress1 = $Order->Address1;
			$paramAddress2 = $Order->Address2;
			$paramCity = $Order->City;
			$paramState = $Order->State;
			$paramZipCode = $Order->ZipCode;
			$paramDueDate = $Order->DueDate;
			$paramDueTime = $Order->DueTime;
			$paramProjectNumnber = $Order->ProjectNumber;
			$paramPurchaseOrderNumber = $Order->PurchaseOrderNumber;
			$paramProjectName = $Order->ProjectName;
			$paramNotes = $Order->Notes;
			$paramStatus = $Order->Status;
			$paramViewOrderKey = $Order->ViewOrderKey;
			$paramUploadFileKey = $Order->UploadFileKey;
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                $result = $this->OrdersDBConnection->insert_id;
            } 

			
            
            // Close statement
            $stmt->close();
        }
		
		return $result;
	}
	
	function SaveOrderStatus($Order)
	{
		$sql = "UPDATE orders SET status = ? WHERE order_id = ?;";

        if($stmt = mysqli_prepare($this->OrdersDBConnection, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sd", $paramStatus, $paramOrderId);
            
            // Set parameters
            $paramStatus = $Order->Status;
			$paramOrderId = $Order->OrderId;
            
            // Attempt to execute the prepared statement
            $stmt->execute();
            // Close statement
            $stmt->close();
        }
		
	}
	
	function SaveOrderFileInformationBeforeUpload($Order, $FileId, $FileName, $FilePath, $Length, $ContentType, $WrittenBytes = 0)
	{
		$result = 0;
		
		if($FileId == 0)
		{
			$sql = "INSERT INTO files (order_id, name, filepath, 
						   content_type, length, written_bytes)
					VALUES (?, ?, ?, ?, ?, 0);";

			if($stmt = mysqli_prepare($this->OrdersDBConnection, $sql)){
				// Bind variables to the prepared statement as parameters
				mysqli_stmt_bind_param($stmt, "dsssd", $paramOrderId, $paramName, $paramPath, $paramContentType, $paramLength);
				
				// Set parameters
				$paramOrderId = $Order->OrderId;
				$paramName = $FileName;
				$paramPath = $FilePath;
				$paramLength = $Length;
				$paramContentType = $ContentType;
				// Attempt to execute the prepared statement
				if($stmt->execute()){
					$result = $this->OrdersDBConnection->insert_id;
				} 

				// Close statement
				$stmt->close();
			}
		}
		else
		{
			$sql = "UPDATE files SET name = ?, filepath = ?, 
						content_type = ?, length = ?, written_bytes = ? 
					WHERE order_id = ? AND file_id = ?";

			if($stmt = mysqli_prepare($this->OrdersDBConnection, $sql)){
				// Bind variables to the prepared statement as parameters
				mysqli_stmt_bind_param($stmt, "sssdddd", $paramName, $paramPath, 
					$paramContentType, $paramLength, $paramWrittenBytes, $paramOrderId, $paramFileId);
				
				// Set parameters
				$paramOrderId = $Order->OrderId;
				$paramFileId = $FileId;
				$paramName = $FileName;
				$paramPath = $FilePath;
				$paramLength = $Length;
				$paramContentType = $ContentType;
				$paramWrittenBytes = $WrittenBytes;
				// Attempt to execute the prepared statement
				if($stmt->execute()){
					$result = $FileId;
				} 

				// Close statement
				$stmt->close();
			}
		}
		return $result;
	}
	
	function SaveOrderFileInformationWrittenBytes($FileId, $WrittenBytes)
	{
		$sql = "UPDATE files SET written_bytes = ? 
				WHERE file_id = ?";

		if($stmt = mysqli_prepare($this->OrdersDBConnection, $sql)){
			// Bind variables to the prepared statement as parameters
			mysqli_stmt_bind_param($stmt, "dd", $paramWrittenBytes, $paramFileId);
			
			// Set parameters		
			$paramFileId = $FileId;
			$paramWrittenBytes = $WrittenBytes;
			// Attempt to execute the prepared statement
			$stmt->execute();
			// Close statement
			$stmt->close();
		}
	}
	
	function GetFile($FileId)
	{
		$result = new FileInformation();
		
		$sql = "SELECT order_id, file_id, name, filepath, 
					   content_type, length, written_bytes
				FROM files WHERE file_id = ?;";

        if($stmt = mysqli_prepare($this->OrdersDBConnection, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "d", $param_id);
            
            // Set parameters
            $param_id = $FileId;
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Store result
                $stmt->bind_result($order_id,
				$file_id, $name, $filepath, 
				$content_type, $length, $written_bytes);
                
                if($stmt->fetch()) {
					$result->OrderId = $order_id;
					$result->FileId = $file_id;
					$result->FileName = $name;
					$result->StoredFilePath = $filepath;
					$result->ContentType = $content_type;
					$result->Length = $length;
					$result->WrittenBytes = $written_bytes;
                }
                
            } 
            
            // Close statement
            $stmt->close();
        }
		
		return $result;
	}
	
	function GetFiles($OrderId)
	{
		$result = array();
		
		$sql = "SELECT order_id, file_id, name, filepath, 
					   content_type, length, written_bytes
				FROM files WHERE order_id = ?;";

        if($stmt = mysqli_prepare($this->OrdersDBConnection, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "d", $param_id);
            
            // Set parameters
            $param_id = $OrderId;
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Store result
                $stmt->bind_result($order_id,
				$file_id, $name, $filepath, 
				$content_type, $length, $written_bytes);
                
                while($stmt->fetch()) {
					$newfile = new FileInformation();
					$newfile->OrderId = $order_id;
					$newfile->FileId = $file_id;
					$newfile->FileName = $name;
					$newfile->StoredFilePath = $filepath;
					$newfile->ContentType = $content_type;
					$newfile->Length = $length;
					$newfile->WrittenBytes = $written_bytes;
					$result[] = $newfile;
                }
                
            } 
            
            // Close statement
            $stmt->close();
        }
		
		return $result;
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