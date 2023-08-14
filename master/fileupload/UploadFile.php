<?php
    require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/Orders.php"); 
	
	class ChunkUploadInformation
	{
		public $WrittenBytes = 0;
	}
	
	if(!isset($_REQUEST["OrderId"]) || !isset($_REQUEST["UploadFileKey"]) || !isset($_REQUEST["FileId"]))
	{
		die("Missing required information.");
	}
	else
	{
		$order = $OrdersContext->GetOrder($_REQUEST["OrderId"]);
		
		if($order->UploadFileKey == $_REQUEST["UploadFileKey"])
		{
			$file = $OrdersContext->GetFile($_REQUEST["FileId"]);
			
			if($file->OrderId == $order->OrderId)
			{
				if(!file_exists(DATA_FILEPATH))
					mkdir(DATA_FILEPATH, 0777, true);
				//print_r($_FILES);
				$tempname = $_FILES['file']["tmp_name"][0];
				$tempfile = fopen($tempname, "rb");
				$storedfile = fopen($file->StoredFilePath, "ab");
				
				while(!feof($tempfile))
				{
					$bytes = fread($tempfile,4096);
					fwrite($storedfile, $bytes, 4096);
				}
				
				$chunkupload = new ChunkUploadInformation();
				$chunkupload->WrittenBytes = ftell($storedfile);
				
				$OrdersContext->SaveOrderFileInformationWrittenBytes($file->FileId, $chunkupload->WrittenBytes);
				if($OrdersContext->GetOrderFilesComplete($file->OrderId))
				{
					$order->Status = "Awaiting file retrieval";
					$OrdersContext->SaveOrderStatus($order);
				}
				
				fclose($storedfile);
				fclose($tempfile);
				
				unlink($tempname);
				print(json_encode($chunkupload));
			}
			else
				print("Access denied.");
		}
		else
		{
			print("Access denied.");
		}	
	}